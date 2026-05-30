<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private CartService  $cartService,
    ) {}

    // ── Client ───────────────────────────────────────────────

    public function checkout()
    {
        $cart = $this->cartService->getCartWithTotal(auth()->user());

        if ($cart['items']->isEmpty()) {
            return redirect()->route('cart.index')
                             ->with('error', 'Votre panier est vide.');
        }

        return view('orders.checkout', $cart);
    }

    public function store(Request $request)
    {
        $request->validate([
            'address' => 'required|string|max:500',
            'phone'   => 'required|string|max:20',
        ]);

        try {
            $order = $this->orderService->createFromCart(
                auth()->user(),
                $request->only(['address', 'phone'])
            );

            return redirect()->route('orders.show', $order)
                             ->with('success', 'Commande passée avec succès !');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function index()
    {
        $orders = auth()->user()
                        ->orders()
                        ->with('items.product')
                        ->latest()
                        ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        $order->load('items.product');

        return view('orders.show', compact('order'));
    }

    // ── Admin ────────────────────────────────────────────────

    public function adminIndex()
    {
        $orders = Order::with('user')
                       ->latest()
                       ->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,shipped,delivered,cancelled',
        ]);

        $order->update(['status' => $request->status]);

        return back()->with('success', 'Statut mis à jour.');
    }
}
