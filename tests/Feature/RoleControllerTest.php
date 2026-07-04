<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_role(): void
    {
        $response = $this->post(route('roles.store'), [
            'name' => 'manager',
            'description' => 'Branch Manager',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Role created successfully.');

        $this->assertDatabaseHas('roles', [
            'name' => 'manager',
            'description' => 'Branch Manager',
        ]);

        $role = Role::where('name', 'manager')->first();
        $this->assertNotNull($role);
        $this->assertNotNull($role->id);
    }

    public function test_cannot_create_role_with_duplicate_name(): void
    {
        Role::create([
            'id' => 'some-uuid',
            'name' => 'manager',
            'description' => 'Branch Manager',
        ]);

        $response = $this->post(route('roles.store'), [
            'name' => 'manager',
            'description' => 'Another Manager',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_can_update_role(): void
    {
        $role = Role::create([
            'id' => 'some-uuid',
            'name' => 'manager',
            'description' => 'Branch Manager',
        ]);

        $response = $this->put(route('roles.update', $role->id), [
            'name' => 'updated-manager',
            'description' => 'Updated Description',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Role updated successfully.');

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'updated-manager',
            'description' => 'Updated Description',
        ]);
    }

    public function test_can_delete_role(): void
    {
        $role = Role::create([
            'id' => 'some-uuid',
            'name' => 'manager',
            'description' => 'Branch Manager',
        ]);

        $response = $this->delete(route('roles.destroy', $role->id));

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Role deleted successfully.');

        $this->assertDatabaseMissing('roles', [
            'id' => $role->id,
        ]);
    }
}
