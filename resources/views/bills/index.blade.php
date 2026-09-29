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
    <div class="sub">Generate tagihan massal per rombel dan catat transaksi pembayaran peserta didik.</div>
  </div>
  <div>
    <a href="{{ route('bills.export', request()->all()) }}" class="btn btn-sm" title="Unduh rekap data tagihan & pembayaran ke Excel (.xlsx)">
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
      Export Excel (.xlsx)
    </a>
  </div>
</div>

<div class="two-col">
  <div class="stack">
    {{-- Form Generate Tagihan --}}
    <div class="glass panel">
      <h2 class="panel-title">
        <svg viewBox="0 0 24 24"><path d="M6 2.5h9L19 7v14H6z"/><path d="M14 2.5V7h5"/><path d="M9 12h6M9 15.5h6"/></svg>
        Generate Tagihan Massal
      </h2>
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
          <label>Target Sasaran</label>
          <select name="class_id" class="select">
            <option value="">🎯 Semua Siswa Aktif Seluruh Sekolah</option>
            @foreach ($classes as $class)
              <option value="{{ $class->id }}">Kelas {{ $class->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="form-grid" style="grid-template-columns:1fr 1fr">
          <div class="field">
            <label>Nominal (Rp) *</label>
            <input type="number" name="amount" id="billAmountInput" class="input" min="0" step="500" required placeholder="50000">
          </div>
          <div class="field">
            <label>Jatuh Tempo *</label>
            <input type="date" name="due_date" class="input" value="{{ date('Y-m-t') }}" required>
          </div>
        </div>

        {{-- Quick Nominal Preset Buttons --}}
        <div style="display:flex;gap:6px;flex-wrap:wrap">
          <button type="button" class="btn btn-sm" onclick="document.getElementById('billAmountInput').value=50000" style="height:28px;padding:0 10px;font-size:11.5px">+50rb</button>
          <button type="button" class="btn btn-sm" onclick="document.getElementById('billAmountInput').value=100000" style="height:28px;padding:0 10px;font-size:11.5px">+100rb</button>
          <button type="button" class="btn btn-sm" onclick="document.getElementById('billAmountInput').value=200000" style="height:28px;padding:0 10px;font-size:11.5px">+200rb</button>
          <button type="button" class="btn btn-sm" onclick="document.getElementById('billAmountInput').value=500000" style="height:28px;padding:0 10px;font-size:11.5px">+500rb</button>
        </div>

        <button class="btn btn-ink" data-loading="Membuat tagihan massal...">
          ⚡ Buat Tagihan Massal
        </button>
      </form>
    </div>

    {{-- Filter Panel --}}
    <div class="glass panel">
      <h2 class="panel-title">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M16.5 16.5L21 21"/></svg>
        Filter Data Tagihan
      </h2>
      <form method="GET" class="stack">
        <div class="field">
          <label>Status Pembayaran</label>
          <select name="status" class="select" onchange="this.form.submit()">
            <option value="">Semua status</option>
            @foreach (['belum_bayar' => '🔴 Belum Bayar', 'sebagian' => '🟡 Sebagian', 'lunas' => '🟢 Lunas'] as $key => $label)
              <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="field">
          <label>Rombel / Kelas</label>
          <select name="class_id" class="select" onchange="this.form.submit()">
            <option value="">Semua kelas</option>
            @foreach ($classes as $class)
              <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>Kelas {{ $class->name }}</option>
            @endforeach
          </select>
        </div>
        @if (request()->anyFilled(['status', 'class_id']))
          <a href="{{ route('bills.index') }}" class="btn btn-sm" style="text-align:center">Reset Filter</a>
        @endif
      </form>
    </div>
  </div>

  {{-- Tabel Tagihan --}}
  <div class="glass table-wrap">
    <table class="tbl">
      <thead>
        <tr>
          <th>Nama Siswa</th>
          <th>Jenis Tagihan</th>
          <th>Nominal</th>
          <th>Jatuh Tempo</th>
          <th>Status</th>
          <th style="text-align:right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($bills as $bill)
          @php $paid = (float) $bill->payments->sum('amount_paid'); @endphp
          <tr>
            <td>
              <b style="color:var(--text);font-size:14px">{{ $bill->student?->full_name ?? '—' }}</b>
              <br><small style="color:var(--muted)">Kelas {{ $bill->student?->schoolClass?->name ?? 'Tanpa kelas' }}</small>
            </td>
            <td>
              <span class="badge badge-blue">{{ $bill->paymentType?->name }}</span>
            </td>
            <td>
              <b style="color:var(--text)">Rp {{ number_format($bill->amount, 0, ',', '.') }}</b>
              @if ($paid > 0)
                <br><small style="color:var(--green)">Terbayar Rp {{ number_format($paid, 0, ',', '.') }}</small>
              @endif
            </td>
            <td>
              <span style="font-size:13px">{{ $bill->due_date?->format('d M Y') ?? '—' }}</span>
            </td>
            <td>
              @php
                $statusMap = [
                  'lunas' => ['badge' => 'badge-ok', 'label' => '🟢 Lunas'],
                  'sebagian' => ['badge' => 'badge-warn', 'label' => '🟡 Sebagian'],
                  'belum_bayar' => ['badge' => 'badge-bad', 'label' => '🔴 Belum Bayar'],
                ];
                $curStatus = $statusMap[$bill->status] ?? $statusMap['belum_bayar'];
              @endphp
              <span class="badge {{ $curStatus['badge'] }}">{{ $curStatus['label'] }}</span>
            </td>
            <td>
              <div class="actions">
                @if ($bill->status !== 'lunas')
                  <button class="btn btn-sm btn-ink" data-dialog="#pay-{{ $bill->id }}" style="height:30px;padding:0 12px;font-size:12px">
                    💳 Bayar
                  </button>
                @endif
                @if ($paid > 0)
                  <button class="btn btn-sm" data-dialog="#receipt-{{ $bill->id }}" style="height:30px;padding:0 12px;font-size:12px">
                    🧾 Kuitansi
                  </button>
                @endif
                <form method="POST" action="{{ route('bills.destroy', $bill) }}" data-confirm="Hapus permanen tagihan {{ $bill->paymentType?->name }} untuk {{ $bill->student?->full_name }}?">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-danger" style="height:30px;padding:0 10px;font-size:12px">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="empty">Belum ada catatan tagihan yang sesuai filter.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Dialog Pencatatan Pembayaran & Kuitansi --}}
@foreach ($bills as $bill)
@php $alreadyPaid = (float) $bill->payments->sum('amount_paid'); @endphp

{{-- Modal Pembayaran --}}
<dialog class="dlg" id="pay-{{ $bill->id }}">
  <h3>Pencatatan Pembayaran — {{ $bill->student?->full_name }}</h3>
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
</dialog>

{{-- Modal Cetak Kuitansi / Bukti Bayar Resmi --}}
<dialog class="dlg" id="receipt-{{ $bill->id }}" style="max-width:540px">
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
</dialog>
@endforeach
@endsection
