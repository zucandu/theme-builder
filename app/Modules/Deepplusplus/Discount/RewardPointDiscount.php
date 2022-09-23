<?php

use Illuminate\Support\Str;

Class RewardPointDiscount {
    public $code, $name, $description,
            $fields, $status, $order;

    /**
     * Constructor
     */
    public function __construct() {

        $this->code = 'RewardPointDiscount';
        $this->name = 'Reward Point';
        $this->description = 'This module support the discount for the order.';
    }
    
    /**
     * Config fields then show on the backend to set config
     */
    public function fields() {
        
        return $this->fields = [
            [
                'label' => 'Enable Reward Point', 
                'description' => 'Do you want to enable the discount coupon module?',
                'key' => 'module_discount_rewardpoint_active',
                'value' => \Deepplusplus\Setting\Helpers::config('module_discount_rewardpoint_active'),
                'input' => 'select',
                'options' => [
                    ['id' => 1, 'name' => 'Enabled'],
                    ['id' => 0, 'name' => 'Disabled']
                ]
            ],
            [
                'label' => 'Sort Order', 
                'description' => 'Sort order of display',
                'key' => 'module_discount_rewardpoint_sort_order',
                'value' => \Deepplusplus\Setting\Helpers::config('module_discount_rewardpoint_sort_order'),
                'input' => 'text'
            ],
        ];
    }

    /**
     * Check status
     */
    public function status($isBackend = false) {
        return \Deepplusplus\Setting\Helpers::config('module_discount_rewardpoint_active') == 1 ? true : false;
    }

    /**
     * Required function
     */
    public function selection() {

        $rewardPointClass = \App\Modules\Deepplusplus\Plugin\Plugin::init('RewardPoint');

        return [
            'id' => $this->code, 
            'module' => $this->name,
            'fields' => [
                [
                    'label' => 'Your current reward points',
                    'input' => 'readonly',
                    'value' => $rewardPointClass->getEarnedPoints(\Request::user()->id) . ' point(s)'
                ],
                [
                    'label' => 'The reward points can be obtained from this order',
                    'input' => 'readonly',
                    'value' => $rewardPointClass->displayRewardPointsEarned() . ' point(s)'
                ],
                [
                    'label' => 'Reward Point', 
                    'name' => 'redeem_reward_points',
                    'input' => 'text',
                    'placeholder' => 'Please redeem your reward points'
                ]
            ]
        ];
    }

    /**
     * process the discount coupon
     */
    public function process($data) {
        
        $customerId = \Request::user()->id;
        $order = \Request::session()->get('order');

        $rewardPointClass = \App\Modules\Deepplusplus\Plugin\Plugin::init('RewardPoint');
        $currentEarnedPoints = $rewardPointClass->getEarnedPoints($customerId);
        $redeemRatio = $rewardPointClass->getRedeemRatio($customerId);

        // Check coupon exists
        if(empty($currentEarnedPoints)) {
            return ['type' => 'error', 'message' => 'You have not enough reward points to redeem', 'status' => 401];
        }
        
        $redeemedPoints = $data['redeem_reward_points'];
        if($redeemedPoints > $currentEarnedPoints) {
            $redeemedPoints = $currentEarnedPoints;
        }
        $discountAmount = $redeemedPoints * $redeemRatio;
        if($discountAmount > $order['subtotal']) {
            $discountAmount = $order['subtotal'];
        }

        $discountDetails = [];
        $discountDetails['amount'] = $discountAmount;

        return [
            'id' => $this->code, 
            'module' => $this->name,
            'details' => $discountDetails
        ];
    }

    

}