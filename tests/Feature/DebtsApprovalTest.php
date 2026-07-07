<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Debts;
use App\Models\DebtsPayment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DebtsApprovalTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $cabangUser;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['id' => (string) Str::uuid()]);
        $cabangRole = Role::firstOrCreate(['name' => 'cabang'], ['id' => (string) Str::uuid()]);

        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Branch Makassar',
            'address' => 'Jl. Makassar',
            'phone' => '0821111111',
        ]);

        Branch::firstOrCreate([
            'id' => 'BRC-001',
        ], [
            'name' => 'Gudang Pusat',
            'address' => 'Jl. Pusat',
            'phone' => '02199999',
        ]);

        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
            'branch_id' => 'BRC-001',
        ]);

        $this->cabangUser = User::factory()->create([
            'role_id' => $cabangRole->id,
            'branch_id' => $this->branch->id,
        ]);
    }

    public function test_cabang_payment_starts_as_pending_and_does_not_affect_remaining_debt(): void
    {
        $debt = Debts::create([
            'id' => (string) Str::uuid(),
            'debtor_type' => 'branch',
            'debtor_branch_id' => $this->branch->id,
            'creditor_type' => 'branch',
            'creditor_branch_id' => 'BRC-001',
            'total_amount' => 5000000,
            'paid_amount' => 0,
            'remaining_amount' => 5000000,
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($this->cabangUser)->postJson(route('debts-payments.store'), [
            'debt_id' => $debt->id,
            'payment_date' => now()->toDateTimeString(),
            'amount' => 2000000,
            'method' => 'TRANSFER',
            'reference' => 'REF-001',
            'notes' => 'Bayar cicilan ke-1',
        ]);

        $response->assertStatus(201);

        $payment = DebtsPayment::first();
        $this->assertNotNull($payment);
        $this->assertEquals('PENDING', $payment->status);

        $debt->refresh();
        $this->assertEquals(0, $debt->paid_amount);
        $this->assertEquals(5000000, $debt->remaining_amount);
        $this->assertEquals('unpaid', $debt->status);
    }

    public function test_admin_can_confirm_pending_payment(): void
    {
        $debt = Debts::create([
            'id' => (string) Str::uuid(),
            'debtor_type' => 'branch',
            'debtor_branch_id' => $this->branch->id,
            'creditor_type' => 'branch',
            'creditor_branch_id' => 'BRC-001',
            'total_amount' => 5000000,
            'paid_amount' => 0,
            'remaining_amount' => 5000000,
            'status' => 'unpaid',
        ]);

        $payment = DebtsPayment::create([
            'id' => (string) Str::uuid(),
            'debt_id' => $debt->id,
            'payment_date' => now(),
            'amount' => 2000000,
            'method' => 'TRANSFER',
            'reference' => 'REF-001',
            'notes' => 'Bayar cicilan ke-1',
            'status' => 'PENDING',
            'created_by' => $this->cabangUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)->postJson(route('debts-payments.confirm', $payment->id));

        $response->assertStatus(200);

        $payment->refresh();
        $this->assertEquals('CONFIRMED', $payment->status);

        $debt->refresh();
        $this->assertEquals(2000000, $debt->paid_amount);
        $this->assertEquals(3000000, $debt->remaining_amount);
        $this->assertEquals('partial', $debt->status);
    }

    public function test_admin_can_reject_pending_payment(): void
    {
        $debt = Debts::create([
            'id' => (string) Str::uuid(),
            'debtor_type' => 'branch',
            'debtor_branch_id' => $this->branch->id,
            'creditor_type' => 'branch',
            'creditor_branch_id' => 'BRC-001',
            'total_amount' => 5000000,
            'paid_amount' => 0,
            'remaining_amount' => 5000000,
            'status' => 'unpaid',
        ]);

        $payment = DebtsPayment::create([
            'id' => (string) Str::uuid(),
            'debt_id' => $debt->id,
            'payment_date' => now(),
            'amount' => 2000000,
            'method' => 'TRANSFER',
            'reference' => 'REF-001',
            'notes' => 'Bayar cicilan ke-1',
            'status' => 'PENDING',
            'created_by' => $this->cabangUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)->postJson(route('debts-payments.reject', $payment->id));

        $response->assertStatus(200);

        $payment->refresh();
        $this->assertEquals('REJECTED', $payment->status);

        $debt->refresh();
        $this->assertEquals(0, $debt->paid_amount);
        $this->assertEquals(5000000, $debt->remaining_amount);
        $this->assertEquals('unpaid', $debt->status);
    }
}
