<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormationPack;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class FormationPackController extends Controller
{
    public function index(Request $request)
    {
        $query = FormationPack::withCount(['formations']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('featured')) {
            $query->where('is_featured', $request->featured === 'yes');
        }

        if ($request->filled('promotion')) {
            if ($request->promotion === 'yes') {
                $query->where('is_on_promotion', true)
                      ->where(function ($q) {
                          $q->whereNull('promotion_starts_at')
                            ->orWhere('promotion_starts_at', '<=', now());
                      })
                      ->where(function ($q) {
                          $q->whereNull('promotion_ends_at')
                            ->orWhere('promotion_ends_at', '>=', now());
                      });
            } else {
                $query->where(function ($q) {
                    $q->where('is_on_promotion', false)
                      ->orWhere('promotion_starts_at', '>', now())
                      ->orWhere('promotion_ends_at', '<', now());
                });
            }
        }

        $packs = $query->latest()->paginate(20);

        return view('admin.formation-packs.index', compact('packs'));
    }

    public function create()
    {
        return view('admin.formation-packs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'total_duration' => 'nullable|integer|min:0',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;

        // Assurer l'unicité du slug
        while (FormationPack::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $data = [
            'name' => $request->name,
            'slug' => $slug,
            'author' => $request->author,
            'description' => $request->description,
            'price' => $request->price,
            'total_duration' => $request->total_duration ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'is_featured' => $request->boolean('is_featured'),
            'order' => $request->order ?? 0,
        ];

        // Gestion de l'upload de l'image
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('packs/thumbnails', 'public');
            $data['thumbnail_url'] = Storage::url($path);
        }

        $pack = FormationPack::create($data);

        return redirect()->route('admin.formation-packs.show', $pack)
            ->with('success', 'Pack de formation créé avec succès.');
    }

    public function show(FormationPack $pack)
    {
        $pack->load('formations.modules');
        
        $stats = [
            'formations_count' => $pack->formations()->count(),
            'modules_count' => $pack->formations()->withCount('modules')->get()->sum('modules_count'),
            'students_count' => $pack->students_count,
            'revenue' => abs(\App\Models\Transaction::where('type', 'purchase')
                ->whereJsonContains('meta->pack_id', $pack->id)
                ->sum('amount')),
        ];

        return view('admin.formation-packs.show', compact('pack', 'stats'));
    }

    public function edit(FormationPack $pack)
    {
        return view('admin.formation-packs.edit', compact('pack'));
    }

    public function update(Request $request, FormationPack $pack)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'total_duration' => 'nullable|integer|min:0',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'order' => 'nullable|integer|min:0',
            'rating' => 'nullable|numeric|between:0,5',
            'students_count' => 'nullable|integer|min:0',
        ]);

        $data = [
            'name' => $request->name,
            'author' => $request->author,
            'description' => $request->description,
            'price' => $request->price,
            'total_duration' => $request->total_duration ?? $pack->total_duration,
            'is_active' => $request->boolean('is_active'),
            'is_featured' => $request->boolean('is_featured'),
            'order' => $request->order ?? $pack->order,
            'rating' => $request->rating ?? $pack->rating,
            'students_count' => $request->students_count ?? $pack->students_count,
        ];

        // Si le nom a changé, régénérer le slug
        if ($request->name !== $pack->name) {
            $slug = Str::slug($request->name);
            $originalSlug = $slug;
            $counter = 1;

            while (FormationPack::where('slug', $slug)->where('id', '!=', $pack->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $data['slug'] = $slug;
        }

        // Gestion de l'upload de l'image
        if ($request->hasFile('thumbnail')) {
            // Supprimer l'ancienne image si elle existe
            if ($pack->thumbnail_url) {
                $oldPath = str_replace('/storage/', '', $pack->thumbnail_url);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('thumbnail')->store('packs/thumbnails', 'public');
            $data['thumbnail_url'] = Storage::url($path);
        }

        $pack->update($data);

        return redirect()->route('admin.formation-packs.show', $pack)
            ->with('success', 'Pack de formation mis à jour avec succès.');
    }

    public function destroy(FormationPack $pack)
    {
        // Supprimer l'image associée
        if ($pack->thumbnail_url) {
            $path = str_replace('/storage/', '', $pack->thumbnail_url);
            Storage::disk('public')->delete($path);
        }

        $pack->delete();

        return redirect()->route('admin.formation-packs.index')
            ->with('success', 'Pack de formation supprimé avec succès.');
    }

    public function toggleStatus(FormationPack $pack)
    {
        $pack->update(['is_active' => !$pack->is_active]);

        $status = $pack->is_active ? 'activé' : 'désactivé';
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Pack {$status} avec succès."
            ]);
        }
        
        return back()->with('success', "Pack {$status} avec succès.");
    }

    public function toggleFeatured(FormationPack $pack)
    {
        $pack->update(['is_featured' => !$pack->is_featured]);

        $status = $pack->is_featured ? 'mis en avant' : 'retiré de la mise en avant';
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Pack {$status} avec succès."
            ]);
        }
        
        return back()->with('success', "Pack {$status} avec succès.");
    }

    public function togglePromotion(Request $request, FormationPack $pack)
    {
        $request->validate([
            'promotion_price' => 'required_if:is_on_promotion,true|nullable|numeric|min:0',
            'promotion_starts_at' => 'nullable|date',
            'promotion_ends_at' => 'nullable|date|after:promotion_starts_at',
        ]);

        $data = [
            'is_on_promotion' => $request->boolean('is_on_promotion'),
            'promotion_price' => $request->promotion_price,
            'promotion_starts_at' => $request->promotion_starts_at ? \Carbon\Carbon::parse($request->promotion_starts_at) : null,
            'promotion_ends_at' => $request->promotion_ends_at ? \Carbon\Carbon::parse($request->promotion_ends_at) : null,
        ];

        $pack->update($data);

        $status = $pack->is_on_promotion ? 'activée' : 'désactivée';
        
        // Toujours retourner JSON pour les requêtes AJAX
        return response()->json([
            'success' => true,
            'message' => "Promotion {$status} avec succès."
        ]);
    }
}