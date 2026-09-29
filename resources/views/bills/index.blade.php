@extends('layouts.app')
@section('title', 'Tagihan & Pembayaran')

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <span class="cs-pill" style="font-size:11px;padding:3px 12px"><span class="dot" style="background:var(--amber);box-shadow:0 0 10px var(--amber)"></span> Modul Keuangan</span>
      <span style="font-size:12px;color:var(--muted)">Manajemen Tagihan &amp; SPP Siswa</span>
    </div>
    <h1>Tagihan &amp; Pembayaran</h1>
    <div class="sub">Kelola penagihan massal per rombel dan catat transaksi kasir pembayaran peserta didik.</div>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <button type="button" class="btn btn-sm btn-outline" data-dialog="#dialog-batch-generate-bills" style="border-color:#0284c7;color:#0284c7">
      📅 Generate SPP Bulanan
    </button>
    <button type="button" class="btn btn-sm btn-ink" data-dialog="#dialog-generate-bills">
      ⚡ + Buat Tagihan
    </button>
    <a href="{{ route('bills.export', request()->all()) }}" class="btn btn-sm" title="Unduh rekap data tagihan & pembayaran ke Excel (.xlsx)">
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
      Export Excel (.xlsx)
    </a>
  </div>
</div>

{{-- Metric Card Banner --}}
<div class="dash-grid-4" style="margin-bottom:18px">
  <div class="kpi-card glass">
    <div class="label">
      <span>Total Tagihan Diterbitkan</span>
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:#0284c7;fill:none;stroke-width:2"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
    </div>
    <div class="val" style="color:#0284c7">Rp {{ number_format($totalBilled, 0, ',', '.') }}</div>
    <div class="note">{{ $bills->count() }} pos tagihan terdaftar</div>
  </div>
  <div class="kpi-card glass kpi-success">
    <div class="label">
      <span>Kas SPP / Tagihan Diterima</span>
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:#10b981;fill:none;stroke-width:2"><polyline points="20 6 9 17 4 12"/></svg>
    </div>
    <div class="val" style="color:#10b981">Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
    <div class="note">Total realisasi pembayaran kas</div>
  </div>
  <div class="kpi-card glass kpi-danger">
    <div class="label">
      <span>Sisa Piutang / Tunggakan</span>
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:#ef4444;fill:none;stroke-width:2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    </div>
    <div class="val" style="color:#ef4444">Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</div>
    <div class="note">Tunggakan yang belum tertagih</div>
  </div>
  <div class="kpi-card glass kpi-purple">
    <div class="label">
      <span>Tingkat Pelunasan</span>
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:#6366f1;fill:none;stroke-width:2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    </div>
    <div class="val" style="color:#6366f1">{{ $settlementRate }}%</div>
    <div class="note">Rasio kas masuk per tagihan</div>
  </div>
</div>

{{-- Bilah Filter Data Tagihan (Horizontal Full-Width) --}}
<div class="glass panel" style="padding:14px 18px;margin-bottom:18px">
  <form method="GET" action="{{ route('bills.index') }}" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
    <div style="display:flex;align-items:center;gap:8px;flex:1;min-width:220px">
      <span style="font-size:14px;color:var(--muted)">🔍</span>
      <input type="text" name="q" value="{{ request('q') }}" class="input" placeholder="Cari nama siswa atau NIS..." style="height:40px">
    </div>
    <div style="min-width:180px">
      <select name="status" class="select" onchange="this.form.submit()" style="height:40px">
        <option value="">Semua Status Pembayaran</option>
        @foreach (['belum_bayar' => '🔴 Belum Bayar', 'sebagian' => '🟡 Bayar Sebagian', 'lunas' => '🟢 Lunas'] as $key => $label)
          <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
    </div>
    <div style="min-width:180px">
      <select name="class_id" class="select" onchange="this.form.submit()" style="height:40px">
        <option value="">Semua Rombel / Kelas</option>
        @foreach ($classes as $class)
          <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>Kelas {{ $class->name }}</option>
        @endforeach
      </select>
    </div>
    <button type="submit" class="btn btn-sm btn-ink" style="height:40px;padding:0 18px">Filter</button>
    @if (request()->anyFilled(['q', 'status', 'class_id']))
      <a href="{{ route('bills.index') }}" class="btn btn-sm" style="height:40px;padding:0 14px">Reset</a>
    @endif
  </form>
</div>

{{-- Tabel Data Tagihan (100% Layar Penuh Desktop - Bebas Terpotong) --}}
<div class="glass table-wrap" style="width:100%;margin-bottom:24px">
  <table class="tbl" style="width:100%">
    <thead>
      <tr>
        <th style="width:50px;text-align:center">NO</th>
        <th>NAMA SISWA &amp; ROMBEL</th>
        <th>JENIS TAGIHAN</th>
        <th>NOMINAL TAGIHAN</th>
        <th>TERBAYAR</th>
        <th>JATUH TEMPO</th>
        <th style="text-align:center">STATUS</th>
        <th style="text-align:right">AKSI KASIR</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($bills as $idx => $bill)
        @php $paid = (float) $bill->payments->sum('amount_paid'); @endphp
        <tr>
          <td style="text-align:center;color:var(--muted);font-size:12px;font-weight:600">{{ $idx + 1 }}</td>
          <td>
            <b style="color:var(--text);font-size:14px">{{ $bill->student?->full_name ?? '—' }}</b>
            <div style="font-size:12px;color:var(--muted);margin-top:2px">
              Kelas {{ $bill->student?->schoolClass?->name ?? 'Tanpa kelas' }} · NIS: {{ $bill->student?->nis ?? '—' }}
            </div>
          </td>
          <td>
            <span class="badge badge-blue" style="font-weight:700">{{ $bill->paymentType?->name }}</span>
          </td>
          <td>
            <b style="color:var(--text);font-size:14px">Rp {{ number_format($bill->amount, 0, ',', '.') }}</b>
          </td>
          <td>
            @if ($paid > 0)
              <b style="color:var(--green);font-size:13.5px">Rp {{ number_format($paid, 0, ',', '.') }}</b>
              @if ($paid < $bill->amount)
                <div style="font-size:11px;color:var(--amber)">Sisa: Rp {{ number_format($bill->amount - $paid, 0, ',', '.') }}</div>
              @endif
            @else
              <span style="color:var(--muted);font-size:13px">Rp 0</span>
            @endif
          </td>
          <td>
            <span style="font-size:13px;font-weight:500">{{ $bill->due_date?->format('d M Y') ?? '—' }}</span>
          </td>
          <td style="text-align:center">
            @php
              $statusMap = [
                'lunas' => ['badge' => 'badge-ok', 'label' => '🟢 LUNAS'],
                'sebagian' => ['badge' => 'badge-warn', 'label' => '🟡 SEBAGIAN'],
                'belum_bayar' => ['badge' => 'badge-bad', 'label' => '🔴 BELUM BAYAR'],
              ];
              $curStatus = $statusMap[$bill->status] ?? $statusMap['belum_bayar'];
            @endphp
            <span class="badge {{ $curStatus['badge'] }}">{{ $curStatus['label'] }}</span>
          </td>
          <td>
            <div class="actions" style="justify-content:flex-end">
              @if ($bill->status !== 'lunas')
                <button class="btn btn-sm btn-ink" data-dialog="#pay-{{ $bill->id }}" style="height:32px;padding:0 12px;font-size:12px">
                  💳 Bayar
                </button>
              @endif
              @if ($paid > 0)
                <button class="btn btn-sm" data-dialog="#receipt-{{ $bill->id }}" style="height:32px;padding:0 12px;font-size:12px">
                  🧾 Kuitansi
                </button>
              @endif
              <form method="POST" action="{{ route('bills.destroy', $bill) }}" data-confirm="Hapus permanen tagihan {{ $bill->paymentType?->name }} untuk {{ $bill->student?->full_name }}?">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger" style="height:32px;padding:0 10px;font-size:12px">Hapus</button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="8" class="empty">Belum ada catatan tagihan yang sesuai filter pencarian.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- MODAL DIALOG: Generate Tagihan Massal --}}
<dialog class="dlg" id="dialog-generate-bills" style="max-width:560px">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
      <h3 style="margin:0;font-size:18px;color:var(--text);display:flex;align-items:center;gap:8px">
        <svg viewBox="0 0 24 24" style="width:20px;height:20px;stroke:var(--accent);fill:none;stroke-width:2"><path d="M6 2.5h9L19 7v14H6z"/><path d="M14 2.5V7h5"/><path d="M9 12h6M9 15.5h6"/></svg>
        Generate Tagihan Massal
      </h3>
      <button type="button" data-close class="modal-close" aria-label="Tutup">✕</button>
    </div>

    <form method="POST" action="{{ route('bills.store') }}" class="stack">
      @csrf
      <div class="field">
        <label>Jenis Tagihan *</label>
        <select name="payment_type_id" id="billTypeSelect" class="select" required>
          @foreach ($paymentTypes as $pt)
            <option value="{{ $pt->id }}" data-amount="{{ (int) $pt->default_amount }}">
              {{ $pt->name }} — Rp {{ number_format($pt->default_amount, 0, ',', '.') }} ({{ ucfirst($pt->recurrence) }})
            </option>
          @endforeach
        </select>
      </div>

      <div class="field">
        <label>Target Sasaran Tagihan *</label>
        <select name="class_id" class="select">
          <option value="">🎯 Semua Siswa Aktif Seluruh Sekolah</option>
          @foreach ($classes as $class)
            <option value="{{ $class->id }}">Kelas {{ $class->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="form-grid" style="grid-template-columns:1fr 1fr">
        <div class="field">
          <label>Nominal Tagihan (Rp) *</label>
          <input type="number" name="amount" id="billAmountInput" class="input" min="0" step="500" required placeholder="50000">
        </div>
        <div class="field">
          <label>Jatuh Tempo Pembayaran *</label>
          <input type="date" name="due_date" class="input" value="{{ date('Y-m-t') }}" required>
        </div>
      </div>

      {{-- Preset Nominal Cepat --}}
      <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;margin-top:2px">
        <span style="font-size:12px;color:var(--muted)">Preset:</span>
        <button type="button" class="btn btn-sm" onclick="document.getElementById('billAmountInput').value=50000" style="height:28px;padding:0 10px;font-size:11.5px">+50rb</button>
        <button type="button" class="btn btn-sm" onclick="document.getElementById('billAmountInput').value=100000" style="height:28px;padding:0 10px;font-size:11.5px">+100rb</button>
        <button type="button" class="btn btn-sm" onclick="document.getElementById('billAmountInput').value=200000" style="height:28px;padding:0 10px;font-size:11.5px">+200rb</button>
        <button type="button" class="btn btn-sm" onclick="document.getElementById('billAmountInput').value=350000" style="height:28px;padding:0 10px;font-size:11.5px">+350rb</button>
        <button type="button" class="btn btn-sm" onclick="document.getElementById('billAmountInput').value=500000" style="height:28px;padding:0 10px;font-size:11.5px">+500rb</button>
      </div>

      <div class="dlg-actions" style="margin-top:16px">
        <button type="button" class="btn" data-close>Batal</button>
        <button type="submit" class="btn btn-ink" data-loading="Membuat tagihan massal...">
          ⚡ Buat Tagihan Massal
        </button>
      </div>
    </form>
  </div>
</dialog>

{{-- MODAL GENERATE SPP BULANAN OTOMATIS --}}
<dialog id="dialog-batch-generate-bills" class="modal glass">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
      <h3 style="margin:0;font-size:17px;color:var(--text);display:flex;align-items:center;gap:8px">
        <svg viewBox="0 0 24 24" style="width:20px;height:20px;stroke:#0284c7;fill:none;stroke-width:2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Generate Tagihan SPP Bulanan Otomatis
      </h3>
      <button type="button" data-close class="modal-close" aria-label="Tutup">✕</button>
    </div>

    <div style="font-size:12.5px;color:var(--muted);margin-bottom:14px">
      Fitur ini secara otomatis membuat tagihan berulang (contoh: SPP bulanan selama 1 semester atau 1 tahun ajaran) untuk rombel yang dipilih tanpa duplikasi data.
    </div>

    <form method="POST" action="{{ route('bills.batch-generate') }}" class="stack">
      @csrf
      <div class="field">
        <label>Jenis Pembayaran *</label>
        <select name="payment_type_id" class="select" required>
          @foreach ($paymentTypes as $pt)
            <option value="{{ $pt->id }}">{{ $pt->name }} (Bawaan: Rp {{ number_format($pt->default_amount, 0, ',', '.') }})</option>
          @endforeach
        </select>
      </div>

      <div class="field">
        <label>Target Sasaran Siswa / Rombel *</label>
        <select name="class_id" class="select">
          <option value="">🎯 Semua Siswa Aktif Seluruh Sekolah</option>
          @foreach ($classes as $class)
            <option value="{{ $class->id }}">Kelas {{ $class->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="field">
        <label>Nominal Tagihan per Bulan (Rp) *</label>
        <input type="number" name="amount" class="input" min="0" step="1000" value="250000" required>
      </div>

      <div class="form-grid" style="grid-template-columns:1fr 1fr">
        <div class="field">
          <label>Bulan Mulai (YYYY-MM) *</label>
          <input type="month" name="start_month" class="input" value="{{ date('Y-07') }}" required>
        </div>
        <div class="field">
          <label>Bulan Selesai (YYYY-MM) *</label>
          <input type="month" name="end_month" class="input" value="{{ date('Y-12') }}" required>
        </div>
      </div>

      <div class="field">
        <label>Hari Jatuh Tempo Tiap Bulan (Tanggal 1 - 28) *</label>
        <input type="number" name="due_day" class="input" value="10" min="1" max="28" required>
        <small style="font-size:11px;color:var(--muted)">Contoh: 10 artinya jatuh tempo tanggal 10 tiap bulan berjalan.</small>
      </div>

      <div class="dlg-actions" style="margin-top:16px">
        <button type="button" class="btn" data-close>Batal</button>
        <button type="submit" class="btn btn-ink" data-loading="Men-generate tagihan bulanan...">
          ⚡ Generate Tagihan Bulanan
        </button>
      </div>
    </form>
  </div>
</dialog>

{{-- MODAL DIALOGS: Pembayaran & Kuitansi per Bill --}}
@foreach ($bills as $bill)
@php $alreadyPaid = (float) $bill->payments->sum('amount_paid'); @endphp

{{-- Modal Pembayaran Kasir --}}
<dialog class="dlg" id="pay-{{ $bill->id }}">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
      <h3 style="margin:0;font-size:17px;color:var(--text)">Pencatatan Pembayaran — {{ $bill->student?->full_name }}</h3>
      <button type="button" data-close class="modal-close" aria-label="Tutup">✕</button>
    </div>

    <div style="padding:12px 14px;border-radius:var(--radius-sm);background:#f0f9ff;border:1px solid #bae6fd;margin-bottom:14px">
      <div style="font-size:13px;color:#334155">Tagihan: <b style="color:var(--text)">{{ $bill->paymentType?->name }}</b></div>
      <div style="font-size:14px;color:var(--amber);margin-top:2px;font-weight:700">
        Total: Rp {{ number_format($bill->amount, 0, ',', '.') }}
        @if ($alreadyPaid > 0)
          (Sisa: Rp {{ number_format($bill->amount - $alreadyPaid, 0, ',', '.') }})
        @endif
      </div>
    </div>

    <form method="POST" action="{{ route('bills.pay', $bill) }}" class="stack">
      @csrf
      <div class="field">
        <label>Nominal Pembayaran (Rp) *</label>
        <input type="number" name="amount_paid" class="input" min="1" step="500" value="{{ (int) ($bill->amount - $alreadyPaid) }}" required>
      </div>
      <div class="field">
        <label>Metode Pembayaran</label>
        <select name="method" class="select">
          @foreach (['Tunai', 'Transfer', 'VA', 'QRIS', 'Retail'] as $m)
            <option value="{{ $m }}">{{ $m }}</option>
          @endforeach
        </select>
      </div>
      <div class="dlg-actions">
        <button type="button" class="btn" data-close>Batal</button>
        <button class="btn btn-ink" data-loading="Menyimpan Pembayaran...">Catat Pembayaran</button>
      </div>
    </form>
  </div>
</dialog>

{{-- Modal Cetak Kuitansi / Bukti Bayar Resmi --}}
<dialog class="dlg" id="receipt-{{ $bill->id }}" style="max-width:540px">
  <div class="modal-box" style="padding:20px">
    <div class="receipt-box" id="print-area-{{ $bill->id }}">
      <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:2px solid var(--accent);padding-bottom:12px;margin-bottom:14px">
        <div>
          <h2 style="font-size:17px;font-weight:800;color:var(--text);margin:0">{{ auth()->user()->school->name }}</h2>
          <div style="font-size:12px;color:var(--muted)">Sistem Informasi Manajemen Sekolah · Ruang GTK</div>
        </div>
        <div style="text-align:right">
          <span class="badge badge-ok" style="font-size:11.5px">BUKTI RESMI</span>
          <div style="font-size:11px;color:var(--muted);margin-top:3px">No: KW-{{ str_pad($bill->id, 5, '0', STR_PAD_LEFT) }}</div>
        </div>
      </div>

      <div style="text-align:center;margin-bottom:16px">
        <h3 style="font-size:15px;letter-spacing:0.04em;text-transform:uppercase;color:var(--accent);margin:0">Kuitansi Pembayaran Siswa</h3>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:13px;margin-bottom:14px;background:#f8fafc;border:1px solid #e2e8f0;padding:10px 12px;border-radius:var(--radius-sm)">
        <div>
          <span style="color:var(--muted)">Nama Siswa:</span>
          <div style="font-weight:700;color:var(--text)">{{ $bill->student?->full_name }}</div>
        </div>
        <div>
          <span style="color:var(--muted)">NIS / Kelas:</span>
          <div style="color:var(--text)">{{ $bill->student?->nis ?? '—' }} · Kelas {{ $bill->student?->schoolClass?->name ?? '—' }}</div>
        </div>
        <div>
          <span style="color:var(--muted)">Uraian Tagihan:</span>
          <div style="font-weight:600;color:var(--text)">{{ $bill->paymentType?->name }}</div>
        </div>
        <div>
          <span style="color:var(--muted)">Status:</span>
          <div><b style="color:{{ $bill->status === 'lunas' ? 'var(--green)' : 'var(--amber)' }}">{{ strtoupper($bill->status) }}</b></div>
        </div>
      </div>

      <div style="font-size:13px;margin-bottom:12px">
        <div style="font-weight:600;color:var(--text);margin-bottom:6px">Riwayat Pembayaran:</div>
        <table style="width:100%;font-size:12px;border-collapse:collapse">
          <thead>
            <tr style="border-bottom:1px solid var(--line-light);text-align:left;color:var(--muted)">
              <th style="padding:4px 0">Tanggal</th>
              <th style="padding:4px 0">Metode</th>
              <th style="padding:4px 0;text-align:right">Jumlah</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($bill->payments as $pmt)
              <tr style="border-bottom:1px solid rgba(255,255,255,0.04)">
                <td style="padding:6px 0">{{ $pmt->paid_at?->format('d/m/Y H:i') }}</td>
                <td style="padding:6px 0"><span class="badge badge-blue" style="font-size:10px">{{ $pmt->method }}</span></td>
                <td style="padding:6px 0;text-align:right;font-weight:600;color:var(--green)">Rp {{ number_format($pmt->amount_paid, 0, ',', '.') }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:rgba(56,189,248,0.08);border:1px solid rgba(56,189,248,0.2);border-radius:var(--radius-sm);margin-top:10px">
        <div>
          <span style="font-size:12px;color:var(--muted)">Total Terbayar:</span>
          <div style="font-size:16px;font-weight:800;color:var(--accent)">Rp {{ number_format($alreadyPaid, 0, ',', '.') }}</div>
        </div>
        <div style="text-align:right">
          <span style="font-size:12px;color:var(--muted)">Total Biaya:</span>
          <div style="font-size:14px;font-weight:700;color:var(--text)">Rp {{ number_format($bill->amount, 0, ',', '.') }}</div>
        </div>
      </div>

      <div style="display:flex;justify-content:space-between;margin-top:20px;padding-top:12px;font-size:12px;color:var(--muted)">
        <div>Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB</div>
        <div style="text-align:center">
          <div>Bendahara / Kasir</div>
          <div style="margin-top:30px;font-weight:600;color:var(--text)">( {{ auth()->user()->username }} )</div>
        </div>
      </div>
    </div>

    <div class="dlg-actions" style="margin-top:14px">
      <button type="button" class="btn" data-close>Tutup</button>
      <button type="button" class="btn btn-ink" onclick="window.print()">🖨️ Cetak Kuitansi</button>
    </div>
  </div>
</dialog>
@endforeach
@endsection
