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

class CabangDashboardTest extends TestCase
{
    use RefreshDatabase;

    private Role $cabangRole;

    private Branch $branch;

    private User $cabangUser;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cabangRole = Role::create([
            'id' => (string) Str::uuid(),
            'name' => 'cabang',
            'description' => 'Pengelola Cabang',
        ]);

        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Bandung',
            'address' => 'Jl. Merdeka No. 10',
            'phone' => '08123456789',
        ]);

        $this->cabangUser = User::factory()->create([
            'role_id' => $this->cabangRole->id,
            'branch_id' => $this->branch->id,
            'name' => 'Cabang Staff',
        ]);

        $this->category = Category::create([
            'id' => (string) Str::uuid(),
            'name' => 'Makanan',
        ]);
    }

    public function test_cabang_dashboard_displays_correct_total_products_for_this_branch_only(): void
    {
        // Create 2 products in the database
        $product1 = Product::create([
            'id' => (string) Str::uuid(),
            'category_id' => $this->category->id,
            'sku' => 'SKU-001',
            'name' => 'Product 1',
            'buy_price' => 1000,
            'sell_price' => 1500,
        ]);

        $product2 = Product::create([
            'id' => (string) Str::uuid(),
            'category_id' => $this->category->id,
            'sku' => 'SKU-002',
            'name' => 'Product 2',
            'buy_price' => 2000,
            'sell_price' => 2500,
        ]);

        // Create stock only for product 1 in our branch
        ProductStock::create([
            'id' => (string) Str::uuid(),
            'product_id' => $product1->id,
            'branch_id' => $this->branch->id,
            'stock' => 10,
            'minimum_stock' => 2,
            'average_cost' => 1000,
        ]);

        // Request cabang dashboard
        $response = $this->actingAs($this->cabangUser)->get(route('cabang.dashboard'));

        $response->assertStatus(200);

        // Verify totalProduk is 1 (representing only product 1 which has stock at this branch),
        // and not 2 (which is the total count of products in the central database)
        $response->assertViewHas('totalProduk', 1);
        $response->assertViewHas('totalStok', 10);
    }
}
