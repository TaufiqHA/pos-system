<?php

namespace Tests\Feature;

use App\Models\Suppliers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SuppliersTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_unauthenticated_user_cannot_access_suppliers(): void
    {
        $response = $this->get(route('suppliers.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_can_list_suppliers(): void
    {
        $supplier = Suppliers::create([
            'id' => (string) Str::uuid(),
            'name' => 'Supplier A',
            'contact_name' => 'John Doe',
            'phone' => '08123456789',
            'email' => 'supplier@example.com',
            'address' => 'Jl. Merdeka No. 1',
            'notes' => 'Catatan supplier A',
        ]);

        $response = $this->actingAs($this->user)->getJson(route('suppliers.index'));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $supplier->id,
                'name' => 'Supplier A',
            ]);
    }

    public function test_can_store_supplier(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('suppliers.store'), [
            'name' => 'Supplier Baru',
            'contact_name' => 'Jane Doe',
            'phone' => '08987654321',
            'email' => 'newsupplier@example.com',
            'address' => 'Jl. Pahlawan No. 2',
            'notes' => 'Catatan supplier baru',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'contact_name',
                    'phone',
                    'email',
                    'address',
                    'notes',
                    'created_at',
                    'updated_at',
                ]
            ]);

        $this->assertDatabaseHas('suppliers', [
            'name' => 'Supplier Baru',
            'contact_name' => 'Jane Doe',
        ]);
    }

    public function test_can_show_supplier(): void
    {
        $supplier = Suppliers::create([
            'id' => (string) Str::uuid(),
            'name' => 'Supplier B',
            'contact_name' => 'Alice',
            'phone' => '08222333444',
            'email' => 'supplierB@example.com',
            'address' => 'Jl. Sudirman No. 5',
            'notes' => 'Catatan supplier B',
        ]);

        $response = $this->actingAs($this->user)->getJson(route('suppliers.show', $supplier->id));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $supplier->id,
                'name' => 'Supplier B',
            ]);
    }

    public function test_can_update_supplier(): void
    {
        $supplier = Suppliers::create([
            'id' => (string) Str::uuid(),
            'name' => 'Supplier Asli',
            'contact_name' => 'Bob',
            'phone' => '08333444555',
            'email' => 'supplierAsli@example.com',
            'address' => 'Jl. Gatot Subroto No. 10',
            'notes' => 'Catatan supplier Asli',
        ]);

        $response = $this->actingAs($this->user)->putJson(route('suppliers.update', $supplier->id), [
            'name' => 'Supplier Diupdate',
            'contact_name' => 'Bob Updated',
            'phone' => '08333444555',
            'email' => 'supplierUpdated@example.com',
            'address' => 'Jl. Gatot Subroto No. 10 Updated',
            'notes' => 'Catatan supplier Asli Updated',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Supplier Diupdate',
                'contact_name' => 'Bob Updated',
            ]);

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Supplier Diupdate',
            'contact_name' => 'Bob Updated',
        ]);
    }

    public function test_can_delete_supplier(): void
    {
        $supplier = Suppliers::create([
            'id' => (string) Str::uuid(),
            'name' => 'Supplier Hapus',
            'contact_name' => 'Charlie',
            'phone' => '08444555666',
            'email' => 'supplierHapus@example.com',
            'address' => 'Jl. Thamrin No. 8',
            'notes' => 'Catatan hapus',
        ]);

        $response = $this->actingAs($this->user)->deleteJson(route('suppliers.destroy', $supplier->id));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Supplier berhasil dihapus'
            ]);

        $this->assertDatabaseMissing('suppliers', [
            'id' => $supplier->id,
        ]);
    }
}
