<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cartService) {}

    public function index()
    {
        $cart = $this->cartService->getCartWithTotal(auth()->user());

        return view('cart.index', $cart);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'integer|min:1|max:99',
        ]);

        $this->cartService->addItem(
            auth()->user(),
            $request->product_id,
            $request->quantity ?? 1
        );

        return back()->with('success', 'Produit ajouté au panier !');
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $this->authorize('update', $cartItem);

        $request->validate(['quantity' => 'required|integer|min:1|max:99']);

        $this->cartService->updateItem($cartItem, $request->quantity);

        return back()->with('success', 'Panier mis à jour.');
    }

    public function destroy(CartItem $cartItem)
    {
        $this->authorize('delete', $cartItem);

        $this->cartService->removeItem($cartItem);

        return back()->with('success', 'Produit retiré du panier.');
    }
}
