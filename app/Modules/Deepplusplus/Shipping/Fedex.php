<?php

//use App\Helpers\Deepplusplus\Setting\Helpers;

/**
 * Class Rate.
 *
 */
class Fedex
{
    
    public $code, $name, $description, 
            $order, $fields, $status;
            
    /**
     * Constructor
     */
    public function __construct() {

        $this->order = session('order');
        
        $this->code  = 'FedEx';
        $this->name = 'FedEx';
        $this->description = 'FedEx Corporation is an American multinational delivery services company';
    }

    /**
     * Config fields then show on the backend to set config
     */
    public function fields() {

        $fedexYesNo = [
            ['id' => 0, 'name' => 'No'],
            ['id' => 1, 'name' => 'Yes'],
        ];

        $fedexWeightUnit = [
            ['id' => 'lb', 'name' => 'Lb'],
            ['id' => 'kg', 'name' => 'Kg'],
        ];

        $fedexRateRequestTypes = [
            ['id' => 1, 'name' => 'Account Rates Only'],
            ['id' => 2, 'name' => 'List Rates Only'],
            ['id' => 3, 'name' => 'Both Account & List Rates (if different)'],
        ];

        $fedexDropoffTypes = [
            ['id' => 'REGULAR_PICKUP', 'name' => 'Regular Pickup'],
            ['id' => 'BUSINESS_SERVICE_CENTER', 'name' => 'Business Service Center'],
            ['id' => 'DROP_BOX', 'name' => 'Drop Box'],
            ['id' => 'REQUEST_COURIER', 'name' => 'Request Courier'],
            ['id' => 'STATION', 'name' => 'Station'],
        ];

        $fedexPackagingTypes = [
            ['id' => 'YOUR_PACKAGING', 'name' => 'Your Packaging'],
            ['id' => 'FEDEX_ENVELOPE', 'name' => 'FedEx Envelope'],
            ['id' => 'FEDEX_BOX', 'name' => 'FedEx Box'],
            ['id' => 'FEDEX_10KG_BOX', 'name' => 'FedEx 10Kg Box'],
            ['id' => 'FEDEX_25KG_BOX', 'name' => 'FedEx 25Kg Box'],
            ['id' => 'FEDEX_PAK', 'name' => 'FedEx Pak'],
            ['id' => 'FEDEX_TUBE', 'name' => 'FedEx Tube']
        ];
        
        $fedexServices = [
            ['id' => 'FEDEX_1_DAY_FREIGHT', 'name' => '1 Day Freight'],
            ['id' => 'FEDEX_2_DAY', 'name' => '2 Day'],
            ['id' => 'FEDEX_2_DAY_FREIGHT', 'name' => '2 Day Freight'],
            ['id' => 'FEDEX_3_DAY_FREIGHT', 'name' => '3 Day Freight'],
            ['id' => 'FEDEX_EXPRESS_SAVER', 'name' => 'Express Saver'],
            ['id' => 'FEDEX_GROUND', 'name' => 'Ground'],
            ['id' => 'GROUND_HOME_DELIVERY', 'name' => 'Ground Home Delivery'],
            ['id' => 'STANDARD_OVERNIGHT', 'name' => 'Standard Overnight'],
            ['id' => 'FIRST_OVERNIGHT', 'name' => 'First Overnight'],
            ['id' => 'PRIORITY_OVERNIGHT', 'name' => 'Priority Overnight'],
            ['id' => 'SMART_POST', 'name' => 'Smart Post'],
            ['id' => 'FREIGHT', 'name' => 'Freight'],
            ['id' => 'NATIONAL_FREIGHT', 'name' => 'National Freight'],
            ['id' => 'INTERNATIONAL_GROUND', 'name' => 'International Ground'],
            ['id' => 'INTERNATIONAL_ECONOMY', 'name' => 'International Economy'],
            ['id' => 'INTERNATIONAL_ECONOMY_FREIGHT', 'name' => 'International Economy Freight'],
            ['id' => 'INTERNATIONAL_FIRST', 'name' => 'International First'],
            ['id' => 'INTERNATIONAL_PRIORITY', 'name' => 'International Priority'],
            ['id' => 'INTERNATIONAL_PRIORITY_FREIGHT', 'name' => 'International Priority Freight'],
            ['id' => 'EUROPE_FIRST_INTERNATIONAL_PRIORITY', 'name' => 'Europe First International Priority']
        ];

        return $this->fields = [
            [
                'label' => 'Enable FedEx Shipping', 
                'description' => 'Do you want to offer FedEx rate shipping?',
                'key' => 'module_shipping_fedex_active',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_fedex_active'),
                'input' => 'select',
                'options' => [
                    ['id' => 1, 'name' => 'Enabled'],
                    ['id' => 0, 'name' => 'Disabled']
                ]
            ],
            [
                'label' => 'FedEx Key', 
                'description' => 'Set the fedex key provided from FedEx',
                'key' => 'module_shipping_fedex_key',
                'value' => \Deepplusplus\Setting\Helpers::hidden('module_shipping_fedex_key'),
                'input' => 'text',
                'hidden' => 1
            ],
            [
                'label' => 'FedEx Password', 
                'description' => 'Set the password provided from FedEx',
                'key' => 'module_shipping_fedex_password',
                'value' => \Deepplusplus\Setting\Helpers::hidden('module_shipping_fedex_password'),
                'input' => 'text',
                'hidden' => 1
            ],
            [
                'label' => 'FedEx Account Number', 
                'description' => 'Set the fedex account number provided from FedEx',
                'key' => 'module_shipping_fedex_account_number',
                'value' => \Deepplusplus\Setting\Helpers::hidden('module_shipping_fedex_account_number'),
                'input' => 'text',
                'hidden' => 1
            ],
            [
                'label' => 'FedEx Meter Number', 
                'description' => 'Set the fedex meter number provided from FedEx',
                'key' => 'module_shipping_fedex_meter_number',
                'value' => \Deepplusplus\Setting\Helpers::hidden('module_shipping_fedex_meter_number'),
                'input' => 'text',
                'hidden' => 1
            ],
            [
                'label' => 'Signature', 
                'description' => 'Require a signature on orders',
                'key' => 'module_shipping_fedex_signature',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_fedex_signature'),
                'options' => $fedexYesNo,
                'input' => 'select'
            ],
            [
                'label' => 'Weight Unit', 
                'description' => 'Set weight units for FedEx',
                'key' => 'module_shipping_fedex_weight_unit',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_fedex_weight_unit') ? \Deepplusplus\Setting\Helpers::config('module_shipping_fedex_weight_unit') : 'lb',
                'options' => $fedexWeightUnit,
                'input' => 'select'
            ],
            [
                'label' => 'Rate Request Type', 
                'description' => 'Set FedEx rate request',
                'key' => 'module_shipping_fedex_rate_request',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_fedex_rate_request') ? \Deepplusplus\Setting\Helpers::config('module_shipping_fedex_rate_request') : 1,
                'options' => $fedexRateRequestTypes,
                'input' => 'select'
            ],
            [
                'label' => 'Drop Off Type', 
                'description' => 'Set FedEx drop off type',
                'key' => 'module_shipping_fedex_drop_off_type',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_fedex_drop_off_type') ? \Deepplusplus\Setting\Helpers::config('module_shipping_fedex_drop_off_type') : 'REGULAR_PICKUP',
                'options' => $fedexDropoffTypes,
                'input' => 'select'
            ],
            [
                'label' => 'Packaging', 
                'description' => 'Set FedEx Packaging',
                'key' => 'module_shipping_fedex_packaging_type',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_fedex_packaging_type') ? \Deepplusplus\Setting\Helpers::config('module_shipping_fedex_packaging_type') : 'YOUR_PACKAGING',
                'options' => $fedexPackagingTypes,
                'input' => 'select'
            ],
            [
                'label' => 'Saturday Delivery', 
                'description' => 'Set saturday delivery for FedEx',
                'key' => 'module_shipping_fedex_saturday_delivery',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_fedex_saturday_delivery'),
                'options' => $fedexYesNo,
                'input' => 'select'
            ],
            [
                'label' => 'FedEx Services', 
                'description' => 'Select the FedEx services to be offered.',
                'key' => 'module_shipping_fedex_services',
                'value' => json_decode(\Deepplusplus\Setting\Helpers::config('module_shipping_fedex_services')),
                'options' => $fedexServices,
                'input' => 'multiselect'
            ],
            [
                'label' => 'Shipping Zone', 
                'description' => 'If a zone is selected, only enable this shipping method for that zone.',
                'key' => 'module_shipping_fedex_shipping_zone',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_fedex_shipping_zone'),
                'options' => \DB::table('module_zones')->select('id', 'name')->get(),
                'input' => 'select'
            ],
            [
                'label' => 'Tax Class', 
                'description' => 'Set tax class for the courier shipping.',
                'key' => 'module_shipping_fedex_tax_class',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_fedex_tax_class'),
                'options' => \DB::table('tax_classes')->select('id', 'name')->get(),
                'input' => 'select'
            ],
            [
                'label' => 'Handling Fee', 
                'description' => 'Add a shipping handling fee.',
                'key' => 'module_shipping_fedex_handling_fee',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_fedex_handling_fee'),
                'input' => 'text'
            ],
            [
                'label' => 'Sort Order', 
                'description' => 'Sort order of display',
                'key' => 'module_shipping_fedex_sort_order',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_fedex_sort_order'),
                'input' => 'text'
            ],
        ];
    }

    /**
     * Check status
     */
    public function status($isBackend = false) {
        
        $this->status = \Deepplusplus\Setting\Helpers::config('module_shipping_fedex_active') == 1 ? true : false;
        if($isBackend === true) {
            return $this->status;
        }

        // Check shipping zone if exists
        if ($this->status === true && \Deepplusplus\Setting\Helpers::config('module_shipping_fedex_shipping_zone')) {
            
            $isFlag = false;

            $shippingAddress = \DB::table('addresses')->where('id', $this->order['profile']['default_shipping_address_id'])->first();
            
            $modulezone = \DB::table('module_zones')
                                ->select('module_zone_regions.country_code', 'module_zone_regions.zone_code')
                                ->join('module_zone_regions', 'module_zones.id', '=', 'module_zone_regions.module_zone_id')
                                ->where([
                                    'module_zones.id' => (int)\Deepplusplus\Setting\Helpers::config('module_shipping_fedex_shipping_zone'),
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
        if(\Deepplusplus\Setting\Helpers::config('module_shipping_fedex_tax_class') > 0) {
            $taxRates = \DB::table('tax_rates')->select('module_zone_id')->where('tax_class_id', \DB::table('tax_classes')->where('id', \Deepplusplus\Setting\Helpers::config('module_shipping_fedex_tax_class'))->pluck('id')->first())->get();
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

        // Calculate shipping boxes
        $shipping_weight = $this->orderTotalWeight();
        $shipping_num_boxes = $this->shippingNumBox($shipping_weight);
        $shipping_max_weight = 50;

        if ($shipping_weight > $shipping_max_weight) { // Split into many boxes
            $zc_boxes =  round(($shipping_weight/$shipping_max_weight) * pow(10, 2), 0);
            $zc_boxes = $zc_boxes/pow(10, 2);
            $shipping_num_boxes = ceil($zc_boxes);
            $shipping_weight = $shipping_weight/$shipping_num_boxes;
        }

        // Customer shipping address
        $shippingAddress = $this->getShippingAddress();

        // BEGIN PO Box Ban street address
        $fedexPoBox = false;
        $street_address = $shippingAddress['address_line_1'];
        if ( preg_match('/PO BOX/si', $street_address) ) {
            $fedexPoBox = true;
        } else if ( preg_match('/BOX/si', $street_address) ) {
            $fedexPoBox = true;
        } else if ( preg_match('/POBOX/si', $street_address) ) {
            $fedexPoBox = true;
        } else if ( preg_match('/P\.O\./si', $street_address) ) {
            $fedexPoBox = true;
        } else if ( preg_match('/P\.O/si', $street_address) ) {
            $fedexPoBox = true;
        } else if ( preg_match('/PO\./si', $street_address) ) {
            $fedexPoBox = true;
        }

        // BEGIN PO Box Ban suburb
        $suburb = $shippingAddress['address_line_2'];
        if ( preg_match('/PO BOX/si', $suburb) ) {
            $fedexPoBox = true;
        } else if ( preg_match('/BOX/si', $suburb) ) {
            $fedexPoBox = true;
        } else if ( preg_match('/POBOX/si', $suburb) ) {
            $fedexPoBox = true;
        } else if ( preg_match('/P\.O\./si', $suburb) ) {
            $fedexPoBox = true;
        } else if ( preg_match('/P\.O/si', $suburb) ) {
            $fedexPoBox = true;
        } else if ( preg_match('/PO\./si', $suburb) ) {
            $fedexPoBox = true;
        }

        if($fedexPoBox === true) {
            return false;
        }

        $path_to_wsdl = "/FedEx/FedExRateService_v20.wsdl";

        ini_set("soap.wsdl_cache_enabled", "0");
        
        // Setting a connection timeout (five seconds on the example)
        try {
            $client = new \SoapClient(dirname(__FILE__) . $path_to_wsdl, [
                'trace' => 1, 
                'connection_timeout' => 5, 
                'exceptions' => true
            ]);
        } catch (\SoapFault $e) {
            //var_dump($e->getMessage());die;
        }

        // customer details      
        $street_address = $shippingAddress['address_line_1'];
        $street_address2 = $shippingAddress['address_line_2'];
        $city = $shippingAddress['city'];
        $state = $shippingAddress['zone_code'];
        if ($state == "QC") $state = "PQ";
        $postcode = str_replace(array(' ', '-'), '', $shippingAddress['postcode']);
        $country_code = $shippingAddress['country_code'];
        
        $totals = $this->order['subtotal'];

        $request['WebAuthenticationDetail'] = array(
            'UserCredential' => array(
                'Key' =>  \Deepplusplus\Setting\Helpers::hidden('module_shipping_fedex_key'), 
                'Password' => \Deepplusplus\Setting\Helpers::hidden('module_shipping_fedex_password')
            )
        );
        $request['ClientDetail'] = array(
            'AccountNumber' => \Deepplusplus\Setting\Helpers::hidden('module_shipping_fedex_account_number'), 
            'MeterNumber' => \Deepplusplus\Setting\Helpers::hidden('module_shipping_fedex_meter_number')
        );
        $request['TransactionDetail'] = array('CustomerTransactionId' => 'A noted from FedEx #11111');
        $request['Version'] = array('ServiceId' => 'crs', 'Major' => '20', 'Intermediate' => '0', 'Minor' => '0');
        $request['ReturnTransitAndCommit'] = true;
        $request['RequestedShipment']['DropoffType'] = \Deepplusplus\Setting\Helpers::config('module_shipping_fedex_drop_off_type'); // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
        $request['RequestedShipment']['ShipTimestamp'] = date('c');
        $request['RequestedShipment']['PackagingType'] = \Deepplusplus\Setting\Helpers::config('module_shipping_fedex_packaging_type'); // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
        $request['RequestedShipment']['TotalInsuredValue'] = array(
            'Amount'=> 0, 
            'Currency' => $this->order['currency']
        ); 

        // Address Validation
        $validate = "false";
        //$validate = 'false';
        $residential_address = true;
        $address_validation = false;

        if ($validate == 'true') {
            $av_client = new \SoapClient("FedEx/FedExAddressValidationService_v4.wsdl", array('trace' => 1));
            $av_request['WebAuthenticationDetail'] = array(
                'UserCredential' => array(
                    'Key' => \Deepplusplus\Setting\Helpers::hidden('module_shipping_fedex_key'), 
                    'Password' => \Deepplusplus\Setting\Helpers::hidden('module_shipping_fedex_password')
                )
            );
            $av_request['ClientDetail'] = array(
                'AccountNumber' => \Deepplusplus\Setting\Helpers::hidden('module_shipping_fedex_account_number'), 
                'MeterNumber' => \Deepplusplus\Setting\Helpers::hidden('module_shipping_fedex_meter_number')
            );
            $av_request['TransactionDetail'] = array('CustomerTransactionId' => ' *** Address Validation Request v2 using PHP ***');
            $av_request['Version'] = array('ServiceId' => 'aval', 'Major' => '2', 'Intermediate' => '0', 'Minor' => '0');
            $av_request['RequestTimestamp'] = date('c');
            $av_request['Options'] = array(
                'CheckResidentialStatus' => 1,
                'MaximumNumberOfMatches' => 5,
                'StreetAccuracy' => 'LOOSE',
                'DirectionalAccuracy' => 'LOOSE',
                'CompanyNameAccuracy' => 'LOOSE',
                'ConvertToUpperCase' => 1,
                'RecognizeAlternateCityNames' => 1,
                'ReturnParsedElements' => 1
            );
            $av_request['AddressesToValidate'] = array(
                array(
                    'AddressId' => 'Customer Address',
                    'Address' => array(
                        'StreetLines' => array(utf8_encode($street_address), utf8_encode($street_address2)),
                        'PostalCode' => $postcode,
                        'CompanyName' => $shippingAddress['company']
                    )
                )
            );

            try {

                $av_response = $av_client->addressValidation($av_request);

                if ($av_response->HighestSeverity == 'SUCCESS') {
                    $address_validation = true;
                    if ($av_response->AddressResults->ProposedAddressDetails->ResidentialStatus == 'BUSINESS') {
                        $residential_address = false;
                    } // already set to true so no need for else statement
                }
            } catch (Exception $e) {
                /* $services[] = [
                    'title' => 'Sorry, the FedEx.com server is currently not responding, please try again later.',
                    'error' => true
                ]; */
            }
        }

        if ($address_validation == false) {
            if (!empty($shippingAddress['company'])) {
                $residential_address = false;
            } else {
                $residential_address = true;
            }
        }

        $request['RequestedShipment']['Shipper'] = array(
            'Address' => array(
                'StreetLines' => '', // Origin details
                'City' => '',
                'StateOrProvinceCode' => '',
                'PostalCode' => \Deepplusplus\Setting\Helpers::config('store_postcode'),
                'CountryCode' => \Deepplusplus\Setting\Helpers::config('store_country_code')
            )
        );          
        $request['RequestedShipment']['Recipient'] = array(
            'Address' => array (
                'StreetLines' => array(utf8_encode($street_address), utf8_encode($street_address2)),
                'City' => utf8_encode($city),
                'PostalCode' => $postcode,
                'CountryCode' => $country_code,
                'Residential' => $residential_address
            )
        ); 
        if (in_array($country_code, array('US', 'CA'))) {
            $request['RequestedShipment']['Recipient']['StateOrProvinceCode'] = $state;
        }
              
        $request['RequestedShipment']['ShippingChargesPayment'] = array(
            'PaymentType' => 'SENDER',
            'Payor' => array(
                'AccountNumber' => \Deepplusplus\Setting\Helpers::hidden('module_shipping_fedex_account_number'),
                'CountryCode' => \Deepplusplus\Setting\Helpers::config('store_country_code')
            )
        );
        $request['RequestedShipment']['RateRequestTypes'] = 'LIST'; 
        $request['RequestedShipment']['PackageDetail'] = 'INDIVIDUAL_PACKAGES';
        $request['RequestedShipment']['RequestedPackageLineItems'] = array();
        
        $dimensions_failed = false;
        
        $boxed_value = 0;//sprintf("%01.2f", $this->insurance / $shipping_num_boxes);
        if ($shipping_weight == 0) $shipping_weight = 0.01;
        
        for ($i=0; $i<$shipping_num_boxes; $i++) {
            $request['RequestedShipment']['RequestedPackageLineItems'][] = array(
                'Weight' => array(
                    'Value' => $shipping_weight,
                    'Units' => strtoupper(\Deepplusplus\Setting\Helpers::config('module_shipping_fedex_weight_unit'))
                ),
                'GroupPackageCount' => 1,
                'InsuredValue' => array(
                    'Currency' => $this->order['currency'],
                    'Amount' => $boxed_value
                ),                  
            );
        }

        $request['RequestedShipment']['PackageCount'] = $shipping_num_boxes;
    
        if ((int)\Deepplusplus\Setting\Helpers::config('module_shipping_fedex_saturday_delivery') === 1) {
            $request['RequestedShipment']['ServiceOptionType'] = 'SATURDAY_DELIVERY';
        }

        if ((int)\Deepplusplus\Setting\Helpers::config('module_shipping_fedex_signature') >= 0 && $totals >= (int)\Deepplusplus\Setting\Helpers::config('module_shipping_fedex_signature')) { 
            $request['RequestedShipment']['SpecialServicesRequested'] = 'SIGNATURE_OPTION'; 
        }

        if($shipping_weight <= 1){
            $shipping_weight = round($shipping_weight, 2);
            $request['RequestedShipment']['SmartPostDetail']['Indicia'] = 'PRESORTED_STANDARD';
        }
        else{
            $request['RequestedShipment']['SmartPostDetail']['Indicia'] = 'PARCEL_SELECT';
        }

        $request['RequestedShipment']['SmartPostDetail']['AncillaryEndorsement'] = 'ADDRESS_CORRECTION';
        $request['RequestedShipment']['SmartPostDetail']['HubId'] = 5431;

        $errorMsg = [];

        $fedexServices = json_decode(\Deepplusplus\Setting\Helpers::config('module_shipping_fedex_services'));
        foreach($fedexServices as $servccalld) {   

            $request['RequestedShipment']['ServiceType'] = $servccalld;

            try {

                $response = $client->getRates($request);

                if($response->HighestSeverity === "ERROR" || $response->HighestSeverity === "FAILURE") {
                    continue;
                }

                if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR' && is_array($response->RateReplyDetails) || is_object($response->RateReplyDetails)) {
                    
                    if (is_object($response->RateReplyDetails)) {
                      $response->RateReplyDetails = get_object_vars($response->RateReplyDetails);
                    }

                    /* $shipping_box_weight_display = 0;
                    switch ($shipping_box_weight_display) {
                        case (0):
                            $show_box_weight = '';
                        break;
                        case (1):
                            $show_box_weight = ' (' . $shipping_num_boxes . ' ' . TEXT_SHIPPING_BOXES . ')';
                        break;
                        case (2):
                            $show_box_weight = ' (' . number_format($shipping_weight * $shipping_num_boxes,2) . TEXT_SHIPPING_WEIGHT . ')';
                        break;
                        default:
                            $show_box_weight = ' (' . $shipping_num_boxes . ' x ' . number_format($shipping_weight,2) . TEXT_SHIPPING_WEIGHT . ')';
                        break;
                    } */

                    if (!$response) {
                        $errorMsg[] = "Error: " . $servccalld;
                        continue;
                    }

                    $severity = $response->HighestSeverity;
                    /* $message = "";
                    foreach($response->Notifications as $noti) {
                        $message .= "{$noti->Code}: {$noti->Message} ";
                    } */

                    $service_name = ucwords(strtolower(str_replace('_', ' ', str_replace('FEDEX', '', $response->RateReplyDetails["ServiceType"]))));

                    if (isset($rated->AppliedOptions) && $rated->AppliedOptions === 'SATURDAY_DELIVERY') {
                        $service_name .= " (Saturday Delivery)";
                    }

                    $ratedshipment = $response->RateReplyDetails["RatedShipmentDetails"];
                    $ratedetails = get_object_vars($ratedshipment[0]);
                    $shipdetail = get_object_vars($ratedetails["ShipmentRateDetail"]);
                    $totalbillweight = get_object_vars($shipdetail["TotalBillingWeight"]);
                    $weight = $totalbillweight["Value"];
                    $netcharge = get_object_vars($shipdetail["TotalNetFedExCharge"]);
                    $rate = $netcharge["Amount"];

                    // Rate is always returned in currency of account holder. Must convert to currency in
                    // use
                    $rate_currency = $netcharge["Currency"];

                    $currencyObj = DB::table('currencies')->where('code', $rate_currency)->first();

                    $current_display_currency = strtoupper($this->order['currency']);

                    if ($rate_currency !== $current_display_currency) {
                        // Must convert from FedEx currency to base currency
                        if (isset($currencyObj->rate) && $currencyObj->rate <= 0) {
                            // FedEx currency doesn't have a conversion value, can't proceed!
                            continue;
                        }
                        $rate_base_conv_rate = 1 / $currencyObj->rate;
                        $rate = number_format($rate * $rate_base_conv_rate, 2, '.', '');
                    }

                    $services[] = [
                        'id' => strtoupper(str_replace('-', '_', $this->code . '_' . $response->RateReplyDetails["ServiceType"])),
                        'title' => $service_name,
                        'cost' => $rate + ($rate*$this->calculateTax($shippingAddress)/100) + (float)\Deepplusplus\Setting\Helpers::config('module_shipping_fedex_handling_fee')
                    ];

                }
                
            } catch (\SoapFault $e) {
                /* $services[] = [
                    'title' => 'We are unable to obtain a rate quote for FedEx shipping. Please contact the store if no other alternative is shown.',
                    'error' => true
                ]; */
            }
            // end try catch

        }

        // Return error if there is no FedEx rate
        if(count($services) === 0) {
            $services[] = [
                'title' => 'We are unable to obtain a rate quote for FedEx shipping. Please contact the store if no other alternative is shown.',
                'error' => true
            ];
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