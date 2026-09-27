<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SuperAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('super')->check()) {
            return redirect()->route('god.dashboard');
        }

        return view('super.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('super')->attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'username' => 'Kredensial super admin salah.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route('god.dashboard')
            ->with('toast', 'Selamat datang, ' . Auth::guard('super')->user()->name);
    }

    public function logout(Request $request)
    {
        Auth::guard('super')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('super.login');
    }
}
