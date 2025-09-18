<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ebook;
use App\Models\UserEbook;
use Illuminate\Support\Facades\Storage;

class EbookController extends Controller
{
    // Liste des ebooks
    public function index(Request $request)
    {
        $ebooks = Ebook::active()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'ebooks' => $ebooks
        ]);
    }

    // Afficher un ebook spécifique
    public function show(Request $request, $id)
    {
        $ebook = Ebook::active()->findOrFail($id);
        $user = $request->user();
        
        // Vérifier si l'utilisateur a acheté l'ebook
        $userEbook = UserEbook::where('user_id', $user->id)
            ->where('ebook_id', $ebook->id)
            ->first();
            
        // Ajouter l'information d'achat à l'ebook
        $ebookData = $ebook->toArray();
        $ebookData['is_purchased'] = $userEbook ? true : false;

        return response()->json([
            'ebook' => $ebookData
        ]);
    }

    // Télécharger un ebook
    public function download(Request $request, $id)
    {
        $user = $request->user();
        $ebook = Ebook::active()->findOrFail($id);

        // Vérifier si l'utilisateur a acheté l'ebook
        $userEbook = UserEbook::where('user_id', $user->id)
            ->where('ebook_id', $ebook->id)
            ->first();

        if (!$userEbook && $ebook->price > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez acheter cet ebook pour le télécharger'
            ], 403);
        }

        // Incrémenter le compteur de téléchargements
        $ebook->increment('downloads');

        // Mettre à jour la date de téléchargement
        if ($userEbook) {
            $userEbook->update(['downloaded_at' => now()]);
        }

        // Vérifier que l'URL du PDF existe
        if (empty($ebook->pdf_url)) {
            return response()->json([
                'success' => false,
                'message' => 'Le fichier PDF n\'est pas disponible pour le moment'
            ], 404);
        }

        // Retourner l'URL du PDF
        return response()->json([
            'success' => true,
            'download_url' => $ebook->pdf_url
        ]);
    }
    
    // Consulter un ebook en ligne (sans téléchargement)
    public function view(Request $request, $id)
    {
        $user = $request->user();
        $ebook = Ebook::active()->findOrFail($id);

        // Vérifier si l'utilisateur a acheté l'ebook
        $userEbook = UserEbook::where('user_id', $user->id)
            ->where('ebook_id', $ebook->id)
            ->first();

        if (!$userEbook && $ebook->price > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez acheter cet ebook pour le consulter'
            ], 403);
        }

        // Vérifier que l'URL du PDF existe
        if (empty($ebook->pdf_url)) {
            return response()->json([
                'success' => false,
                'message' => 'Le fichier PDF n'est pas disponible pour le moment'
            ], 404);
        }

        // Retourner l'URL du PDF pour consultation en ligne
        return response()->json([
            'success' => true,
            'view_url' => $ebook->pdf_url
        ]);
    }

    // Acheter un ebook
    public function purchase(Request $request, $id)
    {
        $user = $request->user();
        $ebook = Ebook::active()->findOrFail($id);

        // Vérifier si l'ebook est gratuit
        if ($ebook->price <= 0) {
            // Créer l'entrée pour les ebooks gratuits
            UserEbook::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'ebook_id' => $ebook->id
                ],
                [
                    'price_paid' => 0,
                    'purchased_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Ebook obtenu avec succès',
                'download_url' => $ebook->pdf_url
            ]);
        }

        // Vérifier si l'utilisateur a déjà acheté l'ebook
        $userEbook = UserEbook::where('user_id', $user->id)
            ->where('ebook_id', $ebook->id)
            ->first();

        if ($userEbook) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà acheté cet ebook'
            ], 400);
        }

        // Vérifier le solde de l'utilisateur
        if ($user->balance < $ebook->price) {
            return response()->json([
                'success' => false,
                'message' => 'Solde insuffisant pour acheter cet ebook'
            ], 400);
        }

        // Débiter le solde de l'utilisateur
        $user->decrement('balance', $ebook->price);

        // Créer l'entrée d'achat
        $userEbook = UserEbook::create([
            'user_id' => $user->id,
            'ebook_id' => $ebook->id,
            'price_paid' => $ebook->price, // Enregistrer le prix payé
            'purchased_at' => now(),
        ]);

        // Créer une transaction
        $user->transactions()->create([
            'type' => 'ebook_purchase',
            'amount' => -$ebook->price,
            'description' => "Achat de l\'ebook {$ebook->title}",
            'status' => 'completed',
            'meta' => json_encode([
                'ebook_id' => $ebook->id,
                'ebook_title' => $ebook->title
            ])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ebook acheté avec succès',
            'download_url' => $ebook->pdf_url,
            'new_balance' => $user->balance
        ]);
    }

    // Recherche d'ebooks
    public function search(Request $request)
    {
        $query = $request->get('q');
        $category = $request->get('category');

        $ebooks = Ebook::active();

        if ($query) {
            $ebooks->where('title', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%")
                ->orWhere('author', 'like', "%{$query}%");
        }

        if ($category) {
            $ebooks->where('category', $category);
        }

        $ebooks = $ebooks->orderBy('created_at', 'desc')->get();

        return response()->json([
            'ebooks' => $ebooks
        ]);
    }

    // Catégories d'ebooks
    public function categories()
    {
        $categories = Ebook::active()
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->filter();

        return response()->json([
            'categories' => $categories
        ]);
    }
}
