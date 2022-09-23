<?php

/**
 * Set up a free online store that syncs with your inventory and your social media—to help you meet customers wherever they shop.
 * Send professional invoices, track them in real time, and accept payments online—all from one place. Save time with auto-billing, 
 * stored information, and powerful reporting tools.
 */
class Square
{

    public $code, $name, $description, $fields, $status, $response;

    /**
     * Constructor
     */
    public function __construct() {
        $this->code  = 'Square';
        $this->name = 'Square';
        $this->description = "Explore Square Payments, or create an account instantly and start accepting payments.";
    }

    /**
     * @param RequiredFunction
     * @param boolean true/false Check the backend/admin
     * @return boolean true/false Check the status of payment gateway
     */
    public function status($isBackend = false) {

        $this->status = \Deepplusplus\Setting\Helpers::config('module_payment_square_active') == 1 ? true : false;
        if($isBackend === true) {
            return $this->status;
        }

        if ($this->status === true && \Deepplusplus\Setting\Helpers::config('module_payment_square_payment_zone')) {
            
            $isFlag = false;

            $order = \Request::session()->get('order');

            $billingAddress = \DB::table('addresses')->where('id', $order['profile']['default_billing_address_id'])->first();

            $modulezone = \DB::table('module_zones')
                                ->select('module_zone_regions.country_code', 'module_zone_regions.zone_code')
                                ->join('module_zone_regions', 'module_zones.id', '=', 'module_zone_regions.module_zone_id')
                                ->where([
                                    'module_zones.id' => (int)\Deepplusplus\Setting\Helpers::config('module_payment_square_payment_zone'),
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
        return \Deepplusplus\Setting\Helpers::config('module_payment_square_order_status');
    }

    /**
     * @param RequiredFunction
     * @param array $params The order data when submit to the payment gateway
     */
    public function process($params) {

        // Access token from Square
        $accessToken = \Deepplusplus\Setting\Helpers::hidden('module_payment_square_sanbox_access_token');
        if(\Deepplusplus\Setting\Helpers::config('module_payment_square_environment') === 'production') {
            $accessToken = \Deepplusplus\Setting\Helpers::hidden('module_payment_square_production_access_token');
        }

        $zerodecimal = $this->zeroDecimal($params['currency']);

        $postVars = json_encode([
            'source_id' => $params['nonce'],
            'idempotency_key' => $params['idempotency_key'],
            'verification_token' => $params['verification_token'],
            'amount_money' => [
                'amount' => round($params['total']*$zerodecimal),
                'currency' => $params['currency']
            ],
            'note' => "A new payment from customer #{$params['profile']['id']} {$params['profile']['firstname']} {$params['profile']['lastname']}",
            'buyer_email_address' => $params['profile']['email'],
            'location_id' => \Deepplusplus\Setting\Helpers::config('module_payment_square_location_id'),
            'autocomplete' => true
        ]);
        
        $url = 'https://connect.squareupsandbox.com/v2/payments';
        if(\Deepplusplus\Setting\Helpers::config('module_payment_square_environment') === 'production') {
            $url = 'https://connect.squareup.com/v2/payments';
        }
        
        $response = Http::withHeaders([
            'Square-Version' => '2021-09-15',
            'Authorization' => "Bearer {$accessToken}",
            'Content-Type' => 'application/json'
        ])->post($url, [
            'source_id' => $params['nonce'],
            'idempotency_key' => $params['idempotency_key'],
            'verification_token' => $params['verification_token'],
            'amount_money' => [
                'amount' => round($params['total']*$zerodecimal),
                'currency' => $params['currency']
            ],
            'note' => "A new payment from customer #{$params['profile']['id']} {$params['profile']['firstname']} {$params['profile']['lastname']}",
            'buyer_email_address' => $params['profile']['email'],
            'location_id' => \Deepplusplus\Setting\Helpers::config('module_payment_square_location_id'),
            'autocomplete' => true
        ]);
        
        $jsonResp = $response->json();
        if(isset($jsonResp['errors'])) {
            $msg = "";
            foreach($jsonResp['errors'] as $error) {
                $msg .= $error['detail'] . "\n";
            }
            
            return ['status' => 'error', 'message' => $msg];
        }

        $this->response = $jsonResp;

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

        $zerodecimal = $this->zeroDecimal($this->response['payment']['amount_money']['currency']);
        $amount = number_format($this->response['payment']['amount_money']['amount']/$zerodecimal, 2) . " {$this->response['payment']['amount_money']['currency']}";

        $note = "Payment ID: {$this->response['payment']['id']}. Status: {$this->response['payment']['status']}. Receipt: {$this->response['payment']['receipt_number']}. Amount: {$amount}. Created at: {$this->response['payment']['created_at']}";

        DB::table('order_comments')->insert([
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