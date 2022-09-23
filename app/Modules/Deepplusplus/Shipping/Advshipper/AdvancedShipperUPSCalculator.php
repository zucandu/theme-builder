<?php 

namespace Advshipper;

/**
 * Titles for UPS shipping methods
 */
define('MODULE_ADVANCED_SHIPPER_TEXT_UPS_TITLE_PREFIX', ' ');
define('MODULE_ADVANCED_SHIPPER_TEXT_UPS_SHIPPING_SERVICE_1DM', 'Next Day Air Early AM');
define('MODULE_ADVANCED_SHIPPER_TEXT_UPS_SHIPPING_SERVICE_1DML', 'Next Day Air Early AM Letter');
define('MODULE_ADVANCED_SHIPPER_TEXT_UPS_SHIPPING_SERVICE_1DA', 'Next Day Air');
define('MODULE_ADVANCED_SHIPPER_TEXT_UPS_SHIPPING_SERVICE_1DAL', 'Next Day Air Letter');
define('MODULE_ADVANCED_SHIPPER_TEXT_UPS_SHIPPING_SERVICE_1DAPI', 'Next Day Air Intra (Puerto Rico)');
define('MODULE_ADVANCED_SHIPPER_TEXT_UPS_SHIPPING_SERVICE_1DP', 'Next Day Air Saver');
define('MODULE_ADVANCED_SHIPPER_TEXT_UPS_SHIPPING_SERVICE_1DPL', 'Next Day Air Saver Letter');
define('MODULE_ADVANCED_SHIPPER_TEXT_UPS_SHIPPING_SERVICE_2DM', '2nd Day Air AM');
define('MODULE_ADVANCED_SHIPPER_TEXT_UPS_SHIPPING_SERVICE_2DML', '2nd Day Air AM Letter');
define('MODULE_ADVANCED_SHIPPER_TEXT_UPS_SHIPPING_SERVICE_2DA', '2nd Day Air');
define('MODULE_ADVANCED_SHIPPER_TEXT_UPS_SHIPPING_SERVICE_2DAL', '2nd Day Air Letter');
define('MODULE_ADVANCED_SHIPPER_TEXT_UPS_SHIPPING_SERVICE_3DS', '3 Day Select');
define('MODULE_ADVANCED_SHIPPER_TEXT_UPS_SHIPPING_SERVICE_GND', 'Ground');
define('MODULE_ADVANCED_SHIPPER_TEXT_UPS_SHIPPING_SERVICE_GNCRES', 'Ground Residential');
define('MODULE_ADVANCED_SHIPPER_TEXT_UPS_SHIPPING_SERVICE_GNDCOM', 'Ground Commercial');
define('MODULE_ADVANCED_SHIPPER_TEXT_UPS_SHIPPING_SERVICE_STD', 'Canada Standard');
define('MODULE_ADVANCED_SHIPPER_TEXT_UPS_SHIPPING_SERVICE_XPR', 'Worldwide Express');
define('MODULE_ADVANCED_SHIPPER_TEXT_UPS_SHIPPING_SERVICE_XPRL', 'Worldwide Express Letter');
define('MODULE_ADVANCED_SHIPPER_TEXT_UPS_SHIPPING_SERVICE_XDM', 'Worldwide Express Plus');
define('MODULE_ADVANCED_SHIPPER_TEXT_UPS_SHIPPING_SERVICE_XDML', 'Worldwide Express Plus Letter');
define('MODULE_ADVANCED_SHIPPER_TEXT_UPS_SHIPPING_SERVICE_XPD', 'Worldwide Expedited');
define('MODULE_ADVANCED_SHIPPER_TEXT_UPS_SHIPPING_SERVICE_WXS', 'Worldwide Saver');

class AdvancedShipperUPSCalculator extends \Advancedshipper {
    /**
	 * The configuration settings for this instance.
	 *
	 * @var     array
	 * @access  public
	 */
	public $_config, $_upsProductCode, $_upsOriginPostalCode, 
			$_upsOriginCountryCode, $_upsDestCountryCode, $_upsDestPostalCode,
			$_upsRateCode, $_upsContainerCode, $_upsPackageWeight,
			$_upsResComCode, $_upsActionCode = null;
	
	// }}}
	
	
	// {{{ Class Constructor
	
	/**
	 * Create a new instance of the AdvancedShipperUPSCalculator class
	 *
	 * @param   array     $ups_config   An associative array with the configuration settings for
	 *                                  this instance.
	 */
	function __construct($ups_config)
	{
        parent::__construct();
        
		$this->_config = $ups_config;
		
	}
	
	/**
	 * Set UPS Product Code
	 *
	 * @param string $prod
	 */
	function _upsProduct($prod){
		$this->_upsProductCode = $prod;
	}

	/**
	 * Set UPS Origin details
	 *
	 * @param string $postal
	 * @param string $country
	 */
	function _upsOrigin($postal, $country){
		$this->_upsOriginPostalCode = substr($postal, 0, 5);
		$this->_upsOriginCountryCode = $country;
	}

	/**
	 * Set UPS Destination information
	 *
	 * @param string $postal
	 * @param string $country
	 */
	function _upsDest($postal, $country){
		
		$postal = str_replace(' ', '', $postal);
		if ($country == 'US') {
			$this->_upsDestPostalCode = substr($postal, 0, 5);
		} else {
			$this->_upsDestPostalCode = substr($postal, 0, 6);
		}

		$this->_upsDestCountryCode = $country;
	}

	/**
	 * Set UPS rate-quote method
	 *
	 * @param string $foo
	 */
	function _upsRate($foo) {
		switch ($foo) {
			case 'RDP':
				$this->_upsRateCode = 'Regular+Daily+Pickup';
			break;
			case 'OCA':
				$this->_upsRateCode = 'On+Call+Air';
			break;
			case 'OTP':
				$this->_upsRateCode = 'One+Time+Pickup';
			break;
			case 'LC':
				$this->_upsRateCode = 'Letter+Center';
			break;
			case 'CC':
				$this->_upsRateCode = 'Customer+Counter';
			break;
		}
	}

	/**
	 * Set UPS Container type
	 *
	 * @param string $foo
	 */
	function _upsContainer($foo) {
		switch ($foo) {
			case 'CP': // Customer Packaging
				$this->_upsContainerCode = '00';
			break;
			case 'ULE': // UPS Letter Envelope
				$this->_upsContainerCode = '01';
			break;
			case 'UT': // UPS Tube
				$this->_upsContainerCode = '03';
			break;
			case 'UEB': // UPS Express Box
				$this->_upsContainerCode = '21';
			break;
			case 'UW25': // UPS Worldwide 25 kilo
				$this->_upsContainerCode = '24';
			break;
			case 'UW10': // UPS Worldwide 10 kilo
				$this->_upsContainerCode = '25';
			break;
		}
	}

	/**
	 * Set UPS package weight
	 *
	 * @param string $foo
	 */
	function _upsWeight($foo) {
		$this->_upsPackageWeight = $foo;
	}
	/**
	 * Set UPS address-quote method (residential vs commercial)
	 *
	 * @param string $foo
	 */
	function _upsRescom($foo) {
		switch ($foo) {
		case 'RES': // Residential Address
			$this->_upsResComCode = '1';
		break;
		case 'COM': // Commercial Address
			$this->_upsResComCode = '0';
		break;
		}
	}

	/**
	 * Set UPS Action method
	 *
	 * @param string/integer $action
	 */
	function _upsAction($action) {
		/* 3 - Single Quote
		4 - All Available Quotes */

		$this->_upsActionCode = $action;
	}
	/**
	 * Sent request for quote to UPS via older HTML method
	 *
	 * @return array
	 */
	function _upsGetQuote() {

		if (!isset($this->_upsActionCode)) $this->_upsActionCode = '4';

		$request = join('&', [
			'accept_UPS_license_agreement=yes',
			'10_action=' . $this->_upsActionCode,
			'13_product=' . $this->_upsProductCode,
			'14_origCountry=' . $this->_upsOriginCountryCode,
			'15_origPostal=' . $this->_upsOriginPostalCode,
			'19_destPostal=' . $this->_upsDestPostalCode,
			'22_destCountry=' . $this->_upsDestCountryCode,
			'23_weight=' . $this->_upsPackageWeight,
			'47_rate_chart=' . $this->_upsRateCode,
			'48_container=' . $this->_upsContainerCode,
			'49_residential=' . $this->_upsResComCode
		]);

		$this->_debug("Data being sent to UPS: \n\n" . str_replace('&', "<br />\n", $request), true);

		$http = new \httpClient();
		if ($http->Connect('www.ups.com', 443)) {
			$http->addHeader('Host', 'www.ups.com');
			$http->addHeader('User-Agent', 'Deepplusplus');
			$http->addHeader('Connection', 'Close');
			
			if ($http->Get('/using/services/rave/qcostcgi.cgi?' . $request)) $body = $http->getBody();

			$http->Disconnect();
		} else {
			return false;
		}

		// BOF: UPS USPS
		/*
		TEST by checking out in the catalog; try a variety of shipping destinations to be sure
		your customers will be properly served.  If you are not getting any quotes, try enabling
		more alternatives in admin. Make sure your store's postal code is set in Admin ->
		Configuration -> Shipping/Packaging, since you won't get any quotes unless there is
		a origin that UPS recognizes.

		If you STILL don't get any quotes, here is a way to find out exactly what UPS is sending
		back in response to rate quote request.  At line 278, you will find this statement in a
		comment block:

		mail('you@yourdomain.com','UPS response',$body,'From: <you@yourdomain.com>');
		*/
		// EOF: UPS USPS
		$this->_debug("Results from contacting UPS: \n\n" . str_replace('%', ' - ', nl2br($body)), true);

		$body_array = explode("\n", $body);

		if (strpos($body, 'Missing ConsigneePostalCode') !== false) {
			return false;
		}

		$returnval = array();
		$errorret = 'error'; // only return 'error' if NO rates returned

		$n = sizeof($body_array);
		for ($i=0; $i<$n; $i++) {
			$result = explode('%', $body_array[$i]);
			$errcode = substr($result[0], -1);
			switch ($errcode) {
				case 3:
					if (is_array($returnval)) $returnval[] = array($result[1] => $result[10]);
				break;
				case 4:
					if (is_array($returnval)) $returnval[] = array($result[1] => $result[10]);
				break;
				case 5:
					$errorret = $result[1];
				break;
				case 6:
					if (is_array($returnval)) $returnval[] = array($result[3] => $result[10]);
				break;
			}
		}
		if (empty($returnval)) $returnval = $errorret;

		return $returnval;
	}
    
	/**
	 * Contacts UPS and gets a quote for the specified weight and configuration settings.
	 *
	 * @author  Conor Kerr <zen-cart.advanced-shipper@dev.ceon.net>
	 * @access  public
	 * @param   float     $weight    The weight of the package to be shipped.
	 * @param   array     $min_max   Any minimum/maximum limits which should be applied to the final
	 *                               rate calculated.
	 * @return  none
	 */
	function quote($weight, $min_max)
	{
		
		$rate_info = [];

		// Customer shipping address
        $shippingAddress = $this->getShippingAddress();
		
		$prod = 'GNDRES';
		$this->_upsProduct($prod);
		
		$weight = ($weight < 0.1 ? 0.1 : $weight);
		
		$this->_upsOrigin($this->_config['source_postcode'], $this->_config['source_country']);
		
		$dest_country_code = $shippingAddress['country_code'];
		$us_zone_name = strtolower($shippingAddress['zone_name']);
		$this->_upsDest($shippingAddress['postcode'], $dest_country_code);
		$this->_upsRate($this->_config['pickup_method']);
		$this->_upsContainer($this->_config['packaging']);
		$this->_upsWeight($weight);
		$this->_upsRescom($this->_config['delivery_type']);
		$upsQuote = $this->_upsGetQuote();
		
		if (!is_array($upsQuote)) {
			$upsQuote = strtolower($upsQuote);
			if (strpos($upsQuote, 'unsupported country') !== false && $this->_config['source_country'] != 'US') {
				// Simply return false if the country is not supported, as this is not strictly an
				// error
				$this->_debug("Source/Destination country is not supported by UPS!\n\n", true);
				
				return false;
				
			} else if (strpos($upsQuote, 'unsupported country') !== false || strpos($upsQuote, 'service is unavailable to residential destinations')) {
				// Simply return false if the country is not supported, as this is not strictly an
				// error
				$this->_debug("Destination country is not supported by UPS!\n\n", true);
				
				return false;
				
			} else if (strpos($upsQuote, 'missing consigneepostalcode') !== false) {
				return array('error' => "Postcode error");
			} else {
				return array('error' => $upsQuote);
			}
		}
		
		$std_rcd = false;
		
		$qsize = sizeof($upsQuote);
		for ($i = 0; $i < $qsize; $i++) {
			$type = key($upsQuote[$i]);
			$rate = $upsQuote[$i][$type];

			if ($type == 'STD') {
				if ($std_rcd) {
					continue;
				} else {
					$std_rcd = true;
				}
			}
			
			// Check if this ups service is to be used by store
			if (array_search('ups_shipping_service_' . str_replace(' ' , '_', strtolower($type)), $this->_config['ups_services']) === false) {
				$this->_debug("UPS: {$type} UPS service not to be offered as it is not enabled in the UPS configuration for the region.\n\n", true);
				continue;
			}
			
			// Make sure rate is in correct numerical format (no commas etc.)
			$rate = preg_replace('/[^0-9\.]/', '', $rate);
			
			if ($min_max != false) {
				// Apply the limit(s) to the rate
				$rate_limited = advshipper::calcMinMaxValue($rate, $min_max['min'],
					$min_max['max']);
				
				if ($rate_limited != $rate) {
					$rate = $rate_limited;
				}
			}
			
			$rate_info[] = array(
				'rate' => $rate,
				'rate_components_info' => array(
					array(
						'value_band_total' => $rate,
						'individual_value' => null,
						'num_individual_values' => $weight,
						'additional_charge' => null,
						'calc_method' => "ups"
					)
				),
				'rate_extra_title' => MODULE_ADVANCED_SHIPPER_TEXT_UPS_TITLE_PREFIX . constant('MODULE_ADVANCED_SHIPPER_TEXT_UPS_SHIPPING_SERVICE_' . strtoupper($type)) . MODULE_ADVANCED_SHIPPER_TEXT_UPS_TITLE_PREFIX
			);
		}
		
		return $rate_info;
	}

	/**
	 * 
	 */
	public function tracking($order)
	{
		// Configuration
		$accessLicenseNumber = $this->_config['ups_access_license_number'];
		$userId = $this->_config['ups_username'];
		$password = $this->_config['ups_password'];
		$endpointurl = 'https://onlinetools.ups.com/ups.app/xml/Track';

		$trackDetails = [];

		try {
			
			// Create AccessRequest XMl
			$accessRequestXML = new \SimpleXMLElement ( "<AccessRequest></AccessRequest>" );
			$accessRequestXML->addChild ( "AccessLicenseNumber", $accessLicenseNumber );
			$accessRequestXML->addChild ( "UserId", $userId );
			$accessRequestXML->addChild ( "Password", $password );
			
			// Create TrackRequest XMl
			$trackRequestXML = new \SimpleXMLElement ( "<TrackRequest></TrackRequest>" );
			$request = $trackRequestXML->addChild ( 'Request' );
			$request->addChild ( "RequestAction", "Track" );
			$request->addChild ( "RequestOption", "activity" );
			
			$trackRequestXML->addChild ( "TrackingNumber", $order->tracking_number );
			
			$requestXML = $accessRequestXML->asXML () . $trackRequestXML->asXML ();
			
			$form = array (
				'http' => array (
					'method' => 'POST',
					'header' => 'Content-type: application/x-www-form-urlencoded',
					'content' => $requestXML
				) 
			);
			
			// get request
			$request = stream_context_create ( $form );
			$browser = fopen ( $endpointurl, 'rb', false, $request );
			if (! $browser) {
				throw new \Exception ( "Connection failed." );
			}
			
			// get response
			$response = stream_get_contents ( $browser );
			fclose ( $browser );
			
			if ($response == false) {
				throw new \Exception ( "Bad data." );
			} else {
				$xml = simplexml_load_string($response, "SimpleXMLElement", LIBXML_NOCDATA);
				$json = json_encode($xml);
				$trackDetails = json_decode($json, true);
			}
		} catch ( Exception $ex ) {
			return false;
		}
		
		return $trackDetails;
	}

}