<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AttendanceStudentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\GodController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PublicPageController;
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

    // Presensi: admin & guru
    Route::middleware('role:admin,guru')->group(function () {
        Route::get('presensi', [AttendanceStudentController::class, 'index'])->name('attendance.index');
        Route::post('presensi', [AttendanceStudentController::class, 'store'])->name('attendance.store');
    });

    // Manajemen data: admin & TU
    Route::middleware('role:admin,staff_tu')->group(function () {
        Route::resource('siswa', StudentController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['siswa' => 'student'])
            ->names('students');

        Route::resource('pegawai', EmployeeController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['pegawai' => 'employee'])
            ->names('employees');
    });

    // Modul penuh: admin
    Route::middleware('role:admin')->group(function () {
        Route::resource('kelas', SchoolClassController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['kelas' => 'class'])
            ->names('classes');

        Route::resource('tagihan', BillController::class)
            ->only(['index', 'store'])
            ->parameters(['tagihan' => 'bill'])
            ->names('bills');
        Route::post('tagihan/{bill}/bayar', [BillController::class, 'pay'])->name('bills.pay');

        Route::resource('pengumuman', AnnouncementController::class)
            ->only(['index', 'store', 'destroy'])
            ->parameters(['pengumuman' => 'announcement'])
            ->names('announcements');
    });
});
