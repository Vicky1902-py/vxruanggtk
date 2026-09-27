@extends('layouts.god')
@section('title', 'Kontrol Global')

@section('content')
<div class="page-head">
  <div>
    <h1>Kontrol Global <span class="god-mode-chip">⚡ GOD MODE</span></h1>
    <div class="sub">Anda mengawasi seluruh sekolah (tenant) di platform.</div>
  </div>
</div>

<div class="stat-grid">
  <div class="glass stat">
    <div class="count-pill"><strong class="count-up" data-count="{{ $stats['schools'] }}">0</strong><small>Sekolah</small></div>
    <div class="stat-label">{{ $stats['active_schools'] }} aktif berlangganan</div>
    <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M3 9l9-6 9 6"/><path d="M5 9v9a2 2 0 002 2h10a2 2 0 002-2V9"/></svg></div>
  </div>
  <div class="glass stat">
    <div class="count-pill"><strong class="count-up" data-count="{{ $stats['users'] }}">0</strong><small>Pengguna</small></div>
    <div class="stat-label">Total akun semua sekolah</div>
    <div class="stat-icon"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M3.5 20c.5-4 2.6-6 5.5-6s5 2 5.5 6"/><circle cx="17" cy="9" r="2.5"/></svg></div>
  </div>
  <div class="glass stat">
    <div class="count-pill"><strong class="count-up" data-count="{{ $stats['students'] }}">0</strong><small>Siswa</small></div>
    <div class="stat-label">Seluruh siswa platform</div>
    <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M22 9L12 4 2 9l10 5 10-5z"/><path d="M6 11.5V16c0 1.3 2.7 2.5 6 2.5s6-1.2 6-2.5v-4.5"/></svg></div>
  </div>
  <div class="glass stat">
    <div class="count-pill"><strong>Rp <span class="count-up" data-count="{{ (int) $stats['revenue'] }}">0</span></strong><small>Pendapatan</small></div>
    <div class="stat-label">Tunggakan platform: Rp {{ number_format($stats['outstanding'], 0, ',', '.') }}</div>
    <div class="stat-icon"><svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18"/></svg></div>
  </div>
</div>

<div class="glass panel" style="margin-top:18px">
  <h2 class="panel-title"><svg viewBox="0 0 24 24"><path d="M3 9l9-6 9 6"/><path d="M5 9v9a2 2 0 002 2h10a2 2 0 002-2V9"/></svg> Daftar Sekolah (Tenant)</h2>

  <div class="table-wrap">
    <table class="tbl">
      <thead><tr><th>Sekolah</th><th>Paket</th><th>Pengguna</th><th>Status</th><th class="actions">Aksi</th></tr></thead>
      <tbody>
        @forelse ($schools as $school)
          <tr>
            <td><b>{{ $school->name }}</b><br><small style="color:var(--muted)">{{ '@' . $school->subdomain }}</small></td>
            <td><span class="badge badge-blue">{{ ucfirst($school->package_tier) }}</span></td>
            <td>{{ $school->users_count }} akun</td>
            <td><span class="badge {{ $school->is_active ? 'badge-ok' : 'badge-bad' }}">{{ $school->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
            <td>
              <div class="actions">
                <form method="POST" action="{{ route('god.impersonate', $school) }}">
                  @csrf
                  <button class="btn btn-sm btn-god" title="Masuk sebagai admin sekolah ini">⚡ GOD MODE</button>
                </form>
                <form method="POST" action="{{ route('god.schools.toggle', $school) }}">
                  @csrf
                  <button class="btn btn-sm">{{ $school->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                </form>
                <button class="btn btn-sm" data-dialog="#reset-{{ $school->id }}">Reset Password</button>
                <form method="POST" action="{{ route('god.schools.destroy', $school) }}" data-confirm="HAPUS PERMANEN {{ $school->name }} beserta SELURUH data siswa, tagihan, dan presensinya?">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="empty">Belum ada sekolah terdaftar.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="two-col" style="margin-top:18px">
  <div class="glass panel">
    <h2 class="panel-title"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg> Daftar Sekolah Baru</h2>
    <form method="POST" action="{{ route('god.schools.store') }}" class="stack">
      @csrf
      <div class="field"><label>Nama Sekolah *</label><input name="name" class="input" required></div>
      <div class="form-grid" style="grid-template-columns:1fr 1fr">
        <div class="field"><label>Subdomain *</label><input name="subdomain" class="input" placeholder="sekolahx" required></div>
        <div class="field">
          <label>Paket *</label>
          <select name="package_tier" class="select">
            <option value="dasar">Dasar</option>
            <option value="menengah" selected>Menengah</option>
            <option value="atas">Atas</option>
          </select>
        </div>
      </div>
      <div class="form-grid" style="grid-template-columns:1fr 1fr">
        <div class="field"><label>Username Admin *</label><input name="admin_username" class="input" required></div>
        <div class="field"><label>Password Admin *</label><input name="admin_password" type="password" class="input" minlength="6" required></div>
      </div>
      <button class="btn btn-god">🏫 Buat Sekolah + Admin</button>
    </form>
  </div>

  <div class="glass panel">
    <h2 class="panel-title"><svg viewBox="0 0 24 24"><path d="M4 11l12-6v14L4 13v-2z"/></svg> Catatan GOD MODE</h2>
    <div class="stack">
      <div class="glass-soft ann-item">
        <h3>⚡ Masuk sebagai sekolah</h3>
        <p>Tombol GOD MODE di tabel akan login sebagai admin sekolah tanpa perlu tahu passwordnya — Anda bisa memeriksa data, mengubah konfigurasi, atau membantu troubleshooting langsung.</p>
      </div>
      <div class="glass-soft ann-item">
        <h3>🛡️ Isolasi tetap terjaga</h3>
        <p>Akun super admin tersimpan di tabel terpisah (<code>super_admins</code>), tidak pernah bercampur dengan akun sekolah. Saat masuk God Mode, banner peringatan aktif di sidebar.</p>
      </div>
      <div class="glass-soft ann-item">
        <h3>🗑️ Hapus permanen</h3>
        <p>Menghapus sekolah akan menghapus seluruh data turunannya (siswa, tagihan, presensi) lewat foreign key cascade. Tindakan ini tidak bisa dibatalkan.</p>
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
      <label>Password Baru *</label>
      <input name="password" type="password" class="input" minlength="6" required>
    </div>
    <div class="dlg-actions">
      <button type="button" class="btn" data-close>Batal</button>
      <button class="btn btn-god">Reset</button>
    </div>
  </form>
</dialog>
@endforeach
@endsection
