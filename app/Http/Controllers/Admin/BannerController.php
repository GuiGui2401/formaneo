<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromotionalBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = PromotionalBanner::orderBy('order')->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:image,video,document',
            'file' => 'required|file|max:20480', // 20MB max
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
        ]);

        $file = $request->file('file');
        $path = $file->store('banners', 'public');
        $fileUrl = Storage::url($path);

        PromotionalBanner::create([
            'name' => $request->title, // Ajouter le champ name
            'title' => $request->title,
            'type' => $request->type,
            'file_path' => $path,
            'file_url' => $fileUrl,
            'url' => $fileUrl, // Ajouter le champ url
            'description' => $request->description,
            'order' => $request->order ?? 0,
            'is_active' => true,
        ]);

        return redirect()->route('admin.banners.index')
            ->with('success', 'Bannière créée avec succès.');
    }

    public function edit(PromotionalBanner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, PromotionalBanner $banner)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:image,video,document',
            'file' => 'nullable|file|max:20480',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
        ]);

        $data = [
            'name' => $request->title,
            'title' => $request->title,
            'type' => $request->type,
            'description' => $request->description,
            'order' => $request->order ?? 0,
        ];

        if ($request->hasFile('file')) {
            // Supprimer l'ancien fichier
            if ($banner->file_path) {
                Storage::disk('public')->delete($banner->file_path);
            }

            $file = $request->file('file');
            $path = $file->store('banners', 'public');
            $fileUrl = Storage::url($path);
            $data['file_path'] = $path;
            $data['file_url'] = $fileUrl;
            $data['url'] = $fileUrl;
        }

        $banner->update($data);

        return redirect()->route('admin.banners.index')
            ->with('success', 'Bannière mise à jour avec succès.');
    }

    public function destroy(PromotionalBanner $banner)
    {
        // Supprimer le fichier
        if ($banner->file_path) {
            Storage::disk('public')->delete($banner->file_path);
        }

        $banner->delete();

        return redirect()->route('admin.banners.index')
            ->with('success', 'Bannière supprimée avec succès.');
    }

    public function toggleStatus(PromotionalBanner $banner)
    {
        $banner->update(['is_active' => !$banner->is_active]);

        return redirect()->route('admin.banners.index')
            ->with('success', 'Statut de la bannière modifié avec succès.');
    }
}
