<?php

//use App\Helpers\Deepplusplus\Setting\Helpers;

/**
 * Class Rate.
 *
 */
class Canadapost
{
    
    public $code, $name, $description, 
            $order, $fields, $status;
    
    /**
     * Constructor
     */
    public function __construct() {

        $this->order = session('order');
        
        $this->code  = 'CanadaPost';
        $this->name = 'Canada Post';
        $this->description = 'Canada Post Corporation, trading as Canada Post, is a Crown corporation which functions as the primary postal operator in Canada. Originally known as Royal Mail Canada, rebranding was done to the "Canada Post" name in the late 1960s, even though it had not yet been separated from the government.';
    }

    /**
     * Check status
     */
    public function status($isBackend = false) {
        
        $this->status = \Deepplusplus\Setting\Helpers::config('module_shipping_canadapost_active') == 1 ? true : false;
        if($isBackend === true) {
            return $this->status;
        }

        // Check shipping zone if exists
        if ($this->status === true && \Deepplusplus\Setting\Helpers::config('module_shipping_canadapost_shipping_zone')) {
            
            $isFlag = false;

            $shippingAddress = \DB::table('addresses')->where('id', $this->order['profile']['default_shipping_address_id'])->first();
            
            $modulezone = \DB::table('module_zones')
                                ->select('module_zone_regions.country_code', 'module_zone_regions.zone_code')
                                ->join('module_zone_regions', 'module_zones.id', '=', 'module_zone_regions.module_zone_id')
                                ->where([
                                    'module_zones.id' => (int)\Deepplusplus\Setting\Helpers::config('module_shipping_canadapost_shipping_zone'),
                                    'module_zone_regions.country_code' => $shippingAddress->country_code
                                ])->get();

            if(!$modulezone->isEmpty()) {
                foreach($modulezone as $region) {
                    if(empty($region->zone_code) || ($region->zone_code == $shippingAddress->zone_code)) {
                        $isFlag = true;
                    }
                }
            }
            
            if($isFlag === false) {
                $this->status = false;
            }
            
        }

        return $this->status;
    }

    /**
     * Get cart total weight
     * @return $totalWeight
     */
    public function orderTotalWeight() {
        
        $totalWeight = 0;

        $cartItems = $this->order['items'];
        foreach($cartItems as $item) {
            $totalWeight += $item['weight']*$item['qty'];
        }
        
        return $totalWeight;
    }

    /**
     * Get number of items
     * @return $numberOfItems
     */
    public function orderNumberOfItems() {
        $numberOfItems = 0;
        $cartItems = $this->order['items'];
        foreach($cartItems as $item) {
            $numberOfItems += $item['qty'];
        }
        
        return $numberOfItems;
    }

    /**
     * Get shipping number boxes when process the shipping
     */
    public function shippingNumBox($weight)
    {
        $numBoxes = 1;
        if ($weight > \Deepplusplus\Setting\Helpers::config('shipping_max_weight')) {
            $boxes = round(($weight/\Deepplusplus\Setting\Helpers::config('shipping_max_weight')), 2);
            $numBoxes = ceil($boxes);
            $weight = $weight/$numBoxes;
        }

        return $numBoxes;
    }

    /**
     * Get shipping address
     */
    public function getShippingAddress() {
        $key = array_search($this->order['profile']['default_shipping_address_id'], array_column($this->order['profile']['addresses'], 'id'));
        return $shippingAddress = $this->order['profile']['addresses'][$key];
    }

    /**
     * Calculate tax
     * @param Array $shippingAddress
     * @return Float taxRatePercentage
     */
    public function calculateTax($shippingAddress) {
        $taxRatePercentage = 0;
        if(\Deepplusplus\Setting\Helpers::config('module_shipping_canadapost_tax_class') > 0) {
            $taxRates = \DB::table('tax_rates')->select('module_zone_id')->where('tax_class_id', \DB::table('tax_classes')->where('id', \Deepplusplus\Setting\Helpers::config('module_shipping_canadapost_tax_class'))->pluck('id')->first())->get();
            foreach($taxRates as $rate) {
                $moduleRegions = \DB::table('module_zone_regions')->where('module_zone_id', $rate->module_zone_id)->get();
                if(!$moduleRegions->isEmpty()) {
                    foreach($moduleRegions as $region) {
                        if(($region->country_code === $shippingAddress['country_code']) && ($region->zone_code === $shippingAddress['zone_code'])) {
                            $taxRatePercentage = $rate->rate;
                        }
                    }
                }
            }
        }
        return $taxRatePercentage;
    }

    /**
     * Get shipping quote
     * @return $numberOfItems
     */
    public function getQuote() {

        $services = [];

        $wsdl = "/CanadaPost/rating.wsdl";
        ini_set("soap.wsdl_cache_enabled", "0");
        
		// SOAP URI
		$location = 'https://' . \Deepplusplus\Setting\Helpers::config('module_shipping_canadapost_hostname') . '/rs/soap/rating/v3';
		
		// SSL Options
		$opts = array('ssl' =>
			array(
				'verify_peer'=> false,
				'cafile' => "/CanadaPost/cacert.pem",
				'CN_match' => \Deepplusplus\Setting\Helpers::config('module_shipping_canadapost_hostname')
			)
		);
		
		$ctx = stream_context_create($opts);	
		$client = new \SoapClient(dirname(__FILE__) . $wsdl, ['location' => $location, 'features' => SOAP_SINGLE_ELEMENT_ARRAYS, 'stream_context' => $ctx]);
		
		// Set WS Security UsernameToken
		$WSSENS = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
		$usernameToken = new \stdClass(); 
		$usernameToken->Username = new \SoapVar(\Deepplusplus\Setting\Helpers::hidden('module_shipping_canadapost_username'), XSD_STRING, null, null, null, $WSSENS);
		$usernameToken->Password = new \SoapVar(\Deepplusplus\Setting\Helpers::hidden('module_shipping_canadapost_password'), XSD_STRING, null, null, null, $WSSENS);
		$content = new \stdClass(); 
		$content->UsernameToken = new \SoapVar($usernameToken, SOAP_ENC_OBJECT, null, null, null, $WSSENS);
		$header = new \SOAPHeader($WSSENS, 'Security', $content);
		$client->__setSoapHeaders($header); 
		
		try {
            
            // Customer shipping address
            $shippingAddress = $this->getShippingAddress();

			// Execute Request
			$mailedBy = \Deepplusplus\Setting\Helpers::hidden('module_shipping_canadapost_customer_number');
			$originPostalCode = \Deepplusplus\Setting\Helpers::hidden('module_shipping_canadapost_postcode'); 
			$postalCode = preg_replace('/\s+/', '', $shippingAddress['postcode']);
			$productWeight = $this->orderTotalWeight();
			
			$weightRate = 1; // default is kg
			if(\Deepplusplus\Setting\Helpers::config('module_shipping_canadapost_weight_unit') == 'lb') {
				$weightRate = 0.453592; // convert from lb to kg
			}
			
			$requestArray = array(
					'get-rates-request' => array(
						'mailing-scenario' => array(
							'customer-number' => $mailedBy,
							'parcel-characteristics'	=> array(
								'weight' => number_format($productWeight*$weightRate, 2)/*,
								'dimensions' => array(
									'width' => $packed_box['width'],
									'height' => $packed_box['height'],
									'length' => $packed_box['length']
								)*/
							),
							'origin-postal-code' => $originPostalCode
						)
				)
            );
            
            switch($shippingAddress['country_code']) {
                case 'US':
                    $requestArray['get-rates-request']['mailing-scenario']['destination'] = array(
                        'united-states' => array(
                            'zip-code' => $postalCode
                        )
                    );
                break;
                
                case 'CA':
                    $requestArray['get-rates-request']['mailing-scenario']['destination'] = array(
                        'domestic' => array(
                            'postal-code' => strtoupper($postalCode) // remove all whitespace
                        )
                    );
                break;
                
                default:
                    $requestArray['get-rates-request']['mailing-scenario']['destination'] = array(
                        'international' => array(
                            'country-code' => $shippingAddress['country_code'] // remove all whitespace
                        )
                    );
                break;
            }
			
            $result = $client->__soapCall('GetRates', $requestArray, NULL, NULL);

            if ( isset($result->{'price-quotes'}) ) {
                
                // Remove whitespace
                $serviceCodeArray = explode('|', \Deepplusplus\Setting\Helpers::config('module_shipping_canadapost_services'));
				foreach ( $result->{'price-quotes'}->{'price-quote'} as $priceQuote ) {
				
					// echo '<pre>';var_dump($priceQuote);echo '</pre>';
					if(in_array($priceQuote->{'service-name'}, $serviceCodeArray)) {
                        $serviceCode = strtoupper(str_replace('-', '_', $this->code . '_' . strtolower(str_replace(array('.', ' ', '-'), '', $priceQuote->{'service-code'}))));
                        $estimated = "";
                        if(isset($priceQuote->{'service-standard'}->{'expected-delivery-date'}) && !empty($priceQuote->{'service-standard'}->{'expected-delivery-date'})) {
                            $estimated = " (" . "Estimated: " . date('F d, Y', strtotime($priceQuote->{'service-standard'}->{'expected-delivery-date'})) . ")";
                        }
                        $services[] = array(
                            'id' => $serviceCode,
                            'title' => $priceQuote->{'service-name'} . $estimated,
                            'cost' => $priceQuote->{'price-details'}->{'due'} + (float)\Deepplusplus\Setting\Helpers::config('module_shipping_canadapost_handling_fee')
                        );
					}
				}
				
				$sortCol = array();
				foreach ($services as $key => $row) {
					$sortCol[$key] = $row['cost'];
				}
				array_multisort($sortCol, SORT_ASC, $services);
				
			}
			
		} catch (SoapFault $exception) {
            $services[] = [
                'title' => trim($exception->getMessage()),
                'error' => true
            ];
			//return trim($exception->faultcode) . trim($exception->getMessage());
		}

        return $services;

    }

    /**
     * get quotes
     */
    public function quotes() {

        $_methods = $this->getQuote();

        $quotes = [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'methods' => $_methods
        ];

        return $quotes;
        
    }

}