<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Debts;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminHutangTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private Branch $pusatBranch;

    private Branch $otherBranch;

    private Branch $debtorBranch;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['id' => (string) Str::uuid()]);

        // Gudang Pusat (Pusat)
        $this->pusatBranch = Branch::firstOrCreate([
            'id' => 'BRC-001',
        ], [
            'name' => 'Gudang Pusat',
            'address' => 'Jl. Pusat No. 1',
            'phone' => '08111111111',
        ]);

        // Other supplier/creditor branch
        $this->otherBranch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Branch B',
            'address' => 'Jl. Branch B',
            'phone' => '0822222222',
        ]);

        // Debtor Branch (the branch that has the debt)
        $this->debtorBranch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Debtor Branch',
            'address' => 'Jl. Debtor',
            'phone' => '0833333333',
        ]);

        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
            'branch_id' => $this->pusatBranch->id,
            'name' => 'Admin User',
        ]);
    }

    public function test_admin_only_sees_branch_debts_where_creditor_is_their_branch(): void
    {
        // 1. Debt to Gudang Pusat (logged in admin's branch) -> SHOULD be visible
        $debtPusat = Debts::create([
            'id' => (string) Str::uuid(),
            'debtor_type' => 'branch',
            'debtor_branch_id' => $this->debtorBranch->id,
            'creditor_type' => 'branch',
            'creditor_branch_id' => $this->pusatBranch->id,
            'total_amount' => 5000000,
            'paid_amount' => 0,
            'remaining_amount' => 5000000,
            'status' => 'unpaid',
            'invoice_number' => 'INV-PUSAT-001',
        ]);

        // 2. Debt to another branch -> SHOULD NOT be visible
        $debtOther = Debts::create([
            'id' => (string) Str::uuid(),
            'debtor_type' => 'branch',
            'debtor_branch_id' => $this->debtorBranch->id,
            'creditor_type' => 'branch',
            'creditor_branch_id' => $this->otherBranch->id,
            'total_amount' => 2000000,
            'paid_amount' => 0,
            'remaining_amount' => 2000000,
            'status' => 'unpaid',
            'invoice_number' => 'INV-OTHER-002',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.hutang'));

        $response->assertStatus(200);

        // Verify the centers see the debt to the center
        $response->assertSee('INV-PUSAT-001');

        // Verify the centers do not see the debt to the other branch
        $response->assertDontSee('INV-OTHER-002');
    }
}
