<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests fonctionnels du panier.
 */
class CartFeatureTest extends TestCase
{
    use RefreshDatabase;

    // ─── Accès ────────────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function guest_cannot_view_cart(): void
    {
        $this->get('/panier')->assertRedirect('/login');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function authenticated_user_can_view_cart(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/panier')->assertStatus(200);
    }

    // ─── Ajout au panier ──────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_add_to_cart(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);

        $response = $this->actingAs($user)->post('/panier', [
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cart_items', [
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function adding_same_product_twice_increments_quantity(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)->post('/panier', ['product_id' => $product->id, 'quantity' => 1]);
        $this->actingAs($user)->post('/panier', ['product_id' => $product->id, 'quantity' => 2]);

        $this->assertDatabaseHas('cart_items', [
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => 3,
        ]);
        $this->assertDatabaseCount('cart_items', 1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function add_to_cart_fails_with_invalid_product(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/panier', [
            'product_id' => 99999,
            'quantity'   => 1,
        ]);

        $response->assertSessionHasErrors('product_id');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function add_to_cart_fails_without_product_id(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/panier', []);

        $response->assertSessionHasErrors('product_id');
    }

    // ─── Mise à jour ──────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_update_cart_item_quantity(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create();
        $item    = CartItem::create([
            'user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->patch("/panier/{$item->id}", ['quantity' => 5]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 5]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_cannot_update_another_users_cart_item(): void
    {
        $user1   = User::factory()->create();
        $user2   = User::factory()->create();
        $product = Product::factory()->create();
        $item    = CartItem::create([
            'user_id' => $user1->id, 'product_id' => $product->id, 'quantity' => 1,
        ]);

        $response = $this->actingAs($user2)->patch("/panier/{$item->id}", ['quantity' => 5]);

        $this->assertContains($response->status(), [302, 403]);
        // Quantité inchangée
        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 1]);
    }

    // ─── Suppression ──────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_remove_cart_item(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create();
        $item    = CartItem::create([
            'user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->delete("/panier/{$item->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_cannot_delete_another_users_cart_item(): void
    {
        $user1   = User::factory()->create();
        $user2   = User::factory()->create();
        $product = Product::factory()->create();
        $item    = CartItem::create([
            'user_id' => $user1->id, 'product_id' => $product->id, 'quantity' => 1,
        ]);

        $response = $this->actingAs($user2)->delete("/panier/{$item->id}");

        $this->assertContains($response->status(), [302, 403]);
        $this->assertDatabaseHas('cart_items', ['id' => $item->id]);
    }
}
