<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'subdomain' => ['required', 'string'],
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'required' => ':attribute wajib diisi.',
        ]);

        $user = User::query()
            ->whereHas('school', fn ($q) => $q->where('subdomain', $credentials['subdomain']))
            ->where('username', $credentials['username'])
            ->first();

        if (! $user || ! Auth::attempt(['id' => $user->id, 'password' => $credentials['password']], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'username' => 'Kombinasi sekolah, username, atau password salah.',
            ]);
        }

        if (! $user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'username' => 'Akun Anda tidak aktif. Hubungi admin sekolah.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
