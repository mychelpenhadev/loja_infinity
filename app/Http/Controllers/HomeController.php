<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $featuredProducts = Product::orderBy('created_at', 'desc')->take(1000)->get();
        $sliderProducts = Product::orderBy('created_at', 'desc')->take(10)->get();

        // Fetch banners server-side for instant loading
        $bannersConfig = \App\Models\Config::where('config_key', 'hero_banners')->first();
        $banners = [];
        if ($bannersConfig) {
            $banners = json_decode($bannersConfig->config_value, true) ?: [];
        }
        
        return view('home', compact('featuredProducts', 'sliderProducts', 'banners'));
    }
}
