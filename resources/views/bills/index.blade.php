@extends('layouts.app')
@section('title', 'Tagihan & Pembayaran')

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <span class="vtx-pill" style="font-size:11px;padding:3px 12px"><span class="dot" style="background:var(--amber);box-shadow:0 0 10px var(--amber)"></span> Modul Keuangan</span>
      <span style="font-size:12px;color:var(--muted)">Manajemen Tagihan &amp; SPP Siswa</span>
    </div>
    <h1>Tagihan &amp; Pembayaran</h1>
    <div class="sub">Generate tagihan massal per rombel dan catat transaksi pembayaran peserta didik.</div>
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
                  <button class="btn btn-sm btn-ink" data-dialog="#pay-{{ $bill->id }}" style="height:32px;padding:0 14px">
                    💳 Bayar
                  </button>
                @else
                  <span style="color:var(--green);font-size:12px;font-weight:600">✓ Selesai</span>
                @endif
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

{{-- Dialog Pencatatan Pembayaran --}}
@foreach ($bills as $bill)
<dialog class="dlg" id="pay-{{ $bill->id }}">
  <h3>Pencatatan Pembayaran — {{ $bill->student?->full_name }}</h3>
  <div style="padding:12px 14px;border-radius:var(--radius-sm);background:rgba(255,255,255,0.04);border:1px solid var(--line-light);margin-bottom:14px">
    <div style="font-size:13px;color:var(--muted)">Tagihan: <b style="color:var(--text)">{{ $bill->paymentType?->name }}</b></div>
    <div style="font-size:14px;color:var(--amber);margin-top:2px;font-weight:700">
      Total: Rp {{ number_format($bill->amount, 0, ',', '.') }}
      @php $alreadyPaid = (float) $bill->payments->sum('amount_paid'); @endphp
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
@endforeach
@endsection
