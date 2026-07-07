<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Debts;
use App\Models\Outlets;
use App\Models\PurchaseOrders;
use App\Models\Role;
use App\Models\Sales;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OutletHistoryTest extends TestCase
{
    use RefreshDatabase;

    private Role $outletRole;

    private Role $cabangRole;

    private Branch $branch;

    private Outlets $outlet;

    private User $outletUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->outletRole = Role::create([
            'id' => (string) Str::uuid(),
            'name' => 'outlet',
            'description' => 'Staff Outlet',
        ]);

        $this->cabangRole = Role::create([
            'id' => (string) Str::uuid(),
            'name' => 'cabang',
            'description' => 'Pengelola Cabang',
        ]);

        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Test',
            'address' => 'Jl. Cabang Test No. 1',
            'phone' => '08123456789',
        ]);

        $this->outlet = Outlets::create([
            'id' => (string) Str::uuid(),
            'branch_id' => $this->branch->id,
            'name' => 'Outlet Test',
            'address' => 'Jl. Outlet Test No. 2',
            'phone' => '08223456789',
        ]);

        $this->outletUser = User::factory()->create([
            'role_id' => $this->outletRole->id,
            'branch_id' => $this->branch->id,
            'outlet_id' => $this->outlet->id,
            'name' => 'Outlet Staff',
        ]);
    }

    public function test_guest_cannot_access_outlet_history(): void
    {
        $response = $this->get(route('outlet.history'));
        $response->assertRedirect(route('login'));
    }

    public function test_cabang_cannot_access_outlet_history(): void
    {
        $cabangUser = User::factory()->create([
            'role_id' => $this->cabangRole->id,
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->actingAs($cabangUser)->get(route('outlet.history'));
        $response->assertStatus(403);
    }

    public function test_outlet_staff_can_access_outlet_history_and_view_orders(): void
    {
        // 1. Create a pending purchase order
        $pendingPoNotes = [
            'user_notes' => 'Catatan pending',
            'subtotal' => 100000,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 100000,
            'payment_method' => 'TUNAI',
            'items' => [],
        ];

        $pendingPo = PurchaseOrders::create([
            'id' => (string) Str::uuid(),
            'po_number' => 'PO-PENDING-001',
            'branch_id' => $this->branch->id,
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->outletUser->id,
            'status' => 'Pending',
            'notes' => json_encode($pendingPoNotes),
        ]);

        // 2. Create an approved purchase order with associated Sale (Lunas)
        $approvedPoNotes = [
            'user_notes' => 'Catatan approved',
            'subtotal' => 250000,
            'discount' => 10000,
            'tax' => 0,
            'grand_total' => 240000,
            'payment_method' => 'TRANSFER',
            'items' => [],
        ];

        $sale = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-APPROVED-001',
            'branch_id' => $this->branch->id,
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->outletUser->id,
            'create_by' => $this->outletUser->id,
            'date' => now(),
            'subtotal' => 250000,
            'discount' => 10000,
            'tax' => 0,
            'grand_total' => 240000,
            'status' => 'LUNAS',
        ]);

        $approvedPo = PurchaseOrders::create([
            'id' => (string) Str::uuid(),
            'po_number' => 'PO-APPROVED-002',
            'branch_id' => $this->branch->id,
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->outletUser->id,
            'status' => 'Approved',
            'notes' => json_encode($approvedPoNotes),
            'sale_id' => $sale->id,
        ]);

        $response = $this->actingAs($this->outletUser)->get(route('outlet.history'));
        $response->assertStatus(200);

        // Verify PO Numbers are present
        $response->assertSee('PO-PENDING-001');
        $response->assertSee('PO-APPROVED-002');
        $response->assertSee('INV-APPROVED-001');

        // Verify payment statuses and methods
        $response->assertSee('TUNAI');
        $response->assertSee('TRANSFER');
        $response->assertSee('LUNAS');
    }

    public function test_outlet_staff_history_displays_lunas_when_debt_is_paid(): void
    {
        $poNotes = [
            'user_notes' => 'Kredit PO',
            'subtotal' => 200000,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 200000,
            'payment_method' => 'KREDIT',
            'items' => [],
        ];

        $sale = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-DEBT-001',
            'branch_id' => $this->branch->id,
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->outletUser->id,
            'create_by' => $this->outletUser->id,
            'date' => now(),
            'subtotal' => 200000,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 200000,
            'status' => 'BELUM BAYAR',
        ]);

        $po = PurchaseOrders::create([
            'id' => (string) Str::uuid(),
            'po_number' => 'PO-DEBT-001',
            'branch_id' => $this->branch->id,
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->outletUser->id,
            'status' => 'Approved',
            'notes' => json_encode($poNotes),
            'sale_id' => $sale->id,
        ]);

        $debt = Debts::create([
            'id' => (string) Str::uuid(),
            'debtor_type' => 'outlet',
            'debtor_outlet_id' => $this->outlet->id,
            'creditor_type' => 'branch',
            'creditor_branch_id' => $this->branch->id,
            'source_type' => 'sale',
            'sale_id' => $sale->id,
            'invoice_number' => $sale->invoice,
            'total_amount' => 200000,
            'paid_amount' => 0,
            'remaining_amount' => 200000,
            'status' => 'unpaid',
            'due_date' => now()->addDays(30),
        ]);

        $response = $this->actingAs($this->outletUser)->get(route('outlet.history'));
        $response->assertStatus(200);
        $response->assertSee('BELUM BAYAR');

        $debt->update([
            'paid_amount' => 200000,
            'remaining_amount' => 0,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($this->outletUser)->get(route('outlet.history'));
        $response->assertStatus(200);
        $response->assertSee('LUNAS');
    }
}
