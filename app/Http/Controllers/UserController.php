<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $query = User::where('school_id', $schoolId)
            ->with(['role', 'employee'])
            ->orderBy('username');

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->integer('role_id'));
        }

        if ($request->filled('status')) {
            $isActive = $request->input('status') === 'aktif';
            $query->where('is_active', $isActive);
        }

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($w) use ($q) {
                $w->where('username', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhereHas('employee', fn ($e) => $e->where('full_name', 'like', "%{$q}%"));
            });
        }

        $users = $query->get();
        $roles = Role::whereIn('name', ['admin', 'guru', 'bendahara', 'staff_tu', 'kepsek', 'wali', 'bk'])->get();
        $unlinkedEmployees = Employee::where('school_id', $schoolId)
            ->whereNull('user_id')
            ->orderBy('full_name')
            ->get();

        return view('users.index', compact('users', 'roles', 'unlinkedEmployees'));
    }

    public function store(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $data = $request->validate([
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users')->where('school_id', $schoolId)],
            'email' => ['nullable', 'email', 'max:120', Rule::unique('users')->where('school_id', $schoolId)],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'string', 'min:6'],
            'employee_id' => ['nullable', 'exists:employees,id'],
        ], [
            'username.unique' => 'Username ini sudah digunakan di sekolah Anda.',
            'username.alpha_dash' => 'Username hanya boleh huruf, angka, tanda strip, atau garis bawah.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        $user = User::create([
            'school_id' => $schoolId,
            'role_id' => $data['role_id'],
            'username' => strtolower($data['username']),
            'email' => $data['email'] ?? null,
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);

        if (!empty($data['employee_id'])) {
            Employee::where('id', $data['employee_id'])
                ->where('school_id', $schoolId)
                ->update(['user_id' => $user->id]);
        }

        return back()->with('toast', 'Pengguna akun "' . $user->username . '" berhasil didaftarkan.');
    }

    public function update(Request $request, User $user)
    {
        $schoolId = auth()->user()->school_id;
        abort_unless($user->school_id === $schoolId, 403);

        $data = $request->validate([
            'email' => ['nullable', 'email', 'max:120', Rule::unique('users')->where('school_id', $schoolId)->ignore($user->id)],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['nullable'],
            'employee_id' => ['nullable', 'exists:employees,id'],
        ]);

        $updateData = [
            'email' => $data['email'] ?? null,
            'role_id' => $data['role_id'],
            'is_active' => $request->boolean('is_active'),
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        // Update relasi pegawai
        if (isset($data['employee_id'])) {
            // Lepaskan relasi lama
            Employee::where('user_id', $user->id)->update(['user_id' => null]);
            if (!empty($data['employee_id'])) {
                Employee::where('id', $data['employee_id'])
                    ->where('school_id', $schoolId)
                    ->update(['user_id' => $user->id]);
            }
        }

        return back()->with('toast', 'Data akun pengguna "' . $user->username . '" berhasil diperbarui.');
    }

    public function toggle(User $user)
    {
        $schoolId = auth()->user()->school_id;
        abort_unless($user->school_id === $schoolId, 403);

        if ($user->id === auth()->id()) {
            return back()->with('toast', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('toast', "Akun {$user->username} berhasil {$statusText}.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $schoolId = auth()->user()->school_id;
        abort_unless($user->school_id === $schoolId, 403);

        $newPass = $request->input('new_password') ?: 'password123';
        $user->update(['password' => Hash::make($newPass)]);

        return back()->with('toast', "Kata sandi untuk {$user->username} berhasil di-reset menjadi: {$newPass}");
    }

    public function destroy(User $user)
    {
        $schoolId = auth()->user()->school_id;
        abort_unless($user->school_id === $schoolId, 403);

        if ($user->id === auth()->id()) {
            return back()->with('toast', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Lepaskan referensi pegawai
        Employee::where('user_id', $user->id)->update(['user_id' => null]);

        $username = $user->username;
        $user->delete();

        return back()->with('toast', "Akun pengguna {$username} berhasil dihapus.");
    }
}
