<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>{{ $letter->subject }} — {{ $letter->reference_number }}</title>
  <style>
    * { box-sizing: border-box; }
    body {
      background: #f1f5f9;
      color: #0f172a;
      margin: 0;
      padding: 30px;
      font-family: 'Times New Roman', Times, serif;
      font-size: 12pt;
      line-height: 1.5;
      display: flex;
      justify-content: center;
    }
    .sheet {
      background: #ffffff;
      width: 210mm;
      min-height: 297mm;
      padding: 20mm 25mm 25mm 25mm;
      margin: 0 auto;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      position: relative;
    }
    .btn-print {
      position: fixed;
      top: 20px;
      right: 20px;
      background: #0284c7;
      color: #fff;
      padding: 10px 20px;
      border-radius: 6px;
      border: none;
      font-family: 'Segoe UI', Arial, sans-serif;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 4px 14px rgba(2,132,199,0.35);
      z-index: 1000;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .btn-print:hover { background: #0369a1; }
    .letter-body {
      margin: 20px 0;
      text-align: justify;
      line-height: 1.6;
    }
    .letter-body p {
      margin: 0 0 10pt 0;
    }
    .letter-body table {
      border-collapse: collapse;
    }
    .signatures {
      margin-top: 35px;
      display: flex;
      justify-content: flex-end;
      page-break-inside: avoid;
    }
    .sig-box {
      width: 270px;
      text-align: center;
      font-size: 11pt;
    }
    @page {
      size: A4;
      margin: 15mm;
    }
    @media print {
      body {
        background: transparent !important;
        padding: 0 !important;
      }
      .sheet {
        box-shadow: none !important;
        padding: 0 !important;
        width: 100% !important;
        min-height: auto !important;
        margin: 0 !important;
      }
      .btn-print {
        display: none !important;
      }
    }
  </style>
</head>
<body>

<button class="btn-print" onclick="window.print()">
  🖨️ Cetak Dokumen (A4)
</button>

<div class="sheet">
  {{-- KOP SURAT RESMI KEDINASAN --}}
  @include('partials.kop-surat', ['school' => $school])

  {{-- FORMAT SURAT KELUAR DINAS (BUKAN SK) --}}
  @if ($letter->category !== 'sk')
    <div style="display:flex;justify-content:space-between;margin-top:16px;margin-bottom:20px;font-size:11.5pt">
      <table style="border-collapse:collapse;line-height:1.4">
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
          <td style="vertical-align:top">Perihal</td>
          <td style="vertical-align:top">:</td>
          <td style="vertical-align:top"><b>{{ $letter->subject }}</b></td>
        </tr>
      </table>

      <div style="text-align:right;max-width:280px">
        <div>{{ $school->city ?? 'Tempat' }}, {{ $letter->letter_date?->translatedFormat('d F Y') }}</div>
        <div style="margin-top:16px;text-align:left;line-height:1.4">
          <div>Kepada Yth.</div>
          <div><b>{{ $letter->recipient ?: 'Bapak / Ibu / Sdr' }}</b></div>
          <div>di Tempat</div>
        </div>
      </div>
    </div>
  @endif

  {{-- ISI SURAT LENGKAP --}}
  <div class="letter-body">
    {!! $letter->content !!}
  </div>

  {{-- PENGESAHAN KEPALA SEKOLAH --}}
  @if ($letter->signed_by_principal)
    <div class="signatures">
      <div class="sig-box">
        @if ($letter->category === 'sk')
          <div style="text-align:left;margin-bottom:12px;font-size:11pt">
            Ditetapkan di: {{ $school->city ?? 'Surabaya' }}<br>
            Pada tanggal: {{ $letter->letter_date?->translatedFormat('d F Y') }}
          </div>
        @else
          <div>{{ $school->city ?? 'Kota' }}, {{ $letter->letter_date?->translatedFormat('d F Y') }}</div>
        @endif

        <div style="font-weight:bold;margin-top:2px">{{ $school->principal_title ?: 'Kepala Sekolah' }}</div>

        @if ($school->signature_url)
          <div style="height:65px;display:flex;align-items:center;justify-content:center;margin:4px 0">
            <img src="{{ asset($school->signature_url) }}" alt="Tanda Tangan / Stempel" style="max-height:60px;max-width:160px;object-fit:contain">
          </div>
        @else
          <div style="height:65px"></div>
        @endif

        <div style="border-bottom:1px solid #0f172a;font-weight:bold;padding-bottom:1px">
          {{ $school->principal_name ?? 'Kepala Sekolah' }}
        </div>
        @if ($school->principal_nip)
          <div style="font-size:10.5pt;margin-top:2px">NIP. {{ $school->principal_nip }}</div>
        @endif
      </div>
    </div>
  @endif
</div>

</body>
</html>
