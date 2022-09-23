<?php

/**
 * Australia Post
 * Postal service company
 * Australia Post, formally the Australian Postal Corporation, is the government business 
 * enterprise that provides postal services in Australia. The head office of Australia Post 
 * is located in Bourke Street, Melbourne, which also serves as a post office.
 */
class Auspost
{
    
    public $code, $name, $description, $fields, $status;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->code  = 'Auspost';
        $this->name = 'Australia Post';
        $this->description = 'Australia Post, formally the Australian Postal Corporation, is the government business enterprise that provides postal services in Australia. The head office of Australia Post is located in Bourke Street, Melbourne, which also serves as a post office.';
    }

    /**
     * Config fields then show on the backend to set config
     */
    public function fields() {

        /*
        temporary disable this because we can't find any list of services
        */
        /* $auspostServices = [
            ['id' => 'AUS_PARCEL_EXPRESS', 'name' => 'Express Post'],
            ['id' => 'AUS_PARCEL_EXPRESS_SATCHEL_3KG', 'name' => 'Express Post Medium (3Kg) Satchel'],
            ['id' => 'AUS_PARCEL_REGULAR', 'name' => 'Parcel Post'],
            ['id' => 'AUS_PARCEL_REGULAR_SATCHEL_3KG', 'name' => 'Parcel Post Medium Satchel']
        ]; */

        /**
         * A list of all standard box sizes available for purchase at Australia Post retail outlets
         * The dimensions returned have the format length x width x height in centimetres 
         * A 'My own box' option if you choose to use your own box
         */
        $standardBoxSizes = [
            ['id' => '22.0x16.0x7.7', 'name' => 'Bx1 (22.0 x 16.0 x 7.7)'],
            ['id' => '31.0x22.5x10.2', 'name' => 'Bx2 (31.0 x 22.5 x 10.2)'],
            ['id' => '40.0x20.0x18.0', 'name' => 'Bx3 (40.0 x 20.0 x 18.0)'],
            ['id' => '43.0x30.5x14.0', 'name' => 'Bx4 (43.0 x 30.5 x 14.0)'],
            ['id' => '40.5x30.0x25.5', 'name' => 'Bx5 (40.5 x 30.0 x 25.5)'],
            ['id' => '22.0x14.5x3.5', 'name' => 'Bx6 (22.0 x 14.5 x 3.5)'],
            ['id' => '14.5x12.7x1.0', 'name' => 'Bx7 (14.5 x 12.7 x 1.0)'],
            ['id' => '36.3x21.2x6.5', 'name' => 'Bx8 (36.3 x 21.2 x 6.5)']
        ];

        return $this->fields = [
            [
                'label' => 'Enable Australia Post Shipping', 
                'description' => 'Do you want to offer Australia Post rate shipping?',
                'key' => 'module_shipping_auspost_active',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_auspost_active'),
                'input' => 'select',
                'options' => [
                    ['id' => 1, 'name' => 'Enabled'],
                    ['id' => 0, 'name' => 'Disabled']
                ]
            ],
            [
                'label' => 'API Key', 
                'description' => 'Set the customer number provided from Australia Post',
                'key' => 'module_shipping_auspost_apikey',
                'value' => \Deepplusplus\Setting\Helpers::hidden('module_shipping_auspost_apikey'),
                'input' => 'text',
                'hidden' => 1
            ],
            [
                'label' => 'Weight Unit', 
                'description' => 'Please select your weight unit so that Australia Post will convert it to match the calculation. Default is KG',
                'key' => 'module_shipping_auspost_weight_unit',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_auspost_weight_unit'),
                'input' => 'select',
                'options' => [
                    ['id' => 'lb', 'name' => 'LB'],
                    ['id' => 'kg', 'name' => 'KG']
                ]
            ],
            [
                'label' => 'Stardard Box Sizes', 
                'description' => 'A list of all standard box sizes available for purchase at Australia Post retail outlets. The dimensions returned have the format length x width x height in centimetres',
                'key' => 'module_shipping_canadapost_stardard_box_sizes',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_canadapost_stardard_box_sizes'),
                'options' => $standardBoxSizes,
                'input' => 'select'
            ],
            [
                'label' => 'Estimated Delivery Time?', 
                'description' => 'Show estimated delivery time for each shipping service however this can make the website slower when activated',
                'key' => 'module_shipping_auspost_show_estimated',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_auspost_show_estimated'),
                'options' => [
                    ['id' => 1, 'name' => 'Yes'],
                    ['id' => 0, 'name' => 'No']
                ],
                'input' => 'select'
            ],
            [
                'label' => 'Shipping Zone', 
                'description' => 'If a zone is selected, only enable this shipping method for that zone.',
                'key' => 'module_shipping_auspost_shipping_zone',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_auspost_shipping_zone'),
                'options' => \DB::table('module_zones')->select('id', 'name')->get(),
                'input' => 'select'
            ],
            [
                'label' => 'Tax Class', 
                'description' => 'Set tax class for the courier shipping.',
                'key' => 'module_shipping_auspost_tax_class',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_auspost_tax_class'),
                'options' => \DB::table('tax_classes')->select('id', 'name')->get(),
                'input' => 'select'
            ],
            [
                'label' => 'Handling Fee', 
                'description' => 'Add a shipping handling fee.',
                'key' => 'module_shipping_auspost_handling_fee',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_auspost_handling_fee'),
                'input' => 'text'
            ],
            [
                'label' => 'Sort Order', 
                'description' => 'Sort order of display',
                'key' => 'module_shipping_auspost_sort_order',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_auspost_sort_order'),
                'input' => 'text'
            ],
        ];
    }

    /**
     * Check status
     */
    public function status($isBackend = false) {
        
        $this->status = \Deepplusplus\Setting\Helpers::config('module_shipping_auspost_active') == 1 ? true : false;
        if($isBackend === true) {
            return $this->status;
        }

        // Check shipping zone if exists
        if ($this->status === true && \Deepplusplus\Setting\Helpers::config('module_shipping_auspost_shipping_zone')) {
            
            $isFlag = false;

            $order = \Request::session()->get('order');

            $shippingAddress = \DB::table('addresses')->where('id', $order['profile']['default_shipping_address_id'])->first();
            
            $modulezone = \DB::table('module_zones')
                                ->select('module_zone_regions.country_code', 'module_zone_regions.zone_code')
                                ->join('module_zone_regions', 'module_zones.id', '=', 'module_zone_regions.module_zone_id')
                                ->where([
                                    'module_zones.id' => (int)\Deepplusplus\Setting\Helpers::config('module_shipping_auspost_shipping_zone'),
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

        $order = \Request::session()->get('order');
        $cartItems = $order['items'];
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
        $order = \Request::session()->get('order');
        $cartItems = $order['items'];
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
        $order = \Request::session()->get('order');
        $key = array_search($order['profile']['default_shipping_address_id'], array_column($order['profile']['addresses'], 'id'));
        return $shippingAddress = $order['profile']['addresses'][$key];
    }

    /**
     * Calculate tax
     * @param Array $shippingAddress
     * @return Float taxRatePercentage
     */
    public function calculateTax($shippingAddress) {
        $taxRatePercentage = 0;
        if(\Deepplusplus\Setting\Helpers::config('module_shipping_auspost_tax_class') > 0) {
            $taxRates = \DB::table('tax_rates')->select('module_zone_id')->where('tax_class_id', \DB::table('tax_classes')->where('id', \Deepplusplus\Setting\Helpers::config('module_shipping_auspost_tax_class'))->pluck('id')->first())->get();
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

        // Set your API key
        $apiKey = \Deepplusplus\Setting\Helpers::hidden('module_shipping_auspost_apikey');

        // Customer shipping address
        $shippingAddress = $this->getShippingAddress();

        // Define the service input parameters
        $fromPostcode = '2000';\Deepplusplus\Setting\Helpers::config('module_shipping_auspost_weight_unit');
        $toPostcode = '3000';//preg_replace('/\s+/', '', $shippingAddress['postcode']);

        // Box
        list($parcelLengthInCMs, $parcelWidthInCMs, $parcelHeighthInCMs) = explode('x', \Deepplusplus\Setting\Helpers::config('module_shipping_canadapost_stardard_box_sizes'));

        $parcelWeightInKGs = $this->orderTotalWeight();
        $weightRate = 1; // default is kg
        if(\Deepplusplus\Setting\Helpers::config('module_shipping_auspost_weight_unit') == 'lb') {
            $weightRate = 0.453592; // convert from lb to kg
        }

        // Set the query params
        $queryParams = [
            "from_postcode" => $fromPostcode,
            "to_postcode" => $toPostcode,
            "length" => $parcelLengthInCMs,
            "width" => $parcelWidthInCMs,
            "height" => $parcelHeighthInCMs,
            "weight" => $parcelWeightInKGs*$weightRate
        ];

        // Set the URL for the Domestic Parcel Size service
        $urlPrefix = 'digitalapi.auspost.com.au';
        $postageTypesURL = 'https://' . $urlPrefix . '/postage/parcel/domestic/service.json?' . http_build_query($queryParams);

        // Lookup available domestic parcel delivery service types
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $postageTypesURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('AUTH-KEY: ' . $apiKey));
        $rawBody = curl_exec($ch);

        // Check the response; if the body is empty then an error has occurred
        if(!$rawBody){
            die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
        }

        // All good, lets parse the response into a JSON object
        $serviceTypesJSON = json_decode($rawBody, true);
        if(isset($serviceTypesJSON['services']['service']) && count($serviceTypesJSON['services']['service']) > 0) {
            foreach($serviceTypesJSON['services']['service'] as $serv) {

                $estimatedText = '';
                if((int)\Deepplusplus\Setting\Helpers::config('module_shipping_auspost_show_estimated') === 1) {
                    $estimated = $this->estimatedDeliveryTime(array_merge($queryParams, ['service_code' => $serv['code']]));
                    $estimatedText = ' / ' . $estimated['postage_result']['delivery_time'];
                }

                // Service code
                $serviceCode = strtoupper(str_replace('-', '_', $this->code . '_' . strtolower(str_replace(array('.', ' ', '-'), '', $serv['code']))));

                $services[] = array(
                    'id' => $serviceCode,
                    'title' => $serv['name'] . $estimatedText,
                    'cost' => $serv['price'] + (float)\Deepplusplus\Setting\Helpers::config('module_shipping_canadapost_handling_fee')
                );
            }
        }

        return $services;

    }

    /**
     * Get estimated delivery time
     */
    public function estimatedDeliveryTime($queryParams)
    {
        // Define the service input parameters
        /* $fromPostcode = '2000';
        $toPostcode = '3000';
        $parcelLengthInCMs = 22;
        $parcelWidthInCMs = 16;
        $parcelHeighthInCMs = 7.7;
        $parcelWeightInKGs = 1.5;

        // Set the query params
        $queryParams = array(
            "from_postcode" => $fromPostcode,
            "to_postcode" => $toPostcode,
            "length" => $parcelLengthInCMs,
            "width" => $parcelWidthInCMs,
            "height" => $parcelHeighthInCMs,
            "weight" => $parcelWeightInKGs,
            "service_code" => 'AUS_PARCEL_REGULAR'
        ); */

        // Set the URL for the Domestic Parcel Calculation service
        $urlPrefix = 'digitalapi.auspost.com.au';
        $calculateRateURL = 'https://' . $urlPrefix . '/postage/parcel/domestic/calculate.json?' . http_build_query($queryParams);

        // Calculate the final domestic parcel delivery price
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $calculateRateURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('AUTH-KEY: ' . \Deepplusplus\Setting\Helpers::hidden('module_shipping_auspost_apikey')));
        $rawBody = curl_exec($ch);

        // Check the response; if the body is empty than an error has occurred
        if(!$rawBody){
            die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
        }

        // All good, lets parse the response into a JSON object
        return $priceJSON = json_decode($rawBody, true);

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