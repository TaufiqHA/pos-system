<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Mendapatkan role admin
        $adminRole = Role::where('name', 'admin')->first();

        // Membuat atau mengupdate user admin default agar idempotent
        $user = User::where('email', 'admin@pos.com')->first();
        if ($user) {
            $user->update([
                'role_id' => $adminRole?->id,
                'branch_id' => 'BRC-001', // Relasi ke Gudang Pusat
                'name' => 'Admin POS',
                'status' => 'active',
            ]);
        } else {
            User::create([
                'id' => (string) Str::uuid(),
                'role_id' => $adminRole?->id,
                'branch_id' => 'BRC-001', // Relasi ke Gudang Pusat
                'name' => 'Admin POS',
                'email' => 'admin@pos.com',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]);
        }
    }
}
