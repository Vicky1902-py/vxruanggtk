<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Slip Gaji — {{ $employee->full_name }} — {{ $payroll->period }}</title>
  <style>
    * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    body { background: #f8fafc; color: #0f172a; margin: 0; padding: 24px; display: flex; justify-content: center; }
    .slip-card {
      background: #ffffff;
      width: 100%;
      max-width: 680px;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 32px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    }
    .slip-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 2px solid #0284c7;
      padding-bottom: 16px;
      margin-bottom: 20px;
    }
    .school-title { font-size: 20px; font-weight: 800; color: #0284c7; margin: 0; }
    .school-sub { font-size: 12px; color: #64748b; margin-top: 4px; }
    .badge-period {
      background: #e0f2fe;
      color: #0369a1;
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 0.5px;
    }
    .info-table { width: 100%; font-size: 13px; margin-bottom: 24px; border-collapse: collapse; }
    .info-table td { padding: 4px 0; }
    .salary-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 13.5px; }
    .salary-table th { background: #f1f5f9; color: #475569; padding: 10px 12px; text-align: left; font-size: 12px; text-transform: uppercase; }
    .salary-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; }
    .total-row { background: #f8fafc; font-weight: 800; font-size: 15px; }
    .signatures { display: flex; justify-content: space-between; margin-top: 40px; padding: 0 20px; text-align: center; }
    .sig-box { width: 200px; font-size: 12.5px; }
    .sig-line { margin-top: 60px; border-bottom: 1px dashed #94a3b8; font-weight: 700; padding-bottom: 4px; }
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
      .slip-card { border: none; box-shadow: none; max-width: 100%; padding: 0; }
      .btn-print { display: none; }
    }
  </style>
</head>
<body>

<button class="btn-print" onclick="window.print()">🖨️ Cetak Slip Gaji</button>

<div class="slip-card">
  <div class="slip-header">
    <div>
      <h1 class="school-title">{{ $school?->name ?? 'SMK / SMA Negeri' }}</h1>
      <div class="school-sub">Sistem Informasi Manajemen Sekolah &amp; GTK · Bukti Sah Pembayaran Honorarium</div>
    </div>
    <div class="badge-period">
      {{ \Illuminate\Support\Carbon::parse($payroll->period . '-01')->translatedFormat('F Y') }}
    </div>
  </div>

  <div style="text-align:center; font-weight:800; font-size:15px; margin: 0 0 18px 0; text-transform: uppercase; letter-spacing: 1px; color:#1e293b; border-bottom: 1px dashed #cbd5e1; padding-bottom: 8px;">SLIP GAJI PEGAWAI</div>

  <table class="info-table">
    <tr>
      <td style="width:130px;color:#64748b">Nama Pegawai</td>
      <td style="width:10px">:</td>
      <td><b>{{ $employee->full_name }}</b></td>
      <td style="width:110px;color:#64748b">No. Referensi</td>
      <td style="width:10px">:</td>
      <td><code>SLIP-{{ $payroll->id }}-{{ str_replace('-', '', $payroll->period) }}</code></td>
    </tr>
    <tr>
      <td style="color:#64748b">NIP / NUPTK</td>
      <td>:</td>
      <td>{{ $employee->nip ?? '—' }}</td>
      <td style="color:#64748b">Jabatan</td>
      <td>:</td>
      <td><b>{{ $employee->position?->name ?? 'Tenaga Pendidik / GTK' }}</b></td>
    </tr>
  </table>

  <table class="salary-table">
    <thead>
      <tr>
        <th>Rincian Penghasilan</th>
        <th style="text-align:right">Jumlah (Rp)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Gaji Pokok &amp; Tunjangan Kinerja</td>
        <td style="text-align:right;font-weight:600">Rp {{ number_format($payroll->gross_amount, 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td style="color:#ef4444">Potongan (Absensi, Iuran, dll)</td>
        <td style="text-align:right;color:#ef4444;font-weight:600">- Rp {{ number_format($payroll->deductions, 0, ',', '.') }}</td>
      </tr>
      <tr class="total-row">
        <td style="color:#0284c7">TOTAL GAJI BERSIH (TAKE HOME PAY)</td>
        <td style="text-align:right;color:#0284c7;font-size:16px">
          Rp {{ number_format($payroll->net_amount, 0, ',', '.') }}
        </td>
      </tr>
    </tbody>
  </table>

  <div style="font-size:12px;color:#64748b;font-style:italic;margin-bottom:20px">
    Catatan: Bukti tanda terima ini dicetak secara otomatis dari sistem Ruang GTK pada {{ now()->translatedFormat('d F Y, H:i') }} WIB dan merupakan dokumen sah internal sekolah.
  </div>

  <div class="signatures">
    <div class="sig-box">
      <div>Penerima / Pegawai,</div>
      <div class="sig-line">{{ $employee->full_name }}</div>
    </div>
    <div class="sig-box">
      <div>Bendahara Sekolah,</div>
      <div class="sig-line">Bendahara GTK</div>
    </div>
  </div>
</div>

</body>
</html>
