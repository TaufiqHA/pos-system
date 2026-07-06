<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Debts;
use App\Models\Outlets;
use App\Models\Role;
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
}
