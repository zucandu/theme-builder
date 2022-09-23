<?php

//use App\Helpers\Deepplusplus\Setting\Helpers;
use App\Models\Deepplusplus\Module\ModuleZone;
use App\Models\Deepplusplus\Customer\Address;

/**
 * Class Rate.
 *
 */
class Free
{
    
    public $code, $name, $description, 
            $order, $fields, $status;

    /**
     * Constructor
     */
    public function __construct() {

        $this->order = session('order');

        $this->code  = 'Free';
        $this->name = 'Free Options';
        $this->description = 'Free Options is used to display a Free Shipping option when other Shipping Modules are displayed. It can be based on: Always show, Order Total, Order Weight or Order Item Count. The Free Options module does not show when Free Shipper is displayed.';
    }
    
    /**
     * Config fields then show on the backend to set config
     */
    public function fields() {
        
        return $this->fields = [
            [
                'label' => 'Enable Free Options', 
                'description' => 'Enable/Disable the Free Options shipping method',
                'key' => 'module_shipping_free_active',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_free_active'),
                'input' => 'select',
                'options' => [
                    ['id' => 1, 'name' => 'Enabled'],
                    ['id' => 0, 'name' => 'Disabled']
                ]
            ],
            [
                'label' => 'Shipping Cost', 
                'description' => 'The shipping cost will be $0.00',
                'key' => 'module_shipping_free_shipping_cost',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_free_shipping_cost'),
                'input' => 'text'
            ],
            [
                'label' => 'Handling Fee', 
                'description' => 'Handling fee for this shipping method.',
                'key' => 'module_shipping_free_handle_fee',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_free_handle_fee'),
                'input' => 'text'
            ],
            [
                'label' => 'Total >=', 
                'description' => 'Free Shipping when Total >=',
                'key' => 'module_shipping_free_total_condition',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_free_total_condition'),
                'input' => 'text'
            ],
            [
                'label' => 'Weight >=', 
                'description' => 'Free Shipping when Weight >=',
                'key' => 'module_shipping_free_weight_condition',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_free_weight_condition'),
                'input' => 'text'
            ],
            [
                'label' => 'Item Count >=', 
                'description' => 'Free Shipping when Item Count >=',
                'key' => 'module_shipping_free_item_count_condition',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_free_item_count_condition'),
                'input' => 'text'
            ],
            [
                'label' => 'Shipping Zone', 
                'description' => 'If a zone is selected, only enable this shipping method for that zone.',
                'key' => 'module_shipping_free_shipping_zone',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_free_shipping_zone'),
                'options' => ModuleZone::all(),
                'input' => 'select'
            ],
            [
                'label' => 'Sort Order', 
                'description' => 'Sort order of display',
                'key' => 'module_shipping_free_sort_order',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_free_sort_order'),
                'input' => 'text'
            ],
        ];
    }

    /**
     * Check status
     */
    public function status($isBackend = false) {

        $this->status = \Deepplusplus\Setting\Helpers::config('module_shipping_free_active') == 1 ? true : false;
        if($isBackend === true) {
            return $this->status;
        }

        if ($this->status === true && \Deepplusplus\Setting\Helpers::config('module_shipping_free_shipping_zone')) {
            
            $isFlag = false;

            $shippingAddress = Address::find($this->order['profile']['default_shipping_address_id']);

            $modulezone = ModuleZone::findorFail(\Deepplusplus\Setting\Helpers::config('module_shipping_free_shipping_zone'));
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

        $orderSubtotal = $this->orderSubTotal();
        $orderTotalWeight = $this->orderTotalWeight();
        $orderNumberOfItems = $this->orderNumberOfItems();

        // Check condition of Sub-total
        $totalCondition = \Deepplusplus\Setting\Helpers::config('module_shipping_free_total_condition');
        if(!empty($totalCondition) && ($orderSubtotal < $totalCondition) && ($this->status === true)) {
            $this->status = false;
        }

        // Check condition of order weight
        $totalWeightCondition = \Deepplusplus\Setting\Helpers::config('module_shipping_free_weight_condition');
        if(!empty($totalWeightCondition) && ($orderTotalWeight < $totalWeightCondition) && ($this->status === true)) {
            $this->status = false;
        }

        // Check condition of order number of items
        $totalItemCountCondition = \Deepplusplus\Setting\Helpers::config('module_shipping_free_item_count_condition');
        if(!empty($totalItemCountCondition) && ($orderNumberOfItems < $totalItemCountCondition) && ($this->status === true)) {
            $this->status = false;
        }

        return (int)\Deepplusplus\Setting\Helpers::config('module_shipping_free_shipping_cost') + (int)\Deepplusplus\Setting\Helpers::config('module_shipping_free_handle_fee');
    }

    /**
     * get quotes
     */
    public function quotes() {

        $quotes = [];

        if($this->status === true) {
            $quotes = [
                'code' => $this->code,
                'name' => $this->name,
                'description' => $this->description,
                'methods' => [
                    [
                        'id' => $this->code,
                        'title' => $this->name,
                        'cost' => $this->calculate()
                    ]
                ]
            ];
        }

        return $quotes;
        
    }
}