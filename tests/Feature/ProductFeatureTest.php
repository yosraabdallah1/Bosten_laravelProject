<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Tests fonctionnels : catalogue public + CRUD admin.
 */
class ProductFeatureTest extends TestCase
{
    use RefreshDatabase;

    // ─── Catalogue public ─────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function guest_can_list_products(): void
    {
        Product::factory()->count(3)->create(['is_active' => true]);

        $response = $this->get('/produits');

        $response->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function product_listing_shows_only_active_products(): void
    {
        Product::factory()->create(['name' => 'Visible', 'is_active' => true]);
        Product::factory()->create(['name' => 'Caché', 'is_active' => false]);

        $response = $this->get('/produits');

        $response->assertSee('Visible');
        $response->assertDontSee('Caché');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function guest_can_view_product_detail(): void
    {
        $product = Product::factory()->create([
            'slug'      => 'aloe-vera',
            'is_active' => true,
        ]);

        $response = $this->get('/produits/aloe-vera');

        $response->assertStatus(200);
        $response->assertSee($product->name);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function inactive_product_detail_returns_404(): void
    {
        Product::factory()->create([
            'slug'      => 'produit-cache',
            'is_active' => false,
        ]);

        $response = $this->get('/produits/produit-cache');

        $response->assertStatus(404);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function guest_can_filter_products_by_search(): void
    {
        Product::factory()->create(['name' => 'Aloe Vera', 'is_active' => true]);
        Product::factory()->create(['name' => 'Cactus', 'is_active' => true]);

        $response = $this->get('/produits?search=aloe');

        $response->assertStatus(200);
        $response->assertSee('Aloe Vera');
        $response->assertDontSee('Cactus');
    }

    // ─── Admin : liste produits ───────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_can_access_product_list(): void
    {
        $admin = User::factory()->admin()->create();
        Product::factory()->count(2)->create();

        $response = $this->actingAs($admin)->get('/admin/products');

        $response->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function non_admin_cannot_access_admin_products(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get('/admin/products');

        $this->assertContains($response->status(), [302, 403]);
    }

    // ─── Admin : créer produit ────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_can_create_product(): void
    {
        $admin    = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/products', [
            'name'        => 'Nouvelle Plante',
            'category_id' => $category->id,
            'description' => 'Description de test',
            'price'       => '29.99',
            'stock'       => '10',
            'is_active'   => '1',
        ]);

        $response->assertRedirect('/admin/products');
        $this->assertDatabaseHas('products', [
            'name'  => 'Nouvelle Plante',
            'price' => 29.99,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_can_create_product_with_image(): void
    {
        Storage::fake('public');
        $admin    = User::factory()->admin()->create();
        $category = Category::factory()->create();

        // Utiliser create() au lieu de image() car GD peut ne pas être disponible
        $image = UploadedFile::fake()->create('plante.jpg', 50, 'image/jpeg');

        $response = $this->actingAs($admin)->post('/admin/products', [
            'name'        => 'Plante avec image',
            'category_id' => $category->id,
            'price'       => '15.00',
            'stock'       => '5',
            'image'       => $image,
        ]);

        $response->assertRedirect('/admin/products');

        $product = Product::where('name', 'Plante avec image')->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->image);
        Storage::disk('public')->assertExists($product->image);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function product_creation_fails_without_required_fields(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/products', []);

        $response->assertSessionHasErrors(['name', 'category_id', 'price', 'stock']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_can_create_duplicate_name_with_unique_slug(): void
    {
        $admin    = User::factory()->admin()->create();
        $category = Category::factory()->create();

        // Premier produit
        $this->actingAs($admin)->post('/admin/products', [
            'name' => 'Aloe Vera', 'category_id' => $category->id,
            'price' => '10', 'stock' => '5',
        ]);

        // Deuxième avec le même nom
        $this->actingAs($admin)->post('/admin/products', [
            'name' => 'Aloe Vera', 'category_id' => $category->id,
            'price' => '12', 'stock' => '3',
        ]);

        $this->assertDatabaseCount('products', 2);
        $slugs = Product::pluck('slug')->toArray();
        $this->assertCount(2, array_unique($slugs));
    }

    // ─── Admin : modifier produit ─────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_can_update_product(): void
    {
        $admin    = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($admin)->put("/admin/products/{$product->id}", [
            'name'        => 'Nom Modifié',
            'category_id' => $category->id,
            'price'       => '99.99',
            'stock'       => '20',
        ]);

        $response->assertRedirect('/admin/products');
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Nom Modifié']);
    }

    // ─── Admin : désactiver produit ───────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_can_deactivate_product(): void
    {
        $admin   = User::factory()->admin()->create();
        $product = Product::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->delete("/admin/products/{$product->id}");

        $this->assertDatabaseHas('products', ['id' => $product->id, 'is_active' => false]);
    }
}
