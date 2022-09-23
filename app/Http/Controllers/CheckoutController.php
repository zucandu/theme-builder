<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json([
            'shippingModules' => [
                [
                    "code" => "Flat",
                    "name" => "Flat Rate",
                    "description" => "Flat Rate",
                    "methods" => [
                        [
                            "id" => "Flat",
                            "title" => "Flat Rate",
                            "cost" => 2.3
                        ]
                    ]
                ],
            ],
            'paymentModules' => [
                [
                    "id" => "MoneyOrder",
                    "module" => "Check/Money Order",
                    "image" => "paypal-visa-mastercard-american.credit-card.png",
                ]
            ],
            'discountModules' => [
                [
                    "id" => "CouponModule",
                    "module" => "Discount Coupon",
                    "fields" => [
                        [
                            "label" => "Coupon Code",
                            "name" => "coupon_code",
                            "input" => "text",
                            "placeholder" => "Please enter your coupon code"
                        ]
                    ]
                ]
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        var_dump($request->all());die;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
