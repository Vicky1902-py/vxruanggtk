<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Page;
use App\Models\Payment;
use App\Models\Role;
use App\Models\School;
use App\Models\SiteSetting;
use App\Models\Student;
use App\Models\SuperAdmin;
use App\Models\User;
use App\Services\DatabaseBackupService;
use App\Services\ServerTelemetryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Panel "GOD MODE" — hanya diakses guard super (tabel super_admins).
 * Bisa melihat & mengelola SEMUA sekolah (tenant), plus CMS situs publik & telemetri server aaPanel.
 */
class GodController extends Controller
{
    // ── Dashboard global (aaPanel Telemetry Engine) ───────────
    public function dashboard(ServerTelemetryService $telemetryService)
    {
        $telemetry = $telemetryService->getFullTelemetry();

        // Data kompatibilitas untuk view lama & test
        $stats = [
            'schools'        => $telemetry['platform']['total_schools'],
            'active_schools' => $telemetry['platform']['active_schools'],
            'users'          => $telemetry['platform']['total_users'],
            'students'       => $telemetry['platform']['total_students'],
            'revenue'        => $telemetry['platform']['collected_amount'],
            'outstanding'    => $telemetry['platform']['outstanding_amount'],
        ];

        $schools = School::withCount(['users', 'academicYears', 'students', 'employees'])
            ->with(['users.role'])
            ->latest()->get();

        return view('god.dashboard', compact('telemetry', 'schools', 'stats'));
    }

    // ── API Telemetri Real-Time (AJAX Polling aaPanel) ───────
    public function telemetry(ServerTelemetryService $telemetryService)
    {
        return response()->json([
            'success'   => true,
            'timestamp' => now()->timestamp,
            'data'      => $telemetryService->getFullTelemetry(),
        ]);
    }

    // ── aaPanel Server Action: Clear All Caches ───────────────
    public function clearCache()
    {
        try {
            Artisan::call('optimize:clear');
            return back()->with('toast', '⚡ aaPanel Action: Seluruh cache framework, views, routes, dan config berhasil dibersihkan!');
        } catch (\Throwable $e) {
            return back()->with('toast', 'Gagal membersihkan cache: ' . $e->getMessage());
        }
    }

    // ── aaPanel Server Action: Rebuild Production Cache ──────
    public function rebuildCache()
    {
        try {
            if (app()->environment('testing')) {
                return back()->with('toast', '⚡ aaPanel Action: Kompilasi cache produksi (Config, Route, View) berhasil diperbarui!');
            }

            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            return back()->with('toast', '⚡ aaPanel Action: Kompilasi cache produksi (Config, Route, View) berhasil diperbarui!');
        } catch (\Throwable $e) {
            return back()->with('toast', 'Gagal kompilasi cache: ' . $e->getMessage());
        }
    }

    // ── CRUD Sekolah (tenant) ────────────────────────────────
    public function storeSchool(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'subdomain' => ['required', 'string', 'max:60', 'alpha_dash', 'unique:schools,subdomain'],
            'package_tier' => ['required', 'in:dasar,menengah,atas'],
            'admin_username' => ['required', 'string', 'max:60'],
            'admin_password' => ['required', 'string', 'min:6'],
        ]);

        DB::transaction(function () use ($data) {
            $school = School::create([
                'name' => $data['name'],
                'subdomain' => $data['subdomain'],
                'package_tier' => $data['package_tier'],
            ]);

            $adminRole = Role::where('name', 'admin')->firstOrFail();

            User::create([
                'school_id' => $school->id,
                'role_id' => $adminRole->id,
                'username' => $data['admin_username'],
                'password' => $data['admin_password'],
            ]);
        });

        return back()->with('toast', "Sekolah \"{$data['name']}\" + akun admin dibuat.");
    }

    public function toggleSchool(School $school)
    {
        $school->update(['is_active' => ! $school->is_active]);

        return back()->with('toast', 'Sekolah ' . ($school->is_active ? 'diaktifkan' : 'dinonaktifkan') . '.');
    }

    public function resetSchoolAdminPassword(Request $request, School $school)
    {
        $data = $request->validate(['password' => ['required', 'min:6']]);

        $admin = $school->users()->whereHas('role', fn ($q) => $q->where('name', 'admin'))->first();
        abort_if(! $admin, 404, 'Akun admin sekolah tidak ditemukan.');

        $admin->update(['password' => $data['password']]);

        return back()->with('toast', 'Password admin ' . $school->name . ' direset.');
    }

    public function updateSchool(Request $request, School $school)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:150'],
            'subdomain'    => ['required', 'string', 'max:60', 'alpha_dash', Rule::unique('schools')->ignore($school->id)],
            'package_tier' => ['required', 'in:dasar,menengah,atas'],
            'is_active'    => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $school->update($data);

        return back()->with('toast', "Data sekolah '{$school->name}' berhasil diperbarui.");
    }

    public function destroySchool(School $school)
    {
        $name = $school->name;
        $school->delete(); // FK cascade menghapus seluruh data tenant

        return back()->with('toast', "Sekolah \"{$name}\" dan seluruh datanya dihapus.");
    }

    // ── GOD MODE: Masuk sebagai admin sekolah manapun ────────
    public function impersonate(Request $request, School $school)
    {
        // Cari akun admin, atau buatkan otomatis jika sekolah belum punya admin
        $admin = $school->users()->whereHas('role', fn ($q) => $q->where('name', 'admin'))->first();

        if (! $admin) {
            $adminRole = Role::firstOrCreate(['name' => 'admin']);
            $admin = User::firstOrCreate(
                ['school_id' => $school->id, 'username' => 'admin_' . $school->subdomain],
                [
                    'role_id'   => $adminRole->id,
                    'password'  => Hash::make('AdminPass' . rand(1000, 9999)),
                    'is_active' => true,
                ]
            );
        }

        $superAdminId = Auth::guard('super')->id() ?? $request->session()->get('god_id');

        Auth::guard('web')->login($admin);
        $request->session()->regenerate();

        $request->session()->put('god_impersonating', true);
        $request->session()->put('god_school_id', $school->id);
        $request->session()->put('god_school_name', $school->name);
        $request->session()->put('god_school_subdomain', $school->subdomain);
        $request->session()->put('god_id', $superAdminId);

        return redirect()->route('dashboard')
            ->with('toast', '⚡ GOD MODE AKTIF: Anda memiliki akses penuh ke ' . $school->name . ' sebagai Administrator.');
    }

    // ── GOD MODE: Masuk sebagai pengguna spesifik manapun ──────
    public function impersonateUser(Request $request, User $user)
    {
        $school = $user->school;
        abort_if(! $school, 404, 'Sekolah pengguna tidak ditemukan.');

        $superAdminId = Auth::guard('super')->id() ?? $request->session()->get('god_id');

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        $request->session()->put('god_impersonating', true);
        $request->session()->put('god_school_id', $school->id);
        $request->session()->put('god_school_name', $school->name);
        $request->session()->put('god_school_subdomain', $school->subdomain);
        $request->session()->put('god_id', $superAdminId);

        return redirect()->route('dashboard')
            ->with('toast', "⚡ GOD MODE: Anda masuk sebagai {$user->username} ({$user->role?->name}) di {$school->name}.");
    }

    // ── GOD MODE: Pindah sekolah instan dari bar navigasi ────
    public function switchSchool(Request $request)
    {
        if (! $request->session()->get('god_impersonating') && ! Auth::guard('super')->check()) {
            abort(403, 'Akses God Mode ditolak.');
        }

        $request->validate(['school_id' => 'required|exists:schools,id']);
        $school = School::findOrFail($request->school_id);

        return $this->impersonate($request, $school);
    }

    public function exitGodMode(Request $request)
    {
        if ($request->session()->pull('god_impersonating')) {
            $godId = $request->session()->pull('god_id');

            Auth::guard('web')->logout();
            $request->session()->regenerate();

            $god = SuperAdmin::find($godId);
            if ($god) {
                Auth::guard('super')->login($god);
                $request->session()->regenerate();

                return redirect()->route('god.dashboard')->with('toast', 'Kembali ke panel super admin.');
            }

            return redirect()->route('super.login');
        }

        return redirect()->route('dashboard');
    }

    // ── CMS & Branding: Landing & Pengaturan Situs ───────────
    public function cms()
    {
        $settings = [
            'site_name'        => SiteSetting::get('site_name', 'Ruang GTK'),
            'site_headline'    => SiteSetting::get('site_headline', 'Satu ruang cerdas untuk Ruang GTK & institusi Anda.'),
            'site_tagline'     => SiteSetting::get('site_tagline', 'Sistem Informasi Manajemen Sekolah multi-tenant — kelola siswa, guru, presensi, tagihan, dan pengumuman dalam satu tampilan yang tenang dan modern.'),
            'site_hero_image'  => SiteSetting::get('site_hero_image', 'img/hero.svg'),
            'site_logo'        => SiteSetting::get('site_logo', 'img/logo.svg'),
            'site_favicon'     => SiteSetting::get('site_favicon', 'img/logo.svg'),
            'site_footer_text' => SiteSetting::get('site_footer_text', 'Ruang GTK — Sistem Informasi Manajemen Sekolah'),
            'contact_email'    => SiteSetting::get('contact_email', 'admin@ruanggtk.my.id'),
            'contact_phone'    => SiteSetting::get('contact_phone', '+62 812-3456-7890'),
            'contact_address'  => SiteSetting::get('contact_address', 'Indonesia'),
        ];

        return view('god.cms', compact('settings'));
    }

    public function saveCms(Request $request)
    {
        $data = $request->validate([
            'site_name'          => ['nullable', 'string', 'max:100'],
            'site_headline'      => ['nullable', 'string', 'max:255'],
            'site_tagline'       => ['required', 'string', 'max:500'],
            'site_hero_image'    => ['nullable', 'string', 'max:255'],
            'site_logo'          => ['nullable', 'string', 'max:255'],
            'site_favicon'       => ['nullable', 'string', 'max:255'],
            'site_footer_text'   => ['required', 'string', 'max:255'],
            'contact_email'      => ['nullable', 'email', 'max:100'],
            'contact_phone'      => ['nullable', 'string', 'max:50'],
            'contact_address'    => ['nullable', 'string', 'max:255'],

            'site_logo_file'     => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'site_favicon_file'  => ['nullable', 'mimes:ico,png,svg,jpg,jpeg,webp', 'max:1024'],
            'site_hero_file'     => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:4096'],
        ]);

        $uploadDir = public_path('uploads/branding');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }

        // Upload Logo Utama Landing
        if ($request->hasFile('site_logo_file')) {
            $file = $request->file('site_logo_file');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            SiteSetting::set('site_logo', 'uploads/branding/' . $filename);
        } elseif (!empty($data['site_logo'])) {
            SiteSetting::set('site_logo', $data['site_logo']);
        }

        // Upload Favicon
        if ($request->hasFile('site_favicon_file')) {
            $file = $request->file('site_favicon_file');
            $filename = 'favicon_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            SiteSetting::set('site_favicon', 'uploads/branding/' . $filename);
        } elseif (!empty($data['site_favicon'])) {
            SiteSetting::set('site_favicon', $data['site_favicon']);
        }

        // Upload Hero Image
        if ($request->hasFile('site_hero_file')) {
            $file = $request->file('site_hero_file');
            $filename = 'hero_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            SiteSetting::set('site_hero_image', 'uploads/branding/' . $filename);
        } elseif (!empty($data['site_hero_image'])) {
            SiteSetting::set('site_hero_image', $data['site_hero_image']);
        }

        // Simpan text settings lainnya
        $textKeys = [
            'site_name', 'site_headline', 'site_tagline', 'site_footer_text',
            'contact_email', 'contact_phone', 'contact_address',
        ];

        foreach ($textKeys as $key) {
            if (array_key_exists($key, $data)) {
                SiteSetting::set($key, $data[$key]);
            }
        }

        return back()->with('toast', 'Pengaturan branding & CMS situs berhasil disimpan.');
    }

    // ── SEO, Search Console & Google AdSense ──────────────────
    public function seo()
    {
        $settings = [
            'google_site_verification' => SiteSetting::get('google_site_verification', ''),
            'ga4_measurement_id'       => SiteSetting::get('ga4_measurement_id', ''),
            'seo_meta_title'           => SiteSetting::get('seo_meta_title', 'Ruang GTK — Sistem Informasi Manajemen Sekolah Modern'),
            'seo_meta_description'     => SiteSetting::get('seo_meta_description', 'Platform Sistem Informasi Manajemen Sekolah multi-tenant terpadu untuk siswa, GTK, absensi, keuangan SPP, persuratan dan administrasi akademik.'),
            'seo_meta_keywords'        => SiteSetting::get('seo_meta_keywords', 'ruang gtk, aplikasi sekolah, sim sekolah, spp sekolah, presensi siswa, sistem informasi manajemen sekolah, buku agenda persuratan'),
            'adsense_enabled'          => (bool) SiteSetting::get('adsense_enabled', '0'),
            'adsense_client_id'        => SiteSetting::get('adsense_client_id', ''),
            'adsense_auto_ads'         => (bool) SiteSetting::get('adsense_auto_ads', '1'),
            'adsense_banner_code'      => SiteSetting::get('adsense_banner_code', ''),
            'ads_txt'                  => SiteSetting::get('ads_txt', "google.com, pub-0000000000000000, DIRECT, f08c47fec0942fa0\n"),
            'custom_head_code'         => SiteSetting::get('custom_head_code', ''),
            'custom_footer_code'       => SiteSetting::get('custom_footer_code', ''),
        ];

        return view('god.seo', compact('settings'));
    }

    public function saveSeo(Request $request)
    {
        $data = $request->validate([
            'google_site_verification' => ['nullable', 'string', 'max:500'],
            'ga4_measurement_id'       => ['nullable', 'string', 'max:50'],
            'seo_meta_title'           => ['nullable', 'string', 'max:200'],
            'seo_meta_description'     => ['nullable', 'string', 'max:500'],
            'seo_meta_keywords'        => ['nullable', 'string', 'max:500'],
            'adsense_enabled'          => ['nullable', 'boolean'],
            'adsense_client_id'        => ['nullable', 'string', 'max:100'],
            'adsense_auto_ads'         => ['nullable', 'boolean'],
            'adsense_banner_code'      => ['nullable', 'string'],
            'ads_txt'                  => ['nullable', 'string'],
            'custom_head_code'         => ['nullable', 'string'],
            'custom_footer_code'       => ['nullable', 'string'],
        ]);

        $data['adsense_enabled'] = $request->boolean('adsense_enabled') ? '1' : '0';
        $data['adsense_auto_ads'] = $request->boolean('adsense_auto_ads') ? '1' : '0';

        foreach ($data as $key => $value) {
            SiteSetting::set($key, $value);
        }

        return back()->with('toast', 'Pengaturan SEO, Google Search Console, dan AdSense tersimpan.');
    }

    // ── Pemeliharaan Server, Database Backup, Cleanup & Live Traffic ──
    public function serverMaintenance(ServerTelemetryService $telemetryService)
    {
        $telemetry = $telemetryService->getFullTelemetry();
        $liveTraffic = $telemetryService->getLiveTrafficFeed();
        $dbDriver = DB::getDriverName();

        return view('god.server', compact('telemetry', 'liveTraffic', 'dbDriver'));
    }

    public function downloadBackup(DatabaseBackupService $backupService)
    {
        return $backupService->downloadBackup();
    }

    public function cleanDatabase(DatabaseBackupService $backupService)
    {
        $result = $backupService->cleanAndOptimize();

        return back()->with('toast', $result['summary']);
    }

    public function liveTraffic(ServerTelemetryService $telemetryService)
    {
        return response()->json([
            'success' => true,
            'data'    => $telemetryService->getLiveTrafficFeed(),
        ]);
    }

    // ── CMS: Halaman statis ──────────────────────────────────
    public function pages()
    {
        $pages = Page::orderBy('sort_order')->orderBy('title')->get();

        return view('god.pages', compact('pages'));
    }

    public function storePage(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', 'alpha_dash', 'unique:pages,slug'],
            'content' => ['required', 'string'],
            'status' => ['required', 'in:draft,published'],
            'show_in_footer' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        $data['show_in_footer'] = $request->boolean('show_in_footer');

        Page::create($data);

        return back()->with('toast', 'Halaman "' . $data['title'] . '" dibuat.');
    }

    public function updatePage(Request $request, Page $page)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:150', 'alpha_dash', 'unique:pages,slug,' . $page->id],
            'content' => ['required', 'string'],
            'status' => ['required', 'in:draft,published'],
            'show_in_footer' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['show_in_footer'] = $request->boolean('show_in_footer');
        $page->update($data);

        return back()->with('toast', 'Halaman diperbarui.');
    }

    public function destroyPage(Page $page)
    {
        $page->delete();

        return back()->with('toast', 'Halaman dihapus.');
    }

    // ── Kelola akun super admin ──────────────────────────────
    public function admins()
    {
        $admins = SuperAdmin::orderBy('username')->get();

        return view('god.admins', compact('admins'));
    }

    public function storeAdmin(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'alpha_dash', 'unique:super_admins,username'],
            'name' => ['required', 'string', 'max:120'],
            'password' => ['required', 'min:8'],
        ]);

        SuperAdmin::create($data);

        return back()->with('toast', 'Super admin "' . $data['username'] . '" dibuat.');
    }

    public function resetAdminPassword(Request $request, SuperAdmin $admin)
    {
        $data = $request->validate(['password' => ['required', 'min:8']]);
        $admin->update(['password' => $data['password']]);

        return back()->with('toast', 'Password super admin direset.');
    }

    public function destroyAdmin(SuperAdmin $admin)
    {
        abort_if($admin->id === Auth::guard('super')->id(), 403, 'Tidak bisa menghapus akun sendiri.');
        abort_if(SuperAdmin::count() <= 1, 403, 'Minimal harus tersisa satu super admin.');

        $admin->delete();

        return back()->with('toast', 'Super admin dihapus.');
    }
}
