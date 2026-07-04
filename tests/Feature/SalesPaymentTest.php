<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Role;
use App\Models\Sales;
use App\Models\SalesPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SalesPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Branch $branch;

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

    public function test_unauthenticated_user_cannot_access_sales_payments(): void
    {
        $response = $this->postJson('/admin/sales-payments', []);
        $response->assertStatus(401);

        $response = $this->getJson('/admin/sales-payments/some-id');
        $response->assertStatus(401);

        $response = $this->putJson('/admin/sales-payments/some-id', []);
        $response->assertStatus(401);

        $response = $this->deleteJson('/admin/sales-payments/some-id');
        $response->assertStatus(401);
    }

    public function test_can_create_sales_payment(): void
    {
        $paymentId = (string) Str::uuid();
        $payload = [
            'id' => $paymentId,
            'sale_id' => $this->sales->id,
            'method' => 'cash',
            'amount' => 5000000.00,
            'status' => 'success',
            'reference' => 'REF-001',
            'paid_at' => '2026-07-04 23:00:00',
        ];

        $response = $this->actingAs($this->user)->postJson('/admin/sales-payments', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'id' => $paymentId,
                'method' => 'cash',
                'amount' => '5000000.00',
                'status' => 'success',
            ]);

        $this->assertDatabaseHas('sales_payments', [
            'id' => $paymentId,
            'method' => 'cash',
        ]);
    }

    public function test_cannot_create_sales_payment_with_invalid_data(): void
    {
        $payload = [
            'id' => '',
            'sale_id' => 'invalid-sale',
            'method' => '',
            'amount' => -100,
            'status' => '',
        ];

        $response = $this->actingAs($this->user)->postJson('/admin/sales-payments', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['id', 'sale_id', 'method', 'amount', 'status']);
    }

    public function test_can_show_sales_payment(): void
    {
        $paymentId = (string) Str::uuid();
        SalesPayment::create([
            'id' => $paymentId,
            'sale_id' => $this->sales->id,
            'method' => 'transfer',
            'amount' => 6000000.00,
            'status' => 'success',
            'reference' => 'REF-TRANSFER-001',
            'paid_at' => '2026-07-04 23:00:00',
        ]);

        $response = $this->actingAs($this->user)->getJson("/admin/sales-payments/{$paymentId}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $paymentId,
                'method' => 'transfer',
            ]);
    }

    public function test_can_update_sales_payment(): void
    {
        $paymentId = (string) Str::uuid();
        SalesPayment::create([
            'id' => $paymentId,
            'sale_id' => $this->sales->id,
            'method' => 'cash',
            'amount' => 5000000.00,
            'status' => 'pending',
            'reference' => 'REF-001',
            'paid_at' => '2026-07-04 23:00:00',
        ]);

        $payload = [
            'status' => 'success',
            'amount' => 5500000.00,
        ];

        $response = $this->actingAs($this->user)->putJson("/admin/sales-payments/{$paymentId}", $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $paymentId,
                'status' => 'success',
                'amount' => '5500000.00',
            ]);

        $this->assertDatabaseHas('sales_payments', [
            'id' => $paymentId,
            'status' => 'success',
            'amount' => '5500000.00',
        ]);
    }

    public function test_can_delete_sales_payment(): void
    {
        $paymentId = (string) Str::uuid();
        SalesPayment::create([
            'id' => $paymentId,
            'sale_id' => $this->sales->id,
            'method' => 'cash',
            'amount' => 5000000.00,
            'status' => 'success',
            'reference' => 'REF-001',
            'paid_at' => '2026-07-04 23:00:00',
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/admin/sales-payments/{$paymentId}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Sales payment deleted successfully',
            ]);

        $this->assertDatabaseMissing('sales_payments', [
            'id' => $paymentId,
        ]);
    }
}
