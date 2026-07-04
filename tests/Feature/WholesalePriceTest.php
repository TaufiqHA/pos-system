<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\WholesalePrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WholesalePriceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Branch $branch;
    private Category $category;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $adminRole = \App\Models\Role::firstOrCreate(['name' => 'admin'], ['id' => (string) \Illuminate\Support\Str::uuid()]);
        $this->user = User::factory()->create(['role_id' => $adminRole->id]);
        
        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Bandung',
            'address' => 'Jl. Merdeka No. 10',
            'phone' => '08123456789',
            'wilayah_id' => 'Jawa Barat',
            'notes' => 'Kantor Cabang Baru',
        ]);

        $this->category = Category::create([
            'id' => (string) Str::uuid(),
            'name' => 'Elektronik',
        ]);

        $this->product = Product::create([
            'id' => (string) Str::uuid(),
            'category_id' => $this->category->id,
            'sku' => 'SKU-001',
            'name' => 'Laptop Asus',
            'buy_price' => 5000000,
            'sell_price' => 6000000,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_wholesale_prices(): void
    {
        $response = $this->post(route('wholesale-prices.store'), [
            'product_id' => $this->product->id,
            'branch_id' => $this->branch->id,
            'min_qty' => 10,
            'price' => 5500000,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_can_create_wholesale_price(): void
    {
        $response = $this->actingAs($this->user)->post(route('wholesale-prices.store'), [
            'product_id' => $this->product->id,
            'branch_id' => $this->branch->id,
            'min_qty' => 10,
            'price' => 5500000,
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'message' => 'Wholesale price berhasil ditambahkan',
        ]);

        $this->assertDatabaseHas('wholesale_prices', [
            'product_id' => $this->product->id,
            'branch_id' => $this->branch->id,
            'min_qty' => 10,
            'price' => 5500000,
        ]);
    }

    public function test_cannot_create_wholesale_price_with_invalid_data(): void
    {
        $response = $this->actingAs($this->user)->post(route('wholesale-prices.store'), [
            'product_id' => 'non-existent-product',
            'branch_id' => 'non-existent-branch',
            'min_qty' => 0, // min is 1
            'price' => -100, // min is 0
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['product_id', 'branch_id', 'min_qty', 'price']);
    }

    public function test_cannot_create_wholesale_price_below_buy_price(): void
    {
        $response = $this->actingAs($this->user)->post(route('wholesale-prices.store'), [
            'product_id' => $this->product->id,
            'branch_id' => $this->branch->id,
            'min_qty' => 10,
            'price' => 4500000, // buy_price is 5000000
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'message' => 'Harga grosir tidak boleh di bawah harga beli produk (Rp 5.000.000)',
        ]);
    }

    public function test_can_update_wholesale_price(): void
    {
        $wholesalePrice = WholesalePrice::create([
            'product_id' => $this->product->id,
            'branch_id' => $this->branch->id,
            'min_qty' => 10,
            'price' => 5500000,
        ]);

        $response = $this->actingAs($this->user)->put(route('wholesale-prices.update', $wholesalePrice->id), [
            'min_qty' => 15,
            'price' => 5300000,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'message' => 'Wholesale price berhasil diperbarui',
        ]);

        $this->assertDatabaseHas('wholesale_prices', [
            'id' => $wholesalePrice->id,
            'min_qty' => 15,
            'price' => 5300000,
        ]);
    }

    public function test_cannot_update_wholesale_price_below_buy_price(): void
    {
        $wholesalePrice = WholesalePrice::create([
            'product_id' => $this->product->id,
            'branch_id' => $this->branch->id,
            'min_qty' => 10,
            'price' => 5500000,
        ]);

        $response = $this->actingAs($this->user)->put(route('wholesale-prices.update', $wholesalePrice->id), [
            'price' => 4500000, // buy_price is 5000000
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'message' => 'Harga grosir tidak boleh di bawah harga beli produk (Rp 5.000.000)',
        ]);
    }

    public function test_can_delete_wholesale_price(): void
    {
        $wholesalePrice = WholesalePrice::create([
            'product_id' => $this->product->id,
            'branch_id' => $this->branch->id,
            'min_qty' => 10,
            'price' => 5500000,
        ]);

        $response = $this->actingAs($this->user)->delete(route('wholesale-prices.destroy', $wholesalePrice->id));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'message' => 'Wholesale price berhasil dihapus',
        ]);

        $this->assertDatabaseMissing('wholesale_prices', [
            'id' => $wholesalePrice->id,
        ]);
    }
}

