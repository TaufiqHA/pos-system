<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Role;
use App\Models\User;
use App\Models\WholesalePrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductBranchPricesTest extends TestCase
{
    use RefreshDatabase;

    private User $cabangUser;

    private Branch $branch;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $cabangRole = Role::firstOrCreate(['name' => 'cabang'], ['id' => (string) Str::uuid()]);

        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Lucifer',
            'address' => 'Jl. Lucifer No. 666',
            'phone' => '08123456789',
        ]);

        $this->cabangUser = User::factory()->create([
            'role_id' => $cabangRole->id,
            'branch_id' => $this->branch->id,
        ]);

        $this->category = Category::create([
            'id' => (string) Str::uuid(),
            'name' => 'Elektronik',
        ]);
    }

    public function test_index_only_shows_products_with_stock_in_current_branch(): void
    {
        // Product 1: Has stock in current branch
        $productInStock = Product::create([
            'id' => (string) Str::uuid(),
            'category_id' => $this->category->id,
            'sku' => 'SKU-IN-STOCK',
            'name' => 'Product In Stock',
            'buy_price' => 1000,
            'sell_price' => 2000,
        ]);
        ProductStock::create([
            'id' => (string) Str::uuid(),
            'product_id' => $productInStock->id,
            'branch_id' => $this->branch->id,
            'stock' => 10,
            'minimum_stock' => 1,
            'average_cost' => 1000,
        ]);

        // Product 2: Has stock in another branch
        $anotherBranch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Lain',
            'address' => 'Jl. Lain No. 456',
            'phone' => '081234567891',
        ]);
        $productOtherBranch = Product::create([
            'id' => (string) Str::uuid(),
            'category_id' => $this->category->id,
            'sku' => 'SKU-OTHER-STOCK',
            'name' => 'Product Other Stock',
            'buy_price' => 1000,
            'sell_price' => 2000,
        ]);
        ProductStock::create([
            'id' => (string) Str::uuid(),
            'product_id' => $productOtherBranch->id,
            'branch_id' => $anotherBranch->id,
            'stock' => 10,
            'minimum_stock' => 1,
            'average_cost' => 1000,
        ]);

        // Product 3: No stock anywhere
        $productNoStock = Product::create([
            'id' => (string) Str::uuid(),
            'category_id' => $this->category->id,
            'sku' => 'SKU-NO-STOCK',
            'name' => 'Product No Stock',
            'buy_price' => 1000,
            'sell_price' => 2000,
        ]);

        $response = $this->actingAs($this->cabangUser)->get(route('product-branch-prices.index'));

        $response->assertStatus(200);
        $response->assertViewHas('products');

        $productsPassed = $response->viewData('products');

        $this->assertTrue($productsPassed->contains('id', $productInStock->id));
        $this->assertFalse($productsPassed->contains('id', $productOtherBranch->id));
        $this->assertFalse($productsPassed->contains('id', $productNoStock->id));
    }

    public function test_cabang_can_add_wholesale_price(): void
    {
        $product = Product::create([
            'id' => (string) Str::uuid(),
            'category_id' => $this->category->id,
            'sku' => 'SKU-WHOLESALE',
            'name' => 'Product Wholesale',
            'buy_price' => 1000,
            'sell_price' => 2000,
            'is_wholesale' => true,
        ]);

        $response = $this->actingAs($this->cabangUser)->post(route('cabang.wholesale-prices.store'), [
            'product_id' => $product->id,
            'branch_id' => $this->branch->id,
            'min_qty' => 10,
            'price' => 1500,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('wholesale_prices', [
            'product_id' => $product->id,
            'branch_id' => $this->branch->id,
            'min_qty' => 10,
            'price' => 1500,
        ]);
    }

    public function test_cabang_cannot_add_wholesale_price_below_buy_price(): void
    {
        $product = Product::create([
            'id' => (string) Str::uuid(),
            'category_id' => $this->category->id,
            'sku' => 'SKU-WHOLESALE-2',
            'name' => 'Product Wholesale 2',
            'buy_price' => 1000,
            'sell_price' => 2000,
            'is_wholesale' => true,
        ]);

        $response = $this->actingAs($this->cabangUser)->post(route('cabang.wholesale-prices.store'), [
            'product_id' => $product->id,
            'branch_id' => $this->branch->id,
            'min_qty' => 10,
            'price' => 900,
        ]);

        $response->assertStatus(422);
    }

    public function test_cabang_can_delete_wholesale_price(): void
    {
        $product = Product::create([
            'id' => (string) Str::uuid(),
            'category_id' => $this->category->id,
            'sku' => 'SKU-WHOLESALE-3',
            'name' => 'Product Wholesale 3',
            'buy_price' => 1000,
            'sell_price' => 2000,
            'is_wholesale' => true,
        ]);

        $wholesalePrice = WholesalePrice::create([
            'id' => (string) Str::uuid(),
            'product_id' => $product->id,
            'branch_id' => $this->branch->id,
            'min_qty' => 10,
            'price' => 1500,
        ]);

        $response = $this->actingAs($this->cabangUser)->delete(route('cabang.wholesale-prices.destroy', $wholesalePrice->id));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('wholesale_prices', [
            'id' => $wholesalePrice->id,
        ]);
    }
}
