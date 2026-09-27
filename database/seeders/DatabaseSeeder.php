<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Bill;
use App\Models\Employee;
use App\Models\Guardian;
use App\Models\PaymentType;
use App\Models\Position;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Peran ────────────────────────────────────────────
        $roles = collect(['admin', 'guru', 'wali', 'siswa', 'kepsek', 'staff_tu', 'bendahara', 'bk'])
            ->mapWithKeys(fn ($name) => [$name => Role::create(['name' => $name])]);

        // ── Tenant demo ──────────────────────────────────────
        $school = School::create([
            'name' => 'SMA Negeri 1 Nusantara',
            'subdomain' => 'demoschool',
            'package_tier' => 'menengah',
            'address' => 'Jl. Pendidikan No. 1, Jakarta',
        ]);

        // ── Pengguna ─────────────────────────────────────────
        $admin = User::create([
            'school_id' => $school->id,
            'role_id' => $roles['admin']->id,
            'username' => 'admin',
            'password' => Hash::make('password'),
            'email' => 'admin@demoschool.test',
        ]);

        $guruUser = User::create([
            'school_id' => $school->id,
            'role_id' => $roles['guru']->id,
            'username' => 'bsantoso',
            'password' => Hash::make('password'),
        ]);

        $waliUser = User::create([
            'school_id' => $school->id,
            'role_id' => $roles['wali']->id,
            'username' => 'wali.ahmad',
            'password' => Hash::make('password'),
        ]);

        // ── Kepegawaian ──────────────────────────────────────
        $posGuru = Position::create(['school_id' => $school->id, 'name' => 'Guru', 'base_salary' => 4500000]);
        $posTu = Position::create(['school_id' => $school->id, 'name' => 'Tenaga Usaha', 'base_salary' => 3200000]);
        $posKepsek = Position::create(['school_id' => $school->id, 'name' => 'Kepala Sekolah', 'base_salary' => 6000000]);

        $guru = Employee::create([
            'school_id' => $school->id,
            'position_id' => $posGuru->id,
            'user_id' => $guruUser->id,
            'nip' => '198505122010011003',
            'full_name' => 'Budi Santoso, S.Pd.',
            'status' => 'aktif',
        ]);

        Employee::create([
            'school_id' => $school->id,
            'position_id' => $posTu->id,
            'nip' => '199003152015021004',
            'full_name' => 'Siti Rahayu',
            'status' => 'aktif',
        ]);

        $kepsek = Employee::create([
            'school_id' => $school->id,
            'position_id' => $posKepsek->id,
            'nip' => '197506102005011002',
            'full_name' => 'Drs. Hendra Wijaya, M.Pd.',
            'status' => 'aktif',
        ]);

        // ── Tahun ajaran & kelas ─────────────────────────────
        $year = AcademicYear::create([
            'school_id' => $school->id,
            'year_label' => '2026/2027',
            'is_active' => true,
        ]);

        $classA = SchoolClass::create([
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'name' => 'X IPA 1',
            'homeroom_teacher_id' => $guru->id,
        ]);
        $classB = SchoolClass::create([
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'name' => 'X IPA 2',
            'homeroom_teacher_id' => $kepsek->id,
        ]);

        // ── Wali & siswa ─────────────────────────────────────
        $guardian = Guardian::create([
            'user_id' => $waliUser->id,
            'full_name' => 'Ahmad Fauzi',
            'phone_whatsapp' => '6281234567890',
            'relation_type' => 'Ayah',
        ]);

        $students = [
            ['class_id' => $classA->id, 'guardian_id' => $guardian->id, 'nis' => '2601', 'nisn' => '0091234567', 'full_name' => 'Aisyah Putri', 'gender' => 'P', 'birth_date' => '2010-04-12', 'status' => 'aktif'],
            ['class_id' => $classA->id, 'guardian_id' => null, 'nis' => '2602', 'nisn' => '0091234568', 'full_name' => 'Bagas Prakoso', 'gender' => 'L', 'birth_date' => '2010-08-03', 'status' => 'aktif'],
            ['class_id' => $classA->id, 'guardian_id' => null, 'nis' => '2603', 'nisn' => '0091234569', 'full_name' => 'Citra Dewi', 'gender' => 'P', 'birth_date' => '2010-01-25', 'status' => 'aktif'],
            ['class_id' => $classB->id, 'guardian_id' => null, 'nis' => '2604', 'nisn' => '0091234570', 'full_name' => 'Dimas Anggara', 'gender' => 'L', 'birth_date' => '2010-11-30', 'status' => 'aktif'],
            ['class_id' => $classB->id, 'guardian_id' => null, 'nis' => '2605', 'nisn' => '0091234571', 'full_name' => 'Elsa Maharani', 'gender' => 'P', 'birth_date' => '2010-06-18', 'status' => 'aktif'],
        ];

        foreach ($students as $data) {
            $data['school_id'] = $school->id;
            Student::create($data);
        }

        // ── Jenis tagihan & tagihan contoh ───────────────────
        $spp = PaymentType::create([
            'school_id' => $school->id,
            'name' => 'SPP',
            'default_amount' => 350000,
            'recurrence' => 'bulanan',
        ]);
        $udy = PaymentType::create([
            'school_id' => $school->id,
            'name' => 'Uang Development',
            'default_amount' => 1500000,
            'recurrence' => 'tahunan',
        ]);

        foreach (Student::all() as $i => $student) {
            Bill::create([
                'student_id' => $student->id,
                'payment_type_id' => $spp->id,
                'amount' => 350000,
                'due_date' => now()->startOfMonth()->addDays(9),
                'status' => $i < 2 ? 'lunas' : 'belum_bayar',
            ]);
            Bill::create([
                'student_id' => $student->id,
                'payment_type_id' => $udy->id,
                'amount' => 1500000,
                'due_date' => now()->addMonths(2),
                'status' => 'belum_bayar',
            ]);
        }

        // ── Pengumuman ───────────────────────────────────────
        Announcement::create([
            'school_id' => $school->id,
            'class_id' => null,
            'title' => 'Selamat Datang di Ruang GTK',
            'content' => 'Sistem Informasi Manajemen Sekolah kami resmi berjalan. Silakan periksa menu Dashboard untuk ringkasan aktivitas sekolah.',
            'published_at' => now(),
        ]);
        Announcement::create([
            'school_id' => $school->id,
            'class_id' => $classA->id,
            'title' => 'Remedial Fisika Kelas X IPA 1',
            'content' => 'Remedial Fisika diadakan Sabtu, pukul 08.00 di Lab Fisika. Siswa yang wajib remedial sudah diberi tahu guru pengampu.',
            'published_at' => now()->subDay(),
        ]);

        // ── Penanda selesai ──────────────────────────────────
        $this->command?->info('Seeder selesai: admin/password @ demoschool');

        // ── Super Admin Platform (guard terpisah) ────────────
        \App\Models\SuperAdmin::create([
            'username' => 'god',
            'password' => Hash::make('godmode123'),
            'name' => 'Platform Owner',
        ]);

        // ── CMS default ──────────────────────────────────────
        \App\Models\SiteSetting::set('site_tagline', 'Sistem Informasi Manajemen Sekolah multi-tenant — kelola siswa, guru, presensi, tagihan, dan pengumuman dalam satu tampilan yang tenang dan modern.');
        \App\Models\SiteSetting::set('site_hero_image', 'img/hero.svg');
        \App\Models\SiteSetting::set('site_footer_text', 'Ruang GTK — Sistem Informasi Manajemen Sekolah');

        \App\Models\Page::create([
            'title' => 'Tentang Ruang GTK',
            'slug' => 'tentang',
            'content' => "Ruang GTK adalah Sistem Informasi Manajemen Sekolah multi-tenant yang dirancang untuk sekolah Indonesia.\n\nSatu platform untuk mengelola data siswa, presensi, kepegawaian, keuangan, dan komunikasi — dengan isolasi data penuh per sekolah.",
            'status' => 'published',
            'show_in_footer' => true,
            'sort_order' => 1,
        ]);
        \App\Models\Page::create([
            'title' => 'Kebijakan Privasi',
            'slug' => 'privasi',
            'content' => "Kami menghargai privasi Anda. Data sekolah tersimpan terisolasi per tenant dan hanya dapat diakses oleh pengguna sekolah yang bersangkutan.",
            'status' => 'published',
            'show_in_footer' => true,
            'sort_order' => 2,
        ]);

        $this->command?->info('Super admin: god / godmode123 (via /super)');
    }
}
