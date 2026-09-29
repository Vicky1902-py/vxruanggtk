@extends('layouts.app')
@section('title', 'Buku Kas Umum (BKU) & Laporan Keuangan')

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <span class="cs-pill" style="font-size:11px;padding:3px 12px"><span class="dot" style="background:#0284c7"></span> Akuntansi &amp; Keuangan</span>
      <span style="font-size:12px;color:var(--muted)">Buku Kas Umum (BKU) &amp; Arus Kas Operasional</span>
    </div>
    <h1>Laporan Keuangan &amp; BKU</h1>
    <div class="sub">Rekapitulasi penerimaan biaya pendidikan, realisasi belanja honorarium GTK, dan arus kas kasir sekolah.</div>
  </div>
  <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
    <form method="GET" style="display:flex;gap:8px;align-items:center">
      <label style="font-size:12.5px;color:var(--muted);white-space:nowrap">Periode:</label>
      <input type="month" name="period" class="input" value="{{ $period }}" onchange="this.form.submit()" style="width:165px;height:36px;font-size:12.5px">
    </form>

    <a href="{{ route('reports.financial.export', ['period' => $period]) }}" class="btn btn-sm" title="Unduh Buku Kas Umum resmi ke Excel (.xlsx)">
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
      Export Excel (.xlsx)
    </a>

    <a href="{{ route('reports.financial.print', ['period' => $period]) }}" target="_blank" class="btn btn-sm btn-ink" title="Cetak format cetak BKU resmi untuk arsip dan tanda tangan">
      🖨️ Cetak BKU Resmi
    </a>
  </div>
</div>

{{-- Metric Card Banner --}}
<div class="dash-grid-4" style="margin-bottom:18px">
  <div class="kpi-card glass kpi-success">
    <div class="label">
      <span>Total Penerimaan (Debet)</span>
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:#10b981;fill:none;stroke-width:2"><polyline points="20 6 9 17 4 12"/></svg>
    </div>
    <div class="val" style="color:#10b981">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
    <div class="note">Realisasi pembayaran tagihan &amp; SPP siswa</div>
  </div>

  <div class="kpi-card glass kpi-danger">
    <div class="label">
      <span>Belanja Pegawai (Kredit)</span>
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:#ef4444;fill:none;stroke-width:2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
    </div>
    <div class="val" style="color:#ef4444">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
    <div class="note">Gaji &amp; honorarium GTK disalurkan</div>
  </div>

  <div class="kpi-card glass {{ $netBalance >= 0 ? '' : 'kpi-danger' }}">
    <div class="label">
      <span>Surplus / Defisit Operasional</span>
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:{{ $netBalance >= 0 ? '#0284c7' : '#ef4444' }};fill:none;stroke-width:2"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
    </div>
    <div class="val" style="color:{{ $netBalance >= 0 ? '#0284c7' : '#ef4444' }}">
      Rp {{ number_format($netBalance, 0, ',', '.') }}
    </div>
    <div class="note">Sisa kas bersih operasional sekolah</div>
  </div>

  <div class="kpi-card glass kpi-purple">
    <div class="label">
      <span>Arus Kas Tabungan (Net)</span>
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:#6366f1;fill:none;stroke-width:2"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
    </div>
    <div class="val" style="color:#6366f1">Rp {{ number_format($netSavings, 0, ',', '.') }}</div>
    <div class="note">Setor Rp {{ number_format($savingDeposits, 0, ',', '.') }} · Tarik Rp {{ number_format($savingWithdrawals, 0, ',', '.') }}</div>
  </div>
</div>

{{-- Tabel Buku Kas Umum --}}
<div class="glass panel">
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:16px">
    <div>
      <h2 class="panel-title" style="margin:0">
        <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20M4 4.5A2.5 2.5 0 016.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15z"/></svg>
        Buku Kas Umum (BKU) — Periode {{ \Illuminate\Support\Carbon::parse($period . '-01')->translatedFormat('F Y') }}
      </h2>
      <div style="font-size:12px;color:var(--muted);margin-top:2px">Catatan kronologis penerimaan dan pengeluaran kas sekolah dengan saldo berjalan.</div>
    </div>
  </div>

  <div class="table-wrap" style="border:none;box-shadow:none">
    <table class="tbl">
      <thead>
        <tr>
          <th style="width:40px;text-align:center">No</th>
          <th>Tanggal &amp; Waktu</th>
          <th>No. Referensi</th>
          <th>Kategori Kas</th>
          <th>Uraian Transaksi</th>
          <th style="text-align:right">Penerimaan / Debet (Rp)</th>
          <th style="text-align:right">Pengeluaran / Kredit (Rp)</th>
          <th style="text-align:right">Saldo Berjalan (Rp)</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($ledger as $idx => $row)
          <tr>
            <td style="text-align:center;color:var(--muted);font-size:12px">{{ $idx + 1 }}</td>
            <td style="white-space:nowrap;font-size:12.5px">{{ $row['date'] }}</td>
            <td style="white-space:nowrap">
              <span class="badge badge-slate" style="font-size:11px;font-family:monospace">{{ $row['ref'] }}</span>
            </td>
            <td>
              @if ($row['debit'] > 0)
                <span class="badge badge-ok" style="font-size:11px">Pemasukan SPP</span>
              @else
                <span class="badge badge-bad" style="font-size:11px">Belanja GTK</span>
              @endif
            </td>
            <td>
              <b style="color:var(--text);font-size:13px">{{ $row['description'] }}</b>
            </td>
            <td style="text-align:right;font-weight:600;color:{{ $row['debit'] > 0 ? '#10b981' : 'var(--muted)' }}">
              {{ $row['debit'] > 0 ? 'Rp ' . number_format($row['debit'], 0, ',', '.') : '—' }}
            </td>
            <td style="text-align:right;font-weight:600;color:{{ $row['credit'] > 0 ? '#ef4444' : 'var(--muted)' }}">
              {{ $row['credit'] > 0 ? 'Rp ' . number_format($row['credit'], 0, ',', '.') : '—' }}
            </td>
            <td style="text-align:right;font-weight:700;color:{{ $row['balance'] >= 0 ? '#0284c7' : '#ef4444' }};font-size:13.5px">
              Rp {{ number_format($row['balance'], 0, ',', '.') }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="empty">Tidak ada transaksi kas tercatat pada periode {{ \Illuminate\Support\Carbon::parse($period . '-01')->translatedFormat('F Y') }}.</td>
          </tr>
        @endforelse
      </tbody>
      @if (count($ledger) > 0)
        <tfoot>
          <tr style="background:#f8fafc;font-weight:800;border-top:2px solid #cbd5e1">
            <td colspan="5" style="padding:14px;text-align:right;color:var(--text);font-size:13px">TOTAL KAS REKAPITULASI (PERIODE INI):</td>
            <td style="padding:14px;text-align:right;color:#10b981;font-size:14px">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
            <td style="padding:14px;text-align:right;color:#ef4444;font-size:14px">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
            <td style="padding:14px;text-align:right;color:{{ $netBalance >= 0 ? '#0284c7' : '#ef4444' }};font-size:15px">Rp {{ number_format($netBalance, 0, ',', '.') }}</td>
          </tr>
        </tfoot>
      @endif
    </table>
  </div>
</div>
@endsection
