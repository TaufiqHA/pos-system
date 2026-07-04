<?php

namespace Tests\Feature;

use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_branch(): void
    {
        $response = $this->post(route('branches.store'), [
            'name' => 'Cabang Bandung',
            'address' => 'Jl. Merdeka No. 10',
            'phone' => '08123456789',
            'wilayah' => 'Jawa Barat',
            'notes' => 'Kantor Cabang Baru',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Branch created successfully.');

        $this->assertDatabaseHas('branches', [
            'name' => 'Cabang Bandung',
            'address' => 'Jl. Merdeka No. 10',
            'phone' => '08123456789',
            'wilayah' => 'Jawa Barat',
            'notes' => 'Kantor Cabang Baru',
        ]);

        $branch = Branch::where('name', 'Cabang Bandung')->first();
        $this->assertNotNull($branch);
        $this->assertNotNull($branch->id);
    }

    public function test_cannot_create_branch_without_name(): void
    {
        $response = $this->post(route('branches.store'), [
            'name' => '',
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
}
