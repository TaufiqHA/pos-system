<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Outlets;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sales;
use App\Models\SalesItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CabangLaporanTest extends TestCase
{
    use RefreshDatabase;

    private User $cabangUser;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $cabangRole = Role::firstOrCreate(
            ['name' => 'cabang'],
            ['id' => (string) Str::uuid(), 'description' => 'Pengelola Cabang']
        );

        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Test',
            'address' => 'Jl. Test No. 123',
            'phone' => '081234567890',
            'wilayah_id' => 'Jawa Barat',
        ]);

        $this->cabangUser = User::factory()->create([
            'role_id' => $cabangRole->id,
            'branch_id' => $this->branch->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_laporan_cabang(): void
    {
        $response = $this->get(route('cabang.laporan'));
        $response->assertStatus(302); // Redirect to login
    }

    public function test_cabang_user_can_access_laporan_with_metrics(): void
    {
        $outlet = Outlets::create([
            'id' => (string) Str::uuid(),
            'branch_id' => $this->branch->id,
            'name' => 'Outlet Test',
            'address' => 'Jl. Outlet No. 1',
            'phone' => '08987654321',
        ]);

        $category = Category::create([
            'id' => (string) Str::uuid(),
            'name' => 'Kategori Test',
        ]);

        $product = Product::create([
            'id' => (string) Str::uuid(),
            'sku' => 'SKU-001',
            'name' => 'Produk A',
            'category_id' => $category->id,
            'buy_price' => 10000.00,
            'sell_price' => 11000.00,
            'unit' => 'pcs',
        ]);

        $sale = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-20260704-0001',
            'branch_id' => $this->branch->id,
            'outlet_id' => $outlet->id,
            'user_id' => $this->cabangUser->id,
            'create_by' => $this->cabangUser->id,
            'date' => now()->toDateString().' 12:00:00',
            'subtotal' => 110000.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'grand_total' => 110000.00,
            'status' => 'completed',
        ]);

        SalesItem::create([
            'id' => (string) Str::uuid(),
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'sku' => $product->sku,
            'product_name' => $product->name,
            'unit' => $product->unit,
            'qty' => 10,
            'price' => 11000.00,
            'cost' => 10000.00,
            'subtotal' => 110000.00,
            'is_wholesale' => false,
        ]);

        $response = $this->actingAs($this->cabangUser)->get(route('cabang.laporan'));

        $response->assertStatus(200);
        $response->assertViewHas('totalOmset', 110000.00);
        $response->assertViewHas('totalKeuntungan', 10000.00);
        $response->assertViewHas('barangTerjual', 10);
        $response->assertViewHas('chartLabels');
        $response->assertViewHas('chartValues');
        $response->assertViewHas('produkTerlaris');
        $response->assertViewHas('transaksiTerakhir');
    }

    public function test_laporan_cabang_excludes_sales_from_other_users_or_branches(): void
    {
        $outlet = Outlets::create([
            'id' => (string) Str::uuid(),
            'branch_id' => $this->branch->id,
            'name' => 'Outlet Test',
            'address' => 'Jl. Outlet No. 1',
            'phone' => '08987654321',
        ]);

        $category = Category::create([
            'id' => (string) Str::uuid(),
            'name' => 'Kategori Test',
        ]);

        $product = Product::create([
            'id' => (string) Str::uuid(),
            'sku' => 'SKU-001',
            'name' => 'Produk A',
            'category_id' => $category->id,
            'buy_price' => 10000.00,
            'sell_price' => 11000.00,
            'unit' => 'pcs',
        ]);

        // 1. Sale by current cabang user (Valid)
        $sale1 = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-20260704-0001',
            'branch_id' => $this->branch->id,
            'outlet_id' => $outlet->id,
            'user_id' => $this->cabangUser->id,
            'create_by' => $this->cabangUser->id,
            'date' => now()->toDateString().' 12:00:00',
            'subtotal' => 110000.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'grand_total' => 110000.00,
            'status' => 'completed',
        ]);

        SalesItem::create([
            'id' => (string) Str::uuid(),
            'sale_id' => $sale1->id,
            'product_id' => $product->id,
            'sku' => $product->sku,
            'product_name' => $product->name,
            'unit' => $product->unit,
            'qty' => 10,
            'price' => 11000.00,
            'cost' => 10000.00,
            'subtotal' => 110000.00,
            'is_wholesale' => false,
        ]);

        // 2. Sale by other user in the same branch (Should be excluded)
        $otherUser = User::factory()->create([
            'role_id' => $this->cabangUser->role_id,
            'branch_id' => $this->branch->id,
        ]);

        $sale2 = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-20260704-0002',
            'branch_id' => $this->branch->id,
            'outlet_id' => $outlet->id,
            'user_id' => $otherUser->id,
            'create_by' => $otherUser->id,
            'date' => now()->toDateString().' 13:00:00',
            'subtotal' => 50000.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'grand_total' => 50000.00,
            'status' => 'completed',
        ]);

        SalesItem::create([
            'id' => (string) Str::uuid(),
            'sale_id' => $sale2->id,
            'product_id' => $product->id,
            'sku' => $product->sku,
            'product_name' => $product->name,
            'unit' => $product->unit,
            'qty' => 5,
            'price' => 10000.00,
            'cost' => 9000.00,
            'subtotal' => 50000.00,
            'is_wholesale' => false,
        ]);

        // 3. Sale by current user but to a different branch (Should be excluded since branch is different)
        $otherBranch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Lain',
            'address' => 'Jl. Lain No. 456',
            'phone' => '081234567891',
            'wilayah_id' => 'Jawa Barat',
        ]);

        $sale3 = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-20260704-0003',
            'branch_id' => $otherBranch->id,
            'outlet_id' => $outlet->id,
            'user_id' => $this->cabangUser->id,
            'create_by' => $this->cabangUser->id,
            'date' => now()->toDateString().' 14:00:00',
            'subtotal' => 80000.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'grand_total' => 80000.00,
            'status' => 'completed',
        ]);

        SalesItem::create([
            'id' => (string) Str::uuid(),
            'sale_id' => $sale3->id,
            'product_id' => $product->id,
            'sku' => $product->sku,
            'product_name' => $product->name,
            'unit' => $product->unit,
            'qty' => 8,
            'price' => 10000.00,
            'cost' => 9000.00,
            'subtotal' => 80000.00,
            'is_wholesale' => false,
        ]);

        // 4. Sale by current user, same branch, but NOT to an outlet (outlet_id is null - e.g., center/direct) (Should be excluded)
        $sale4 = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-20260704-0004',
            'branch_id' => $this->branch->id,
            'outlet_id' => null,
            'user_id' => $this->cabangUser->id,
            'create_by' => $this->cabangUser->id,
            'date' => now()->toDateString().' 15:00:00',
            'subtotal' => 30000.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'grand_total' => 30000.00,
            'status' => 'completed',
        ]);

        SalesItem::create([
            'id' => (string) Str::uuid(),
            'sale_id' => $sale4->id,
            'product_id' => $product->id,
            'sku' => $product->sku,
            'product_name' => $product->name,
            'unit' => $product->unit,
            'qty' => 3,
            'price' => 10000.00,
            'cost' => 9000.00,
            'subtotal' => 30000.00,
            'is_wholesale' => false,
        ]);

        $response = $this->actingAs($this->cabangUser)->get(route('cabang.laporan'));

        $response->assertStatus(200);
        // Only sale1 (110000.00) should be included.
        $response->assertViewHas('totalOmset', 110000.00);
        $response->assertViewHas('totalKeuntungan', 10000.00);
        $response->assertViewHas('barangTerjual', 10);

        // Chart values should only have sale1's amount (110000.00) for today
        $chartValues = $response->viewData('chartValues');
        $this->assertEquals(110000.00, end($chartValues));

        // Transaksi Terakhir should only include sale1
        $transaksiTerakhir = $response->viewData('transaksiTerakhir');
        $this->assertCount(1, $transaksiTerakhir);
        $this->assertEquals($sale1->id, $transaksiTerakhir[0]->id);
    }
}
