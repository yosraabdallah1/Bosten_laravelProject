<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests fonctionnels des commandes.
 */
class OrderFeatureTest extends TestCase
{
    use RefreshDatabase;

    // ─── Accès ────────────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function guest_cannot_view_orders(): void
    {
        $this->get('/commandes')->assertRedirect('/login');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function authenticated_user_can_view_order_history(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/commandes')->assertStatus(200);
    }

    // ─── Checkout ─────────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_cannot_checkout_with_empty_cart(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/checkout');

        $response->assertRedirect('/panier');
        $response->assertSessionHas('error');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_checkout_with_products_in_cart(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create(['price' => 20.00, 'stock' => 10]);

        CartItem::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);

        $response = $this->actingAs($user)->get('/checkout');

        $response->assertStatus(200);
    }

    // ─── Création de commande ─────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_place_order(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create(['price' => 15.00, 'stock' => 10]);
        CartItem::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);

        $response = $this->actingAs($user)->post('/commandes', [
            'address' => '10 rue des Plantes, Tunis',
            'phone'   => '+216 20 000 000',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status'  => 'pending',
            'total'   => 30.00,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function order_creation_decrements_stock(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);
        CartItem::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 3]);

        $this->actingAs($user)->post('/commandes', [
            'address' => 'Tunis',
            'phone'   => '12345678',
        ]);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 7]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function order_creation_clears_cart(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);
        CartItem::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($user)->post('/commandes', [
            'address' => 'Tunis',
            'phone'   => '12345678',
        ]);

        $this->assertDatabaseCount('cart_items', 0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function order_fails_validation_without_address(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/commandes', ['phone' => '12345678']);

        $response->assertSessionHasErrors('address');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function order_fails_validation_without_phone(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/commandes', ['address' => 'Tunis']);

        $response->assertSessionHasErrors('phone');
    }

    // ─── Afficher commande ────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_view_own_order(): void
    {
        $user  = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/commandes/{$order->id}");

        $response->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_cannot_view_other_users_order(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2)->get("/commandes/{$order->id}");

        $this->assertContains($response->status(), [302, 403]);
    }

    // ─── Admin : gestion commandes ────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_can_view_all_orders(): void
    {
        $admin = User::factory()->admin()->create();
        Order::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/admin/orders');

        $response->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_can_update_order_status(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->pending()->create();

        $response = $this->actingAs($admin)->patch("/admin/orders/{$order->id}/status", [
            'status' => 'confirmed',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'confirmed']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_cannot_set_invalid_order_status(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->create();

        $response = $this->actingAs($admin)->patch("/admin/orders/{$order->id}/status", [
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors('status');
    }
}
