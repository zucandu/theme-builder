<?php

class RewardPoint {

    /**
     * Hook into source code
     */
    public $availableHooks = ['process_data_after_order_completed', 'process_data_after_order_status_updated'];
    public $scopeGlobal = 0;
    public $scopeCategory = 1;
    public $scopeProduct = 2;
    public $scopeGroup = 3;
    public $scopeCustomer = 4;

    /**
     * Process the subscription data from order completed
     * @param Object $data paramenter from external websites
     * @param String $listener where the event/listener called
     * @return Boolean true/false
     * 
     */
    public function hook($data, $listener)
    {
        switch($listener) {
            case 'process_data_after_order_completed':
                $points = $this->displayRewardPointsEarned();
                \DB::table('reward_customer_points')->where('customer_id', \Request::user()->id)->updateOrInsert(
                    [
                        'customer_id' => \Request::user()->id
                    ],
                    [
                        'pending_points' => \DB::raw('pending_points + ' . $points)
                    ]
                );
                \DB::table('reward_logs')->insert([
                    'customer_id' => \Request::user()->id,
                    'order_id' => $data->order->id,
                    'points' => $points,
                    'message' => "Added {$points} pending points when order #{$data->order->id} completed",
                    'created_at' => \DB::raw('now()')
                ]);

                // Update reward points if reward point used
                $discount = \DB::table('order_discounts')->where([
                    'order_id' => $data->order->id,
                    'module' => 'RewardPointDiscount'
                ])->first();
                if($discount) {

                    $redeemRatio = $this->getRedeemRatio(\Request::user()->id);
                    $earnedPointUsed = $discount->amount/$redeemRatio;
                    
                    \DB::table('reward_customer_points')->where('customer_id', \Request::user()->id)->update([
                        'reward_points' => \DB::raw('reward_points - ' . $earnedPointUsed)
                    ]);
                    \DB::table('reward_logs')->insert([
                        'customer_id' => \Request::user()->id,
                        'order_id' => $data->order->id,
                        'points' => $earnedPointUsed*-1,
                        'message' => "Used {$earnedPointUsed} reward points for order #{$data->order->id}",
                        'created_at' => \DB::raw('now()')
                    ]);
                }

            break;
            
            case 'process_data_after_order_status_updated':
                // Transfer pending points to earned points when order is delivery
                if((int)$data->order->status === 5) {  
                    $reward = \DB::table('reward_logs')->where('order_id', $data->order->id)->first();
                    \DB::table('reward_customer_points')->where('customer_id', $reward->customer_id)->update([
                        'pending_points' => \DB::raw('pending_points - ' . $reward->points),
                        'reward_points' => \DB::raw('reward_points + ' . $reward->points)
                    ]);
                    \DB::table('reward_logs')->insert([
                        'customer_id' => $reward->customer_id,
                        'order_id' => $data->order->id,
                        'points' => $reward->points,
                        'message' => "Transferred {$reward->points} pending points to earned when order #{$data->order->id} deliveried",
                        'created_at' => \DB::raw('now()')
                    ]);
                }
            break;

        }
        return true;
    }

    /**
     * Get order reward points earned
     */
    public function displayRewardPointsEarned()
    {

        $order = \Request::session()->get('order');

        // Get reward points from the product prices 
        // and are calculated individually
        $rewardPoints = $this->GetRewardPoints($order['items']);
        
        // Global reward points
        //$GlobalRewardPointRatio = $this->GetGlobalRewardPointRatio();

		//$AdjustValue = GetRewardPointAdvancedCalculateValue();
        //$reward_points+=$AdjustValue*$GlobalRewardPointRatio;
        
        return $rewardPoints;
    }

    /**
     * Get global reward point
     * @return Number
     */
    public function GetGlobalRewardPointRatio()
    {
        return \DB::table('rewards')->select('point_ratio')->where('scope', 0)->value('point_ratio');
    }

    /**
     * Get total reward points for orders
     * @param Array $products
     * @return Int $rewardPoints
     */
    public function GetRewardPoints($products)
    {
        $rewardPoints = 0;
        foreach($products as $product) {
            $rewardPoints += $this->GetProductRewardPoints($product['id']) * $product['qty'];
        }

        return $rewardPoints;
    }

    /**
     * Get product reward points
     * @param Int $id
     * @return Number $rewardPoints
     */
    public function GetProductRewardPoints($id)
    {
        $rewardPoints = 0;
        $query = \DB::table('rewards')
        ->where('point_ratio', '>', 0)
        ->where(function($query) use($id) {
            $query->where('scope_id', $id)
            ->orWhere([
                ['scope_id', '=', \DB::table('product_category')->select('category_id')->where('product_id', $id)->value('category_id')],
                ['scope', '=', $this->scopeCategory],
            ])
            ->orWhere([
                ['scope', '=', $this->scopeGlobal]
            ]);
        })
        ->orderBy('scope', 'desc')->first();

        /* echo $sqlDebug = Str::replaceArray('?', $query->getBindings(), $query->toSql());
        die(); */
        
        if($query) {
            $rewardPoints = \Deepplusplus\Catalog\Product::displayPrice(\DB::table('products')->where('id', $id)->first());
            $rewardPoints *= $query->point_ratio;
        }
        
        return round($rewardPoints);
    }

    /**
     * Get redeem ratio for customer
     * @param Int $id customer id
     * @return Number $redeemRatio
     */
    public function getRedeemRatio($id)
    {
        $redeemRatio = 0.01;
        $query = \DB::table('rewards')
        ->where('redeem_ratio', '>', 0)
        ->where(function($query) use($id) {
            $query->where('scope_id', $id)
            ->orWhere([
                ['scope_id', '=', \DB::table('customers')->select('id')->where('id', $id)->value('id')],
                ['scope', '=', $this->scopeCustomer],
            ])
            ->orWhere([
                ['scope', '=', $this->scopeGlobal]
            ]);
        })
        ->orderBy('scope', 'desc')->first();

        if($query) {
            $redeemRatio = $query->redeem_ratio;
        }
        
        return $redeemRatio;
    }

    /**
     * Get earned points from customer
     * @param Int $id customer id
     * @return Int $earnedPoints
     */
    public function getEarnedPoints($id)
    {
        $points = \DB::table('reward_customer_points')->where('customer_id', $id)->select('reward_points')->value('reward_points');
        return (float)$points > 0 ? $points : 0;
    }

}