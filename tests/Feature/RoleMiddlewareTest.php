<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;
    private Role $cabangRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::create([
            'id' => (string) Str::uuid(),
            'name' => 'admin',
            'description' => 'Administrator Utama',
        ]);

        $this->cabangRole = Role::create([
            'id' => (string) Str::uuid(),
            'name' => 'cabang',
            'description' => 'Pengelola Cabang',
        ]);
    }

    /**
     * Guest user should be redirected to login when trying to access admin or cabang dashboard.
     */
    public function test_guest_cannot_access_protected_dashboards(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('cabang.dashboard'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Admin user can access admin dashboard but not cabang dashboard.
     */
    public function test_admin_can_access_admin_dashboard_but_not_cabang_dashboard(): void
    {
        $admin = User::factory()->create(['role_id' => $this->adminRole->id]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get(route('cabang.dashboard'));
        $response->assertStatus(403);
    }

    /**
     * Cabang user can access cabang dashboard but not admin dashboard.
     */
    public function test_cabang_can_access_cabang_dashboard_but_not_admin_dashboard(): void
    {
        $cabang = User::factory()->create(['role_id' => $this->cabangRole->id]);

        $response = $this->actingAs($cabang)->get(route('cabang.dashboard'));
        $response->assertStatus(200);

        $response = $this->actingAs($cabang)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    /**
     * User without role cannot access either admin or cabang dashboard.
     */
    public function test_user_without_role_cannot_access_dashboards(): void
    {
        $user = User::factory()->create(['role_id' => null]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get(route('cabang.dashboard'));
        $response->assertStatus(403);
    }
}
