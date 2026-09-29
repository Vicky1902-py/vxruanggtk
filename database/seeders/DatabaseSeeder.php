<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Bersih tanpa akun demo: Hanya menginisialisasi peran, super admin awal, dan konten CMS.
     * Semua sekolah dan akun pengguna dibuat langsung melalui Super Admin Panel (/super).
     */
    public function run(): void
    {
        $this->call(ProductionSeeder::class);
    }
}
