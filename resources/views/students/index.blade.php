@extends('layouts.app')
@section('title', 'Data Siswa')

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <span class="cs-pill" style="font-size:11px;padding:3px 12px"><span class="dot"></span> Direktori Akademik</span>
      <span style="font-size:12px;color:var(--muted)">Data Pokok Peserta Didik</span>
    </div>
    <h1>Data Siswa</h1>
    <div class="sub">{{ $students->count() }} siswa terdata · Terhubung dengan Rombel, Jurusan, Wali Murid, dan Presensi.</div>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <a href="{{ route('students.export', request()->all()) }}" class="btn btn-sm" title="Unduh seluruh data siswa ke Microsoft Excel (.xlsx)">
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
      Export Excel (.xlsx)
    </a>
    <a href="{{ route('students.template') }}" class="btn btn-sm" title="Unduh template Excel untuk input cepat">
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      Template Excel
    </a>
    <button type="button" class="btn btn-sm" data-dialog="#import-student">
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
      Import Excel / XLSX
    </button>
  </div>
</div>

<div class="glass panel" style="margin-bottom:18px">
  <form method="GET" class="form-grid" style="grid-template-columns: 2fr 1.2fr 1.2fr auto auto; align-items: flex-end;">
    <div class="field">
      <label>Pencarian Siswa</label>
      <input type="text" name="q" class="input" value="{{ request('q') }}" placeholder="Cari nama, NIS, atau NISN...">
    </div>
    <div class="field">
      <label>Filter Kelas</label>
      <select name="class_id" class="select">
        <option value="">Semua kelas</option>
        @foreach ($classes as $class)
          <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>Kelas {{ $class->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="field">
      <label>Filter Jurusan</label>
      <select name="major_id" class="select">
        <option value="">Semua jurusan</option>
        @foreach ($majors as $m)
          <option value="{{ $m->id }}" {{ request('major_id') == $m->id ? 'selected' : '' }}>{{ $m->code }} - {{ $m->name }}</option>
        @endforeach
      </select>
    </div>
    <button class="btn btn-sm btn-ink" style="height:44px">Cari Data</button>
    @if (request()->anyFilled(['q', 'class_id', 'major_id']))
      <a href="{{ route('students.index') }}" class="btn btn-sm" style="height:44px">Reset</a>
    @endif
  </form>
</div>

<div class="two-col">
  {{-- Form Tambah Siswa --}}
  <div class="glass panel">
    <h2 class="panel-title">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
      Tambah Siswa Baru
    </h2>
    <form method="POST" action="{{ route('students.store') }}" class="stack">
      @csrf
      <div class="field">
        <label>Nama Lengkap *</label>
        <input name="full_name" class="input" value="{{ old('full_name') }}" placeholder="mis. Muhammad Raihan" required>
        @error('full_name') <span class="error-text">{{ $message }}</span> @enderror
      </div>
      <div class="form-grid" style="grid-template-columns:1fr 1fr">
        <div class="field">
          <label>NIS</label>
          <input name="nis" class="input" value="{{ old('nis') }}" placeholder="mis. 24001">
        </div>
        <div class="field">
          <label>NISN</label>
          <input name="nisn" class="input" value="{{ old('nisn') }}" placeholder="mis. 0081234567">
        </div>
      </div>
      <div class="form-grid" style="grid-template-columns:1fr 1fr">
        <div class="field">
          <label>Jenis Kelamin *</label>
          <select name="gender" class="select">
            <option value="L" {{ old('gender') === 'L' ? 'selected' : '' }}>Laki-laki</option>
            <option value="P" {{ old('gender') === 'P' ? 'selected' : '' }}>Perempuan</option>
          </select>
        </div>
        <div class="field">
          <label>Tanggal Lahir</label>
          <input type="date" name="birth_date" class="input" value="{{ old('birth_date') }}">
        </div>
      </div>
      <div class="form-grid" style="grid-template-columns:1fr 1fr">
        <div class="field">
          <label>Rombel (Kelas)</label>
          <select name="class_id" class="select">
            <option value="">— Pilih Rombel —</option>
            @foreach ($classes as $class)
              <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>Kelas {{ $class->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="field">
          <label>Jurusan / Peminatan</label>
          <select name="major_id" class="select">
            <option value="">— Pilih Jurusan —</option>
            @foreach ($majors as $m)
              <option value="{{ $m->id }}" {{ old('major_id') == $m->id ? 'selected' : '' }}>{{ $m->code }} - {{ $m->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="field">
        <label>Wali Murid Terdaftar</label>
        <select name="guardian_id" class="select">
          <option value="">— Tanpa wali —</option>
          @foreach ($guardians as $guardian)
            <option value="{{ $guardian->id }}" {{ old('guardian_id') == $guardian->id ? 'selected' : '' }}>{{ $guardian->full_name }} ({{ $guardian->relation_type }})</option>
          @endforeach
        </select>
      </div>
      <div class="field">
        <label>Status Keaktifan</label>
        <select name="status" class="select">
          @foreach (['aktif' => 'Aktif', 'lulus' => 'Lulus', 'pindah' => 'Pindah', 'keluar' => 'Keluar'] as $st => $label)
            <option value="{{ $st }}" {{ old('status', 'aktif') === $st ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <button class="btn btn-ink" data-loading="Menyimpan Siswa...">💾 Simpan Data Siswa</button>
    </form>
  </div>

  {{-- Tabel Siswa --}}
  <div class="glass table-wrap">
    {{-- Bulk Action Bar --}}
    <div id="bulk-toolbar" style="display:none;background:rgba(2,132,199,0.08);border:1px solid rgba(2,132,199,0.25);border-radius:var(--radius-sm);padding:8px 14px;margin-bottom:12px;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
      <div style="font-size:13px;font-weight:600;color:var(--text);display:flex;align-items:center;gap:6px">
        <span class="cs-pill" style="font-size:11px;padding:2px 8px"><span id="selected-count">0</span> siswa dipilih</span>
      </div>
      <div style="display:flex;gap:8px">
        <button type="button" class="btn btn-sm btn-ink" onclick="openBulkPromote()">
          🎓 Naik / Pindah Kelas
        </button>
        <button type="button" class="btn btn-sm btn-outline" onclick="submitBulkGraduation()">
          🏅 Luluskan Terpilih
        </button>
      </div>
    </div>

    <table class="tbl">
      <thead>
        <tr>
          <th style="width:36px;text-align:center">
            <input type="checkbox" id="check-all" title="Pilih Semua Siswa" style="cursor:pointer">
          </th>
          <th>Nama Siswa</th>
          <th>NIS / NISN</th>
          <th>Kelas &amp; Jurusan</th>
          <th>Wali Murid</th>
          <th>Status</th>
          <th style="text-align:right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($students as $student)
          <tr>
            <td style="text-align:center">
              <input type="checkbox" class="student-cb" value="{{ $student->id }}" style="cursor:pointer">
            </td>
            <td>
              <div style="display:flex;align-items:center;gap:10px">
                <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:11px;flex:none">
                  {{ strtoupper(substr($student->full_name, 0, 2)) }}
                </div>
                <div>
                  <b style="color:var(--text);font-size:14px">{{ $student->full_name }}</b>
                  <div style="margin-top:2px">
                    <span style="font-size:11.5px;font-weight:600;color:{{ $student->gender === 'L' ? '#0284c7' : '#db2777' }}">
                      {{ $student->gender === 'L' ? '♂ Laki-laki' : '♀ Perempuan' }}
                    </span>
                  </div>
                </div>
              </div>
            </td>
            <td>
              <span>{{ $student->nis ?? '—' }}</span>
              @if ($student->nisn)
                <br><small style="color:var(--muted)">{{ $student->nisn }}</small>
              @endif
            </td>
            <td>
              @if ($student->schoolClass)
                <span class="badge badge-blue">Kelas {{ $student->schoolClass->name }}</span>
              @else
                <span style="color:var(--muted);font-size:12px">—</span>
              @endif
              @if ($student->major)
                <span class="badge badge-ink" style="font-size:10px;margin-left:4px">{{ $student->major->code }}</span>
              @endif
            </td>
            <td>{{ $student->guardian?->full_name ?? '—' }}</td>
            <td>
              @php
                $statusClass = match ($student->status) {
                  'aktif' => 'badge-ok',
                  'lulus' => 'badge-blue',
                  'pindah' => 'badge-warn',
                  default => 'badge-bad'
                };
              @endphp
              <span class="badge {{ $statusClass }}">{{ ucfirst($student->status) }}</span>
            </td>
            <td>
              <div class="actions">
                <button class="btn btn-sm" data-dialog="#dlg-{{ $student->id }}">Edit</button>
                <form method="POST" action="{{ route('students.destroy', $student) }}" data-confirm="Hapus permanen data siswa {{ $student->full_name }}?">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="empty">Tidak ada data siswa yang cocok dengan filter.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- MODAL IMPORT EXCEL SISWA --}}
<dialog id="import-student" class="modal glass">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
      <h3 style="margin:0;font-size:17px;color:var(--text);display:flex;align-items:center;gap:8px">
        <svg viewBox="0 0 24 24" style="width:20px;height:20px;stroke:var(--accent);fill:none;stroke-width:2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
        Import Data Siswa (Excel .xlsx)
      </h3>
      <button type="button" data-close class="modal-close" aria-label="Tutup">✕</button>
    </div>

    <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data" class="stack">
      @csrf
      <p style="font-size:13px;color:var(--muted-2);margin-bottom:10px">
        Unggah file Microsoft Excel (<b>.xlsx</b>). Sistem otomatis mendeteksi kolom:
        <b>NIS, NISN, NAMA_LENGKAP, JENIS_KELAMIN (L/P), KELAS, JURUSAN, TANGGAL_LAHIR, STATUS, NAMA_WALI, NO_HP_WALI, HUBUNGAN_WALI</b>.
      </p>

      <div class="field">
        <label>Pilih File Excel (.xlsx)</label>
        <input type="file" name="file" class="input" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,.csv" required style="padding:10px">
      </div>

      <div style="display:flex;align-items:center;justify-content:space-between;margin-top:14px">
        <a href="{{ route('students.template') }}" class="btn btn-sm" style="font-size:12px">
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

{{-- Dialog Edit Siswa --}}
@foreach ($students as $student)
<dialog class="dlg" id="dlg-{{ $student->id }}">
  <h3>Perbarui Data Siswa — {{ $student->full_name }}</h3>
  <form method="POST" action="{{ route('students.update', $student) }}" class="stack">
    @csrf @method('PUT')
    <div class="field">
      <label>Nama Lengkap *</label>
      <input name="full_name" class="input" value="{{ $student->full_name }}" required>
    </div>
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
      <div class="field"><label>Tanggal Lahir</label><input type="date" name="birth_date" class="input" value="{{ $student->birth_date?->format('Y-m-d') }}"></div>
    </div>
    <div class="form-grid" style="grid-template-columns:1fr 1fr">
      <div class="field">
        <label>Kelas</label>
        <select name="class_id" class="select">
          <option value="">— Belum ditempatkan —</option>
          @foreach ($classes as $class)
            <option value="{{ $class->id }}" {{ $student->class_id === $class->id ? 'selected' : '' }}>Kelas {{ $class->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="field">
        <label>Jurusan</label>
        <select name="major_id" class="select">
          <option value="">— Belum ditentukan —</option>
          @foreach ($majors as $m)
            <option value="{{ $m->id }}" {{ $student->major_id === $m->id ? 'selected' : '' }}>{{ $m->code }} - {{ $m->name }}</option>
          @endforeach
        </select>
      </div>
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
        @foreach (['aktif' => 'Aktif', 'lulus' => 'Lulus', 'pindah' => 'Pindah', 'keluar' => 'Keluar'] as $st => $label)
          <option value="{{ $st }}" {{ $student->status === $st ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
    </div>
    <div class="dlg-actions">
      <button type="button" class="btn" data-close>Batal</button>
      <button class="btn btn-ink" data-loading="Menyimpan Perubahan...">Simpan Perubahan</button>
    </div>
  </form>
</dialog>
@endforeach

{{-- Modal Bulk Kenaikan / Pindah Kelas --}}
<dialog id="dlg-bulk-promote" class="modal glass">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
      <h3 style="margin:0;font-size:17px;color:var(--text);display:flex;align-items:center;gap:8px">
        <svg viewBox="0 0 24 24" style="width:20px;height:20px;stroke:var(--accent);fill:none;stroke-width:2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
        Kenaikan Kelas / Pindah Rombel Massal
      </h3>
      <button type="button" data-close class="modal-close" aria-label="Tutup">✕</button>
    </div>
    <form method="POST" action="{{ route('students.promote') }}" id="form-bulk-promote" class="stack">
      @csrf
      <p style="font-size:13px;color:var(--muted);margin-bottom:12px">
        Anda akan memindahkan <b id="promote-count-text">0</b> siswa terpilih ke rombel/kelas tujuan baru.
      </p>

      <div class="field">
        <label>Pilih Rombel / Kelas Tujuan *</label>
        <select name="target_class_id" class="select" required>
          <option value="">— Pilih Kelas Baru —</option>
          @foreach ($classes as $c)
            <option value="{{ $c->id }}">Kelas {{ $c->name }} ({{ $c->academicYear?->year_label ?? 'Aktif' }})</option>
          @endforeach
        </select>
      </div>

      <div id="promote-hidden-inputs"></div>

      <div class="modal-actions" style="margin-top:16px">
        <button type="button" class="btn" data-close>Batal</button>
        <button class="btn btn-ink" data-loading="Memproses Kenaikan Kelas...">🎓 Proses Kenaikan Kelas</button>
      </div>
    </form>
  </div>
</dialog>

{{-- Form Kelulusan Massal Tersembunyi --}}
<form method="POST" action="{{ route('students.graduate') }}" id="form-bulk-graduate" class="hidden">
  @csrf
  <div id="graduate-hidden-inputs"></div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const checkAll = document.getElementById('check-all');
  const cbs = document.querySelectorAll('.student-cb');
  const toolbar = document.getElementById('bulk-toolbar');
  const countEl = document.getElementById('selected-count');

  function updateState() {
    const selected = Array.from(cbs).filter(cb => cb.checked);
    const count = selected.length;
    if (countEl) countEl.textContent = count;
    if (toolbar) toolbar.style.display = count > 0 ? 'flex' : 'none';
    if (checkAll) {
      checkAll.checked = count > 0 && count === cbs.length;
      checkAll.indeterminate = count > 0 && count < cbs.length;
    }
  }

  if (checkAll) {
    checkAll.addEventListener('change', () => {
      cbs.forEach(cb => cb.checked = checkAll.checked);
      updateState();
    });
  }

  cbs.forEach(cb => cb.addEventListener('change', updateState));

  window.openBulkPromote = function() {
    const selected = Array.from(cbs).filter(cb => cb.checked);
    if (!selected.length) return;

    const container = document.getElementById('promote-hidden-inputs');
    container.innerHTML = '';
    selected.forEach(cb => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'student_ids[]';
      input.value = cb.value;
      container.appendChild(input);
    });

    const countText = document.getElementById('promote-count-text');
    if (countText) countText.textContent = selected.length;

    const dlg = document.getElementById('dlg-bulk-promote');
    if (dlg) dlg.showModal();
  };

  window.submitBulkGraduation = function() {
    const selected = Array.from(cbs).filter(cb => cb.checked);
    if (!selected.length) return;

    if (!confirm(`Apakah Anda yakin ingin meluluskan ${selected.length} siswa terpilih?`)) {
      return;
    }

    const form = document.getElementById('form-bulk-graduate');
    const container = document.getElementById('graduate-hidden-inputs');
    container.innerHTML = '';
    selected.forEach(cb => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'student_ids[]';
      input.value = cb.value;
      container.appendChild(input);
    });

    form.submit();
  };
});
</script>
@endsection
