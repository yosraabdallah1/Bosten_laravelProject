<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function createFromCart(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {

            $cartItems = $user->cartItems()->with('product')->get();

            if ($cartItems->isEmpty()) {
                throw new \Exception('Le panier est vide.');
            }

            // Vérifier le stock de chaque produit
            foreach ($cartItems as $item) {
                if ($item->product->stock < $item->quantity) {
                    throw new \Exception(
                        "Stock insuffisant pour : {$item->product->name}"
                    );
                }
            }

            // Calculer le total
            $total = $cartItems->sum(
                fn($item) => $item->product->price * $item->quantity
            );

            // Créer la commande
            $order = Order::create([
                'user_id' => $user->id,
                'total'   => $total,
                'status'  => 'pending',
                'address' => $data['address'],
                'phone'   => $data['phone'],
            ]);

            // Créer les lignes de commande + décrémenter le stock
            foreach ($cartItems as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'unit_price' => $item->product->price,
                ]);

                $item->product->decrement('stock', $item->quantity);
            }

            // Vider le panier
            app(CartService::class)->clearCart($user);

            return $order;
        });
    }
}
