<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ─── Routes publiques ────────────────────────────────────────────
Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/produits', [ProductController::class, 'index'])->name('products.index');
Route::get('/produits/{slug}', [ProductController::class, 'show'])->name('products.show');

// ─── Routes authentifiées (clients connectés) ────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    // Profil (généré par Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Panier
    Route::get('/panier', [CartController::class, 'index'])->name('cart.index');
    Route::post('/panier', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/panier/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/panier/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

    // Commandes
    Route::get('/commandes', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/commandes/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/commandes', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('orders.checkout');

    // Chatbot
    Route::get('/chatbot', [ChatbotController::class, 'index'])->name('chatbot.index');
    Route::post('/chatbot', [ChatbotController::class, 'ask'])->name('chatbot.ask');
    Route::post('/chatbot/clear', [ChatbotController::class, 'clear'])->name('chatbot.clear');
});

// ─── Routes admin ─────────────────────────────────────────────────
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Gestion des produits
        Route::get('/products', [ProductController::class, 'index_admin'])->name('products.index');
        Route::resource('products', ProductController::class)
            ->except(['index', 'show']);

        // Gestion des catégories
        Route::resource('categories', CategoryController::class);

        // Gestion des commandes
        Route::get('/orders', [OrderController::class, 'adminIndex'])->name('orders.index');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    });

// Routes auth générées par Breeze
require __DIR__.'/auth.php';


// ─── Démo de la navigation ───────────────────────────────
Route::get('/demo/navbar', function () {
    return view('demo.navbar-demo');
})->name('navbar.demo');