@extends('layouts.app')
@section('title', 'Data Kelas')

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <span class="vtx-pill" style="font-size:11px;padding:3px 12px"><span class="dot"></span> Struktur Rombel</span>
      <span style="font-size:12px;color:var(--muted)">Manajemen Kelas &amp; Wali Kelas</span>
    </div>
    <h1>Data Kelas &amp; Rombel</h1>
    <div class="sub">Kelola struktur rombongan belajar per tahun ajaran beserta penugasan wali kelas.</div>
  </div>
</div>

<div class="two-col">
  {{-- Form Tambah Kelas --}}
  <div class="glass panel">
    <h2 class="panel-title">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
      Tambah Kelas Baru
    </h2>
    <form method="POST" action="{{ route('classes.store') }}" class="stack">
      @csrf
      <div class="field">
        <label>Tahun Ajaran *</label>
        <select name="academic_year_id" class="select" required>
          @foreach ($academicYears as $year)
            <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>
              {{ $year->year_label }}{{ $year->is_active ? ' (Aktif)' : '' }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="field">
        <label>Nama Rombel / Kelas *</label>
        <input type="text" name="name" class="input" placeholder="mis. 7A, 8B, X-MIPA-1" required maxlength="30">
      </div>

      <div class="field">
        <label>Wali Kelas (GTK)</label>
        <select name="homeroom_teacher_id" class="select">
          <option value="">— Pilih Guru Wali Kelas —</option>
          @foreach ($teachers as $teacher)
            <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
          @endforeach
        </select>
      </div>

      <button class="btn btn-ink" data-loading="Menyimpan Kelas...">💾 Simpan Rombel Kelas</button>
    </form>
  </div>

  {{-- Tabel Daftar Kelas --}}
  <div class="glass table-wrap">
    <table class="tbl">
      <thead>
        <tr>
          <th>Nama Kelas</th>
          <th>Tahun Ajaran</th>
          <th>Wali Kelas</th>
          <th>Jml Siswa</th>
          <th style="text-align:right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($classes as $class)
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:10px">
                <span class="icon-chip icon-sm is-blue">
                  <svg viewBox="0 0 24 24"><path d="M3 9l9-6 9 6"/><path d="M5 9v9a2 2 0 002 2h10a2 2 0 002-2V9"/></svg>
                </span>
                <b style="font-size:14.5px;color:var(--text)">Kelas {{ $class->name }}</b>
              </div>
            </td>
            <td>
              <span class="badge badge-blue">{{ $class->academicYear?->year_label ?? '—' }}</span>
            </td>
            <td>
              <span>{{ $class->homeroomTeacher?->full_name ?? '—' }}</span>
            </td>
            <td>
              <span class="badge badge-ok">{{ $class->students()->count() }} siswa</span>
            </td>
            <td>
              <div class="actions">
                <button class="btn btn-sm" data-dialog="#dlg-{{ $class->id }}">Edit</button>
                <form method="POST" action="{{ route('classes.destroy', $class) }}" data-confirm="Hapus rombel kelas {{ $class->name }}?">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="empty">Belum ada rombel kelas yang terdaftar. Tambahkan lewat formulir di samping.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@foreach ($classes as $class)
<dialog class="dlg" id="dlg-{{ $class->id }}">
  <h3>Perbarui Kelas — {{ $class->name }}</h3>
  <form method="POST" action="{{ route('classes.update', $class) }}" class="stack">
    @csrf @method('PUT')
    <div class="field">
      <label>Tahun Ajaran</label>
      <select name="academic_year_id" class="select" required>
        @foreach ($academicYears as $year)
          <option value="{{ $year->id }}" {{ $class->academic_year_id === $year->id ? 'selected' : '' }}>
            {{ $year->year_label }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="field">
      <label>Nama Kelas</label>
      <input type="text" name="name" class="input" value="{{ old('name', $class->name) }}" required maxlength="30">
    </div>
    <div class="field">
      <label>Wali Kelas</label>
      <select name="homeroom_teacher_id" class="select">
        <option value="">— Pilih wali kelas —</option>
        @foreach ($teachers as $teacher)
          <option value="{{ $teacher->id }}" {{ $class->homeroom_teacher_id === $teacher->id ? 'selected' : '' }}>
            {{ $teacher->full_name }}
          </option>
        @endforeach
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
