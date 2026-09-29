<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Bill;
use App\Models\Employee;
use App\Models\Major;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Position;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\SuperAdmin;
use App\Models\User;
use App\Services\SpreadsheetService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SystemE2ETest extends TestCase
{
    use DatabaseMigrations;
    protected School $school;
    protected User $admin;
    protected User $guru;
    protected User $bendahara;
    protected User $kepsek;
    protected Role $adminRole;
    protected Role $guruRole;
    protected Role $bendaharaRole;
    protected Role $kepsekRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup base tenant and roles
        $this->adminRole = Role::firstOrCreate(['name' => 'admin']);
        $this->guruRole = Role::firstOrCreate(['name' => 'guru']);
        $this->bendaharaRole = Role::firstOrCreate(['name' => 'bendahara']);
        $this->kepsekRole = Role::firstOrCreate(['name' => 'kepsek']);

        $this->school = School::firstOrCreate(
            ['subdomain' => 'testschool'],
            ['name' => 'SMK Test Unggulan', 'package_tier' => 'menengah']
        );

        $this->admin = User::firstOrCreate(
            ['username' => 'testadmin', 'school_id' => $this->school->id],
            ['role_id' => $this->adminRole->id, 'password' => Hash::make('password123'), 'email' => 'admin@test.com']
        );

        $this->guru = User::firstOrCreate(
            ['username' => 'testguru', 'school_id' => $this->school->id],
            ['role_id' => $this->guruRole->id, 'password' => Hash::make('password123'), 'email' => 'guru@test.com']
        );

        $this->bendahara = User::firstOrCreate(
            ['username' => 'testbendahara', 'school_id' => $this->school->id],
            ['role_id' => $this->bendaharaRole->id, 'password' => Hash::make('password123'), 'email' => 'bendahara@test.com']
        );

        $this->kepsek = User::firstOrCreate(
            ['username' => 'testkepsek', 'school_id' => $this->school->id],
            ['role_id' => $this->kepsekRole->id, 'password' => Hash::make('password123'), 'email' => 'kepsek@test.com']
        );
    }

    public function test_public_pages_render_cleanly(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Ruang');
        $response->assertSee('GTK');

        $loginResponse = $this->get('/masuk');
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee('Subdomain');

        $superResponse = $this->get('/super');
        $superResponse->assertStatus(200);
    }

    public function test_auth_workflow_works(): void
    {
        // Failed login
        $fail = $this->post('/masuk', [
            'subdomain' => 'testschool',
            'username' => 'testadmin',
            'password' => 'wrongpass',
        ]);
        $fail->assertSessionHasErrors('username');
        $this->assertGuest();

        // Successful login
        $success = $this->post('/masuk', [
            'subdomain' => 'testschool',
            'username' => 'testadmin',
            'password' => 'password123',
        ]);
        $success->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_dashboard_renders_aapanel_telemetry_for_admin(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Selamat datang di');
        $response->assertSee('Presensi Siswa');
        $response->assertSee('Realisasi Kas SPP');
        $response->assertSee('Telemetri Server');
    }

    public function test_device_service_detection_and_adaptive_view(): void
    {
        // 1. Desktop request
        $desktopRes = $this->actingAs($this->admin)->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36'
        ])->get('/dashboard');
        $desktopRes->assertStatus(200);
        $desktopRes->assertSee('Selamat datang di');

        // 2. Mobile detection via User-Agent
        $mobileRes = $this->actingAs($this->admin)->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148'
        ])->get('/dashboard');
        $mobileRes->assertStatus(200);
    }

    public function test_major_module_crud_and_template(): void
    {
        // 1. Template download
        $tmpl = $this->actingAs($this->admin)->get(route('majors.template'));
        $tmpl->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', $tmpl->headers->get('Content-Type'));

        // 2. Store Major
        $store = $this->actingAs($this->admin)->post(route('majors.store'), [
            'code' => 'PPLG',
            'name' => 'Pengembangan Perangkat Lunak dan Gim',
            'description' => 'Fokus rekayasa perangkat lunak modern',
        ]);
        $store->assertRedirect();
        $this->assertDatabaseHas('majors', [
            'school_id' => $this->school->id,
            'code' => 'PPLG',
        ]);

        // 3. Update Major
        $major = Major::where('school_id', $this->school->id)->where('code', 'PPLG')->first();
        $update = $this->actingAs($this->admin)->put(route('majors.update', $major), [
            'code' => 'PPLG-1',
            'name' => 'Pengembangan Perangkat Lunak dan Gim Updated',
        ]);
        $update->assertRedirect();
        $this->assertDatabaseHas('majors', [
            'id' => $major->id,
            'code' => 'PPLG-1',
        ]);
    }

    public function test_classes_and_academic_year_crud(): void
    {
        // Store academic year
        $year = $this->actingAs($this->admin)->post(route('academic-years.store'), [
            'year_label' => '2026/2027',
            'is_active' => '1',
        ]);
        $year->assertRedirect();
        $this->assertDatabaseHas('academic_years', [
            'school_id' => $this->school->id,
            'year_label' => '2026/2027',
            'is_active' => true,
        ]);

        $ay = AcademicYear::where('school_id', $this->school->id)->first();

        // Store class
        $classRes = $this->actingAs($this->admin)->post(route('classes.store'), [
            'name' => 'X PPLG 1',
            'academic_year_id' => $ay->id,
        ]);
        $classRes->assertRedirect();
        $this->assertDatabaseHas('classes', [
            'school_id' => $this->school->id,
            'name' => 'X PPLG 1',
        ]);
    }

    public function test_student_crud_and_attendance_flow(): void
    {
        $ay = AcademicYear::firstOrCreate(
            ['school_id' => $this->school->id, 'year_label' => '2026/2027'],
            ['is_active' => true]
        );
        $cls = SchoolClass::firstOrCreate(
            ['school_id' => $this->school->id, 'name' => 'X PPLG 1'],
            ['academic_year_id' => $ay->id]
        );

        // Store student
        $studentRes = $this->actingAs($this->admin)->post(route('students.store'), [
            'class_id' => $cls->id,
            'nis' => '889901',
            'nisn' => '0088990101',
            'full_name' => 'Ahmad Dahlan',
            'gender' => 'L',
            'birth_date' => '2009-05-10',
            'status' => 'aktif',
        ]);
        $studentRes->assertRedirect();
        $this->assertDatabaseHas('students', [
            'school_id' => $this->school->id,
            'nis' => '889901',
            'full_name' => 'Ahmad Dahlan',
        ]);

        $student = Student::where('school_id', $this->school->id)->where('nis', '889901')->first();

        // Record attendance
        $attRes = $this->actingAs($this->admin)->post(route('attendance.store'), [
            'class_id' => $cls->id,
            'att_date' => date('Y-m-d'),
            'statuses' => [
                $student->id => 'hadir',
            ],
        ]);
        $attRes->assertRedirect();
        $this->assertDatabaseHas('attendance_student', [
            'student_id' => $student->id,
            'status' => 'hadir',
        ]);

        // Attendance index (harian & rekap)
        $harian = $this->actingAs($this->admin)->get(route('attendance.index', [
            'class_id' => $cls->id,
            'mode' => 'harian',
            'date' => date('Y-m-d'),
        ]));
        $harian->assertStatus(200);
        $harian->assertSee('Ahmad Dahlan');

        $rekap = $this->actingAs($this->admin)->get(route('attendance.index', [
            'class_id' => $cls->id,
            'mode' => 'rekap',
            'month' => date('Y-m'),
        ]));
        $rekap->assertStatus(200);
    }

    public function test_employee_and_gtk_attendance(): void
    {
        $pos = Position::firstOrCreate(
            ['school_id' => $this->school->id, 'name' => 'Guru Kejuruan'],
            ['base_salary' => 5000000]
        );

        $empRes = $this->actingAs($this->admin)->post(route('employees.store'), [
            'position_id' => $pos->id,
            'nip' => '198501012010011005',
            'full_name' => 'Dra. Sri Wahyuni',
            'status' => 'aktif',
        ]);
        $empRes->assertRedirect();
        $this->assertDatabaseHas('employees', [
            'school_id' => $this->school->id,
            'nip' => '198501012010011005',
        ]);

        $employee = Employee::where('nip', '198501012010011005')->first();

        // Bulk attendance
        $bulkAtt = $this->actingAs($this->admin)->post(route('attendance-employee.store'), [
            'att_date' => date('Y-m-d'),
            'statuses' => [
                $employee->id => 'hadir',
            ],
        ]);
        $bulkAtt->assertRedirect();
        $this->assertDatabaseHas('attendance_employee', [
            'employee_id' => $employee->id,
            'status' => 'hadir',
        ]);
    }

    public function test_billing_and_payment_flow(): void
    {
        $student = Student::where('school_id', $this->school->id)->first();
        if (!$student) {
            $ay = AcademicYear::firstOrCreate(['school_id' => $this->school->id, 'year_label' => '2026/2027'], ['is_active' => true]);
            $cls = SchoolClass::firstOrCreate(['school_id' => $this->school->id, 'name' => 'X PPLG 1'], ['academic_year_id' => $ay->id]);
            $student = Student::create([
                'school_id' => $this->school->id,
                'class_id' => $cls->id,
                'nis' => '12345',
                'full_name' => 'Siswa Test Bayar',
                'status' => 'aktif',
            ]);
        }

        $paymentType = PaymentType::firstOrCreate(
            ['school_id' => $this->school->id, 'name' => 'SPP Bulanan'],
            ['default_amount' => 250000, 'recurrence' => 'bulanan']
        );

        // 1. Generate mass bill
        $billRes = $this->actingAs($this->bendahara)->post(route('bills.store'), [
            'payment_type_id' => $paymentType->id,
            'class_id' => $student->class_id,
            'amount' => 250000,
            'due_date' => date('Y-m-t'),
        ]);
        $billRes->assertRedirect();

        $bill = Bill::where('student_id', $student->id)->where('payment_type_id', $paymentType->id)->latest()->first();
        $this->assertNotNull($bill);
        $this->assertEquals('belum_bayar', $bill->status);

        // 2. Pay bill
        $payRes = $this->actingAs($this->bendahara)->post(route('bills.pay', $bill), [
            'amount_paid' => 250000,
            'method' => 'Tunai',
        ]);
        $payRes->assertRedirect();

        $bill->refresh();
        $this->assertEquals('lunas', $bill->status);
        $this->assertDatabaseHas('payments', [
            'bill_id' => $bill->id,
            'amount_paid' => 250000,
            'method' => 'Tunai',
        ]);
    }

    public function test_announcement_workflow(): void
    {
        $annRes = $this->actingAs($this->admin)->post(route('announcements.store'), [
            'title' => 'Libur Awal Semester',
            'content' => 'Diberitahukan bahwa libur semester dimulai tanggal 1 Juli.',
        ]);
        $annRes->assertRedirect();
        $this->assertDatabaseHas('announcements', [
            'school_id' => $this->school->id,
            'title' => 'Libur Awal Semester',
        ]);
    }

    public function test_profile_password_update(): void
    {
        $updatePass = $this->actingAs($this->admin)->put(route('profile.password'), [
            'current_password' => 'password123',
            'new_password' => 'newsecretpass123',
            'new_password_confirmation' => 'newsecretpass123',
        ]);
        $updatePass->assertRedirect();

        $this->admin->refresh();
        $this->assertTrue(Hash::check('newsecretpass123', $this->admin->password));
    }

    public function test_excel_exports_for_all_modules(): void
    {
        // 1. Siswa Export
        $exportStudent = $this->actingAs($this->admin)->get(route('students.export'));
        $exportStudent->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', $exportStudent->headers->get('Content-Type'));

        // 2. GTK Export
        $exportEmployee = $this->actingAs($this->admin)->get(route('employees.export'));
        $exportEmployee->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', $exportEmployee->headers->get('Content-Type'));

        // 3. Tagihan Export
        $exportBills = $this->actingAs($this->bendahara)->get(route('bills.export'));
        $exportBills->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', $exportBills->headers->get('Content-Type'));

        // 4. Rekap Presensi Siswa Export
        $cls = SchoolClass::firstOrCreate(
            ['school_id' => $this->school->id, 'name' => 'X PPLG 1'],
            ['academic_year_id' => AcademicYear::firstOrCreate(['school_id' => $this->school->id, 'year_label' => '2026/2027'], ['is_active' => true])->id]
        );
        $exportAtt = $this->actingAs($this->admin)->get(route('attendance.export', [
            'class_id' => $cls->id,
            'month' => date('Y-m'),
        ]));
        $exportAtt->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', $exportAtt->headers->get('Content-Type'));

        // 5. Rekap Presensi GTK Export
        $exportGtkAtt = $this->actingAs($this->admin)->get(route('attendance-employee.export', [
            'month' => date('Y-m'),
        ]));
        $exportGtkAtt->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', $exportGtkAtt->headers->get('Content-Type'));
    }

    public function test_student_mass_promotion_and_graduation(): void
    {
        $ay = AcademicYear::firstOrCreate(['school_id' => $this->school->id, 'year_label' => '2026/2027'], ['is_active' => true]);
        $classA = SchoolClass::firstOrCreate(['school_id' => $this->school->id, 'name' => 'X PPLG 1'], ['academic_year_id' => $ay->id]);
        $classB = SchoolClass::firstOrCreate(['school_id' => $this->school->id, 'name' => 'XI PPLG 1'], ['academic_year_id' => $ay->id]);

        $s1 = Student::create(['school_id' => $this->school->id, 'class_id' => $classA->id, 'full_name' => 'Siswa Naik 1', 'status' => 'aktif']);
        $s2 = Student::create(['school_id' => $this->school->id, 'class_id' => $classA->id, 'full_name' => 'Siswa Naik 2', 'status' => 'aktif']);

        // Promote to classB
        $promoteRes = $this->actingAs($this->admin)->post(route('students.promote'), [
            'student_ids' => [$s1->id, $s2->id],
            'target_class_id' => $classB->id,
        ]);
        $promoteRes->assertRedirect();

        $this->assertDatabaseHas('students', ['id' => $s1->id, 'class_id' => $classB->id]);
        $this->assertDatabaseHas('students', ['id' => $s2->id, 'class_id' => $classB->id]);

        // Graduate
        $gradRes = $this->actingAs($this->admin)->post(route('students.graduate'), [
            'student_ids' => [$s1->id, $s2->id],
        ]);
        $gradRes->assertRedirect();

        $this->assertDatabaseHas('students', ['id' => $s1->id, 'status' => 'lulus']);
        $this->assertDatabaseHas('students', ['id' => $s2->id, 'status' => 'lulus']);
    }

    public function test_school_user_management_crud(): void
    {
        // 1. Index
        $index = $this->actingAs($this->admin)->get(route('users.index'));
        $index->assertStatus(200);
        $index->assertSee('Manajemen Pengguna');

        // 2. Store new internal user
        $store = $this->actingAs($this->admin)->post(route('users.store'), [
            'username' => 'bendahara_smk',
            'email' => 'bendahara@smk.sch.id',
            'role_id' => $this->bendaharaRole->id,
            'password' => 'secret123',
        ]);
        $store->assertRedirect();
        $this->assertDatabaseHas('users', [
            'school_id' => $this->school->id,
            'username' => 'bendahara_smk',
            'is_active' => true,
        ]);

        $newUser = User::where('username', 'bendahara_smk')->first();

        // 3. Toggle status
        $toggle = $this->actingAs($this->admin)->post(route('users.toggle', $newUser));
        $toggle->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $newUser->id,
            'is_active' => false,
        ]);

        // 4. Reset password
        $reset = $this->actingAs($this->admin)->post(route('users.reset', $newUser), [
            'new_password' => 'newpassword789',
        ]);
        $reset->assertRedirect();
        $newUser->refresh();
        $this->assertTrue(Hash::check('newpassword789', $newUser->password));

        // 5. Destroy
        $destroy = $this->actingAs($this->admin)->delete(route('users.destroy', $newUser));
        $destroy->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $newUser->id]);
    }

    public function test_savings_module_and_passbook_workflow(): void
    {
        $ay = AcademicYear::firstOrCreate(['school_id' => $this->school->id, 'year_label' => '2026/2027'], ['is_active' => true]);
        $cls = SchoolClass::firstOrCreate(['school_id' => $this->school->id, 'name' => 'X PPLG 1'], ['academic_year_id' => $ay->id]);
        $student = Student::create([
            'school_id' => $this->school->id,
            'class_id' => $cls->id,
            'nis' => '99001',
            'full_name' => 'Siswa Menabung',
            'status' => 'aktif',
        ]);

        // 1. Index
        $index = $this->actingAs($this->bendahara)->get(route('savings.index'));
        $index->assertStatus(200);
        $index->assertSee('Tabungan Siswa');

        // 2. Setor Rp 50.000
        $setor = $this->actingAs($this->bendahara)->post(route('savings.store'), [
            'student_id' => $student->id,
            'direction' => 'setor',
            'amount' => 50000,
            'note' => 'Setoran awal tabungan',
        ]);
        $setor->assertRedirect();
        $this->assertDatabaseHas('savings', [
            'student_id' => $student->id,
            'balance' => 50000,
        ]);
        $this->assertDatabaseHas('savings_transactions', [
            'student_id' => $student->id,
            'direction' => 'setor',
            'amount' => 50000,
            'balance_after' => 50000,
        ]);

        // 3. Tarik Rp 20.000
        $tarik = $this->actingAs($this->bendahara)->post(route('savings.store'), [
            'student_id' => $student->id,
            'direction' => 'tarik',
            'amount' => 20000,
            'note' => 'Beli alat tulis',
        ]);
        $tarik->assertRedirect();
        $this->assertDatabaseHas('savings', [
            'student_id' => $student->id,
            'balance' => 30000,
        ]);

        // 4. Overdraw tarik Rp 100.000 (harus ditolak karena saldo hanya 30.000)
        $overdraw = $this->actingAs($this->bendahara)->post(route('savings.store'), [
            'student_id' => $student->id,
            'direction' => 'tarik',
            'amount' => 100000,
        ]);
        $overdraw->assertRedirect();
        $this->assertDatabaseHas('savings', [
            'student_id' => $student->id,
            'balance' => 30000, // Saldo tetap utuh 30.000
        ]);

        // 5. Buku Mutasi Passbook View
        $show = $this->actingAs($this->bendahara)->get(route('savings.show', $student));
        $show->assertStatus(200);
        $show->assertSee('Buku Tabungan: Siswa Menabung');
        $show->assertSee('Setoran awal tabungan');

        // 6. Export Tabungan
        $export = $this->actingAs($this->bendahara)->get(route('savings.export'));
        $export->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', $export->headers->get('Content-Type'));
    }

    public function test_student_welfare_violations_permits_counseling(): void
    {
        $student = Student::where('school_id', $this->school->id)->first() ?? Student::create([
            'school_id' => $this->school->id,
            'full_name' => 'Siswa Disiplin',
            'status' => 'aktif',
        ]);

        // 1. Index
        $index = $this->actingAs($this->admin)->get(route('welfare.index'));
        $index->assertStatus(200);
        $index->assertSee('Catat Pelanggaran Siswa');

        // 2. Store Violation
        $vioStore = $this->actingAs($this->admin)->post(route('welfare.violations.store'), [
            'student_id' => $student->id,
            'category' => 'Keterlambatan Masuk',
            'points' => 5,
            'incident_date' => date('Y-m-d'),
            'description' => 'Terlambat 15 menit apel pagi',
        ]);
        $vioStore->assertRedirect();
        $this->assertDatabaseHas('violations', [
            'student_id' => $student->id,
            'category' => 'Keterlambatan Masuk',
            'points' => 5,
        ]);

        // 3. Store Permit
        $permitStore = $this->actingAs($this->admin)->post(route('welfare.permits.store'), [
            'student_id' => $student->id,
            'type' => 'keluar',
            'start_time' => date('Y-m-d H:i:s'),
            'status' => 'pending',
        ]);
        $permitStore->assertRedirect();
        $this->assertDatabaseHas('permits', [
            'student_id' => $student->id,
            'type' => 'keluar',
            'status' => 'pending',
        ]);

        $permit = \App\Models\Permit::where('student_id', $student->id)->latest()->first();

        // 4. Update Permit status to approved
        $permitApprove = $this->actingAs($this->admin)->put(route('welfare.permits.update-status', $permit), [
            'status' => 'approved',
        ]);
        $permitApprove->assertRedirect();
        $this->assertDatabaseHas('permits', [
            'id' => $permit->id,
            'status' => 'approved',
        ]);

        // 5. Store Counseling
        $counselingStore = $this->actingAs($this->admin)->post(route('welfare.counseling.store'), [
            'student_id' => $student->id,
            'type' => 'konseling',
            'session_date' => date('Y-m-d'),
            'notes' => 'Bimbingan kedisiplinan dan motivasi belajar',
        ]);
        $counselingStore->assertRedirect();
        $this->assertDatabaseHas('counselings', [
            'student_id' => $student->id,
            'type' => 'konseling',
        ]);

        // 6. Export Violations
        $export = $this->actingAs($this->admin)->get(route('welfare.export-violations'));
        $export->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', $export->headers->get('Content-Type'));
    }

    public function test_payroll_module_generate_and_slip(): void
    {
        $period = date('Y-m');

        $employee = Employee::firstOrCreate(
            ['school_id' => $this->school->id, 'nip' => '198501012010011005'],
            ['full_name' => 'Dra. Sri Wahyuni', 'status' => 'aktif']
        );

        // 1. Visit Payroll Index
        $index = $this->actingAs($this->admin)->get(route('payrolls.index', ['period' => $period]));
        $index->assertStatus(200);
        $index->assertSee('Penggajian GTK');

        // 2. Generate Payroll for period
        $gen = $this->actingAs($this->admin)->post(route('payrolls.generate'), [
            'period' => $period,
        ]);
        $gen->assertRedirect();
        $this->assertDatabaseHas('payrolls', [
            'employee_id' => $employee->id,
            'period' => $period,
        ]);

        $payroll = \App\Models\Payroll::where('employee_id', $employee->id)
            ->where('period', $period)
            ->first();

        // 3. Update Payroll amount & deductions
        $update = $this->actingAs($this->admin)->put(route('payrolls.update', $payroll), [
            'gross_amount' => 5000000,
            'deductions' => 200000,
        ]);
        $update->assertRedirect();
        $this->assertDatabaseHas('payrolls', [
            'id' => $payroll->id,
            'gross_amount' => 5000000,
            'deductions' => 200000,
            'net_amount' => 4800000,
        ]);

        // 4. View Slip Gaji
        $slip = $this->actingAs($this->admin)->get(route('payrolls.slip', $payroll));
        $slip->assertStatus(200);
        $slip->assertSee('SLIP GAJI PEGAWAI');
        $slip->assertSee('4.800.000');

        // 5. Export Payroll Excel
        $export = $this->actingAs($this->admin)->get(route('payrolls.export', ['period' => $period]));
        $export->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', $export->headers->get('Content-Type'));
    }

    public function test_payroll_disbursement_workflow(): void
    {
        $period = date('Y-m');

        $employee = Employee::firstOrCreate(
            ['school_id' => $this->school->id, 'nip' => '198701012015011009'],
            ['full_name' => 'Bambang Pamungkas, M.Pd.', 'status' => 'aktif']
        );

        $this->actingAs($this->admin)->post(route('payrolls.generate'), ['period' => $period]);

        $payroll = \App\Models\Payroll::where('employee_id', $employee->id)->where('period', $period)->first();
        $this->assertNotNull($payroll);

        // 1. Single Disburse
        $disburseRes = $this->actingAs($this->bendahara)->post(route('payrolls.disburse', $payroll), [
            'payment_method' => 'Transfer Bank BCA',
        ]);
        $disburseRes->assertRedirect();

        $payroll->refresh();
        $this->assertEquals('terbayar', $payroll->status);
        $this->assertEquals('Transfer Bank BCA', $payroll->payment_method);
        $this->assertNotNull($payroll->paid_at);

        // 2. Batch Disburse
        $batchRes = $this->actingAs($this->bendahara)->post(route('payrolls.disburse-all'), [
            'period' => $period,
            'payment_method' => 'Tunai Kasir',
        ]);
        $batchRes->assertRedirect();
    }

    public function test_batch_monthly_bill_generation(): void
    {
        $pt = \App\Models\PaymentType::firstOrCreate(
            ['school_id' => $this->school->id, 'name' => 'SPP Bulanan Terpadu'],
            ['default_amount' => 300000, 'recurrence' => 'bulanan']
        );

        $cls = SchoolClass::firstOrCreate(
            ['school_id' => $this->school->id, 'name' => 'XII RPL 1'],
            ['academic_year_id' => AcademicYear::firstOrCreate(['school_id' => $this->school->id, 'year_label' => '2026/2027'], ['is_active' => true])->id]
        );

        $student = Student::create([
            'school_id' => $this->school->id,
            'class_id'  => $cls->id,
            'full_name' => 'Batch Test Student',
            'status'    => 'aktif',
        ]);

        $res = $this->actingAs($this->bendahara)->post(route('bills.batch-generate'), [
            'payment_type_id' => $pt->id,
            'class_id'        => $cls->id,
            'amount'          => 300000,
            'start_month'     => '2026-07',
            'end_month'       => '2026-09',
            'due_day'         => 10,
        ]);
        $res->assertRedirect();

        // Must create 3 bills (Jul, Aug, Sep)
        $count = \App\Models\Bill::where('student_id', $student->id)
            ->where('payment_type_id', $pt->id)
            ->count();
        $this->assertEquals(3, $count);
    }

    public function test_financial_report_bku_ledger_and_export(): void
    {
        $period = date('Y-m');

        // 1. Visit Financial Report (BKU)
        $res = $this->actingAs($this->bendahara)->get(route('reports.financial', ['period' => $period]));
        $res->assertStatus(200);
        $res->assertSee('Buku Kas Umum (BKU)');
        $res->assertSee('Total Penerimaan (Debet)');

        // Kepsek can also view
        $kepsekRes = $this->actingAs($this->kepsek)->get(route('reports.financial', ['period' => $period]));
        $kepsekRes->assertStatus(200);

        // 2. Export Excel (.xlsx)
        $export = $this->actingAs($this->bendahara)->get(route('reports.financial.export', ['period' => $period]));
        $export->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', $export->headers->get('Content-Type'));

        // 3. Print View
        $print = $this->actingAs($this->bendahara)->get(route('reports.financial.print', ['period' => $period]));
        $print->assertStatus(200);
        $print->assertSee('BUKU KAS UMUM (BKU) SEKOLAH');
        $print->assertSee('Kepala Sekolah');
        $print->assertSee('Bendahara Sekolah');
    }
}


