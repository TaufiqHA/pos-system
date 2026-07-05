<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Outlets;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OutletsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_outlet_with_uuid(): void
    {
        $branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Test',
        ]);

        $outletId = (string) Str::uuid();

        $outlet = Outlets::create([
            'id' => $outletId,
            'branch_id' => $branch->id,
            'name' => 'Outlet Test',
            'address' => 'Jl. Test No. 123',
            'phone' => '081234567890',
        ]);

        $this->assertDatabaseHas('outlets', [
            'id' => $outletId,
            'branch_id' => $branch->id,
            'name' => 'Outlet Test',
            'address' => 'Jl. Test No. 123',
            'phone' => '081234567890',
        ]);

        $this->assertEquals($branch->id, $outlet->branch->id);
        $this->assertTrue($branch->outlets->contains('id', $outletId));
    }
}
