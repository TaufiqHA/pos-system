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
}
