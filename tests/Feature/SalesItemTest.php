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

class SalesItemTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Branch $branch;

    private Category $category;

    private Product $product;

    private Sales $sales;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['id' => (string) Str::uuid()]);

        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Bandung',
            'address' => 'Jl. Merdeka No. 10',
            'phone' => '08123456789',
            'wilayah_id' => 'Jawa Barat',
            'notes' => 'Kantor Cabang Baru',
        ]);

        $this->user = User::factory()->create([
            'role_id' => $adminRole->id,
            'branch_id' => $this->branch->id,
        ]);

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

        $this->sales = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-20260704-0001',
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'date' => '2026-07-04 22:00:00',
            'subtotal' => 6000000.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'grand_total' => 6000000.00,
            'status' => 'completed',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_sales_items(): void
    {
        $response = $this->postJson('/admin/sales-items', []);
        $response->assertStatus(401);

        $response = $this->getJson('/admin/sales-items/some-id');
        $response->assertStatus(401);

        $response = $this->putJson('/admin/sales-items/some-id', []);
        $response->assertStatus(401);

        $response = $this->deleteJson('/admin/sales-items/some-id');
        $response->assertStatus(401);
    }

    public function test_can_create_sales_item(): void
    {
        $itemId = (string) Str::uuid();
        $payload = [
            'id' => $itemId,
            'sale_id' => $this->sales->id,
            'product_id' => $this->product->id,
            'sku' => $this->product->sku,
            'product_name' => $this->product->name,
            'unit' => 'pcs',
            'qty' => 1,
            'price' => 6000000,
            'cost' => 5000000,
            'subtotal' => 6000000,
            'is_wholesale' => false,
        ];

        $response = $this->actingAs($this->user)->postJson('/admin/sales-items', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'id' => $itemId,
                'sku' => $this->product->sku,
            ]);

        $this->assertDatabaseHas('sale_items', [
            'id' => $itemId,
            'sku' => $this->product->sku,
        ]);
    }

    public function test_cannot_create_sales_item_with_invalid_data(): void
    {
        $payload = [
            'id' => '',
            'sale_id' => 'invalid-sale',
            'product_id' => 'invalid-product',
            'sku' => '',
            'product_name' => '',
            'unit' => '',
            'qty' => 0,
            'price' => '',
            'cost' => '',
            'subtotal' => '',
        ];

        try {
            $response = $this->actingAs($this->user)->postJson('/admin/sales-items', $payload);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['id', 'sale_id', 'product_id', 'sku', 'product_name', 'unit', 'qty', 'price', 'cost', 'subtotal']);
        } catch (\Throwable $e) {
            dd($e->getMessage(), $e->getTraceAsString());
        }
    }

    public function test_can_show_sales_item(): void
    {
        $itemId = (string) Str::uuid();
        SalesItem::create([
            'id' => $itemId,
            'sale_id' => $this->sales->id,
            'product_id' => $this->product->id,
            'sku' => $this->product->sku,
            'product_name' => $this->product->name,
            'unit' => 'pcs',
            'qty' => 1,
            'price' => 6000000,
            'cost' => 5000000,
            'subtotal' => 6000000,
            'is_wholesale' => false,
        ]);

        $response = $this->actingAs($this->user)->getJson("/admin/sales-items/{$itemId}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $itemId,
                'product_name' => $this->product->name,
            ]);
    }

    public function test_can_update_sales_item(): void
    {
        $itemId = (string) Str::uuid();
        SalesItem::create([
            'id' => $itemId,
            'sale_id' => $this->sales->id,
            'product_id' => $this->product->id,
            'sku' => $this->product->sku,
            'product_name' => $this->product->name,
            'unit' => 'pcs',
            'qty' => 1,
            'price' => 6000000,
            'cost' => 5000000,
            'subtotal' => 6000000,
            'is_wholesale' => false,
        ]);

        $payload = [
            'qty' => 2,
            'subtotal' => 12000000,
        ];

        $response = $this->actingAs($this->user)->putJson("/admin/sales-items/{$itemId}", $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $itemId,
                'qty' => 2,
                'subtotal' => 12000000,
            ]);

        $this->assertDatabaseHas('sale_items', [
            'id' => $itemId,
            'qty' => 2,
            'subtotal' => 12000000,
        ]);
    }

    public function test_can_delete_sales_item(): void
    {
        $itemId = (string) Str::uuid();
        SalesItem::create([
            'id' => $itemId,
            'sale_id' => $this->sales->id,
            'product_id' => $this->product->id,
            'sku' => $this->product->sku,
            'product_name' => $this->product->name,
            'unit' => 'pcs',
            'qty' => 1,
            'price' => 6000000,
            'cost' => 5000000,
            'subtotal' => 6000000,
            'is_wholesale' => false,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/admin/sales-items/{$itemId}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Sales Item deleted successfully',
            ]);

        $this->assertDatabaseMissing('sale_items', [
            'id' => $itemId,
        ]);
    }
}
