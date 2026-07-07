<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sales;
use App\Models\SalesItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminLaporanTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['id' => (string) Str::uuid(), 'description' => 'Administrator']
        );

        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Test',
            'address' => 'Jl. Test No. 123',
            'phone' => '081234567890',
            'wilayah_id' => 'Jawa Barat',
        ]);

        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
            'branch_id' => $this->branch->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_laporan(): void
    {
        $response = $this->get(route('admin.laporan'));
        $response->assertStatus(302); // Redirect to login
    }

    public function test_admin_can_access_laporan_with_metrics(): void
    {
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
            'user_id' => $this->adminUser->id,
            'create_by' => $this->adminUser->id,
            'date' => '2026-07-04 22:00:00',
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

        $response = $this->actingAs($this->adminUser)->get(route('admin.laporan'));

        $response->assertStatus(200);
        $response->assertViewHas('totalOmset', 110000.00);
        $response->assertViewHas('totalKeuntungan', 10000.00);
        $response->assertViewHas('barangTerjual', 10);
        $response->assertViewHas('chartLabels');
        $response->assertViewHas('chartValues');
        $response->assertViewHas('produkTerlaris');
        $response->assertViewHas('transaksiTerakhir');
    }

    public function test_laporan_filters_total_omset_and_chart_by_logged_in_user(): void
    {
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

        // Sale by current admin user
        $sale1 = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-20260704-0001',
            'branch_id' => $this->branch->id,
            'user_id' => $this->adminUser->id,
            'create_by' => $this->adminUser->id,
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

        // Sale by other user
        $otherUser = User::factory()->create([
            'role_id' => $this->adminUser->role_id,
            'branch_id' => $this->branch->id,
        ]);

        $sale2 = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-20260704-0002',
            'branch_id' => $this->branch->id,
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

        $response = $this->actingAs($this->adminUser)->get(route('admin.laporan'));

        $response->assertStatus(200);
        // Total Omset must only include sale1 (110000.00) and exclude sale2 (50000.00)
        $response->assertViewHas('totalOmset', 110000.00);

        // Total Keuntungan must only include profit from sale1 (10000.00)
        $response->assertViewHas('totalKeuntungan', 10000.00);

        // Barang Terjual must only include qty from sale1 (10)
        $response->assertViewHas('barangTerjual', 10);

        // Chart values should only have sale1's amount (110000.00) for today
        $chartValues = $response->viewData('chartValues');
        $this->assertEquals(110000.00, end($chartValues));

        // Produk Terlaris should only count sale1's items
        $produkTerlaris = $response->viewData('produkTerlaris');
        $this->assertCount(1, $produkTerlaris);
        $this->assertEquals('Produk A', $produkTerlaris[0]->product_name);
        $this->assertEquals(10, $produkTerlaris[0]->total_terjual);
        $this->assertEquals(110000.00, $produkTerlaris[0]->total_omset);

        // Transaksi Terakhir should only include sale1
        $transaksiTerakhir = $response->viewData('transaksiTerakhir');
        $this->assertCount(1, $transaksiTerakhir);
        $this->assertEquals($sale1->id, $transaksiTerakhir[0]->id);
    }
}
