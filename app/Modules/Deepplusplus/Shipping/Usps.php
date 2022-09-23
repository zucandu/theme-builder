<?php 

use App\Models\Deepplusplus\Module\ModuleZone;
use App\Models\Deepplusplus\Module\TaxClass;
use App\Models\Deepplusplus\Customer\Address;

/**
 * Class Rate.
 *
 */
class Usps
{
    
    private $code, $name, $description, 
            $order, $fields, $status;

    /**
     * Constructor
     */
    public function __construct() {

        $this->order = session('order');
        
        $this->code  = 'Usps';
        $this->name = 'USPS - United States Postal Service';
        $this->description = 'USPS - United States Postal Service';
    }

    /**
     * Check status
     */
    public function status($isBackend = false) {
        
        $this->status = \Deepplusplus\Setting\Helpers::config('module_shipping_usps_active') == 1 ? true : false;
        if($isBackend === true) {
            return $this->status;
        }

        if ($this->status === true && \Deepplusplus\Setting\Helpers::config('module_shipping_usps_shipping_zone')) {
            
            $isFlag = false;

            $shippingAddress = Address::find($this->order['profile']['default_shipping_address_id']);

            $modulezone = ModuleZone::findorFail(\Deepplusplus\Setting\Helpers::config('module_shipping_usps_shipping_zone'));
            $moduleregions = $modulezone->moduleregions()->where('country_code', $shippingAddress->country_code)->get();
            if(!$moduleregions->isEmpty()) {
                foreach($moduleregions as $region) {
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
        if(\Deepplusplus\Setting\Helpers::config('module_shipping_usps_tax_class') > 0) {
            $taxClass = TaxClass::with(['taxrates'])->where('id', \Deepplusplus\Setting\Helpers::config('module_shipping_usps_tax_class'))->first();
            foreach($taxClass->taxrates as $rate) {
                foreach($rate->zone->moduleregions as $region) {
                    if(($region->country_code === $shippingAddress['country_code']) && ($region->zone_code === $shippingAddress['zone_code'])) {
                        $taxRatePercentage = $rate->rate;
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

        // USPS doesnt accept zero weight send 1 ounce (0.0625) minimum
		$weight = ($weight < 0.0625 ? 0.0625 : $weight);
		$shippingPounds = floor($weight);
        $shippingOunces = (16 * ($weight - floor($weight)));
        
        // USPS currently cannot handle more than 5 digits on international
        $shippingOunces = round($shippingOunces, 2);
        
        // weight must be less than 35lbs and greater than 6 ounces or it is not machinable
        switch (true) {
            case ($shippingOunces == 0 and $shippingOunces < 6):
                // override admin choice too light
                $isMachinable = false;
                break;
            case ($weight > 35):
                // override admin choice too heavy
                $isMachinable = false;
                break;
            default:
                // admin choice on what to use
                $isMachinable = \Deepplusplus\Setting\Helpers::config('module_shipping_usps_machinable') === 'y' ? true : false;
        }
        
        $rate = new \USPS\Rate(\Deepplusplus\Setting\Helpers::hidden('module_shipping_usps_username'));
        // Create new package object and assign the properties
        // apartently the order you assign them is important so make sure
        // to set them as the example below
        // set the RatePackage for more info about the constants
        $package = new \USPS\RatePackage();

        $uspsServices = explode('|', \Deepplusplus\Setting\Helpers::config('module_shipping_usps_services'));
        foreach($uspsServices as $service) {
            $package->setService($service);
            $package->setFirstClassMailType(\Deepplusplus\Setting\Helpers::config('module_shipping_usps_mail_type'));
            $package->setZipOrigination(\Deepplusplus\Setting\Helpers::hidden('module_shipping_usps_origination_postcode'));
            $package->setZipDestination($shippingAddress['postcode']);
            $package->setPounds($shippingPounds);
            $package->setOunces($shippingOunces);
            $package->setContainer('');
            $package->setSize(\Deepplusplus\Setting\Helpers::config('module_shipping_usps_size'));
            $package->setField('Machinable', $isMachinable);
            $rate->addPackage($package);
        }
        
        $rate->getRate();
        
        $services = [];
        $response = $rate->getArrayResponse();

        if (count($response['RateV4Response']['Package']) > 0) {
            foreach($response['RateV4Response']['Package'] as $package) {
                if(!isset($package['Error']) && empty($package['Error'])) {
                    $packageId = substr(preg_replace('/-+/', '-', preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $package['Postage']['MailService']))), 0, 15);
                    $packageId = strtoupper(str_replace('-', '_', $this->code . '_' . $packageId));
                    if(array_search($packageId, array_column($services, 'id')) === false) {
                        $services[] = [
                            'id' => $packageId,
                            'title' => strip_tags(html_entity_decode($package['Postage']['MailService'])),
                            'cost' => $package['Postage']['Rate'] + ($package['Postage']['Rate']*$this->calculateTax($shippingAddress)/100) + (float)\Deepplusplus\Setting\Helpers::config('module_shipping_usps_handling_fee')
                        ];
                    }
                }/*  else { // It's not good if display the error message for method because customers can understand that the site is having issue
                    $services[] = [
                        'title' => $package['Error']['Description'],
                        'error' => true
                    ];
                } */
            }
        } else {
            $services[] = [
                'title' => $rate->getErrorMessage(),
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