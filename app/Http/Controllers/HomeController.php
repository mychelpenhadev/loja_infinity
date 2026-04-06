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

        return view('home', compact('featuredProducts', 'sliderProducts'));
    }
}
