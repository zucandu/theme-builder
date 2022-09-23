<?php

Class CouponModule {
    public $code, $name, $description,
            $fields, $status, $order;
    
    /**
     * Constructor
     */
    public function __construct() {

        $this->order = session('order');

        $this->code = 'CouponModule';
        $this->name = 'Discount Coupon';
        $this->description = 'This module support the discount for the order.';
    }
    
    /**
     * Config fields then show on the backend to set config
     */
    public function fields() {
        
        return $this->fields = [
            [
                'label' => 'Enable Discount Coupon', 
                'description' => 'Do you want to enable the discount coupon module?',
                'key' => 'module_discount_coupon_active',
                'value' => \Deepplusplus\Setting\Helpers::config('module_discount_coupon_active'),
                'input' => 'select',
                'options' => [
                    ['id' => 1, 'name' => 'Enabled'],
                    ['id' => 0, 'name' => 'Disabled']
                ]
            ],
            [
                'label' => 'Sort Order', 
                'description' => 'Sort order of display',
                'key' => 'module_discount_coupon_sort_order',
                'value' => \Deepplusplus\Setting\Helpers::config('module_discount_coupon_sort_order'),
                'input' => 'text'
            ],
        ];
    }

    /**
     * Check status
     */
    public function status($isBackend = false) {
        return \Deepplusplus\Setting\Helpers::config('module_discount_coupon_active') == 1 ? true : false;
    }

    /**
     * Required function
     */
    public function selection() {
        return [
            'id' => $this->code, 
            'module' => $this->name,
            'fields' => [
                [
                    'label' => 'Coupon Code', 
                    'name' => 'coupon_code',
                    'input' => 'text',
                    'placeholder' => 'Please enter your coupon code'
                ]
            ]
        ];
    }

    /**
     * process the discount coupon
     */
    public function process($data) {
        
        $coupon = DB::table('coupons')->where('code', $data['coupon_code'])->first();
        
        // Check coupon exists
        if(empty($coupon)) {
            return ['status' => 'error', 'message' => 'Invalid coupon code'];
        }
        
        // Validation minimum order
        $currency = DB::table('currencies')->where('code', $this->order['currency'])->first();
        $miniumOrder = \Deepplusplus\Setting\Currency::rateAdjusted($currency, $coupon->minimum_order);
        if($this->order['subtotal'] < $miniumOrder) {
            return ['status' => 'error', 'message' => sprintf('You must spend at least %s to redeem this coupon', $miniumOrder . ' ' . $this->order['currency'])];
        }

        // Check if customer used this coupon or not
        $usesPerUserCount = DB::table('orders')->whereIn('id', DB::table('order_discounts')->where('module_track_id', $coupon->id)->pluck('order_id')->toArray())->where('customer_id', request()->user()->id)->count();

        if($usesPerUserCount >= $coupon->uses_per_user && $coupon->uses_per_user > 0) {
            return ['status' => 'error', 'message' => sprintf('You have used coupon code: %s the maximum number of times allowed per customer', $coupon->uses_per_user)];
        }

        // Check the number of used coupon
        $usesPerCouponCount = DB::table('orders')->whereIn('id', DB::table('order_discounts')->where('module_track_id', $coupon->id)->pluck('order_id')->toArray())->count();

        if($usesPerCouponCount >= $coupon->uses_per_coupon && $coupon->uses_per_coupon > 0) {
            return ['status' => 'error', 'message' => sprintf('This coupon could only be used %s time(s)', $coupon->uses_per_coupon)];
        }

        if($coupon->started_at) {
            $dt = new DateTime($coupon->started_at);
            if(strtotime($dt->format('Y-m-d H:i:s')) > strtotime('now')) {
                return ['status' => 'error', 'message' => 'This coupon is not available yet'];
            }
        }

        if($coupon->expired_at) {
            $dt = new DateTime($coupon->expired_at);
            if(strtotime($dt->format('Y-m-d H:i:s')) < strtotime('now')) {
                return ['status' => 'error', 'message' => 'This coupon has expired'];
            }
        }
        
        // apply for zone here
        // to do later

        $couponDetails = [];
        $couponDetails['module_track_id'] = $coupon->id;

        $couponOrderTotal = $this->order['subtotal'];

        switch($coupon->type) {
            case 'P': // percentage
                $couponDetails['amount'] = number_format(round($couponOrderTotal*($coupon->amount/100), 2), 2);
                $couponDetails['type'] = $coupon->type;
                $ratio = $couponDetails['amount']/$couponOrderTotal;
            break;
            case 'F': // amount Off
                $amount = $currency->rateAdjusted($coupon->amount);
                $amount = ($amount > $couponOrderTotal) ? $couponOrderTotal : $amount;
                $couponDetails['amount'] = numbe_format(round($amount, 2), 2);//zen_round(($coupon->fields['coupon_amount'] > $orderTotalDetails['orderTotal'] ? $orderTotalDetails['orderTotal'] : $coupon->fields['coupon_amount']) * ($orderTotalDetails['orderTotal']>0) * $coupon_product_count, $currencyDecimalPlaces);
                $couponDetails['type'] = $coupon->type; // amount off 'F' or amount off and free shipping 'O'
                $ratio = $couponDetails['amount']/$couponOrderTotal;
            break;
        }
        
        return [
            'id' => $this->code, 
            'module' => "{$this->name} ({$coupon->code})",
            'details' => $couponDetails
        ];
    }

    /**
     * Calculate discount 
     */

}