<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Outlets;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Role;
use App\Models\Sales;
use App\Models\SalesItem;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminLaporanCabangTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $cabangUser;

    private Branch $adminBranch;

    private Branch $cabangBranch;

    private Wilayah $wilayah;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['id' => (string) Str::uuid(), 'description' => 'Administrator']
        );

        $cabangRole = Role::firstOrCreate(
            ['name' => 'cabang'],
            ['id' => (string) Str::uuid(), 'description' => 'Branch User']
        );

        $this->wilayah = Wilayah::create([
            'id' => 'Jawa Barat',
            'name' => 'Jawa Barat',
        ]);

        // Branch connected to admin user
        $this->adminBranch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Kantor Pusat Admin',
            'address' => 'Jl. Merdeka No. 10',
            'phone' => '081234567890',
            'wilayah_id' => $this->wilayah->id,
        ]);

        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
            'branch_id' => $this->adminBranch->id,
        ]);

        // Regular branch with no admin user
        $this->cabangBranch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Bandung',
            'address' => 'Jl. Bandung No. 5',
            'phone' => '081122334455',
            'wilayah_id' => $this->wilayah->id,
        ]);

        $this->cabangUser = User::factory()->create([
            'role_id' => $cabangRole->id,
            'branch_id' => $this->cabangBranch->id,
        ]);

        $this->category = Category::create([
            'id' => (string) Str::uuid(),
            'name' => 'Elektronik',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_laporan_cabang(): void
    {
        $response = $this->get(route('admin.laporan-cabang'));
        $response->assertStatus(302); // Redirect to login
    }

    public function test_admin_can_access_laporan_cabang_manajemen_tab(): void
    {
        $outlet = Outlets::create([
            'id' => (string) Str::uuid(),
            'branch_id' => $this->cabangBranch->id,
            'name' => 'Outlet Cihampelas',
            'address' => 'Jl. Cihampelas',
            'phone' => '08987654321',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.laporan-cabang', ['tab' => 'manajemen']));

        $response->assertStatus(200);
        $response->assertViewHas('tab', 'manajemen');
        $response->assertViewHas('regions');
        $response->assertSee('Jawa Barat');
        $response->assertSee('Cabang Bandung');
        $response->assertSee('1 Outlet');
    }

    public function test_admin_can_access_laporan_cabang_stok_tab_with_filters(): void
    {
        $product = Product::create([
            'id' => (string) Str::uuid(),
            'sku' => 'SKU-001',
            'name' => 'Laptop Asus',
            'category_id' => $this->category->id,
            'buy_price' => 10000000.00,
            'sell_price' => 12000000.00,
            'unit' => 'pcs',
        ]);

        $stock = ProductStock::create([
            'id' => (string) Str::uuid(),
            'product_id' => $product->id,
            'branch_id' => $this->cabangBranch->id,
            'stock' => 15,
            'minimum_stock' => 5,
            'average_cost' => 10000000.00,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.laporan-cabang', [
            'tab' => 'stok',
            'wilayah_id' => $this->wilayah->id,
            'branch_id' => $this->cabangBranch->id,
            'category_id' => $this->category->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('tab', 'stok');
        $response->assertViewHas('stocks');
        $response->assertSee('Laptop Asus');
        $response->assertSee('SKU-001');
        $response->assertSee('Elektronik');
        $response->assertSee('15 pcs');
    }

    public function test_admin_can_access_laporan_cabang_transaksi_tab_with_filters(): void
    {
        $outlet = Outlets::create([
            'id' => (string) Str::uuid(),
            'branch_id' => $this->cabangBranch->id,
            'name' => 'Outlet Cihampelas',
            'address' => 'Jl. Cihampelas',
            'phone' => '08987654321',
        ]);

        $product = Product::create([
            'id' => (string) Str::uuid(),
            'sku' => 'SKU-001',
            'name' => 'Laptop Asus',
            'category_id' => $this->category->id,
            'buy_price' => 10000000.00,
            'sell_price' => 12000000.00,
            'unit' => 'pcs',
        ]);

        $sale = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-TEST-001',
            'branch_id' => $this->cabangBranch->id,
            'outlet_id' => $outlet->id,
            'user_id' => $this->adminUser->id,
            'create_by' => $this->adminUser->id,
            'date' => now()->toDateTimeString(),
            'subtotal' => 12000000.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'grand_total' => 12000000.00,
            'status' => 'completed',
        ]);

        SalesItem::create([
            'id' => (string) Str::uuid(),
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'sku' => $product->sku,
            'product_name' => $product->name,
            'unit' => $product->unit,
            'qty' => 1,
            'price' => 12000000.00,
            'cost' => 10000000.00,
            'subtotal' => 12000000.00,
            'is_wholesale' => false,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.laporan-cabang', [
            'tab' => 'transaksi',
            'wilayah_id' => $this->wilayah->id,
            'branch_id' => $this->cabangBranch->id,
            'category_id' => $this->category->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('tab', 'transaksi');
        $response->assertViewHas('transactions');
        $response->assertSee('INV-TEST-001');
        $response->assertSee('Cabang Bandung');
        $response->assertSee('Outlet Cihampelas');
        $response->assertSee('Rp 12.000.000');
    }

    public function test_admin_connected_branch_is_excluded_from_stok_and_transaksi(): void
    {
        // 1. Set up regular branch items
        $product = Product::create([
            'id' => (string) Str::uuid(),
            'sku' => 'SKU-REGULAR',
            'name' => 'Laptop Regular',
            'category_id' => $this->category->id,
            'buy_price' => 1000.00,
            'sell_price' => 1200.00,
            'unit' => 'pcs',
        ]);

        // Regular Stock
        ProductStock::create([
            'id' => (string) Str::uuid(),
            'product_id' => $product->id,
            'branch_id' => $this->cabangBranch->id,
            'stock' => 50,
            'minimum_stock' => 5,
            'average_cost' => 1000.00,
        ]);

        // Admin Stock (should be excluded)
        ProductStock::create([
            'id' => (string) Str::uuid(),
            'product_id' => $product->id,
            'branch_id' => $this->adminBranch->id,
            'stock' => 999,
            'minimum_stock' => 5,
            'average_cost' => 1000.00,
        ]);

        // Regular Sale
        $outlet = Outlets::create([
            'id' => (string) Str::uuid(),
            'branch_id' => $this->cabangBranch->id,
            'name' => 'Outlet Cihampelas',
            'address' => 'Jl. Cihampelas',
            'phone' => '08987654321',
        ]);

        Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-REGULAR',
            'branch_id' => $this->cabangBranch->id,
            'outlet_id' => $outlet->id,
            'user_id' => $this->adminUser->id,
            'create_by' => $this->adminUser->id,
            'date' => now()->toDateTimeString(),
            'subtotal' => 1200.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'grand_total' => 1200.00,
            'status' => 'completed',
        ]);

        // Admin Sale (should be excluded)
        $adminOutlet = Outlets::create([
            'id' => (string) Str::uuid(),
            'branch_id' => $this->adminBranch->id,
            'name' => 'Outlet Admin',
            'address' => 'Jl. Admin',
            'phone' => '08987654322',
        ]);

        Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-ADMIN-EXCLUDE',
            'branch_id' => $this->adminBranch->id,
            'outlet_id' => $adminOutlet->id,
            'user_id' => $this->adminUser->id,
            'create_by' => $this->adminUser->id,
            'date' => now()->toDateTimeString(),
            'subtotal' => 5000.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'grand_total' => 5000.00,
            'status' => 'completed',
        ]);

        // 2. Query Stok Tab and verify
        $responseStok = $this->actingAs($this->adminUser)->get(route('admin.laporan-cabang', ['tab' => 'stok']));
        $responseStok->assertStatus(200);
        $responseStok->assertSee('Laptop Regular');
        $responseStok->assertSee('50 pcs');
        $responseStok->assertDontSee('999 pcs');
        $responseStok->assertDontSee('Kantor Pusat Admin');

        // 3. Query Transaksi Tab and verify
        $responseTransaksi = $this->actingAs($this->adminUser)->get(route('admin.laporan-cabang', ['tab' => 'transaksi']));
        $responseTransaksi->assertStatus(200);
        $responseTransaksi->assertSee('INV-REGULAR');
        $responseTransaksi->assertDontSee('INV-ADMIN-EXCLUDE');
        $responseTransaksi->assertDontSee('Outlet Admin');
    }
}
