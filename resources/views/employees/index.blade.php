@extends('layouts.app')
@section('title', 'Data Pegawai')

@section('content')
<div class="page-head">
  <div>
    <h1>Data Pegawai</h1>
    <div class="sub">Guru dan tenaga kependidikan beserta jabatannya.</div>
  </div>
</div>

<div class="two-col">
  <div class="glass panel">
    <h2 class="panel-title"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg> Tambah Pegawai</h2>
    <form method="POST" action="{{ route('employees.store') }}" class="stack">
      @csrf
      <div class="field"><label>Nama Lengkap *</label><input name="full_name" class="input" required></div>
      <div class="field"><label>NIP</label><input name="nip" class="input"></div>
      <div class="field">
        <label>Jabatan</label>
        <select name="position_id" class="select">
          <option value="">— Belum ditentukan —</option>
          @foreach ($positions as $position)
            <option value="{{ $position->id }}">{{ $position->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="field">
        <label>Status</label>
        <select name="status" class="select">
          <option value="aktif">Aktif</option>
          <option value="nonaktif">Nonaktif</option>
        </select>
      </div>
      <button class="btn btn-ink">Simpan Pegawai</button>
    </form>
  </div>

  <div class="glass table-wrap">
    <table class="tbl">
      <thead><tr><th>Nama</th><th>NIP</th><th>Jabatan</th><th>Status</th><th></th></tr></thead>
      <tbody>
        @forelse ($employees as $employee)
          <tr>
            <td><b>{{ $employee->full_name }}</b></td>
            <td>{{ $employee->nip ?? '—' }}</td>
            <td>{{ $employee->position?->name ?? '—' }}</td>
            <td><span class="badge {{ $employee->status === 'aktif' ? 'badge-ok' : '' }}">{{ ucfirst($employee->status) }}</span></td>
            <td>
              <div class="actions">
                <button class="btn btn-sm" data-dialog="#dlg-{{ $employee->id }}">Edit</button>
                <form method="POST" action="{{ route('employees.destroy', $employee) }}" data-confirm="Hapus pegawai {{ $employee->full_name }}?">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="empty">Belum ada pegawai.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@foreach ($employees as $employee)
<dialog class="dlg" id="dlg-{{ $employee->id }}">
  <h3>Edit {{ $employee->full_name }}</h3>
  <form method="POST" action="{{ route('employees.update', $employee) }}" class="stack">
    @csrf @method('PUT')
    <div class="field"><label>Nama Lengkap *</label><input name="full_name" class="input" value="{{ $employee->full_name }}" required></div>
    <div class="field"><label>NIP</label><input name="nip" class="input" value="{{ $employee->nip }}"></div>
    <div class="field">
      <label>Jabatan</label>
      <select name="position_id" class="select">
        <option value="">— Belum ditentukan —</option>
        @foreach ($positions as $position)
          <option value="{{ $position->id }}" {{ $employee->position_id === $position->id ? 'selected' : '' }}>{{ $position->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="field">
      <label>Status</label>
      <select name="status" class="select">
        <option value="aktif" {{ $employee->status === 'aktif' ? 'selected' : '' }}>Aktif</option>
        <option value="nonaktif" {{ $employee->status === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
      </select>
    </div>
    <div class="dlg-actions">
      <button type="button" class="btn" data-close>Batal</button>
      <button class="btn btn-ink">Simpan</button>
    </div>
  </form>
</dialog>
@endforeach
@endsection
