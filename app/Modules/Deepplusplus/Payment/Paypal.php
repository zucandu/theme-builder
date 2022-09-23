<?php

class Paypal
{

    public $code, $name, $description, $fields, $status, $image = 'paypal-visa-mastercard-american.credit-card.png';

    // Order details can get from \Request::session()->get('order')

    /**
     * Constructor
     */
    public function __construct() {
        $this->code  = 'Paypal';
        $this->name = 'Paypal';
        $this->description = "PayPal is an electronic commerce company that facilitates payments between parties through online transfers.";
    }

    /**
     * @param RequiredFunction
     * @param boolean true/false Check the backend/admin
     * @return boolean true/false Check the status of payment gateway
     */
    public function status($isBackend = false) {

        $this->status = \Deepplusplus\Setting\Helpers::config('module_payment_paypal_active') == 1 ? true : false;
        if($isBackend === true) {
            return $this->status;
        }

        if ($this->status === true && \Deepplusplus\Setting\Helpers::config('module_payment_paypal_payment_zone')) {
            
            $isFlag = false;

            $order = \Request::session()->get('order');

            $billingAddress = \DB::table('addresses')->where('id', $order['profile']['default_billing_address_id'])->first();

            $modulezone = \DB::table('module_zones')
                                ->select('module_zone_regions.country_code', 'module_zone_regions.zone_code')
                                ->join('module_zone_regions', 'module_zones.id', '=', 'module_zone_regions.module_zone_id')
                                ->where([
                                    'module_zones.id' => (int)\Deepplusplus\Setting\Helpers::config('module_payment_paypal_payment_zone'),
                                    'module_zone_regions.country_code' => $billingAddress->country_code
                                ])->get();

            if(!$modulezone->isEmpty()) {
                foreach($modulezone as $region) {
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
     * @param RequiredFunction
     * @return array $selection Display the radio on the checkout page
     */
    public function selection() {
        return ['id' => $this->code, 'module' => $this->name, 'image' => $this->image];
    }

    /**
     * @param RequiredFunction
     * @return int $status The order status when payment processed
     */
    public function orderStatus() {
        return \Deepplusplus\Setting\Helpers::config('module_payment_paypal_order_status');
    }
    
    /**
     * @param RequiredFunction
     * @param array $params The order data when submit to the payment gateway
     */
    public function process() {
        return true;
    }

    /**
     * @param RequiredFunction
     * @param integer $orderId The latest order has been processed
     * @param array $request all orders 
     * @return boolean true/false Process the data from payment gateway. 
     * E.g. store the payment response into order comment
     */
    public function complete($orderId, $request) {
        if(isset($request['transaction'])) {

            $transaction = $request['transaction'];
            
            DB::table('order_comments')->insert([
                'order_id' => $orderId,
                'order_status_id' => $this->orderStatus(),
                'hidden' => 1,
                'note' => "ID: {$transaction['id']}. Status: {$transaction['status']}. Amount: {$transaction['purchase_units'][0]['amount']['value']} {$transaction['purchase_units'][0]['amount']['currency_code']}"
            ]);
        }

    }

}