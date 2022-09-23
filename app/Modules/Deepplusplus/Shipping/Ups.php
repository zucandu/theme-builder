<?php

//use App\Helpers\Deepplusplus\Setting\Helpers;

/**
 * Class Rate.
 *
 */
class Ups
{
    
    public $code, $name, $description, 
            $order, $fields, $status,
            $_upsProductCode, $_upsOriginPostalCode, 
            $_upsOriginCountryCode, $_upsDestPostalCode,
            $_upsDestCountryCode, $_upsRateCode,
            $_upsContainerCode, $_upsPackageWeight,
            $_upsResComCode, $_upsActionCode;
    
    public $_upsServices = [
        ['id' => '1DM', 'name' => 'Next Day Air Early AM'],
        ['id' => '1DML', 'name' => 'Next Day Air Early AM Letter'],
        ['id' => '1DA', 'name' => 'Next Day Air'],
        ['id' => '1DAL', 'name' => 'Next Day Air Letter'],
        ['id' => '1DAPI', 'name' => 'Next Day Air Intra (Puerto Rico)'],
        ['id' => '1DP', 'name' => 'Next Day Air Saver'],
        ['id' => '1DPL', 'name' => 'Next Day Air Saver Letter'],
        ['id' => '2DM', 'name' => '2nd Day Air AM'],
        ['id' => '2DML', 'name' => '2nd Day Air AM Letter'],
        ['id' => '2DA', 'name' => '2nd Day Air'],
        ['id' => '2DAL', 'name' => '2nd Day Air Letter'],
        ['id' => '3DS', 'name' => '3 Day Select'],
        ['id' => 'GND', 'name' => 'Ground'],
        ['id' => 'GNDCOM', 'name' => 'Ground Commercial'],
        ['id' => 'GNDRES', 'name' => 'Ground Residential'],
        ['id' => 'STD', 'name' => 'Canada Standard'],
        ['id' => 'XPR', 'name' => 'Worldwide Express'],
        ['id' => 'XPRL', 'name' => 'Worldwide Express Letter'],
        ['id' => 'XDM', 'name' => 'Worldwide Express Plus'],
        ['id' => 'XDML', 'name' => 'Worldwide Express Plus Letter'],
        ['id' => 'XPD', 'name' => 'Worldwide Expedited'],
        ['id' => 'WXS', 'name' => 'Worldwide Saver']
    ];
    /**
     * Constructor
     */
    public function __construct() {

        $this->order = session('order');
        
        $this->code  = 'Ups';
        $this->name = 'United Parcel Service';
        $this->description = 'United Parcel Service';
    }

    /**
     * Config fields then show on the backend to set config
     */
    public function fields() {

        // UPS Pickup Method
        $upsPickupMethods = [
            ['id' => 'CC', 'name' => 'Customer Counter'],
            ['id' => 'RDP', 'name' => 'Daily Pickup'],
            ['id' => 'OTP', 'name' => 'One Time Pickup'],
            ['id' => 'LC', 'name' => 'Letter Center'],
            ['id' => 'OCA', 'name' => 'On Call Air']
        ];

        // UPS Packaging
        $upsPackaging = [
            ['id' => 'CP', 'name' => 'Your Packaging'],
            ['id' => 'ULE', 'name' => 'UPS Letter'],
            ['id' => 'UT', 'name' => 'UPS Tube'],
            ['id' => 'UBE', 'name' => 'UPS Express Box']
        ];
        
        return $this->fields = [
            [
                'label' => 'Enable UPS Shipping', 
                'description' => 'Do you want to offer UPS rate shipping?',
                'key' => 'module_shipping_ups_active',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_ups_active'),
                'input' => 'select',
                'options' => [
                    ['id' => 1, 'name' => 'Enabled'],
                    ['id' => 0, 'name' => 'Disabled']
                ]
            ],
            [
                'label' => 'UPS Username', 
                'description' => 'Set the username provided from UPS',
                'key' => 'module_shipping_ups_username',
                'value' => \Deepplusplus\Setting\Helpers::hidden('module_shipping_ups_username'),
                'input' => 'text',
                'hidden' => 1
            ],
            [
                'label' => 'UPS Password', 
                'description' => 'Set the password provided from UPS',
                'key' => 'module_shipping_ups_password',
                'value' => \Deepplusplus\Setting\Helpers::hidden('module_shipping_ups_password'),
                'input' => 'text',
                'hidden' => 1
            ],
            [
                'label' => 'UPS Access License Number', 
                'description' => 'Set the access license number provided from UPS',
                'key' => 'module_shipping_ups_access_license_number',
                'value' => \Deepplusplus\Setting\Helpers::hidden('module_shipping_ups_access_license_number'),
                'input' => 'text',
                'hidden' => 1
            ],
            [
                'label' => 'UPS Pickup Method', 
                'description' => 'How do you give packages to UPS?',
                'key' => 'module_shipping_ups_pickup_method',
                'value' => !empty(\Deepplusplus\Setting\Helpers::config('module_shipping_ups_pickup_method')) ? \Deepplusplus\Setting\Helpers::config('module_shipping_ups_pickup_method') : 'CC',
                'options' => $upsPickupMethods,
                'input' => 'select'
            ],
            [
                'label' => 'UPS Packaging?', 
                'description' => 'Select your packaging',
                'key' => 'module_shipping_ups_packaging',
                'value' => !empty(\Deepplusplus\Setting\Helpers::config('module_shipping_ups_packaging')) ? \Deepplusplus\Setting\Helpers::config('module_shipping_ups_packaging') : 'CP',
                'options' => $upsPackaging,
                'input' => 'select'
            ],
            [
                'label' => 'Residential Delivery?', 
                'description' => 'Quote for Residential (RES) or Commercial Delivery (COM)',
                'key' => 'module_shipping_ups_residential_delivery',
                'value' => !empty(\Deepplusplus\Setting\Helpers::config('module_shipping_ups_residential_delivery')) ? \Deepplusplus\Setting\Helpers::config('module_shipping_ups_residential_delivery') : 'RES',
                'input' => 'select',
                'options' => [
                    ['id' => 'RES', 'name' => 'Quote for Residential'],
                    ['id' => 'COM', 'name' => 'Commercial Delivery']
                ]
            ],
            [
                'label' => 'UPS Services', 
                'description' => 'Select the UPS services to be offered.',
                'key' => 'module_shipping_ups_services',
                'value' => json_decode(\Deepplusplus\Setting\Helpers::config('module_shipping_ups_services')),
                'options' => $this->_upsServices,
                'input' => 'multiselect'
            ],
            [
                'label' => 'Shipping Zone', 
                'description' => 'If a zone is selected, only enable this shipping method for that zone.',
                'key' => 'module_shipping_ups_shipping_zone',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_ups_shipping_zone'),
                'options' => \DB::table('module_zones')->select('id', 'name')->get(),
                'input' => 'select'
            ],
            [
                'label' => 'Tax Class', 
                'description' => 'Set tax class for the courier shipping.',
                'key' => 'module_shipping_ups_tax_class',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_ups_tax_class'),
                'options' => \DB::table('tax_classes')->select('id', 'name')->get(),
                'input' => 'select'
            ],
            [
                'label' => 'Handling Fee', 
                'description' => 'Add a shipping handling fee.',
                'key' => 'module_shipping_ups_handling_fee',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_ups_handling_fee'),
                'input' => 'text'
            ],
            [
                'label' => 'Handling Per Order or Per Box', 
                'description' => 'Do you want to charge Handling Fee Per Order or Per Box?',
                'key' => 'module_shipping_ups_handling_per',
                'value' => !empty(\Deepplusplus\Setting\Helpers::config('module_shipping_ups_handling_per')) ? \Deepplusplus\Setting\Helpers::config('module_shipping_ups_handling_per') : 'ORDER',
                'input' => 'select',
                'options' => [
                    ['id' => 'ORDER', 'name' => 'Per Order'],
                    ['id' => 'BOX', 'name' => 'Per Box']
                ]
            ],
            [
                'label' => 'Sort Order', 
                'description' => 'Sort order of display',
                'key' => 'module_shipping_ups_sort_order',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_ups_sort_order'),
                'input' => 'text'
            ],
        ];
    }

    /**
     * Check status
     */
    public function status($isBackend = false) {
        
        $this->status = \Deepplusplus\Setting\Helpers::config('module_shipping_ups_active') == 1 ? true : false;
        if($isBackend === true) {
            return $this->status;
        }

        // Check shipping zone if exists
        if ($this->status === true && \Deepplusplus\Setting\Helpers::config('module_shipping_ups_shipping_zone')) {
            
            $isFlag = false;

            $shippingAddress = \DB::table('addresses')->where('id', $this->order['profile']['default_shipping_address_id'])->first();
            
            $modulezone = \DB::table('module_zones')
                                ->select('module_zone_regions.country_code', 'module_zone_regions.zone_code')
                                ->join('module_zone_regions', 'module_zones.id', '=', 'module_zone_regions.module_zone_id')
                                ->where([
                                    'module_zones.id' => (int)\Deepplusplus\Setting\Helpers::config('module_shipping_ups_shipping_zone'),
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
        if(\Deepplusplus\Setting\Helpers::config('module_shipping_ups_tax_class') > 0) {
            $taxRates = \DB::table('tax_rates')->select('module_zone_id')->where('tax_class_id', \DB::table('tax_classes')->where('id', \Deepplusplus\Setting\Helpers::config('module_shipping_ups_tax_class'))->pluck('id')->first())->get();
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

        $shippingAddress = $this->getShippingAddress();

        $weight = $this->orderTotalWeight();

        // UPS doesnt accept zero weight send 1 ounce (0.0625) minimum
        $weight = ($weight < 0.0625) ? 0.0625 : $weight;
        
        if ($shippingAddress['zone_code'] === 'CA') {
            $prod = 'STD';
        } else {
            $prod = 'GNDRES';
        }

        $this->_upsProduct($prod);

        $this->_upsOrigin(\Deepplusplus\Setting\Helpers::config('store_postcode'), \Deepplusplus\Setting\Helpers::config('store_country_code'));
        $this->_upsDest($shippingAddress['postcode'], $shippingAddress['country_code']);
        $this->_upsRate(\Deepplusplus\Setting\Helpers::config('module_shipping_ups_pickup_method'));
        $this->_upsContainer(\Deepplusplus\Setting\Helpers::config('module_shipping_ups_packaging'));
        $this->_upsWeight($weight);
        $this->_upsRescom(\Deepplusplus\Setting\Helpers::config('module_shipping_ups_residential_delivery'));
        $upsQuote = $this->_upsGetQuote();

        $upsServices = json_decode(\Deepplusplus\Setting\Helpers::config('module_shipping_ups_services'));

        $stdrcd = false;

        $services = [];
        if (is_array($upsQuote) && count($upsQuote) > 0) {

            foreach ($upsQuote as $quote) {
                foreach ($quote as $type => $cost) {

                    //
                    if ($type === 'STD') {
                        if ($stdrcd) continue;
                        else $stdrcd = true;
                    }

                    if (!in_array($type, $upsServices)) continue;

                    $key = array_search($type, array_column($this->_upsServices, 'id'));
                    
                    $cost = preg_replace('/[^0-9.]/', '', $cost);
                    $services[] = [
                        'id' => strtoupper(str_replace('-', '_', $this->code . '_' . $type)),
                        'title' => $this->_upsServices[$key]['name'],
                        'cost' => $cost + ($cost*$this->calculateTax($shippingAddress)/100) + (float)\Deepplusplus\Setting\Helpers::config('module_shipping_ups_handling_fee')
                    ];

                }
            }
        } else {
            $services[] = [
                'title' => 'We are unable to obtain a rate quote for UPS shipping.<br>Please contact the store if no other alternative is shown.',
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

    /**
     * Set UPS Product Code
     *
     * @param string $prod
     */
    public function _upsProduct($prod)
    {
        $this->_upsProductCode = $prod;
    }

    /**
     * Set UPS Origin details
     *
     * @param string $postal
     * @param string $country
     */
    public function _upsOrigin($postal, $country){
        $this->_upsOriginPostalCode = substr($postal, 0, 5);
        $this->_upsOriginCountryCode = $country;
    }

    /**
     * Set UPS Destination information
     *
     * @param string $postal
     * @param string $country
     */
    public function _upsDest($postal, $country){

        $postal = str_replace(' ', '', $postal);

        if ($country === 'US') {
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
    public function _upsRate($foo) {
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
    * Sent request for quote to UPS via older HTML method
    *
    * @return array
    */
    public function _upsGetQuote() {
            
        // Return UPS multiple services
        $this->_upsActionCode = '4';

        $host = 'https://www.ups.com/using/services/rave/qcostcgi.cgi?';
        $request = implode('&', array('accept_UPS_license_agreement=yes',
                            '10_action=' . $this->_upsActionCode,
                            '13_product=' . $this->_upsProductCode,
                            '14_origCountry=' . $this->_upsOriginCountryCode,
                            '15_origPostal=' . $this->_upsOriginPostalCode,
                            '19_destPostal=' . $this->_upsDestPostalCode,
                            '22_destCountry=' . $this->_upsDestCountryCode,
                            '23_weight=' . $this->_upsPackageWeight,
                            '47_rate_chart=' . $this->_upsRateCode,
                            '48_container=' . $this->_upsContainerCode,
                            '49_residential=' . $this->_upsResComCode));
        $url = $host . $request;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Deepplusplus');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $error = curl_error($ch);

        if ($error > 0) {
            curl_setopt($ch, CURLOPT_URL, str_replace('tps:', 'tp:', $url));
            $response = curl_exec($ch);
            $error = curl_error($ch);
        }
        curl_close($ch);

        if ($error > 0 || $response == '') {
            $response = file_get_contents($url);
        }
        if ($response === false) {
            $response = file_get_contents(str_replace('tps:', 'tp:', $url));
        }
        if ($response === false) return false;

        $body = $response;

        // BOF: UPS
        /*
        TEST by checking out in the catalog; try a variety of shipping destinations to be sure
        your customers will be properly served.  If you are not getting any quotes, try enabling
        more alternatives in admin. Make sure your store's postal code is set in Admin ->
        Configuration -> Shipping/Packaging, since you won't get any quotes unless there is
        a origin that UPS recognizes.

        If you STILL don't get any quotes, here is a way to find out exactly what UPS is sending
        back in response to rate quote request, you can uncomment the following mail() line and
        then check your email after visiting the shipping page in checkout ...
        */
        //mail(STORE_OWNER_EMAIL_ADDRESS, 'UPS response', $body, 'From: <'.STORE_OWNER_EMAIL_ADDRESS.'>');

        // EOF: UPS

        $body_array = explode("\n", $body);

        /* //DEBUG ONLY
        $n = count($body_array);
        for ($i=0; $i<$n; $i++) {
        $result = explode('%', $body_array[$i]);
        print_r($result);
        }
        die('END');
        */

        $returnval = array();
        $errorret = 'error'; // only return 'error' if NO rates returned

        $n = count($body_array);
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

}