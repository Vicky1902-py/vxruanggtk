@extends('layouts.app')
@section('title', 'Penggajian & Slip Gaji GTK')

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <span class="cs-pill" style="font-size:11px;padding:3px 12px"><span class="dot" style="background:#0284c7"></span> Kepegawaian &amp; Keuangan</span>
      <span style="font-size:12px;color:var(--muted)">Penggajian Tenaga Pendidik &amp; Kependidikan</span>
    </div>
    <h1>Penggajian GTK (Payroll)</h1>
    <div class="sub">Perhitungan honorarium, penerbitan slip gaji resmi, dan rekapitulasi realisasi belanja pegawai.</div>
  </div>
  <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
    <a href="{{ route('payrolls.export', ['period' => $period]) }}" class="btn btn-sm" title="Unduh rekapitulasi penggajian ke Excel (.xlsx)">
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
      Export Excel (.xlsx)
    </a>

    @if ($payrolls->isNotEmpty() && $payrolls->where('status', '!=', 'terbayar')->isNotEmpty())
      <form method="POST" action="{{ route('payrolls.disburse-all') }}">
        @csrf
        <input type="hidden" name="period" value="{{ $period }}">
        <button class="btn btn-sm" style="border-color:#10b981;color:#065f46;background:#d1fae5;font-weight:700">
          💸 Cairkan Semua Gaji ({{ $payrolls->where('status', '!=', 'terbayar')->count() }})
        </button>
      </form>
    @endif

    <form method="POST" action="{{ route('payrolls.generate') }}">
      @csrf
      <input type="hidden" name="period" value="{{ $period }}">
      <button class="btn btn-sm btn-ink" data-loading="Men-generate Payroll...">
        ⚡ Generate Slip Gaji Bulan Ini
      </button>
    </form>
  </div>
</div>

{{-- Metric Card Banner --}}
<div class="dash-grid-4" style="margin-bottom:18px">
  <div class="kpi-card glass">
    <div class="label">
      <span>Total Pengeluaran Gaji Bersih</span>
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:#0284c7;fill:none;stroke-width:2"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
    </div>
    <div class="val" style="color:#0284c7">Rp {{ number_format($totalNetSalary, 0, ',', '.') }}</div>
    <div class="note">Periode {{ \Illuminate\Support\Carbon::parse($period . '-01')->translatedFormat('F Y') }}</div>
  </div>
  <div class="kpi-card glass kpi-success">
    <div class="label">
      <span>Realisasi Gaji Terbayar</span>
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:#10b981;fill:none;stroke-width:2"><polyline points="20 6 9 17 4 12"/></svg>
    </div>
    <div class="val" style="color:#10b981">Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
    <div class="note">{{ $paidCount }} dari {{ $payrolls->count() }} slip telah disalurkan</div>
  </div>
  <div class="kpi-card glass kpi-danger">
    <div class="label">
      <span>Total Potongan</span>
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:#ef4444;fill:none;stroke-width:2"><circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><line x1="20" y1="4" x2="8.12" y2="15.88"/><line x1="14.47" y1="14.48" x2="20" y2="20"/><line x1="8.12" y1="8.12" x2="12" y2="12"/></svg>
    </div>
    <div class="val" style="color:#ef4444">Rp {{ number_format($totalDeductions, 0, ',', '.') }}</div>
    <div class="note">Absensi &amp; potongan lain</div>
  </div>
  <div class="kpi-card glass kpi-purple">
    <div class="label">
      <span>Total GTK Terdaftar</span>
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:#6366f1;fill:none;stroke-width:2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    </div>
    <div class="val" style="color:#6366f1">{{ $employees->count() }} Orang</div>
    <div class="note">Guru &amp; tenaga kependidikan aktif</div>
  </div>
</div>

<div class="glass panel">
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:16px">
    <h2 class="panel-title" style="margin:0">
      <svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M7 15h0M2 9.5h20"/></svg>
      Daftar Penggajian GTK — {{ \Illuminate\Support\Carbon::parse($period . '-01')->translatedFormat('F Y') }}
    </h2>

    <form method="GET" style="display:flex;gap:8px;align-items:center">
      <label style="font-size:12.5px;color:var(--muted)">Periode Bulan:</label>
      <input type="month" name="period" class="input" value="{{ $period }}" onchange="this.form.submit()" style="width:170px;height:36px;font-size:12.5px">
    </form>
  </div>

  <div class="table-wrap" style="border:none;box-shadow:none">
    <table class="tbl">
      <thead>
        <tr>
          <th>Pegawai (GTK)</th>
          <th>Jabatan</th>
          <th style="text-align:right">Gaji Pokok</th>
          <th style="text-align:right">Potongan</th>
          <th style="text-align:right">Gaji Bersih</th>
          <th style="text-align:center">Status</th>
          <th style="text-align:right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($employees as $emp)
          @php
            $p = $payrolls->get($emp->id);
          @endphp
          <tr>
            <td>
              <b style="color:var(--text);font-size:13.5px">{{ $emp->full_name }}</b>
              <div style="font-size:11.5px;color:var(--muted)">NIP: {{ $emp->nip ?? '—' }}</div>
            </td>
            <td>
              <span class="badge badge-slate">{{ $emp->position?->name ?? 'Staf / Pendidik' }}</span>
            </td>
            <td style="text-align:right;font-weight:600;color:var(--text)">
              @if ($p)
                Rp {{ number_format($p->gross_amount, 0, ',', '.') }}
              @else
                <span style="color:var(--muted)">Rp {{ number_format($emp->position?->base_salary ?? 0, 0, ',', '.') }}</span>
              @endif
            </td>
            <td style="text-align:right;color:#ef4444;font-weight:600">
              @if ($p)
                - Rp {{ number_format($p->deductions, 0, ',', '.') }}
              @else
                <span style="color:var(--muted)">Rp 0</span>
              @endif
            </td>
            <td style="text-align:right;font-weight:700;color:#0284c7;font-size:14px">
              @if ($p)
                Rp {{ number_format($p->net_amount, 0, ',', '.') }}
              @else
                <span class="badge badge-warn">Draft Otomatis</span>
              @endif
            </td>
            <td style="text-align:center">
              @if ($p)
                @if ($p->status === 'terbayar')
                  <span class="badge badge-ok" title="Gaji telah disalurkan pada {{ $p->paid_at?->format('d/m/Y') }}">✓ Terbayar</span>
                @else
                  <span class="badge badge-warn">Draft / Belum Bayar</span>
                @endif
              @else
                <span class="badge badge-slate">—</span>
              @endif
            </td>
            <td style="text-align:right">
              @if ($p)
                <div class="actions" style="justify-content:flex-end;gap:4px">
                  @if ($p->status !== 'terbayar')
                    <form method="POST" action="{{ route('payrolls.disburse', $p) }}" style="display:inline">
                      @csrf
                      <button class="btn btn-sm btn-ink" style="padding:4px 8px;font-size:11px" title="Tandai gaji telah ditransfer/dibayar">
                        💸 Bayar
                      </button>
                    </form>
                  @endif
                  <button class="btn btn-sm" data-dialog="#dlg-edit-payroll-{{ $p->id }}" style="padding:4px 8px;font-size:11px">Edit</button>
                  <a href="{{ route('payrolls.slip', $p) }}" target="_blank" class="btn btn-sm btn-outline" title="Cetak slip gaji resmi" style="padding:4px 8px;font-size:11px">
                    🖨️ Slip
                  </a>
                </div>
              @else
                <form method="POST" action="{{ route('payrolls.generate') }}">
                  @csrf
                  <input type="hidden" name="period" value="{{ $period }}">
                  <button class="btn btn-sm btn-outline" style="padding:4px 8px;font-size:11px">Generate</button>
                </form>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="empty">Tidak ada data pegawai.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- MODAL EDIT PAYROLL --}}
@foreach ($payrolls as $p)
  <dialog id="dlg-edit-payroll-{{ $p->id }}" class="modal glass">
    <div class="modal-box">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <h3 style="margin:0;font-size:17px;color:var(--text)">Sesuaikan Nominal: {{ $p->employee?->full_name }}</h3>
        <button type="button" class="btn btn-sm" data-close>✕</button>
      </div>
      <form method="POST" action="{{ route('payrolls.update', $p) }}" class="stack">
        @csrf @method('PUT')
        <div class="field">
          <label>Gaji Pokok &amp; Tunjangan (Rp) *</label>
          <input type="number" name="gross_amount" class="input" value="{{ (int) $p->gross_amount }}" min="0" step="1000" required>
        </div>
        <div class="field">
          <label>Total Potongan (Rp) *</label>
          <input type="number" name="deductions" class="input" value="{{ (int) $p->deductions }}" min="0" step="1000" required>
        </div>
        <div class="modal-actions" style="margin-top:16px">
          <button type="button" class="btn" data-close>Batal</button>
          <button class="btn btn-ink">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </dialog>
@endforeach

@endsection
