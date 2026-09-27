@extends('layouts.god')
@section('title', 'Super Admin')

@section('content')
<div class="page-head">
  <div>
    <h1>Akun Super Admin</h1>
    <div class="sub">Akun ini terpisah penuh dari akun sekolah dan berhak atas GOD MODE.</div>
  </div>
</div>

<div class="two-col">
  <div class="glass panel">
    <h2 class="panel-title"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg> Tambah Super Admin</h2>
    <form method="POST" action="{{ route('god.admins.store') }}" class="stack">
      @csrf
      <div class="field"><label>Username *</label><input name="username" class="input" required></div>
      <div class="field"><label>Nama Lengkap *</label><input name="name" class="input" required></div>
      <div class="field"><label>Password * (min. 8)</label><input name="password" type="password" class="input" minlength="8" required></div>
      <button class="btn btn-god">Buat Akun</button>
    </form>
  </div>

  <div class="glass table-wrap">
    <table class="tbl">
      <thead><tr><th>Nama</th><th>Username</th><th>Status</th><th></th></tr></thead>
      <tbody>
        @foreach ($admins as $admin)
          <tr>
            <td><b>{{ $admin->name }}</b></td>
            <td>{{ '@' . $admin->username }}</td>
            <td><span class="badge {{ $admin->is_active ? 'badge-ok' : 'badge-bad' }}">{{ $admin->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
            <td>
              <div class="actions">
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
</div>

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
