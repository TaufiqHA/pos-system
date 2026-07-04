<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Role;
use App\Models\Sales;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SalesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['id' => (string) Str::uuid()]
        );

        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Test',
            'address' => 'Jl. Test No. 123',
            'phone' => '081234567890',
            'wilayah_id' => 'Jawa Barat',
            'notes' => 'Catatan Cabang',
        ]);

        $this->user = User::factory()->create([
            'role_id' => $adminRole->id,
            'branch_id' => $this->branch->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_sales(): void
    {
        $response = $this->getJson(route('sales.index'));
        $response->assertStatus(401);
    }

    public function test_can_list_sales(): void
    {
        $sale = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-20260704-0001',
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'date' => '2026-07-04 22:00:00',
            'subtotal' => 100000.00,
            'discount' => 10000.00,
            'tax' => 9000.00,
            'grand_total' => 99000.00,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user)->getJson(route('sales.index'));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $sale->id,
                'invoice' => 'INV-20260704-0001',
            ]);
    }

    public function test_can_store_sale(): void
    {
        $payload = [
            'invoice' => 'INV-20260704-0002',
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'date' => '2026-07-04 22:00:00',
            'subtotal' => 150000.00,
            'discount' => 5000.00,
            'tax' => 14500.00,
            'grand_total' => 159500.00,
            'status' => 'pending',
        ];

        $response = $this->actingAs($this->user)->postJson(route('sales.store'), $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'invoice',
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
            ]);

        $this->assertDatabaseHas('sales', [
            'invoice' => 'INV-20260704-0002',
            'status' => 'pending',
        ]);
    }

    public function test_cannot_store_duplicate_invoice(): void
    {
        Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-DUP-111',
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'date' => '2026-07-04 22:00:00',
            'subtotal' => 100000.00,
            'discount' => 10000.00,
            'tax' => 9000.00,
            'grand_total' => 99000.00,
            'status' => 'completed',
        ]);

        $payload = [
            'invoice' => 'INV-DUP-111',
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'date' => '2026-07-04 22:00:00',
            'subtotal' => 150000.00,
            'discount' => 5000.00,
            'tax' => 14500.00,
            'grand_total' => 159500.00,
            'status' => 'pending',
        ];

        $response = $this->actingAs($this->user)->postJson(route('sales.store'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['invoice']);
    }

    public function test_can_show_sale(): void
    {
        $sale = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-20260704-0003',
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'date' => '2026-07-04 22:00:00',
            'subtotal' => 100000.00,
            'discount' => 10000.00,
            'tax' => 9000.00,
            'grand_total' => 99000.00,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user)->getJson(route('sales.show', $sale->id));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $sale->id,
                'invoice' => 'INV-20260704-0003',
            ]);
    }

    public function test_can_update_sale(): void
    {
        $sale = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-20260704-0004',
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'date' => '2026-07-04 22:00:00',
            'subtotal' => 100000.00,
            'discount' => 10000.00,
            'tax' => 9000.00,
            'grand_total' => 99000.00,
            'status' => 'completed',
        ]);

        $payload = [
            'invoice' => 'INV-20260704-0004',
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'date' => '2026-07-04 23:00:00',
            'subtotal' => 120000.00,
            'discount' => 20000.00,
            'tax' => 10000.00,
            'grand_total' => 110000.00,
            'status' => 'updated_status',
        ];

        $response = $this->actingAs($this->user)->putJson(route('sales.update', $sale->id), $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'updated_status',
                'subtotal' => 120000.00,
            ]);

        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'status' => 'updated_status',
        ]);
    }

    public function test_can_delete_sale(): void
    {
        $sale = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-20260704-0005',
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'date' => '2026-07-04 22:00:00',
            'subtotal' => 100000.00,
            'discount' => 10000.00,
            'tax' => 9000.00,
            'grand_total' => 99000.00,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->user)->deleteJson(route('sales.destroy', $sale->id));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Deleted',
            ]);

        $this->assertDatabaseMissing('sales', [
            'id' => $sale->id,
        ]);
    }
}
