<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great! 
|
*/

/**
 * App vue component
 */
Route::get('/{any}', function() {
    $configs = json_decode(Storage::disk('public')->get("data/configs.json"), true);
    return view('themebuilder', ['configs' => $configs]);
})->where('any', '.*');

