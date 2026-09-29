@extends('layouts.god')
@section('title', 'Super Admin')

@section('content')
<div class="page-head">
  <div>
    <h1>Akun Super Admin</h1>
    <div class="sub">Akun ini terpisah penuh dari akun sekolah dan berhak atas GOD MODE.</div>
  </div>
  <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
    <button type="button" class="btn btn-sm btn-god" data-dialog="#dialog-create-admin">
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
      + Tambah Super Admin
    </button>
  </div>
</div>

<div class="glass table-wrap" style="margin-top:16px">
  <table class="tbl">
    <thead><tr><th>Nama</th><th>Username</th><th>Status</th><th style="text-align:right">Aksi</th></tr></thead>
    <tbody>
      @foreach ($admins as $admin)
        <tr>
          <td><b>{{ $admin->name }}</b></td>
          <td>{{ '@' . $admin->username }}</td>
          <td><span class="badge {{ $admin->is_active ? 'badge-ok' : 'badge-bad' }}">{{ $admin->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
          <td style="text-align:right">
            <div class="actions" style="justify-content:flex-end">
              <button class="btn btn-sm" data-dialog="#rp-{{ $admin->id }}">Reset PW</button>
              @if ($admin->id !== auth('super')->id())
                <form method="POST" action="{{ route('god.admins.destroy', $admin) }}" data-confirm="Hapus super admin {{ $admin->username }}?">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
              @endif
            </div>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

{{-- MODAL TAMBAH SUPER ADMIN --}}
<dialog id="dialog-create-admin" class="modal glass">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
      <h3 style="margin:0;font-size:17px;color:var(--text);display:flex;align-items:center;gap:8px">
        <svg viewBox="0 0 24 24" style="width:20px;height:20px;stroke:var(--accent);fill:none;stroke-width:2"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
        Tambah Super Admin
      </h3>
      <button type="button" data-close class="modal-close" aria-label="Tutup">✕</button>
    </div>
    <form method="POST" action="{{ route('god.admins.store') }}" class="stack">
      @csrf
      <div class="field"><label>Username *</label><input name="username" class="input" required></div>
      <div class="field"><label>Nama Lengkap *</label><input name="name" class="input" required></div>
      <div class="field"><label>Password * (min. 8)</label><input name="password" type="password" class="input" minlength="8" required></div>
      <div class="modal-actions" style="margin-top:16px">
        <button type="button" class="btn" data-close>Batal</button>
        <button class="btn btn-god">Buat Akun</button>
      </div>
    </form>
  </div>
</dialog>

@foreach ($admins as $admin)
<dialog class="dlg" id="rp-{{ $admin->id }}">
  <h3>Reset Password — {{ $admin->username }}</h3>
  <form method="POST" action="{{ route('god.admins.reset', $admin) }}" class="stack">
    @csrf
    <div class="field"><label>Password Baru * (min. 8)</label><input name="password" type="password" class="input" minlength="8" required></div>
    <div class="dlg-actions">
      <button type="button" class="btn" data-close>Batal</button>
      <button class="btn btn-god">Reset</button>
    </div>
  </form>
</dialog>
@endforeach
@endsection
