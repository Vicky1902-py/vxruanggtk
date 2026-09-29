@extends('layouts.app')
@section('title', 'Manajemen Pengguna')

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <span class="cs-pill" style="font-size:11px;padding:3px 12px"><span class="dot"></span> Pengaturan Sekolah</span>
      <span style="font-size:12px;color:var(--muted)">Akses &amp; Keamanan</span>
    </div>
    <h1>Manajemen Pengguna</h1>
    <div class="sub">{{ $users->count() }} akun terdaftar · Kelola hak akses staf, guru, bendahara, dan administrator internal.</div>
  </div>
</div>

<div class="glass panel" style="margin-bottom:18px">
  <form method="GET" class="form-grid" style="grid-template-columns: 2fr 1.5fr 1.2fr auto auto; align-items: flex-end;">
    <div class="field">
      <label>Pencarian Akun</label>
      <input type="text" name="q" class="input" value="{{ request('q') }}" placeholder="Cari username, email, atau nama pegawai...">
    </div>
    <div class="field">
      <label>Peran / Role</label>
      <select name="role_id" class="select">
        <option value="">Semua Peran</option>
        @foreach ($roles as $r)
          <option value="{{ $r->id }}" {{ request('role_id') == $r->id ? 'selected' : '' }}>
            {{ strtoupper($r->name) }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="field">
      <label>Status Akun</label>
      <select name="status" class="select">
        <option value="">Semua Status</option>
        <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
        <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
      </select>
    </div>
    <button class="btn btn-sm btn-ink" style="height:44px">Filter Data</button>
    @if (request()->anyFilled(['q', 'role_id', 'status']))
      <a href="{{ route('users.index') }}" class="btn btn-sm" style="height:44px">Reset</a>
    @endif
  </form>
</div>

<div class="two-col">
  {{-- Form Tambah Pengguna --}}
  <div class="glass panel">
    <h2 class="panel-title">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
      Tambah Pengguna Baru
    </h2>
    <form method="POST" action="{{ route('users.store') }}" class="stack">
      @csrf
      <div class="field">
        <label>Username *</label>
        <input name="username" class="input" value="{{ old('username') }}" placeholder="mis. bendahara_sekolah" required>
        @error('username') <span class="error-text">{{ $message }}</span> @enderror
      </div>

      <div class="field">
        <label>Alamat Email (Opsional)</label>
        <input type="email" name="email" class="input" value="{{ old('email') }}" placeholder="user@sekolah.sch.id">
        @error('email') <span class="error-text">{{ $message }}</span> @enderror
      </div>

      <div class="field">
        <label>Peran / Hak Akses *</label>
        <select name="role_id" class="select" required>
          <option value="">— Pilih Hak Akses —</option>
          @foreach ($roles as $role)
            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
              {{ strtoupper($role->name) }} 
              @if ($role->name === 'admin') (Akses Penuh Sekolah)
              @elseif ($role->name === 'bendahara') (Keuangan & SPP)
              @elseif ($role->name === 'guru') (Akademik & Presensi)
              @elseif ($role->name === 'staff_tu') (Administrasi & Data)
              @elseif ($role->name === 'kepsek') (Monitoring & Laporan)
              @endif
            </option>
          @endforeach
        </select>
        @error('role_id') <span class="error-text">{{ $message }}</span> @enderror
      </div>

      <div class="field">
        <label>Kata Sandi Akun *</label>
        <input type="password" name="password" class="input" placeholder="Minimal 6 karakter" required>
        @error('password') <span class="error-text">{{ $message }}</span> @enderror
      </div>

      <div class="field">
        <label>Hubungkan dengan Pegawai (GTK)</label>
        <select name="employee_id" class="select">
          <option value="">— Tidak terhubung / Akun sistem mandiri —</option>
          @foreach ($unlinkedEmployees as $emp)
            <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
              {{ $emp->full_name }} ({{ $emp->position?->name ?? 'Staf' }})
            </option>
          @endforeach
        </select>
        <small style="color:var(--muted);font-size:11.5px">Pilih nama guru/staf jika akun ini digunakan oleh pegawai yang sudah terdata.</small>
      </div>

      <button class="btn btn-ink" data-loading="Menyimpan Pengguna...">💾 Daftarkan Pengguna</button>
    </form>
  </div>

  {{-- Tabel Pengguna --}}
  <div class="glass table-wrap">
    <table class="tbl">
      <thead>
        <tr>
          <th>Pengguna</th>
          <th>Peran</th>
          <th>Pegawai Terkait</th>
          <th>Status</th>
          <th style="text-align:right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($users as $u)
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:10px">
                <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#0284c7,#38bdf8);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;flex:none">
                  {{ strtoupper(substr($u->username, 0, 2)) }}
                </div>
                <div>
                  <b style="color:var(--text);font-size:14px">{{ $u->username }}</b>
                  @if ($u->id === auth()->id())
                    <span class="badge badge-ok" style="font-size:10px;margin-left:4px">Anda</span>
                  @endif
                  <div style="margin-top:2px;color:var(--muted);font-size:12px">
                    {{ $u->email ?? 'Tanpa email' }}
                  </div>
                </div>
              </div>
            </td>
            <td>
              @php
                $roleColor = match ($u->role?->name) {
                  'admin' => 'badge-blue',
                  'bendahara' => 'badge-warn',
                  'guru' => 'badge-ok',
                  'kepsek' => 'badge-ink',
                  default => 'badge-slate'
                };
              @endphp
              <span class="badge {{ $roleColor }}" style="font-weight:600;letter-spacing:0.5px">
                {{ strtoupper($u->role?->name ?? 'Tanpa Role') }}
              </span>
            </td>
            <td>
              @if ($u->employee)
                <div style="font-weight:600;font-size:13px;color:var(--text)">{{ $u->employee->full_name }}</div>
                <small style="color:var(--muted)">{{ $u->employee->position?->name ?? 'GTK' }}</small>
              @else
                <span style="color:var(--muted);font-size:12px">—</span>
              @endif
            </td>
            <td>
              @if ($u->is_active)
                <span class="badge badge-ok">🟢 Aktif</span>
              @else
                <span class="badge badge-bad">🔴 Nonaktif</span>
              @endif
            </td>
            <td>
              <div class="actions">
                <button class="btn btn-sm" data-dialog="#dlg-edit-{{ $u->id }}">Edit</button>
                <button class="btn btn-sm" data-dialog="#dlg-reset-{{ $u->id }}" title="Reset Password">Reset</button>
                @if ($u->id !== auth()->id())
                  <form method="POST" action="{{ route('users.toggle', $u) }}">
                    @csrf
                    <button class="btn btn-sm {{ $u->is_active ? 'btn-warn' : 'btn-ok' }}" title="{{ $u->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}">
                      {{ $u->is_active ? 'Kunci' : 'Buka' }}
                    </button>
                  </form>
                  <form method="POST" action="{{ route('users.destroy', $u) }}" data-confirm="Hapus permanen akun pengguna {{ $u->username }}?">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger">Hapus</button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="empty">Tidak ada data pengguna yang sesuai.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- MODALS PER USER --}}
@foreach ($users as $u)
  {{-- Modal Edit User --}}
  <dialog id="dlg-edit-{{ $u->id }}" class="modal glass">
    <div class="modal-box">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <h3 style="margin:0;font-size:17px;color:var(--text)">Edit Akun: {{ $u->username }}</h3>
        <button type="button" class="btn btn-sm" data-close>✕</button>
      </div>
      <form method="POST" action="{{ route('users.update', $u) }}" class="stack">
        @csrf @method('PUT')
        <div class="field">
          <label>Email</label>
          <input type="email" name="email" class="input" value="{{ $u->email }}" placeholder="email@sekolah.sch.id">
        </div>
        <div class="field">
          <label>Peran / Hak Akses</label>
          <select name="role_id" class="select" required>
            @foreach ($roles as $role)
              <option value="{{ $role->id }}" {{ $u->role_id == $role->id ? 'selected' : '' }}>
                {{ strtoupper($role->name) }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="field">
          <label>Kata Sandi Baru (Kosongkan bila tidak diubah)</label>
          <input type="password" name="password" class="input" placeholder="Isi hanya jika ingin mengganti sandi">
        </div>
        <div class="field">
          <label>Status Akun</label>
          <select name="is_active" class="select">
            <option value="1" {{ $u->is_active ? 'selected' : '' }}>🟢 Aktif</option>
            <option value="0" {{ !$u->is_active ? 'selected' : '' }}>🔴 Nonaktif / Kunci</option>
          </select>
        </div>
        <div class="modal-actions" style="margin-top:16px">
          <button type="button" class="btn" data-close>Batal</button>
          <button class="btn btn-ink">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </dialog>

  {{-- Modal Reset Password --}}
  <dialog id="dlg-reset-{{ $u->id }}" class="modal glass">
    <div class="modal-box">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <h3 style="margin:0;font-size:17px;color:var(--text)">Reset Password: {{ $u->username }}</h3>
        <button type="button" class="btn btn-sm" data-close>✕</button>
      </div>
      <form method="POST" action="{{ route('users.reset', $u) }}" class="stack">
        @csrf
        <p style="font-size:13px;color:var(--muted);margin:0 0 12px 0">
          Masukkan kata sandi baru untuk pengguna <b>{{ $u->username }}</b>, atau biarkan default <code>password123</code>.
        </p>
        <div class="field">
          <label>Kata Sandi Baru</label>
          <input type="text" name="new_password" class="input" value="password123" required>
        </div>
        <div class="modal-actions" style="margin-top:16px">
          <button type="button" class="btn" data-close>Batal</button>
          <button class="btn btn-ink">Reset Kata Sandi</button>
        </div>
      </form>
    </div>
  </dialog>
@endforeach

@endsection
