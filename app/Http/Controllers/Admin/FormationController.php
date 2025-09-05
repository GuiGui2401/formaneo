<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use App\Models\FormationPack;
use App\Models\FormationModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FormationController extends Controller
{
    public function index(Request $request)
    {
        $query = Formation::with(['pack']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('pack', function ($packQuery) use ($search) {
                      $packQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('pack_id')) {
            $query->where('pack_id', $request->pack_id);
        }

        $formations = $query->latest()->paginate(20);
        $packs = FormationPack::where('is_active', true)->get();

        return view('admin.formations.index', compact('formations', 'packs'));
    }

    public function create()
    {
        $packs = FormationPack::where('is_active', true)->get();
        return view('admin.formations.create', compact('packs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pack_id' => 'required|exists:formation_packs,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'nullable|url',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'duration_minutes' => 'nullable|integer|min:0',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data = [
            'pack_id' => $request->pack_id,
            'title' => $request->title,
            'description' => $request->description,
            'video_url' => $request->video_url,
            'duration_minutes' => $request->duration_minutes ?? 0,
            'order' => $request->order ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ];

        // Traitement de l'image de couverture
        if ($request->hasFile('thumbnail')) {
            $thumbnail = $request->file('thumbnail');
            $filename = 'formation-thumbnail-' . time() . '.' . $thumbnail->getClientOriginalExtension();
            $path = $thumbnail->storeAs('formations/thumbnails', $filename, 'public');
            $data['thumbnail_url'] = Storage::url($path);
        }

        $formation = Formation::create($data);

        return redirect()->route('admin.formations.show', $formation)
            ->with('success', 'Formation créée avec succès.');
    }

    public function show(Formation $formation)
    {
        $formation->load(['pack', 'modules']);
        return view('admin.formations.show', compact('formation'));
    }

    public function edit(Formation $formation)
    {
        $packs = FormationPack::where('is_active', true)->get();
        return view('admin.formations.edit', compact('formation', 'packs'));
    }

    public function update(Request $request, Formation $formation)
    {
        $request->validate([
            'pack_id' => 'required|exists:formation_packs,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'nullable|url',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'duration_minutes' => 'nullable|integer|min:0',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data = [
            'pack_id' => $request->pack_id,
            'title' => $request->title,
            'description' => $request->description,
            'video_url' => $request->video_url,
            'duration_minutes' => $request->duration_minutes ?? $formation->duration_minutes,
            'order' => $request->order ?? $formation->order,
            'is_active' => $request->boolean('is_active'),
        ];

        // Traitement de l'image de couverture
        if ($request->hasFile('thumbnail')) {
            // Supprimer l'ancienne image si elle existe
            if ($formation->thumbnail_url) {
                $oldPath = str_replace('/storage/', '', $formation->thumbnail_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $thumbnail = $request->file('thumbnail');
            $filename = 'formation-thumbnail-' . time() . '.' . $thumbnail->getClientOriginalExtension();
            $path = $thumbnail->storeAs('formations/thumbnails', $filename, 'public');
            $data['thumbnail_url'] = Storage::url($path);
        }

        $formation->update($data);

        return redirect()->route('admin.formations.show', $formation)
            ->with('success', 'Formation mise à jour avec succès.');
    }

    public function destroy(Formation $formation)
    {
        // Supprimer l'image de couverture si elle existe
        if ($formation->thumbnail_url) {
            $oldPath = str_replace('/storage/', '', $formation->thumbnail_url);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $formation->delete();

        return redirect()->route('admin.formations.index')
            ->with('success', 'Formation supprimée avec succès.');
    }

    // Gestion des modules
    public function storeModule(Request $request, Formation $formation)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url',
            'duration_minutes' => 'nullable|integer|min:0',
            'order' => 'nullable|integer|min:0',
        ]);

        $module = $formation->modules()->create([
            'title' => $request->title,
            'content' => $request->content,
            'video_url' => $request->video_url,
            'duration_minutes' => $request->duration_minutes ?? 0,
            'order' => $request->order ?? 0,
            'is_active' => true,
        ]);

        return back()->with('success', 'Module ajouté avec succès.');
    }

    public function updateModule(Request $request, FormationModule $module)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url',
            'duration_minutes' => 'nullable|integer|min:0',
            'order' => 'nullable|integer|min:0',
        ]);

        $module->update([
            'title' => $request->title,
            'content' => $request->content,
            'video_url' => $request->video_url,
            'duration_minutes' => $request->duration_minutes ?? $module->duration_minutes,
            'order' => $request->order ?? $module->order,
        ]);

        return back()->with('success', 'Module mis à jour avec succès.');
    }

    public function destroyModule(FormationModule $module)
    {
        $module->delete();
        return back()->with('success', 'Module supprimé avec succès.');
    }
}
