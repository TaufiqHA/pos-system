<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'description' => 'Administrator Utama'],
            ['name' => 'cabang', 'description' => 'Pengelola Cabang'],
            ['name' => 'outlet', 'description' => 'Pengelola Outlet'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                [
                    'id' => (string) Str::uuid(),
                    'description' => $role['description']
                ]
            );
        }
    }
}
