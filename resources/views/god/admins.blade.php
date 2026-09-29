@extends('layouts.god')
@section('title', 'Akun Super Admin')

@section('content')
<div class="god-hero-strip" style="margin-bottom:20px">
  <div>
    <h1 class="god-greeting-title">Manajemen Akun Super Admin</h1>
    <div class="god-greeting-sub">
      <span>Akun super admin memiliki hak akses mutlak (God Mode) di seluruh platform dan terisolasi dari database sekolah.</span>
    </div>
  </div>
  <div>
    <button type="button" class="god-btn-primary-neo" data-dialog="#dialog-create-admin">
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
      + Tambah Super Admin
    </button>
  </div>
</div>

<div class="god-recent-card-neo">
  <div class="god-recent-head">
    <div>
      <h2 class="god-recent-title">Daftar Akun Supreme Controller</h2>
      <small style="color:#94a3b8;font-size:12.5px">Setiap akun super admin dapat memantau telemetri, mengatur SEO/AdSense, dan masuk ke sekolah manapun.</small>
    </div>
    <span class="god-badge-pill">{{ $admins->count() }} Super Admin</span>
  </div>

  <div class="god-recent-list-wrap">
    @foreach ($admins as $admin)
      <div class="god-recent-row-item">
        <div class="god-row-avatar" style="background:#ede9fe;color:#7c3aed">
          ⚡
        </div>

        <div class="god-row-info">
          <div class="god-row-name-col" style="min-width:180px;max-width:240px">
            <strong>{{ $admin->name }}</strong>
            <small style="color:#7c3aed">{{ '@' . $admin->username }}</small>
          </div>

          <div class="god-row-detail-col">
            <span class="badge {{ $admin->is_active ? 'badge-ok' : 'badge-bad' }}">
              {{ $admin->is_active ? '🟢 Aktif' : '🔴 Nonaktif' }}
            </span>
            @if ($admin->id === auth('super')->id())
              <span class="badge badge-blue" style="margin-left:6px">Sesi Anda Saat Ini</span>
            @endif
          </div>
        </div>

        <div style="display:flex;align-items:center;gap:6px;flex:none">
          <button type="button" class="btn btn-sm" data-dialog="#rp-{{ $admin->id }}">Reset PW</button>
          @if ($admin->id !== auth('super')->id())
            <form method="POST" action="{{ route('god.admins.destroy', $admin) }}" data-confirm="Hapus super admin {{ $admin->username }}?" style="margin:0">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-danger">Hapus</button>
            </form>
          @endif
        </div>
      </div>
    @endforeach
  </div>
</div>

{{-- MODAL TAMBAH SUPER ADMIN --}}
<dialog id="dialog-create-admin" class="dlg" style="max-width:520px;width:95%">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
      <h3 style="margin:0;font-size:17px;color:#0f172a;display:flex;align-items:center;gap:8px">
        <svg viewBox="0 0 24 24" width="20" height="20" stroke="#7c3aed" fill="none" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
        Tambah Super Admin Baru
      </h3>
      <button type="button" data-close class="modal-close" aria-label="Tutup">✕</button>
    </div>
    <form method="POST" action="{{ route('god.admins.store') }}" class="stack">
      @csrf
      <div class="field"><label>Username Akun *</label><input name="username" class="input" placeholder="god_master" required></div>
      <div class="field"><label>Nama Lengkap *</label><input name="name" class="input" placeholder="Super Administrator" required></div>
      <div class="field"><label>Kata Sandi * (min. 8 karakter)</label><input name="password" type="password" class="input" minlength="8" required></div>
      <div class="dlg-actions" style="margin-top:16px">
        <button type="button" class="btn" data-close>Batal</button>
        <button class="god-btn-primary-neo">Buat Akun Super</button>
      </div>
    </form>
  </div>
</dialog>

@foreach ($admins as $admin)
<dialog class="dlg" id="rp-{{ $admin->id }}" style="max-width:440px;width:95%">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
      <h3 style="margin:0;font-size:16px">Reset Password — {{ $admin->username }}</h3>
      <button type="button" class="modal-close" data-close>✕</button>
    </div>
    <form method="POST" action="{{ route('god.admins.reset', $admin) }}" class="stack">
      @csrf
      <div class="field"><label>Password Baru * (min. 8 karakter)</label><input name="password" type="password" class="input" minlength="8" required></div>
      <div class="dlg-actions">
        <button type="button" class="btn" data-close>Batal</button>
        <button class="god-btn-primary-neo">Reset Password</button>
      </div>
    </form>
  </div>
</dialog>
@endforeach
@endsection
