@extends('layouts.guest')
@section('title', 'Masuk ke Ruang GTK')

@section('content')
<div class="auth-deco" aria-hidden="true"><i></i><i></i><i></i></div>
<div class="center-screen">
  <div class="auth-card glass">
    <div class="auth-brand">
      <div class="mark"><img src="{{ asset('img/logo.svg') }}" alt="Logo Ruang GTK"></div>
      <div>
        <h1 style="font-size:22px;font-weight:700">Ruang GTK</h1>
        <p style="color:var(--muted);font-size:13.5px;margin-top:2px">Sistem Informasi Manajemen Sekolah Multi-Tenant</p>
      </div>
    </div>

    <form method="POST" action="{{ route('login.attempt') }}" class="stack">
      @csrf
      <div class="field">
        <label for="subdomain">Subdomain Sekolah</label>
        <input id="subdomain" name="subdomain" type="text" class="input" value="{{ old('subdomain') }}" placeholder="mis. demoschool" required autofocus>
      </div>
      <div class="field">
        <label for="username">Username</label>
        <input id="username" name="username" type="text" class="input" value="{{ old('username') }}" placeholder="Username akun Anda" required>
      </div>
      <div class="field">
        <label for="password">Kata Sandi</label>
        <input id="password" name="password" type="password" class="input" placeholder="••••••••" required>
      </div>
      @error('username') <div class="error-text">{{ $message }}</div> @enderror

      <button type="submit" class="btn btn-ink" style="width:100%;height:46px;font-size:15px;margin-top:6px" data-loading="Masuk ke Akun...">
        Masuk ke Ruang GTK
      </button>
    </form>

    {{-- Quick Demo Credential Autofill --}}
    <div class="demo-hint">
      <div style="font-weight:600;color:#fff;margin-bottom:6px;font-size:12.5px">⚡ Akses Cepat Akun Demo (Klik untuk Isi):</div>
      <div style="display:flex;gap:6px;flex-wrap:wrap">
        <button type="button" class="btn btn-sm" style="height:26px;font-size:11.5px;padding:0 10px" data-demo-subdomain="demoschool" data-demo-user="admin" data-demo-pass="password">
          Admin Sekolah
        </button>
        <button type="button" class="btn btn-sm" style="height:26px;font-size:11.5px;padding:0 10px" data-demo-subdomain="demoschool" data-demo-user="bsantoso" data-demo-pass="password">
          Guru (Budi S.)
        </button>
        <button type="button" class="btn btn-sm" style="height:26px;font-size:11.5px;padding:0 10px" data-demo-subdomain="demoschool" data-demo-user="wali.ahmad" data-demo-pass="password">
          Wali Murid
        </button>
      </div>
    </div>

    <p style="margin-top:16px;text-align:center;font-size:12.5px;color:var(--muted)">
      Super Admin Platform? <a href="{{ route('super.login') }}" style="color:var(--amber)">Masuk Panel Global →</a>
    </p>

    <p style="margin-top:12px;text-align:center;font-size:13px">
      <a href="{{ route('landing') }}">← Kembali ke beranda</a>
    </p>
  </div>
</div>
@endsection
