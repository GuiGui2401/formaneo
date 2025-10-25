<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
    
            public function store(Request $request)
    
            {
    
                if ($request->hasFile('file')) {
    
                                        Log::info('Uploaded file MIME type (store)', ['mime_type' => $request->file('file')->getMimeType()]);
    
                                    }
    
                    
    
                                    $validated = $request->validate([
    
                    
    
                                'name' => 'required|string|max:255',                       
    
                                'description' => 'nullable|string',
    
                                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    
                                'file' => 'nullable|file|mimetypes:application/pdf,video/mp4,video/quicktime,video/x-msvideo,application/vnd.android.package-archive,application/zip|max:102400',
            'price' => 'required|numeric|min:0',
            'promotion_price' => 'nullable|numeric|lt:price',
            'is_on_promotion' => '',
            'category' => 'required|string|max:255',
            'is_active' => '',
            'metadata' => 'nullable|json',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_on_promotion'] = (bool) $request->input('is_on_promotion');
        $validated['is_active'] = (bool) $request->input('is_active');

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $validated['image_url'] = Storage::url($imagePath);
        }

        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');
            $originalName = $uploadedFile->getClientOriginalName();
            $originalExtension = $uploadedFile->getClientOriginalExtension();
            $detectedMimeType = $uploadedFile->getMimeType();

            $uniqueFileName = time() . '_' . pathinfo($originalName, PATHINFO_FILENAME);
            $finalExtension = $originalExtension;

            // If detected as zip but original was apk, ensure .apk extension
            if ($detectedMimeType === 'application/zip' && strtolower($originalExtension) === 'apk') {
                $finalExtension = 'apk';
            }
            
            $fileNameToStore = $uniqueFileName . '.' . $finalExtension;
            $filePath = 'products/files/' . $fileNameToStore;

            Storage::disk('local')->putFileAs('products/files', $uploadedFile, $fileNameToStore);
            $validated['file_path'] = $filePath;
            Log::info('AdminProductController@store: File path saved', ['file_path' => $filePath]);
        }

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
        if ($request->hasFile('file')) {
            Log::info('Uploaded file MIME type (update)', ['mime_type' => $request->file('file')->getMimeType()]);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'file' => 'nullable|file|mimetypes:application/pdf,video/mp4,video/quicktime,video/x-msvideo,application/vnd.android.package-archive,application/zip|max:102400',
            'price' => 'required|numeric|min:0',
            'promotion_price' => 'nullable|numeric|lt:price',
            'is_on_promotion' => '',
            'category' => 'required|string|max:255',
            'is_active' => '',
            'metadata' => 'nullable|json',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_on_promotion'] = (bool) $request->input('is_on_promotion');
        $validated['is_active'] = (bool) $request->input('is_active');

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image_url) {
                Storage::disk('public')->delete(Str::after($product->image_url, '/storage/'));
            }
            $imagePath = $request->file('image')->store('products', 'public');
            $validated['image_url'] = Storage::url($imagePath);
        }

        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($product->file_path) {
                Storage::disk('local')->delete($product->file_path);
            }
            $uploadedFile = $request->file('file');
            $originalName = $uploadedFile->getClientOriginalName();
            $originalExtension = $uploadedFile->getClientOriginalExtension();
            $detectedMimeType = $uploadedFile->getMimeType();

            $uniqueFileName = time() . '_' . pathinfo($originalName, PATHINFO_FILENAME);
            $finalExtension = $originalExtension;

            // If detected as zip but original was apk, ensure .apk extension
            if ($detectedMimeType === 'application/zip' && strtolower($originalExtension) === 'apk') {
                $finalExtension = 'apk';
            }
            
            $fileNameToStore = $uniqueFileName . '.' . $finalExtension;
            $filePath = 'products/files/' . $fileNameToStore;

            Storage::disk('local')->putFileAs('products/files', $uploadedFile, $fileNameToStore);
            $validated['file_path'] = $filePath;
            Log::info('AdminProductController@update: File path saved', ['file_path' => $filePath]);
        }

        $product->update($validated);

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        if ($product->image_url) {
            Storage::disk('public')->delete(Str::after($product->image_url, '/storage/'));
        }
        if ($product->file_path) {
            Storage::disk('local')->delete($product->file_path);
        }
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }
}
