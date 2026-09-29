@extends('layouts.app')
@section('title', 'Kesiswaan & Kedisiplinan')

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <span class="cs-pill" style="font-size:11px;padding:3px 12px"><span class="dot" style="background:#ef4444"></span> Kesiswaan &amp; BK</span>
      <span style="font-size:12px;color:var(--muted)">Kedisiplinan, Perizinan, &amp; Bimbingan Konseling</span>
    </div>
    <h1>Kedisiplinan &amp; Kesiswaan</h1>
    <div class="sub">Pemantauan poin pelanggaran tata tertib, penerbitan surat izin siswa, serta rekam jejak bimbingan konseling &amp; prestasi.</div>
  </div>
  <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
    @if ($tab === 'pelanggaran')
      <button type="button" class="btn btn-sm btn-ink" data-dialog="#dialog-create-violation">
        <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        + Catat Pelanggaran
      </button>
      <a href="{{ route('welfare.export-violations') }}" class="btn btn-sm" title="Unduh rekap poin pelanggaran siswa ke Excel (.xlsx)">
        <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
        Export Pelanggaran (.xlsx)
      </a>
    @elseif ($tab === 'izin')
      <button type="button" class="btn btn-sm btn-ink" data-dialog="#dialog-create-permit">
        <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        + Terbitkan Surat Izin
      </button>
    @elseif ($tab === 'konseling')
      <button type="button" class="btn btn-sm btn-ink" data-dialog="#dialog-create-counseling">
        <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        + Catat BK &amp; Prestasi
      </button>
    @endif

    {{-- Switch Tab --}}
    <div style="display:flex;gap:6px;background:#ffffff;padding:4px;border-radius:var(--radius-pill);border:1px solid #cbd5e1;box-shadow:0 2px 8px rgba(0,0,0,0.04)">
      <a href="{{ route('welfare.index', ['tab' => 'pelanggaran']) }}" class="btn btn-sm {{ $tab === 'pelanggaran' ? 'btn-ink' : '' }}" style="height:32px;font-size:12px;border-radius:var(--radius-pill)">
        ⚠️ Pelanggaran
      </a>
      <a href="{{ route('welfare.index', ['tab' => 'izin']) }}" class="btn btn-sm {{ $tab === 'izin' ? 'btn-ink' : '' }}" style="height:32px;font-size:12px;border-radius:var(--radius-pill)">
        🎫 Perizinan
      </a>
      <a href="{{ route('welfare.index', ['tab' => 'konseling']) }}" class="btn btn-sm {{ $tab === 'konseling' ? 'btn-ink' : '' }}" style="height:32px;font-size:12px;border-radius:var(--radius-pill)">
        🌱 BK &amp; Prestasi
      </a>
    </div>
  </div>
</div>

@if ($tab === 'pelanggaran')
  {{-- TAB 1: PELANGGARAN KEDISIPLINAN FULL WIDTH --}}
  <div class="glass panel">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px">
      <h2 class="panel-title" style="margin:0">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Daftar Pelanggaran Tata Tertib Siswa
      </h2>
    </div>

    {{-- Filter Pelanggaran --}}
    <form method="GET" style="display:grid;grid-template-columns:1.5fr 1fr auto;gap:8px;margin-bottom:14px">
      <input type="hidden" name="tab" value="pelanggaran">
      <input type="text" name="q" class="input" value="{{ request('q') }}" placeholder="Cari siswa / kategori pelanggaran...">
      <select name="class_id" class="select" onchange="this.form.submit()">
        <option value="">Semua Kelas</option>
        @foreach ($classes as $c)
          <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>Kelas {{ $c->name }}</option>
        @endforeach
      </select>
      <button class="btn btn-sm btn-ink">Cari</button>
    </form>

    <div class="table-wrap" style="border:none;box-shadow:none">
      <table class="tbl">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Siswa</th>
            <th>Kategori</th>
            <th style="text-align:center">Poin</th>
            <th style="text-align:right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($violations as $v)
            <tr>
              <td style="font-size:12px;color:var(--muted)">{{ $v->incident_date?->format('d/m/Y') }}</td>
              <td>
                <b style="color:var(--text);font-size:13.5px">{{ $v->student?->full_name }}</b>
                <div style="font-size:11.5px;color:var(--muted)">
                  {{ $v->student?->schoolClass ? 'Kelas ' . $v->student->schoolClass->name : '—' }}
                </div>
              </td>
              <td>
                <span style="font-weight:600;color:var(--text)">{{ $v->category }}</span>
                @if ($v->description)
                  <div style="font-size:11.5px;color:var(--muted)">{{ $v->description }}</div>
                @endif
              </td>
              <td style="text-align:center">
                <span class="badge badge-bad" style="font-weight:700">+{{ $v->points }}</span>
              </td>
              <td style="text-align:right">
                <form method="POST" action="{{ route('welfare.violations.destroy', $v) }}" data-confirm="Hapus catatan pelanggaran ini?">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="empty">Belum ada data pelanggaran tercatat.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($violations->hasPages())
      <div style="margin-top:10px">{{ $violations->links() }}</div>
    @endif
  </div>

  {{-- MODAL CATAT PELANGGARAN --}}
  <dialog id="dialog-create-violation" class="modal glass">
    <div class="modal-box">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <h3 style="margin:0;font-size:17px;color:var(--text);display:flex;align-items:center;gap:8px">
          <svg viewBox="0 0 24 24" style="width:20px;height:20px;stroke:var(--accent);fill:none;stroke-width:2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          Catat Pelanggaran Siswa
        </h3>
        <button type="button" data-close class="modal-close" aria-label="Tutup">✕</button>
      </div>
      <form method="POST" action="{{ route('welfare.violations.store') }}" class="stack">
        @csrf
        <div class="field">
          <label>Pilih Peserta Didik *</label>
          <select name="student_id" class="select" required>
            <option value="">— Pilih Siswa —</option>
            @foreach ($students as $st)
              <option value="{{ $st->id }}">
                {{ $st->full_name }} ({{ $st->schoolClass ? 'Kelas ' . $st->schoolClass->name : 'Tanpa Rombel' }})
              </option>
            @endforeach
          </select>
        </div>

        <div class="form-grid" style="grid-template-columns:1.5fr 1fr">
          <div class="field">
            <label>Kategori Pelanggaran *</label>
            <input type="text" name="category" class="input" placeholder="mis. Terlambat Masuk / Atribut Tidak Lengkap" list="category-list" required>
            <datalist id="category-list">
              <option value="Keterlambatan Masuk">
              <option value="Atribut Seragam Tidak Lengkap">
              <option value="Bolos Pelajaran / Jam Sekolah">
              <option value="Penggunaan Ponsel Tanpa Izin">
              <option value="Merokok di Lingkungan Sekolah">
              <option value="Perkelahian / Tindakan Asusila">
              <option value="Merusak Fasilitas Sekolah">
            </datalist>
          </div>
          <div class="field">
            <label>Poin Pelanggaran *</label>
            <input type="number" name="points" class="input" min="1" max="100" value="5" required>
          </div>
        </div>

        <div class="field">
          <label>Tanggal Kejadian *</label>
          <input type="date" name="incident_date" class="input" value="{{ date('Y-m-d') }}" required>
        </div>

        <div class="field">
          <label>Keterangan Kejadian</label>
          <textarea name="description" class="input" rows="3" placeholder="Jelaskan kronologi singkat atau barang bukti yang diamankan..."></textarea>
        </div>

        <div class="modal-actions" style="margin-top:16px">
          <button type="button" class="btn" data-close>Batal</button>
          <button class="btn btn-ink" data-loading="Menyimpan...">💾 Simpan Catatan Pelanggaran</button>
        </div>
      </form>
    </div>
  </dialog>

@elseif ($tab === 'izin')
  {{-- TAB 2: PERIZINAN SISWA FULL WIDTH --}}
  <div class="glass panel">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px">
      <h2 class="panel-title" style="margin:0">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Daftar Perizinan Siswa
      </h2>
    </div>

    <div class="table-wrap" style="border:none;box-shadow:none">
      <table class="tbl">
        <thead>
          <tr>
            <th>Siswa</th>
            <th>Jenis Izin</th>
            <th>Waktu</th>
            <th>Status</th>
            <th style="text-align:right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($permits as $p)
            <tr>
              <td>
                <b style="color:var(--text);font-size:13.5px">{{ $p->student?->full_name }}</b>
                <div style="font-size:11.5px;color:var(--muted)">{{ $p->student?->schoolClass ? 'Kelas ' . $p->student->schoolClass->name : '—' }}</div>
              </td>
              <td>
                <span class="badge badge-slate" style="text-transform:capitalize">{{ str_replace('_', ' ', $p->type) }}</span>
              </td>
              <td style="font-size:12px;color:var(--muted)">
                {{ $p->start_time?->translatedFormat('d M, H:i') }}
                @if ($p->end_time)
                  s/d {{ $p->end_time?->translatedFormat('d M, H:i') }}
                @endif
              </td>
              <td>
                @php
                  $pBadge = match ($p->status) {
                    'approved' => 'badge-ok',
                    'pending' => 'badge-warn',
                    default => 'badge-bad'
                  };
                @endphp
                <span class="badge {{ $pBadge }}">{{ ucfirst($p->status) }}</span>
                @if ($p->approver)
                  <div style="font-size:10.5px;color:var(--muted);margin-top:2px">oleh: {{ $p->approver->full_name }}</div>
                @endif
              </td>
              <td style="text-align:right">
                @if ($p->status !== 'approved')
                  <form method="POST" action="{{ route('welfare.permits.update-status', $p) }}" style="display:inline">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="approved">
                    <button class="btn btn-sm btn-ok">Setujui</button>
                  </form>
                @endif
                @if ($p->status !== 'rejected')
                  <form method="POST" action="{{ route('welfare.permits.update-status', $p) }}" style="display:inline">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="rejected">
                    <button class="btn btn-sm btn-warn">Tolak</button>
                  </form>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="empty">Belum ada pengajuan izin siswa.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if ($permits->hasPages())
      <div style="margin-top:10px">{{ $permits->links() }}</div>
    @endif
  </div>

  {{-- MODAL BUAT IZIN --}}
  <dialog id="dialog-create-permit" class="modal glass">
    <div class="modal-box">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <h3 style="margin:0;font-size:17px;color:var(--text);display:flex;align-items:center;gap:8px">
          <svg viewBox="0 0 24 24" style="width:20px;height:20px;stroke:var(--accent);fill:none;stroke-width:2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
          Penerbitan Surat Izin Siswa
        </h3>
        <button type="button" data-close class="modal-close" aria-label="Tutup">✕</button>
      </div>
      <form method="POST" action="{{ route('welfare.permits.store') }}" class="stack">
        @csrf
        <div class="field">
          <label>Peserta Didik *</label>
          <select name="student_id" class="select" required>
            <option value="">— Pilih Siswa —</option>
            @foreach ($students as $st)
              <option value="{{ $st->id }}">{{ $st->full_name }} ({{ $st->schoolClass ? 'Kelas ' . $st->schoolClass->name : '—' }})</option>
            @endforeach
          </select>
        </div>

        <div class="field">
          <label>Jenis Perizinan *</label>
          <select name="type" class="select" required>
            <option value="izin">Izin Tidak Masuk Sekolah</option>
            <option value="keluar">Izin Keluar Lingkungan Sekolah (Dispensasi)</option>
            <option value="pulang_awal">Izin Pulang Awal (Sakit/Keperluan Mendesak)</option>
          </select>
        </div>

        <div class="form-grid" style="grid-template-columns:1fr 1fr">
          <div class="field">
            <label>Waktu Mulai *</label>
            <input type="datetime-local" name="start_time" class="input" value="{{ date('Y-m-d\TH:i') }}" required>
          </div>
          <div class="field">
            <label>Waktu Berakhir</label>
            <input type="datetime-local" name="end_time" class="input">
          </div>
        </div>

        <div class="field">
          <label>Status Persetujuan Awal *</label>
          <select name="status" class="select" required>
            <option value="approved">🟢 Disetujui (Approved)</option>
            <option value="pending">🟡 Menunggu Konfirmasi (Pending)</option>
            <option value="rejected">🔴 Ditolak (Rejected)</option>
          </select>
        </div>

        <div class="modal-actions" style="margin-top:16px">
          <button type="button" class="btn" data-close>Batal</button>
          <button class="btn btn-ink" data-loading="Menyimpan Izin...">💾 Terbitkan Surat Izin</button>
        </div>
      </form>
    </div>
  </dialog>

@elseif ($tab === 'konseling')
  {{-- TAB 3: KONSELING & PRESTASI FULL WIDTH --}}
  <div class="glass panel">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:gap;gap:10px">
      <h2 class="panel-title" style="margin:0">
        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        Rekam Jejak BK &amp; Prestasi Siswa
      </h2>
    </div>

    <div class="table-wrap" style="border:none;box-shadow:none">
      <table class="tbl">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Siswa</th>
            <th>Jenis</th>
            <th>Catatan / Uraian</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($counselings as $c)
            <tr>
              <td style="font-size:12px;color:var(--muted)">{{ $c->session_date?->format('d/m/Y') }}</td>
              <td>
                <b style="color:var(--text);font-size:13.5px">{{ $c->student?->full_name }}</b>
                <div style="font-size:11.5px;color:var(--muted)">{{ $c->student?->schoolClass ? 'Kelas ' . $c->student->schoolClass->name : '—' }}</div>
              </td>
              <td>
                @if ($c->type === 'prestasi')
                  <span class="badge badge-ok">🏆 Prestasi</span>
                @else
                  <span class="badge badge-blue">🌱 Konseling</span>
                @endif
              </td>
              <td style="font-size:13px;color:var(--text)">
                {{ $c->notes }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="empty">Belum ada catatan konseling atau prestasi tercatat.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if ($counselings->hasPages())
      <div style="margin-top:10px">{{ $counselings->links() }}</div>
    @endif
  </div>

  {{-- MODAL CATAT KONSELING / PRESTASI --}}
  <dialog id="dialog-create-counseling" class="modal glass">
    <div class="modal-box">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <h3 style="margin:0;font-size:17px;color:var(--text);display:flex;align-items:center;gap:8px">
          <svg viewBox="0 0 24 24" style="width:20px;height:20px;stroke:var(--accent);fill:none;stroke-width:2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          Catat Bimbingan Konseling / Prestasi
        </h3>
        <button type="button" data-close class="modal-close" aria-label="Tutup">✕</button>
      </div>
      <form method="POST" action="{{ route('welfare.counseling.store') }}" class="stack">
        @csrf
        <div class="field">
          <label>Peserta Didik *</label>
          <select name="student_id" class="select" required>
            <option value="">— Pilih Siswa —</option>
            @foreach ($students as $st)
              <option value="{{ $st->id }}">{{ $st->full_name }} ({{ $st->schoolClass ? 'Kelas ' . $st->schoolClass->name : '—' }})</option>
            @endforeach
          </select>
        </div>

        <div class="form-grid" style="grid-template-columns:1fr 1fr">
          <div class="field">
            <label>Jenis Catatan *</label>
            <select name="type" class="select" required>
              <option value="konseling">🌱 Bimbingan Konseling (BK)</option>
              <option value="prestasi">🏆 Penghargaan &amp; Prestasi Juara</option>
            </select>
          </div>
          <div class="field">
            <label>Tanggal Sesi / Penghargaan *</label>
            <input type="date" name="session_date" class="input" value="{{ date('Y-m-d') }}" required>
          </div>
        </div>

        <div class="field">
          <label>Catatan / Notulen Sesi / Nama Lomba *</label>
          <textarea name="notes" class="input" rows="4" placeholder="Tuliskan ringkasan bimbingan, konseling psikologis, atau rincian kejuaraan yang diraih siswa..." required></textarea>
        </div>

        <div class="modal-actions" style="margin-top:16px">
          <button type="button" class="btn" data-close>Batal</button>
          <button class="btn btn-ink" data-loading="Menyimpan...">💾 Simpan Catatan</button>
        </div>
      </form>
    </div>
  </dialog>
@endif

@endsection
