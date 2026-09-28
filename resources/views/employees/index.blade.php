@extends('layouts.app')
@section('title', 'Data Pegawai & GTK')

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <span class="cs-pill" style="font-size:11px;padding:3px 12px"><span class="dot"></span> Direktori GTK</span>
      <span style="font-size:12px;color:var(--muted)">Pendidik &amp; Tenaga Kependidikan</span>
    </div>
    <h1>Data Pegawai (GTK)</h1>
    <div class="sub">Direktori seluruh guru dan tenaga kependidikan sekolah beserta jabatan fungsional.</div>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <a href="{{ route('employees.template') }}" class="btn btn-sm" title="Unduh template Excel untuk input cepat">
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
      Template Excel (.xlsx)
    </a>
    <button type="button" class="btn btn-sm" data-dialog="#import-employee">
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
      Import Excel (.xlsx)
    </button>
  </div>
</div>

<div class="two-col">
  {{-- Form Tambah Pegawai --}}
  <div class="glass panel">
    <h2 class="panel-title">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
      Tambah Pegawai Baru
    </h2>
    <form method="POST" action="{{ route('employees.store') }}" class="stack">
      @csrf
      <div class="field">
        <label>Nama Lengkap &amp; Gelar *</label>
        <input name="full_name" class="input" placeholder="mis. Dra. Siti Rahma, M.Pd" required>
      </div>

      <div class="field">
        <label>NIP / NUPTK</label>
        <input name="nip" class="input" placeholder="mis. 19800512 200501 1 002">
      </div>

      <div class="field">
        <label>Jabatan Fungsional</label>
        <select name="position_id" class="select">
          <option value="">— Belum ditentukan —</option>
          @foreach ($positions as $position)
            <option value="{{ $position->id }}">{{ $position->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="field">
        <label>Akun Login Guru/Staf (Opsional)</label>
        <input name="username" class="input" placeholder="mis. siti.rahma (kata sandi default: password)">
      </div>

      <div class="field">
        <label>Status Kepegawaian</label>
        <select name="status" class="select">
          <option value="aktif">🟢 Aktif Bertugas</option>
          <option value="nonaktif">⚪ Nonaktif / Mutasi</option>
        </select>
      </div>

      <button class="btn btn-ink" data-loading="Menyimpan Pegawai...">💾 Simpan Data GTK</button>
    </form>
  </div>

  {{-- Tabel Pegawai --}}
  <div class="glass table-wrap">
    <table class="tbl">
      <thead>
        <tr>
          <th>Nama Pegawai (GTK)</th>
          <th>NIP / NUPTK</th>
          <th>Jabatan</th>
          <th>Status</th>
          <th style="text-align:right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($employees as $employee)
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:10px">
                <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#06b6d4,#3b82f6);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:11.5px;flex:none">
                  {{ strtoupper(substr($employee->full_name, 0, 2)) }}
                </div>
                <div>
                  <b style="color:var(--text);font-size:14px">{{ $employee->full_name }}</b>
                  @if ($employee->user)
                    <div style="font-size:11px;color:var(--accent);margin-top:1px">
                      👤 Akun: {{ $employee->user->username }}
                    </div>
                  @endif
                </div>
              </div>
            </td>
            <td>
              <span style="font-size:13px">{{ $employee->nip ?? '—' }}</span>
            </td>
            <td>
              <span class="badge badge-blue">{{ $employee->position?->name ?? 'Staf Umum' }}</span>
            </td>
            <td>
              <span class="badge {{ $employee->status === 'aktif' ? 'badge-ok' : 'badge-bad' }}">
                {{ $employee->status === 'aktif' ? '🟢 Aktif' : '⚪ Nonaktif' }}
              </span>
            </td>
            <td>
              <div class="actions">
                <button class="btn btn-sm" data-dialog="#dlg-{{ $employee->id }}">Edit</button>
                <form method="POST" action="{{ route('employees.destroy', $employee) }}" data-confirm="Hapus data pegawai {{ $employee->full_name }}?">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="empty">Belum ada pegawai terdata. Tambahkan melalui formulir di samping atau gunakan Import Excel.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- MODAL IMPORT EXCEL GTK --}}
<dialog id="import-employee" class="modal glass">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
      <h3 style="margin:0;font-size:17px;color:var(--text);display:flex;align-items:center;gap:8px">
        <svg viewBox="0 0 24 24" style="width:20px;height:20px;stroke:var(--accent);fill:none;stroke-width:2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
        Import Data Pegawai / GTK (Excel .xlsx)
      </h3>
      <button type="button" data-close class="side-close" style="position:static">✕</button>
    </div>

    <form action="{{ route('employees.import') }}" method="POST" enctype="multipart/form-data" class="stack">
      @csrf
      <p style="font-size:13px;color:var(--muted-2);margin-bottom:10px">
        Unggah file Microsoft Excel (<b>.xlsx</b>) berisi data GTK. Kolom yang didukung:
        <b>NIP, NAMA_LENGKAP, JABATAN, STATUS, USERNAME, EMAIL</b>.
      </p>

      <div class="field">
        <label>Pilih File Excel (.xlsx)</label>
        <input type="file" name="file" class="input" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,.csv" required style="padding:10px">
      </div>

      <div style="display:flex;align-items:center;justify-content:space-between;margin-top:14px">
        <a href="{{ route('employees.template') }}" class="btn btn-sm" style="font-size:12px">
          Unduh Template Excel (.xlsx)
        </a>
        <div style="display:flex;gap:8px">
          <button type="button" data-close class="btn btn-sm">Batal</button>
          <button type="submit" class="btn btn-sm btn-ink">Mulai Import</button>
        </div>
      </div>
    </form>
  </div>
</dialog>

@foreach ($employees as $employee)
<dialog class="dlg" id="dlg-{{ $employee->id }}">
  <h3>Perbarui Pegawai — {{ $employee->full_name }}</h3>
  <form method="POST" action="{{ route('employees.update', $employee) }}" class="stack">
    @csrf @method('PUT')
    <div class="field">
      <label>Nama Lengkap *</label>
      <input name="full_name" class="input" value="{{ $employee->full_name }}" required>
    </div>
    <div class="field">
      <label>NIP</label>
      <input name="nip" class="input" value="{{ $employee->nip }}">
    </div>
    <div class="field">
      <label>Jabatan</label>
      <select name="position_id" class="select">
        <option value="">— Belum ditentukan —</option>
        @foreach ($positions as $position)
          <option value="{{ $position->id }}" {{ $employee->position_id === $position->id ? 'selected' : '' }}>
            {{ $position->name }}
          </option>
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
      <button class="btn btn-ink" data-loading="Menyimpan...">Simpan Perubahan</button>
    </div>
  </form>
</dialog>
@endforeach
@endsection
