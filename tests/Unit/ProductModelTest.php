<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @covers \App\Models\Product
 */
class ProductModelTest extends TestCase
{
    use RefreshDatabase;

    // ─── isInStock() ─────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function isInStock_returns_true_when_stock_greater_than_zero(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $this->assertTrue($product->isInStock());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function isInStock_returns_false_when_stock_is_zero(): void
    {
        $product = Product::factory()->create(['stock' => 0]);
        $this->assertFalse($product->isInStock());
    }

    // ─── Relations ───────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function product_belongs_to_category(): void
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertSame($category->id, $product->category->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function product_has_fillable_attributes(): void
    {
        $product = new Product();
        $this->assertContains('name', $product->getFillable());
        $this->assertContains('slug', $product->getFillable());
        $this->assertContains('price', $product->getFillable());
        $this->assertContains('stock', $product->getFillable());
        $this->assertContains('is_active', $product->getFillable());
        $this->assertContains('category_id', $product->getFillable());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function product_can_be_created_with_factory(): void
    {
        $product = Product::factory()->create();

        $this->assertNotNull($product->id);
        $this->assertNotNull($product->name);
        $this->assertNotNull($product->slug);
        $this->assertGreaterThan(0, $product->price);
        $this->assertTrue((bool) $product->is_active);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function inactive_state_creates_hidden_product(): void
    {
        $product = Product::factory()->inactive()->create();
        $this->assertFalse((bool) $product->is_active);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function out_of_stock_state_sets_stock_to_zero(): void
    {
        $product = Product::factory()->outOfStock()->create();
        $this->assertSame(0, $product->stock);
        $this->assertFalse($product->isInStock());
    }
}
