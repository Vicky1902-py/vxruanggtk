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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Panel "GOD MODE" — hanya diakses guard super (tabel super_admins).
 * Bisa melihat & mengelola SEMUA sekolah (tenant), plus CMS situs publik.
 */
class GodController extends Controller
{
    // ── Dashboard global ─────────────────────────────────────
    public function dashboard()
    {
        $stats = [
            'schools' => School::count(),
            'active_schools' => School::where('is_active', true)->count(),
            'users' => User::count(),
            'students' => Student::count(),
            'revenue' => (float) Payment::sum('amount_paid'),
            'outstanding' => (float) Bill::where('status', '!=', 'lunas')->sum('amount'),
        ];

        $schools = School::withCount(['users', 'academicYears', 'students', 'employees'])
            ->with(['users.role'])
            ->latest()->get();

        return view('god.dashboard', compact('stats', 'schools'));
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

    // ── CMS: Landing & Pengaturan Situs ──────────────────────
    public function cms()
    {
        $settings = [
            'site_tagline' => SiteSetting::get('site_tagline', 'Sistem Informasi Manajemen Sekolah multi-tenant'),
            'site_hero_image' => SiteSetting::get('site_hero_image', 'img/hero.svg'),
            'site_footer_text' => SiteSetting::get('site_footer_text', 'Ruang GTK — Sistem Informasi Manajemen Sekolah'),
        ];

        return view('god.cms', compact('settings'));
    }

    public function saveCms(Request $request)
    {
        $data = $request->validate([
            'site_tagline' => ['required', 'string', 'max:300'],
            'site_hero_image' => ['required', 'string', 'max:200'],
            'site_footer_text' => ['required', 'string', 'max:200'],
        ]);

        foreach ($data as $key => $value) {
            SiteSetting::set($key, $value);
        }

        return back()->with('toast', 'Pengaturan situs tersimpan.');
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
