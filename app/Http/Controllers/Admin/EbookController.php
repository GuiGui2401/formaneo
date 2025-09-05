<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ebook;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class EbookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Ebook::query();
        
        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Filtrage par catégorie
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }
        
        // Filtrage par statut
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status);
        }
        
        $ebooks = $query->latest()->paginate(20);
        
        return view('admin.ebooks.index', compact('ebooks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.ebooks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'author' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'price' => 'nullable|numeric|min:0',
            'pages' => 'nullable|integer|min:1',
            'rating' => 'nullable|numeric|min:0|max:5',
            'is_active' => 'nullable|boolean',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'pdf_file' => 'nullable|mimes:pdf|max:10240',
        ]);
        
        $data = $request->only([
            'title', 'description', 'author', 'category', 'price', 
            'pages', 'rating', 'is_active'
        ]);
        
        // Valeurs par défaut
        $data['is_active'] = $request->boolean('is_active', true);
        $data['price'] = $request->price ?? 0;
        $data['rating'] = $request->rating ?? 0;
        $data['pages'] = $request->pages ?? null;
        
        // Générer le slug
        $data['slug'] = Str::slug($data['title']);
        
        // Traitement de l'image de couverture
        if ($request->hasFile('cover_image')) {
            $coverImage = $request->file('cover_image');
            $filename = 'ebook-cover-' . time() . '.' . $coverImage->getClientOriginalExtension();
            $path = $coverImage->storeAs('ebooks/covers', $filename, 'public');
            $data['cover_image_url'] = Storage::url($path);
        }
        
        // Traitement du fichier PDF
        if ($request->hasFile('pdf_file')) {
            $pdfFile = $request->file('pdf_file');
            $filename = 'ebook-' . time() . '.' . $pdfFile->getClientOriginalExtension();
            $path = $pdfFile->storeAs('ebooks/pdfs', $filename, 'public');
            $data['pdf_url'] = Storage::url($path);
        }
        
        $ebook = Ebook::create($data);
        
        return redirect()->route('admin.ebooks.show', $ebook)
            ->with('success', 'Ebook créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Ebook $ebook)
    {
        return view('admin.ebooks.show', compact('ebook'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ebook $ebook)
    {
        return view('admin.ebooks.edit', compact('ebook'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ebook $ebook)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'author' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'price' => 'nullable|numeric|min:0',
            'pages' => 'nullable|integer|min:1',
            'rating' => 'nullable|numeric|min:0|max:5',
            'is_active' => 'nullable|boolean',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'pdf_file' => 'nullable|mimes:pdf|max:10240',
        ]);
        
        $data = $request->only([
            'title', 'description', 'author', 'category', 'price', 
            'pages', 'rating', 'is_active'
        ]);
        
        // Générer le slug
        $data['slug'] = Str::slug($data['title']);
        
        // Traitement de l'image de couverture
        if ($request->hasFile('cover_image')) {
            // Supprimer l'ancienne image si elle existe
            if ($ebook->cover_image_url) {
                $oldPath = str_replace('/storage/', '', $ebook->cover_image_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            
            $coverImage = $request->file('cover_image');
            $filename = 'ebook-cover-' . time() . '.' . $coverImage->getClientOriginalExtension();
            $path = $coverImage->storeAs('ebooks/covers', $filename, 'public');
            $data['cover_image_url'] = Storage::url($path);
        }
        
        // Traitement du fichier PDF
        if ($request->hasFile('pdf_file')) {
            // Supprimer l'ancien PDF si il existe
            if ($ebook->pdf_url) {
                $oldPath = str_replace('/storage/', '', $ebook->pdf_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            
            $pdfFile = $request->file('pdf_file');
            $filename = 'ebook-' . time() . '.' . $pdfFile->getClientOriginalExtension();
            $path = $pdfFile->storeAs('ebooks/pdfs', $filename, 'public');
            $data['pdf_url'] = Storage::url($path);
        }
        
        $ebook->update($data);
        
        return redirect()->route('admin.ebooks.show', $ebook)
            ->with('success', 'Ebook mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ebook $ebook)
    {
        // Supprimer les fichiers associés
        if ($ebook->cover_image_url) {
            $oldPath = str_replace('/storage/', '', $ebook->cover_image_url);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }
        
        if ($ebook->pdf_url) {
            $oldPath = str_replace('/storage/', '', $ebook->pdf_url);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }
        
        $ebook->delete();
        
        return redirect()->route('admin.ebooks.index')
            ->with('success', 'Ebook supprimé avec succès.');
    }
}