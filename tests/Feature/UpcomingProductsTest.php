<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\UpcomingProducts;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class UpcomingProductsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['id' => (string) Str::uuid()]);
        $this->user = User::factory()->create(['role_id' => $adminRole->id]);
    }

    public function test_unauthenticated_user_cannot_access_upcoming_products(): void
    {
        $response = $this->get(route('upcoming-products.show', (string) Str::uuid()));
        $response->assertRedirect(route('login'));
    }

    public function test_can_list_upcoming_products_json(): void
    {
        $product = UpcomingProducts::create([
            'id' => (string) Str::uuid(),
            'name' => 'Sample Upcoming Product',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->getJson(route('upcoming-products.index'));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $product->id,
            'name' => 'Sample Upcoming Product',
        ]);
    }

    public function test_can_store_upcoming_product_without_image(): void
    {
        $response = $this->actingAs($this->user)->post(route('upcoming-products.store'), [
            'name' => 'Super Premium Widget',
            'description' => 'A wonderful new product coming soon.',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('upcoming-products.index'));

        $this->assertDatabaseHas('upcoming_products', [
            'name' => 'Super Premium Widget',
            'description' => 'A wonderful new product coming soon.',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_can_store_upcoming_product_json(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('upcoming-products.store'), [
            'name' => 'Super Premium Widget JSON',
            'description' => 'A wonderful new product coming soon in JSON.',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'name',
                'description',
                'created_by',
            ],
        ]);

        $this->assertDatabaseHas('upcoming_products', [
            'name' => 'Super Premium Widget JSON',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_can_store_upcoming_product_with_image(): void
    {
        Storage::fake('public');

        $imageFile = UploadedFile::fake()->image('upcoming_widget.jpg');

        $response = $this->actingAs($this->user)->post(route('upcoming-products.store'), [
            'name' => 'Super Premium Widget with Image',
            'description' => 'Coming soon with high quality graphics.',
            'image' => $imageFile,
        ]);

        $response->assertStatus(302);

        $product = UpcomingProducts::where('name', 'Super Premium Widget with Image')->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->image);

        // Check file exists in faked storage
        $storedPath = str_replace('/storage/', '', $product->image);
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_can_show_upcoming_product_json(): void
    {
        $product = UpcomingProducts::create([
            'id' => (string) Str::uuid(),
            'name' => 'Fancy Glassware',
            'description' => 'Extremely delicate.',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('upcoming-products.show', $product->id));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $product->id,
            'name' => 'Fancy Glassware',
        ]);
    }

    public function test_can_update_upcoming_product(): void
    {
        $product = UpcomingProducts::create([
            'id' => (string) Str::uuid(),
            'name' => 'Fancy Glassware',
            'description' => 'Extremely delicate.',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->put(route('upcoming-products.update', $product->id), [
            'name' => 'Fancy Glassware Improved',
            'description' => 'A bit sturdier now.',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('upcoming-products.index'));

        $this->assertDatabaseHas('upcoming_products', [
            'id' => $product->id,
            'name' => 'Fancy Glassware Improved',
            'description' => 'A bit sturdier now.',
        ]);
    }

    public function test_can_delete_upcoming_product(): void
    {
        $product = UpcomingProducts::create([
            'id' => (string) Str::uuid(),
            'name' => 'Fancy Glassware To Delete',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('upcoming-products.destroy', $product->id));

        $response->assertStatus(302);
        $response->assertRedirect(route('upcoming-products.index'));

        // Since it's soft deletes, we assert it's soft deleted
        $this->assertSoftDeleted('upcoming_products', [
            'id' => $product->id,
        ]);
    }

    public function test_cabang_user_can_access_upcoming_products_index(): void
    {
        $cabangRole = Role::firstOrCreate(['name' => 'cabang'], ['id' => (string) Str::uuid()]);
        $cabangUser = User::factory()->create(['role_id' => $cabangRole->id]);

        $product = UpcomingProducts::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cabang Visible Upcoming Product',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($cabangUser)->getJson(route('cabang.upcoming-products.index'));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $product->id,
            'name' => 'Cabang Visible Upcoming Product',
        ]);
    }

    public function test_outlet_user_can_access_upcoming_products_index(): void
    {
        $outletRole = Role::firstOrCreate(['name' => 'outlet'], ['id' => (string) Str::uuid()]);
        $outletUser = User::factory()->create(['role_id' => $outletRole->id]);

        $product = UpcomingProducts::create([
            'id' => (string) Str::uuid(),
            'name' => 'Outlet Visible Upcoming Product',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($outletUser)->getJson(route('outlet.upcoming-products.index'));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $product->id,
            'name' => 'Outlet Visible Upcoming Product',
        ]);
    }
}
