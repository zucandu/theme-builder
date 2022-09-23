<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['prefix' => 'theme-builder'], function() {
    
    Route::get('billboard-all', 'BillboardController@index');

    Route::get('discover-listing', 'DiscoverController@show');
    Route::get('discover-category/{slug}', 'DiscoverController@category');
    Route::get('discover-search', 'DiscoverController@search');
    Route::get('discover-latest', 'DiscoverController@index');
    Route::get('discover-ids/{ids}', 'DiscoverController@GetPostIds');
    Route::get('discover-{slug}', 'DiscoverController@details');

    Route::get('item-widgets', 'ItemController@index');
    Route::get('nav-{key}', 'NavController@index');

    Route::post('login', 'AuthController@login');
    Route::post('register', 'AuthController@register');

    Route::get('account-profile', 'AccountController@profile');
    Route::get('account-orders', 'AccountController@orders');
    Route::get('orderdetails/{ref}', 'OrderController@index');
    Route::get('account-track-order/{id}', 'OrderController@edit');
    Route::post('account-wishlist-add', 'AccountController@store');
    Route::get('account-wishlist-list', 'AccountController@show');
    Route::get('country-list', 'CountryController@index');

    Route::get('cate-listing-{slug}', 'CateController@index');
    Route::get('manu-listing-{slug}', 'ManuController@index');
    Route::get('search-listing-{slug}', 'SearchController@index');

    Route::get('item-{id}/reviews', 'ProductController@show');
    Route::get('item/{slug}', 'ProductController@index');
    Route::group(['prefix' => 'shopping-cart'], function() {
        Route::post('add-product', 'ShoppingCartController@store');
        Route::post('update-product', 'ShoppingCartController@update');
        Route::get('remove-product/{id}', 'ShoppingCartController@destroy');
    });

    Route::post('checkout-init', 'CheckoutController@index');
    Route::post('checkout-process', 'CheckoutController@store');

});

Route::group(['prefix' => 'v1'], function() {
    Route::group(['prefix' => 'storefront'], function() {
        Route::get('blog/ids/{ids}', 'DiscoverController@ids');
        Route::get('product/ids/{ids}', 'ProductController@ids');
        Route::get('product/category-ids/{ids}', 'ProductController@cids');
    });
});
