<?php

namespace Advshipper;


define('CALC_ADVANCED_SHIPPER_USPS_EMI', 1);
define('CALC_ADVANCED_SHIPPER_USPS_PMI', 2);
define('CALC_ADVANCED_SHIPPER_USPS_GEG', 4);
define('CALC_ADVANCED_SHIPPER_USPS_GEG_D', 5);
define('CALC_ADVANCED_SHIPPER_USPS_GEG_NDR', 6);
define('CALC_ADVANCED_SHIPPER_USPS_GEG_NDNR', 7);
define('CALC_ADVANCED_SHIPPER_USPS_PMI_FRE', 8);
define('CALC_ADVANCED_SHIPPER_USPS_PMI_MFRB', 9);
define('CALC_ADVANCED_SHIPPER_USPS_EMI_FRE', 10);
define('CALC_ADVANCED_SHIPPER_USPS_PMI_LFRB', 11);
define('CALC_ADVANCED_SHIPPER_USPS_GEG_E', 12);
define('CALC_ADVANCED_SHIPPER_USPS_FCMI_L', 13);
define('CALC_ADVANCED_SHIPPER_USPS_FCMI_LE', 14);
define('CALC_ADVANCED_SHIPPER_USPS_FCMI_P', 15);
define('CALC_ADVANCED_SHIPPER_USPS_PMI_SFRB', 16);
define('CALC_ADVANCED_SHIPPER_USPS_EMI_LFRE', 17);
define('CALC_ADVANCED_SHIPPER_USPS_PMI_GCFRE', 18);
define('CALC_ADVANCED_SHIPPER_USPS_PMI_SFRE', 20);
define('CALC_ADVANCED_SHIPPER_USPS_PMI_LFRE', 22);
define('CALC_ADVANCED_SHIPPER_USPS_PMI_PFRE', 23);
define('CALC_ADVANCED_SHIPPER_USPS_PMI_DFRB', 24);
define('CALC_ADVANCED_SHIPPER_USPS_PMI_LVFRB', 25);
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_TITLE_PREFIX', ' ');
define('ADVSHIPPER_CALC_METHOD_USPS', 'usps');
/**
 * Titles for USPS shipping methods
 */
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_DOMESTIC_EXPRESS', 'Priority Mail Express');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_DOMESTIC_PRIORITY', 'Priority Mail');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_DOMESTIC_FIRST_CLASS', 'First-Class Mail');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_DOMESTIC_PARCEL', 'Parcel Post');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_DOMESTIC_MEDIA', 'Media Mail');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_DOMESTIC_BPM', 'Bound Printed Matter');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_DOMESTIC_LIBRARY', 'Library');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_GEG', 'Global Express Guaranteed (GXG)');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_GEG_D', 'Global Express Guaranteed Document');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_GEG_NDR', 'Global Express Guaranteed Non-Document Rectangular');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_GEG_NDNR', 'Global Express Guaranteed Non-Document Non-Rectangular');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_GEG_E', 'USPS GXG Envelopes');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_EMI', 'Express Mail International');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_EMI_FRE', 'Express Mail International Flat Rate Envelope');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_EMI_LFRE', 'Express Mail International Legal Flat Rate Envelope');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_PMI', 'Priority Mail International');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_PMI_SFRB', 'Priority Mail International Small Flat Rate Box');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_PMI_MFRB', 'Priority Mail International Medium Flat Rate Box');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_PMI_LFRB', 'Priority Mail International Large Flat Rate Box');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_PMI_DFRB', 'Priority Mail International DVD Flat Rate Box');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_PMI_LVFRB', 'Priority Mail International Large Video Flat Rate Box');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_PMI_FRE', 'Priority Mail International Flat Rate Envelope');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_PMI_SFRE', 'Priority Mail International Small Flat Rate Envelope');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_PMI_PFRE', 'Priority Mail International Padded Flat Rate Envelope');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_PMI_GCFRE', 'Priority Mail International Gift Card Flat Rate Envelope');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_PMI_LFRE', 'Priority Mail International Legal Flat Rate Envelope');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_FCMI_LE', 'First-Class Mail International Large Envelope');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_FCMI_P', 'First-Class Mail International Package');
define('MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_FCMI_L', 'First-Class Mail International Letter');

class AdvancedShipperUSPSCalculator extends \Advancedshipper
{
	// {{{ properties
	
	/**
	 * The configuration settings for this instance.
	 *
	 * @var     array
	 * @access  public
	 */
	var $_config = null;
	
	/**
	 * The total price of the items in package to be shipped.
	 *
	 * @var     float
	 * @access  protected
	 */
	var $_price = 0;
	
	// }}}
	
	
	// {{{ Class Constructor
	
	/**
	 * Create a new instance of the AdvancedShipperUSPSCalculator class
	 *
	 * @param   array     $ups_config   An associative array with the configuration settings for
	 *                                  this instance.
	 */
	function __construct($ups_config)
	{

    parent::__construct();

		$this->_config = $ups_config;
		
    $this->types = array('EXPRESS' => 'Express Mail',
        'FIRST CLASS' => 'First-Class Mail',
        'PRIORITY' => 'Priority Mail',
        'PARCEL' => 'Parcel Post',
        'MEDIA' => 'Media Mail',
        'BPM' => 'Bound Printed Matter',
        'LIBRARY' => 'Library'
    );

    $this->countries = $this->country_list();
	}
	
	// }}}
	
	
	// {{{ quote()

	/**
	 * Contacts USPS and gets a quote for the specified weight and configuration settings.
	 *
	 * @author  Conor Kerr <zen-cart.advanced-shipper@dev.CALC.net>
	 * @access  public
	 * @param   float     $weight    The weight of the package to be shipped.
	 * @param   float     $price     The total price of the items in package to be shipped.
	 * @param   array     $min_max   Any minimum/maximum limits which should be applied to the final
	 *                               rate calculated.
	 * @return  none
	 */
	function quote($weight, $price, $min_max)
	{

		$rate_info = false;
		
		$this->_price = $price;
		
		// USPS doesnt accept zero weight send 1 ounce (0.0625) minimum
		$weight = ($weight < 0.0625 ? 0.0625 : $weight);
		$shipping_pounds = floor ($weight);
		$shipping_ounces = (16 * ($weight - floor($weight)));
		
		// USPS currently cannot handle more than 5 digits on international
    $shipping_ounces = round($shipping_ounces, 2);
		
		// weight must be less than 35lbs and greater than 6 ounces or it is not machinable
		switch (true) {
			case ($shipping_pounds == 0 and $shipping_ounces < 6):
				// override admin choice too light
				$is_machinable = false;
				break;
			case ($weight > 35):
				// override admin choice too heavy
				$is_machinable = false;
				break;
			default:
				// admin choice on what to use
				$is_machinable = ($this->_config['machinable'] == 1 ? true : false);
    }
		
		$this->_setMachinable(($is_machinable ? 'True' : 'False'));
		$this->_setContainer('None');
		$this->_setSize('REGULAR');
		$this->_setFirstClassType('PARCEL');
		
		$this->_setWeight($shipping_pounds, $shipping_ounces);
    $result = $this->_getQuote();
    $uspsQuote = $result['rates'];
    $transittime = $result['transittime'];
		
		if (is_array($uspsQuote)) {
			if (isset($uspsQuote['error'])) {
				if (strpos($uspsQuote['error'], 'Missing value for ZipDestination') !== false) {
					return array(
						'error' => "Please enter your Post/Zip Code."
					);
				}
				
				return array(
					'error' => "#1212 " . $uspsQuote['error']
				);
			} else {
				$methods = array();
				$size = sizeof($uspsQuote);
				
				for ($i = 0; $i < $size; $i++) {
          
          $type = key($uspsQuote[$i]);
          $rate = $uspsQuote[$i][$type];

					// BOF: USPS USPS
					switch ($type) {
						case 'EXPRESS':
						case 'FIRST CLASS':
						case 'PRIORITY':
						case 'PARCEL':
						case 'MEDIA':
						case 'BPM':
						case 'LIBRARY':
							$title = constant('MODULE_ADVANCED_SHIPPER_TEXT_USPS_DOMESTIC_' . str_replace(' ', '_', strtoupper($type)));
							break;
						case CALC_ADVANCED_SHIPPER_USPS_GEG:
							$title = MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_GEG;
							break;
						case CALC_ADVANCED_SHIPPER_USPS_GEG_D:
							$title = MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_GEG_D;
							break;
						case CALC_ADVANCED_SHIPPER_USPS_GEG_NDR:
							$title = MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_GEG_NDR;
							break;
						case CALC_ADVANCED_SHIPPER_USPS_GEG_NDNR:
							$title = MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_GEG_NDNR;
							break;
						case CALC_ADVANCED_SHIPPER_USPS_GEG_E:
							$title = MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_GEG_E;
							break;
						case CALC_ADVANCED_SHIPPER_USPS_EMI:
							$title = MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_EMI;
							break;
						case CALC_ADVANCED_SHIPPER_USPS_EMI_FRE:
							$title = MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_EMI_FRE;
							break;
						case CALC_ADVANCED_SHIPPER_USPS_EMI_LFRE:
							$title = MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_EMI_LFRE;
							break;
						case CALC_ADVANCED_SHIPPER_USPS_PMI:
							$title = MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_PMI;
							break;
						case CALC_ADVANCED_SHIPPER_USPS_PMI_SFRB:
							$title = MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_PMI_SFRB;
							break;
						case CALC_ADVANCED_SHIPPER_USPS_PMI_MFRB:
							$title = MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_PMI_MFRB;
							break;
						case CALC_ADVANCED_SHIPPER_USPS_PMI_LFRB:
							$title = MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_PMI_LFRB;
							break;
						case CALC_ADVANCED_SHIPPER_USPS_PMI_DFRB:
							$title = MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_PMI_DFRB;
							break;
						case CALC_ADVANCED_SHIPPER_USPS_PMI_LVFRB:
							$title = MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_PMI_LVFRB;
							break;
						case CALC_ADVANCED_SHIPPER_USPS_PMI_FRE:
							$title = MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_PMI_FRE;
							break;
						case CALC_ADVANCED_SHIPPER_USPS_PMI_SFRE:
							$title = MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_PMI_SFRE;
							break;
						case CALC_ADVANCED_SHIPPER_USPS_PMI_PFRE:
							$title = MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_PMI_PFRE;
							break;
						case CALC_ADVANCED_SHIPPER_USPS_PMI_GCFRE:
							$title = MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_PMI_GCFRE;
							break;
						case CALC_ADVANCED_SHIPPER_USPS_PMI_LFRE:
							$title = MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_PMI_LFRE;
							break;
						case CALC_ADVANCED_SHIPPER_USPS_FCMI_LE:
							$title = MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_FCMI_LE;
							break;
						case CALC_ADVANCED_SHIPPER_USPS_FCMI_P:
							$title = MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_FCMI_P;
							break;
						case CALC_ADVANCED_SHIPPER_USPS_FCMI_L:
							$title = MODULE_ADVANCED_SHIPPER_TEXT_USPS_INTERNATIONAL_FCMI_L;
							break;
						default:
							$title = $type;
							break;
					}
					
					
					if (preg_match('/Retail/', $title)) {
					  $title = " First-Class Mail (Estimated 3 to 5 business days, not guaranteed)";
					} else{
            $title = MODULE_ADVANCED_SHIPPER_TEXT_USPS_TITLE_PREFIX . $title;
					}
					if ($this->_config['display_transit_time'] == 1 && isset($transittime[$type])) {
						$title .= $transittime[$type];
					}
					
					if (!is_array($rate_info)) {
						$rate_info = array();
					}
					
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
								'calc_method' => "usps"
								)
							),
						'rate_extra_title' => $title
						);
				}
			}
		} else {
			// No quotes for current address
    }
		
		return $rate_info;
	}
	
	// }}}
  
  /**
   * Set USPS service mode
   *
   * @param string $service
   */
  function _setService($service) {
    $this->service = $service;
  }
  /**
   * Set USPS weight for quotation collection
   *
   * @param integer $pounds
   * @param integer $ounces
   */
  function _setWeight($pounds, $ounces=0) {
    $this->pounds = $pounds;
    $this->ounces = $ounces;
  }
  /**
   * Set USPS container type
   *
   * @param string $container
   */
  function _setContainer($container) {
    $this->container = $container;
  }
  
  /**
   * Set USPS First Class type
   *
   * @param string $fctype
   */
  function _setFirstClassType($fctype) {
    $this->fctype = $fctype;
  }
  
  /**
   * Set USPS package size
   *
   * @param integer $size
   */
  function _setSize($size) {
    $this->size = $size;
  }
  /**
   * Set USPS machinable flag
   *
   * @param boolean $machinable
   */
  function _setMachinable($machinable) {
    $this->machinable = $machinable;
  }
  /**
   * Get actual quote from USPS
   *
   * @return array of results or boolean false if no results
   */
  function _getQuote() {

    $transit = ($this->_config['display_transit_time'] == 1);

    $shippingAddress = $this->getShippingAddress();

    // Transit request
    $transreq = [];
	
    // Check if the delivery address is a US "territory"
    //$dest_country_id = $order->delivery['country']['id'];
    $dest_country_code = $shippingAddress['country_code'];
	

    if ($dest_country_code === $this->_config['source_country']) {
      $request  = '<RateV4Request USERID="' . $this->_config['usps_userid'] . '">';
      $services_count = 0;

      if (isset($this->service)) {
        $this->types = array($this->service => $this->types[$this->service]);
      }

      $dest_zip = str_replace(' ', '', $shippingAddress['postcode']);
      if ($dest_country_code == 'US') $dest_zip = substr($dest_zip, 0, 5);

      reset($this->types);

      // BOF: UPS USPS
      //$allowed_types = explode(", ", MODULE_SHIPPING_USPS_TYPES);
      foreach($this->types as $key => $value) {

        // BOF: UPS USPS
        //if ( !in_array($key, $allowed_types) ) continue;
        if (array_search('usps_domestic_' . str_replace(' ' , '_', strtolower($key)), $this->_config['domestic_services']) === false) {
          $this->_debug("USPS: {$key} USPS service not to be offered as it is not enabled in the USPS configuration for the region.\n\n", true);
          continue;
        }

        //For Options list, go to page 6 of document: http://www.usps.com/webtools/_pdf/Rate-Calculators-v1-2.pdf
        $serv = $key;

        //FIRST CLASS MAIL OPTIONS
        if ($key == 'FIRST CLASS') {
          $this->FirstClassMailType = 'PARCEL';
        } else {
          $this->FirstClassMailType = '';
        }

        //PRIORITY MAIL OPTIONS
        if ($key === 'PRIORITY') {
          $this->container = ''; // Blank, Flate Rate Envelope, or Flat Rate Box
        }
        //EXPRESS MAIL OPTIONS
        if ($key === 'EXPRESS') {
          $this->container = '';  // Blank, or Flate Rate Envelope
          $serv = 'PRIORITY EXPRESS';
        }
        //PARCEL POST OPTIONS
        if ($key === 'PARCEL') {
          $this->container = 'Variable';
          $this->machinable = 'true';
          $serv = 'STANDARD POST';
        }

        $request .= '<Package ID="' . $services_count . '">' .
          '<Service>' . $serv . '</Service>' .
          '<FirstClassMailType>' . $this->FirstClassMailType . '</FirstClassMailType>' .
          '<ZipOrigination>' . $this->_config['source_postcode'] . '</ZipOrigination>' .
          '<ZipDestination>' . $dest_zip . '</ZipDestination>' .
          '<Pounds>' . $this->pounds . '</Pounds>' .
          '<Ounces>' . $this->ounces . '</Ounces>' .
          '<Container>' . $this->container . '</Container>' .
          '<Size>' . $this->size . '</Size>' .
          '<Machinable>' . $this->machinable . '</Machinable>' .
        '</Package>';
        // BOF: UPS USPS
        if($transit) {
          $transitreq  = 'USERID="' . $this->_config['usps_userid'] . '">' .
          '<OriginZip>' . $this->_config['source_postcode'] . '</OriginZip>' .
          '<DestinationZip>' . $dest_zip . '</DestinationZip>';
          
          switch ($key) {
            case 'EXPRESS':  
              $transreq[$key] = 'API=ExpressMail&XML=' . urlencode( '<ExpressMailRequest ' . $transitreq . '</ExpressMailRequest>');
            break;
            case 'PRIORITY': 
              $transreq[$key] = 'API=PriorityMail&XML=' . urlencode( '<PriorityMailRequest ' . $transitreq . '</PriorityMailRequest>');
            break;
            case 'PARCEL':   
              $transreq[$key] = 'API=StandardB&XML=' . urlencode( '<StandardBRequest ' . $transitreq . '</StandardBRequest>');
            break;
            default:
              $transreq[$key] = '';
            break;
          }
        }
        // EOF: UPS USPS
        $services_count++;
      }
      $request .= '</RateV4Request>';

      $request = 'API=RateV4&XML=' . urlencode($request);

    } else {

      $request  = '<IntlRateV2Request USERID="' . $this->_config['usps_userid'] . '">' .
      '<Revision>2</Revision>' .
      '<Package ID="0">' .
        '<Pounds>' . $this->pounds . '</Pounds>' .
        '<Ounces>' . round($this->ounces, 2) . '</Ounces>' .
        '<MailType>Package</MailType>' .
        '<ValueOfContents>' . $this->_price . '</ValueOfContents>' .
        '<Country>' . $this->countries[$shippingAddress['country_code']] . '</Country>' .
        '<Container>RECTANGULAR</Container>' .
        '<Size>REGULAR</Size>' .
        '<Width></Width>' .
        '<Length></Length>' .
        '<Height></Height>' .
        '<Girth></Girth>' .
        '<OriginZip>' . $this->_config['source_postcode'] . '</OriginZip>' .
	    '</Package>' .
      '</IntlRateV2Request>';

      $request = 'API=IntlRateV2&XML=' . urlencode($request);
    }
    
    switch ($this->_config['environment']) {
      case '1':
        $usps_server = 'production.shippingapis.com';
        $api_dll = 'shippingapi.dll';
      break;
      case '2':
      default:
        $usps_server = 'testing.shippingapis.com';
        $api_dll = 'ShippingAPI.dll';
      break;
    }
	
    $this->_debug("USPS Data being sent to USPS: " . str_replace('&amp;', "<br />\n", htmlentities(urldecode($request))), true);

    $http = new \httpClient();
    $http->timeout = 5;
    if ($http->Connect($usps_server, 80)) {
      $http->addHeader('Host', $usps_server);
      $http->addHeader('Connection', 'Close');
      if ($http->Get('/' . $api_dll . '?' . $request)) $body = $http->getBody();

      $this->_debug("USPS Results from contacting USPS:" . nl2br(htmlentities($body)), true);
      
      // BOF: UPS USPS
      $transresp = [];
      if ($transit && is_array($transreq) && ($dest_country_code === $this->_config['source_country'])) {
        foreach($transreq as $key => $value) {
          if ($http->Get('/' . $api_dll . '?' . $value)) $transresp[$key] = $http->getBody();
        }
      }
      // EOF: UPS USPS

      $http->Disconnect();
    } else {
      $this->_debug("USPS Status message: " . $http->getStatusMessage(), true);
      return -1;
    }

    $response = array();
    while (true) {
      if ($start = strpos($body, '<Package ID=')) {
        $body = substr($body, $start);
        $end = strpos($body, '</Package>');
        $response[] = substr($body, 0, $end+10);
        $body = substr($body, $end+9);
      } else {
        break;
      }
    }
    
    $rates = $transittime = array();
    if ($dest_country_code === $this->_config['source_country']) {
      if (sizeof($response) == 0) {
		    $response = array($body);
	    }
      if (sizeof($response) == '1') {
        if (preg_match('/<Error>/i', $response[0])) {
          $number = preg_match('/<Number>(.*)<\/Number>/msi', $response[0], $regs);
          $number = $regs[1];
          $description = preg_match('/<Description>(.*)<\/Description>/msi', $response[0], $regs);
          $description = $regs[1];

          return array('error' => $number . ' - ' . $description);
        }
      }
      
      $n = sizeof($response);
      for ($i=0; $i<$n; $i++) {
        if (strpos($response[$i], '<Rate>')) {
          $service = preg_match('/<MailService>(.*)<\/MailService>/msi', $response[$i], $regs);
          $service = $regs[1];
          if (preg_match('/Express/i', $service)) $service = 'EXPRESS';
          if (preg_match('/Priority/i', $service)) $service = 'PRIORITY';
          if (preg_match('/First-Class Mail/i', $service)) $service = 'FIRST CLASS';
          if (preg_match('/Standard Post/i', $service)) $service = 'PARCEL';
          if (preg_match('/Media/i', $service)) $service = 'MEDIA';
          if (preg_match('/Bound Printed/i', $service)) $service = 'BPM';
          if (preg_match('/Library/i', $service)) $service = 'LIBRARY';
          $postage = preg_match('/<Rate>(.*)<\/Rate>/msi', $response[$i], $regs);
          $postage = $regs[1];

          $rates[] = array($service => $postage);
          
          /* var_dump($rates);
          var_dump($transresp);
          die('#1'); */

          // BOF: UPS USPS
          $time = '';
          if ($transit) {
            switch ($service) {
              case 'EXPRESS':
                if(isset($transresp[$service])) {
                  $time = preg_match('/<MonFriCommitment>(.*)<\/MonFriCommitment>/msi', $transresp[$service], $tregs);
                  if(!empty($tregs)) {
                    $time = $tregs[1];
                    if ($time == '' || $time == 'No Data') {
                      $time = '1 - 2 business days guaranteed';
                    } else {
                      $time = 'Tomorrow by ' . $time . ' guaranteed';
                    }
                  }
                }
              break;
              case 'PRIORITY':
                if(isset($transresp[$service])) {
                  $time = preg_match('/<Days>(.*)<\/Days>/msi', $transresp[$service], $tregs);
                  $time = 'Estimated ' . $tregs[1];
                  if ($time == '' || $time == 'No Data') {
                    $time = 'Estimated 2 - 3 business days, not guaranteed';
                  } elseif ($time == '1') {
                    $time .= ' business day, not guaranteed';
                  } else {
                    $time .= ' business days, not guaranteed';
                  }
                }
              break;
              case 'PARCEL':
                if(isset($transresp[$service])) {
                  $time = preg_match('/<Days>(.*)<\/Days>/msi', $transresp[$service], $tregs);
                  $time = $tregs[1];
                  if ($time == '' || $time == 'No Data') {
                    $time = 'Estimated 4 - 7 business days';
                  } elseif ($time == '1') {
                    $time .= ' business day, not guaranteed';
                  } else {
                    $time .= ' business days, not guaranteed';
                  }
                }
              break;
              case 'FIRST CLASS':
                $time = 'Estimated 2 - 5 business days, not guaranteed';
              break;
              default:
                $time = '';
              break;
            }
            if ($time != '') $transittime[$service] = ' (' . $time . ')';
          }
          // EOF: UPS USPS
        }
      }
    } else {
      if (preg_match('/<Error>/i', $response[0])) {
        $number = preg_match('/<Number>(.*)<\/Number>/msi', $response[0], $regs);
        $number = $regs[1];
        $description = preg_match('/<Description>(.*)<\/Description>/msi', $response[0], $regs);
        $description = $regs[1];

        return array('error' => $number . ' - ' . $description);
      } else {
        $body = $response[0];
        $services = array();
        while (true) {
          if ($start = strpos($body, '<Service ID=')) {
            $body = substr($body, $start);
            $end = strpos($body, '</Service>');
            $services[] = substr($body, 0, $end+10);
            $body = substr($body, $end+9);
          } else {
            break;
          }
        }

        // BOF: UPS USPS
        //$allowed_types = array();
        //foreach( explode(", ", MODULE_SHIPPING_USPS_TYPES_INTL) as $value ) $allowed_types[$value] = $this->intl_types[$value];
        // EOF: UPS USPS

        $size = sizeof($services);
        for ($i=0, $n=$size; $i<$n; $i++) {
          if (strpos($services[$i], '<Postage>')) {
            //$service = preg_match('/<SvcDescription>(.*)<\/SvcDescription>/msi', $services[$i], $regs);
            //$service = $regs[1];
            preg_match('/<Service ID="([^"]+)">/Ui', $services[$i], $regs);
			      $service_id = $regs[1];
			      $postage = preg_match('/<Postage>(.*)<\/Postage>/i', $services[$i], $regs);
            $postage = $regs[1];
            // BOF: UPS USPS
            $time = preg_match('/<SvcCommitments>(.*)<\/SvcCommitments>/msi', $services[$i], $tregs);
            $time = $tregs[1];
            $time = preg_replace('/Weeks$/', "weeks", $time);
            $time = preg_replace('/Days$/', "days", $time);
            $time = preg_replace('/Day$/', "day", $time);
			
            //if( !in_array($service, $allowed_types) ) continue;
           
            // Convert the service ID to a database field so it can determined if the service should
            // have a quote shown to the customer
            $column_name = null;
            
            switch ($service_id) {
              case CALC_ADVANCED_SHIPPER_USPS_GEG:
                $column_name = 'GEG';
                break;
              case CALC_ADVANCED_SHIPPER_USPS_GEG_D:
                $column_name = 'GEG_D';
                break;
              case CALC_ADVANCED_SHIPPER_USPS_GEG_NDR:
                $column_name = 'GEG_NDR';
                break;
              case CALC_ADVANCED_SHIPPER_USPS_GEG_NDNR:
                $column_name = 'GEG_NDNR';
                break;
              case CALC_ADVANCED_SHIPPER_USPS_GEG_E:
                $column_name = 'GEG_E';
                break;
              case CALC_ADVANCED_SHIPPER_USPS_EMI:
                $column_name = 'EMI';
                break;
              case CALC_ADVANCED_SHIPPER_USPS_EMI_FRE:
                $column_name = 'EMI_FRE';
                break;
              case CALC_ADVANCED_SHIPPER_USPS_EMI_LFRE:
                $column_name = 'EMI_LFRE';
                break;
              case CALC_ADVANCED_SHIPPER_USPS_PMI:
                $column_name = 'PMI';
                break;
              case CALC_ADVANCED_SHIPPER_USPS_PMI_SFRB:
                $column_name = 'PMI_SFRB';
                break;
              case CALC_ADVANCED_SHIPPER_USPS_PMI_MFRB:
                $column_name = 'PMI_MFRB';
                break;
              case CALC_ADVANCED_SHIPPER_USPS_PMI_LFRB:
                $column_name = 'PMI_LFRB';
                break;
              case CALC_ADVANCED_SHIPPER_USPS_PMI_DFRB:
                $column_name = 'PMI_DFRB';
                break;
              case CALC_ADVANCED_SHIPPER_USPS_PMI_LVFRB:
                $column_name = 'PMI_LVFRB';
                break;
              case CALC_ADVANCED_SHIPPER_USPS_PMI_FRE:
                $column_name = 'PMI_FRE';
                break;
              case CALC_ADVANCED_SHIPPER_USPS_PMI_SFRE:
                $column_name = 'PMI_SFRE';
                break;
              case CALC_ADVANCED_SHIPPER_USPS_PMI_PFRE:
                $column_name = 'PMI_PFRE';
                break;
              case CALC_ADVANCED_SHIPPER_USPS_PMI_GCFRE:
                $column_name = 'PMI_GCFRE';
                break;
              case CALC_ADVANCED_SHIPPER_USPS_PMI_LFRE:
                $column_name = 'PMI_LFRE';
                break;
              case CALC_ADVANCED_SHIPPER_USPS_FCMI_LE:
                $column_name = 'FCMI_LE';
                break;
              case CALC_ADVANCED_SHIPPER_USPS_FCMI_P:
                $column_name = 'FCMI_P';
                break;
              case CALC_ADVANCED_SHIPPER_USPS_FCMI_L:
                $column_name = 'FCMI_L';
                break;
            }
            
            if (is_null($column_name)) {
              $this->_debug('USPS service ID ' . $service_id . ' could not be identified as' . " the service ID is unknown.\n\n", true);
              continue;
              
            } else if (array_search('usps_international_' . str_replace(' ' , '_', strtolower($column_name)), $this->_config['international_services']) === false) {
              $this->_debug('USPS service not offered as it is not enabled in the USPS' . " configuration for the region.\n\n", true);
              continue;
            }
            
            //if ($_SESSION['cart']->total > 400 && strstr($services[$i], 'Priority Mail International Flat Rate Envelope')) continue; // skip value > $400 Priority Mail International Flat Rate Envelope
            // Skip value > $400 Priority Mail International Flat Rate Envelope
            if ($this->_price > 400 && $service_id == CALC_ADVANCED_SHIPPER_USPS_PMI_FRE) {
              continue;
            }
			
            // EOF: UPS USPS
            //if (isset($this->service) && ($service != $this->service) ) {
            //  continue;
            //}

            $rates[] = array($service_id => $postage);
            // BOF: UPS USPS
            if ($time != '') $transittime[$service_id] = ' (' . $time . ')';
            // EOF: UPS USPS
          }
        }
      }
    }

    return [
      'rates' => (sizeof($rates) > 0) ? $rates : false, 
      'transittime' => $transittime
    ];
  }

  /**
   * 
   */
  public function tracking($order)
  {

    $trackDetails = [];

    $request  = '<TrackRequest USERID="' . $this->_config['usps_userid'] . '">' . 
                  '<TrackID ID="' . $order->tracking_number . '"></TrackID>' . 
                '</TrackRequest>';
    $request = 'API=TrackV2&XML=' . urlencode($request);

    $usps_server = 'production.shippingapis.com';
    $api_dll = 'shippingapi.dll';
    $http = new \httpClient();
    $http->timeout = 5;
    if ($http->Connect($usps_server, 80)) {
      $http->addHeader('Host', $usps_server);
      $http->addHeader('Connection', 'Close');
      if ($http->Get('/' . $api_dll . '?' . $request)) {
        $xml = simplexml_load_string($http->getBody(), "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $trackDetails = json_decode($json, true);
      }
    }

    return $trackDetails;
  }

  /**
   * USPS Country Code List
   * This list is used to compare the 2-letter ISO code against the order country ISO code, and provide the proper/expected
   * spelling of the country name to USPS in order to obtain a rate quote
   *
   * @return array
   */
  function country_list() {
    $list = array(
    'AF' => 'Afghanistan',
    'AL' => 'Albania',
    'AX' => 'Aland Island (Finland)',
    'DZ' => 'Algeria',
    'AD' => 'Andorra',
    'AO' => 'Angola',
    'AI' => 'Anguilla',
    'AG' => 'Antigua and Barbuda',
    'AR' => 'Argentina',
    'AM' => 'Armenia',
    'AW' => 'Aruba',
    'AU' => 'Australia',
    'AT' => 'Austria',
    'AZ' => 'Azerbaijan',
    'BS' => 'Bahamas',
    'BH' => 'Bahrain',
    'BD' => 'Bangladesh',
    'BB' => 'Barbados',
    'BY' => 'Belarus',
    'BE' => 'Belgium',
    'BZ' => 'Belize',
    'BJ' => 'Benin',
    'BM' => 'Bermuda',
    'BT' => 'Bhutan',
    'BO' => 'Bolivia',
    'BA' => 'Bosnia-Herzegowina',
    'BW' => 'Botswana',
    'BR' => 'Brazil',
    'VG' => 'British Virgin Islands',
    'BN' => 'Brunei Darussalam',
    'BG' => 'Bulgaria',
    'BF' => 'Burkina Faso',
    'MM' => 'Burma',
    'BI' => 'Burundi',
    'KH' => 'Cambodia',
    'CM' => 'Cameroon',
    'CA' => 'Canada',
    'CV' => 'Cape Verde',
    'KY' => 'Cayman Islands',
    'CF' => 'Central African Republic',
    'TD' => 'Chad',
    'CL' => 'Chile',
    'CN' => 'China',
    'CX' => 'Christmas Island (Australia)',
    'CC' => 'Cocos Island (Australia)',
    'CO' => 'Colombia',
    'KM' => 'Comoros',
    'CG' => 'Congo, Republic of the',
    'CD' => 'Congo, Democratic Republic of the',
    'CK' => 'Cook Islands (New Zealand)',
    'CR' => 'Costa Rica',
    'CI' => 'Cote d Ivoire (Ivory Coast)',
    'HR' => 'Croatia',
    'CU' => 'Cuba',
    'CY' => 'Cyprus',
    'CZ' => 'Czech Republic',
    'DK' => 'Denmark',
    'DJ' => 'Djibouti',
    'DM' => 'Dominica',
    'DO' => 'Dominican Republic',
    'EC' => 'Ecuador',
    'EG' => 'Egypt',
    'SV' => 'El Salvador',
    'GQ' => 'Equatorial Guinea',
    'ER' => 'Eritrea',
    'EE' => 'Estonia',
    'ET' => 'Ethiopia',
    'FK' => 'Falkland Islands',
    'FO' => 'Faroe Islands',
    'FJ' => 'Fiji',
    'FI' => 'Finland',
    'FR' => 'France',
    'GF' => 'French Guiana',
    'PF' => 'French Polynesia',
    'GA' => 'Gabon',
    'GM' => 'Gambia',
    'GE' => 'Georgia, Republic of',
    'DE' => 'Germany',
    'GH' => 'Ghana',
    'GI' => 'Gibraltar',
    'GB' => 'Great Britain and Northern Ireland',
    'GR' => 'Greece',
    'GL' => 'Greenland',
    'GD' => 'Grenada',
    'GP' => 'Guadeloupe',
    'GT' => 'Guatemala',
    'GN' => 'Guinea',
    'GW' => 'Guinea-Bissau',
    'GY' => 'Guyana',
    'HT' => 'Haiti',
    'HN' => 'Honduras',
    'HK' => 'Hong Kong',
    'HU' => 'Hungary',
    'IS' => 'Iceland',
    'IN' => 'India',
    'ID' => 'Indonesia',
    'IR' => 'Iran',
    'IQ' => 'Iraq',
    'IE' => 'Ireland',
    'IL' => 'Israel',
    'IT' => 'Italy',
    'JM' => 'Jamaica',
    'JP' => 'Japan',
    'JO' => 'Jordan',
    'KZ' => 'Kazakhstan',
    'KE' => 'Kenya',
    'KI' => 'Kiribati',
    'KW' => 'Kuwait',
    'KG' => 'Kyrgyzstan',
    'LA' => 'Laos',
    'LV' => 'Latvia',
    'LB' => 'Lebanon',
    'LS' => 'Lesotho',
    'LR' => 'Liberia',
    'LY' => 'Libya',
    'LI' => 'Liechtenstein',
    'LT' => 'Lithuania',
    'LU' => 'Luxembourg',
    'MO' => 'Macao',
    'MK' => 'Macedonia, Republic of',
    'MG' => 'Madagascar',
    'MW' => 'Malawi',
    'MY' => 'Malaysia',
    'MV' => 'Maldives',
    'ML' => 'Mali',
    'MT' => 'Malta',
    'MQ' => 'Martinique',
    'MR' => 'Mauritania',
    'MU' => 'Mauritius',
    'YT' => 'Mayotte (France)',
    'MX' => 'Mexico',
    'FM' => 'Micronesia, Federated States of',
    'MD' => 'Moldova',
    'MC' => 'Monaco (France)',
    'MN' => 'Mongolia',
    'MS' => 'Montserrat',
    'MA' => 'Morocco',
    'MZ' => 'Mozambique',
    'NA' => 'Namibia',
    'NR' => 'Nauru',
    'NP' => 'Nepal',
    'NL' => 'Netherlands',
    'AN' => 'Netherlands Antilles',
    'NC' => 'New Caledonia',
    'NZ' => 'New Zealand',
    'NI' => 'Nicaragua',
    'NE' => 'Niger',
    'NG' => 'Nigeria',
    'KP' => 'North Korea (Korea, Democratic People\'s Republic of)',
    'NO' => 'Norway',
    'OM' => 'Oman',
    'PK' => 'Pakistan',
    'PA' => 'Panama',
    'PG' => 'Papua New Guinea',
    'PY' => 'Paraguay',
    'PE' => 'Peru',
    'PH' => 'Philippines',
    'PN' => 'Pitcairn Island',
    'PL' => 'Poland',
    'PT' => 'Portugal',
    'QA' => 'Qatar',
    'RE' => 'Reunion',
    'RO' => 'Romania',
    'RU' => 'Russia',
    'RW' => 'Rwanda',
    'SH' => 'Saint Helena',
    'KN' => 'Saint Kitts (St. Christopher and Nevis)',
    'LC' => 'Saint Lucia',
    'PM' => 'Saint Pierre and Miquelon',
    'VC' => 'Saint Vincent and the Grenadines',
    'SM' => 'San Marino',
    'ST' => 'Sao Tome and Principe',
    'SA' => 'Saudi Arabia',
    'SN' => 'Senegal',
    'RS' => 'Serbia',
    'SC' => 'Seychelles',
    'SL' => 'Sierra Leone',
    'SG' => 'Singapore',
    'SK' => 'Slovak Republic',
    'SI' => 'Slovenia',
    'SB' => 'Solomon Islands',
    'SO' => 'Somalia',
    'ZA' => 'South Africa',
    'GS' => 'South Georgia (Falkland Islands)',
    'KR' => 'South Korea (Korea, Republic of)',
    'ES' => 'Spain',
    'LK' => 'Sri Lanka',
    'SD' => 'Sudan',
    'SR' => 'Suriname',
    'SZ' => 'Swaziland',
    'SE' => 'Sweden',
    'CH' => 'Switzerland',
    'SY' => 'Syrian Arab Republic',
    'TW' => 'Taiwan',
    'TJ' => 'Tajikistan',
    'TZ' => 'Tanzania',
    'TH' => 'Thailand',
    'TL' => 'East Timor (Indonesia)',
    'TG' => 'Togo',
    'TK' => 'Tokelau (Union) Group (Western Samoa)',
    'TO' => 'Tonga',
    'TT' => 'Trinidad and Tobago',
    'TN' => 'Tunisia',
    'TR' => 'Turkey',
    'TM' => 'Turkmenistan',
    'TC' => 'Turks and Caicos Islands',
    'TV' => 'Tuvalu',
    'UG' => 'Uganda',
    'UA' => 'Ukraine',
    'AE' => 'United Arab Emirates',
    'UY' => 'Uruguay',
    'UZ' => 'Uzbekistan',
    'VU' => 'Vanuatu',
    'VA' => 'Vatican City',
    'VE' => 'Venezuela',
    'VN' => 'Vietnam',
    'WF' => 'Wallis and Futuna Islands',
    'WS' => 'Western Samoa',
    'YE' => 'Yemen',
    'ZM' => 'Zambia',
    'ZW' => 'Zimbabwe'
    );

    return $list;
  }
}

// }}}
