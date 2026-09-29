@extends('layouts.app')
@section('title', 'Presensi Siswa')

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <span class="cs-pill" style="font-size:11px;padding:3px 12px"><span class="dot"></span> Modul Presensi</span>
      <span style="font-size:12px;color:var(--muted)">Kehadiran &amp; Rekapitulasi Akademik</span>
    </div>
    <h1>Presensi Siswa</h1>
    <div class="sub">Pencatatan kehadiran harian per kelas dan rekapitulasi leger kehadiran bulanan.</div>
  </div>

  <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
    @if ($classId)
    <a href="{{ route('attendance.export', ['class_id' => $classId, 'month' => $month]) }}" class="btn btn-sm" title="Unduh rekap presensi kelas ini ke Excel (.xlsx)">
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
      Export Rekap (.xlsx)
    </a>
    @endif

    {{-- Switch Tab Mode --}}
    <div style="display:flex;gap:6px;background:#ffffff;padding:4px;border-radius:var(--radius-pill);border:1px solid #cbd5e1;box-shadow:0 2px 8px rgba(0,0,0,0.04)">
      <a href="{{ route('attendance.index', ['mode' => 'harian', 'class_id' => $classId, 'date' => $date]) }}" 
         class="btn btn-sm {{ $mode === 'harian' ? 'btn-ink' : '' }}" 
         style="height:32px;font-size:12px;border-radius:var(--radius-pill)">
        📅 Harian
      </a>
      <a href="{{ route('attendance.index', ['mode' => 'rekap', 'class_id' => $classId, 'month' => $month]) }}" 
         class="btn btn-sm {{ $mode === 'rekap' ? 'btn-ink' : '' }}" 
         style="height:32px;font-size:12px;border-radius:var(--radius-pill)">
        📊 Rekap Bulanan
      </a>
    </div>
  </div>
</div>

{{-- Filter Box --}}
<div class="glass panel" style="margin-bottom:18px">
  <form method="GET" class="form-grid" style="grid-template-columns:1fr 1fr auto">
    <input type="hidden" name="mode" value="{{ $mode }}">

    <div class="field">
      <label>Pilih Rombongan Belajar (Kelas)</label>
      <select name="class_id" class="select" onchange="this.form.submit()">
        @foreach ($classes as $class)
          <option value="{{ $class->id }}" {{ (string) $classId === (string) $class->id ? 'selected' : '' }}>
            Kelas {{ $class->name }} ({{ $class->academicYear?->year_label ?? 'Aktif' }})
          </option>
        @endforeach
      </select>
    </div>

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
          🖨️ Cetak Rekap
        </button>
      @endif
    </div>
  </form>
</div>

@if ($mode === 'harian')
  {{-- ==================== MODE HARIAN ==================== --}}
  <form method="POST" action="{{ route('attendance.store') }}">
    @csrf
    <input type="hidden" name="class_id" value="{{ $classId }}">
    <input type="hidden" name="att_date" value="{{ $date }}">
    <input type="hidden" name="back" value="{{ request()->getRequestUri() }}">
    
    <div class="glass panel" style="display:grid;gap:10px">
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding-bottom:12px;border-bottom:1px solid var(--line-light)">
        <div style="font-size:13.5px;font-weight:600;color:var(--text)">
          Daftar Siswa ({{ $students->count() }} orang)
        </div>
        <div style="display:flex;gap:8px;font-size:12px" id="attCounterStrip">
          <span class="badge badge-ok">Hadir: <b id="countHadir">0</b></span>
          <span class="badge badge-blue">Izin: <b id="countIzin">0</b></span>
          <span class="badge badge-warn">Sakit: <b id="countSakit">0</b></span>
          <span class="badge badge-bad">Alpa: <b id="countAlpa">0</b></span>
        </div>
      </div>

      @forelse ($students as $student)
        @php $att = $existing->get($student->id); @endphp
        <div class="att-row">
          <div class="att-name">
            <b>{{ $student->full_name }}</b>
            <small>NIS: {{ $student->nis ?? '—' }} · NISN: {{ $student->nisn ?? '—' }}</small>
          </div>
          <div class="seg" role="radiogroup" aria-label="Status {{ $student->full_name }}">
            @foreach (['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'] as $st => $label)
              <label>
                <input type="radio" class="att-radio" name="statuses[{{ $student->id }}]" value="{{ $st }}"
                  {{ ($att?->status ?? 'hadir') === $st ? 'checked' : '' }}>
                <span>{{ $label }}</span>
              </label>
            @endforeach
          </div>
        </div>
      @empty
        <div class="empty">
          <p>Belum ada siswa di kelas ini atau belum ada kelas yang dipilih.</p>
          @if (in_array(auth()->user()?->role?->name, ['admin', 'staff_tu']))
            <p style="margin-top:10px"><a href="{{ route('students.index') }}" class="btn btn-sm">Tambahkan Siswa ke Kelas →</a></p>
          @endif
        </div>
      @endforelse
    </div>

    @if ($students->isNotEmpty())
      <div style="margin-top:16px;display:flex;gap:12px;align-items:center;flex-wrap:wrap">
        <button class="btn btn-ink" data-loading="Menyimpan Presensi...">
          💾 Simpan Data Presensi
        </button>
        <span class="cs-pill" style="font-size:12px">
          📅 {{ \Illuminate\Support\Carbon::parse($date)->translatedFormat('l, d F Y') }}
        </span>
        <span style="font-size:13px;color:var(--muted)">Perubahan langsung tersimpan ke rekap kehadiran.</span>
      </div>
    @endif
  </form>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      function updateCounters() {
        var radios = document.querySelectorAll('.att-radio:checked');
        var counts = { hadir: 0, izin: 0, sakit: 0, alpa: 0 };
        radios.forEach(function(r) {
          if (counts[r.value] !== undefined) counts[r.value]++;
        });
        var h = document.getElementById('countHadir');
        var i = document.getElementById('countIzin');
        var s = document.getElementById('countSakit');
        var a = document.getElementById('countAlpa');
        if (h) h.textContent = counts.hadir;
        if (i) i.textContent = counts.izin;
        if (s) s.textContent = counts.sakit;
        if (a) a.textContent = counts.alpa;
      }
      document.querySelectorAll('.att-radio').forEach(function(r) {
        r.addEventListener('change', updateCounters);
      });
      updateCounters();
    });
  </script>

@else
  {{-- ==================== MODE REKAP BULANAN ==================== --}}
  <div class="glass table-wrap" style="overflow-x:auto">
    <div style="padding:14px 18px;border-bottom:1px solid var(--line-light);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
      <div>
        <h3 style="font-size:16px;color:var(--text);margin:0">
          Matriks Rekapitulasi Kehadiran — {{ \Illuminate\Support\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
        </h3>
        <div style="font-size:12.5px;color:var(--muted)">
          Kelas {{ $classes->firstWhere('id', $classId)?->name ?? '—' }} · Total {{ $students->count() }} siswa
        </div>
      </div>
      <div style="display:flex;gap:6px;font-size:11px">
        <span style="display:inline-flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:2px;background:#10b981;display:inline-block"></span> H: Hadir</span>
        <span style="display:inline-flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:2px;background:#38bdf8;display:inline-block"></span> I: Izin</span>
        <span style="display:inline-flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:2px;background:#f59e0b;display:inline-block"></span> S: Sakit</span>
        <span style="display:inline-flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:2px;background:#ef4444;display:inline-block"></span> A: Alpa</span>
      </div>
    </div>

    <table class="tbl" style="font-size:12px;white-space:nowrap;margin:0">
      <thead>
        <tr>
          <th style="width:30px">No</th>
          <th>Nama Siswa</th>
          <th>NIS</th>
          @for ($d = 1; $d <= $daysInMonth; $d++)
            <th style="padding:6px 4px;text-align:center;width:24px">{{ $d }}</th>
          @endfor
          <th style="text-align:center;color:#10b981">H</th>
          <th style="text-align:center;color:#38bdf8">I</th>
          <th style="text-align:center;color:#f59e0b">S</th>
          <th style="text-align:center;color:#ef4444">A</th>
          <th style="text-align:center">%</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($students as $idx => $student)
          @php
            $m = $rekapMatrix[$student->id] ?? [];
            $sum = $rekapSummary[$student->id] ?? ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0, 'percentage' => 0];
          @endphp
          <tr>
            <td>{{ $idx + 1 }}</td>
            <td><b style="color:var(--text)">{{ $student->full_name }}</b></td>
            <td>{{ $student->nis ?? '—' }}</td>
            @for ($d = 1; $d <= $daysInMonth; $d++)
              @php
                $st = $m[$d] ?? null;
                $stColor = match($st) {
                  'hadir' => '#10b981',
                  'izin'  => '#38bdf8',
                  'sakit' => '#f59e0b',
                  'alpa'  => '#ef4444',
                  default => 'transparent'
                };
                $stChar = match($st) {
                  'hadir' => 'H',
                  'izin'  => 'I',
                  'sakit' => 'S',
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
            <td style="text-align:center;font-weight:700;color:#38bdf8">{{ $sum['izin'] }}</td>
            <td style="text-align:center;font-weight:700;color:#f59e0b">{{ $sum['sakit'] }}</td>
            <td style="text-align:center;font-weight:700;color:#ef4444">{{ $sum['alpa'] }}</td>
            <td style="text-align:center">
              <span class="badge {{ $sum['percentage'] >= 85 ? 'badge-ok' : ($sum['percentage'] >= 70 ? 'badge-warn' : 'badge-bad') }}" style="font-size:10.5px">
                {{ $sum['percentage'] }}%
              </span>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="{{ $daysInMonth + 8 }}" class="empty">Tidak ada data siswa di kelas ini.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endif
@endsection
