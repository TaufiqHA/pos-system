<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Role;
use App\Models\SalesItem;
use App\Models\Suppliers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MovingAverageInventoryTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $cabangUser;

    private Branch $branch;

    private Suppliers $supplier;

    private Category $category;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['id' => (string) Str::uuid()]);
        $cabangRole = Role::firstOrCreate(['name' => 'cabang'], ['id' => (string) Str::uuid()]);

        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Test',
            'address' => 'Jl. Test No. 123',
            'phone' => '0812345678',
            'wilayah_id' => 'Jawa Barat',
        ]);

        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
            'branch_id' => $this->branch->id,
        ]);

        $this->cabangUser = User::factory()->create([
            'role_id' => $cabangRole->id,
            'branch_id' => $this->branch->id,
        ]);

        $this->supplier = Suppliers::create([
            'id' => (string) Str::uuid(),
            'name' => 'Supplier Test',
            'contact_name' => 'Jane Doe',
            'phone' => '0812345679',
            'email' => 'supplier.test@example.com',
            'address' => 'Jl. Supplier No. 1',
        ]);

        $this->category = Category::create([
            'id' => (string) Str::uuid(),
            'name' => 'Sembako',
        ]);

        $this->product = Product::create([
            'id' => (string) Str::uuid(),
            'category_id' => $this->category->id,
            'sku' => 'PROD-001',
            'name' => 'Beras Pandan Wangi',
            'buy_price' => 20000,
            'sell_price' => 30000,
            'unit' => 'kg',
        ]);
    }

    public function test_moving_average_cogs_recalculation_flow(): void
    {
        // 1. Pembelian 1: Qty 10 @ Rp 20.000 (Total Rp 200.000) - Dilakukan oleh Admin
        $purchasePayload1 = [
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'user_id' => $this->adminUser->id,
            'date' => now()->format('Y-m-d H:i:s'),
            'subtotal' => 200000.00,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 200000.00,
            'status' => 'completed',
            'payment_method' => 'TUNAI',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 10,
                    'price' => 20000,
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)->postJson(route('purchases.store'), $purchasePayload1);
        $response->assertStatus(201);

        // Pastikan stok awal bertambah dan average_cost bernilai Rp 20.000
        $stock1 = ProductStock::where('product_id', $this->product->id)
            ->where('branch_id', $this->branch->id)
            ->first();

        $this->assertNotNull($stock1);
        $this->assertEquals(10, $stock1->stock);
        $this->assertEquals(20000, (float) $stock1->average_cost);

        // 2. Penjualan 1: Jual 4 unit @ Rp 30.000 - Dilakukan oleh Cabang (Checkout)
        $salePayload1 = [
            'branch_id' => $this->branch->id,
            'user_id' => $this->cabangUser->id,
            'date' => now()->format('Y-m-d H:i:s'),
            'subtotal' => 120000.00,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 120000.00,
            'status' => 'completed',
            'payment_method' => 'TUNAI',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 4,
                    'price' => 30000,
                ],
            ],
        ];

        $response = $this->actingAs($this->cabangUser)->postJson(route('sales.store'), $salePayload1);
        $response->assertStatus(201);

        // Pastikan sisa stok berkurang menjadi 6 unit
        $stock1->refresh();
        $this->assertEquals(6, $stock1->stock);

        // Pastikan HPP (cost) yang tercatat di SalesItem adalah Rp 20.000 (sesuai average_cost saat itu)
        $saleId1 = $response->json('id');
        $saleItem1 = SalesItem::where('sale_id', $saleId1)
            ->where('product_id', $this->product->id)
            ->first();
        $this->assertNotNull($saleItem1);
        $this->assertEquals(20000, (float) $saleItem1->cost);

        // 3. Pembelian 2: Qty 50 @ Rp 18.000 (Total Rp 900.000) - Dilakukan oleh Admin
        $purchasePayload2 = [
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'user_id' => $this->adminUser->id,
            'date' => now()->format('Y-m-d H:i:s'),
            'subtotal' => 900000.00,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 900000.00,
            'status' => 'completed',
            'payment_method' => 'TUNAI',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 50,
                    'price' => 18000,
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)->postJson(route('purchases.store'), $purchasePayload2);
        $response->assertStatus(201);

        // Hitung manual moving average:
        // (6 * 20.000 + 50 * 18.000) / 56 = 1.020.000 / 56 = 18.214,2857...
        $expectedAverage = (6 * 20000 + 50 * 18000) / 56;

        $stock1->refresh();
        $this->assertEquals(56, $stock1->stock);
        $this->assertEquals(round($expectedAverage, 2), round((float) $stock1->average_cost, 2));

        // 4. Penjualan 2: Jual 10 unit @ Rp 30.000 - Dilakukan oleh Cabang
        $salePayload2 = [
            'branch_id' => $this->branch->id,
            'user_id' => $this->cabangUser->id,
            'date' => now()->format('Y-m-d H:i:s'),
            'subtotal' => 300000.00,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 300000.00,
            'status' => 'completed',
            'payment_method' => 'TUNAI',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 10,
                    'price' => 30000,
                ],
            ],
        ];

        $response = $this->actingAs($this->cabangUser)->postJson(route('sales.store'), $salePayload2);
        $response->assertStatus(201);

        // Pastikan sisa stok berkurang menjadi 46 unit
        $stock1->refresh();
        $this->assertEquals(46, $stock1->stock);

        // Pastikan HPP (cost) yang tercatat di SalesItem baru adalah sesuai average_cost (~18.214,29)
        $saleId2 = $response->json('id');
        $saleItem2 = SalesItem::where('sale_id', $saleId2)
            ->where('product_id', $this->product->id)
            ->first();

        $this->assertNotNull($saleItem2);
        $this->assertEquals(round($expectedAverage, 2), round((float) $saleItem2->cost, 2));
    }
}
