<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Role $adminRole;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminRole = Role::firstOrCreate(['name' => 'admin'], ['id' => (string) Str::uuid()]);
        $this->user = User::factory()->create(['role_id' => $this->adminRole->id]);
    }

    public function test_unauthenticated_user_cannot_access_users(): void
    {
        $response = $this->get(route('users.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_can_list_users(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('users.index'));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]);
    }

    public function test_can_store_user(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('users.store'), [
            'name' => 'User Baru',
            'email' => 'userbaru@example.com',
            'password' => 'password123',
            'role_id' => $this->adminRole->id,
            'status' => 'active',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'role_id',
                    'status',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'User Baru',
            'email' => 'userbaru@example.com',
        ]);
    }

    public function test_can_show_user(): void
    {
        $targetUser = User::factory()->create([
            'role_id' => $this->adminRole->id,
        ]);

        $response = $this->actingAs($this->user)->getJson(route('users.show', $targetUser->id));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $targetUser->id,
                'name' => $targetUser->name,
            ]);
    }

    public function test_can_update_user(): void
    {
        $targetUser = User::factory()->create([
            'role_id' => $this->adminRole->id,
        ]);

        $response = $this->actingAs($this->user)->putJson(route('users.update', $targetUser->id), [
            'name' => 'User Diupdate',
            'email' => $targetUser->email,
            'role_id' => $this->adminRole->id,
            'status' => 'inactive',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'User Diupdate',
                'status' => 'inactive',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'User Diupdate',
            'status' => 'inactive',
        ]);
    }

    public function test_can_update_user_without_password(): void
    {
        $targetUser = User::factory()->create([
            'role_id' => $this->adminRole->id,
        ]);

        $response = $this->actingAs($this->user)->putJson(route('users.update', $targetUser->id), [
            'name' => 'Tanpa Password',
            'email' => $targetUser->email,
            'role_id' => $this->adminRole->id,
            'status' => 'active',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Tanpa Password',
            ]);
    }

    public function test_can_delete_user(): void
    {
        $targetUser = User::factory()->create([
            'role_id' => $this->adminRole->id,
        ]);

        $response = $this->actingAs($this->user)->deleteJson(route('users.destroy', $targetUser->id));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'User berhasil dihapus',
            ]);

        $this->assertSoftDeleted('users', [
            'id' => $targetUser->id,
        ]);
    }

    public function test_store_validation_fails_without_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('users.store'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'role_id', 'status']);
    }

    public function test_store_validation_fails_with_duplicate_email(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('users.store'), [
            'name' => 'Duplicate User',
            'email' => $this->user->email,
            'password' => 'password123',
            'role_id' => $this->adminRole->id,
            'status' => 'active',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
