@extends('layouts.god')
@section('title', 'Kontrol Global')

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <span class="god-mode-chip">⚡ GOD MODE</span>
      <span style="font-size:12px;color:var(--muted)">Pusat Kendali Seluruh Sekolah</span>
    </div>
    <h1>Kontrol Global Platform</h1>
    <div class="sub">Pengawasan seluruh tenant sekolah, metrik agregat, dan manajemen akses super admin.</div>
  </div>
</div>

<div class="stat-grid" style="margin-top:8px">
  <div class="glass stat">
    <div class="count-pill" style="background:var(--grad-sunset)">
      <strong class="count-up" data-count="{{ $stats['schools'] }}">0</strong>
      <small>Sekolah</small>
    </div>
    <div class="stat-label">{{ $stats['active_schools'] }} aktif berlangganan</div>
    <div class="stat-icon">
      <svg viewBox="0 0 24 24"><path d="M3 9l9-6 9 6"/><path d="M5 9v9a2 2 0 002 2h10a2 2 0 002-2V9"/></svg>
    </div>
  </div>

  <div class="glass stat">
    <div class="count-pill">
      <strong class="count-up" data-count="{{ $stats['users'] }}">0</strong>
      <small>Akun Pengguna</small>
    </div>
    <div class="stat-label">Total akun seluruh tenant</div>
    <div class="stat-icon">
      <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M3.5 20c.5-4 2.6-6 5.5-6s5 2 5.5 6"/><circle cx="17" cy="9" r="2.5"/></svg>
    </div>
  </div>

  <div class="glass stat">
    <div class="count-pill">
      <strong class="count-up" data-count="{{ $stats['students'] }}">0</strong>
      <small>Total Siswa</small>
    </div>
    <div class="stat-label">Seluruh siswa di platform</div>
    <div class="stat-icon">
      <svg viewBox="0 0 24 24"><path d="M22 9L12 4 2 9l10 5 10-5z"/><path d="M6 11.5V16c0 1.3 2.7 2.5 6 2.5s6-1.2 6-2.5v-4.5"/></svg>
    </div>
  </div>

  <div class="glass stat">
    <div class="count-pill">
      <strong>Rp <span class="count-up" data-count="{{ (int) $stats['revenue'] }}">0</span></strong>
      <small>Terkumpul</small>
    </div>
    <div class="stat-label">Tunggakan: Rp {{ number_format($stats['outstanding'], 0, ',', '.') }}</div>
    <div class="stat-icon">
      <svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18"/></svg>
    </div>
  </div>
</div>

<div class="glass panel" style="margin-top:20px">
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:14px">
    <h2 class="panel-title" style="margin:0">
      <svg viewBox="0 0 24 24"><path d="M3 9l9-6 9 6"/><path d="M5 9v9a2 2 0 002 2h10a2 2 0 002-2V9"/></svg>
      Daftar Sekolah Terdaftar (Multi-Tenant)
    </h2>
    <span class="vtx-pill" style="font-size:11px">{{ $schools->count() }} Tenant Aktif</span>
  </div>

  <div class="table-wrap">
    <table class="tbl">
      <thead>
        <tr>
          <th>Nama Sekolah</th>
          <th>Paket Langganan</th>
          <th>Akun Pengguna</th>
          <th>Status</th>
          <th style="text-align:right">Aksi Manajemen</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($schools as $school)
          <tr>
            <td>
              <b style="font-size:14.5px;color:var(--text)">{{ $school->name }}</b>
              <br><small style="color:var(--sky)">{{ '@' . $school->subdomain }}</small>
            </td>
            <td>
              <span class="badge badge-blue">{{ ucfirst($school->package_tier) }}</span>
            </td>
            <td>
              <span>{{ $school->users_count }} akun pengguna</span>
            </td>
            <td>
              <span class="badge {{ $school->is_active ? 'badge-ok' : 'badge-bad' }}">
                {{ $school->is_active ? '🟢 Aktif' : '🔴 Nonaktif' }}
              </span>
            </td>
            <td>
              <div class="actions">
                <form method="POST" action="{{ route('god.impersonate', $school) }}">
                  @csrf
                  <button class="btn btn-sm btn-god" title="Masuk langsung ke sekolah ini">⚡ GOD MODE</button>
                </form>
                <form method="POST" action="{{ route('god.schools.toggle', $school) }}">
                  @csrf
                  <button class="btn btn-sm">{{ $school->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                </form>
                <button class="btn btn-sm" data-dialog="#reset-{{ $school->id }}">Reset Pass</button>
                <form method="POST" action="{{ route('god.schools.destroy', $school) }}" data-confirm="HAPUS PERMANEN sekolah '{{ $school->name }}' beserta SELURUH data siswa, rombel, presensi, dan tagihannya?">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="empty">Belum ada sekolah yang terdaftar di platform.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="two-col" style="margin-top:20px">
  {{-- Form Daftarkan Sekolah Baru --}}
  <div class="glass panel">
    <h2 class="panel-title">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
      Daftarkan Sekolah Baru
    </h2>
    <form method="POST" action="{{ route('god.schools.store') }}" class="stack">
      @csrf
      <div class="field">
        <label>Nama Sekolah / Institusi *</label>
        <input name="name" class="input" placeholder="mis. SMP Negeri 1 Jakarta" required>
      </div>
      <div class="form-grid" style="grid-template-columns:1fr 1fr">
        <div class="field">
          <label>Subdomain Unik *</label>
          <input name="subdomain" class="input" placeholder="smpn1jkt" required>
        </div>
        <div class="field">
          <label>Paket Langganan *</label>
          <select name="package_tier" class="select">
            <option value="dasar">Dasar</option>
            <option value="menengah" selected>Menengah</option>
            <option value="atas">Atas (Enterprise)</option>
          </select>
        </div>
      </div>
      <div class="form-grid" style="grid-template-columns:1fr 1fr">
        <div class="field">
          <label>Username Admin *</label>
          <input name="admin_username" class="input" placeholder="admin" required>
        </div>
        <div class="field">
          <label>Password Admin *</label>
          <input name="admin_password" type="password" class="input" placeholder="min. 6 karakter" minlength="6" required>
        </div>
      </div>
      <button class="btn btn-god" data-loading="Membuat Tenant...">
        🏫 Buat Sekolah &amp; Inisialisasi Admin
      </button>
    </form>
  </div>

  {{-- Informasi God Mode --}}
  <div class="glass panel">
    <h2 class="panel-title">
      <svg viewBox="0 0 24 24"><path d="M4 11l12-6v14L4 13v-2z"/></svg>
      Panduan Keamanan God Mode
    </h2>
    <div class="stack">
      <div class="glass-soft ann-item">
        <h3 style="color:#fef08a">⚡ Akses Impersonate Sekolah</h3>
        <p>Tombol GOD MODE akan langsung mengautentikasi Anda sebagai administrator sekolah tersebut tanpa perlu mengetahui kata sandi asli.</p>
      </div>
      <div class="glass-soft ann-item">
        <h3 style="color:#67e8f9">🛡️ Isolasi Tenant Terjamin</h3>
        <p>Data super admin disimpan terpisah pada tabel khusus (<code>super_admins</code>), tidak pernah bercampur dengan database operasional sekolah.</p>
      </div>
      <div class="glass-soft ann-item">
        <h3 style="color:#fca5a5">🗑️ Penghapusan Kaskade</h3>
        <p>Menghapus sekolah akan menghapus otomatis seluruh relasi (siswa, rombel, keuangan, GTK) melalui foreign key cascade deletion.</p>
      </div>
    </div>
  </div>
</div>

@foreach ($schools as $school)
<dialog class="dlg" id="reset-{{ $school->id }}">
  <h3>Reset Password Admin — {{ $school->name }}</h3>
  <form method="POST" action="{{ route('god.schools.reset', $school) }}" class="stack">
    @csrf
    <div class="field">
      <label>Kata Sandi Baru *</label>
      <input name="password" type="password" class="input" minlength="6" placeholder="min. 6 karakter" required>
    </div>
    <div class="dlg-actions">
      <button type="button" class="btn" data-close>Batal</button>
      <button class="btn btn-god" data-loading="Mereset...">Reset Kata Sandi</button>
    </div>
  </form>
</dialog>
@endforeach
@endsection
