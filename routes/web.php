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
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SuperAuthController;
use Illuminate\Support\Facades\Route;

// ── Publik ──────────────────────────────────────────────────
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/halaman/{slug}', [PublicPageController::class, 'show'])->name('page.show');
Route::get('/super', [SuperAuthController::class, 'showLogin'])->name('super.login');
Route::post('/super', [SuperAuthController::class, 'login'])->name('super.attempt');
Route::post('/super/keluar', [SuperAuthController::class, 'logout'])->name('super.logout');
Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login');
Route::post('/masuk', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');

// ── Panel GOD MODE (super admin, guard terpisah) ────────────
Route::prefix('god')->name('god.')->middleware('auth.super')->group(function () {
    Route::get('/', [GodController::class, 'dashboard'])->name('dashboard');

    // Kelola sekolah
    Route::post('sekolah', [GodController::class, 'storeSchool'])->name('schools.store');
    Route::post('sekolah/{school}/toggle', [GodController::class, 'toggleSchool'])->name('schools.toggle');
    Route::post('sekolah/{school}/reset-password', [GodController::class, 'resetSchoolAdminPassword'])->name('schools.reset');
    Route::delete('sekolah/{school}', [GodController::class, 'destroySchool'])->name('schools.destroy');
    Route::post('sekolah/{school}/impersonate', [GodController::class, 'impersonate'])->name('impersonate');

    // CMS
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
    });

    // Presensi Pegawai (GTK): admin, staff_tu, kepsek, guru
    Route::middleware('role:admin,staff_tu,kepsek,guru')->group(function () {
        Route::get('presensi-pegawai', [AttendanceEmployeeController::class, 'index'])->name('attendance-employee.index');
        Route::post('presensi-pegawai/checkin', [AttendanceEmployeeController::class, 'selfCheckIn'])->name('attendance-employee.checkin');
        Route::post('presensi-pegawai', [AttendanceEmployeeController::class, 'store'])->name('attendance-employee.store');
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
        Route::post('siswa-import', [StudentController::class, 'import'])->name('students.import');
        Route::resource('siswa', StudentController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['siswa' => 'student'])
            ->names('students');

        // Tenaga Pendidik & Kependidikan (GTK)
        Route::get('pegawai-template', [EmployeeController::class, 'template'])->name('employees.template');
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

    // Keuangan & SPP: admin & bendahara
    Route::middleware('role:admin,bendahara')->group(function () {
        Route::resource('tagihan', BillController::class)
            ->only(['index', 'store', 'destroy'])
            ->parameters(['tagihan' => 'bill'])
            ->names('bills');
        Route::post('tagihan/{bill}/bayar', [BillController::class, 'pay'])->name('bills.pay');
    });
});
