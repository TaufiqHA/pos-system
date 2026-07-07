<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Debts;
use App\Models\DebtsPayment;
use App\Models\Role;
use App\Models\Sales;
use App\Models\SalesPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DebtsPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Branch $branch;

    private Debts $debt;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['id' => (string) Str::uuid()]);
        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Branch A',
            'address' => 'Jl. Branch A',
            'phone' => '021111111',
        ]);
        $this->user = User::factory()->create([
            'role_id' => $adminRole->id,
            'branch_id' => $this->branch->id,
        ]);
        $this->debt = Debts::create([
            'id' => (string) Str::uuid(),
            'debtor_type' => 'branch',
            'debtor_branch_id' => $this->branch->id,
            'creditor_type' => 'branch',
            'creditor_branch_id' => $this->branch->id,
            'total_amount' => 500000,
            'paid_amount' => 100000,
            'remaining_amount' => 400000,
            'status' => 'partial',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_debts_payments(): void
    {
        $response = $this->get(route('debts-payments.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_can_list_debts_payments(): void
    {
        $payment = DebtsPayment::create([
            'id' => (string) Str::uuid(),
            'debt_id' => $this->debt->id,
            'payment_date' => now()->toDateTimeString(),
            'amount' => 50000,
            'method' => 'cash',
            'reference' => 'REF-001',
            'notes' => 'Pembayaran pertama',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->getJson(route('debts-payments.index'));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $payment->id,
                'amount' => '50000.00',
                'method' => 'cash',
            ]);
    }

    public function test_can_store_debts_payment_and_updates_debt(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('debts-payments.store'), [
            'debt_id' => $this->debt->id,
            'payment_date' => now()->toDateTimeString(),
            'amount' => 200000,
            'method' => 'transfer',
            'reference' => 'TRF-100',
            'notes' => 'Pembayaran kedua',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'debt_id',
                    'payment_date',
                    'amount',
                    'method',
                    'reference',
                    'notes',
                    'created_by',
                ],
            ]);

        // Assert payment is saved in the database
        $this->assertDatabaseHas('debts_payments', [
            'debt_id' => $this->debt->id,
            'amount' => 200000,
            'method' => 'transfer',
            'created_by' => $this->user->id,
        ]);

        // Assert debt is updated accordingly:
        // Initial paid was 100k. New payment is 200k. Total paid should be 300k.
        // Total amount is 500k. Remaining should be 200k. Status should be partial.
        $this->assertDatabaseHas('debts', [
            'id' => $this->debt->id,
            'paid_amount' => 300000,
            'remaining_amount' => 200000,
            'status' => 'partial',
        ]);
    }

    public function test_can_store_debts_payment_fully_paying_debt(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('debts-payments.store'), [
            'debt_id' => $this->debt->id,
            'payment_date' => now()->toDateTimeString(),
            'amount' => 400000, // Remaining was 400k. This pays it fully.
            'method' => 'cash',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('debts', [
            'id' => $this->debt->id,
            'paid_amount' => 500000,
            'remaining_amount' => 0,
            'status' => 'paid',
        ]);
    }

    public function test_debts_payment_fully_paid_updates_related_sale_status(): void
    {
        $sale = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-TEST-DEBT-PAY',
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'create_by' => $this->user->id,
            'date' => now()->toDateTimeString(),
            'subtotal' => 500000,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 500000,
            'status' => 'BELUM BAYAR',
        ]);

        $salesPayment = SalesPayment::create([
            'id' => (string) Str::uuid(),
            'sale_id' => $sale->id,
            'method' => 'KREDIT',
            'amount' => 500000,
            'status' => 'BELUM BAYAR',
        ]);

        $debt = Debts::create([
            'id' => (string) Str::uuid(),
            'debtor_type' => 'branch',
            'debtor_branch_id' => $this->branch->id,
            'creditor_type' => 'branch',
            'creditor_branch_id' => $this->branch->id,
            'total_amount' => 500000,
            'paid_amount' => 0,
            'remaining_amount' => 500000,
            'status' => 'unpaid',
            'sale_id' => $sale->id,
        ]);

        $response = $this->actingAs($this->user)->postJson(route('debts-payments.store'), [
            'debt_id' => $debt->id,
            'payment_date' => now()->toDateTimeString(),
            'amount' => 500000,
            'method' => 'cash',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('debts', [
            'id' => $debt->id,
            'status' => 'paid',
            'remaining_amount' => 0,
        ]);

        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'status' => 'LUNAS',
        ]);

        $this->assertDatabaseHas('sales_payments', [
            'id' => $salesPayment->id,
            'status' => 'LUNAS',
        ]);
    }

    public function test_can_show_debts_payment(): void
    {
        $payment = DebtsPayment::create([
            'id' => (string) Str::uuid(),
            'debt_id' => $this->debt->id,
            'payment_date' => now()->toDateTimeString(),
            'amount' => 100000,
            'method' => 'cash',
        ]);

        $response = $this->actingAs($this->user)->getJson(route('debts-payments.show', $payment->id));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $payment->id,
                'amount' => '100000.00',
            ]);
    }

    public function test_can_update_debts_payment_and_adjusts_debt(): void
    {
        // First create a payment manually and update the debt (like store method does)
        $payment = DebtsPayment::create([
            'id' => (string) Str::uuid(),
            'debt_id' => $this->debt->id,
            'payment_date' => now()->toDateTimeString(),
            'amount' => 100000, // Debts paid becomes 100k + 100k = 200k. Remaining 300k.
            'method' => 'cash',
        ]);
        $this->debt->paid_amount += 100000;
        $this->debt->remaining_amount = 300000;
        $this->debt->save();

        // Now update payment amount to 250000 (diff is +150000). Debts paid should become 350k. Remaining 150k.
        $response = $this->actingAs($this->user)->putJson(route('debts-payments.update', $payment->id), [
            'amount' => 250000,
            'method' => 'transfer',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('debts_payments', [
            'id' => $payment->id,
            'amount' => 250000,
            'method' => 'transfer',
        ]);

        $this->assertDatabaseHas('debts', [
            'id' => $this->debt->id,
            'paid_amount' => 350000,
            'remaining_amount' => 150000,
            'status' => 'partial',
        ]);
    }

    public function test_can_delete_debts_payment_and_reverts_debt(): void
    {
        // Create a payment and update the debt
        $payment = DebtsPayment::create([
            'id' => (string) Str::uuid(),
            'debt_id' => $this->debt->id,
            'payment_date' => now()->toDateTimeString(),
            'amount' => 150000, // Debts paid becomes 100k + 150k = 250k.
            'method' => 'cash',
        ]);
        $this->debt->paid_amount += 150000;
        $this->debt->remaining_amount = 250000;
        $this->debt->save();

        // Delete the payment
        $response = $this->actingAs($this->user)->deleteJson(route('debts-payments.destroy', $payment->id));

        $response->assertStatus(200);

        $this->assertDatabaseMissing('debts_payments', [
            'id' => $payment->id,
        ]);

        // Debt paid_amount should revert back to 100000, remaining to 400000
        $this->assertDatabaseHas('debts', [
            'id' => $this->debt->id,
            'paid_amount' => 100000,
            'remaining_amount' => 400000,
            'status' => 'partial',
        ]);
    }

    public function test_can_delete_debts_payment_via_delete_route(): void
    {
        $payment = DebtsPayment::create([
            'id' => (string) Str::uuid(),
            'debt_id' => $this->debt->id,
            'payment_date' => now()->toDateTimeString(),
            'amount' => 100000,
            'method' => 'cash',
        ]);

        $response = $this->actingAs($this->user)->deleteJson(route('debts-payments.delete', $payment->id));

        $response->assertStatus(200);

        $this->assertDatabaseMissing('debts_payments', [
            'id' => $payment->id,
        ]);
    }
}
