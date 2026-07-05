<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Deliveries;
use App\Models\Role;
use App\Models\Sales;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DeliveriesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Branch $branch;

    private Sales $sale;

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

        $this->sale = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-TEST-001',
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'date' => '2026-07-05 10:00:00',
            'subtotal' => 100000.00,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 100000.00,
            'status' => 'completed',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_deliveries(): void
    {
        $response = $this->getJson(route('deliveries.index'));
        $response->assertStatus(401);
    }

    public function test_can_list_deliveries(): void
    {
        $delivery = Deliveries::create([
            'id' => (string) Str::uuid(),
            'sale_id' => $this->sale->id,
            'driver_name' => 'Budi',
            'status' => 'PENDING',
            'sent_at' => null,
            'received_at' => null,
        ]);

        $response = $this->actingAs($this->user)->getJson(route('deliveries.index'));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $delivery->id,
                'driver_name' => 'Budi',
            ]);
    }

    public function test_can_store_delivery(): void
    {
        $payload = [
            'sale_id' => $this->sale->id,
            'status' => 'DIKIRIM',
            'sent_at' => '2026-07-05 12:00:00',
            'received_at' => null,
        ];

        $response = $this->actingAs($this->user)->postJson(route('deliveries.store'), $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'sale_id',
                'driver_name',
                'status',
                'sent_at',
                'received_at',
                'created_at',
                'updated_at',
            ]);

        $this->assertDatabaseHas('deliveries', [
            'driver_name' => 'Belum Ditentukan',
            'status' => 'DIKIRIM',
        ]);
    }

    public function test_can_store_delivery_without_sale(): void
    {
        $payload = [
            'status' => 'PENDING',
        ];

        $response = $this->actingAs($this->user)->postJson(route('deliveries.store'), $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('deliveries', [
            'driver_name' => 'Belum Ditentukan',
            'sale_id' => null,
        ]);
    }

    public function test_validation_errors_on_store(): void
    {
        $payload = [
            'sale_id' => $this->sale->id,
        ];

        $response = $this->actingAs($this->user)->postJson(route('deliveries.store'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_can_show_delivery(): void
    {
        $delivery = Deliveries::create([
            'id' => (string) Str::uuid(),
            'sale_id' => $this->sale->id,
            'driver_name' => 'Dedi',
            'status' => 'DIKIRIM',
            'sent_at' => '2026-07-05 12:00:00',
            'received_at' => null,
        ]);

        $response = $this->actingAs($this->user)->getJson(route('deliveries.show', $delivery->id));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $delivery->id,
                'driver_name' => 'Dedi',
            ]);
    }

    public function test_can_update_delivery(): void
    {
        $delivery = Deliveries::create([
            'id' => (string) Str::uuid(),
            'sale_id' => $this->sale->id,
            'driver_name' => 'Eko',
            'status' => 'DIKIRIM',
            'sent_at' => '2026-07-05 12:00:00',
            'received_at' => null,
        ]);

        $payload = [
            'sale_id' => $this->sale->id,
            'status' => 'DITERIMA',
            'sent_at' => '2026-07-05 12:00:00',
            'received_at' => '2026-07-05 15:00:00',
        ];

        $response = $this->actingAs($this->user)->putJson(route('deliveries.update', $delivery->id), $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'driver_name' => 'Belum Ditentukan',
                'status' => 'DITERIMA',
            ]);

        $this->assertDatabaseHas('deliveries', [
            'id' => $delivery->id,
            'driver_name' => 'Belum Ditentukan',
            'status' => 'DITERIMA',
        ]);
    }

    public function test_update_delivery_ignores_sale_id_change(): void
    {
        $delivery = Deliveries::create([
            'id' => (string) Str::uuid(),
            'sale_id' => $this->sale->id,
            'driver_name' => 'Eko',
            'status' => 'PENDING',
            'sent_at' => null,
            'received_at' => null,
        ]);

        $anotherSale = Sales::create([
            'id' => (string) Str::uuid(),
            'invoice' => 'INV-TEST-002',
            'branch_id' => $this->branch->id,
            'user_id' => $this->user->id,
            'date' => '2026-07-05 10:00:00',
            'subtotal' => 50000.00,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 50000.00,
            'status' => 'completed',
        ]);

        $payload = [
            'sale_id' => $anotherSale->id, // Attempt to change sale_id
            'status' => 'PENDING',
        ];

        $response = $this->actingAs($this->user)->putJson(route('deliveries.update', $delivery->id), $payload);

        $response->assertStatus(200);

        // Verify sale_id remains unchanged
        $this->assertDatabaseHas('deliveries', [
            'id' => $delivery->id,
            'sale_id' => $this->sale->id,
        ]);
    }

    public function test_update_delivery_status_to_dikirim_sets_sent_at(): void
    {
        $delivery = Deliveries::create([
            'id' => (string) Str::uuid(),
            'sale_id' => $this->sale->id,
            'driver_name' => 'Eko',
            'status' => 'PENDING',
            'sent_at' => null,
            'received_at' => null,
        ]);

        $payload = [
            'status' => 'DIKIRIM',
        ];

        $response = $this->actingAs($this->user)->putJson(route('deliveries.update', $delivery->id), $payload);

        $response->assertStatus(200);

        $freshDelivery = $delivery->fresh();
        $this->assertEquals('DIKIRIM', $freshDelivery->status);
        $this->assertNotNull($freshDelivery->sent_at);
    }

    public function test_can_delete_delivery(): void
    {
        $delivery = Deliveries::create([
            'id' => (string) Str::uuid(),
            'sale_id' => $this->sale->id,
            'driver_name' => 'Farid',
            'status' => 'PENDING',
            'sent_at' => null,
            'received_at' => null,
        ]);

        $response = $this->actingAs($this->user)->deleteJson(route('deliveries.destroy', $delivery->id));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Deleted',
            ]);

        $this->assertDatabaseMissing('deliveries', [
            'id' => $delivery->id,
        ]);
    }
}
