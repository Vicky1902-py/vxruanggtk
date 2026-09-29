<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AttendanceEmployeeController;
use App\Http\Controllers\AttendanceStudentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\GodController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SavingsController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentWelfareController;
use App\Http\Controllers\SuperAuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ── Publik ──────────────────────────────────────────────────
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/halaman/{slug}', [PublicPageController::class, 'show'])->name('page.show');
Route::get('/ads.txt', function () {
    $content = \App\Models\SiteSetting::get('ads_txt', "google.com, pub-0000000000000000, DIRECT, f08c47fec0942fa0\n");
    return response($content, 200, ['Content-Type' => 'text/plain; charset=utf-8']);
})->name('ads.txt');
Route::get('/sitemap.xml', function () {
    $pages = \App\Models\Page::where('status', 'published')->get();
    $schools = \App\Models\School::where('is_active', true)->get();
    return response()->view('sitemap', compact('pages', 'schools'), 200, [
        'Content-Type' => 'application/xml; charset=utf-8',
    ]);
})->name('sitemap.xml');
Route::get('/super', [SuperAuthController::class, 'showLogin'])->name('super.login');
Route::post('/super', [SuperAuthController::class, 'login'])->name('super.attempt');
Route::post('/super/keluar', [SuperAuthController::class, 'logout'])->name('super.logout');
Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
Route::post('/masuk', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');

// ── Panel GOD MODE (super admin, guard terpisah) ────────────
Route::prefix('god')->name('god.')->middleware('auth.super')->group(function () {
    Route::get('/', [GodController::class, 'dashboard'])->name('dashboard');
    Route::get('telemetry', [GodController::class, 'telemetry'])->name('telemetry');
    Route::post('server/clear-cache', [GodController::class, 'clearCache'])->name('server.clear-cache');
    Route::post('server/rebuild-cache', [GodController::class, 'rebuildCache'])->name('server.rebuild-cache');

    // Pemeliharaan Server, Backup, Pembersihan DB & Live Traffic
    Route::get('server', [GodController::class, 'serverMaintenance'])->name('server');
    Route::get('server/backup', [GodController::class, 'downloadBackup'])->name('server.backup');
    Route::post('server/clean-database', [GodController::class, 'cleanDatabase'])->name('server.clean-database');
    Route::get('server/live-traffic', [GodController::class, 'liveTraffic'])->name('server.live-traffic');

    // SEO, Google Search Console & Google AdSense
    Route::get('seo', [GodController::class, 'seo'])->name('seo');
    Route::post('seo', [GodController::class, 'saveSeo'])->name('seo.save');

    // Kelola sekolah
    Route::post('sekolah', [GodController::class, 'storeSchool'])->name('schools.store');
    Route::put('sekolah/{school}', [GodController::class, 'updateSchool'])->name('schools.update');
    Route::post('sekolah/{school}/toggle', [GodController::class, 'toggleSchool'])->name('schools.toggle');
    Route::post('sekolah/{school}/reset-password', [GodController::class, 'resetSchoolAdminPassword'])->name('schools.reset');
    Route::delete('sekolah/{school}', [GodController::class, 'destroySchool'])->name('schools.destroy');
    Route::post('sekolah/{school}/impersonate', [GodController::class, 'impersonate'])->name('impersonate');
    Route::post('users/{user}/impersonate', [GodController::class, 'impersonateUser'])->name('impersonate.user');

    // CMS & Halaman
    Route::get('cms', [GodController::class, 'cms'])->name('cms');
    Route::post('cms', [GodController::class, 'saveCms'])->name('cms.save');
    Route::get('halaman', [GodController::class, 'pages'])->name('pages');
    Route::post('halaman', [GodController::class, 'storePage'])->name('pages.store');
    Route::put('halaman/{page}', [GodController::class, 'updatePage'])->name('pages.update');
    Route::delete('halaman/{page}', [GodController::class, 'destroyPage'])->name('pages.destroy');

    // Kelola super admin
    Route::get('admin', [GodController::class, 'admins'])->name('admins');
    Route::post('admin', [GodController::class, 'storeAdmin'])->name('admins.store');
    Route::post('admin/{admin}/reset-password', [GodController::class, 'resetAdminPassword'])->name('admins.reset');
    Route::delete('admin/{admin}', [GodController::class, 'destroyAdmin'])->name('admins.destroy');
});

Route::get('/god/keluar', [GodController::class, 'exitGodMode'])->name('god.exit')->middleware('web');
Route::post('/god/switch-school', [GodController::class, 'switchSchool'])->name('god.switch-school')->middleware('web');

// ── Terautentikasi (sekolah) ────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil Akun & Ganti Password (semua user)
    Route::get('/profil', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Pengumuman: dapat dibaca seluruh warga sekolah terdaftar
    Route::get('pengumuman', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::middleware('role:admin')->group(function () {
        Route::post('pengumuman', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::delete('pengumuman/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    });

    // Presensi Siswa: admin, guru, kepsek
    Route::middleware('role:admin,guru,kepsek')->group(function () {
        Route::get('presensi', [AttendanceStudentController::class, 'index'])->name('attendance.index');
        Route::post('presensi', [AttendanceStudentController::class, 'store'])->name('attendance.store');
        Route::get('presensi-export', [AttendanceStudentController::class, 'export'])->name('attendance.export');
    });

    // Presensi Pegawai (GTK): admin, staff_tu, kepsek, guru
    Route::middleware('role:admin,staff_tu,kepsek,guru')->group(function () {
        Route::get('presensi-pegawai', [AttendanceEmployeeController::class, 'index'])->name('attendance-employee.index');
        Route::post('presensi-pegawai/checkin', [AttendanceEmployeeController::class, 'selfCheckIn'])->name('attendance-employee.checkin');
        Route::post('presensi-pegawai', [AttendanceEmployeeController::class, 'store'])->name('attendance-employee.store');
        Route::get('presensi-pegawai-export', [AttendanceEmployeeController::class, 'export'])->name('attendance-employee.export');
    });

    // Manajemen Akademik & Data: admin, staff_tu, kepsek
    Route::middleware('role:admin,staff_tu,kepsek')->group(function () {
        // Jurusan / Program Keahlian
        Route::get('jurusan-template', [\App\Http\Controllers\MajorController::class, 'template'])->name('majors.template');
        Route::post('jurusan-import', [\App\Http\Controllers\MajorController::class, 'import'])->name('majors.import');
        Route::resource('jurusan', \App\Http\Controllers\MajorController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['jurusan' => 'major'])
            ->names('majors');

        // Peserta Didik (Siswa)
        Route::get('siswa-template', [StudentController::class, 'template'])->name('students.template');
        Route::get('siswa-export', [StudentController::class, 'export'])->name('students.export');
        Route::post('siswa-import', [StudentController::class, 'import'])->name('students.import');
        Route::post('siswa-naik-kelas', [StudentController::class, 'promote'])->name('students.promote');
        Route::post('siswa-kelulusan', [StudentController::class, 'graduate'])->name('students.graduate');
        Route::resource('siswa', StudentController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['siswa' => 'student'])
            ->names('students');

        // Tenaga Pendidik & Kependidikan (GTK)
        Route::get('pegawai-template', [EmployeeController::class, 'template'])->name('employees.template');
        Route::get('pegawai-export', [EmployeeController::class, 'export'])->name('employees.export');
        Route::post('pegawai-import', [EmployeeController::class, 'import'])->name('employees.import');
        Route::resource('pegawai', EmployeeController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['pegawai' => 'employee'])
            ->names('employees');

        // Rombongan Belajar (Kelas)
        Route::get('kelas-template', [SchoolClassController::class, 'template'])->name('classes.template');
        Route::post('kelas-import', [SchoolClassController::class, 'import'])->name('classes.import');
        Route::resource('kelas', SchoolClassController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['kelas' => 'class'])
            ->names('classes');

        Route::post('tahun-ajaran', [SchoolClassController::class, 'storeYear'])->name('academic-years.store');
        Route::post('tahun-ajaran/{year}/aktifkan', [SchoolClassController::class, 'activateYear'])->name('academic-years.activate');
    });

    // Keuangan & SPP: admin, bendahara, kepsek
    Route::middleware('role:admin,bendahara,kepsek')->group(function () {
        Route::get('tagihan-export', [BillController::class, 'export'])->name('bills.export');
        Route::post('tagihan/generate-bulanan', [BillController::class, 'batchGenerate'])->name('bills.batch-generate');
        Route::resource('tagihan', BillController::class)
            ->only(['index', 'store', 'destroy'])
            ->parameters(['tagihan' => 'bill'])
            ->names('bills');
        Route::post('tagihan/{bill}/bayar', [BillController::class, 'pay'])->name('bills.pay');

        // Tabungan Siswa
        Route::get('tabungan', [SavingsController::class, 'index'])->name('savings.index');
        Route::post('tabungan', [SavingsController::class, 'store'])->name('savings.store');
        Route::get('tabungan-export', [SavingsController::class, 'export'])->name('savings.export');
        Route::get('tabungan/{student}', [SavingsController::class, 'show'])->name('savings.show');

        // Penggajian GTK (Payroll)
        Route::get('penggajian', [PayrollController::class, 'index'])->name('payrolls.index');
        Route::post('penggajian/generate', [PayrollController::class, 'generate'])->name('payrolls.generate');
        Route::post('penggajian/disburse-all', [PayrollController::class, 'disburseAll'])->name('payrolls.disburse-all');
        Route::put('penggajian/{payroll}', [PayrollController::class, 'update'])->name('payrolls.update');
        Route::post('penggajian/{payroll}/disburse', [PayrollController::class, 'disburse'])->name('payrolls.disburse');
        Route::get('penggajian/{payroll}/slip', [PayrollController::class, 'slip'])->name('payrolls.slip');
        Route::get('penggajian-export', [PayrollController::class, 'export'])->name('payrolls.export');

        // Buku Kas Umum (BKU) & Laporan Keuangan Terpadu
        Route::get('laporan-keuangan', [\App\Http\Controllers\FinancialReportController::class, 'index'])->name('reports.financial');
        Route::get('laporan-keuangan/export', [\App\Http\Controllers\FinancialReportController::class, 'export'])->name('reports.financial.export');
        Route::get('laporan-keuangan/cetak', [\App\Http\Controllers\FinancialReportController::class, 'print'])->name('reports.financial.print');
    });

    // Kesiswaan, Kedisiplinan & BK
    Route::middleware('role:admin,guru,staff_tu,kepsek,bk')->group(function () {
        Route::get('kesiswaan', [StudentWelfareController::class, 'index'])->name('welfare.index');
        Route::post('kesiswaan/pelanggaran', [StudentWelfareController::class, 'storeViolation'])->name('welfare.violations.store');
        Route::delete('kesiswaan/pelanggaran/{violation}', [StudentWelfareController::class, 'destroyViolation'])->name('welfare.violations.destroy');
        Route::get('kesiswaan/pelanggaran-export', [StudentWelfareController::class, 'exportViolations'])->name('welfare.export-violations');
        Route::post('kesiswaan/izin', [StudentWelfareController::class, 'storePermit'])->name('welfare.permits.store');
        Route::put('kesiswaan/izin/{permit}', [StudentWelfareController::class, 'updatePermitStatus'])->name('welfare.permits.update-status');
        Route::post('kesiswaan/konseling', [StudentWelfareController::class, 'storeCounseling'])->name('welfare.counseling.store');
    });

    // Manajemen Pengguna Sekolah: khusus admin
    Route::middleware('role:admin')->group(function () {
        // Manajemen Pengguna Sekolah
        Route::get('pengguna', [UserController::class, 'index'])->name('users.index');
        Route::post('pengguna', [UserController::class, 'store'])->name('users.store');
        Route::put('pengguna/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('pengguna/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
        Route::post('pengguna/{user}/reset', [UserController::class, 'resetPassword'])->name('users.reset');
        Route::delete('pengguna/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Pengaturan Sekolah & Kop Surat
        Route::get('pengaturan-sekolah', [\App\Http\Controllers\SchoolSettingController::class, 'index'])->name('school.settings');
        Route::put('pengaturan-sekolah', [\App\Http\Controllers\SchoolSettingController::class, 'update'])->name('school.settings.update');
    });

    // Administrasi Persuratan & SK (Buku Agenda, Generator Nomor Otomatis, Pembuat Surat)
    Route::middleware('role:admin,staff_tu,kepsek')->group(function () {
        Route::get('persuratan/export', [\App\Http\Controllers\LetterController::class, 'export'])->name('letters.export');
        Route::get('persuratan/preview-number', [\App\Http\Controllers\LetterController::class, 'previewNumber'])->name('letters.preview-number');
        Route::get('persuratan/{letter}/cetak', [\App\Http\Controllers\LetterController::class, 'print'])->name('letters.print');
        Route::resource('persuratan', \App\Http\Controllers\LetterController::class)
            ->parameters(['persuratan' => 'letter'])
            ->names('letters');

        // Format & Master Jenis Surat
        Route::resource('jenis-surat', \App\Http\Controllers\LetterTypeController::class)
            ->parameters(['jenis-surat' => 'letterType'])
            ->names('letter-types')
            ->except(['create', 'show', 'edit']);
    });
});
