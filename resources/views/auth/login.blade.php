@extends('layouts.guest')
@section('title', 'Masuk ke Ruang GTK')

@section('content')
<div class="auth-deco" aria-hidden="true"><i></i><i></i><i></i></div>
<div class="center-screen">
  <div class="auth-card glass">
    <div class="auth-brand">
      <div class="mark"><img src="{{ asset('img/logo.svg') }}" alt="Logo Ruang GTK"></div>
      <div style="margin-top:6px">
        <div class="brand-ruanggtk" style="font-size:24px">
          <span class="brand-ruanggtk-text" style="font-size:24px">Ruang<span class="gtk-tag">GTK</span></span>
          <span class="brand-beam"></span>
        </div>
        <p style="color:var(--muted);font-size:13px;margin-top:4px">Portal Masuk SIM Sekolah Multi-Tenant</p>
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
      <div style="font-weight:700;color:#0369a1;margin-bottom:8px;font-size:12px">⚡ Akses Cepat Akun Demo (Klik untuk Isi Langsung):</div>
      <div style="display:flex;gap:6px;flex-wrap:wrap">
        <button type="button" class="btn btn-sm" style="height:26px;font-size:11px;padding:0 9px" data-demo-subdomain="demoschool" data-demo-user="admin" data-demo-pass="password">
          👑 Admin
        </button>
        <button type="button" class="btn btn-sm" style="height:26px;font-size:11px;padding:0 9px" data-demo-subdomain="demoschool" data-demo-user="bendahara" data-demo-pass="password">
          💰 Bendahara
        </button>
        <button type="button" class="btn btn-sm" style="height:26px;font-size:11px;padding:0 9px" data-demo-subdomain="demoschool" data-demo-user="bsantoso" data-demo-pass="password">
          👨‍🏫 Guru (Budi)
        </button>
        <button type="button" class="btn btn-sm" style="height:26px;font-size:11px;padding:0 9px" data-demo-subdomain="demoschool" data-demo-user="tu.siti" data-demo-pass="password">
          📋 Staf TU
        </button>
        <button type="button" class="btn btn-sm" style="height:26px;font-size:11px;padding:0 9px" data-demo-subdomain="demoschool" data-demo-user="kepsek.hendra" data-demo-pass="password">
          🎓 Kepsek
        </button>
        <button type="button" class="btn btn-sm" style="height:26px;font-size:11px;padding:0 9px" data-demo-subdomain="demoschool" data-demo-user="wali.ahmad" data-demo-pass="password">
          👨‍👩‍👦 Wali Murid
        </button>
      </div>
    </div>

    <p style="margin-top:16px;text-align:center;font-size:12.5px;color:var(--muted)">
      Super Admin Platform? <a href="{{ route('super.login') }}" style="color:var(--accent)">Masuk Panel Global →</a>
    </p>

    <p style="margin-top:12px;text-align:center;font-size:13px">
      <a href="{{ route('landing') }}">← Kembali ke beranda</a>
    </p>
  </div>
</div>
@endsection
