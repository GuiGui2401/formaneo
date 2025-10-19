<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    /**
     * Obtenir toutes les informations de support
     */
    public function index()
    {
        $supportInfos = [
            [
                'type' => 'email',
                'label' => 'Email',
                'value' => Settings::getValue('support_email', 'support@formaneo.com'),
                'order' => 1,
            ],
            [
                'type' => 'phone',
                'label' => 'Téléphone',
                'value' => Settings::getValue('support_phone', '+225 XX XX XX XX XX'),
                'order' => 2,
            ],
            [
                'type' => 'whatsapp',
                'label' => 'WhatsApp',
                'value' => Settings::getValue('support_whatsapp', '+225XXXXXXXXXX'),
                'order' => 3,
            ],
        ];

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
