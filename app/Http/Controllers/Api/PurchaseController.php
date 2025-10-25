<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Log;

class PurchaseController extends Controller
{
    /**
     * Lister les produits achetés par l'utilisateur.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $purchaseTransactions = Transaction::where('user_id', $user->id)
            ->where('type', 'product_purchase')
            ->latest()
            ->get();

        $purchasedItems = $purchaseTransactions->map(function ($transaction) {
            if ($transaction->product) {
                return [
                    'id' => $transaction->product->id,
                    'name' => $transaction->product->name,
                    'description' => $transaction->product->description,
                    'image_url' => $transaction->product->image_url,
                    'file_path' => $transaction->product->file_path,
                    'category' => $transaction->product->category,
                    'purchased_at' => $transaction->created_at->toDateTimeString(),
                    // Ajoutez d'autres champs si nécessaire
                ];
            }
            return null;
        })->filter(); // Supprimer les éléments null si un produit n'est pas trouvé

        return response()->json(['data' => $purchasedItems]);
    }

    /**
     * Télécharger le fichier associé à un produit acheté.
     */
    public function download(Request $request, $productId)
    {
        $user = $request->user();
        Log::info('PurchaseController@download: User ID', ['user_id' => $user->id]);
        Log::info('PurchaseController@download: Product ID', ['product_id' => $productId]);


        // Fallback for token-based auth via query parameter (for web-based downloads)
        if (!$user && $request->has('token')) {
            $token = PersonalAccessToken::findToken($request->query('token'));
            if ($token) {
                $user = $token->tokenable;
            }
        }

        if (!$user) {
            Log::warning('PurchaseController@download: Unauthenticated user');
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $product = Product::findOrFail($productId);

        // Log all product_purchase transactions for the user
        $allProductPurchases = Transaction::where('user_id', $user->id)
                                        ->where('type', 'product_purchase')
                                        ->get();
        Log::info('PurchaseController@download: All product_purchase transactions for user', [
            'user_id' => $user->id,
            'transactions' => $allProductPurchases->toArray()
        ]);


        // Vérifier si l'utilisateur a acheté ce produit
        $hasPurchased = false;
        foreach ($allProductPurchases as $transaction) {
            $meta = is_string($transaction->meta) ? json_decode($transaction->meta, true) : $transaction->meta;
            if (is_array($meta) && array_key_exists('product_id', $meta) && $meta['product_id'] == $productId) {
                $hasPurchased = true;
                break;
            }
        }

        Log::info('PurchaseController@download: hasPurchased result (manual check)', ['hasPurchased' => $hasPurchased]);

        if (!$hasPurchased) {
            Log::warning('PurchaseController@download: Product not purchased or access denied', [
                'user_id' => $user->id,
                'product_id' => $productId,
                'transactions_found_by_manual_check' => $allProductPurchases->filter(function ($transaction) use ($productId) {
                    $meta = is_string($transaction->meta) ? json_decode($transaction->meta, true) : $transaction->meta;
                    return is_array($meta) && array_key_exists('product_id', $meta) && $meta['product_id'] == $productId;
                })->toArray()
            ]);
            return response()->json(['message' => 'Product not purchased or access denied.'], 403);
        }

        // Vérifier si le fichier existe
        if (empty($product->file_path) || !Storage::disk('local')->exists($product->file_path)) {
            Log::error('PurchaseController@download: File not found', [
                'product_id' => $productId,
                'file_path' => $product->file_path
            ]);
            return response()->json(['message' => 'File not found.'], 404);
        }

        // Retourner le fichier pour le téléchargement
        $filePath = Storage::disk('local')->path($product->file_path);
        $fileName = basename($product->file_path);
        $mimeType = Storage::disk('local')->mimeType($product->file_path);

        return response()->download($filePath, $fileName, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
        ]);
    }
}
