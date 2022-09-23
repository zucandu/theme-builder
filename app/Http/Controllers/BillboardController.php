<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Storage;
use DB;

class BillboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $billboards = json_decode(Storage::disk('public')->get('data/billboard-all.json'), true);
        return response()->json($billboards);
    }
}
