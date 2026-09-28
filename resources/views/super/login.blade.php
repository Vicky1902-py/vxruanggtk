@extends('layouts.guest')
@section('title', 'Super Admin — Ruang GTK')

@section('content')
<div class="auth-deco" aria-hidden="true"><i></i><i></i></div>
<div class="center-screen">
  <div class="auth-card glass">
    <div class="auth-brand">
      <div class="mark god-mark-lg"><img src="{{ asset('img/logo.svg') }}" alt="Logo Ruang GTK"></div>
      <div style="margin-top:6px">
        <div class="brand-ruanggtk">
          <span class="brand-ruanggtk-text" style="font-size:22px">Ruang<span class="gtk-tag">GTK</span></span>
          <span class="brand-beam"></span>
        </div>
        <h2 style="font-size:16px;color:#0f172a;margin-top:6px;font-weight:700">Kontrol Global Super Admin</h2>
      </div>
    </div>

    <form method="POST" action="{{ route('super.attempt') }}" class="stack">
      @csrf
      <div class="field">
        <label for="username">Username Super Admin</label>
        <input id="username" name="username" type="text" class="input" value="{{ old('username') }}" placeholder="superadmin / god" required autofocus>
      </div>
      <div class="field">
        <label for="password">Kata Sandi</label>
        <input id="password" name="password" type="password" class="input" placeholder="••••••••" required>
      </div>
      @error('username') <div class="error-text">{{ $message }}</div> @enderror

      <button type="submit" class="btn btn-god" style="width:100%;height:46px;font-size:15px;margin-top:6px" data-loading="Memverifikasi...">
        Masuk Panel Global ⚡
      </button>
    </form>

    <div class="demo-hint" style="background:#fffbeb;border-color:#fde68a">
      <div style="font-weight:700;color:#b45309;margin-bottom:4px;font-size:12px">⚡ Akun Super Admin Default:</div>
      <div style="font-size:12px;color:#334155">
        Username: <code>god</code> · Password: <code>godmode123</code>
      </div>
    </div>

    <p style="margin-top:18px;text-align:center;font-size:13px">
      <a href="{{ route('landing') }}">← Kembali ke halaman utama</a>
    </p>
  </div>
</div>
@endsection
