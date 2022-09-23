<?php

//use App\Helpers\Deepplusplus\Setting\Helpers;
use App\Models\Deepplusplus\Module\ModuleZone;
use App\Models\Deepplusplus\Customer\Address;

/**
 * Class Rate.
 *
 */
class Flat
{
    
    public $code, $name, $description, 
            $order, $fields, $status;

    /**
     * Constructor
     */
    public function __construct() {

        $this->order = session('order');
        
        $this->code  = 'Flat';
        $this->name = 'Flat Rate';
        $this->description = 'Flat Rate';
    }

    /**
     * Check status
     */
    public function status($isBackend = false) {
        
        $this->status = \Deepplusplus\Setting\Helpers::config('module_shipping_flat_active') == 1 ? true : false;
        if($isBackend === true) {
            return $this->status;
        }

        if ($this->status === true && \Deepplusplus\Setting\Helpers::config('module_shipping_flat_shipping_zone')) {
            
            $isFlag = false;

            $shippingAddress = Address::find($this->order['profile']['default_shipping_address_id']);

            $modulezone = ModuleZone::findorFail(\Deepplusplus\Setting\Helpers::config('module_shipping_flat_shipping_zone'));
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
     * Get sub-total
     * @return $subTotal
     */
    public function orderSubTotal() {
        $subTotal = 0;
        $cartItems = $this->order['items'];
        foreach($cartItems as $item) {
            $subTotal += $item['price']*$item['qty'];
        }
        
        return $subTotal;
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
     * Returns rate
     *
     * @return array
     */
    public function calculate()
    {
        return (float)\Deepplusplus\Setting\Helpers::config('module_shipping_flat_shipping_cost');
    }

    /**
     * get quotes
     */
    public function quotes() {

        $quotes = [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'methods' => [
                [
                    'id' => $this->code,
                    'title' => $this->name,
                    'cost' => (float)\Deepplusplus\Setting\Helpers::config('module_shipping_flat_shipping_cost')
                ]
            ]
        ];

        return $quotes;
        
    }
}