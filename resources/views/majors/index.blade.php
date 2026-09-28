@extends('layouts.app')
@section('title', 'Data Jurusan & Program Keahlian')

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
      <span class="cs-pill"><span class="dot"></span>Akademik Terpadu</span>
      <span style="font-size:12px;color:var(--muted)">Program Keahlian / Peminatan</span>
    </div>
    <h1>Data Jurusan &amp; Keahlian</h1>
    <div class="sub">Kelola konsentrasi keahlian, penugasan Kepala Program (Kaprog), rombel dan siswa terhubung.</div>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <a href="{{ route('majors.template') }}" class="btn btn-sm" title="Unduh format spreadsheet">
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
      Template Excel (.xlsx)
    </a>
    <button type="button" class="btn btn-sm" data-dialog="#import-major">
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
      Import Excel (.xlsx)
    </button>
    <button type="button" class="btn btn-sm btn-ink" data-dialog="#create-major">
      + Tambah Jurusan
    </button>
  </div>
</div>

<div class="glass panel" style="margin-top:16px">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px">
    <h2 class="panel-title" style="margin:0">
      <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20M4 4.5A2.5 2.5 0 016.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15z"/></svg>
      Daftar Jurusan Aktif ({{ $majors->count() }})
    </h2>
    <span class="cs-pill" style="font-size:11px">{{ auth()->user()->school->name }}</span>
  </div>

  <div class="tbl-wrap">
    <table class="tbl">
      <thead>
        <tr>
          <th style="width:120px">Kode</th>
          <th>Nama Program Keahlian</th>
          <th>Kepala Program (Kaprog)</th>
          <th style="text-align:center">Rombel</th>
          <th style="text-align:center">Total Siswa</th>
          <th style="text-align:right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($majors as $m)
          <tr>
            <td>
              <span class="badge badge-ink" style="font-weight:700;letter-spacing:0.04em">
                {{ $m->code }}
              </span>
            </td>
            <td>
              <b style="color:var(--text);font-size:14px">{{ $m->name }}</b>
              @if ($m->description)
                <div style="font-size:12px;color:var(--muted);margin-top:2px">{{ $m->description }}</div>
              @endif
            </td>
            <td>
              @if ($m->headOfMajor)
                <div style="display:flex;align-items:center;gap:6px">
                  <span class="icon-chip icon-sm is-blue" style="width:26px;height:26px">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="7.5" r="3.5"/><path d="M5 20.5c.7-4.5 3.2-7 7-7s6.3 2.5 7 7"/></svg>
                  </span>
                  <div>
                    <span style="font-weight:600;color:var(--text)">{{ $m->headOfMajor->full_name }}</span>
                    @if ($m->headOfMajor->nip)
                      <small style="display:block;color:var(--muted);font-size:11px">NIP. {{ $m->headOfMajor->nip }}</small>
                    @endif
                  </div>
                </div>
              @else
                <span style="color:var(--muted);font-size:12px">— Belum ditentukan</span>
              @endif
            </td>
            <td style="text-align:center">
              <span class="badge badge-blue">{{ $m->classes_count }} Kelas</span>
            </td>
            <td style="text-align:center">
              <span class="badge badge-ok">{{ $m->students_count }} Siswa</span>
            </td>
            <td class="actions">
              <button type="button" class="btn btn-sm"
                data-dialog="#edit-major"
                data-field-code="{{ $m->code }}"
                data-field-name="{{ $m->name }}"
                data-field-description="{{ $m->description }}"
                data-field-head_of_major_id="{{ $m->head_of_major_id }}"
                data-action="{{ route('majors.update', $m) }}">
                Edit
              </button>
              <form action="{{ route('majors.destroy', $m) }}" method="POST" data-confirm="Hapus jurusan {{ $m->name }}? Rombel yang terhubung akan dilepas asosiasinya.">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="empty">
              Belum ada data jurusan. Silakan tambahkan jurusan manual atau gunakan fitur <b>Import Excel</b>.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- MODAL TAMBAH JURUSAN --}}
<dialog id="create-major" class="modal glass">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
      <h3 style="margin:0;font-size:17px;color:var(--text);display:flex;align-items:center;gap:8px">
        <svg viewBox="0 0 24 24" style="width:20px;height:20px;stroke:var(--accent);fill:none;stroke-width:2"><path d="M12 4v16m8-8H4"/></svg>
        Tambah Program Keahlian (Jurusan)
      </h3>
      <button type="button" data-close class="side-close" style="position:static">✕</button>
    </div>

    <form action="{{ route('majors.store') }}" method="POST" class="stack">
      @csrf
      <div class="two-col" style="gap:12px">
        <div class="field">
          <label>Kode Jurusan (Singkatan)</label>
          <input type="text" name="code" class="input" placeholder="Misal: RPL, TKJ, AKL" required maxlength="25">
        </div>
        <div class="field">
          <label>Kepala Program (Kaprog)</label>
          <select name="head_of_major_id" class="select">
            <option value="">— Pilih Guru / Kaprog (Opsional) —</option>
            @foreach ($teachers as $t)
              <option value="{{ $t->id }}">{{ $t->full_name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="field">
        <label>Nama Lengkap Jurusan</label>
        <input type="text" name="name" class="input" placeholder="Misal: Rekayasa Perangkat Lunak" required maxlength="120">
      </div>

      <div class="field">
        <label>Keterangan / Fokus Bidang</label>
        <textarea name="description" class="textarea" rows="2" placeholder="Fokus kompetensi keahlian..."></textarea>
      </div>

      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:10px">
        <button type="button" data-close class="btn btn-sm">Batal</button>
        <button type="submit" class="btn btn-sm btn-ink">Simpan Jurusan</button>
      </div>
    </form>
  </div>
</dialog>

{{-- MODAL EDIT JURUSAN --}}
<dialog id="edit-major" class="modal glass">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
      <h3 style="margin:0;font-size:17px;color:var(--text);display:flex;align-items:center;gap:8px">
        <svg viewBox="0 0 24 24" style="width:20px;height:20px;stroke:var(--accent);fill:none;stroke-width:2"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
        Edit Program Keahlian
      </h3>
      <button type="button" data-close class="side-close" style="position:static">✕</button>
    </div>

    <form id="editMajorForm" action="" method="POST" class="stack">
      @csrf
      @method('PUT')
      <div class="two-col" style="gap:12px">
        <div class="field">
          <label>Kode Jurusan</label>
          <input type="text" name="code" class="input" required maxlength="25">
        </div>
        <div class="field">
          <label>Kepala Program (Kaprog)</label>
          <select name="head_of_major_id" class="select">
            <option value="">— Tidak Ada / Pilih —</option>
            @foreach ($teachers as $t)
              <option value="{{ $t->id }}">{{ $t->full_name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="field">
        <label>Nama Lengkap Jurusan</label>
        <input type="text" name="name" class="input" required maxlength="120">
      </div>

      <div class="field">
        <label>Keterangan / Fokus Bidang</label>
        <textarea name="description" class="textarea" rows="2"></textarea>
      </div>

      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:10px">
        <button type="button" data-close class="btn btn-sm">Batal</button>
        <button type="submit" class="btn btn-sm btn-ink">Perbarui Data</button>
      </div>
    </form>
  </div>
</dialog>

{{-- MODAL IMPORT EXCEL JURUSAN --}}
<dialog id="import-major" class="modal glass">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
      <h3 style="margin:0;font-size:17px;color:var(--text);display:flex;align-items:center;gap:8px">
        <svg viewBox="0 0 24 24" style="width:20px;height:20px;stroke:var(--accent);fill:none;stroke-width:2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
        Import Data Jurusan (Excel .xlsx)
      </h3>
      <button type="button" data-close class="side-close" style="position:static">✕</button>
    </div>

    <form action="{{ route('majors.import') }}" method="POST" enctype="multipart/form-data" class="stack">
      @csrf
      <p style="font-size:13px;color:var(--muted-2);margin-bottom:10px">
        Unggah file Microsoft Excel (<b>.xlsx</b>) berisi daftar jurusan. Format kolom: <b>KODE_JURUSAN, NAMA_JURUSAN, NIP_KAPROG, KETERANGAN</b>.
      </p>

      <div class="field">
        <label>Pilih File Excel (.xlsx)</label>
        <input type="file" name="file" class="input" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,.csv" required style="padding:10px">
      </div>

      <div style="display:flex;align-items:center;justify-content:space-between;margin-top:14px">
        <a href="{{ route('majors.template') }}" class="btn btn-sm" style="font-size:12px">
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

<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-dialog="#edit-major"]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var act = btn.getAttribute('data-action');
      if (act) {
        document.getElementById('editMajorForm').action = act;
      }
    });
  });
});
</script>
@endsection
