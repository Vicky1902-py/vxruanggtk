@extends('layouts.app')
@section('title', 'Data Siswa')

@section('content')
<div class="page-head">
  <div>
    <h1>Data Siswa</h1>
    <div class="sub">{{ $students->count() }} siswa · Kelola data, wali, dan status siswa.</div>
  </div>
</div>

<div class="glass panel" style="margin-bottom:16px">
  <form method="GET" class="form-grid">
    <div class="field">
      <label>Cari</label>
      <input type="text" name="q" class="input" value="{{ request('q') }}" placeholder="Nama / NIS / NISN">
    </div>
    <div class="field">
      <label>Kelas</label>
      <select name="class_id" class="select">
        <option value="">Semua kelas</option>
        @foreach ($classes as $class)
          <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
        @endforeach
      </select>
    </div>
    <button class="btn">Terapkan</button>
    @if (request()->anyFilled(['q', 'class_id']))
      <a href="{{ route('students.index') }}" class="btn btn-sm">Reset</a>
    @endif
  </form>
</div>

<div class="two-col">
  <div class="glass panel">
    <h2 class="panel-title"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg> Tambah Siswa</h2>
    <form method="POST" action="{{ route('students.store') }}" class="stack">
      @csrf
      <div class="field"><label>Nama Lengkap *</label><input name="full_name" class="input" required></div>
      <div class="form-grid" style="grid-template-columns:1fr 1fr">
        <div class="field"><label>NIS</label><input name="nis" class="input"></div>
        <div class="field"><label>NISN</label><input name="nisn" class="input"></div>
      </div>
      <div class="form-grid" style="grid-template-columns:1fr 1fr">
        <div class="field">
          <label>Jenis Kelamin</label>
          <select name="gender" class="select"><option value="L">Laki-laki</option><option value="P">Perempuan</option></select>
        </div>
        <div class="field"><label>Tgl Lahir</label><input type="date" name="birth_date" class="input"></div>
      </div>
      <div class="field">
        <label>Kelas</label>
        <select name="class_id" class="select">
          <option value="">— Belum ditempatkan —</option>
          @foreach ($classes as $class)
            <option value="{{ $class->id }}">{{ $class->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="field">
        <label>Wali Murid</label>
        <select name="guardian_id" class="select">
          <option value="">— Tanpa wali —</option>
          @foreach ($guardians as $guardian)
            <option value="{{ $guardian->id }}">{{ $guardian->full_name }} ({{ $guardian->relation_type }})</option>
          @endforeach
        </select>
      </div>
      <div class="field">
        <label>Status</label>
        <select name="status" class="select">
          @foreach (['aktif', 'lulus', 'pindah', 'keluar'] as $st)
            <option value="{{ $st }}">{{ ucfirst($st) }}</option>
          @endforeach
        </select>
      </div>
      <button class="btn btn-ink">Simpan Siswa</button>
    </form>
  </div>

  <div class="glass table-wrap">
    <table class="tbl">
      <thead><tr><th>Nama</th><th>NIS/NISN</th><th>Kelas</th><th>Wali</th><th>Status</th><th></th></tr></thead>
      <tbody>
        @forelse ($students as $student)
          <tr>
            <td><b>{{ $student->full_name }}</b><br><small style="color:var(--muted)">{{ $student->gender === 'L' ? '♂ Laki-laki' : '♀ Perempuan' }}</small></td>
            <td>{{ $student->nis ?? '—' }}<br><small style="color:var(--muted)">{{ $student->nisn ?? '' }}</small></td>
            <td>{{ $student->schoolClass?->name ?? '—' }}</td>
            <td>{{ $student->guardian?->full_name ?? '—' }}</td>
            <td><span class="badge {{ $student->status === 'aktif' ? 'badge-ok' : '' }}">{{ ucfirst($student->status) }}</span></td>
            <td>
              <div class="actions">
                <button class="btn btn-sm" data-dialog="#dlg-{{ $student->id }}">Edit</button>
                <form method="POST" action="{{ route('students.destroy', $student) }}" data-confirm="Hapus siswa {{ $student->full_name }}?">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="empty">Tidak ada siswa ditemukan.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@foreach ($students as $student)
<dialog class="dlg" id="dlg-{{ $student->id }}">
  <h3>Edit {{ $student->full_name }}</h3>
  <form method="POST" action="{{ route('students.update', $student) }}" class="stack">
    @csrf @method('PUT')
    <div class="field"><label>Nama Lengkap *</label><input name="full_name" class="input" value="{{ $student->full_name }}" required></div>
    <div class="form-grid" style="grid-template-columns:1fr 1fr">
      <div class="field"><label>NIS</label><input name="nis" class="input" value="{{ $student->nis }}"></div>
      <div class="field"><label>NISN</label><input name="nisn" class="input" value="{{ $student->nisn }}"></div>
    </div>
    <div class="form-grid" style="grid-template-columns:1fr 1fr">
      <div class="field">
        <label>Jenis Kelamin</label>
        <select name="gender" class="select">
          <option value="L" {{ $student->gender === 'L' ? 'selected' : '' }}>Laki-laki</option>
          <option value="P" {{ $student->gender === 'P' ? 'selected' : '' }}>Perempuan</option>
        </select>
      </div>
      <div class="field"><label>Tgl Lahir</label><input type="date" name="birth_date" class="input" value="{{ $student->birth_date?->format('Y-m-d') }}"></div>
    </div>
    <div class="field">
      <label>Kelas</label>
      <select name="class_id" class="select">
        <option value="">— Belum ditempatkan —</option>
        @foreach ($classes as $class)
          <option value="{{ $class->id }}" {{ $student->class_id === $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="field">
      <label>Wali Murid</label>
      <select name="guardian_id" class="select">
        <option value="">— Tanpa wali —</option>
        @foreach ($guardians as $guardian)
          <option value="{{ $guardian->id }}" {{ $student->guardian_id === $guardian->id ? 'selected' : '' }}>{{ $guardian->full_name }}</option>
        @endforeach
      </select>
    </div>
    <div class="field">
      <label>Status</label>
      <select name="status" class="select">
        @foreach (['aktif', 'lulus', 'pindah', 'keluar'] as $st)
          <option value="{{ $st }}" {{ $student->status === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
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
