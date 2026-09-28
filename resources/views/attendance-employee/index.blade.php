@extends('layouts.app')
@section('title', 'Presensi Pegawai & GTK')

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <span class="cs-pill" style="font-size:11px;padding:3px 12px"><span class="dot"></span> Modul GTK</span>
      <span style="font-size:12px;color:var(--muted)">Presensi &amp; Disiplin Tenaga Kependidikan</span>
    </div>
    <h1>Presensi Pegawai (GTK)</h1>
    <div class="sub">Pencatatan kehadiran harian guru/staf dan rekapitulasi leger kehadiran bulanan.</div>
  </div>

  {{-- Switch Tab Mode --}}
  <div style="display:flex;gap:6px;background:rgba(255,255,255,0.04);padding:4px;border-radius:var(--radius-pill);border:1px solid var(--line-light)">
    <a href="{{ route('attendance-employee.index', ['mode' => 'harian', 'date' => $date]) }}" 
       class="btn btn-sm {{ $mode === 'harian' ? 'btn-ink' : '' }}" 
       style="height:32px;font-size:12px;border-radius:var(--radius-pill)">
      📅 Harian
    </a>
    <a href="{{ route('attendance-employee.index', ['mode' => 'rekap', 'month' => $month]) }}" 
       class="btn btn-sm {{ $mode === 'rekap' ? 'btn-ink' : '' }}" 
       style="height:32px;font-size:12px;border-radius:var(--radius-pill)">
      📊 Rekap Bulanan
    </a>
  </div>
</div>

{{-- Banner Check-in Mandiri (Jika akun terhubung ke pegawai) --}}
@if ($myEmployee)
<div class="glass panel" style="margin-bottom:18px;border-left:4px solid var(--accent)">
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
    <div>
      <div style="font-size:12px;color:var(--muted)">Check-in Mandiri Hari Ini ({{ \Illuminate\Support\Carbon::now()->translatedFormat('l, d F Y') }})</div>
      <h3 style="font-size:16px;color:var(--text);margin-top:2px">{{ $myEmployee->full_name }} — {{ $myEmployee->position?->name ?? 'GTK' }}</h3>
    </div>

    @if ($myTodayAttendance)
      <div style="display:flex;align-items:center;gap:10px">
        <span style="font-size:13px;color:var(--muted)">Status Hari Ini:</span>
        @php
          $attBadge = match($myTodayAttendance->status) {
            'hadir' => 'badge-ok',
            'telat' => 'badge-warn',
            'izin'  => 'badge-blue',
            default => 'badge-bad'
          };
        @endphp
        <span class="badge {{ $attBadge }}" style="font-size:13px;padding:4px 14px">
          {{ strtoupper($myTodayAttendance->status) }}
        </span>
      </div>
    @else
      <form method="POST" action="{{ route('attendance-employee.checkin') }}" style="display:flex;gap:8px">
        @csrf
        <button name="status" value="hadir" class="btn btn-sm btn-ink" style="height:34px;font-size:12px">🟢 Hadir Sekarang</button>
        <button name="status" value="telat" class="btn btn-sm" style="height:34px;font-size:12px">🟡 Terlambat</button>
        <button name="status" value="izin" class="btn btn-sm" style="height:34px;font-size:12px">🔵 Izin / Dinas</button>
      </form>
    @endif
  </div>
</div>
@endif

{{-- Filter Box --}}
<div class="glass panel" style="margin-bottom:18px">
  <form method="GET" class="form-grid" style="grid-template-columns:1fr auto">
    <input type="hidden" name="mode" value="{{ $mode }}">

    @if ($mode === 'harian')
      <div class="field">
        <label>Tanggal Presensi</label>
        <input type="date" name="date" class="input" value="{{ $date }}" onchange="this.form.submit()">
      </div>
    @else
      <div class="field">
        <label>Bulan Rekapitulasi</label>
        <input type="month" name="month" class="input" value="{{ $month }}" onchange="this.form.submit()">
      </div>
    @endif

    <div style="display:flex;align-items:flex-end">
      @if ($mode === 'rekap')
        <button type="button" class="btn btn-ink" onclick="window.print()" style="height:44px;gap:6px">
          🖨️ Cetak Rekap GTK
        </button>
      @endif
    </div>
  </form>
</div>

@if ($mode === 'harian')
  {{-- ==================== MODE HARIAN (KELOLA GTK) ==================== --}}
  <form method="POST" action="{{ route('attendance-employee.store') }}">
    @csrf
    <input type="hidden" name="att_date" value="{{ $date }}">
    
    <div class="glass panel" style="display:grid;gap:10px">
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding-bottom:12px;border-bottom:1px solid var(--line-light)">
        <div style="font-size:13.5px;font-weight:600;color:var(--text)">
          Daftar Tenaga GTK ({{ $employees->count() }} orang)
        </div>
        <div style="display:flex;gap:8px;font-size:12px">
          <span class="badge badge-ok">Hadir</span>
          <span class="badge badge-warn">Telat</span>
          <span class="badge badge-blue">Izin</span>
          <span class="badge badge-bad">Alpa</span>
        </div>
      </div>

      @forelse ($employees as $emp)
        @php $att = $existing->get($emp->id); @endphp
        <div class="att-row">
          <div class="att-name">
            <b>{{ $emp->full_name }}</b>
            <small>NIP: {{ $emp->nip ?? '—' }} · {{ $emp->position?->name ?? 'Staf Umum' }}</small>
          </div>
          <div class="seg" role="radiogroup" aria-label="Status {{ $emp->full_name }}">
            @foreach (['hadir' => 'Hadir', 'telat' => 'Telat', 'izin' => 'Izin', 'alpa' => 'Alpa'] as $st => $label)
              <label>
                <input type="radio" class="att-radio" name="statuses[{{ $emp->id }}]" value="{{ $st }}"
                  {{ ($att?->status ?? 'hadir') === $st ? 'checked' : '' }}>
                <span>{{ $label }}</span>
              </label>
            @endforeach
          </div>
        </div>
      @empty
        <div class="empty">Belum ada pegawai aktif di sekolah Anda.</div>
      @endforelse
    </div>

    @if ($employees->isNotEmpty() && in_array(auth()->user()?->role?->name, ['admin', 'staff_tu']))
      <div style="margin-top:16px;display:flex;gap:12px;align-items:center;flex-wrap:wrap">
        <button class="btn btn-ink" data-loading="Menyimpan Presensi Pegawai...">
          💾 Simpan Presensi GTK
        </button>
        <span class="cs-pill" style="font-size:12px">
          📅 {{ \Illuminate\Support\Carbon::parse($date)->translatedFormat('l, d F Y') }}
        </span>
        <span style="font-size:13px;color:var(--muted)">Tersimpan langsung ke rekap absensi kepegawaian.</span>
      </div>
    @endif
  </form>

@else
  {{-- ==================== MODE REKAP BULANAN GTK ==================== --}}
  <div class="glass table-wrap" style="overflow-x:auto">
    <div style="padding:14px 18px;border-bottom:1px solid var(--line-light);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
      <div>
        <h3 style="font-size:16px;color:var(--text);margin:0">
          Matriks Rekapitulasi Presensi GTK — {{ \Illuminate\Support\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
        </h3>
        <div style="font-size:12.5px;color:var(--muted)">
          Total {{ $employees->count() }} Pendidik &amp; Tenaga Kependidikan
        </div>
      </div>
      <div style="display:flex;gap:6px;font-size:11px">
        <span style="display:inline-flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:2px;background:#10b981;display:inline-block"></span> H: Hadir</span>
        <span style="display:inline-flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:2px;background:#f59e0b;display:inline-block"></span> T: Telat</span>
        <span style="display:inline-flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:2px;background:#38bdf8;display:inline-block"></span> I: Izin</span>
        <span style="display:inline-flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:2px;background:#ef4444;display:inline-block"></span> A: Alpa</span>
      </div>
    </div>

    <table class="tbl" style="font-size:12px;white-space:nowrap;margin:0">
      <thead>
        <tr>
          <th style="width:30px">No</th>
          <th>Nama Pegawai (GTK)</th>
          <th>Jabatan</th>
          @for ($d = 1; $d <= $daysInMonth; $d++)
            <th style="padding:6px 4px;text-align:center;width:24px">{{ $d }}</th>
          @endfor
          <th style="text-align:center;color:#10b981">H</th>
          <th style="text-align:center;color:#f59e0b">T</th>
          <th style="text-align:center;color:#38bdf8">I</th>
          <th style="text-align:center;color:#ef4444">A</th>
          <th style="text-align:center">%</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($employees as $idx => $emp)
          @php
            $m = $rekapMatrix[$emp->id] ?? [];
            $sum = $rekapSummary[$emp->id] ?? ['hadir' => 0, 'telat' => 0, 'izin' => 0, 'alpa' => 0, 'percentage' => 0];
          @endphp
          <tr>
            <td>{{ $idx + 1 }}</td>
            <td><b style="color:var(--text)">{{ $emp->full_name }}</b></td>
            <td><span class="badge badge-blue" style="font-size:10.5px">{{ $emp->position?->name ?? 'Staf' }}</span></td>
            @for ($d = 1; $d <= $daysInMonth; $d++)
              @php
                $st = $m[$d] ?? null;
                $stColor = match($st) {
                  'hadir' => '#10b981',
                  'telat' => '#f59e0b',
                  'izin'  => '#38bdf8',
                  'alpa'  => '#ef4444',
                  default => 'transparent'
                };
                $stChar = match($st) {
                  'hadir' => 'H',
                  'telat' => 'T',
                  'izin'  => 'I',
                  'alpa'  => 'A',
                  default => '·'
                };
              @endphp
              <td style="padding:4px 2px;text-align:center">
                @if ($st)
                  <span style="display:inline-block;width:20px;height:20px;line-height:20px;border-radius:4px;background:{{ $stColor }}25;color:{{ $stColor }};font-weight:700;font-size:10px">
                    {{ $stChar }}
                  </span>
                @else
                  <span style="color:var(--line-light);font-size:11px">·</span>
                @endif
              </td>
            @endfor
            <td style="text-align:center;font-weight:700;color:#10b981">{{ $sum['hadir'] }}</td>
            <td style="text-align:center;font-weight:700;color:#f59e0b">{{ $sum['telat'] }}</td>
            <td style="text-align:center;font-weight:700;color:#38bdf8">{{ $sum['izin'] }}</td>
            <td style="text-align:center;font-weight:700;color:#ef4444">{{ $sum['alpa'] }}</td>
            <td style="text-align:center">
              <span class="badge {{ $sum['percentage'] >= 85 ? 'badge-ok' : ($sum['percentage'] >= 70 ? 'badge-warn' : 'badge-bad') }}" style="font-size:10.5px">
                {{ $sum['percentage'] }}%
              </span>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="{{ $daysInMonth + 9 }}" class="empty">Tidak ada data pegawai aktif.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endif
@endsection
