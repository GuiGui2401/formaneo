<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
            'price' => 'required|numeric|min:0',
            'promotion_price' => 'nullable|numeric|lt:price',
            'is_on_promotion' => 'boolean',
            'category' => 'required|string|max:255',
            'is_active' => 'boolean',
            'metadata' => 'nullable|json',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_on_promotion'] = $request->has('is_on_promotion');
        $validated['is_active'] = $request->has('is_active');

        Product::create($validated);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
            'price' => 'required|numeric|min:0',
            'promotion_price' => 'nullable|numeric|lt:price',
            'is_on_promotion' => 'boolean',
            'category' => 'required|string|max:255',
            'is_active' => 'boolean',
            'metadata' => 'nullable|json',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_on_promotion'] = $request->has('is_on_promotion');
        $validated['is_active'] = $request->has('is_active');

        $product->update($validated);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }
}
