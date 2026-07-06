<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Debts;
use App\Models\DebtsPayment;
use App\Models\Product;
use App\Models\Purchases;
use App\Models\Role;
use App\Models\Suppliers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PurchasesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Branch $branch;

    private Suppliers $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['id' => (string) Str::uuid()]);
        $this->user = User::factory()->create(['role_id' => $adminRole->id]);

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
    }

    public function test_unauthenticated_user_cannot_access_purchases(): void
    {
        $response = $this->get(route('purchases.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_can_list_purchases_via_html(): void
    {
        $response = $this->actingAs($this->user)->get(route('purchases.index'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.purchases');
        $response->assertViewHas('purchases');
    }

    public function test_can_list_purchases_via_json(): void
    {
        $purchase = Purchases::create([
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

        $response = $this->actingAs($this->user)->getJson(route('purchases.index'));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $purchase->id,
                'invoice' => 'PUR-20260704-TEST01',
            ]);
    }

    public function test_create_purchase_redirects_to_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('purchases.create'));
        $response->assertStatus(302);
        $response->assertRedirect(route('purchases.index', ['action' => 'create']));
    }

    public function test_can_store_purchase(): void
    {
        $payload = [
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'date' => '2026-07-04 20:00:00',
            'subtotal' => 150000.00,
            'discount' => 5000.00,
            'tax' => 14500.00,
            'grand_total' => 159500.00,
            'status' => 'pending',
        ];

        $response = $this->actingAs($this->user)->postJson(route('purchases.store'), $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'invoice',
                    'supplier_id',
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
                ],
            ]);

        $this->assertDatabaseHas('purchases', [
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);
    }

    public function test_can_show_purchase(): void
    {
        $purchase = Purchases::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'PUR-20260704-TEST02',
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

        $response = $this->actingAs($this->user)->getJson(route('purchases.show', $purchase->id));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $purchase->id,
                'invoice' => 'PUR-20260704-TEST02',
            ]);
    }

    public function test_edit_purchase_redirects_to_index(): void
    {
        $purchase = Purchases::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'PUR-20260704-TEST03',
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

        $response = $this->actingAs($this->user)->get(route('purchases.edit', $purchase->id));
        $response->assertStatus(302);
        $response->assertRedirect(route('purchases.index', ['action' => 'edit', 'id' => $purchase->id]));
    }

    public function test_can_update_purchase(): void
    {
        $purchase = Purchases::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'PUR-20260704-TEST04',
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

        $payload = [
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'date' => '2026-07-04 21:00:00',
            'subtotal' => 120000.00,
            'discount' => 20000.00,
            'tax' => 10000.00,
            'grand_total' => 110000.00,
            'status' => 'updated_status',
        ];

        $response = $this->actingAs($this->user)->putJson(route('purchases.update', $purchase->id), $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'updated_status',
                'subtotal' => 120000.00,
            ]);

        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'status' => 'updated_status',
        ]);
    }

    public function test_can_delete_purchase(): void
    {
        $purchase = Purchases::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'PUR-20260704-TEST05',
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

        $response = $this->actingAs($this->user)->deleteJson(route('purchases.destroy', $purchase->id));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Purchase berhasil dihapus',
            ]);

        $this->assertDatabaseMissing('purchases', [
            'id' => $purchase->id,
        ]);
    }

    public function test_storing_purchase_increases_stock_when_status_is_lunas(): void
    {
        $category = Category::create([
            'id' => (string) Str::uuid(),
            'name' => 'Elektronik',
        ]);

        $product = Product::create([
            'id' => (string) Str::uuid(),
            'category_id' => $category->id,
            'sku' => 'PROD-TEST001',
            'name' => 'Test Product',
            'unit' => 'pcs',
            'buy_price' => 2000.00,
            'sell_price' => 3000.00,
        ]);

        $payload = [
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'date' => '2026-07-04 20:00:00',
            'subtotal' => 20000.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'grand_total' => 20000.00,
            'status' => 'LUNAS',
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 10,
                    'price' => 2000.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->postJson(route('purchases.store'), $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $product->id,
            'branch_id' => $this->branch->id,
            'stock' => 10,
        ]);
    }

    public function test_kredit_purchase_creates_debt_and_sets_status_completed(): void
    {
        $category = Category::create([
            'id' => (string) Str::uuid(),
            'name' => 'Elektronik',
        ]);

        $product = Product::create([
            'id' => (string) Str::uuid(),
            'category_id' => $category->id,
            'sku' => 'PROD-TEST002',
            'name' => 'Test Product 2',
            'unit' => 'pcs',
            'buy_price' => 5000.00,
            'sell_price' => 7000.00,
        ]);

        $payload = [
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'date' => '2026-07-04 20:00:00',
            'subtotal' => 50000.00,
            'discount' => 0.00,
            'tax' => 0.00,
            'grand_total' => 50000.00,
            'status' => 'pending',
            'payment_method' => 'KREDIT',
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 10,
                    'price' => 5000.00,
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->postJson(route('purchases.store'), $payload);

        $response->assertStatus(201);

        $purchase = Purchases::where('grand_total', 50000.00)->first();
        $this->assertNotNull($purchase);
        $this->assertEquals('completed', $purchase->status);

        // Verify stock increased
        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $product->id,
            'branch_id' => $this->branch->id,
            'stock' => 10,
        ]);

        // Verify Debt created
        $this->assertDatabaseHas('debts', [
            'purchase_id' => $purchase->id,
            'debtor_type' => 'branch',
            'debtor_branch_id' => $this->branch->id,
            'creditor_type' => 'supplier',
            'supplier_id' => $this->supplier->id,
            'total_amount' => 50000.00,
            'paid_amount' => 0.00,
            'remaining_amount' => 50000.00,
            'status' => 'unpaid',
        ]);
    }

    public function test_updating_purchase_propagates_to_debt(): void
    {
        $purchase = Purchases::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'PUR-20260704-TEST-KREDIT',
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

        // Create Debt manually
        $debt = Debts::create([
            'id' => (string) Str::uuid(),
            'debtor_type' => 'branch',
            'debtor_branch_id' => $this->branch->id,
            'creditor_type' => 'supplier',
            'supplier_id' => $this->supplier->id,
            'source_type' => 'purchase',
            'purchase_id' => $purchase->id,
            'invoice_number' => $purchase->invoice,
            'total_amount' => 99000.00,
            'paid_amount' => 10000.00,
            'remaining_amount' => 89000.00,
            'status' => 'partial',
        ]);

        $payload = [
            'supplier_id' => $this->supplier->id,
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'date' => '2026-07-04 20:00:00',
            'subtotal' => 120000.00,
            'discount' => 10000.00,
            'tax' => 0.00,
            'grand_total' => 110000.00,
            'status' => 'completed',
        ];

        $response = $this->actingAs($this->user)->putJson(route('purchases.update', $purchase->id), $payload);

        $response->assertStatus(200);

        // Verify debt total and remaining were updated
        $this->assertDatabaseHas('debts', [
            'id' => $debt->id,
            'total_amount' => 110000.00,
            'paid_amount' => 10000.00,
            'remaining_amount' => 100000.00,
            'status' => 'partial',
        ]);
    }

    public function test_deleting_purchase_deletes_associated_debt(): void
    {
        $purchase = Purchases::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'PUR-20260704-TEST-DEL-KREDIT',
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

        // Create Debt manually
        $debt = Debts::create([
            'id' => (string) Str::uuid(),
            'debtor_type' => 'branch',
            'debtor_branch_id' => $this->branch->id,
            'creditor_type' => 'supplier',
            'supplier_id' => $this->supplier->id,
            'source_type' => 'purchase',
            'purchase_id' => $purchase->id,
            'invoice_number' => $purchase->invoice,
            'total_amount' => 99000.00,
            'paid_amount' => 0.00,
            'remaining_amount' => 99000.00,
            'status' => 'unpaid',
        ]);

        // Create Debt Payment manually
        $payment = DebtsPayment::create([
            'id' => (string) Str::uuid(),
            'debt_id' => $debt->id,
            'payment_date' => now()->toDateTimeString(),
            'amount' => 10000.00,
            'method' => 'cash',
        ]);

        $response = $this->actingAs($this->user)->deleteJson(route('purchases.destroy', $purchase->id));

        $response->assertStatus(200);

        // Verify purchase, debt, and debt payment are deleted
        $this->assertDatabaseMissing('purchases', ['id' => $purchase->id]);
        $this->assertDatabaseMissing('debts', ['id' => $debt->id]);
        $this->assertDatabaseMissing('debts_payments', ['id' => $payment->id]);
    }
}
