<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportInfo;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    /**
     * Obtenir toutes les informations de support
     */
    public function index()
    {
        $supportInfos = SupportInfo::active()
            ->ordered()
            ->get();

        return response()->json([
            'support_info' => $supportInfos,
        ]);
    }

    /**
     * Envoyer une demande de support
     */
    public function submitRequest(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'category' => 'nullable|string',
        ]);

        // TODO: Implémenter l'envoi d'email ou l'enregistrement en base
        // Pour l'instant, on retourne simplement un succès

        return response()->json([
            'success' => true,
            'message' => 'Votre demande a été envoyée avec succès. Nous vous répondrons dans les plus brefs délais.',
        ]);
    }
}
