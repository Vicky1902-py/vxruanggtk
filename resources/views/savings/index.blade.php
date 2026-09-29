@extends('layouts.app')
@section('title', 'Tabungan Siswa')

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <span class="cs-pill" style="font-size:11px;padding:3px 12px"><span class="dot" style="background:#10b981;box-shadow:0 0 10px #10b981"></span> Modul Keuangan</span>
      <span style="font-size:12px;color:var(--muted)">Simpanan &amp; Mutasi Tabungan Peserta Didik</span>
    </div>
    <h1>Tabungan Siswa</h1>
    <div class="sub">Pencatatan setor-tarik tunai, buku mutasi tabungan siswa, dan rekap saldo kas titipan.</div>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <button type="button" class="btn btn-sm btn-ink" data-dialog="#dialog-create-saving-tx">
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      + Catat Transaksi Tabungan
    </button>
    <a href="{{ route('savings.export', request()->all()) }}" class="btn btn-sm" title="Unduh rekap saldo tabungan seluruh siswa ke Excel (.xlsx)">
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
      Export Excel (.xlsx)
    </a>
  </div>
</div>

{{-- Metric Card Banner --}}
<div class="dash-grid-4" style="margin-bottom:18px">
  <div class="kpi-card glass">
    <div class="label">
      <span>Total Saldo Kas Tabungan</span>
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:#0284c7;fill:none;stroke-width:2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
    </div>
    <div class="val" style="color:#0284c7">Rp {{ number_format($totalBalance, 0, ',', '.') }}</div>
    <div class="note">Total simpanan siswa aktif sekolah</div>
  </div>
  <div class="kpi-card glass kpi-success">
    <div class="label">
      <span>Total Setoran Masuk</span>
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:#10b981;fill:none;stroke-width:2"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
    </div>
    <div class="val" style="color:#10b981">Rp {{ number_format($totalDeposits, 0, ',', '.') }}</div>
    <div class="note">Akumulasi penerimaan tabungan</div>
  </div>
  <div class="kpi-card glass kpi-danger">
    <div class="label">
      <span>Total Penarikan Tunai</span>
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:#ef4444;fill:none;stroke-width:2"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
    </div>
    <div class="val" style="color:#ef4444">Rp {{ number_format($totalWithdrawals, 0, ',', '.') }}</div>
    <div class="note">Akumulasi pencairan simpanan</div>
  </div>
  <div class="kpi-card glass kpi-purple">
    <div class="label">
      <span>Rekening Siswa Aktif</span>
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:#6366f1;fill:none;stroke-width:2"><circle cx="9" cy="7" r="4"/><path d="M17 11a3 3 0 1 0-4-2.82"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    </div>
    <div class="val" style="color:#6366f1">{{ $activeAccountsCount }} Rekening</div>
    <div class="note">{{ $recentTransactions->count() }} mutasi transaksi terbaru</div>
  </div>
</div>

{{-- Tabel Saldo Siswa Full Width --}}
<div class="glass panel" style="display:flex;flex-direction:column;gap:14px">
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
    <h2 class="panel-title" style="margin:0">
      <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8h10M7 12h10M7 16h6"/></svg>
      Daftar Saldo Tabungan Siswa
    </h2>
  </div>

    {{-- Filter Mini --}}
    <form method="GET" style="display:grid;grid-template-columns:1.5fr 1fr auto;gap:8px">
      <input type="text" name="q" class="input" value="{{ request('q') }}" placeholder="Cari nama / NIS...">
      <select name="class_id" class="select" onchange="this.form.submit()">
        <option value="">Semua Kelas</option>
        @foreach ($classes as $c)
          <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>Kelas {{ $c->name }}</option>
        @endforeach
      </select>
      <button class="btn btn-sm btn-ink">Cari</button>
    </form>

    <div class="table-wrap" style="border:none;box-shadow:none">
      <table class="tbl">
        <thead>
          <tr>
            <th>Siswa</th>
            <th>Kelas</th>
            <th style="text-align:right">Saldo Tabungan</th>
            <th style="text-align:right">Buku Mutasi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($students as $st)
            <tr>
              <td>
                <b style="color:var(--text);font-size:13.5px">{{ $st->full_name }}</b>
                <div style="font-size:11.5px;color:var(--muted)">NIS: {{ $st->nis ?? '—' }}</div>
              </td>
              <td>
                @if ($st->schoolClass)
                  <span class="badge badge-blue">Kelas {{ $st->schoolClass->name }}</span>
                @else
                  <span style="color:var(--muted)">—</span>
                @endif
              </td>
              <td style="text-align:right;font-weight:700;color:#0284c7;font-size:14px">
                Rp {{ number_format($st->savingAccount?->balance ?? 0, 0, ',', '.') }}
              </td>
              <td style="text-align:right">
                <a href="{{ route('savings.show', $st) }}" class="btn btn-sm" title="Lihat buku mutasi tabungan siswa">
                  📖 Buku Mutasi
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="empty">Tidak ada data siswa yang cocok.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($students->hasPages())
      <div style="margin-top:10px">{{ $students->links() }}</div>
    @endif
  </div>

{{-- MODAL CATAT TRANSAKSI TABUNGAN --}}
<dialog id="dialog-create-saving-tx" class="modal glass">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
      <h3 style="margin:0;font-size:17px;color:var(--text);display:flex;align-items:center;gap:8px">
        <svg viewBox="0 0 24 24" style="width:20px;height:20px;stroke:var(--accent);fill:none;stroke-width:2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        Catat Transaksi Tabungan Siswa
      </h3>
      <button type="button" data-close class="modal-close" aria-label="Tutup">✕</button>
    </div>
    <form method="POST" action="{{ route('savings.store') }}" class="stack">
      @csrf
      <div class="field">
        <label>Pilih Peserta Didik *</label>
        <select name="student_id" class="select" required>
          <option value="">— Cari / Pilih Nama Siswa —</option>
          @foreach ($students as $st)
            <option value="{{ $st->id }}" {{ old('student_id') == $st->id ? 'selected' : '' }}>
              {{ $st->full_name }} ({{ $st->schoolClass ? 'Kelas ' . $st->schoolClass->name : 'Tanpa Kelas' }}) — Saldo: Rp {{ number_format($st->savingAccount?->balance ?? 0, 0, ',', '.') }}
            </option>
          @endforeach
        </select>
        @error('student_id') <span class="error-text">{{ $message }}</span> @enderror
      </div>

      <div class="field">
        <label>Jenis Transaksi *</label>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <label style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:#ffffff;border:1.5px solid #cbd5e1;border-radius:var(--radius-sm);cursor:pointer">
            <input type="radio" name="direction" value="setor" checked>
            <b style="color:#10b981">📥 Setor Tabungan</b>
          </label>
          <label style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:#ffffff;border:1.5px solid #cbd5e1;border-radius:var(--radius-sm);cursor:pointer">
            <input type="radio" name="direction" value="tarik">
            <b style="color:#ef4444">📤 Tarik Tabungan</b>
          </label>
        </div>
      </div>

      <div class="field">
        <label>Nominal Transaksi (Rp) *</label>
        <input type="number" name="amount" class="input" min="1000" step="500" placeholder="mis. 50000" required>
        @error('amount') <span class="error-text">{{ $message }}</span> @enderror
      </div>

      <div class="field">
        <label>Catatan / Keterangan (Opsional)</label>
        <input type="text" name="note" class="input" placeholder="mis. Setoran mingguan / uang saku lomba">
      </div>

      <div class="modal-actions" style="margin-top:16px">
        <button type="button" class="btn" data-close>Batal</button>
        <button class="btn btn-ink" data-loading="Menyimpan Transaksi...">💾 Simpan Transaksi</button>
      </div>
    </form>
  </div>
</dialog>

{{-- Riwayat Transaksi Terakhir --}}
<div class="glass panel" style="margin-top:20px">
  <h2 class="panel-title">
    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    10 Transaksi Tabungan Terkini
  </h2>
  <div class="table-wrap" style="border:none;box-shadow:none">
    <table class="tbl">
      <thead>
        <tr>
          <th>Waktu</th>
          <th>Siswa</th>
          <th>Kelas</th>
          <th>Jenis</th>
          <th>Keterangan</th>
          <th style="text-align:right">Nominal</th>
          <th style="text-align:right">Saldo Setelah Transaksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($recentTransactions as $tx)
          <tr>
            <td style="font-size:12px;color:var(--muted)">
              {{ $tx->created_at?->translatedFormat('d M Y, H:i') }}
            </td>
            <td>
              <b style="color:var(--text)">{{ $tx->student?->full_name }}</b>
            </td>
            <td>
              @if ($tx->student?->schoolClass)
                <span class="badge badge-blue">Kelas {{ $tx->student->schoolClass->name }}</span>
              @else
                —
              @endif
            </td>
            <td>
              @if ($tx->direction === 'setor')
                <span class="badge badge-ok">📥 Setor</span>
              @else
                <span class="badge badge-bad">📤 Tarik</span>
              @endif
            </td>
            <td style="font-size:12.5px;color:var(--muted)">
              {{ $tx->note ?? '-' }}
            </td>
            <td style="text-align:right;font-weight:700;color:{{ $tx->direction === 'setor' ? '#10b981' : '#ef4444' }}">
              {{ $tx->direction === 'setor' ? '+' : '-' }} Rp {{ number_format($tx->amount, 0, ',', '.') }}
            </td>
            <td style="text-align:right;font-weight:600;color:var(--text)">
              Rp {{ number_format($tx->balance_after, 0, ',', '.') }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="empty">Belum ada transaksi tabungan tercatat.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
