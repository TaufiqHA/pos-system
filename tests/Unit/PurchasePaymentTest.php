<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\PurchasePayment;
use App\Models\Purchases;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PurchasePaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Branch $branch;

    private Purchases $purchase;

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

        $this->purchase = Purchases::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'PUR-20260704-TEST01',
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

    // Test Create
    public function test_can_create_purchase_payment()
    {
        $payload = [
            'purchase_id' => $this->purchase->id,
            'method' => 'Transfer',
            'amount' => 500000,
            'status' => 'Paid',
            'reference' => 'TRX-12345',
            'paid_at' => now()->toDateTimeString(),
        ];

        $response = $this->actingAs($this->user)->postJson('/admin/purchase-payments', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('purchase_payments', [
            'method' => 'Transfer',
            'amount' => 500000,
        ]);
    }

    // Test Show
    public function test_can_show_purchase_payment()
    {
        $payment = PurchasePayment::create([
            'id' => (string) Str::uuid(),
            'purchase_id' => $this->purchase->id,
            'method' => 'Cash',
            'amount' => 10000,
            'status' => 'Paid',
        ]);

        $response = $this->actingAs($this->user)->getJson('/admin/purchase-payments/'.$payment->id);

        $response->assertStatus(200)
            ->assertJsonFragment(['method' => 'Cash']);
    }

    // Test Update
    public function test_can_update_purchase_payment()
    {
        $payment = PurchasePayment::create([
            'id' => (string) Str::uuid(),
            'purchase_id' => $this->purchase->id,
            'method' => 'Cash',
            'amount' => 10000,
            'status' => 'Unpaid',
        ]);

        $response = $this->actingAs($this->user)->putJson('/admin/purchase-payments/'.$payment->id, [
            'status' => 'Paid',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('purchase_payments', [
            'id' => $payment->id,
            'status' => 'Paid',
        ]);
    }

    // Test Delete
    public function test_can_delete_purchase_payment()
    {
        $payment = PurchasePayment::create([
            'id' => (string) Str::uuid(),
            'purchase_id' => $this->purchase->id,
            'method' => 'Cash',
            'amount' => 10000,
            'status' => 'Paid',
        ]);

        $response = $this->actingAs($this->user)->deleteJson('/admin/purchase-payments/'.$payment->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('purchase_payments', [
            'id' => $payment->id,
        ]);
    }
}
