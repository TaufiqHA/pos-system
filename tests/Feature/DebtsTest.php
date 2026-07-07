<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Debts;
use App\Models\Outlets;
use App\Models\Role;
use App\Models\Sales;
use App\Models\SalesPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DebtsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Branch $branch;

    private Outlets $outlet;

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
        $this->outlet = Outlets::create([
            'id' => (string) Str::uuid(),
            'branch_id' => $this->branch->id,
            'name' => 'Outlet A',
            'address' => 'Jl. Outlet A',
            'phone' => '021222222',
        ]);
        $this->user = User::factory()->create([
            'role_id' => $adminRole->id,
            'branch_id' => $this->branch->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_debts(): void
    {
        $response = $this->get(route('debts.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_can_list_debts(): void
    {
        $debt = Debts::create([
            'id' => (string) Str::uuid(),
            'debtor_type' => 'branch',
            'debtor_branch_id' => $this->branch->id,
            'creditor_type' => 'branch',
            'creditor_branch_id' => $this->branch->id,
            'total_amount' => 100000,
            'paid_amount' => 20000,
            'remaining_amount' => 80000,
            'status' => 'partial',
        ]);

        $response = $this->actingAs($this->user)->getJson(route('debts.index'));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $debt->id,
                'debtor_type' => 'branch',
                'total_amount' => 100000,
            ]);
    }

    public function test_can_store_debt(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('debts.store'), [
            'debtor_type' => 'outlet',
            'debtor_outlet_id' => $this->outlet->id,
            'creditor_type' => 'branch',
            'creditor_branch_id' => $this->branch->id,
            'total_amount' => 500000,
            'paid_amount' => 100000,
            'due_date' => now()->addDays(7)->toDateTimeString(),
            'notes' => 'Hutang pembelian inventaris',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'debtor_type',
                    'debtor_outlet_id',
                    'creditor_type',
                    'creditor_branch_id',
                    'total_amount',
                    'paid_amount',
                    'remaining_amount',
                    'due_date',
                    'status',
                    'notes',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('debts', [
            'debtor_type' => 'outlet',
            'debtor_outlet_id' => $this->outlet->id,
            'remaining_amount' => 400000,
            'status' => 'partial',
        ]);
    }

    public function test_can_show_debt(): void
    {
        $debt = Debts::create([
            'id' => (string) Str::uuid(),
            'debtor_type' => 'branch',
            'debtor_branch_id' => $this->branch->id,
            'creditor_type' => 'branch',
            'creditor_branch_id' => $this->branch->id,
            'total_amount' => 150000,
            'paid_amount' => 150000,
            'remaining_amount' => 0,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($this->user)->getJson(route('debts.show', $debt->id));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $debt->id,
                'status' => 'paid',
            ]);
    }

    public function test_can_update_debt(): void
    {
        $debt = Debts::create([
            'id' => (string) Str::uuid(),
            'debtor_type' => 'branch',
            'debtor_branch_id' => $this->branch->id,
            'creditor_type' => 'branch',
            'creditor_branch_id' => $this->branch->id,
            'total_amount' => 200000,
            'paid_amount' => 50000,
            'remaining_amount' => 150000,
            'status' => 'partial',
        ]);

        $response = $this->actingAs($this->user)->putJson(route('debts.update', $debt->id), [
            'paid_amount' => 200000,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('debts', [
            'id' => $debt->id,
            'paid_amount' => 200000,
            'remaining_amount' => 0,
            'status' => 'paid',
        ]);
    }

    public function test_update_debt_fully_paid_updates_related_sale_status(): void
    {
        $sale = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-TEST-DEBT-UPDATE',
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'create_by' => $this->user->id,
            'date' => now()->toDateTimeString(),
            'subtotal' => 200000,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 200000,
            'status' => 'BELUM BAYAR',
        ]);

        $salesPayment = SalesPayment::create([
            'id' => (string) Str::uuid(),
            'sale_id' => $sale->id,
            'method' => 'KREDIT',
            'amount' => 200000,
            'status' => 'BELUM BAYAR',
        ]);

        $debt = Debts::create([
            'id' => (string) Str::uuid(),
            'debtor_type' => 'branch',
            'debtor_branch_id' => $this->branch->id,
            'creditor_type' => 'branch',
            'creditor_branch_id' => $this->branch->id,
            'total_amount' => 200000,
            'paid_amount' => 0,
            'remaining_amount' => 200000,
            'status' => 'unpaid',
            'sale_id' => $sale->id,
        ]);

        $response = $this->actingAs($this->user)->putJson(route('debts.update', $debt->id), [
            'paid_amount' => 200000,
        ]);

        $response->assertStatus(200);

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

    public function test_can_delete_debt_via_destroy(): void
    {
        $debt = Debts::create([
            'id' => (string) Str::uuid(),
            'debtor_type' => 'branch',
            'debtor_branch_id' => $this->branch->id,
            'creditor_type' => 'branch',
            'creditor_branch_id' => $this->branch->id,
            'total_amount' => 100000,
            'paid_amount' => 0,
            'remaining_amount' => 100000,
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($this->user)->deleteJson(route('debts.destroy', $debt->id));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('debts', [
            'id' => $debt->id,
        ]);
    }

    public function test_can_delete_debt_via_delete_route(): void
    {
        $debt = Debts::create([
            'id' => (string) Str::uuid(),
            'debtor_type' => 'branch',
            'debtor_branch_id' => $this->branch->id,
            'creditor_type' => 'branch',
            'creditor_branch_id' => $this->branch->id,
            'total_amount' => 100000,
            'paid_amount' => 0,
            'remaining_amount' => 100000,
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($this->user)->deleteJson(route('debts.delete', $debt->id));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('debts', [
            'id' => $debt->id,
        ]);
    }

    public function test_cabang_user_can_view_their_debts(): void
    {
        $cabangRole = Role::firstOrCreate(['name' => 'cabang'], ['id' => (string) Str::uuid()]);
        $cabangUser = User::factory()->create([
            'role_id' => $cabangRole->id,
            'branch_id' => $this->branch->id,
        ]);

        Branch::create([
            'id' => 'BRC-001',
            'name' => 'Gudang Pusat',
            'address' => 'Jl. Pusat No. 1',
            'phone' => '081111111',
        ]);

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

        $otherBranch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Branch B',
            'address' => 'Jl. Branch B',
            'phone' => '021333333',
        ]);
        $otherDebt = Debts::create([
            'id' => (string) Str::uuid(),
            'debtor_type' => 'branch',
            'debtor_branch_id' => $otherBranch->id,
            'creditor_type' => 'branch',
            'creditor_branch_id' => 'BRC-001',
            'total_amount' => 7000000,
            'paid_amount' => 0,
            'remaining_amount' => 7000000,
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($cabangUser)->get(route('cabang.hutang'));

        $response->assertStatus(200);
        $response->assertSee('Rp 5.000.000');
        $response->assertDontSee('Rp 7.000.000');
    }

    public function test_cabang_user_can_view_outlet_debts(): void
    {
        $cabangRole = Role::firstOrCreate(['name' => 'cabang'], ['id' => (string) Str::uuid()]);
        $cabangUser = User::factory()->create([
            'role_id' => $cabangRole->id,
            'branch_id' => $this->branch->id,
        ]);

        Branch::create([
            'id' => 'BRC-001',
            'name' => 'Gudang Pusat',
            'address' => 'Jl. Pusat No. 1',
            'phone' => '081111111',
        ]);

        // Branch's debt to Center
        Debts::create([
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

        // Outlet's debt to Branch
        $outletDebt = Debts::create([
            'id' => (string) Str::uuid(),
            'debtor_type' => 'outlet',
            'debtor_outlet_id' => $this->outlet->id,
            'creditor_type' => 'branch',
            'creditor_branch_id' => $this->branch->id,
            'total_amount' => 2500000,
            'paid_amount' => 0,
            'remaining_amount' => 2500000,
            'status' => 'unpaid',
        ]);

        // JSON response
        $responseJson = $this->actingAs($cabangUser)->getJson(route('cabang.hutang'));
        $responseJson->assertStatus(200)
            ->assertJsonFragment([
                'id' => $outletDebt->id,
                'debtor_type' => 'outlet',
                'total_amount' => 2500000,
            ]);

        // HTML response
        $responseHtml = $this->actingAs($cabangUser)->get(route('cabang.hutang'));
        $responseHtml->assertStatus(200);
        $responseHtml->assertSee('Rp 2.500.000');
    }
}
