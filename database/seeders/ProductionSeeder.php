<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\SuperAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder untuk lingkungan PRODUKSI.
 * Hanya mengisi: peran, akun super admin pertama, dan konten CMS default.
 * Tidak membuat data demo sekolah.
 *
 * Jalankan: php artisan db:seed --force --class=ProductionSeeder
 * Idempoten: aman dijalankan berulang.
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // ── Peran (wajib untuk pembuatan akun sekolah) ──────
        foreach (['admin', 'guru', 'wali', 'siswa', 'kepsek', 'staff_tu', 'bendahara', 'bk'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // ── Super admin pertama ─────────────────────────────
        // PENTING: segera ganti password ini lewat panel /god → Super Admin.
        SuperAdmin::firstOrCreate(
            ['username' => 'god'],
            ['password' => Hash::make('godmode123'), 'name' => 'Platform Owner']
        );
        SuperAdmin::firstOrCreate(
            ['username' => 'superadmin'],
            ['password' => Hash::make('password'), 'name' => 'Super Administrator']
        );

        // ── CMS default ─────────────────────────────────────
        SiteSetting::set('site_tagline', SiteSetting::get('site_tagline', 'Sistem Informasi Manajemen Sekolah multi-tenant — kelola siswa, guru, presensi, tagihan, dan pengumuman dalam satu tampilan yang tenang dan modern.'));
        SiteSetting::set('site_hero_image', SiteSetting::get('site_hero_image', 'img/hero.svg'));
        SiteSetting::set('site_footer_text', SiteSetting::get('site_footer_text', 'Ruang GTK — Sistem Informasi Manajemen Sekolah'));

        Page::firstOrCreate(
            ['slug' => 'tentang'],
            [
                'title' => 'Tentang Ruang GTK',
                'content' => "Ruang GTK adalah Sistem Informasi Manajemen Sekolah multi-tenant yang dirancang untuk sekolah Indonesia.\n\nSatu platform untuk mengelola data siswa, presensi, kepegawaian, keuangan, dan komunikasi — dengan isolasi data penuh per sekolah.",
                'status' => 'published',
                'show_in_footer' => true,
                'sort_order' => 1,
            ]
        );
        Page::firstOrCreate(
            ['slug' => 'privasi'],
            [
                'title' => 'Kebijakan Privasi',
                'content' => "Kami menghargai privasi Anda. Data sekolah tersimpan terisolasi per tenant dan hanya dapat diakses oleh pengguna sekolah yang bersangkutan.",
                'status' => 'published',
                'show_in_footer' => true,
                'sort_order' => 2,
            ]
        );

        $this->command?->info('Produksi siap: super admin god/godmode123 (SEGERA GANTI!), roles & CMS terisi.');
    }
}
