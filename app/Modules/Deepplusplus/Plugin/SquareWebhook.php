<?php

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Deepplusplus\Order\OrderController;
use App\Http\Controllers\Deepplusplus\Store\ClientController;

class SquareWebhook {

    /**
     * Hook into source code
     */
    public $availableHooks = ['update_data_from_response'];

    /**
     * Process the subscription data from order completed
     * @param Object $webhook param from square
     * @param String $listener where the event/listener called
     * @return Boolean true/false
     * 
     */
    public function responsehandler($webhook, $listener)
    {

        switch($listener) {
            case 'update_data_from_response':
                //Storage::put('square-' . $webhook->data['type'] . '-' . time() . '.txt', json_encode($webhook->data));
				if(strpos(strtolower($webhook->data['header_user_agent']), 'square') !== false) {
                    
                    switch($webhook->data['type']) {
                        case 'invoice.created':
                            $this->createOrder($webhook->data['data'], $webhook->data['type']);
                            break;
                        
                        case 'invoice.payment_made':
                            $this->updateOrder($webhook->data['data'], $webhook->data['type']);
                            break;
                    }
                }
            break;
        }
    }

    /**
     * Create a new order from Square Webhook
     * @param array $data
     */
    public function createOrder($data, $type = null)
    {
        $sqInvoiceId = $data['object']['invoice']['id'];
        $sqCustomerId = $data['object']['invoice']['primary_recipient']['customer_id'];
        $sqSubscriptionId = $data['object']['invoice']['subscription_id'];
		
		// Check if order exists
		$isExist = DB::table('orders')->where('meta->sq_invoice_id', $sqInvoiceId)->exists();
		if($isExist === true) {
			return false;
		}
        
        $orderParams = [];
        
        $customer = DB::table('customers')->where('meta->sq_customer_id', $sqCustomerId)->first();
		$customer->store = DB::table('stores')->where('subscription_id', $sqSubscriptionId)->first();
        
        // Customer
        $orderParams['customer'] = [
            'id' => $customer->id,
            'firstname' => $customer->firstname,
            'lastname' => $customer->lastname,
            'email' => $customer->email,
        ];
        
        // The shipping address can be used in conjunction with the billing address
        $shippingAddress = DB::table('addresses')->where('id', $customer->default_shipping_address_id)->first();
        $orderParams['shipping_address'] = $orderParams['billing_address'] = [
            'name' => $shippingAddress->name,
            'address_line_1' => $shippingAddress->address_line_1,
            'city' => $shippingAddress->city,
            'postcode' => $shippingAddress->postcode,
            'zone_name' => $shippingAddress->zone_name,
            'country_name' => $shippingAddress->country_name,
            'phone' => $shippingAddress->phone,
        ];

        // Get Square subscription details to create product
        $orderProducts = [];
        $orderExtras = ['subtotal' => 0, 'shipping.cost' => 0, 'order_tax' => 0, 'total' => 0, 'shipping' => ['method' => 'No charge', 'cost' => 0]];
        $subscriptionDetails = $this->subscriptionDetails($customer);
        if($subscriptionDetails) {
            
            list($productName, $productPrice, $currency, $productSku) = array_values($subscriptionDetails['plan']);

            // Check product by name. If not exists, create a new
            $productId = DB::table('product_translations')->where('name', $productName)->select('product_id')->value('product_id');
            if((int)$productId === 0) {
                $productId = DB::table('products')->insertGetId([
                    'sku' => $productSku,
                    'price' => $productPrice,
                    'price_sorter' => $productPrice,
                    'quantity' => 9999999,
                    'visibility' => 0,
                    'created_at' => Carbon::now()
                ]);

                DB::table('product_translations')->insertGetId([
                    'product_id' => $productId,
                    'locale' => 'en',
                    'name' => $productName,
                    'created_at' => Carbon::now()
                ]);
            }

            $orderProducts[] = [
                'id' => $productId,
                'sku' => $productSku,
                'translations' => ['en' => ['name' => $productName, 'locale' => 'en']],
                'finalprice' => $productPrice,
                'qty' => 1
            ];

            $orderExtras['subtotal'] += $productPrice;
            $orderExtras['total'] += $productPrice;

        }

        if(count($orderProducts) > 0) {
            $orderParams['products'] = $orderProducts;
        }

        $orderParams = array_merge($orderParams, $orderExtras);

        // Create a new order
        $request = new Request();
        $request->merge($orderParams);
        $orderController = (new OrderController)->storeOrder($request);
        $order = $orderController->original['order'];
        
        // Add square invoice for order meta
        if(!empty($order->meta)) {
            $meta = json_decode($order->meta, true);
            $meta = array_merge($meta, ['sq_invoice_id' => $sqInvoiceId]);
        } else {
            $meta = ['sq_invoice_id' => $sqInvoiceId];
        }

        DB::table('orders')->where('id', $order->id)->update(['meta' => $meta]);

        return true;

    }

    /**
     * 
     */
    public function updateOrder($data, $type)
    {

        $sqInvoiceId = $data['object']['invoice']['id'];
        $sqSubscriptionId = $data['object']['invoice']['subscription_id'];

        $order = DB::table('orders')->where('meta->sq_invoice_id', $sqInvoiceId)->first();

        // Updated store expired_at
        $itemName = DB::table('order_items')->where('order_id', $order->id)->select('name')->value('name');
        $plan = explode('-', $itemName);
        if((int)$plan[0] > 0) {
            DB::table('stores')->where('subscription_id', $sqSubscriptionId)->update([
                'paid_at' => Carbon::now(),
                'expired_at' => Carbon::now()->addMonths($plan[0])->format('Y-m-d'),
                'notify_14' => 0,
                'notify_7' => 0,
                'notify_3' => 0,
            ]);
        }

        // Update order status
        DB::table('orders')->where('id', $order->id)->update(['status' => 3]);

        // Update all admin role
        //DB::table('admins')->where('role_id', 6)->update(['role_id' => 1]);
        
        // Add comment
        DB::table('order_comments')->insert([
            'order_id' => $order->id,
            'order_status_id' => 3, // Paid
            'hidden' => 1,
            'admin' => "System",
            'note' => "Paid #: {$sqInvoiceId}. Type: {$type}",
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return true;
    }
	
	/**
     * Get subscription details
     *
     * @return \Illuminate\Http\Response
     */
    public function subscriptionDetails($customer)
    {
        $meta = json_decode($customer->meta, true);
		
		$clientController = new ClientController;
	
        if($meta) {

            $subscriptionId = $customer->store->subscription_id;
            $subscription = [
                'subscription_id' => $subscriptionId,
                'expired_at' => Carbon::parse($customer->store->expired_at)->format('F d, Y'),
                'card' => [
                    'brand' => $meta['sq_card_brand'],
                    'last4' => $meta['sq_card_last4'],
                    'exp_month' => $meta['sq_exp_month'],
                    'exp_year' => $meta['sq_exp_year'],
                ]
            ];

            // Get subscription from Square
            $response = Http::withHeaders([
                'Square-Version' => '2021-09-15',
                'Authorization' => "Bearer {$clientController->getSquareAccessToken()}",
                'Content-Type' => 'application/json'
            ])->get("{$clientController->getSquareURL()}/subscriptions/{$subscriptionId}");
            
            if(!isset($response->json()['subscription'])) {
                return response()->json(['message' => "Subscription not found"], 422);
            }

            $sqSubscription = $response->json()['subscription'];

            $subscription = array_merge($subscription, [
                'sq_subscr_start_date' => Carbon::parse($sqSubscription['start_date'])->format('F d, Y'),
                'sq_subscr_canceled_date' => isset($sqSubscription['canceled_date']) ? Carbon::parse($sqSubscription['canceled_date'])->format('F d, Y') : null,
                'sq_subscr_status' => Str::ucfirst(Str::lower($sqSubscription['status']))
            ]);

            // Get plan from Square
            $planId = $sqSubscription['plan_id'];
            $response = Http::withHeaders([
                'Square-Version' => '2021-09-15',
                'Authorization' => "Bearer {$clientController->getSquareAccessToken()}",
                'Content-Type' => 'application/json'
            ])->get("{$clientController->getSquareURL()}/catalog/object/{$planId}");
            
            if(!isset($response->json()['object'])) {
                return response()->json(['message' => "Plan not found"], 422);
            }

            $sqPlan = $response->json()['object'];

            $subscription = array_merge($subscription, [
                'plan' => [
                    'name' => $sqPlan['subscription_plan_data']['name'],
                    'price' => $sqPlan['subscription_plan_data']['phases'][0]['recurring_price_money']['amount']/100, // decimal digit from Square
                    'currency' => $sqPlan['subscription_plan_data']['phases'][0]['recurring_price_money']['currency'],
                    'cadence' => $sqPlan['subscription_plan_data']['phases'][0]['cadence'],
                ]
            ]);

            return $subscription;
        }

        return false;
    }

}