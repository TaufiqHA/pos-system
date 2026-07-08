<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Units;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UnitsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['id' => (string) Str::uuid()]);
        $this->user = User::factory()->create(['role_id' => $adminRole->id]);
    }

    public function test_unauthenticated_user_cannot_access_units(): void
    {
        $response = $this->get(route('units.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_can_list_units(): void
    {
        $unit = Units::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Pcs',
        ]);

        $response = $this->actingAs($this->user)->get(route('units.index'));

        $response->assertStatus(200);
        $response->assertViewHas('units');
        $response->assertSee('Pcs');
    }

    public function test_can_list_units_json(): void
    {
        $unit = Units::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Box',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('units.index'));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $unit->id,
            'name' => 'Box',
        ]);
    }

    public function test_create_unit_form_redirects_to_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('units.create'));

        $response->assertStatus(302);
        $response->assertRedirect(route('units.index'));
    }

    public function test_can_store_unit(): void
    {
        $response = $this->actingAs($this->user)->post(route('units.store'), [
            'name' => 'Kilogram',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('units.index'));
        $response->assertSessionHas('success', 'Unit created successfully.');

        $this->assertDatabaseHas('units', [
            'name' => 'Kilogram',
        ]);
    }

    public function test_can_store_unit_json(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('units.store'), [
                'name' => 'Liter',
            ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'message' => 'Unit berhasil dibuat',
        ]);

        $this->assertDatabaseHas('units', [
            'name' => 'Liter',
        ]);
    }

    public function test_cannot_store_duplicate_unit_name(): void
    {
        Units::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Meter',
        ]);

        $response = $this->actingAs($this->user)->post(route('units.store'), [
            'name' => 'Meter',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_can_show_unit_json(): void
    {
        $unit = Units::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Gram',
        ]);

        $response = $this->actingAs($this->user)->get(route('units.show', $unit->id));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $unit->id,
            'name' => 'Gram',
        ]);
    }

    public function test_edit_unit_form_redirects_to_index(): void
    {
        $unit = Units::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Milliliter',
        ]);

        $response = $this->actingAs($this->user)->get(route('units.edit', $unit->id));

        $response->assertStatus(302);
        $response->assertRedirect(route('units.index'));
    }

    public function test_can_update_unit(): void
    {
        $unit = Units::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Pack',
        ]);

        $response = $this->actingAs($this->user)->put(route('units.update', $unit->id), [
            'name' => 'Pack Premium',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('units.index'));
        $response->assertSessionHas('success', 'Unit updated successfully.');

        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'name' => 'Pack Premium',
        ]);
    }

    public function test_can_update_unit_json(): void
    {
        $unit = Units::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Sachet',
        ]);

        $response = $this->actingAs($this->user)
            ->putJson(route('units.update', $unit->id), [
                'name' => 'Sachet Kecil',
            ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'message' => 'Unit berhasil diupdate',
        ]);

        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'name' => 'Sachet Kecil',
        ]);
    }

    public function test_can_delete_unit(): void
    {
        $unit = Units::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Roll',
        ]);

        $response = $this->actingAs($this->user)->delete(route('units.destroy', $unit->id));

        $response->assertStatus(302);
        $response->assertRedirect(route('units.index'));
        $response->assertSessionHas('success', 'Unit deleted successfully.');

        $this->assertDatabaseMissing('units', [
            'id' => $unit->id,
        ]);
    }

    public function test_can_delete_unit_json(): void
    {
        $unit = Units::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Lusin',
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson(route('units.destroy', $unit->id));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'message' => 'Unit berhasil dihapus',
        ]);

        $this->assertDatabaseMissing('units', [
            'id' => $unit->id,
        ]);
    }
}
