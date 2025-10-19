<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChallengeController extends Controller
{
    /**
     * Afficher la liste des défis
     */
    public function index()
    {
        $challenges = Challenge::orderBy('order', 'asc')->paginate(20);

        return view('admin.challenges.index', compact('challenges'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        return view('admin.challenges.create');
    }

    /**
     * Enregistrer un nouveau défi
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'reward' => 'required|numeric|min:0',
            'target' => 'nullable|integer|min:1',
            'icon_name' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
            'order' => 'nullable|integer',
        ]);

        // Gestion de l'upload d'image
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('challenges', 'public');
            $validated['image_url'] = Storage::url($imagePath);
        }

        // Définir l'ordre si non fourni
        if (!isset($validated['order'])) {
            $validated['order'] = Challenge::max('order') + 1;
        }

        $validated['is_active'] = $request->has('is_active');

        Challenge::create($validated);

        return redirect()->route('admin.challenges.index')
            ->with('success', 'Défi créé avec succès');
    }

    /**
     * Afficher un défi spécifique
     */
    public function show($id)
    {
        $challenge = Challenge::with('users')->findOrFail($id);

        return view('admin.challenges.show', compact('challenge'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        $challenge = Challenge::findOrFail($id);

        return view('admin.challenges.edit', compact('challenge'));
    }

    /**
     * Mettre à jour un défi
     */
    public function update(Request $request, $id)
    {
        $challenge = Challenge::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'reward' => 'required|numeric|min:0',
            'target' => 'nullable|integer|min:1',
            'icon_name' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
            'order' => 'nullable|integer',
        ]);

        // Gestion de l'upload d'image
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image si elle existe
            if ($challenge->image_url) {
                $oldPath = str_replace('/storage/', '', $challenge->image_url);
                Storage::disk('public')->delete($oldPath);
            }

            $imagePath = $request->file('image')->store('challenges', 'public');
            $validated['image_url'] = Storage::url($imagePath);
        }

        $validated['is_active'] = $request->has('is_active');

        $challenge->update($validated);

        return redirect()->route('admin.challenges.index')
            ->with('success', 'Défi mis à jour avec succès');
    }

    /**
     * Supprimer un défi
     */
    public function destroy($id)
    {
        $challenge = Challenge::findOrFail($id);

        // Supprimer l'image si elle existe
        if ($challenge->image_url) {
            $oldPath = str_replace('/storage/', '', $challenge->image_url);
            Storage::disk('public')->delete($oldPath);
        }

        $challenge->delete();

        return redirect()->route('admin.challenges.index')
            ->with('success', 'Défi supprimé avec succès');
    }

    /**
     * Activer/Désactiver un défi
     */
    public function toggleActive($id)
    {
        $challenge = Challenge::findOrFail($id);
        $challenge->is_active = !$challenge->is_active;
        $challenge->save();

        return redirect()->route('admin.challenges.index')
            ->with('success', 'Statut du défi mis à jour');
    }
}
