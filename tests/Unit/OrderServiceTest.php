<?php

namespace Tests\Unit;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @covers \App\Services\OrderService
 */
class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OrderService();
        $this->user    = User::factory()->create();
    }

    private function addToCart(Product $product, int $qty): void
    {
        CartItem::create([
            'user_id'    => $this->user->id,
            'product_id' => $product->id,
            'quantity'   => $qty,
        ]);
    }

    // ─── createFromCart() — succès ────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function createFromCart_creates_order_with_correct_total(): void
    {
        $p1 = Product::factory()->create(['price' => 15.00, 'stock' => 10]);
        $p2 = Product::factory()->create(['price' => 25.00, 'stock' => 10]);

        $this->addToCart($p1, 2);
        $this->addToCart($p2, 1);

        $order = $this->service->createFromCart($this->user, [
            'address' => '12 rue des Plantes, Tunis',
            'phone'   => '+216 20 000 000',
        ]);

        $this->assertEqualsWithDelta(55.00, $order->total, 0.001); // 2*15 + 1*25
        $this->assertSame('pending', $order->status);
        $this->assertSame($this->user->id, $order->user_id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function createFromCart_creates_order_items(): void
    {
        $product = Product::factory()->create(['price' => 30.00, 'stock' => 5]);
        $this->addToCart($product, 3);

        $order = $this->service->createFromCart($this->user, [
            'address' => 'Tunis',
            'phone'   => '12345678',
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => 3,
            'unit_price' => 30.00,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function createFromCart_decrements_product_stock(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        $this->addToCart($product, 3);

        $this->service->createFromCart($this->user, [
            'address' => 'Tunis',
            'phone'   => '12345678',
        ]);

        $this->assertDatabaseHas('products', [
            'id'    => $product->id,
            'stock' => 7,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function createFromCart_clears_cart_after_order(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        $this->addToCart($product, 2);

        $this->service->createFromCart($this->user, [
            'address' => 'Tunis',
            'phone'   => '12345678',
        ]);

        $this->assertDatabaseCount('cart_items', 0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function createFromCart_saves_address_and_phone(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $this->addToCart($product, 1);

        $order = $this->service->createFromCart($this->user, [
            'address' => '5 Avenue Habib Bourguiba',
            'phone'   => '+216 71 000 000',
        ]);

        $this->assertSame('5 Avenue Habib Bourguiba', $order->address);
        $this->assertSame('+216 71 000 000', $order->phone);
    }

    // ─── createFromCart() — exceptions ───────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function createFromCart_throws_when_cart_is_empty(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('panier est vide');

        $this->service->createFromCart($this->user, [
            'address' => 'Tunis',
            'phone'   => '12345678',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function createFromCart_throws_when_stock_insufficient(): void
    {
        $product = Product::factory()->create(['name' => 'Aloe Vera', 'stock' => 2]);
        $this->addToCart($product, 5); // demande 5, stock = 2

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Stock insuffisant');

        $this->service->createFromCart($this->user, [
            'address' => 'Tunis',
            'phone'   => '12345678',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function createFromCart_rolls_back_on_exception(): void
    {
        $product = Product::factory()->create(['stock' => 1]);
        $this->addToCart($product, 99);

        try {
            $this->service->createFromCart($this->user, [
                'address' => 'Tunis',
                'phone'   => '12345678',
            ]);
        } catch (\Exception $e) {
            // attendu
        }

        // Aucune commande créée grâce à la transaction
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        // Le stock n'a pas été modifié
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 1]);
    }
}
