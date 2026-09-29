@extends('layouts.app')
@section('title', 'Buku Tabungan — ' . $student->full_name)

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <a href="{{ route('savings.index') }}" style="color:var(--muted);text-decoration:none;font-size:12px;display:flex;align-items:center;gap:4px">
        ← Kembali ke Daftar Tabungan
      </a>
      <span class="cs-pill" style="font-size:11px;padding:3px 12px"><span class="dot" style="background:#10b981"></span> Buku Mutasi</span>
    </div>
    <h1>Buku Tabungan: {{ $student->full_name }}</h1>
    <div class="sub">NIS: {{ $student->nis ?? '—' }} · {{ $student->schoolClass ? 'Kelas ' . $student->schoolClass->name : 'Tanpa Kelas' }}</div>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <button type="button" class="btn btn-sm btn-ink" onclick="window.print()">
      🖨️ Cetak Buku Tabungan
    </button>
  </div>
</div>

<div class="two-col" style="grid-template-columns: 1fr 2fr">
  {{-- Kartu Info Rekening Tabungan --}}
  <div class="stack">
    <div class="glass panel" style="background:linear-gradient(135deg,#0284c7,#2563eb);color:#ffffff;border:none">
      <div style="font-size:12px;opacity:0.85;text-transform:uppercase;letter-spacing:1px">Saldo Simpanan Siswa</div>
      <div style="font-size:28px;font-weight:800;margin:10px 0 16px 0;letter-spacing:-0.5px">
        Rp {{ number_format($saving->balance, 0, ',', '.') }}
      </div>
      <div style="border-top:1px solid rgba(255,255,255,0.2);padding-top:12px;font-size:12px;display:flex;justify-content:space-between">
        <span>No. Rekening Siswa:</span>
        <b style="font-family:monospace;letter-spacing:1px">TAB-{{ str_pad($student->id, 5, '0', STR_PAD_LEFT) }}</b>
      </div>
    </div>

    <div class="glass panel">
      <h3 style="font-size:15px;margin:0 0 12px 0;color:var(--text)">Identitas Peserta Didik</h3>
      <table style="width:100%;font-size:13px;line-height:2">
        <tr>
          <td style="color:var(--muted)">Nama Lengkap:</td>
          <td><b>{{ $student->full_name }}</b></td>
        </tr>
        <tr>
          <td style="color:var(--muted)">NIS / NISN:</td>
          <td>{{ $student->nis ?? '—' }} / {{ $student->nisn ?? '—' }}</td>
        </tr>
        <tr>
          <td style="color:var(--muted)">Rombel / Kelas:</td>
          <td>{{ $student->schoolClass ? 'Kelas ' . $student->schoolClass->name : '—' }}</td>
        </tr>
        <tr>
          <td style="color:var(--muted)">Nama Wali:</td>
          <td>{{ $student->guardian?->full_name ?? '—' }}</td>
        </tr>
        <tr>
          <td style="color:var(--muted)">Kontak Wali:</td>
          <td>{{ $student->guardian?->phone_whatsapp ?? '—' }}</td>
        </tr>
      </table>
    </div>
  </div>

  {{-- Mutasi Transaksi Tabungan --}}
  <div class="glass panel" id="print-area">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
      <h2 class="panel-title" style="margin:0">
        <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        Mutasi Transaksi Simpanan
      </h2>
      <span style="font-size:12px;color:var(--muted)">{{ $transactions->total() }} catatan transaksi</span>
    </div>

    <div class="table-wrap" style="border:none;box-shadow:none">
      <table class="tbl">
        <thead>
          <tr>
            <th>Tanggal / Waktu</th>
            <th>Jenis</th>
            <th>Keterangan</th>
            <th style="text-align:right">Setor (Masuk)</th>
            <th style="text-align:right">Tarik (Keluar)</th>
            <th style="text-align:right">Saldo Akhir</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($transactions as $t)
            <tr>
              <td style="font-size:12px;color:var(--muted)">
                {{ $t->created_at?->translatedFormat('d/m/Y H:i') }}
              </td>
              <td>
                @if ($t->direction === 'setor')
                  <span class="badge badge-ok">📥 Setor</span>
                @else
                  <span class="badge badge-bad">📤 Tarik</span>
                @endif
              </td>
              <td style="font-size:12.5px;color:var(--text)">
                {{ $t->note ?? '-' }}
              </td>
              <td style="text-align:right;font-weight:600;color:#10b981">
                {{ $t->direction === 'setor' ? 'Rp ' . number_format($t->amount, 0, ',', '.') : '—' }}
              </td>
              <td style="text-align:right;font-weight:600;color:#ef4444">
                {{ $t->direction === 'tarik' ? 'Rp ' . number_format($t->amount, 0, ',', '.') : '—' }}
              </td>
              <td style="text-align:right;font-weight:700;color:var(--text)">
                Rp {{ number_format($t->balance_after, 0, ',', '.') }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="empty">Belum ada riwayat mutasi transaksi untuk siswa ini.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($transactions->hasPages())
      <div style="margin-top:14px">{{ $transactions->links() }}</div>
    @endif
  </div>
</div>

<style>
@media print {
  body { background: #fff !important; color: #000 !important; }
  .sidebar, .nav-head, .page-head div:last-child, .btn, .cs-pill { display: none !important; }
  .main { margin: 0 !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }
  .panel { border: 1px solid #ccc !important; box-shadow: none !important; }
}
</style>
@endsection
