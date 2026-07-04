<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['id' => (string) Str::uuid()]);
        $this->user = User::factory()->create(['role_id' => $adminRole->id]);
    }

    public function test_unauthenticated_user_cannot_access_categories(): void
    {
        $response = $this->get(route('categories.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_can_list_categories(): void
    {
        $category = Category::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Elektronik',
        ]);

        $response = $this->actingAs($this->user)->get(route('categories.index'));

        $response->assertStatus(200);
        $response->assertViewHas('categories');
        $response->assertSee('Elektronik');
    }

    public function test_create_category_form_redirects_to_index(): void
    {
        $response = $this->actingAs($this->user)->get(route('categories.create'));

        $response->assertStatus(302);
        $response->assertRedirect(route('categories.index'));
    }

    public function test_can_store_category(): void
    {
        $response = $this->actingAs($this->user)->post(route('categories.store'), [
            'name' => 'Makanan Ringan',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('success', 'Category created successfully.');

        $this->assertDatabaseHas('categories', [
            'name' => 'Makanan Ringan',
        ]);
    }

    public function test_cannot_store_duplicate_category_name(): void
    {
        Category::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Makanan Ringan',
        ]);

        $response = $this->actingAs($this->user)->post(route('categories.store'), [
            'name' => 'Makanan Ringan',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_can_show_category_json(): void
    {
        $category = Category::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Minuman',
        ]);

        $response = $this->actingAs($this->user)->get(route('categories.show', $category->id));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $category->id,
            'name' => 'Minuman',
        ]);
    }

    public function test_edit_category_form_redirects_to_index(): void
    {
        $category = Category::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Minuman',
        ]);

        $response = $this->actingAs($this->user)->get(route('categories.edit', $category->id));

        $response->assertStatus(302);
        $response->assertRedirect(route('categories.index'));
    }

    public function test_can_update_category(): void
    {
        $category = Category::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Sembako',
        ]);

        $response = $this->actingAs($this->user)->put(route('categories.update', $category->id), [
            'name' => 'Sembako Premium',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('success', 'Category updated successfully.');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Sembako Premium',
        ]);
    }

    public function test_can_delete_category(): void
    {
        $category = Category::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Sembako',
        ]);

        $response = $this->actingAs($this->user)->delete(route('categories.destroy', $category->id));

        $response->assertStatus(302);
        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('success', 'Category deleted successfully.');

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }
}
