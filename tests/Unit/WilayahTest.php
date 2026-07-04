<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Wilayah;

class WilayahTest extends TestCase
{
    use RefreshDatabase;

    // Test untuk method 0: index (get all)
    public function test_can_get_all_wilayah()
    {
        Wilayah::create([
            'id' => '32.73',
            'name' => 'Kota Bandung',
        ]);
        Wilayah::create([
            'id' => '31',
            'name' => 'DKI Jakarta',
        ]);

        $response = $this->getJson('/wilayah');

        $response->assertStatus(200)
                 ->assertJsonCount(2)
                 ->assertJsonFragment(['name' => 'Kota Bandung'])
                 ->assertJsonFragment(['name' => 'DKI Jakarta']);
    }

    // Test untuk method 1: create
    public function test_can_create_wilayah()
    {
        $payload = [
            'id' => '32.73',
            'name' => 'Kota Bandung',
        ];

        $response = $this->postJson('/wilayah', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('wilayahs', [
            'id' => '32.73',
            'name' => 'Kota Bandung',
        ]);
    }

    // Test untuk method 2: show
    public function test_can_show_wilayah()
    {
        $wilayah = Wilayah::create([
            'id' => '32.73',
            'name' => 'Kota Bandung',
        ]);

        $response = $this->getJson('/wilayah/' . $wilayah->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => '32.73',
                     'name' => 'Kota Bandung',
                 ]);
    }

    // Test untuk method 3: update
    public function test_can_update_wilayah()
    {
        $wilayah = Wilayah::create([
            'id' => '32.73',
            'name' => 'Kota Bandung',
        ]);

        $response = $this->putJson('/wilayah/' . $wilayah->id, [
            'name' => 'Kota Bandung Updated',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('wilayahs', [
            'id' => '32.73',
            'name' => 'Kota Bandung Updated',
        ]);
    }

    // Test untuk method 4: delete
    public function test_can_delete_wilayah()
    {
        $wilayah = Wilayah::create([
            'id' => '32.73',
            'name' => 'Kota Bandung',
        ]);

        $response = $this->deleteJson('/wilayah/' . $wilayah->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('wilayahs', [
            'id' => '32.73',
        ]);
    }
}
