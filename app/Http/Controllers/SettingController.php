<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Storage;
use DB;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $setting = json_decode(Storage::disk('public')->get('data/setting.json'), true);
        return response()->json($setting);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function selectLanguage($lang)
    {
        session(['locale' => $lang]);
        return response()->json(['lang' => $lang]);
    }

}
