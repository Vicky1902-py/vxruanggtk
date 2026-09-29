@extends('layouts.app')
@section('title', 'Buku Agenda Persuratan & SK')

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <span class="cs-pill" style="font-size:11px;padding:3px 12px"><span class="dot" style="background:#0284c7"></span> Tata Usaha &amp; Kearsipan</span>
      <span style="font-size:12px;color:var(--muted)">Sistem Administrasi Persuratan &amp; Generator Nomor Otomatis</span>
    </div>
    <h1>Buku Agenda Persuratan &amp; SK</h1>
    <div class="sub">Kelola penomoran otomatis berjenjang untuk semua jenis surat dinas, surat tugas, keterangan, dan Surat Keputusan (SK) Kepala Sekolah.</div>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <a href="{{ route('letters.export', request()->query()) }}" class="btn btn-sm" style="display:flex;align-items:center;gap:6px">
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      Ekspor Excel (.xlsx)
    </a>
    <a href="{{ route('letter-types.index') }}" class="btn btn-sm" style="display:flex;align-items:center;gap:6px">
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
      Format Penomoran
    </a>
    <a href="{{ route('letters.create') }}" class="btn btn-sm btn-ink" style="display:flex;align-items:center;gap:6px">
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      ⚡ Buat Surat / SK Baru
    </a>
  </div>
</div>

{{-- STATISTIK KEARSIPAN PERSURATAN --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:16px;margin-bottom:20px">
  <div class="glass panel" style="display:flex;align-items:center;gap:16px;padding:18px 20px">
    <div style="width:48px;height:48px;border-radius:12px;background:#e0f2fe;display:flex;align-items:center;justify-content:center;color:#0284c7;flex:none">
      <svg viewBox="0 0 24 24" style="width:24px;height:24px;stroke:currentColor;fill:none;stroke-width:2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
    </div>
    <div>
      <div style="font-size:12px;color:var(--muted)">Total Seluruh Arsip Surat</div>
      <div style="font-size:24px;font-weight:800;color:var(--text);margin-top:2px">{{ $totalAll }}</div>
    </div>
  </div>

  <div class="glass panel" style="display:flex;align-items:center;gap:16px;padding:18px 20px">
    <div style="width:48px;height:48px;border-radius:12px;background:#fef3c7;display:flex;align-items:center;justify-content:center;color:#b45309;flex:none">
      <svg viewBox="0 0 24 24" style="width:24px;height:24px;stroke:currentColor;fill:none;stroke-width:2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
    </div>
    <div>
      <div style="font-size:12px;color:var(--muted)">Surat Keputusan (SK) Kepala Sekolah</div>
      <div style="font-size:24px;font-weight:800;color:#b45309;margin-top:2px">{{ $totalSK }}</div>
    </div>
  </div>

  <div class="glass panel" style="display:flex;align-items:center;gap:16px;padding:18px 20px">
    <div style="width:48px;height:48px;border-radius:12px;background:#ecfdf5;display:flex;align-items:center;justify-content:center;color:#047857;flex:none">
      <svg viewBox="0 0 24 24" style="width:24px;height:24px;stroke:currentColor;fill:none;stroke-width:2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
    </div>
    <div>
      <div style="font-size:12px;color:var(--muted)">Surat Keluar / Dinas / Keterangan</div>
      <div style="font-size:24px;font-weight:800;color:#047857;margin-top:2px">{{ $totalSuratKeluar }}</div>
    </div>
  </div>
</div>

{{-- FILTER & PENCARIAN --}}
<div class="glass panel" style="margin-bottom:20px;padding:16px 20px">
  <form method="GET" action="{{ route('letters.index') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;justify-content:space-between">
    {{-- Tabs Kategori --}}
    <div style="display:flex;gap:6px;flex-wrap:wrap">
      <a href="{{ route('letters.index', array_merge(request()->query(), ['category' => null])) }}" class="btn btn-sm {{ !request('category') ? 'btn-ink' : '' }}" style="font-size:12px;padding:6px 14px">
        Semua ({{ $totalAll }})
      </a>
      <a href="{{ route('letters.index', array_merge(request()->query(), ['category' => 'sk'])) }}" class="btn btn-sm {{ request('category') === 'sk' ? 'btn-ink' : '' }}" style="font-size:12px;padding:6px 14px">
        📜 Buku SK ({{ $totalSK }})
      </a>
      <a href="{{ route('letters.index', array_merge(request()->query(), ['category' => 'surat_keluar'])) }}" class="btn btn-sm {{ request('category') === 'surat_keluar' ? 'btn-ink' : '' }}" style="font-size:12px;padding:6px 14px">
        ✉️ Surat Keluar Dinas ({{ $totalSuratKeluar }})
      </a>
    </div>

    {{-- Dropdown & Pencarian --}}
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
      <select name="type_id" class="select" onchange="this.form.submit()" style="font-size:12px;height:36px;padding:4px 10px;min-width:180px">
        <option value="">Semua Jenis Persuratan</option>
        @foreach ($letterTypes as $lt)
          <option value="{{ $lt->id }}" {{ request('type_id') == $lt->id ? 'selected' : '' }}>
            {{ $lt->name }} ({{ $lt->code }})
          </option>
        @endforeach
      </select>

      <select name="year" class="select" onchange="this.form.submit()" style="font-size:12px;height:36px;padding:4px 10px;width:110px">
        <option value="all" {{ request('year') === 'all' ? 'selected' : '' }}>Semua Thn</option>
        @foreach (range(date('Y'), date('Y') - 3) as $y)
          <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
        @endforeach
      </select>

      <div style="display:flex;align-items:center;position:relative">
        <input type="text" name="q" class="input" placeholder="Cari nomor, perihal, tujuan..." value="{{ request('q') }}" style="height:36px;font-size:12px;padding-right:32px;width:240px">
        <button type="submit" style="position:absolute;right:8px;background:none;border:none;cursor:pointer;color:var(--muted)">🔍</button>
      </div>

      @if (request()->hasAny(['category', 'type_id', 'q', 'year']))
        <a href="{{ route('letters.index') }}" class="btn btn-sm" style="font-size:12px;height:36px;display:flex;align-items:center">✕ Reset</a>
      @endif
    </div>
  </form>
</div>

{{-- TABEL BUKU AGENDA SURAT & SK --}}
<div class="glass panel" style="padding:0;overflow:hidden">
  <div class="table-wrap">
    <table class="tbl">
      <thead>
        <tr>
          <th style="width:45px;text-align:center">No</th>
          <th style="width:230px">Nomor Surat / SK</th>
          <th style="width:110px">Tanggal</th>
          <th style="width:180px">Jenis Persuratan</th>
          <th>Perihal / Isi Ringkas</th>
          <th style="width:180px">Tujuan / Sasaran</th>
          <th style="width:100px;text-align:center">Status</th>
          <th style="width:150px;text-align:right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($letters as $idx => $l)
          <tr>
            <td style="text-align:center;font-size:12px;color:var(--muted)">{{ $letters->firstItem() + $idx }}</td>
            <td>
              <div style="font-weight:700;font-family:monospace;font-size:13px;color:var(--text);display:flex;align-items:center;gap:6px">
                @if ($l->category === 'sk')
                  <span class="badge" style="background:#fef3c7;color:#b45309;font-size:10px;padding:2px 6px">SK</span>
                @else
                  <span class="badge" style="background:#e0f2fe;color:#0284c7;font-size:10px;padding:2px 6px">DINAS</span>
                @endif
                {{ $l->reference_number }}
              </div>
            </td>
            <td style="font-size:12.5px;color:var(--muted)">
              {{ $l->letter_date?->translatedFormat('d M Y') }}
            </td>
            <td>
              <span style="font-size:12.5px;font-weight:600;color:var(--text)">{{ $l->letterType?->name ?? '—' }}</span>
              <div style="font-size:11px;color:var(--muted)">Kode: {{ $l->letterType?->classification_code ?? '-' }}</div>
            </td>
            <td>
              <a href="{{ route('letters.show', $l) }}" style="text-decoration:none;color:var(--text);font-weight:600;display:block">
                {{ $l->subject }}
              </a>
              @if ($l->student)
                <div style="font-size:11.5px;color:var(--accent);margin-top:2px">
                  🎓 Siswa: {{ $l->student->full_name }} ({{ $l->student->schoolClass?->name ?? 'NIS: ' . $l->student->nis }})
                </div>
              @elseif ($l->employee)
                <div style="font-size:11.5px;color:#10b981;margin-top:2px">
                  👨‍🏫 GTK: {{ $l->employee->full_name }} ({{ $l->employee->position?->name ?? 'GTK' }})
                </div>
              @endif
            </td>
            <td style="font-size:12.5px;color:var(--text)">
              {{ $l->recipient ?: '—' }}
            </td>
            <td style="text-align:center">
              @if ($l->status === 'diterbitkan')
                <span class="badge badge-ok" style="font-size:11px">Diterbitkan</span>
              @elseif ($l->status === 'draft')
                <span class="badge badge-warning" style="font-size:11px">Draft</span>
              @else
                <span class="badge badge-blue" style="font-size:11px">Arsip</span>
              @endif
            </td>
            <td>
              <div class="actions" style="justify-content:flex-end;gap:4px">
                <a href="{{ route('letters.print', $l) }}" target="_blank" class="btn btn-sm" title="Cetak Dokumen Resmi" style="height:30px;padding:0 8px;font-size:12px">
                  🖨️
                </a>
                <a href="{{ route('letters.show', $l) }}" class="btn btn-sm" title="Lihat Detail" style="height:30px;padding:0 8px;font-size:12px">
                  👁️
                </a>
                <a href="{{ route('letters.edit', $l) }}" class="btn btn-sm" title="Edit Isi Surat" style="height:30px;padding:0 8px;font-size:12px">
                  ✏️
                </a>
                <form method="POST" action="{{ route('letters.destroy', $l) }}" data-confirm="Hapus permanen surat {{ $l->reference_number }} dari buku agenda?">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger" title="Hapus" style="height:30px;padding:0 8px;font-size:12px">
                    🗑️
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="empty" style="padding:36px;text-align:center">
              <div style="font-size:28px;margin-bottom:8px">📭</div>
              <div style="font-weight:700;color:var(--text);font-size:15px">Belum Ada Catatan Persuratan</div>
              <div style="font-size:12.5px;color:var(--muted);margin-top:4px;max-width:400px;margin-left:auto;margin-right:auto">
                Mulai buat surat keluar atau SK baru dengan nomor urut cerdas otomatis melalui tombol di bawah.
              </div>
              <a href="{{ route('letters.create') }}" class="btn btn-ink" style="margin-top:16px;display:inline-flex;align-items:center;gap:6px">
                ⚡ Buat Surat / SK Baru
              </a>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if ($letters->hasPages())
    <div style="padding:14px 20px;border-top:1px solid var(--line-light)">
      {{ $letters->links() }}
    </div>
  @endif
</div>
@endsection
