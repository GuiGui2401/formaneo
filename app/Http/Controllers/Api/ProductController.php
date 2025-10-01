<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::active()->get();
        return response()->json(['products' => $products]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // This will be implemented later for dashboard management
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::active()->findOrFail($id);
        return response()->json(['product' => $product]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // This will be implemented later for dashboard management
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // This will be implemented later for dashboard management
    }
}
