<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;
use App\Models\UserPack;
use App\Models\UserEbook;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    // Add item to cart
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');
        $product = Product::active()->find($productId);

        if (!$product) {
            return response()->json(['message' => 'Product not found or not active'], 404);
        }

        $cart = $request->session()->get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'product' => $product->toArray(), // Store product details to avoid re-fetching
            ];
        }

        $request->session()->put('cart', $cart);

        return response()->json(['message' => 'Product added to cart', 'cart' => array_values($cart)]);
    }

    // Get cart contents
    public function index(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        return response()->json(['cart' => array_values($cart)]);
    }

    // Remove item from cart
    public function remove(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $productId = $request->input('product_id');
        $cart = $request->session()->get('cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
        }

        $request->session()->put('cart', $cart);

        return response()->json(['message' => 'Product removed from cart', 'cart' => array_values($cart)]);
    }

    // Checkout process
    public function checkout(Request $request)
    {
        $user = $request->user();
        $cart = $request->session()->get('cart', []);

        if (empty($cart)) {
            return response()->json(['message' => 'Your cart is empty'], 400);
        }

        $totalAmount = 0;
        foreach ($cart as $item) {
            $product = Product::find($item['product_id']);
            if (!$product || !$product->is_active) {
                return response()->json(['message' => 'One or more products in your cart are no longer available'], 400);
            }
            $totalAmount += ($product->is_on_promotion && $product->promotion_price !== null)
                ? $product->promotion_price * $item['quantity']
                : $product->price * $item['quantity'];
        }

        if ($user->balance < $totalAmount) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        DB::beginTransaction();
        try {
            $user->decrement('balance', $totalAmount);

            foreach ($cart as $item) {
                $product = Product::find($item['product_id']);
                $pricePaid = ($product->is_on_promotion && $product->promotion_price !== null)
                    ? $product->promotion_price
                    : $product->price;

                // Record purchase based on product category
                if ($product->category === 'formation_pack') {
                    UserPack::create([
                        'user_id' => $user->id,
                        'pack_id' => $product->metadata['original_id'],
                        'price_paid' => $pricePaid,
                        'purchased_at' => now(),
                    ]);
                } elseif ($product->category === 'ebook') {
                    UserEbook::create([
                        'user_id' => $user->id,
                        'ebook_id' => $product->metadata['original_id'],
                        'price_paid' => $pricePaid,
                        'purchased_at' => now(),
                    ]);
                }
                // Add other product types here if needed

                // Create transaction record
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'product_purchase',
                    'amount' => -$pricePaid * $item['quantity'],
                    'description' => 'Purchase of ' . $product->name . ' (x' . $item['quantity'] . ')',
                    'status' => 'completed',
                    'meta' => json_encode([
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'category' => $product->category,
                        'quantity' => $item['quantity'],
                        'price_per_unit' => $pricePaid,
                    ]),
                ]);
            }

            $request->session()->forget('cart'); // Clear cart after successful checkout
            DB::commit();

            return response()->json(['message' => 'Checkout successful', 'new_balance' => $user->balance]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Checkout failed', 'error' => $e->getMessage()], 500);
        }
    }

    // Traiter la commission de parrainage (à chaque paiement)
    private function processReferralCommission($user, $purchaseAmount)
    {
        if (!$user->referred_by) {
            return;
        }

        $referrer = User::find($user->referred_by);
        if (!$referrer) {
            return;
        }

        // Déterminer le montant de la commission en fonction du nombre d'affiliés
        $commissionAmount = $referrer->total_affiliates >= config('app.affiliate_premium_threshold', 100)
            ? config('app.commission_premium', 2500)
            : config('app.commission_basic', 2000);

        // Incrémenter le solde du parrain
        $referrer->increment('balance', $commissionAmount);
        $referrer->increment('total_commissions', $commissionAmount);

        // Vérifier si c'est le premier achat du filleul pour incrémenter le compteur
        $isFirstPurchase = $user->transactions()
            ->whereIn('type', ['pack_purchase', 'ebook_purchase', 'product_purchase'])
            ->count() === 1;

        if ($isFirstPurchase) {
            $referrer->increment('total_affiliates');
            $referrer->increment('monthly_affiliates');
        }

        // Créer une transaction pour la commission
        $referrer->transactions()->create([
            'type' => 'affiliate_commission',
            'amount' => $commissionAmount,
            'description' => "Commission pour l'achat de {$user->name}",
            'status' => 'completed',
            'meta' => json_encode([
                'referred_user_id' => $user->id,
                'referred_user_name' => $user->name,
                'purchase_amount' => $purchaseAmount,
                'is_first_purchase' => $isFirstPurchase
            ])
        ]);
    }
}
