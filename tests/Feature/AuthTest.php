<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Memastikan user bisa berhasil login dengan password yang benar.
     */
    public function test_user_can_login_with_correct_password(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('secret-password'),
        ]);

        $response = $this->postJson('/auth/login', [
            'email' => 'john@example.com',
            'password' => 'secret-password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Login success',
            ])
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'status',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertAuthenticatedAs($user);
    }

    /**
     * Memastikan user gagal login jika menggunakan kombinasi email/password yang salah.
     */
    public function test_user_cannot_login_with_incorrect_password(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('secret-password'),
        ]);

        $response = $this->postJson('/auth/login', [
            'email' => 'john@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials',
            ]);

        $this->assertGuest();
    }

    /**
     * Memastikan endpoint /auth/me dapat diakses dan mengembalikan profil saat user telah terautentikasi.
     */
    public function test_authenticated_user_can_access_me_endpoint(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);
    }

    /**
     * Memastikan endpoint /auth/me menolak (redirect) saat user belum terautentikasi.
     */
    public function test_unauthenticated_user_cannot_access_me_endpoint(): void
    {
        $response = $this->get('/auth/me');
        $response->assertRedirect(route('login'));
    }

    /**
     * Memastikan fungsi /auth/logout berhasil memutus sesi pengguna.
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logout success',
            ]);

        $this->assertGuest();
    }
}
