<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $adminRole = \App\Models\Role::firstOrCreate(['name' => 'admin'], ['id' => (string) \Illuminate\Support\Str::uuid()]);
        $this->user = User::factory()->create(['role_id' => $adminRole->id]);
        $this->category = Category::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Elektronik',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_products(): void
    {
        $response = $this->get(route('products.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_can_list_products(): void
    {
        $product = Product::create([
            'id' => Str::uuid()->toString(),
            'category_id' => $this->category->id,
            'sku' => 'SKU-001',
            'name' => 'Laptop Asus',
            'buy_price' => 5000000,
            'sell_price' => 6000000,
        ]);

        $response = $this->actingAs($this->user)->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertViewHas('products');
        $response->assertSee('Laptop Asus');
        $response->assertSee('SKU-001');
    }

    public function test_create_product_form_redirects_to_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('products.create'));

        $response->assertStatus(302);
        $response->assertRedirect(route('products.index'));
    }

    public function test_can_store_product(): void
    {
        $response = $this->actingAs($this->user)->post(route('products.store'), [
            'category_id' => $this->category->id,
            'sku' => 'SKU-002',
            'name' => 'Laptop HP',
            'description' => 'HP Laptop Core i5',
            'unit' => 'pcs',
            'buy_price' => 7000000,
            'sell_price' => 8000000,
            'is_wholesale' => 0,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success', 'Product created successfully.');

        $this->assertDatabaseHas('products', [
            'sku' => 'SKU-002',
            'name' => 'Laptop HP',
            'buy_price' => 7000000,
            'sell_price' => 8000000,
        ]);
    }

    public function test_can_store_product_and_automatically_creates_stock(): void
    {
        $branch = \App\Models\Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Surabaya',
            'address' => 'Jl. Pemuda',
            'phone' => '081234567890',
            'wilayah_id' => 'Jawa Timur',
        ]);

        $this->user->update(['branch_id' => $branch->id]);

        $response = $this->actingAs($this->user)->post(route('products.store'), [
            'category_id' => $this->category->id,
            'sku' => 'SKU-777',
            'name' => 'Laptop Dell',
            'buy_price' => 10000000,
            'sell_price' => 12000000,
        ]);

        $response->assertStatus(302);

        $product = Product::where('sku', 'SKU-777')->firstOrFail();

        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'stock' => 0,
            'minimum_stock' => 0,
            'average_cost' => 0,
        ]);
    }

    public function test_cannot_store_product_without_required_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('products.store'), [
            'sku' => '',
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['category_id', 'sku', 'name', 'buy_price', 'sell_price']);
    }

    public function test_can_show_product_json(): void
    {
        $product = Product::create([
            'id' => Str::uuid()->toString(),
            'category_id' => $this->category->id,
            'sku' => 'SKU-001',
            'name' => 'Laptop Asus',
            'buy_price' => 5000000,
            'sell_price' => 6000000,
        ]);

        $response = $this->actingAs($this->user)->get(route('products.show', $product->id));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $product->id,
            'sku' => 'SKU-001',
            'name' => 'Laptop Asus',
        ]);
    }

    public function test_edit_product_form_redirects_to_index(): void
    {
        $product = Product::create([
            'id' => Str::uuid()->toString(),
            'category_id' => $this->category->id,
            'sku' => 'SKU-001',
            'name' => 'Laptop Asus',
            'buy_price' => 5000000,
            'sell_price' => 6000000,
        ]);

        $response = $this->actingAs($this->user)->get(route('products.edit', $product->id));

        $response->assertStatus(302);
        $response->assertRedirect(route('products.index'));
    }

    public function test_can_update_product(): void
    {
        $product = Product::create([
            'id' => Str::uuid()->toString(),
            'category_id' => $this->category->id,
            'sku' => 'SKU-001',
            'name' => 'Laptop Asus',
            'buy_price' => 5000000,
            'sell_price' => 6000000,
        ]);

        $response = $this->actingAs($this->user)->put(route('products.update', $product->id), [
            'category_id' => $this->category->id,
            'sku' => 'SKU-001',
            'name' => 'Laptop Asus ROG',
            'buy_price' => 5500000,
            'sell_price' => 6500000,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success', 'Product updated successfully.');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Laptop Asus ROG',
            'buy_price' => 5500000,
            'sell_price' => 6500000,
        ]);
    }

    public function test_can_delete_product_soft_delete(): void
    {
        $product = Product::create([
            'id' => Str::uuid()->toString(),
            'category_id' => $this->category->id,
            'sku' => 'SKU-001',
            'name' => 'Laptop Asus',
            'buy_price' => 5000000,
            'sell_price' => 6000000,
        ]);

        $response = $this->actingAs($this->user)->delete(route('products.destroy', $product->id));

        $response->assertStatus(302);
        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success', 'Product deleted successfully.');

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }

    public function test_check_sku_returns_false_if_sku_does_not_exist(): void
    {
        $response = $this->actingAs($this->user)->get(route('products.check_sku', ['sku' => 'NONEXISTENT']));

        $response->assertStatus(200);
        $response->assertJson(['exists' => false]);
    }

    public function test_check_sku_returns_true_if_sku_exists(): void
    {
        Product::create([
            'id' => Str::uuid()->toString(),
            'category_id' => $this->category->id,
            'sku' => 'SKU-EXIST',
            'name' => 'Laptop Acer',
            'buy_price' => 4000000,
            'sell_price' => 5000000,
        ]);

        $response = $this->actingAs($this->user)->get(route('products.check_sku', ['sku' => 'SKU-EXIST']));

        $response->assertStatus(200);
        $response->assertJson(['exists' => true]);
    }

    public function test_check_sku_ignores_specified_id(): void
    {
        $product = Product::create([
            'id' => Str::uuid()->toString(),
            'category_id' => $this->category->id,
            'sku' => 'SKU-EXIST',
            'name' => 'Laptop Acer',
            'buy_price' => 4000000,
            'sell_price' => 5000000,
        ]);

        $response = $this->actingAs($this->user)->get(route('products.check_sku', [
            'sku' => 'SKU-EXIST',
            'ignore_id' => $product->id
        ]));

        $response->assertStatus(200);
        $response->assertJson(['exists' => false]);
    }

    public function test_check_sku_returns_false_if_sku_empty(): void
    {
        $response = $this->actingAs($this->user)->get(route('products.check_sku', ['sku' => '']));

        $response->assertStatus(200);
        $response->assertJson(['exists' => false]);
    }

    public function test_check_sku_returns_true_even_if_sku_is_soft_deleted(): void
    {
        $product = Product::create([
            'id' => Str::uuid()->toString(),
            'category_id' => $this->category->id,
            'sku' => 'SKU-TRASHED',
            'name' => 'Laptop Toshiba',
            'buy_price' => 3000000,
            'sell_price' => 4000000,
        ]);
        $product->delete();

        $response = $this->actingAs($this->user)->get(route('products.check_sku', ['sku' => 'SKU-TRASHED']));

        $response->assertStatus(200);
        $response->assertJson(['exists' => true]);
    }

    public function test_check_sku_is_case_insensitive(): void
    {
        Product::create([
            'id' => Str::uuid()->toString(),
            'category_id' => $this->category->id,
            'sku' => 'SKU-mixedCASE',
            'name' => 'Laptop Lenovo',
            'buy_price' => 6000000,
            'sell_price' => 7000000,
        ]);

        $response = $this->actingAs($this->user)->get(route('products.check_sku', ['sku' => 'sku-MIXEDcase']));

        $response->assertStatus(200);
        $response->assertJson(['exists' => true]);
    }
}

