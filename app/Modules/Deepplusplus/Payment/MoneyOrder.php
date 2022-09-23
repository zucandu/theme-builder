<?php

//use App\Helpers\Deepplusplus\Setting\Helpers;
use App\Models\Deepplusplus\Module\ModuleZone;
use App\Models\Deepplusplus\Customer\Address;
use App\Models\Deepplusplus\Order\OrderStatus;

/**
 * Class Rate.
 *
 */
class MoneyOrder
{

    public $code, $name, $description, $fields, $status;

    // Order details can get from \Request::session()->get('order')

    /**
     * Constructor
     */
    public function __construct() {
        $this->code  = 'MoneyOrder';
        $this->name = 'Check/Money Order';
        $this->description = "Customers can mail in their payment. Their order confirmation email will ask them to:\n\nPlease make your check or money order payable to:\n\n(your store name)\n\nMail your payment to:\nStore Name\nAddress\nCountry\nPhone\n\nYour order will not ship until we receive payment.";
    }
    
    /**
     * Check status
     */
    public function status($isBackend = false) {

        $this->status = \Deepplusplus\Setting\Helpers::config('module_payment_moneyorder_active') == 1 ? true : false;
        if($isBackend === true) {
            return $this->status;
        }

        if ($this->status === true && \Deepplusplus\Setting\Helpers::config('module_payment_moneyorder_payment_zone')) {
            
            $isFlag = false;

            $order = \Request::session()->get('order');

            $billingAddress = Address::find($order['profile']['default_billing_address_id']);

            $modulezone = ModuleZone::findorFail(\Deepplusplus\Setting\Helpers::config('module_shipping_free_shipping_zone'));
            $moduleregions = $modulezone->moduleregions()->where('country_code', $billingAddress->country_code)->get();
            if(!$moduleregions->isEmpty()) {
                foreach($moduleregions as $region) {
                    if(empty($region->zone_code) || ($region->zone_code == $billingAddress->zone_code)) {
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
     * Required function
     */
    public function selection() {
        return ['id' => $this->code, 'module' => $this->name];
    }

    /**
     * Required function
     * return the order status once the payment is completed
     */
    public function orderStatus() {
        return \Deepplusplus\Setting\Helpers::config('module_payment_moneyorder_order_status');
    }

    /**
     * Required function
     */
    public function process() {

    }

    /**
     * @param RequiredFunction
     * @param integer $orderId The latest order has been processed
     * @return boolean true/false Process the data from payment gateway. 
     * E.g. store the payment response into order comment
     */
    public function complete($orderId) {

        \DB::table('order_comments')->insert([
            'order_id' => $orderId,
            'order_status_id' => $this->orderStatus(),
            'note' => 'Thank you for ordered. We will update your order as soon as possible.',
            'created_at' => DB::raw('now()')
        ]);

        return true;
    }

}