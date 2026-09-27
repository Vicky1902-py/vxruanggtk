@extends('layouts.app')
@section('title', 'Data Kelas')

@section('content')
<div class="page-head">
  <div>
    <h1>Data Kelas</h1>
    <div class="sub">Kelola kelas per tahun ajaran beserta wali kelasnya.</div>
  </div>
</div>

<div class="two-col">
  <div class="glass panel">
    <h2 class="panel-title"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg> Tambah Kelas</h2>
    <form method="POST" action="{{ route('classes.store') }}" class="stack">
      @csrf
      <div class="field">
        <label>Tahun Ajaran</label>
        <select name="academic_year_id" class="select" required>
          @foreach ($academicYears as $year)
            <option value="{{ $year->id }}" {{ $year->is_active ? 'selected' : '' }}>{{ $year->year_label }}{{ $year->is_active ? ' (aktif)' : '' }}</option>
          @endforeach
        </select>
      </div>
      <div class="field">
        <label>Nama Kelas</label>
        <input type="text" name="name" class="input" placeholder="mis. 7A" required maxlength="30">
      </div>
      <div class="field">
        <label>Wali Kelas</label>
        <select name="homeroom_teacher_id" class="select">
          <option value="">— Pilih wali kelas —</option>
          @foreach ($teachers as $teacher)
            <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
          @endforeach
        </select>
      </div>
      <button class="btn btn-ink">Simpan Kelas</button>
    </form>
  </div>

  <div class="glass table-wrap">
    <table class="tbl">
      <thead><tr><th>Kelas</th><th>Tahun Ajaran</th><th>Wali Kelas</th><th>Jml Siswa</th><th></th></tr></thead>
      <tbody>
        @forelse ($classes as $class)
          <tr>
            <td><b>{{ $class->name }}</b></td>
            <td>{{ $class->academicYear?->year_label }}</td>
            <td>{{ $class->homeroomTeacher?->full_name ?? '—' }}</td>
            <td>{{ $class->students()->count() }}</td>
            <td>
              <div class="actions">
                <button class="btn btn-sm" data-dialog="#dlg-{{ $class->id }}">Edit</button>
                <form method="POST" action="{{ route('classes.destroy', $class) }}" data-confirm="Hapus kelas {{ $class->name }}?">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="empty">Belum ada kelas. Tambahkan lewat form di samping.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@foreach ($classes as $class)
<dialog class="dlg" id="dlg-{{ $class->id }}">
  <h3>Edit Kelas {{ $class->name }}</h3>
  <form method="POST" action="{{ route('classes.update', $class) }}" class="stack">
    @csrf @method('PUT')
    <div class="field">
      <label>Tahun Ajaran</label>
      <select name="academic_year_id" class="select" required>
        @foreach ($academicYears as $year)
          <option value="{{ $year->id }}" {{ $class->academic_year_id === $year->id ? 'selected' : '' }}>{{ $year->year_label }}</option>
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
          <option value="{{ $teacher->id }}" {{ $class->homeroom_teacher_id === $teacher->id ? 'selected' : '' }}>{{ $teacher->full_name }}</option>
        @endforeach
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
