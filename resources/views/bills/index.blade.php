@extends('layouts.app')
@section('title', 'Tagihan & Pembayaran')

@section('content')
<div class="page-head">
  <div>
    <h1>Tagihan &amp; Pembayaran</h1>
    <div class="sub">Generate tagihan massal dan catat pembayaran siswa.</div>
  </div>
</div>

<div class="two-col">
  <div class="stack">
    <div class="glass panel">
      <h2 class="panel-title"><svg viewBox="0 0 24 24"><path d="M6 2.5h9L19 7v14H6z"/><path d="M14 2.5V7h5"/><path d="M9 12h6M9 15.5h6"/></svg> Generate Tagihan</h2>
      <form method="POST" action="{{ route('bills.store') }}" class="stack">
        @csrf
        <div class="field">
          <label>Jenis Tagihan</label>
          <select name="payment_type_id" id="billTypeSelect" class="select" required>
            @foreach ($paymentTypes as $pt)
              <option value="{{ $pt->id }}" data-amount="{{ $pt->default_amount }}">
                {{ $pt->name }} — Rp {{ number_format($pt->default_amount, 0, ',', '.') }} ({{ $pt->recurrence }})
              </option>
            @endforeach
          </select>
        </div>
        <div class="field">
          <label>Target</label>
          <select name="class_id" class="select">
            <option value="">🎯 Semua siswa aktif</option>
            @foreach ($classes as $class)
              <option value="{{ $class->id }}">Kelas {{ $class->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-grid" style="grid-template-columns:1fr 1fr">
          <div class="field"><label>Nominal (Rp)</label><input type="number" name="amount" id="billAmountInput" class="input" min="0" step="500" required></div>
          <div class="field"><label>Jatuh Tempo</label><input type="date" name="due_date" class="input" required></div>
        </div>
        <button class="btn btn-ink" data-loading="Membuat tagihan...">Buat Tagihan Massal</button>
      </form>
    </div>

    <div class="glass panel">
      <h2 class="panel-title"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M16.5 16.5L21 21"/></svg> Filter</h2>
      <form method="GET" class="stack">
        <div class="field">
          <label>Status</label>
          <select name="status" class="select" onchange="this.form.submit()">
            <option value="">Semua status</option>
            @foreach (['belum_bayar' => 'Belum Bayar', 'sebagian' => 'Sebagian', 'lunas' => 'Lunas'] as $key => $label)
              <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="field">
          <label>Kelas</label>
          <select name="class_id" class="select" onchange="this.form.submit()">
            <option value="">Semua kelas</option>
            @foreach ($classes as $class)
              <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
            @endforeach
          </select>
        </div>
        @if (request()->anyFilled(['status', 'class_id']))
          <a href="{{ route('bills.index') }}" class="btn btn-sm">Reset</a>
        @endif
      </form>
    </div>
  </div>

  <div class="glass table-wrap">
    <table class="tbl">
      <thead><tr><th>Siswa</th><th>Jenis</th><th>Nominal</th><th>Tempo</th><th>Status</th><th></th></tr></thead>
      <tbody>
        @forelse ($bills as $bill)
          @php $paid = (float) $bill->payments->sum('amount_paid'); @endphp
          <tr>
            <td><b>{{ $bill->student?->full_name ?? '—' }}</b><br><small style="color:var(--muted)">{{ $bill->student?->schoolClass?->name ?? 'Tanpa kelas' }}</small></td>
            <td>{{ $bill->paymentType?->name }}</td>
            <td>Rp {{ number_format($bill->amount, 0, ',', '.') }}
              @if ($paid > 0)<br><small style="color:var(--green)">Dibayar Rp {{ number_format($paid, 0, ',', '.') }}</small>@endif
            </td>
            <td>{{ $bill->due_date?->format('d M Y') ?? '—' }}</td>
            <td>
              @php
                $cls = match ($bill->status) { 'lunas' => 'badge-ok', 'sebagian' => 'badge-warn', default => 'badge-bad' };
              @endphp
              <span class="badge {{ $cls }}">{{ ['belum_bayar' => 'Belum Bayar', 'sebagian' => 'Sebagian', 'lunas' => 'Lunas'][$bill->status] }}</span>
            </td>
            <td>
              @if ($bill->status !== 'lunas')
                <button class="btn btn-sm btn-ink" data-dialog="#pay-{{ $bill->id }}"><svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18"/></svg> Bayar</button>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="empty">Belum ada tagihan.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@foreach ($bills as $bill)
<dialog class="dlg" id="pay-{{ $bill->id }}">
  <h3>Pembayaran — {{ $bill->student?->full_name }}</h3>
  <p style="font-size:13.5px;color:var(--muted-2);margin-bottom:14px">
    {{ $bill->paymentType?->name }} · Rp {{ number_format($bill->amount, 0, ',', '.') }}
  </p>
  <form method="POST" action="{{ route('bills.pay', $bill) }}" class="stack">
    @csrf
    <div class="field">
      <label>Nominal Dibayar (Rp) *</label>
      <input type="number" name="amount_paid" class="input" min="1" step="500" required>
    </div>
    <div class="field">
      <label>Metode</label>
      <select name="method" class="select">
        @foreach (['Tunai', 'Transfer', 'VA', 'QRIS', 'Retail'] as $m)
          <option value="{{ $m }}">{{ $m }}</option>
        @endforeach
      </select>
    </div>
    <div class="dlg-actions">
      <button type="button" class="btn" data-close>Batal</button>
      <button class="btn btn-ink">Catat Pembayaran</button>
    </div>
  </form>
</dialog>
@endforeach
@endsection
