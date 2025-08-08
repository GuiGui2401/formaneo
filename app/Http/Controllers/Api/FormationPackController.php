<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FormationPack;
use App\Models\Formation;

class FormationPackController extends Controller
{
    public function index(Request $request)
    {
        $packs = FormationPack::where('is_active', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('order')
            ->paginate(12);

        return response()->json($packs);
    }

    public function show($slug)
    {
        $pack = FormationPack::where('slug', $slug)
            ->with(['formations.modules'])
            ->firstOrFail();

        return response()->json($pack);
    }

    // acheter un pack (simplifié)
    public function purchase(Request $request, $id)
    {
        $pack = FormationPack::findOrFail($id);
        $user = $request->user();

        // déduction basique du solde (améliore ensuite avec transaction/Payment gateway)
        if ($user->balance < $pack->price) {
            return response()->json(['error'=>'Solde insuffisant'], 400);
        }

        $user->balance -= $pack->price;
        $user->save();

        // création d'une transaction
        $user->transactions()->create([
            'type' => 'purchase',
            'amount' => $pack->price,
            'meta' => json_encode(['pack_id'=>$pack->id, 'pack_name'=>$pack->name])
        ]);

        return response()->json(['message'=>'Achat effectué', 'balance'=>$user->balance]);
    }
}
