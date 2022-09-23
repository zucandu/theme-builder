<?php

//use App\Helpers\Deepplusplus\Setting\Helpers;

class Stripe
{

    public $code, $name, $description, $fields, $status, $response;

    // Order details can get from \Request::session()->get('order')

    /**
     * Constructor
     */
    public function __construct() {
        $this->code  = 'Stripe';
        $this->name = 'Stripe';
        $this->description = "Explore Stripe Payments, or create an account instantly and start accepting payments.";
    }

    /**
     * @param RequiredFunction
     * @param boolean true/false Check the backend/admin
     * @return boolean true/false Check the status of payment gateway
     */
    public function status($isBackend = false) {

        $this->status = \Deepplusplus\Setting\Helpers::config('module_payment_stripe_active') == 1 ? true : false;
        if($isBackend === true) {
            return $this->status;
        }

        if ($this->status === true && \Deepplusplus\Setting\Helpers::config('module_payment_stripe_payment_zone')) {
            
            $isFlag = false;

            $order = \Request::session()->get('order');

            $billingAddress = \DB::table('addresses')->where('id', $order['profile']['default_billing_address_id'])->first();

            $modulezone = \DB::table('module_zones')
                                ->select('module_zone_regions.country_code', 'module_zone_regions.zone_code')
                                ->join('module_zone_regions', 'module_zones.id', '=', 'module_zone_regions.module_zone_id')
                                ->where([
                                    'module_zones.id' => (int)\Deepplusplus\Setting\Helpers::config('module_payment_stripe_payment_zone'),
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
        return ['id' => $this->code, 'module' => $this->name];
    }

    /**
     * @param RequiredFunction
     * @return int $status The order status when payment processed
     */
    public function orderStatus() {
        $orderStatus = \Deepplusplus\Setting\Helpers::config('module_payment_stripe_order_status');
        if((int)\Deepplusplus\Setting\Helpers::config('module_payment_stripe_subscription_ignore_first_payment') === 1) {
            $orderStatus = 1; // Pending
        }
        return $orderStatus;
    }

    /**
     * @param RequiredFunction
     * @param array $params The order data when submit to the payment gateway
     */
    public function process($params) {
        
        // If subscription trial set, ignore the first charge
        $this->response = [
            'subscr_source' => $params['stripetoken']
        ];

        if($params['total'] > 0) {
            // Set your secret key. Remember to switch to your live secret key in production.
            // See your keys here: https://dashboard.stripe.com/account/apikeys
            $apiKey = \Deepplusplus\Setting\Helpers::config('module_payment_stripe_environment') === 'live' ? \Deepplusplus\Setting\Helpers::hidden('module_payment_stripe_live_secret_key') : \Deepplusplus\Setting\Helpers::hidden('module_payment_stripe_testing_secret_key');

            $stripe = new \Stripe\StripeClient($apiKey);
            
            // All API requests expect amounts to be provided in a currency’s smallest unit. For example, to charge 10 USD, provide an amount value of 1000 (i.e., 1000 cents).
            $zerodecimal = $this->zeroDecimal($params['currency']);

            // Stripe charge
            $this->response = $stripe->charges->create([
                'amount' => $params['total']*$zerodecimal,
                'currency' => $params['currency'],
                'description' => "New order from {$params['profile']['firstname']} {$params['profile']['lastname']} #{$params['profile']['id']}",
                'source' => $params['stripetoken']['id'],
            ]);
        }

        return $this->response;

    }

    /**
     * @param RequiredFunction
     * @param integer $orderId The latest order has been processed
     * @return boolean true/false Process the data from payment gateway. 
     * E.g. store the payment response into order comment
     */
    public function complete($orderId) {

        // Return false if response is empty
        if(empty($this->response)) {
            return false;
        }
        
        $zerodecimal = $this->zeroDecimal($this->response->currency);
        $amount = number_format($this->response->amount/$zerodecimal, 2) . " " . strtoupper($this->response->currency);
        $isPaid = $this->response->paid === true ? 'Yes' : 'No';
        $note = "ID: {$this->response->id}. Amount: {$amount}. Status: {$this->response->status}. Paid: {$isPaid}. Created: {$this->response->created}";

        \DB::table('order_comments')->insert([
            'order_id' => $orderId,
            'order_status_id' => $this->orderStatus(),
            'hidden' => 1,
            'note' => $note
        ]);

        return true;
    }

    /**
     * All monetary fields in Square APIs are represented by a Money object with an amount field in 
     * the smallest denomination of the currency indicated by currency_code. 
     */
    public function zeroDecimal($currency)
    {
        $zerodecimal = 100;
        if(in_array($currency, ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'])) {
            $zerodecimal = 1;
        }

        return $zerodecimal;
    }

}