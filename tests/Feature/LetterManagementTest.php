<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Employee;
use App\Models\Letter;
use App\Models\LetterType;
use App\Models\Major;
use App\Models\Position;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Services\LetterNumberService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LetterManagementTest extends TestCase
{
    use DatabaseMigrations;

    protected School $school;
    protected User $admin;
    protected User $tu;
    protected User $kepsek;
    protected User $siswa;
    protected Student $student;
    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $tuRole = Role::firstOrCreate(['name' => 'staff_tu']);
        $kepsekRole = Role::firstOrCreate(['name' => 'kepsek']);
        $siswaRole = Role::firstOrCreate(['name' => 'siswa']);

        $this->school = School::firstOrCreate(
            ['subdomain' => 'testschool'],
            [
                'name'            => 'SMK Negeri 1 Surabaya',
                'package_tier'    => 'menengah',
                'npsn'            => '20532219',
                'level'           => 'SMK',
                'status_sekolah'  => 'Negeri',
                'city'            => 'Kota Surabaya',
                'province'        => 'Jawa Timur',
                'principal_name'  => 'Drs. Hendra Kusuma, M.Pd.',
                'principal_nip'   => '19720315 199802 1 004',
                'principal_title' => 'Kepala Sekolah',
            ]
        );

        $this->admin = User::firstOrCreate(
            ['username' => 'testadmin', 'school_id' => $this->school->id],
            ['role_id' => $adminRole->id, 'password' => Hash::make('password123'), 'email' => 'admin@test.com']
        );

        $this->tu = User::firstOrCreate(
            ['username' => 'testtu', 'school_id' => $this->school->id],
            ['role_id' => $tuRole->id, 'password' => Hash::make('password123'), 'email' => 'tu@test.com']
        );

        $this->kepsek = User::firstOrCreate(
            ['username' => 'testkepsek', 'school_id' => $this->school->id],
            ['role_id' => $kepsekRole->id, 'password' => Hash::make('password123'), 'email' => 'kepsek@test.com']
        );

        $this->siswa = User::firstOrCreate(
            ['username' => 'testsiswa', 'school_id' => $this->school->id],
            ['role_id' => $siswaRole->id, 'password' => Hash::make('password123'), 'email' => 'siswa@test.com']
        );

        // Dummy academic & student data
        $year = AcademicYear::firstOrCreate(
            ['school_id' => $this->school->id, 'year_label' => '2026/2027'],
            ['is_active' => true]
        );
        $major = Major::firstOrCreate(
            ['school_id' => $this->school->id, 'code' => 'RPL'],
            ['name' => 'Rekayasa Perangkat Lunak']
        );
        $class = SchoolClass::firstOrCreate(
            ['school_id' => $this->school->id, 'name' => 'XII RPL 1'],
            ['academic_year_id' => $year->id, 'major_id' => $major->id]
        );
        $this->student = Student::firstOrCreate(
            ['school_id' => $this->school->id, 'nis' => '9901'],
            [
                'full_name'   => 'Budi Santoso Pratama',
                'nisn'        => '0081234567',
                'class_id'    => $class->id,
                'major_id'    => $major->id,
                'status'      => 'aktif',
                'birth_place' => 'Surabaya',
                'birth_date'  => '2008-05-14',
            ]
        );

        // Dummy GTK
        $pos = Position::firstOrCreate(['school_id' => $this->school->id, 'name' => 'Guru Kejuruan']);
        $this->employee = Employee::firstOrCreate(
            ['school_id' => $this->school->id, 'nip' => '198501012010011005'],
            [
                'full_name'   => 'Ahmad Zaki, S.Kom.',
                'position_id' => $pos->id,
                'status'      => 'aktif',
            ]
        );

        // Seed default letter templates
        LetterType::seedDefaultTemplatesForSchool($this->school);
    }

    public function test_access_control_and_auto_seeding(): void
    {
        // 1. Guest redirected
        $this->get(route('letters.index'))->assertRedirect(route('login'));

        // 2. Siswa role forbidden
        $this->actingAs($this->siswa)->get(route('letters.index'))->assertStatus(403);

        // 3. Admin & Staf TU can access
        $res = $this->actingAs($this->admin)->get(route('letters.index'));
        $res->assertStatus(200);
        $res->assertSee('Buku Agenda Persuratan &amp; SK', false);

        // Verify default letter types auto-seeded
        $this->assertGreaterThan(0, $this->school->letterTypes()->count());
        $this->assertDatabaseHas('letter_types', [
            'school_id' => $this->school->id,
            'code'      => 'SK',
            'category'  => 'sk',
        ]);
        $this->assertDatabaseHas('letter_types', [
            'school_id' => $this->school->id,
            'code'      => 'KET-AKTIF',
            'category'  => 'surat_keluar',
        ]);
    }

    public function test_intelligent_letter_numbering_for_sk_and_surat_keluar(): void
    {
        $skType = $this->school->letterTypes()->where('code', 'SK')->first();
        $ketType = $this->school->letterTypes()->where('code', 'KET-AKTIF')->first();

        // 1. Issue Surat Keluar #1
        $res1 = $this->actingAs($this->tu)->post(route('letters.store'), [
            'letter_type_id'      => $ketType->id,
            'letter_date'         => '2026-09-15',
            'subject'             => 'Surat Keterangan Siswa Aktif PIP',
            'recipient'           => 'Bank Penyalur Beasiswa',
            'student_id'          => $this->student->id,
            'content'             => '<p>Menerangkan bahwa {nama_siswa} aktif di {nama_sekolah}.</p>',
            'status'              => 'diterbitkan',
            'signed_by_principal' => true,
        ]);
        $res1->assertRedirect();

        $letter1 = Letter::where('subject', 'Surat Keterangan Siswa Aktif PIP')->first();
        $this->assertNotNull($letter1);
        $this->assertEquals(1, $letter1->sequence_number);
        $this->assertEquals('surat_keluar', $letter1->category);
        $this->assertStringContainsString('001', $letter1->reference_number);

        // 2. Issue Surat Keluar #2 -> must be sequence 2
        $res2 = $this->actingAs($this->tu)->post(route('letters.store'), [
            'letter_type_id'      => $ketType->id,
            'letter_date'         => '2026-09-16',
            'subject'             => 'Surat Keterangan Siswa Aktif Lomba',
            'recipient'           => 'Panitia Lomba Nasional',
            'student_id'          => $this->student->id,
            'content'             => '<p>Siswa aktif.</p>',
            'status'              => 'diterbitkan',
        ]);
        $res2->assertRedirect();

        $letter2 = Letter::where('subject', 'Surat Keterangan Siswa Aktif Lomba')->first();
        $this->assertEquals(2, $letter2->sequence_number);
        $this->assertStringContainsString('002', $letter2->reference_number);

        // 3. Issue Surat Keputusan (SK) #1 -> must start from sequence 1 (independent from Surat Keluar!)
        $skRes1 = $this->actingAs($this->tu)->post(route('letters.store'), [
            'letter_type_id'      => $skType->id,
            'letter_date'         => '2026-09-17',
            'subject'             => 'SK Pembagian Tugas Guru TA 2026/2027',
            'recipient'           => 'Dewan Guru',
            'employee_id'         => $this->employee->id,
            'content'             => '<p>Memutuskan pembagian tugas guru.</p>',
            'status'              => 'diterbitkan',
        ]);
        $skRes1->assertRedirect();

        $sk1 = Letter::where('subject', 'SK Pembagian Tugas Guru TA 2026/2027')->first();
        $this->assertNotNull($sk1);
        $this->assertEquals(1, $sk1->sequence_number);
        $this->assertEquals('sk', $sk1->category);
        $this->assertStringContainsString('/001/SK-', $sk1->reference_number);

        // 4. Issue Surat Keputusan (SK) #2 -> must be sequence 2
        $skRes2 = $this->actingAs($this->tu)->post(route('letters.store'), [
            'letter_type_id'      => $skType->id,
            'letter_date'         => '2026-09-18',
            'subject'             => 'SK Tim Pengembang Kurikulum',
            'recipient'           => 'Bapak/Ibu Tim',
            'content'             => '<p>Memutuskan tim kurikulum.</p>',
            'status'              => 'diterbitkan',
        ]);
        $skRes2->assertRedirect();

        $sk2 = Letter::where('subject', 'SK Tim Pengembang Kurikulum')->first();
        $this->assertEquals(2, $sk2->sequence_number);
        $this->assertEquals('sk', $sk2->category);
        $this->assertStringContainsString('/002/SK-', $sk2->reference_number);
    }

    public function test_custom_reference_number_override(): void
    {
        $skType = $this->school->letterTypes()->where('code', 'SK')->first();

        $customRef = '800/999.KHUSUS/SK-KS/IX/2026';

        $res = $this->actingAs($this->tu)->post(route('letters.store'), [
            'letter_type_id'          => $skType->id,
            'letter_date'             => '2026-09-20',
            'subject'                 => 'SK Khusus Penunjukan Panitia Akreditasi',
            'custom_reference_number' => $customRef,
            'content'                 => '<p>Konten SK Khusus.</p>',
            'status'                  => 'diterbitkan',
        ]);

        $res->assertRedirect();
        $letter = Letter::where('subject', 'SK Khusus Penunjukan Panitia Akreditasi')->first();
        $this->assertEquals($customRef, $letter->reference_number);
    }

    public function test_placeholder_replacer_service(): void
    {
        $service = app(LetterNumberService::class);

        $template = "Nama: {nama_siswa}, NIS: {nis_siswa}, Sekolah: {nama_sekolah}, Kepsek: {nama_kepsek}";
        $parsed = $service->parseTemplatePlaceholders(
            $template,
            $this->school,
            ['reference_number' => '422/001/2026', 'subject' => 'Surat Keterangan'],
            $this->student
        );

        $this->assertStringContainsString('Budi Santoso Pratama', $parsed);
        $this->assertStringContainsString('9901', $parsed);
        $this->assertStringContainsString('SMK Negeri 1 Surabaya', $parsed);
        $this->assertStringContainsString('Drs. Hendra Kusuma, M.Pd.', $parsed);
    }

    public function test_letter_print_and_export(): void
    {
        $skType = $this->school->letterTypes()->where('code', 'SK')->first();

        $letter = Letter::create([
            'school_id'           => $this->school->id,
            'letter_type_id'      => $skType->id,
            'category'            => 'sk',
            'sequence_number'     => 1,
            'year'                => 2026,
            'reference_number'    => '800/001/SK-SMK/IX/2026',
            'letter_date'         => '2026-09-20',
            'subject'             => 'SK Pengangkatan Staf',
            'recipient'           => 'Staf Terkait',
            'content'             => '<p>Memutuskan dan menetapkan pengangkatan.</p>',
            'status'              => 'diterbitkan',
            'signed_by_principal' => true,
            'created_by'          => $this->admin->id,
        ]);

        // 1. Detail view
        $show = $this->actingAs($this->tu)->get(route('letters.show', $letter));
        $show->assertStatus(200);
        $show->assertSee('800/001/SK-SMK/IX/2026');

        // 2. Print view
        $print = $this->actingAs($this->tu)->get(route('letters.print', $letter));
        $print->assertStatus(200);
        $print->assertSee('PEMERINTAH PROVINSI JAWA TIMUR');
        $print->assertSee('SMK NEGERI 1 SURABAYA');
        $print->assertSee('Drs. Hendra Kusuma, M.Pd.');
        $print->assertSee('19720315 199802 1 004');

        // 3. Excel Export (.xlsx)
        $export = $this->actingAs($this->tu)->get(route('letters.export'));
        $export->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', $export->headers->get('Content-Type'));
    }

    public function test_letter_type_management_crud(): void
    {
        // 1. Visit letter-types index
        $res = $this->actingAs($this->admin)->get(route('letter-types.index'));
        $res->assertStatus(200);
        $res->assertSee('Format Penomoran &amp; Jenis Surat', false);

        // 2. Store new type
        $post = $this->actingAs($this->admin)->post(route('letter-types.store'), [
            'name'                  => 'Surat Rekomendasi Beasiswa Kuliah',
            'code'                  => 'REK-KULIAH',
            'category'              => 'surat_keluar',
            'classification_code'   => '422',
            'numbering_format'      => '{KODE}/{NOMOR}/REK-KUL/{SEKOLAH}/{BULAN_ROMAWI}/{TAHUN}',
            'padding_digits'        => 3,
            'default_template_body' => '<p>Dengan ini memberikan rekomendasi...</p>',
        ]);
        $post->assertRedirect();
        $this->assertDatabaseHas('letter_types', [
            'school_id' => $this->school->id,
            'code'      => 'REK-KULIAH',
        ]);
    }

    public function test_create_and_edit_views_render_cleanly(): void
    {
        // 1. Visit Create page (ensures $school->students() and $school->employees() load smoothly without BadMethodCallException)
        $createRes = $this->actingAs($this->tu)->get(route('letters.create'));
        $createRes->assertStatus(200);
        $createRes->assertSee('Buat Surat Keluar / Surat Keputusan (SK)');
        $createRes->assertSee('Budi Santoso Pratama'); // Student option
        $createRes->assertSee('Ahmad Zaki, S.Kom.'); // GTK option

        // 2. Test AJAX preview endpoint
        $skType = $this->school->letterTypes()->where('code', 'SK')->first();
        $previewRes = $this->actingAs($this->tu)->get(route('letters.preview-number', [
            'type_id'    => $skType->id,
            'student_id' => $this->student->id,
            'date'       => '2026-09-29',
        ]));
        $previewRes->assertStatus(200);
        $previewRes->assertJsonStructure(['reference_number', 'sequence_number', 'category', 'template_body']);

        // 3. Visit Edit page
        $letter = Letter::create([
            'school_id'           => $this->school->id,
            'letter_type_id'      => $skType->id,
            'category'            => 'sk',
            'sequence_number'     => 1,
            'year'                => 2026,
            'reference_number'    => '800/001/SK-SMK/IX/2026',
            'letter_date'         => '2026-09-20',
            'subject'             => 'SK Pengangkatan Staf',
            'content'             => '<p>Isi surat...</p>',
            'status'              => 'diterbitkan',
            'signed_by_principal' => true,
            'created_by'          => $this->admin->id,
        ]);

        $editRes = $this->actingAs($this->tu)->get(route('letters.edit', $letter));
        $editRes->assertStatus(200);
        $editRes->assertSee('Edit Surat: 800/001/SK-SMK/IX/2026');
        $editRes->assertSee('Budi Santoso Pratama');
    }
}
