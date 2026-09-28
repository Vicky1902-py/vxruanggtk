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
        // ── Peran (Idempotent) ───────────────────────────────
        $roles = collect(['admin', 'guru', 'wali', 'siswa', 'kepsek', 'staff_tu', 'bendahara', 'bk'])
            ->mapWithKeys(fn ($name) => [$name => Role::firstOrCreate(['name' => $name])]);

        // ── Tenant demo ──────────────────────────────────────
        $school = School::firstOrCreate(
            ['subdomain' => 'demoschool'],
            [
                'name' => 'SMA Negeri 1 Nusantara',
                'package_tier' => 'menengah',
                'address' => 'Jl. Pendidikan No. 1, Jakarta',
            ]
        );

        // ── Pengguna ─────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'school_id' => $school->id,
                'role_id' => $roles['admin']->id,
                'password' => Hash::make('password'),
                'email' => 'admin@demoschool.test',
            ]
        );

        $guruUser = User::firstOrCreate(
            ['username' => 'bsantoso'],
            [
                'school_id' => $school->id,
                'role_id' => $roles['guru']->id,
                'password' => Hash::make('password'),
            ]
        );

        $waliUser = User::firstOrCreate(
            ['username' => 'wali.ahmad'],
            [
                'school_id' => $school->id,
                'role_id' => $roles['wali']->id,
                'password' => Hash::make('password'),
            ]
        );

        $bendaharaUser = User::firstOrCreate(
            ['username' => 'bendahara'],
            [
                'school_id' => $school->id,
                'role_id' => $roles['bendahara']->id,
                'password' => Hash::make('password'),
            ]
        );

        $tuUser = User::firstOrCreate(
            ['username' => 'tu.siti'],
            [
                'school_id' => $school->id,
                'role_id' => $roles['staff_tu']->id,
                'password' => Hash::make('password'),
            ]
        );

        $kepsekUser = User::firstOrCreate(
            ['username' => 'kepsek.hendra'],
            [
                'school_id' => $school->id,
                'role_id' => $roles['kepsek']->id,
                'password' => Hash::make('password'),
            ]
        );

        // ── Kepegawaian ──────────────────────────────────────
        $posGuru = Position::firstOrCreate(
            ['school_id' => $school->id, 'name' => 'Guru'],
            ['base_salary' => 4500000]
        );
        $posTu = Position::firstOrCreate(
            ['school_id' => $school->id, 'name' => 'Tenaga Usaha'],
            ['base_salary' => 3200000]
        );
        $posBendahara = Position::firstOrCreate(
            ['school_id' => $school->id, 'name' => 'Bendahara Sekolah'],
            ['base_salary' => 4000000]
        );
        $posKepsek = Position::firstOrCreate(
            ['school_id' => $school->id, 'name' => 'Kepala Sekolah'],
            ['base_salary' => 6000000]
        );

        $guru = Employee::firstOrCreate(
            ['nip' => '198505122010011003'],
            [
                'school_id' => $school->id,
                'position_id' => $posGuru->id,
                'user_id' => $guruUser->id,
                'full_name' => 'Budi Santoso, S.Pd.',
                'status' => 'aktif',
            ]
        );

        Employee::firstOrCreate(
            ['nip' => '199003152015021004'],
            [
                'school_id' => $school->id,
                'position_id' => $posTu->id,
                'user_id' => $tuUser->id,
                'full_name' => 'Siti Rahayu',
                'status' => 'aktif',
            ]
        );

        Employee::firstOrCreate(
            ['nip' => '198807202012012005'],
            [
                'school_id' => $school->id,
                'position_id' => $posBendahara->id,
                'user_id' => $bendaharaUser->id,
                'full_name' => 'Rina Marlina, S.E.',
                'status' => 'aktif',
            ]
        );

        $kepsek = Employee::firstOrCreate(
            ['nip' => '197506102005011002'],
            [
                'school_id' => $school->id,
                'position_id' => $posKepsek->id,
                'user_id' => $kepsekUser->id,
                'full_name' => 'Drs. Hendra Wijaya, M.Pd.',
                'status' => 'aktif',
            ]
        );

        // ── Tahun ajaran & kelas ─────────────────────────────
        $year = AcademicYear::firstOrCreate(
            ['school_id' => $school->id, 'year_label' => '2026/2027'],
            ['is_active' => true]
        );

        $classA = SchoolClass::firstOrCreate(
            ['school_id' => $school->id, 'name' => 'X IPA 1'],
            [
                'academic_year_id' => $year->id,
                'homeroom_teacher_id' => $guru->id,
            ]
        );
        $classB = SchoolClass::firstOrCreate(
            ['school_id' => $school->id, 'name' => 'X IPA 2'],
            [
                'academic_year_id' => $year->id,
                'homeroom_teacher_id' => $kepsek->id,
            ]
        );

        // ── Wali & siswa ─────────────────────────────────────
        $guardian = Guardian::firstOrCreate(
            ['user_id' => $waliUser->id],
            [
                'full_name' => 'Ahmad Fauzi',
                'phone_whatsapp' => '6281234567890',
                'relation_type' => 'Ayah',
            ]
        );

        $students = [
            ['class_id' => $classA->id, 'guardian_id' => $guardian->id, 'nis' => '2601', 'nisn' => '0091234567', 'full_name' => 'Aisyah Putri', 'gender' => 'P', 'birth_date' => '2010-04-12', 'status' => 'aktif'],
            ['class_id' => $classA->id, 'guardian_id' => null, 'nis' => '2602', 'nisn' => '0091234568', 'full_name' => 'Bagas Prakoso', 'gender' => 'L', 'birth_date' => '2010-08-03', 'status' => 'aktif'],
            ['class_id' => $classA->id, 'guardian_id' => null, 'nis' => '2603', 'nisn' => '0091234569', 'full_name' => 'Citra Dewi', 'gender' => 'P', 'birth_date' => '2010-01-25', 'status' => 'aktif'],
            ['class_id' => $classB->id, 'guardian_id' => null, 'nis' => '2604', 'nisn' => '0091234570', 'full_name' => 'Dimas Anggara', 'gender' => 'L', 'birth_date' => '2010-11-30', 'status' => 'aktif'],
            ['class_id' => $classB->id, 'guardian_id' => null, 'nis' => '2605', 'nisn' => '0091234571', 'full_name' => 'Elsa Maharani', 'gender' => 'P', 'birth_date' => '2010-06-18', 'status' => 'aktif'],
        ];

        foreach ($students as $data) {
            $data['school_id'] = $school->id;
            Student::firstOrCreate(['school_id' => $school->id, 'nis' => $data['nis']], $data);
        }

        // ── Jenis tagihan & tagihan contoh ───────────────────
        $spp = PaymentType::firstOrCreate(
            ['school_id' => $school->id, 'name' => 'SPP'],
            [
                'default_amount' => 350000,
                'recurrence' => 'bulanan',
            ]
        );
        $udy = PaymentType::firstOrCreate(
            ['school_id' => $school->id, 'name' => 'Uang Development'],
            [
                'default_amount' => 1500000,
                'recurrence' => 'tahunan',
            ]
        );

        foreach (Student::where('school_id', $school->id)->get() as $i => $student) {
            Bill::firstOrCreate(
                [
                    'school_id' => $school->id,
                    'student_id' => $student->id,
                    'payment_type_id' => $spp->id,
                ],
                [
                    'amount' => 350000,
                    'due_date' => now()->startOfMonth()->addDays(9),
                    'status' => $i < 2 ? 'lunas' : 'belum_bayar',
                ]
            );
            Bill::firstOrCreate(
                [
                    'school_id' => $school->id,
                    'student_id' => $student->id,
                    'payment_type_id' => $udy->id,
                ],
                [
                    'amount' => 1500000,
                    'due_date' => now()->addMonths(2),
                    'status' => 'belum_bayar',
                ]
            );
        }

        // ── Pengumuman ───────────────────────────────────────
        Announcement::firstOrCreate(
            [
                'school_id' => $school->id,
                'title' => 'Selamat Datang di Ruang GTK',
            ],
            [
                'class_id' => null,
                'content' => 'Sistem Informasi Manajemen Sekolah kami resmi berjalan. Silakan periksa menu Dashboard untuk ringkasan aktivitas sekolah.',
                'published_at' => now(),
            ]
        );
        Announcement::firstOrCreate(
            [
                'school_id' => $school->id,
                'title' => 'Remedial Fisika Kelas X IPA 1',
            ],
            [
                'class_id' => $classA->id,
                'content' => 'Remedial Fisika diadakan Sabtu, pukul 08.00 di Lab Fisika. Siswa yang wajib remedial sudah diberi tahu guru pengampu.',
                'published_at' => now()->subDay(),
            ]
        );

        // ── Penanda selesai ──────────────────────────────────
        $this->command?->info('Seeder selesai: admin/password @ demoschool');

        // ── Super Admin Platform (guard terpisah) ────────────
        \App\Models\SuperAdmin::firstOrCreate(
            ['username' => 'god'],
            [
                'password' => Hash::make('godmode123'),
                'name' => 'Platform Owner',
            ]
        );

        // ── CMS default ──────────────────────────────────────
        \App\Models\SiteSetting::set('site_tagline', 'Sistem Informasi Manajemen Sekolah multi-tenant — kelola siswa, guru, presensi, tagihan, dan pengumuman dalam satu tampilan yang tenang dan modern.');
        \App\Models\SiteSetting::set('site_hero_image', 'img/hero.svg');
        \App\Models\SiteSetting::set('site_footer_text', 'Ruang GTK — Sistem Informasi Manajemen Sekolah');

        \App\Models\Page::firstOrCreate(
            ['slug' => 'tentang'],
            [
                'title' => 'Tentang Ruang GTK',
                'content' => "Ruang GTK adalah Sistem Informasi Manajemen Sekolah multi-tenant yang dirancang untuk sekolah Indonesia.\n\nSatu platform untuk mengelola data siswa, presensi, kepegawaian, keuangan, dan komunikasi — dengan isolasi data penuh per sekolah.",
                'status' => 'published',
                'show_in_footer' => true,
                'sort_order' => 1,
            ]
        );
        \App\Models\Page::firstOrCreate(
            ['slug' => 'privasi'],
            [
                'title' => 'Kebijakan Privasi',
                'content' => "Kami menghargai privasi Anda. Data sekolah tersimpan terisolasi per tenant dan hanya dapat diakses oleh pengguna sekolah yang bersangkutan.",
                'status' => 'published',
                'show_in_footer' => true,
                'sort_order' => 2,
            ]
        );

        $this->command?->info('Super admin: god / godmode123 (via /super)');
    }
}
