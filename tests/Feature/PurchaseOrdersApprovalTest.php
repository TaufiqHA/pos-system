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

    public function test_approved_purchase_order_is_hidden_on_dashboard_after_midnight(): void
    {
        // Assert it is currently visible on the dashboard as Pending
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee($this->purchaseOrder->po_number);

        // Approve the PO today
        $notes = json_decode($this->purchaseOrder->notes, true);
        $this->actingAs($this->admin)->putJson(route('purchase-orders.update', $this->purchaseOrder->id), [
            'po_number' => $this->purchaseOrder->po_number,
            'branch_id' => $this->purchaseOrder->branch_id,
            'user_id' => $this->purchaseOrder->user_id,
            'status' => 'Approved',
            'notes' => json_encode($notes),
            'sale_id' => null,
        ])->assertStatus(200);

        // Verify it is still visible on the dashboard today as Approved
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee($this->purchaseOrder->po_number);

        // Travel to the next day
        $this->travel(1)->days();

        // Verify it is now hidden from the dashboard
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertDontSee($this->purchaseOrder->po_number);
    }

    public function test_approving_purchase_order_with_credit_creates_debt(): void
    {
        $notes = json_decode($this->purchaseOrder->notes, true);
        $notes['payment_method'] = 'KREDIT';

        $response = $this->actingAs($this->admin)->putJson(route('purchase-orders.update', $this->purchaseOrder->id), [
            'po_number' => $this->purchaseOrder->po_number,
            'branch_id' => $this->purchaseOrder->branch_id,
            'user_id' => $this->purchaseOrder->user_id,
            'status' => 'Approved',
            'notes' => json_encode($notes),
            'sale_id' => null,
        ]);

        $response->assertStatus(200);

        $this->purchaseOrder = $this->purchaseOrder->fresh();
        $this->assertEquals('Approved', $this->purchaseOrder->status);

        $this->assertNotEmpty($this->purchaseOrder->sale_id);
        $this->assertDatabaseHas('sales', [
            'id' => $this->purchaseOrder->sale_id,
            'branch_id' => $this->branch->id,
            'user_id' => $this->cabangUser->id,
            'status' => 'BELUM BAYAR',
        ]);

        $this->assertDatabaseHas('sales_payments', [
            'sale_id' => $this->purchaseOrder->sale_id,
            'method' => 'KREDIT',
            'status' => 'BELUM BAYAR',
        ]);

        $this->assertDatabaseHas('debts', [
            'debtor_type' => 'branch',
            'debtor_branch_id' => $this->branch->id,
            'creditor_type' => 'branch',
            'creditor_branch_id' => 'BRC-001',
            'source_type' => 'sale',
            'sale_id' => $this->purchaseOrder->sale_id,
            'total_amount' => 5000000,
            'paid_amount' => 0,
            'remaining_amount' => 5000000,
            'status' => 'unpaid',
        ]);
    }

    public function test_completed_purchase_order_is_hidden_on_dashboard_after_midnight(): void
    {
        // Assert it is currently visible on the dashboard as Pending
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee($this->purchaseOrder->po_number);

        // Update status to Completed today
        $this->purchaseOrder->update([
            'status' => 'Completed',
            'updated_at' => now(),
        ]);

        // Verify it is still visible on the dashboard today as Completed
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee($this->purchaseOrder->po_number);

        // Travel to the next day
        $this->travel(1)->days();

        // Verify it is now hidden from the dashboard
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertDontSee($this->purchaseOrder->po_number);
    }
}
