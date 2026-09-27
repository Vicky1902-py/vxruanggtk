@extends('layouts.guest')
@section('title', 'Super Admin — Ruang GTK')

@section('content')
<div class="auth-deco" aria-hidden="true"><i></i><i></i></div>
<div class="center-screen">
  <div class="auth-card glass">
    <div class="auth-brand">
      <div class="mark god-mark-lg"><img src="{{ asset('img/logo.svg') }}" alt="Logo Ruang GTK"></div>
      <h1 style="font-size:22px;font-weight:700">Kontrol Global</h1>
      <p style="color:var(--muted);font-size:13.5px;margin-top:2px">Super Admin Platform — Ruang GTK</p>
    </div>

    <form method="POST" action="{{ route('super.attempt') }}" class="stack">
      @csrf
      <div class="field">
        <label for="username">Username Super Admin</label>
        <input id="username" name="username" type="text" class="input" value="{{ old('username') }}" placeholder="superadmin" required autofocus>
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

    <div class="demo-hint" style="background:rgba(245,158,11,0.08);border-color:rgba(245,158,11,0.25)">
      <div style="font-weight:600;color:#fef08a;margin-bottom:4px;font-size:12px">Akun Super Admin Default:</div>
      <div style="font-size:12px;color:var(--muted-2)">
        Username: <code>superadmin</code> · Password: <code>password</code>
      </div>
    </div>

    <p style="margin-top:18px;text-align:center;font-size:13px">
      <a href="{{ route('landing') }}">← Kembali ke halaman utama</a>
    </p>
  </div>
</div>
@endsection
