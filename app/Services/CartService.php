<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\User;

class CartService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getCartWithTotal(User $user): array
    {
        $items = $user->cartItems()->with('product')->get();

        $total = $items->sum(fn ($item) => $item->product->price * $item->quantity);

        return [
            'items' => $items,
            'total' => $total,
            'count' => $items->sum('quantity'),
        ];
    }

    public function addItem(User $user, int $productId, int $quantity = 1): CartItem
    {
        $item = CartItem::firstOrNew([
            'user_id' => $user->id,
            'product_id' => $productId,
        ]);

        $item->quantity = ($item->exists ? $item->quantity : 0) + $quantity;
        $item->save();

        return $item;
    }

    public function updateItem(CartItem $item, int $quantity): void
    {
        $item->update(['quantity' => $quantity]);
    }

    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    public function clearCart(User $user): void
    {
        $user->cartItems()->delete();
    }
}
