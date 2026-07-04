<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BranchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_branch(): void
    {
        Wilayah::create([
            'id' => 'Jawa Barat',
            'name' => 'Jawa Barat',
        ]);

        $response = $this->post(route('branches.store'), [
            'name' => 'Cabang Bandung',
            'address' => 'Jl. Merdeka No. 10',
            'phone' => '08123456789',
            'wilayah_id' => 'Jawa Barat',
            'notes' => 'Kantor Cabang Baru',
            'email' => 'bandung@pos.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Branch dan Akun berhasil dibuat.');

        $this->assertDatabaseHas('branches', [
            'name' => 'Cabang Bandung',
            'address' => 'Jl. Merdeka No. 10',
            'phone' => '08123456789',
            'wilayah_id' => 'Jawa Barat',
            'notes' => 'Kantor Cabang Baru',
        ]);

        $branch = Branch::where('name', 'Cabang Bandung')->first();
        $this->assertNotNull($branch);
        $this->assertNotNull($branch->id);

        $this->assertDatabaseHas('users', [
            'name' => 'Admin Cabang Bandung',
            'email' => 'bandung@pos.com',
            'branch_id' => $branch->id,
            'status' => 'active',
        ]);
    }

    public function test_cannot_create_branch_without_name(): void
    {
        $response = $this->post(route('branches.store'), [
            'name' => '',
            'email' => 'bandung@pos.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_can_update_branch(): void
    {
        $branch = Branch::create([
            'id' => 'some-uuid',
            'name' => 'Cabang Bandung',
            'address' => 'Jl. Merdeka No. 10',
        ]);

        $response = $this->put(route('branches.update', $branch->id), [
            'name' => 'Cabang Bandung Updated',
            'address' => 'Jl. Diponegoro No. 20',
            'phone' => '08987654321',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Branch updated successfully.');

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'name' => 'Cabang Bandung Updated',
            'address' => 'Jl. Diponegoro No. 20',
            'phone' => '08987654321',
        ]);
    }

    public function test_can_delete_branch_soft_delete(): void
    {
        $branch = Branch::create([
            'id' => 'some-uuid',
            'name' => 'Cabang Bandung',
        ]);

        $response = $this->delete(route('branches.destroy', $branch->id));

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Branch deleted successfully.');

        $this->assertSoftDeleted('branches', [
            'id' => $branch->id,
        ]);
    }

    public function test_can_get_branches_index(): void
    {
        $response = $this->get(route('branches.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.branch');
        $response->assertViewHas('branches');
        $response->assertViewHas('wilayahs');
    }

    public function test_branches_index_hides_branches_associated_with_admin_users(): void
    {
        // 1. Create a branch linked to an admin user
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['id' => (string) Str::uuid()]);
        $branchWithAdmin = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Admin',
        ]);
        User::factory()->create([
            'role_id' => $adminRole->id,
            'branch_id' => $branchWithAdmin->id,
        ]);

        // 2. Create a branch linked to a non-admin user
        $cabangRole = Role::firstOrCreate(['name' => 'cabang'], ['id' => (string) Str::uuid()]);
        $branchWithCabang = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Biasa',
        ]);
        User::factory()->create([
            'role_id' => $cabangRole->id,
            'branch_id' => $branchWithCabang->id,
        ]);

        // 3. Create a branch not linked to any user
        $branchWithoutUser = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Kosong',
        ]);

        // Run request
        $response = $this->get(route('branches.index'));

        $response->assertStatus(200);

        $branchesInView = $response->viewData('branches');

        // Assert Cabang Admin is hidden
        $this->assertFalse($branchesInView->contains('id', $branchWithAdmin->id));

        // Assert other branches are visible
        $this->assertTrue($branchesInView->contains('id', $branchWithCabang->id));
        $this->assertTrue($branchesInView->contains('id', $branchWithoutUser->id));
    }
}
