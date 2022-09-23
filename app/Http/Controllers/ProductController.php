<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($slug)
    {
        return response()->json(json_decode(Storage::disk('public')->get("data/product-details.json"), true));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->json(json_decode(Storage::disk('public')->get("data/product-reviews.json"), true));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function ids($id)
    {
        if(!in_array($id, ['39_40', '35_36'])) {
            $id = "39_40";
        }
        return response()->json(json_decode(Storage::disk('public')->get("data/product-ids-{$id}.json"), true));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function cids($id)
    {
        return response()->json(json_decode(Storage::disk('public')->get("data/category-ids-{$id}.json"), true));
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
