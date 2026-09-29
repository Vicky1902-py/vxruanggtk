@extends('layouts.app')
@section('title', 'Detail Surat — ' . $letter->reference_number)

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <a href="{{ route('letters.index') }}" style="color:var(--muted);text-decoration:none;font-size:12px;display:flex;align-items:center;gap:4px">
        ← Kembali ke Buku Agenda
      </a>
      <span class="cs-pill" style="font-size:11px;padding:3px 12px"><span class="dot" style="background:#10b981"></span> Dokumen Resmi</span>
    </div>
    <h1>{{ $letter->subject }}</h1>
    <div class="sub">Nomor: <b style="font-family:monospace;color:var(--accent)">{{ $letter->reference_number }}</b> · Tanggal: {{ $letter->letter_date?->translatedFormat('d F Y') }}</div>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <a href="{{ route('letters.print', $letter) }}" target="_blank" class="btn btn-sm btn-ink" style="display:flex;align-items:center;gap:6px">
      🖨️ Cetak Surat Resmi
    </a>
    <a href="{{ route('letters.edit', $letter) }}" class="btn btn-sm" style="display:flex;align-items:center;gap:6px">
      ✏️ Edit Redaksi
    </a>
    <form method="POST" action="{{ route('letters.destroy', $letter) }}" data-confirm="Hapus permanen surat {{ $letter->reference_number }}?">
      @csrf @method('DELETE')
      <button type="submit" class="btn btn-sm btn-danger" style="display:flex;align-items:center;gap:4px">
        🗑️ Hapus
      </button>
    </form>
  </div>
</div>

{{-- METADATA PERSURATAN --}}
<div class="glass panel" style="margin-bottom:20px;padding:16px 20px">
  <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;font-size:13px">
    <div>
      <div style="color:var(--muted);font-size:11.5px">Kategori Buku Agenda:</div>
      <div style="font-weight:700;margin-top:2px">
        @if ($letter->category === 'sk')
          <span class="badge" style="background:#fef3c7;color:#b45309">📜 Surat Keputusan (SK)</span>
        @else
          <span class="badge" style="background:#e0f2fe;color:#0284c7">✉️ Surat Keluar / Dinas</span>
        @endif
      </div>
    </div>

    <div>
      <div style="color:var(--muted);font-size:11.5px">Jenis Persuratan:</div>
      <div style="font-weight:600;color:var(--text);margin-top:2px">{{ $letter->letterType?->name ?? '—' }}</div>
    </div>

    <div>
      <div style="color:var(--muted);font-size:11.5px">Tujuan / Kepada Yth.:</div>
      <div style="font-weight:600;color:var(--text);margin-top:2px">{{ $letter->recipient ?: '—' }}</div>
    </div>

    <div>
      <div style="color:var(--muted);font-size:11.5px">Status Surat:</div>
      <div style="margin-top:2px">
        @if ($letter->status === 'diterbitkan')
          <span class="badge badge-ok">Diterbitkan</span>
        @elseif ($letter->status === 'draft')
          <span class="badge badge-warning">Draft</span>
        @else
          <span class="badge badge-blue">Arsip</span>
        @endif
      </div>
    </div>

    @if ($letter->student)
      <div>
        <div style="color:var(--muted);font-size:11.5px">Data Siswa Terkait:</div>
        <div style="font-weight:600;color:var(--accent);margin-top:2px">
          {{ $letter->student->full_name }} (NIS: {{ $letter->student->nis ?? '—' }} · {{ $letter->student->schoolClass?->name ?? '—' }})
        </div>
      </div>
    @endif

    @if ($letter->employee)
      <div>
        <div style="color:var(--muted);font-size:11.5px">Data GTK Terkait:</div>
        <div style="font-weight:600;color:#10b981;margin-top:2px">
          {{ $letter->employee->full_name }} ({{ $letter->employee->position?->name ?? 'GTK' }})
        </div>
      </div>
    @endif
  </div>
</div>

{{-- SIMULASI / PRATINJAU DOKUMEN CETAK A4 --}}
<div class="glass panel" style="background:#52525b;padding:30px;display:flex;justify-content:center;border:none">
  <div style="background:#ffffff;color:#0f172a;width:100%;max-width:760px;min-height:900px;padding:48px 56px;box-shadow:0 8px 30px rgba(0,0,0,0.3);border-radius:2px;font-family:'Segoe UI', Arial, sans-serif;font-size:13.5px;line-height:1.6">
    
    {{-- KOP SURAT RESMI SEKOLAH DUAL LOGO --}}
    @include('partials.kop-surat', ['school' => $school])

    {{-- KEPALA SURAT (NOMOR, LAMPIRAN, PERIHAL & TANGGAL) --}}
    @if ($letter->category !== 'sk')
      <div style="display:flex;justify-content:space-between;margin-top:16px;margin-bottom:24px;font-size:13px">
        <table style="border-collapse:collapse">
          <tr>
            <td style="width:75px">Nomor</td>
            <td style="width:12px">:</td>
            <td><b>{{ $letter->reference_number }}</b></td>
          </tr>
          <tr>
            <td>Lampiran</td>
            <td>:</td>
            <td>—</td>
          </tr>
          <tr>
            <td>Perihal</td>
            <td>:</td>
            <td><b>{{ $letter->subject }}</b></td>
          </tr>
        </table>

        <div style="text-align:right">
          <div>{{ $school->city ?? 'Tempat' }}, {{ $letter->letter_date?->translatedFormat('d F Y') }}</div>
          <div style="margin-top:16px;text-align:left">
            <div>Kepada Yth.</div>
            <div><b>{{ $letter->recipient ?: 'Bapak / Ibu / Sdr' }}</b></div>
            <div>di Tempat</div>
          </div>
        </div>
      </div>
    @endif

    {{-- ISI SURAT --}}
    <div style="margin:24px 0;line-height:1.7;color:#1e293b;text-align:justify">
      {!! $letter->content !!}
    </div>

    {{-- TANDA TANGAN & PENGESAHAN KEPALA SEKOLAH --}}
    @if ($letter->signed_by_principal)
      <div style="margin-top:40px;display:flex;justify-content:flex-end">
        <div style="width:260px;text-align:center;font-size:12.5px;color:#0f172a">
          <div>{{ $school->city ?? 'Kota' }}, {{ $letter->letter_date?->translatedFormat('d F Y') }}</div>
          <div style="font-weight:700;margin-top:2px">{{ $school->principal_title ?: 'Kepala Sekolah' }}</div>

          @if ($school->signature_url)
            <div style="height:65px;display:flex;align-items:center;justify-content:center;margin:4px 0">
              <img src="{{ asset($school->signature_url) }}" alt="Tanda Tangan / Stempel" style="max-height:60px;max-width:160px;object-fit:contain">
            </div>
          @else
            <div style="height:65px"></div>
          @endif

          <div style="border-bottom:1px solid #0f172a;font-weight:800;padding-bottom:2px">
            {{ $school->principal_name ?? 'Kepala Sekolah' }}
          </div>
          @if ($school->principal_nip)
            <div style="font-size:11px;color:#334155;margin-top:2px">NIP. {{ $school->principal_nip }}</div>
          @endif
        </div>
      </div>
    @endif

  </div>
</div>
@endsection
