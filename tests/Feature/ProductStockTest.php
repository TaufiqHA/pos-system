<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductStockTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Branch $branch;

    private Category $category;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['id' => (string) Str::uuid()]);
        $this->user = User::factory()->create(['role_id' => $adminRole->id]);

        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Bandung',
            'address' => 'Jl. Merdeka No. 10',
            'phone' => '08123456789',
            'wilayah_id' => 'Jawa Barat',
            'notes' => 'Kantor Cabang Baru',
        ]);

        $this->user->update(['branch_id' => $this->branch->id]);

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

    public function test_unauthenticated_user_cannot_access_product_stocks(): void
    {
        $response = $this->get(route('product-stocks.index'));
        $response->assertRedirect(route('login'));

        $response = $this->post(route('product-stocks.store'), []);
        $response->assertRedirect(route('login'));
    }

    public function test_can_list_product_stocks(): void
    {
        $stock = ProductStock::create([
            'id' => (string) Str::uuid(),
            'product_id' => $this->product->id,
            'branch_id' => $this->branch->id,
            'stock' => 50,
            'minimum_stock' => 5,
            'average_cost' => 4800000,
        ]);

        $response = $this->actingAs($this->user)->get(route('product-stocks.index'));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $stock->id,
            'product_id' => $this->product->id,
            'branch_id' => $this->branch->id,
            'stock' => 50,
        ]);
    }

    public function test_can_create_product_stock(): void
    {
        $stockId = (string) Str::uuid();

        $response = $this->actingAs($this->user)->post(route('product-stocks.store'), [
            'id' => $stockId,
            'product_id' => $this->product->id,
            'branch_id' => $this->branch->id,
            'stock' => 100,
            'minimum_stock' => 10,
            'average_cost' => 4900000,
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'message' => 'Stock created successfully',
        ]);

        $this->assertDatabaseHas('product_stocks', [
            'id' => $stockId,
            'product_id' => $this->product->id,
            'branch_id' => $this->branch->id,
            'stock' => 100,
            'minimum_stock' => 10,
            'average_cost' => 4900000,
        ]);
    }

    public function test_can_show_product_stock(): void
    {
        $stock = ProductStock::create([
            'id' => (string) Str::uuid(),
            'product_id' => $this->product->id,
            'branch_id' => $this->branch->id,
            'stock' => 50,
            'minimum_stock' => 5,
            'average_cost' => 4800000,
        ]);

        $response = $this->actingAs($this->user)->get(route('product-stocks.show', $stock->id));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $stock->id,
            'product_id' => $this->product->id,
            'branch_id' => $this->branch->id,
            'stock' => 50,
        ]);
    }

    public function test_can_update_product_stock(): void
    {
        $stock = ProductStock::create([
            'id' => (string) Str::uuid(),
            'product_id' => $this->product->id,
            'branch_id' => $this->branch->id,
            'stock' => 50,
            'minimum_stock' => 5,
            'average_cost' => 4800000,
        ]);

        $response = $this->actingAs($this->user)->put(route('product-stocks.update', $stock->id), [
            'stock' => 75,
            'minimum_stock' => 8,
            'average_cost' => 4850000,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'message' => 'Stock updated successfully',
        ]);

        $this->assertDatabaseHas('product_stocks', [
            'id' => $stock->id,
            'stock' => 75,
            'minimum_stock' => 8,
            'average_cost' => 4850000,
        ]);
    }

    public function test_can_delete_product_stock(): void
    {
        $stock = ProductStock::create([
            'id' => (string) Str::uuid(),
            'product_id' => $this->product->id,
            'branch_id' => $this->branch->id,
            'stock' => 50,
            'minimum_stock' => 5,
            'average_cost' => 4800000,
        ]);

        $response = $this->actingAs($this->user)->delete(route('product-stocks.destroy', $stock->id));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'message' => 'Stock deleted successfully',
        ]);

        $this->assertDatabaseMissing('product_stocks', [
            'id' => $stock->id,
        ]);
    }

    public function test_can_view_monitoring_stock_page(): void
    {
        $stock = ProductStock::create([
            'id' => (string) Str::uuid(),
            'product_id' => $this->product->id,
            'branch_id' => $this->branch->id,
            'stock' => 50,
            'minimum_stock' => 5,
            'average_cost' => 4800000,
        ]);

        $response = $this->actingAs($this->user)->get(route('admin.monitoring-stock'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.monitoringstock');
        $response->assertViewHas('stocks');
        $response->assertSee($this->product->name);
    }

    public function test_can_update_product_stock_via_web_form_redirects(): void
    {
        $stock = ProductStock::create([
            'id' => (string) Str::uuid(),
            'product_id' => $this->product->id,
            'branch_id' => $this->branch->id,
            'stock' => 50,
            'minimum_stock' => 5,
            'average_cost' => 4800000,
        ]);

        $response = $this->actingAs($this->user)->put(route('product-stocks.update', $stock->id), [
            '_token' => 'dummy_csrf_token',
            'stock' => 80,
            'minimum_stock' => 10,
            'average_cost' => 4900000,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.monitoring-stock'));
        $response->assertSessionHas('success', 'Stok produk berhasil diperbarui.');

        $this->assertDatabaseHas('product_stocks', [
            'id' => $stock->id,
            'stock' => 80,
            'minimum_stock' => 10,
            'average_cost' => 4900000,
        ]);
    }

    public function test_cabang_user_can_view_cabang_monitoring_stok_page(): void
    {
        $cabangRole = Role::firstOrCreate(['name' => 'cabang'], ['id' => (string) Str::uuid()]);
        $cabangUser = User::factory()->create([
            'role_id' => $cabangRole->id,
            'branch_id' => $this->branch->id,
        ]);

        $stock = ProductStock::create([
            'id' => (string) Str::uuid(),
            'product_id' => $this->product->id,
            'branch_id' => $this->branch->id,
            'stock' => 50,
            'minimum_stock' => 5,
            'average_cost' => 4800000,
        ]);

        $response = $this->actingAs($cabangUser)->get(route('cabang.monitoring-stok'));

        $response->assertStatus(200);
        $response->assertViewIs('cabang.monitoring-stok');
        $response->assertViewHas('stocks');
        $response->assertSee($this->product->name);
    }

    public function test_cabang_monitoring_stok_page_only_shows_current_branch_stocks(): void
    {
        $cabangRole = Role::firstOrCreate(['name' => 'cabang'], ['id' => (string) Str::uuid()]);
        $cabangUser = User::factory()->create([
            'role_id' => $cabangRole->id,
            'branch_id' => $this->branch->id,
        ]);

        $anotherBranch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Jakarta',
            'address' => 'Jl. Sudirman',
            'phone' => '08122334455',
        ]);

        $stockCurrent = ProductStock::create([
            'id' => (string) Str::uuid(),
            'product_id' => $this->product->id,
            'branch_id' => $this->branch->id,
            'stock' => 50,
            'minimum_stock' => 5,
            'average_cost' => 4800000,
        ]);

        $stockOther = ProductStock::create([
            'id' => (string) Str::uuid(),
            'product_id' => $this->product->id,
            'branch_id' => $anotherBranch->id,
            'stock' => 10,
            'minimum_stock' => 2,
            'average_cost' => 4800000,
        ]);

        $response = $this->actingAs($cabangUser)->get(route('cabang.monitoring-stok'));

        $response->assertStatus(200);
        $stocks = $response->viewData('stocks');
        $this->assertTrue($stocks->contains('id', $stockCurrent->id));
        $this->assertFalse($stocks->contains('id', $stockOther->id));
    }

    public function test_cabang_user_can_update_cabang_stock(): void
    {
        $cabangRole = Role::firstOrCreate(['name' => 'cabang'], ['id' => (string) Str::uuid()]);
        $cabangUser = User::factory()->create([
            'role_id' => $cabangRole->id,
            'branch_id' => $this->branch->id,
        ]);

        $stock = ProductStock::create([
            'id' => (string) Str::uuid(),
            'product_id' => $this->product->id,
            'branch_id' => $this->branch->id,
            'stock' => 50,
            'minimum_stock' => 5,
            'average_cost' => 4800000,
        ]);

        $response = $this->actingAs($cabangUser)->put(route('cabang.monitoring-stok.update', $stock->id), [
            '_token' => 'dummy_csrf_token',
            'stock' => 60,
            'minimum_stock' => 8,
            'average_cost' => 4850000,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('cabang.monitoring-stok'));
        $response->assertSessionHas('success', 'Stok produk cabang berhasil diperbarui.');

        $this->assertDatabaseHas('product_stocks', [
            'id' => $stock->id,
            'stock' => 60,
            'minimum_stock' => 8,
            'average_cost' => 4850000,
        ]);
    }

    public function test_cabang_user_cannot_update_stock_of_another_branch(): void
    {
        $cabangRole = Role::firstOrCreate(['name' => 'cabang'], ['id' => (string) Str::uuid()]);
        $cabangUser = User::factory()->create([
            'role_id' => $cabangRole->id,
            'branch_id' => $this->branch->id,
        ]);

        $anotherBranch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Jakarta',
            'address' => 'Jl. Sudirman',
            'phone' => '08122334455',
        ]);

        $stock = ProductStock::create([
            'id' => (string) Str::uuid(),
            'product_id' => $this->product->id,
            'branch_id' => $anotherBranch->id,
            'stock' => 50,
            'minimum_stock' => 5,
            'average_cost' => 4800000,
        ]);

        $response = $this->actingAs($cabangUser)->putJson(route('cabang.monitoring-stok.update', $stock->id), [
            'stock' => 60,
            'minimum_stock' => 8,
            'average_cost' => 4850000,
        ]);

        $response->assertStatus(404);
    }
}
