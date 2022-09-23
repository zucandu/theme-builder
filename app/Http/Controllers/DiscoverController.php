<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Storage;
use DB;

class DiscoverController extends Controller
{
    public function index()
    {
        $posts = json_decode(Storage::disk('public')->get('data/discover-latest.json'), true);
        return response()->json($posts);
    }

    public function show()
    {
        return response()->json(json_decode(Storage::disk('public')->get('data/article-listing.json'), true));
    }

    public function details()
    {
        return response()->json(json_decode(Storage::disk('public')->get('data/article-details.json'), true));
    }

    public function category()
    {
        return response()->json(json_decode(Storage::disk('public')->get('data/article-category-listing.json'), true));
    }

    public function ids()
    {
        return response()->json(json_decode(Storage::disk('public')->get("data/discover-ids.json"), true));
    }

    public function search()
    {
        return response()->json(json_decode(Storage::disk('public')->get('data/article-category-listing.json'), true));
    }

}
