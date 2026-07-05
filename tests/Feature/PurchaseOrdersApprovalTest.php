<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrders;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PurchaseOrdersApprovalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $cabangUser;

    private Branch $branch;

    private Category $category;

    private Product $product;

    private PurchaseOrders $purchaseOrder;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['id' => (string) Str::uuid()]);
        $cabangRole = Role::firstOrCreate(['name' => 'cabang'], ['id' => (string) Str::uuid()]);

        $this->admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'name' => 'Main Admin',
        ]);

        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Lucifer',
            'address' => 'Jl. Lucifer No. 666',
            'phone' => '08123456789',
        ]);

        Branch::create([
            'id' => 'BRC-001',
            'name' => 'Gudang Pusat',
            'address' => 'Jl. Pusat No. 1',
            'phone' => '08111111111',
            'wilayah_id' => null,
            'notes' => 'Gudang Pusat',
        ]);

        $this->cabangUser = User::factory()->create([
            'role_id' => $cabangRole->id,
            'branch_id' => $this->branch->id,
            'parent_id' => $this->admin->id,
            'name' => 'Cabang Admin',
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

        $notesData = [
            'user_notes' => 'Tolong segera dikirim ya.',
            'subtotal' => 5000000,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 5000000,
            'payment_method' => 'KREDIT',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'name' => $this->product->name,
                    'sku' => $this->product->sku,
                    'qty' => 1,
                    'price' => 5000000,
                ],
            ],
        ];

        $this->purchaseOrder = PurchaseOrders::create([
            'id' => (string) Str::uuid(),
            'po_number' => 'PO-20260705-TEST',
            'branch_id' => $this->branch->id,
            'user_id' => $this->cabangUser->id,
            'status' => 'Pending',
            'notes' => json_encode($notesData),
        ]);
    }

    public function test_updating_purchase_order_status_to_approved_creates_sale(): void
    {
        $notes = json_decode($this->purchaseOrder->notes, true);
        $notes['payment_method'] = 'TRANSFER'; // simulate editing payment method on approval

        $response = $this->actingAs($this->admin)->putJson(route('purchase-orders.update', $this->purchaseOrder->id), [
            'po_number' => $this->purchaseOrder->po_number,
            'branch_id' => $this->purchaseOrder->branch_id,
            'user_id' => $this->purchaseOrder->user_id,
            'status' => 'Approved',
            'notes' => json_encode($notes),
            'sale_id' => null,
        ]);

        $response->assertStatus(200);

        // Verify status is Approved
        $this->purchaseOrder = $this->purchaseOrder->fresh();
        $this->assertEquals('Approved', $this->purchaseOrder->status);

        // Verify sale_id is updated and points to a valid Sales record
        $this->assertNotEmpty($this->purchaseOrder->sale_id);
        $this->assertDatabaseHas('sales', [
            'id' => $this->purchaseOrder->sale_id,
            'branch_id' => $this->branch->id,
            'user_id' => $this->cabangUser->id,
            'subtotal' => 5000000,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 5000000,
        ]);

        // Verify SalesItem was created
        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $this->purchaseOrder->sale_id,
            'product_id' => $this->product->id,
            'sku' => $this->product->sku,
            'qty' => 1,
            'price' => 5000000,
        ]);

        // Verify SalesPayment was created
        $this->assertDatabaseHas('sales_payments', [
            'sale_id' => $this->purchaseOrder->sale_id,
            'method' => 'TRANSFER',
            'amount' => 5000000,
            'status' => 'LUNAS',
        ]);

        // Verify Deliveries was created
        $this->assertDatabaseHas('deliveries', [
            'sale_id' => $this->purchaseOrder->sale_id,
            'status' => 'PENDING',
        ]);
    }
}
