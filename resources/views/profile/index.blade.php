@extends('layouts.app')
@section('title', 'Profil & Keamanan Akun')

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <span class="cs-pill" style="font-size:11px;padding:3px 12px"><span class="dot"></span> Pengaturan Akun</span>
      <span style="font-size:12px;color:var(--muted)">Keamanan &amp; Informasi Pengguna</span>
    </div>
    <h1>Profil &amp; Keamanan Akun</h1>
    <div class="sub">Kelola informasi kredensial akun dan perbarui kata sandi Anda secara berkala.</div>
  </div>
</div>

<div class="two-col">
  {{-- Detail Akun Aktif --}}
  <div class="glass panel">
    <h2 class="panel-title">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="7.5" r="3.5"/><path d="M5 20.5c.7-4.5 3.2-7 7-7s6.3 2.5 7 7"/></svg>
      Informasi Akun
    </h2>

    <div style="display:flex;align-items:center;gap:16px;padding:16px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:var(--radius-md);margin-bottom:18px">
      <div style="width:54px;height:54px;border-radius:50%;background:linear-gradient(135deg,#0284c7,#2563eb);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:20px;color:#fff;box-shadow:0 4px 14px rgba(2,132,199,0.35)">
        {{ strtoupper(substr($user->username, 0, 2)) }}
      </div>
      <div>
        <h3 style="font-size:18px;color:var(--text);margin-bottom:4px">{{ $employee?->full_name ?? $guardian?->full_name ?? $user->username }}</h3>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
          <span class="badge badge-ink">{{ strtoupper($user->role?->name ?? 'User') }}</span>
          <span style="font-size:12px;color:var(--muted)">@ {{ $user->username }}</span>
          <span class="badge badge-ok">🟢 Aktif</span>
        </div>
      </div>
    </div>

    <div class="stack" style="gap:12px">
      <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--line-light);font-size:13.5px">
        <span style="color:var(--muted)">Sekolah (Tenant)</span>
        <b style="color:var(--text)">{{ $school->name }}</b>
      </div>
      <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--line-light);font-size:13.5px">
        <span style="color:var(--muted)">Subdomain</span>
        <span style="color:var(--accent)">{{ $school->subdomain }}.ruanggtk.my.id</span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--line-light);font-size:13.5px">
        <span style="color:var(--muted)">Email Terdaftar</span>
        <span style="color:var(--text)">{{ $user->email ?? '—' }}</span>
      </div>
      @if ($employee)
      <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--line-light);font-size:13.5px">
        <span style="color:var(--muted)">NIP / NUPTK</span>
        <span style="color:var(--text)">{{ $employee->nip ?? '—' }}</span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--line-light);font-size:13.5px">
        <span style="color:var(--muted)">Jabatan</span>
        <span class="badge badge-blue">{{ $employee->position?->name ?? 'Staf Umum' }}</span>
      </div>
      @endif
      @if ($guardian)
      <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--line-light);font-size:13.5px">
        <span style="color:var(--muted)">Hubungan Keluarga</span>
        <span class="badge badge-blue">{{ $guardian->relation_type }}</span>
      </div>
      <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--line-light);font-size:13.5px">
        <span style="color:var(--muted)">WhatsApp</span>
        <span style="color:var(--text)">{{ $guardian->phone_whatsapp ?? '—' }}</span>
      </div>
      @endif
      <div style="display:flex;justify-content:space-between;padding:10px 0;font-size:13.5px">
        <span style="color:var(--muted)">Akun Dibuat</span>
        <span style="color:var(--text)">{{ $user->created_at?->translatedFormat('d F Y') ?? '—' }}</span>
      </div>
    </div>
  </div>

  {{-- Form Ganti Password --}}
  <div class="glass panel">
    <h2 class="panel-title">
      <svg viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
      Perbarui Kata Sandi
    </h2>

    <form method="POST" action="{{ route('profile.password') }}" class="stack">
      @csrf
      @method('PUT')

      <div class="field">
        <label for="current_password">Kata Sandi Saat Ini *</label>
        <input id="current_password" name="current_password" type="password" class="input" placeholder="Masukkan kata sandi lama" required>
        @error('current_password') <span class="error-text">{{ $message }}</span> @enderror
      </div>

      <div class="field">
        <label for="new_password">Kata Sandi Baru * (Minimal 6 karakter)</label>
        <input id="new_password" name="new_password" type="password" class="input" placeholder="Masukkan kata sandi baru" required minlength="6">
        @error('new_password') <span class="error-text">{{ $message }}</span> @enderror
      </div>

      <div class="field">
        <label for="new_password_confirmation">Konfirmasi Kata Sandi Baru *</label>
        <input id="new_password_confirmation" name="new_password_confirmation" type="password" class="input" placeholder="Ulangi kata sandi baru" required minlength="6">
      </div>

      <button class="btn btn-ink" data-loading="Menyimpan Kata Sandi..." style="margin-top:6px">
        🔒 Simpan Kata Sandi Baru
      </button>
    </form>
  </div>
</div>
@endsection
