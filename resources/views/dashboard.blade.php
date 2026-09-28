@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
{{-- Page Head with aaPanel Status Indicators --}}
<div class="page-head" style="align-items: flex-start;">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;flex-wrap:wrap">
      <span class="cs-pill" style="font-size:11px;padding:3px 12px">
        <span class="dot"></span>
        {{ $school->name }}
      </span>
      <span class="badge badge-ok" style="font-size:11px">
        ● Multi-Tenant Isolation Active
      </span>
      <span style="font-size:12px;color:var(--muted)">
        TA {{ $serverInfo['active_year'] }}
      </span>
    </div>
    <h1>
      Selamat datang di <span class="brand-ruanggtk"><span class="brand-ruanggtk-text" style="font-size:27px">Ruang<span class="gtk-tag">GTK</span></span><span class="brand-beam"></span></span>, {{ auth()->user()->username }} 👋
    </h1>
    <div class="sub">Pusat kendali telemetri operasional sekolah, peserta didik, GTK, jurusan, dan arus kas pendidikan.</div>
  </div>
  <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
    <span class="badge badge-ink" style="padding:5px 16px;font-size:12.5px;letter-spacing:0.04em">
      {{ strtoupper(auth()->user()->role?->name ?? 'User') }}
    </span>
  </div>
</div>

{{-- SECTION KHUSUS WALI MURID --}}
@if ($userRole === 'wali')
<div class="stack" style="margin-top:16px;gap:16px">
  <div class="glass panel" style="border-left:4px solid var(--accent)">
    <h2 class="panel-title">
      <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M3.5 20c.5-4 2.6-6 5.5-6s5 2 5.5 6"/><circle cx="17" cy="9" r="2.5"/><path d="M16 14.5c2.5.2 4 1.8 4.5 4.5"/></svg>
      Portal Wali Murid — Peserta Didik Binaan Anda
    </h2>
    <div class="sub" style="margin-bottom:14px">Informasi kehadiran, tagihan biaya pendidikan, dan aktivitas putra/putri Anda.</div>

    @forelse ($myChildren as $child)
      <div class="glass-soft" style="padding:16px;border-radius:var(--radius-md);margin-bottom:12px">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;border-bottom:1px solid var(--line-light);padding-bottom:10px;margin-bottom:12px">
          <div>
            <b style="font-size:16px;color:var(--text)">{{ $child->full_name }}</b>
            <div style="font-size:12px;color:var(--muted);margin-top:2px">
              Kelas {{ $child->schoolClass?->name ?? '—' }}
              @if ($child->major)
                · Jurusan {{ $child->major->name }} ({{ $child->major->code }})
              @endif
              · NIS: {{ $child->nis ?? '—' }} · NISN: {{ $child->nisn ?? '—' }}
            </div>
          </div>
          <span class="badge badge-ok">Status: {{ ucfirst($child->status) }}</span>
        </div>

        <div class="two-col" style="gap:14px">
          <div>
            <div style="font-size:12.5px;font-weight:600;color:var(--text);margin-bottom:6px">📅 Kehadiran Terakhir:</div>
            <div style="display:flex;gap:6px;flex-wrap:wrap">
              @forelse ($child->attendances as $att)
                @php
                  $color = match($att->status) {
                    'hadir' => '#10b981',
                    'izin' => '#38bdf8',
                    'sakit' => '#f59e0b',
                    default => '#ef4444'
                  };
                @endphp
                <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:4px;background:{{ $color }}20;border:1px solid {{ $color }}40;font-size:11px;color:{{ $color }}">
                  {{ $att->att_date?->format('d/m') }}: <b>{{ ucfirst($att->status) }}</b>
                </span>
              @empty
                <span style="font-size:12px;color:var(--muted)">Belum ada catatan presensi minggu ini.</span>
              @endforelse
            </div>
          </div>

          <div>
            <div style="font-size:12.5px;font-weight:600;color:var(--text);margin-bottom:6px">💳 Tagihan Sekolah:</div>
            <div style="display:grid;gap:6px">
              @forelse ($child->bills as $b)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:var(--radius-sm);font-size:12px">
                  <span>{{ $b->paymentType?->name }} (Rp {{ number_format($b->amount, 0, ',', '.') }})</span>
                  <span class="badge {{ $b->status === 'lunas' ? 'badge-ok' : 'badge-bad' }}">{{ strtoupper($b->status) }}</span>
                </div>
              @empty
                <span style="font-size:12px;color:var(--green)">✓ Tidak ada tunggakan tagihan.</span>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="empty">Belum ada siswa yang ditautkan dengan akun wali Anda.</div>
    @endforelse
  </div>
</div>
@endif

@if ($userRole !== 'wali')
{{-- ════════════════════════════════════════════════════════════════════
     1. AAPANEL CIRCULAR TELEMETRY GAUGES
     ════════════════════════════════════════════════════════════════════ --}}
<div class="aapanel-gauges-grid" style="margin-top:16px">
  {{-- Gauge 1: Presensi Siswa --}}
  <div class="glass aapanel-gauge-card">
    <div class="gauge-ring-wrap">
      <svg viewBox="0 0 100 100" class="gauge-ring">
        <circle class="ring-bg" cx="50" cy="50" r="40"/>
        <circle class="ring-fill ring-cyan" cx="50" cy="50" r="40"
          style="stroke-dasharray: 251.2; stroke-dashoffset: {{ 251.2 - (251.2 * ($telemetry['attendance_rate'] / 100)) }};"/>
      </svg>
      <div class="gauge-val">
        <strong>{{ $telemetry['attendance_rate'] }}%</strong>
      </div>
    </div>
    <div class="gauge-info">
      <h4>Presensi Siswa</h4>
      <p>Rasio kehadiran hari ini</p>
    </div>
  </div>

  {{-- Gauge 2: Realisasi Kas SPP --}}
  <div class="glass aapanel-gauge-card">
    <div class="gauge-ring-wrap">
      <svg viewBox="0 0 100 100" class="gauge-ring">
        <circle class="ring-bg" cx="50" cy="50" r="40"/>
        <circle class="ring-fill ring-gold" cx="50" cy="50" r="40"
          style="stroke-dasharray: 251.2; stroke-dashoffset: {{ 251.2 - (251.2 * ($telemetry['spp_rate'] / 100)) }};"/>
      </svg>
      <div class="gauge-val">
        <strong>{{ $telemetry['spp_rate'] }}%</strong>
      </div>
    </div>
    <div class="gauge-info">
      <h4>Realisasi Kas SPP</h4>
      <p>Pembayaran diterima</p>
    </div>
  </div>

  {{-- Gauge 3: Utilisasi Rombel --}}
  <div class="glass aapanel-gauge-card">
    <div class="gauge-ring-wrap">
      <svg viewBox="0 0 100 100" class="gauge-ring">
        <circle class="ring-bg" cx="50" cy="50" r="40"/>
        <circle class="ring-fill ring-blue" cx="50" cy="50" r="40"
          style="stroke-dasharray: 251.2; stroke-dashoffset: {{ 251.2 - (251.2 * ($telemetry['capacity_rate'] / 100)) }};"/>
      </svg>
      <div class="gauge-val">
        <strong>{{ $telemetry['capacity_rate'] }}%</strong>
      </div>
    </div>
    <div class="gauge-info">
      <h4>Kapasitas Rombel</h4>
      <p>{{ $stats['students'] }} siswa di {{ $stats['classes'] }} kelas</p>
    </div>
  </div>

  {{-- Gauge 4: GTK Aktif --}}
  <div class="glass aapanel-gauge-card">
    <div class="gauge-ring-wrap">
      <svg viewBox="0 0 100 100" class="gauge-ring">
        <circle class="ring-bg" cx="50" cy="50" r="40"/>
        <circle class="ring-fill ring-emerald" cx="50" cy="50" r="40"
          style="stroke-dasharray: 251.2; stroke-dashoffset: {{ 251.2 - (251.2 * ($telemetry['gtk_rate'] / 100)) }};"/>
      </svg>
      <div class="gauge-val">
        <strong>{{ $telemetry['gtk_rate'] }}%</strong>
      </div>
    </div>
    <div class="gauge-info">
      <h4>GTK Bertugas</h4>
      <p>{{ $stats['employees'] }} tenaga kependidikan</p>
    </div>
  </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════
     2. AAPANEL CORE STATS BAR
     ════════════════════════════════════════════════════════════════════ --}}
<div class="stat-grid" style="margin-top:16px">
  <div class="glass stat">
    <div class="count-pill">
      <strong class="count-up" data-count="{{ $stats['students'] }}">0</strong>
      <small>Peserta Didik</small>
    </div>
    <div class="stat-label">Siswa terdaftar aktif di sistem</div>
    <div class="stat-icon">
      <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M3.5 20c.5-4 2.6-6 5.5-6s5 2 5.5 6"/><circle cx="17" cy="9" r="2.5"/><path d="M16 14.5c2.5.2 4 1.8 4.5 4.5"/></svg>
    </div>
  </div>

  <div class="glass stat">
    <div class="count-pill">
      <strong class="count-up" data-count="{{ $stats['classes'] }}">0</strong>
      <small>Rombel Kelas</small>
    </div>
    <div class="stat-label">Rombongan belajar aktif</div>
    <div class="stat-icon">
      <svg viewBox="0 0 24 24"><path d="M3 9l9-6 9 6"/><path d="M5 9v9a2 2 0 002 2h10a2 2 0 002-2V9"/></svg>
    </div>
  </div>

  <div class="glass stat">
    <div class="count-pill">
      <strong class="count-up" data-count="{{ $stats['majors'] }}">0</strong>
      <small>Program Keahlian</small>
    </div>
    <div class="stat-label">Jurusan keahlian terintegrasi</div>
    <div class="stat-icon">
      <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20M4 4.5A2.5 2.5 0 016.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15z"/></svg>
    </div>
  </div>

  <div class="glass stat">
    <div class="count-pill">
      <strong>Rp <span class="count-up" data-count="{{ (int) $stats['paid_amount'] }}">0</span></strong>
      <small>Kas Masuk</small>
    </div>
    <div class="stat-label">Tunggakan: Rp {{ number_format($stats['unpaid_amount'], 0, ',', '.') }}</div>
    <div class="stat-icon">
      <svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18"/></svg>
    </div>
  </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════
     3. AAPANEL INTERACTIVE CHARTS SECTION (Grafik yang Keren)
     ════════════════════════════════════════════════════════════════════ --}}
<div class="aapanel-charts-grid" style="margin-top:20px">
  {{-- Chart 1: Tren Presensi 7 Hari Terakhir (SVG Vector Area Chart) --}}
  <div class="glass panel chart-card-wide">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:8px">
      <div>
        <h2 class="panel-title" style="margin:0">
          <svg viewBox="0 0 24 24"><path d="M3 3v18h18M7 16l4-6 4 4 5-8"/></svg>
          Grafik Tren Presensi Siswa (7 Hari Terakhir)
        </h2>
        <div class="sub" style="margin:2px 0 0">Fluktuasi kehadiran, izin, dan alpa harian sekolah.</div>
      </div>
      <div style="display:flex;gap:12px;font-size:12px;align-items:center">
        <span style="display:inline-flex;align-items:center;gap:5px;color:#38bdf8">
          <span style="width:10px;height:10px;background:#38bdf8;border-radius:2px"></span> Hadir
        </span>
        <span style="display:inline-flex;align-items:center;gap:5px;color:#f59e0b">
          <span style="width:10px;height:10px;background:#f59e0b;border-radius:2px"></span> Izin / Sakit
        </span>
        <span style="display:inline-flex;align-items:center;gap:5px;color:#ef4444">
          <span style="width:10px;height:10px;background:#ef4444;border-radius:2px"></span> Alpa
        </span>
      </div>
    </div>

    {{-- Vector Chart Container --}}
    <div class="aapanel-chart-box">
      @php
        $maxHadir = max(1, collect($chart7Days)->max('hadir'));
      @endphp
      <div class="chart-bars-wrap">
        @foreach ($chart7Days as $idx => $d)
          @php
            $heightPct = round(($d['hadir'] / $maxHadir) * 100);
          @endphp
          <div class="chart-bar-col" title="{{ $d['date'] }}: {{ $d['hadir'] }} Hadir, {{ $d['izin'] }} Izin, {{ $d['alpa'] }} Alpa">
            <div class="chart-col-tooltip">
              <b>{{ $d['date'] }}</b>
              <div style="color:#38bdf8">Hadir: {{ $d['hadir'] }}</div>
              <div style="color:#f59e0b">Izin: {{ $d['izin'] }}</div>
              <div style="color:#ef4444">Alpa: {{ $d['alpa'] }}</div>
            </div>
            <div class="chart-col-body">
              <div class="bar-slice bar-hadir" style="height: {{ $heightPct }}%"></div>
              @if ($d['izin'] > 0)
                <div class="bar-slice bar-izin" style="height: {{ max(4, round(($d['izin'] / $maxHadir) * 100)) }}%"></div>
              @endif
              @if ($d['alpa'] > 0)
                <div class="bar-slice bar-alpa" style="height: {{ max(4, round(($d['alpa'] / $maxHadir) * 100)) }}%"></div>
              @endif
            </div>
            <div class="chart-col-label">{{ $d['date'] }}</div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  {{-- Chart 2: Distribusi Siswa per Jurusan / Peminatan --}}
  <div class="glass panel">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
      <h2 class="panel-title" style="margin:0">
        <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20M4 4.5A2.5 2.5 0 016.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15z"/></svg>
        Komposisi Siswa per Jurusan
      </h2>
      <a href="{{ route('majors.index') }}" style="font-size:12px;color:var(--accent)">Kelola →</a>
    </div>

    <div class="stack" style="gap:12px">
      @forelse ($majorsDistribution as $md)
        <div>
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;font-size:12.5px">
            <span style="font-weight:600;color:var(--text)">
              <span class="badge badge-ink" style="padding:1px 6px;font-size:10px;margin-right:4px">{{ $md['code'] }}</span>
              {{ $md['name'] }}
            </span>
            <span style="color:var(--muted)"><b>{{ $md['count'] }}</b> siswa ({{ $md['percent'] }}%)</span>
          </div>
          <div class="progress-track">
            <div class="progress-bar-fill" style="width: {{ $md['percent'] }}%"></div>
          </div>
        </div>
      @empty
        <div class="empty">Belum ada jurusan keahlian. <a href="{{ route('majors.index') }}" style="color:var(--accent)">+ Tambah Jurusan</a></div>
      @endforelse
    </div>
  </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════
     4. AAPANEL SYSTEM TELEMETRY & TASK BAR
     ════════════════════════════════════════════════════════════════════ --}}
<div class="two-col" style="margin-top:20px">
  {{-- aaPanel System Environment --}}
  <div class="glass panel">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
      <h2 class="panel-title" style="margin:0">
        <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></svg>
        Telemetri Server &amp; Environment
      </h2>
      <span class="badge badge-ok" style="font-size:11px">● System Healthy</span>
    </div>

    <div class="aapanel-info-grid">
      <div class="info-row">
        <span class="lbl">Platform Engine</span>
        <span class="val">PHP {{ $serverInfo['php_version'] }} · Laravel {{ $serverInfo['laravel_version'] }}</span>
      </div>
      <div class="info-row">
        <span class="lbl">Database Engine</span>
        <span class="val">MySQL 8.0 (Driver: {{ $serverInfo['db_driver'] }})</span>
      </div>
      <div class="info-row">
        <span class="lbl">Host OS</span>
        <span class="val">{{ $serverInfo['server_os'] }} (cPanel Cloud Hosting)</span>
      </div>
      <div class="info-row">
        <span class="lbl">Tenant Domain</span>
        <span class="val" style="color:var(--accent)">{{ $school->subdomain }}.ruanggtk.my.id</span>
      </div>
      <div class="info-row">
        <span class="lbl">Tahun Ajaran Aktif</span>
        <span class="val">{{ $serverInfo['active_year'] }}</span>
      </div>
      <div class="info-row">
        <span class="lbl">Waktu Server</span>
        <span class="val" style="font-family:monospace;font-size:12px">{{ $serverInfo['server_time'] }}</span>
      </div>
    </div>
  </div>

  {{-- aaPanel Quick Actions Hub --}}
  <div class="glass panel">
    <h2 class="panel-title">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
      Akses Cepat &amp; Import Data
    </h2>

    <div class="quick-tools-grid">
      <a href="{{ route('students.index') }}" class="quick-tool-card glass-soft">
        <span class="icon-chip is-blue">📥</span>
        <div>
          <b>Import Siswa</b>
          <small>Excel / CSV massal</small>
        </div>
      </a>

      <a href="{{ route('employees.index') }}" class="quick-tool-card glass-soft">
        <span class="icon-chip is-ok">🧑‍🏫</span>
        <div>
          <b>Import GTK</b>
          <small>Guru &amp; Tenaga Usaha</small>
        </div>
      </a>

      <a href="{{ route('classes.index') }}" class="quick-tool-card glass-soft">
        <span class="icon-chip is-ink">🏫</span>
        <div>
          <b>Rombel Kelas</b>
          <small>Struktur kelas &amp; wali</small>
        </div>
      </a>

      <a href="{{ route('majors.index') }}" class="quick-tool-card glass-soft">
        <span class="icon-chip">🎓</span>
        <div>
          <b>Data Jurusan</b>
          <small>Program keahlian</small>
        </div>
      </a>

      <a href="{{ route('attendance.index') }}" class="quick-tool-card glass-soft">
        <span class="icon-chip is-ok">📊</span>
        <div>
          <b>Rekap Presensi</b>
          <small>Matriks 1-31 &amp; Cetak</small>
        </div>
      </a>

      <a href="{{ route('bills.index') }}" class="quick-tool-card glass-soft">
        <span class="icon-chip is-ink">🧾</span>
        <div>
          <b>Kasir &amp; SPP</b>
          <small>Kuitansi resmi cetak</small>
        </div>
      </a>
    </div>
  </div>
</div>
@endif
@endsection
