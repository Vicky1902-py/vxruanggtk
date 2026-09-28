@extends('layouts.app')
@section('title', 'Data Kelas')

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <span class="cs-pill" style="font-size:11px;padding:3px 12px"><span class="dot"></span> Struktur Rombel</span>
      <span style="font-size:12px;color:var(--muted)">Manajemen Kelas &amp; Wali Kelas</span>
    </div>
    <h1>Data Kelas &amp; Rombel</h1>
    <div class="sub">Kelola struktur rombongan belajar per tahun ajaran beserta penugasan wali kelas dan jurusan.</div>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <a href="{{ route('classes.template') }}" class="btn btn-sm" title="Unduh template Excel untuk input cepat">
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
      Template Excel
    </a>
    <button type="button" class="btn btn-sm" data-dialog="#import-class">
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
      Import Excel / XLSX
    </button>
  </div>
</div>

<div class="two-col">
  <div class="stack">
    {{-- Form Tambah Kelas --}}
    <div class="glass panel">
      <h2 class="panel-title">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
        Tambah Kelas Baru
      </h2>
      <form method="POST" action="{{ route('classes.store') }}" class="stack">
        @csrf
        <div class="two-col" style="gap:10px">
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
            <label>Jurusan / Konsentrasi</label>
            <select name="major_id" class="select">
              <option value="">— Umum / Tanpa Jurusan —</option>
              @foreach ($majors as $m)
                <option value="{{ $m->id }}">{{ $m->code }} - {{ $m->name }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="field">
          <label>Nama Rombel / Kelas *</label>
          <input type="text" name="name" class="input" placeholder="mis. X RPL 1, XI TKJ 2" required maxlength="30">
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

    {{-- Panel Kelola Tahun Ajaran --}}
    <div class="glass panel">
      <h2 class="panel-title">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        Kelola Tahun Ajaran
      </h2>

      {{-- List Tahun Ajaran --}}
      <div class="stack" style="gap:8px;margin-bottom:14px">
        @forelse ($academicYears as $year)
          <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:rgba(255,255,255,0.03);border:1px solid var(--line-light);border-radius:var(--radius-sm)">
            <div>
              <b style="color:var(--text);font-size:13.5px">{{ $year->year_label }}</b>
              @if ($year->is_active)
                <span class="badge badge-ok" style="font-size:10.5px;margin-left:6px">Aktif</span>
              @endif
            </div>
            <div>
              @if (! $year->is_active)
                <form method="POST" action="{{ route('academic-years.activate', $year) }}">
                  @csrf
                  <button class="btn btn-sm" style="height:26px;font-size:11px;padding:0 8px">Jadikan Aktif</button>
                </form>
              @endif
            </div>
          </div>
        @empty
          <div style="font-size:12.5px;color:var(--muted)">Belum ada data tahun ajaran.</div>
        @endforelse
      </div>

      {{-- Form Tambah Tahun Ajaran --}}
      <form method="POST" action="{{ route('academic-years.store') }}" style="display:flex;gap:8px">
        @csrf
        <input type="text" name="year_label" class="input" placeholder="mis. 2027/2028" required style="height:34px;font-size:12.5px" maxlength="20">
        <button class="btn btn-sm btn-ink" style="height:34px;white-space:nowrap;padding:0 12px;font-size:12px">+ Tambah</button>
      </form>
    </div>
  </div>

  {{-- Tabel Daftar Kelas --}}
  <div class="glass table-wrap">
    <table class="tbl">
      <thead>
        <tr>
          <th>Nama Kelas</th>
          <th>Jurusan</th>
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
              @if ($class->major)
                <span class="badge badge-ink" style="font-weight:700">{{ $class->major->code }}</span>
              @else
                <span style="color:var(--muted);font-size:12px">Umum</span>
              @endif
            </td>
            <td>
              <span class="badge {{ $class->academicYear?->is_active ? 'badge-ok' : 'badge-blue' }}">{{ $class->academicYear?->year_label ?? '—' }}</span>
            </td>
            <td>
              <span>{{ $class->homeroomTeacher?->full_name ?? '—' }}</span>
            </td>
            <td>
              <span class="badge badge-ok">{{ $class->students->count() }} siswa</span>
            </td>
            <td>
              <div class="actions">
                <button class="btn btn-sm btn-ink" data-dialog="#students-{{ $class->id }}" style="height:30px;padding:0 10px;font-size:12px">
                  👥 Siswa ({{ $class->students->count() }})
                </button>
                <button class="btn btn-sm" data-dialog="#dlg-{{ $class->id }}" style="height:30px;padding:0 10px;font-size:12px">Edit</button>
                <form method="POST" action="{{ route('classes.destroy', $class) }}" data-confirm="Hapus rombel kelas {{ $class->name }}?">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-danger" style="height:30px;padding:0 10px;font-size:12px">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="empty">Belum ada rombel kelas yang terdaftar. Tambahkan lewat formulir di samping atau gunakan Import Excel.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- MODAL IMPORT EXCEL KELAS --}}
<dialog id="import-class" class="modal glass">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
      <h3 style="margin:0;font-size:17px;color:var(--text);display:flex;align-items:center;gap:8px">
        <svg viewBox="0 0 24 24" style="width:20px;height:20px;stroke:var(--accent);fill:none;stroke-width:2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
        Import Data Rombel Kelas (.xlsx / .csv)
      </h3>
      <button type="button" data-close class="side-close" style="position:static">✕</button>
    </div>

    <form action="{{ route('classes.import') }}" method="POST" enctype="multipart/form-data" class="stack">
      @csrf
      <p style="font-size:13px;color:var(--muted-2);margin-bottom:10px">
        Unggah file Excel (<code>.xlsx</code>) atau <code>.csv</code>. Kolom yang didukung:
        <b>NAMA_KELAS, KODE_JURUSAN, TAHUN_AJARAN, NIP_WALI_KELAS</b>.
      </p>

      <div class="field">
        <label>Pilih File Excel / CSV (.xlsx / .csv)</label>
        <input type="file" name="file" class="input" accept=".xlsx,.csv,.txt" required style="padding:10px">
      </div>

      <div style="display:flex;align-items:center;justify-content:space-between;margin-top:14px">
        <a href="{{ route('classes.template') }}" class="btn btn-sm" style="font-size:12px">
          Unduh Format Contoh (.csv/.xlsx)
        </a>
        <div style="display:flex;gap:8px">
          <button type="button" data-close class="btn btn-sm">Batal</button>
          <button type="submit" class="btn btn-sm btn-ink">Mulai Import</button>
        </div>
      </div>
    </form>
  </div>
</dialog>

@foreach ($classes as $class)
{{-- Dialog Edit Kelas --}}
<dialog class="dlg" id="dlg-{{ $class->id }}">
  <h3>Perbarui Kelas — {{ $class->name }}</h3>
  <form method="POST" action="{{ route('classes.update', $class) }}" class="stack">
    @csrf @method('PUT')
    <div class="field">
      <label>Tahun Ajaran</label>
      <select name="academic_year_id" class="select" required>
        @foreach ($academicYears as $year)
          <option value="{{ $year->id }}" {{ $class->academic_year_id === $year->id ? 'selected' : '' }}>
            {{ $year->year_label }}{{ $year->is_active ? ' (Aktif)' : '' }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="field">
      <label>Jurusan / Peminatan</label>
      <select name="major_id" class="select">
        <option value="">— Umum / Tanpa Jurusan —</option>
        @foreach ($majors as $m)
          <option value="{{ $m->id }}" {{ $class->major_id === $m->id ? 'selected' : '' }}>
            {{ $m->code }} - {{ $m->name }}
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

{{-- Dialog Daftar Siswa di Kelas Ini --}}
<dialog class="dlg" id="students-{{ $class->id }}" style="max-width:560px">
  <h3>Daftar Siswa Kelas {{ $class->name }} ({{ $class->students->count() }} Siswa)</h3>
  <div style="font-size:12.5px;color:var(--muted);margin-bottom:12px">
    Wali Kelas: <b style="color:var(--text)">{{ $class->homeroomTeacher?->full_name ?? 'Belum ditentukan' }}</b> · TA: {{ $class->academicYear?->year_label ?? '—' }}
  </div>

  <div style="max-height:360px;overflow-y:auto;border:1px solid var(--line-light);border-radius:var(--radius-sm)">
    <table class="tbl" style="margin:0">
      <thead>
        <tr>
          <th>No</th>
          <th>Nama Siswa</th>
          <th>NIS / NISN</th>
          <th>Gender</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($class->students as $idx => $st)
          <tr>
            <td>{{ $idx + 1 }}</td>
            <td><b style="color:var(--text)">{{ $st->full_name }}</b></td>
            <td>{{ $st->nis ?? '—' }}</td>
            <td>
              <span style="font-size:11.5px;font-weight:600;color:{{ $st->gender === 'L' ? '#0284c7' : '#db2777' }}">
                {{ $st->gender === 'L' ? '♂ L' : '♀ P' }}
              </span>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="empty">Belum ada siswa yang ditempatkan di kelas ini.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="dlg-actions" style="margin-top:14px">
    <button type="button" class="btn" data-close>Tutup</button>
    <a href="{{ route('students.index', ['class_id' => $class->id]) }}" class="btn btn-ink">Kelola di Direktori Siswa →</a>
  </div>
</dialog>
@endforeach
@endsection
