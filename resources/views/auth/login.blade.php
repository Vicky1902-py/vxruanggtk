@extends('layouts.guest')
@section('title', 'Masuk')

@section('content')
<div class="auth-deco" aria-hidden="true"><i></i><i></i></div>
<div class="center-screen">
  <div class="auth-card glass">
    <div class="auth-brand">
      <div class="mark"><img src="{{ asset('img/logo.svg') }}" alt="Logo Ruang GTK"></div>
      <h1>Ruang GTK</h1>
      <p>Sistem Informasi Manajemen Sekolah</p>
    </div>

    <form method="POST" action="{{ route('login.attempt') }}" class="stack">
      @csrf
      <div class="field">
        <label for="subdomain">Subdomain Sekolah</label>
        <input id="subdomain" name="subdomain" type="text" class="input" value="{{ old('subdomain') }}" placeholder="namasekolah" required autofocus>
      </div>
      <div class="field">
        <label for="username">Username</label>
        <input id="username" name="username" type="text" class="input" value="{{ old('username') }}" placeholder="username Anda" required>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" class="input" required>
      </div>
      @error('username') <div class="error-text">{{ $message }}</div> @enderror

      <button type="submit" class="btn btn-ink" style="width:100%" data-loading="Masuk...">Masuk ke Ruang GTK</button>
    </form>

    <div class="demo-hint">
      <b>Akun demo sekolah:</b><br>
      Subdomain <code>demoschool</code> · Admin <code>admin</code> / <code>password</code><br>
      Guru <code>bsantoso</code> / <code>password</code> · Wali <code>wali.ahmad</code> / <code>password</code>
    </div>
    <p style="margin-top:10px;text-align:center;font-size:12px;color:var(--muted)">
      Super admin platform? <a href="{{ route('super.login') }}">Masuk lewat sini →</a>
    </p>

    <p style="margin-top:16px;text-align:center;font-size:13px">
      <a href="{{ route('landing') }}">← Kembali ke halaman utama</a>
    </p>
  </div>
</div>
@endsection
