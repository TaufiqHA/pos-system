<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Branch;
use App\Models\Suppliers;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchases;
use App\Models\PurchaseItem;
use Illuminate\Support\Str;

class PurchaseItemTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Branch $branch;
    private Suppliers $supplier;
    private Category $category;
    private Product $product;
    private Purchases $purchase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Bandung',
            'address' => 'Jl. Merdeka No. 10',
            'phone' => '08123456789',
            'wilayah_id' => 'Jawa Barat',
            'notes' => 'Kantor Cabang Baru',
        ]);

        $this->user->update(['branch_id' => $this->branch->id]);

        $this->supplier = Suppliers::create([
            'id' => (string) Str::uuid(),
            'name' => 'Supplier A',
            'contact_name' => 'John Doe',
            'phone' => '08123456789',
            'email' => 'supplier@example.com',
            'address' => 'Jl. Merdeka No. 1',
            'notes' => 'Catatan supplier A',
        ]);

        $this->category = Category::create([
            'id' => (string) Str::uuid(),
            'name' => 'Elektronik',
        ]);

        $this->product = Product::create([
            'id' => (string) Str::uuid(),
            'category_id' => $this->category->id,
            'sku' => 'PROD-SKU01',
            'name' => 'Kabel HDMI',
            'unit' => 'pcs',
            'buy_price' => 15000.00,
            'sell_price' => 20000.00,
        ]);

        $this->purchase = Purchases::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'PUR-20260704-TEST01',
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'date' => '2026-07-04 20:00:00',
            'subtotal' => 100000.00,
            'discount' => 10000.00,
            'tax' => 9000.00,
            'grand_total' => 99000.00,
            'status' => 'completed',
        ]);
    }

    public function test_can_create_purchase_item(): void
    {
        $payload = [
            'purchase_id' => $this->purchase->id,
            'product_id' => $this->product->id,
            'sku' => $this->product->sku,
            'product_name' => $this->product->name,
            'unit' => $this->product->unit,
            'qty' => 5,
            'price' => 15000.00,
            'subtotal' => 75000.00,
        ];

        $response = $this->actingAs($this->user)->postJson(route('purchase-items.store'), $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'purchase_id',
                    'product_id',
                    'sku',
                    'product_name',
                    'unit',
                    'qty',
                    'price',
                    'subtotal',
                    'created_at',
                    'updated_at',
                ]
            ]);

        $this->assertDatabaseHas('purchase_items', [
            'purchase_id' => $this->purchase->id,
            'product_id' => $this->product->id,
            'qty' => 5,
            'price' => 15000.00,
            'subtotal' => 75000.00,
        ]);
    }

    public function test_can_show_purchase_item(): void
    {
        $purchaseItem = PurchaseItem::create([
            'id' => (string) Str::uuid(),
            'purchase_id' => $this->purchase->id,
            'product_id' => $this->product->id,
            'sku' => $this->product->sku,
            'product_name' => $this->product->name,
            'unit' => $this->product->unit,
            'qty' => 5,
            'price' => 15000.00,
            'subtotal' => 75000.00,
        ]);

        $response = $this->actingAs($this->user)->getJson(route('purchase-items.show', $purchaseItem->id));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $purchaseItem->id,
                'qty' => 5,
                'sku' => $this->product->sku,
            ]);
    }

    public function test_can_update_purchase_item(): void
    {
        $purchaseItem = PurchaseItem::create([
            'id' => (string) Str::uuid(),
            'purchase_id' => $this->purchase->id,
            'product_id' => $this->product->id,
            'sku' => $this->product->sku,
            'product_name' => $this->product->name,
            'unit' => $this->product->unit,
            'qty' => 5,
            'price' => 15000.00,
            'subtotal' => 75000.00,
        ]);

        $payload = [
            'qty' => 10,
            'price' => 14000.00,
            'subtotal' => 140000.00,
        ];

        $response = $this->actingAs($this->user)->putJson(route('purchase-items.update', $purchaseItem->id), $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'qty' => 10,
                'price' => 14000.00,
                'subtotal' => 140000.00,
            ]);

        $this->assertDatabaseHas('purchase_items', [
            'id' => $purchaseItem->id,
            'qty' => 10,
            'price' => 14000.00,
            'subtotal' => 140000.00,
        ]);
    }

    public function test_can_delete_purchase_item(): void
    {
        $purchaseItem = PurchaseItem::create([
            'id' => (string) Str::uuid(),
            'purchase_id' => $this->purchase->id,
            'product_id' => $this->product->id,
            'sku' => $this->product->sku,
            'product_name' => $this->product->name,
            'unit' => $this->product->unit,
            'qty' => 5,
            'price' => 15000.00,
            'subtotal' => 75000.00,
        ]);

        $response = $this->actingAs($this->user)->deleteJson(route('purchase-items.destroy', $purchaseItem->id));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Purchase item berhasil dihapus'
            ]);

        $this->assertDatabaseMissing('purchase_items', [
            'id' => $purchaseItem->id,
        ]);
    }
}
