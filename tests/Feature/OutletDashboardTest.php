<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Outlets;
use App\Models\PurchaseOrders;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OutletDashboardTest extends TestCase
{
    use RefreshDatabase;

    private Role $outletRole;

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

        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Test',
            'address' => 'Jl. Cabang Test No. 1',
            'phone' => '08123456789',
        ]);

        $this->outlet = Outlets::create([
            'id' => (string) Str::uuid(),
            'name' => 'Outlet Test',
            'address' => 'Jl. Outlet Test No. 2',
            'phone' => '08223456789',
            'branch_id' => $this->branch->id,
        ]);

        $this->outletUser = User::factory()->create([
            'role_id' => $this->outletRole->id,
            'branch_id' => $this->branch->id,
            'outlet_id' => $this->outlet->id,
            'name' => 'Outlet Staff',
        ]);
    }

    public function test_outlet_dashboard_displays_correct_metrics(): void
    {
        // Create purchase orders
        $po1Notes = [
            'grand_total' => 15000000,
            'items' => [],
        ];
        PurchaseOrders::create([
            'id' => (string) Str::uuid(),
            'po_number' => 'PO-001',
            'branch_id' => $this->branch->id,
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->outletUser->id,
            'status' => 'Pending',
            'notes' => json_encode($po1Notes),
        ]);

        $po2Notes = [
            'grand_total' => 10000000,
            'items' => [],
        ];
        PurchaseOrders::create([
            'id' => (string) Str::uuid(),
            'po_number' => 'PO-002',
            'branch_id' => $this->branch->id,
            'outlet_id' => $this->outlet->id,
            'user_id' => $this->outletUser->id,
            'status' => 'Completed',
            'notes' => json_encode($po2Notes),
        ]);

        // Request the outlet dashboard
        $response = $this->actingAs($this->outletUser)->get(route('outlet.dashboard'));

        $response->assertStatus(200);

        // Assert view variables
        $response->assertViewHas('totalBelanja', 25000000);
        $response->assertViewHas('totalOrder', 2);

        // Assert content is rendered in view
        $response->assertSee('Rp 25.000.000');
        $response->assertSee('2 Order');
        $response->assertSee('Order yang telah dilakukan');
    }
}
