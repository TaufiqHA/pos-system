<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        // Menggunakan updateOrInsert untuk menghindari error duplicate entry jika dijalankan berulang
        DB::table('branches')->updateOrInsert(
            ['id' => 'BRC-001'],
            [
                'name' => 'Gudang Pusat',
                'address' => 'Alamat Gudang Pusat',
                'phone' => '081111111111',
                'wilayah_id' => null,
                'notes' => 'Cabang utama / Gudang Pusat',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
