<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Outlets;
use App\Models\Product;
use App\Models\ProductBranchPrices;
use App\Models\ProductStock;
use App\Models\Role;
use App\Models\Sales;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SalesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['id' => (string) Str::uuid()]
        );

        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Test',
            'address' => 'Jl. Test No. 123',
            'phone' => '081234567890',
            'wilayah_id' => 'Jawa Barat',
            'notes' => 'Catatan Cabang',
        ]);

        $this->user = User::factory()->create([
            'role_id' => $adminRole->id,
            'branch_id' => $this->branch->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_sales(): void
    {
        $response = $this->getJson(route('sales.index'));
        $response->assertStatus(401);
    }

    public function test_can_list_sales(): void
    {
        $sale = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-20260704-0001',
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'create_by' => $this->user->id,
            'date' => '2026-07-04 22:00:00',
            'subtotal' => 100000.00,
            'discount' => 10000.00,
            'tax' => 9000.00,
            'grand_total' => 99000.00,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user)->getJson(route('sales.index'));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $sale->id,
                'invoice' => 'INV-20260704-0001',
            ]);
    }

    public function test_can_store_sale(): void
    {
        $payload = [
            'invoice' => 'INV-20260704-0002',
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'date' => '2026-07-04 22:00:00',
            'subtotal' => 150000.00,
            'discount' => 5000.00,
            'tax' => 14500.00,
            'grand_total' => 159500.00,
            'status' => 'pending',
        ];

        $response = $this->actingAs($this->user)->postJson(route('sales.store'), $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'invoice',
                'branch_id',
                'user_id',
                'date',
                'subtotal',
                'discount',
                'tax',
                'grand_total',
                'status',
                'created_at',
                'updated_at',
            ]);

        $saleId = $response->json('id');

        $this->assertDatabaseHas('sales', [
            'invoice' => 'INV-20260704-0002',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('deliveries', [
            'sale_id' => $saleId,
            'driver_name' => 'Belum Ditentukan',
            'status' => 'PENDING',
        ]);
    }

    public function test_cannot_store_duplicate_invoice(): void
    {
        Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-DUP-111',
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'date' => '2026-07-04 22:00:00',
            'subtotal' => 100000.00,
            'discount' => 10000.00,
            'tax' => 9000.00,
            'grand_total' => 99000.00,
            'status' => 'completed',
        ]);

        $payload = [
            'invoice' => 'INV-DUP-111',
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'date' => '2026-07-04 22:00:00',
            'subtotal' => 150000.00,
            'discount' => 5000.00,
            'tax' => 14500.00,
            'grand_total' => 159500.00,
            'status' => 'pending',
        ];

        $response = $this->actingAs($this->user)->postJson(route('sales.store'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['invoice']);
    }

    public function test_can_show_sale(): void
    {
        $sale = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-20260704-0003',
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'date' => '2026-07-04 22:00:00',
            'subtotal' => 100000.00,
            'discount' => 10000.00,
            'tax' => 9000.00,
            'grand_total' => 99000.00,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user)->getJson(route('sales.show', $sale->id));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $sale->id,
                'invoice' => 'INV-20260704-0003',
            ]);
    }

    public function test_can_update_sale(): void
    {
        $sale = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-20260704-0004',
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'date' => '2026-07-04 22:00:00',
            'subtotal' => 100000.00,
            'discount' => 10000.00,
            'tax' => 9000.00,
            'grand_total' => 99000.00,
            'status' => 'completed',
        ]);

        $payload = [
            'invoice' => 'INV-20260704-0004',
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'date' => '2026-07-04 23:00:00',
            'subtotal' => 120000.00,
            'discount' => 20000.00,
            'tax' => 10000.00,
            'grand_total' => 110000.00,
            'status' => 'updated_status',
        ];

        $response = $this->actingAs($this->user)->putJson(route('sales.update', $sale->id), $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'updated_status',
                'subtotal' => 120000.00,
            ]);

        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'status' => 'updated_status',
        ]);
    }

    public function test_can_delete_sale(): void
    {
        $sale = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-20260704-0005',
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'date' => '2026-07-04 22:00:00',
            'subtotal' => 100000.00,
            'discount' => 10000.00,
            'tax' => 9000.00,
            'grand_total' => 99000.00,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user)->deleteJson(route('sales.destroy', $sale->id));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Deleted',
            ]);

        $this->assertDatabaseMissing('sales', [
            'id' => $sale->id,
        ]);
    }

    public function test_cabang_index_passes_outlets_to_view(): void
    {
        $cabangRole = Role::firstOrCreate(
            ['name' => 'cabang'],
            ['id' => (string) Str::uuid()]
        );
        $cabangUser = User::factory()->create([
            'role_id' => $cabangRole->id,
            'branch_id' => $this->branch->id,
        ]);

        $outlet = Outlets::create([
            'id' => (string) Str::uuid(),
            'branch_id' => $this->branch->id,
            'name' => 'Outlet Cabang Lucifer',
            'address' => 'Jl. Lucifer No. 666',
            'phone' => '08123456789',
        ]);

        $response = $this->actingAs($cabangUser)->get(route('cabang.penjualan'));

        $response->assertStatus(200);
        $response->assertViewHas('outlets');
        $outletsPassed = $response->viewData('outlets');
        $this->assertTrue($outletsPassed->contains('id', $outlet->id));
    }

    public function test_cabang_index_only_passes_products_with_stock_in_current_branch(): void
    {
        $cabangRole = Role::firstOrCreate(
            ['name' => 'cabang'],
            ['id' => (string) Str::uuid()]
        );
        $cabangUser = User::factory()->create([
            'role_id' => $cabangRole->id,
            'branch_id' => $this->branch->id,
        ]);

        $category = Category::create([
            'id' => (string) Str::uuid(),
            'name' => 'Kategori Test',
        ]);

        // Product 1: Has stock in current branch
        $productInStock = Product::create([
            'id' => (string) Str::uuid(),
            'category_id' => $category->id,
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
            'category_id' => $category->id,
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
            'category_id' => $category->id,
            'sku' => 'SKU-NO-STOCK',
            'name' => 'Product No Stock',
            'buy_price' => 1000,
            'sell_price' => 2000,
        ]);

        $response = $this->actingAs($cabangUser)->get(route('cabang.penjualan'));

        $response->assertStatus(200);
        $response->assertViewHas('products');

        $productsPassed = $response->viewData('products');

        $this->assertTrue($productsPassed->contains('id', $productInStock->id));
        $this->assertFalse($productsPassed->contains('id', $productOtherBranch->id));
        $this->assertFalse($productsPassed->contains('id', $productNoStock->id));
    }

    public function test_cabang_index_passes_products_with_custom_branch_prices(): void
    {
        $cabangRole = Role::firstOrCreate(
            ['name' => 'cabang'],
            ['id' => (string) Str::uuid()]
        );
        $cabangUser = User::factory()->create([
            'role_id' => $cabangRole->id,
            'branch_id' => $this->branch->id,
        ]);

        $category = Category::create([
            'id' => (string) Str::uuid(),
            'name' => 'Kategori Test',
        ]);

        $product = Product::create([
            'id' => (string) Str::uuid(),
            'category_id' => $category->id,
            'sku' => 'SKU-CUSTOM-PRICE',
            'name' => 'Product Custom Price',
            'buy_price' => 1000,
            'sell_price' => 2000,
        ]);

        ProductStock::create([
            'id' => (string) Str::uuid(),
            'product_id' => $product->id,
            'branch_id' => $this->branch->id,
            'stock' => 10,
            'minimum_stock' => 1,
            'average_cost' => 1000,
        ]);

        $branchPrice = ProductBranchPrices::create([
            'id' => (string) Str::uuid(),
            'product_id' => $product->id,
            'branch_id' => $this->branch->id,
            'sell_price' => 2500,
        ]);

        $response = $this->actingAs($cabangUser)->get(route('cabang.penjualan'));

        $response->assertStatus(200);
        $response->assertViewHas('products');

        $productsPassed = $response->viewData('products');
        $this->assertTrue($productsPassed->contains('id', $product->id));

        $passedProduct = $productsPassed->firstWhere('id', $product->id);
        $this->assertEquals(2500, $passedProduct->branchPrices->first()->sell_price);
    }

    public function test_cabang_index_only_shows_sales_created_by_logged_in_user(): void
    {
        $cabangRole = Role::firstOrCreate(
            ['name' => 'cabang'],
            ['id' => (string) Str::uuid()]
        );
        $cabangUser = User::factory()->create([
            'role_id' => $cabangRole->id,
            'branch_id' => $this->branch->id,
        ]);

        $otherCabangUser = User::factory()->create([
            'role_id' => $cabangRole->id,
            'branch_id' => $this->branch->id,
        ]);

        $saleOfUser = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-USER-1',
            'branch_id' => $this->branch->id,
            'user_id' => $cabangUser->id,
            'create_by' => $cabangUser->id,
            'date' => '2026-07-06 12:00:00',
            'subtotal' => 1000,
            'grand_total' => 1000,
            'status' => 'completed',
        ]);

        $saleOfOther = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-OTHER-1',
            'branch_id' => $this->branch->id,
            'user_id' => $otherCabangUser->id,
            'create_by' => $otherCabangUser->id,
            'date' => '2026-07-06 12:00:00',
            'subtotal' => 1000,
            'grand_total' => 1000,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($cabangUser)->get(route('cabang.penjualan'));

        $response->assertStatus(200);
        $response->assertViewHas('sales');

        $salesPassed = $response->viewData('sales');
        $this->assertTrue($salesPassed->contains('id', $saleOfUser->id));
        $this->assertFalse($salesPassed->contains('id', $saleOfOther->id));
    }

    public function test_admin_index_only_shows_sales_created_by_logged_in_user(): void
    {
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['id' => (string) Str::uuid()]
        );

        $otherAdminUser = User::factory()->create([
            'role_id' => $adminRole->id,
            'branch_id' => $this->branch->id,
        ]);

        $saleOfUser = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-ADMIN-1',
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'create_by' => $this->user->id,
            'date' => '2026-07-06 12:00:00',
            'subtotal' => 1000,
            'grand_total' => 1000,
            'status' => 'completed',
        ]);

        $saleOfOther = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-ADMIN-2',
            'branch_id' => $this->branch->id,
            'user_id' => $otherAdminUser->id,
            'create_by' => $otherAdminUser->id,
            'date' => '2026-07-06 12:00:00',
            'subtotal' => 1000,
            'grand_total' => 1000,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user)->get(route('sales.index'));

        $response->assertStatus(200);
        $response->assertViewHas('sales');

        $salesPassed = $response->viewData('sales');
        $this->assertTrue($salesPassed->contains('id', $saleOfUser->id));
        $this->assertFalse($salesPassed->contains('id', $saleOfOther->id));
    }

    public function test_cabang_sales_creation_decrements_branch_product_stock(): void
    {
        $cabangRole = Role::firstOrCreate(
            ['name' => 'cabang'],
            ['id' => (string) Str::uuid()]
        );

        $cabangUser = User::factory()->create([
            'role_id' => $cabangRole->id,
            'branch_id' => $this->branch->id,
        ]);

        $category = Category::create([
            'id' => (string) Str::uuid(),
            'name' => 'Kategori Test',
        ]);

        $product = Product::create([
            'id' => (string) Str::uuid(),
            'category_id' => $category->id,
            'sku' => 'SKU-STOCK-DEC',
            'name' => 'Product Stock Dec',
            'buy_price' => 1000,
            'sell_price' => 2000,
        ]);

        // Create starting stock of 10
        ProductStock::create([
            'id' => (string) Str::uuid(),
            'product_id' => $product->id,
            'branch_id' => $this->branch->id,
            'stock' => 10,
            'minimum_stock' => 1,
            'average_cost' => 1000,
        ]);

        $payload = [
            'invoice' => 'INV-DEC-001',
            'branch_id' => $this->branch->id,
            'user_id' => $cabangUser->id,
            'date' => '2026-07-06 12:00:00',
            'subtotal' => 4000.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'grand_total' => 4000.00,
            'status' => 'completed',
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 2,
                    'price' => 2000.00,
                ],
            ],
        ];

        $response = $this->actingAs($cabangUser)->postJson(route('sales.store'), $payload);

        $response->assertStatus(201);

        // Verify stock has decremented from 10 to 8
        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $product->id,
            'branch_id' => $this->branch->id,
            'stock' => 8,
        ]);

        // Verify stock history record exists
        $this->assertDatabaseHas('stock_histories', [
            'product_id' => $product->id,
            'branch_id' => $this->branch->id,
            'type' => 'OUT',
            'qty' => 2,
            'previous_stock' => 10,
            'new_stock' => 8,
            'reference_type' => Sales::class,
            'user_id' => $cabangUser->id,
        ]);
    }
}
