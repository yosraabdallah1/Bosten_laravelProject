<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @covers \App\Services\ProductService
 */
class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProductService();
    }

    // ─── getActiveProducts() ──────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function getActiveProducts_returns_only_active_products(): void
    {
        Product::factory()->count(3)->create(['is_active' => true]);
        Product::factory()->count(2)->inactive()->create();

        $result = $this->service->getActiveProducts();

        $this->assertCount(3, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getActiveProducts_filters_by_category_slug(): void
    {
        $cat1 = Category::factory()->create(['slug' => 'interieur']);
        $cat2 = Category::factory()->create(['slug' => 'exterieur']);

        Product::factory()->count(2)->create(['category_id' => $cat1->id, 'is_active' => true]);
        Product::factory()->count(3)->create(['category_id' => $cat2->id, 'is_active' => true]);

        $result = $this->service->getActiveProducts('interieur');

        $this->assertCount(2, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getActiveProducts_filters_by_search_term(): void
    {
        Product::factory()->create(['name' => 'Aloe Vera', 'is_active' => true]);
        Product::factory()->create(['name' => 'Cactus rouge', 'is_active' => true]);
        Product::factory()->create(['name' => 'Pothos', 'is_active' => true]);

        $result = $this->service->getActiveProducts(null, 'aloe');

        $this->assertCount(1, $result);
        $this->assertSame('Aloe Vera', $result->first()->name);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getActiveProducts_searches_in_description(): void
    {
        Product::factory()->create([
            'name'        => 'Plante mystère',
            'description' => 'Cette plante tolère la sécheresse',
            'is_active'   => true,
        ]);
        Product::factory()->create(['name' => 'Autre plante', 'is_active' => true]);

        $result = $this->service->getActiveProducts(null, 'sécheresse');

        $this->assertCount(1, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getActiveProducts_returns_paginated_result(): void
    {
        Product::factory()->count(15)->create(['is_active' => true]);

        $result = $this->service->getActiveProducts();

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $result);
        $this->assertCount(12, $result); // paginate(12)
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getActiveProducts_returns_empty_when_no_products(): void
    {
        $result = $this->service->getActiveProducts();

        $this->assertCount(0, $result);
    }

    // ─── getInStockProducts() ─────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function getInStockProducts_excludes_out_of_stock(): void
    {
        Product::factory()->count(3)->create(['is_active' => true, 'stock' => 10]);
        Product::factory()->count(2)->create(['is_active' => true, 'stock' => 0]);

        $result = $this->service->getInStockProducts();

        $this->assertCount(3, $result);
        $result->each(fn ($p) => $this->assertGreaterThan(0, $p->stock));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getInStockProducts_excludes_inactive_products(): void
    {
        Product::factory()->create(['is_active' => false, 'stock' => 10]);
        Product::factory()->create(['is_active' => true, 'stock' => 5]);

        $result = $this->service->getInStockProducts();

        $this->assertCount(1, $result);
    }

    // ─── getLowStock() ────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function getLowStock_returns_products_below_threshold(): void
    {
        Product::factory()->create(['stock' => 2, 'is_active' => true]);
        Product::factory()->create(['stock' => 4, 'is_active' => true]);
        Product::factory()->create(['stock' => 10, 'is_active' => true]);

        $result = $this->service->getLowStock(5);

        $this->assertCount(2, $result);
        $result->each(fn ($p) => $this->assertLessThan(5, $p->stock));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getLowStock_uses_default_threshold_of_5(): void
    {
        Product::factory()->create(['stock' => 3, 'is_active' => true]);
        Product::factory()->create(['stock' => 6, 'is_active' => true]);

        $result = $this->service->getLowStock();

        $this->assertCount(1, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getLowStock_excludes_inactive_products(): void
    {
        Product::factory()->create(['stock' => 1, 'is_active' => false]);
        Product::factory()->create(['stock' => 1, 'is_active' => true]);

        $result = $this->service->getLowStock();

        $this->assertCount(1, $result);
    }

    // ─── getBestSellers() ─────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function getBestSellers_returns_correct_limit(): void
    {
        Product::factory()->count(10)->create(['is_active' => true]);

        $result = $this->service->getBestSellers(3);

        $this->assertCount(3, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getBestSellers_excludes_inactive_products(): void
    {
        Product::factory()->create(['is_active' => false]);
        Product::factory()->count(2)->create(['is_active' => true]);

        $result = $this->service->getBestSellers(5);

        $this->assertCount(2, $result);
        $result->each(fn ($p) => $this->assertTrue((bool) $p->is_active));
    }
}
