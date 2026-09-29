<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Buku Kas Umum (BKU) — {{ $school->name }} — {{ $period }}</title>
  <style>
    * { box-sizing: border-box; font-family: 'Segoe UI', Arial, sans-serif; }
    body { background: #f8fafc; color: #0f172a; margin: 0; padding: 24px; display: flex; justify-content: center; }
    .print-card {
      background: #ffffff;
      width: 100%;
      max-width: 900px;
      border: 1px solid #cbd5e1;
      padding: 32px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    }
    .kop {
      text-align: center;
      border-bottom: 3px double #0f172a;
      padding-bottom: 12px;
      margin-bottom: 20px;
    }
    .kop h1 { margin: 0; font-size: 20px; text-transform: uppercase; letter-spacing: 0.5px; }
    .kop p { margin: 4px 0 0; font-size: 12px; color: #475569; }
    .title-box { text-align: center; margin-bottom: 20px; }
    .title-box h2 { margin: 0; font-size: 16px; text-transform: uppercase; letter-spacing: 1px; }
    .title-box span { font-size: 13px; font-weight: 600; color: #334155; }
    .bku-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 12px; }
    .bku-table th, .bku-table td { border: 1px solid #94a3b8; padding: 8px 10px; }
    .bku-table th { background: #f1f5f9; text-transform: uppercase; font-size: 11px; text-align: center; font-weight: 700; }
    .total-row { background: #f8fafc; font-weight: 800; font-size: 12.5px; }
    .signatures { display: flex; justify-content: space-between; margin-top: 40px; padding: 0 30px; text-align: center; }
    .sig-box { width: 220px; font-size: 12px; }
    .sig-line { margin-top: 8px; border-bottom: 1px solid #0f172a; font-weight: 700; padding-bottom: 4px; }
    .btn-print {
      position: fixed;
      top: 20px;
      right: 20px;
      background: #0284c7;
      color: #fff;
      padding: 10px 18px;
      border-radius: 6px;
      border: none;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(2,132,199,0.3);
    }
    @media print {
      body { background: #fff; padding: 0; }
      .print-card { border: none; box-shadow: none; max-width: 100%; padding: 0; }
      .btn-print { display: none; }
    }
  </style>
</head>
<body>

<button class="btn-print" onclick="window.print()">🖨️ Cetak Dokumen BKU</button>

<div class="print-card">
  @include('partials.kop-surat', ['school' => $school])

  <div class="title-box">
    <h2>BUKU KAS UMUM (BKU) SEKOLAH</h2>
    <span>Periode: {{ \Illuminate\Support\Carbon::parse($period . '-01')->translatedFormat('F Y') }}</span>
  </div>

  <table class="bku-table">
    <thead>
      <tr>
        <th style="width:30px">No</th>
        <th style="width:110px">Tanggal</th>
        <th style="width:100px">No. Bukti</th>
        <th>Uraian Transaksi Kas</th>
        <th style="width:120px">Penerimaan / Debet (Rp)</th>
        <th style="width:120px">Pengeluaran / Kredit (Rp)</th>
        <th style="width:120px">Saldo Kas (Rp)</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($ledger as $idx => $row)
        <tr>
          <td style="text-align:center">{{ $idx + 1 }}</td>
          <td>{{ $row['date'] }}</td>
          <td style="font-family:monospace">{{ $row['ref'] }}</td>
          <td>{{ $row['description'] }}</td>
          <td style="text-align:right">{{ $row['debit'] > 0 ? number_format($row['debit'], 0, ',', '.') : '-' }}</td>
          <td style="text-align:right">{{ $row['credit'] > 0 ? number_format($row['credit'], 0, ',', '.') : '-' }}</td>
          <td style="text-align:right;font-weight:600">{{ number_format($row['balance'], 0, ',', '.') }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="7" style="text-align:center;padding:16px;color:#64748b">Tidak ada mutasi kas pada periode ini.</td>
        </tr>
      @endforelse
    </tbody>
    <tfoot>
      <tr class="total-row">
        <td colspan="4" style="text-align:right;padding:10px">TOTAL REKAPITULASI:</td>
        <td style="text-align:right;padding:10px">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
        <td style="text-align:right;padding:10px">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
        <td style="text-align:right;padding:10px">Rp {{ number_format($netBalance, 0, ',', '.') }}</td>
      </tr>
    </tfoot>
  </table>

  <div style="font-size:11px;color:#64748b;margin-bottom:24px">
    Dicetak pada {{ now()->translatedFormat('l, d F Y H:i') }} WIB melalui Sistem Manajemen Sekolah Ruang GTK.
  </div>

  <div class="signatures">
    <div class="sig-box">
      <div>Mengetahui,</div>
      <div style="font-weight:600">{{ $school->principal_title ?? 'Kepala Sekolah' }}</div>
      @if ($school->signature_url)
        <div style="height:55px;display:flex;align-items:center;justify-content:center">
          <img src="{{ asset($school->signature_url) }}" style="max-height:50px;max-width:140px;object-fit:contain">
        </div>
      @else
        <div style="height:55px"></div>
      @endif
      <div class="sig-line">{{ $school->principal_name ?? 'Kepala Sekolah' }}</div>
      @if ($school->principal_nip)
        <div style="font-size:11px;color:#475569;margin-top:2px">NIP. {{ $school->principal_nip }}</div>
      @endif
    </div>
    <div class="sig-box">
      <div>Dibuat Oleh,</div>
      <div style="font-weight:600">Bendahara Sekolah</div>
      <div style="height:55px"></div>
      <div class="sig-line">Bendahara Sekolah</div>
      <div style="font-size:11px;color:#475569;margin-top:2px">NIP. —</div>
    </div>
  </div>
</div>

</body>
</html>
