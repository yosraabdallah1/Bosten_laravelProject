<?php

namespace Tests\Unit;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @covers \App\Services\CartService
 */
class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CartService();
        $this->user    = User::factory()->create();
    }

    // ─── addItem() ────────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function addItem_creates_new_cart_item(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $item = $this->service->addItem($this->user, $product->id, 2);

        $this->assertDatabaseHas('cart_items', [
            'user_id'    => $this->user->id,
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);
        $this->assertSame(2, $item->quantity);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function addItem_increments_quantity_for_existing_item(): void
    {
        $product = Product::factory()->create();

        $this->service->addItem($this->user, $product->id, 2);
        $this->service->addItem($this->user, $product->id, 3);

        $this->assertDatabaseHas('cart_items', [
            'user_id'    => $this->user->id,
            'product_id' => $product->id,
            'quantity'   => 5,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function addItem_defaults_quantity_to_1(): void
    {
        $product = Product::factory()->create();

        $item = $this->service->addItem($this->user, $product->id);

        $this->assertSame(1, $item->quantity);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function addItem_creates_separate_items_for_different_products(): void
    {
        $p1 = Product::factory()->create();
        $p2 = Product::factory()->create();

        $this->service->addItem($this->user, $p1->id, 1);
        $this->service->addItem($this->user, $p2->id, 1);

        $this->assertDatabaseCount('cart_items', 2);
    }

    // ─── getCartWithTotal() ───────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function getCartWithTotal_returns_correct_total(): void
    {
        $p1 = Product::factory()->create(['price' => 10.00]);
        $p2 = Product::factory()->create(['price' => 20.00]);

        CartItem::create(['user_id' => $this->user->id, 'product_id' => $p1->id, 'quantity' => 2]);
        CartItem::create(['user_id' => $this->user->id, 'product_id' => $p2->id, 'quantity' => 1]);

        $cart = $this->service->getCartWithTotal($this->user);

        $this->assertEqualsWithDelta(40.00, $cart['total'], 0.001); // 2*10 + 1*20
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getCartWithTotal_returns_correct_item_count(): void
    {
        $p1 = Product::factory()->create();
        $p2 = Product::factory()->create();

        CartItem::create(['user_id' => $this->user->id, 'product_id' => $p1->id, 'quantity' => 3]);
        CartItem::create(['user_id' => $this->user->id, 'product_id' => $p2->id, 'quantity' => 2]);

        $cart = $this->service->getCartWithTotal($this->user);

        $this->assertSame(5, $cart['count']); // 3 + 2
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getCartWithTotal_returns_zero_for_empty_cart(): void
    {
        $cart = $this->service->getCartWithTotal($this->user);

        $this->assertSame(0, $cart['total']);
        $this->assertSame(0, $cart['count']);
        $this->assertTrue($cart['items']->isEmpty());
    }

    // ─── updateItem() ─────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function updateItem_changes_quantity(): void
    {
        $product = Product::factory()->create();
        $item    = CartItem::create([
            'user_id'    => $this->user->id,
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $this->service->updateItem($item, 7);

        $this->assertDatabaseHas('cart_items', [
            'id'       => $item->id,
            'quantity' => 7,
        ]);
    }

    // ─── removeItem() ─────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function removeItem_deletes_the_item(): void
    {
        $product = Product::factory()->create();
        $item    = CartItem::create([
            'user_id'    => $this->user->id,
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $this->service->removeItem($item);

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    // ─── clearCart() ──────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function clearCart_removes_all_user_items(): void
    {
        $p1 = Product::factory()->create();
        $p2 = Product::factory()->create();

        CartItem::create(['user_id' => $this->user->id, 'product_id' => $p1->id, 'quantity' => 1]);
        CartItem::create(['user_id' => $this->user->id, 'product_id' => $p2->id, 'quantity' => 2]);

        $this->service->clearCart($this->user);

        $this->assertDatabaseCount('cart_items', 0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function clearCart_does_not_remove_other_users_items(): void
    {
        $otherUser = User::factory()->create();
        $product   = Product::factory()->create();

        CartItem::create(['user_id' => $otherUser->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->service->clearCart($this->user);

        $this->assertDatabaseCount('cart_items', 1);
    }
}
