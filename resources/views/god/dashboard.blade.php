@extends('layouts.god')
@section('title', 'NOC aaPanel Telemetri Platform')

@section('content')
{{-- 1. HEADER KONTROL SUPREME & OPERATIONAL STATUS --}}
<div class="page-head" style="margin-bottom:12px">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;flex-wrap:wrap">
      <span class="vtx-pill" style="background:#fef3c7;color:#92400e;border-color:#fcd34d;font-weight:800">⚡ GOD MODE · SUPREME CONTROL</span>
      <span style="display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:#15803d">
        <span class="live-pulse-dot"></span> Seluruh Node &amp; Layanan Multi-Tenant Beroperasi Normal
      </span>
    </div>
    <h1>Pusat Kendali aaPanel &amp; Kontrol Global Platform</h1>
    <div class="sub">Pemantauan real-time CPU, RAM, Disk, Throughput Trafik, Lingkungan Server, dan Akses Penuh Seluruh Tenant Sekolah.</div>
  </div>
</div>

{{-- 2. AAPANEL SERVER RUNTIME & QUICK ACTION COMMAND STRIP --}}
<div class="aapanel-server-strip" style="margin-bottom:18px">
  <div class="aapanel-spec-pills">
    <div class="aapanel-spec-chip" title="IP Node / Host">
      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></svg>
      <span class="chip-label">Host:</span>
      <span>{{ $telemetry['runtime']['ip_address'] }}</span>
    </div>
    <div class="aapanel-spec-chip" title="Sistem Operasi Kernel">
      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
      <span class="chip-label">OS:</span>
      <span>{{ $telemetry['runtime']['os'] }}</span>
    </div>
    <div class="aapanel-spec-chip" title="Runtime PHP Engine">
      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
      <span class="chip-label">PHP:</span>
      <span>{{ $telemetry['runtime']['php_version'] }}</span>
    </div>
    <div class="aapanel-spec-chip" title="Framework Multi-Tenant Core">
      <span class="chip-label">Core:</span>
      <span>Laravel {{ $telemetry['runtime']['laravel_version'] }}</span>
    </div>
    <div class="aapanel-spec-chip" title="Database Engine & Ukuran">
      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
      <span class="chip-label">DB:</span>
      <span>{{ $telemetry['runtime']['db_driver'] }} ({{ $telemetry['runtime']['db_size'] }})</span>
    </div>
    <div class="aapanel-spec-chip" title="Waktu Aktif Server (Uptime)">
      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      <span class="chip-label">Uptime:</span>
      <span id="runtimeUptime">{{ $telemetry['runtime']['uptime'] }}</span>
    </div>
    <div class="aapanel-spec-chip" style="background:#f8fafc;border-color:#cbd5e1" title="Jam Server WIB">
      <span class="chip-label" style="color:#475569">🕒</span>
      <span id="liveClock">{{ $telemetry['runtime']['server_time'] }}</span>
    </div>
  </div>

  <div class="aapanel-actions-strip">
    <button type="button" class="btn btn-sm" id="btnRefreshTelemetry" title="Refresh data metrik telemetri real-time">
      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" id="refreshIcon"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
      Sync Telemetri
    </button>
    <form method="POST" action="{{ route('god.server.clear-cache') }}" style="margin:0">
      @csrf
      <button class="btn btn-sm btn-ink" title="Bersihkan cache framework, view, route, dan config">
        ⚡ Bersihkan Cache
      </button>
    </form>
    <form method="POST" action="{{ route('god.server.rebuild-cache') }}" style="margin:0">
      @csrf
      <button class="btn btn-sm" title="Rebuild cache produksi untuk performa maksimal">
        📦 Cache Produksi
      </button>
    </form>
    <button class="btn btn-sm btn-god" data-dialog="#dlg-create-school">
      🏫 Daftarkan Tenant Baru
    </button>
  </div>
</div>

{{-- 3. AAPANEL CIRCULAR TELEMETRY GAUGES (REAL-TIME HARDWARE RINGS) --}}
<div class="aapanel-gauge-grid" style="margin-bottom:20px">

  {{-- GAUGE 1: CPU LOAD --}}
  <div class="aapanel-gauge-card glass">
    <div class="gauge-ring-wrap">
      <svg viewBox="0 0 80 80" class="gauge-ring">
        <circle class="ring-bg" cx="40" cy="40" r="32"/>
        <circle class="ring-fill ring-cyan" id="gaugeCpuRing" cx="40" cy="40" r="32"
          stroke-dasharray="201"
          style="stroke-dashoffset: calc(201 - (201 * {{ min(100, $telemetry['server']['cpu']['percent']) }}) / 100);"/>
      </svg>
      <div class="gauge-val-inner">
        <span id="gaugeCpuVal">{{ $telemetry['server']['cpu']['percent'] }}%</span>
        <small>LOAD</small>
      </div>
    </div>
    <div class="aapanel-gauge-details">
      <h4>
        <span>CPU Processor</span>
        <span class="badge badge-blue" id="gaugeCpuStatus">{{ $telemetry['server']['cpu']['status'] }}</span>
      </h4>
      <div class="gauge-stat-big"><span id="cpuPercentBig">{{ $telemetry['server']['cpu']['percent'] }}</span>%</div>
      <div class="gauge-stat-sub">{{ $telemetry['server']['cpu']['cores'] }} CPU Core Berjalan</div>
      <div class="gauge-stat-extra">Load: 1m: <b id="load1m">{{ $telemetry['server']['cpu']['load_1m'] }}</b> · 5m: <b id="load5m">{{ $telemetry['server']['cpu']['load_5m'] }}</b></div>
    </div>
  </div>

  {{-- GAUGE 2: RAM MEMORY --}}
  <div class="aapanel-gauge-card glass">
    <div class="gauge-ring-wrap">
      <svg viewBox="0 0 80 80" class="gauge-ring">
        <circle class="ring-bg" cx="40" cy="40" r="32"/>
        <circle class="ring-fill ring-emerald" id="gaugeMemRing" cx="40" cy="40" r="32"
          stroke-dasharray="201"
          style="stroke-dashoffset: calc(201 - (201 * {{ min(100, $telemetry['server']['memory']['percent']) }}) / 100);"/>
      </svg>
      <div class="gauge-val-inner">
        <span id="gaugeMemVal">{{ $telemetry['server']['memory']['percent'] }}%</span>
        <small>RAM</small>
      </div>
    </div>
    <div class="aapanel-gauge-details">
      <h4>
        <span>Memori RAM</span>
        <span class="badge badge-ok" id="gaugeMemStatus">{{ $telemetry['server']['memory']['status'] }}</span>
      </h4>
      <div class="gauge-stat-big"><span id="memUsedBig">{{ $telemetry['server']['memory']['used'] }}</span></div>
      <div class="gauge-stat-sub">dari total <span id="memTotalText">{{ $telemetry['server']['memory']['total'] }}</span></div>
      <div class="gauge-stat-extra">Tersisa: <b id="memFreeText">{{ $telemetry['server']['memory']['free'] }}</b> · Peak: {{ $telemetry['server']['memory']['peak'] }}</div>
    </div>
  </div>

  {{-- GAUGE 3: DISK STORAGE --}}
  <div class="aapanel-gauge-card glass">
    <div class="gauge-ring-wrap">
      <svg viewBox="0 0 80 80" class="gauge-ring">
        <circle class="ring-bg" cx="40" cy="40" r="32"/>
        <circle class="ring-fill ring-gold" id="gaugeDiskRing" cx="40" cy="40" r="32"
          stroke-dasharray="201"
          style="stroke-dashoffset: calc(201 - (201 * {{ min(100, $telemetry['server']['disk']['percent']) }}) / 100);"/>
      </svg>
      <div class="gauge-val-inner">
        <span id="gaugeDiskVal">{{ $telemetry['server']['disk']['percent'] }}%</span>
        <small>DISK</small>
      </div>
    </div>
    <div class="aapanel-gauge-details">
      <h4>
        <span>Storage Disk (/)</span>
        <span class="badge badge-ok" id="gaugeDiskStatus">{{ $telemetry['server']['disk']['status'] }}</span>
      </h4>
      <div class="gauge-stat-big"><span id="diskUsedBig">{{ $telemetry['server']['disk']['used'] }}</span></div>
      <div class="gauge-stat-sub">dari total <span id="diskTotalText">{{ $telemetry['server']['disk']['total'] }}</span></div>
      <div class="gauge-stat-extra">Bebas: <b id="diskFreeText">{{ $telemetry['server']['disk']['free'] }}</b> (Tersedia)</div>
    </div>
  </div>

  {{-- GAUGE 4: NETWORK & APP TRAFFIC --}}
  <div class="aapanel-gauge-card glass">
    <div class="gauge-ring-wrap">
      <svg viewBox="0 0 80 80" class="gauge-ring">
        <circle class="ring-bg" cx="40" cy="40" r="32"/>
        <circle class="ring-fill ring-purple" id="gaugeTrafficRing" cx="40" cy="40" r="32"
          stroke-dasharray="201"
          style="stroke-dashoffset: calc(201 - (201 * 38) / 100);"/>
      </svg>
      <div class="gauge-val-inner">
        <span id="gaugeTrafficVal">{{ $telemetry['server']['traffic']['outbound_kbs'] }}</span>
        <small>KB/s</small>
      </div>
    </div>
    <div class="aapanel-gauge-details">
      <h4>
        <span>Trafik I/O Jaringan</span>
        <span class="badge badge-blue">Real-Time</span>
      </h4>
      <div class="gauge-stat-big"><span id="trafficOutBig">{{ $telemetry['server']['traffic']['outbound_kbs'] }}</span> KB/s</div>
      <div class="gauge-stat-sub">In: <b id="trafficInText">{{ $telemetry['server']['traffic']['inbound_kbs'] }} KB/s</b> · Out: <b id="trafficOutText">{{ $telemetry['server']['traffic']['outbound_kbs'] }} KB/s</b></div>
      <div class="gauge-stat-extra">Sesi Online: <b id="activeUsersText">{{ $telemetry['server']['traffic']['active_users'] }}</b> · Hits: <b id="totalHitsText">{{ number_format($telemetry['server']['traffic']['total_hits'], 0, ',', '.') }}</b></div>
    </div>
  </div>

</div>

{{-- 4. PLATFORM OPERATIONAL IMPACT METRICS (4 HIGH-CONTRAST SOLID CARDS) --}}
<div class="kpi-grid-4" style="margin-bottom:20px">

  <div class="kpi-card glass">
    <div class="label">
      <span>Multi-Tenant Sekolah</span>
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-6 9 6"/><path d="M5 9v9a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V9"/></svg>
    </div>
    <div class="val">{{ $telemetry['platform']['total_schools'] }} <small style="font-size:14px;font-weight:600;color:var(--muted)">Sekolah</small></div>
    <div class="note">
      <span class="badge badge-ok" style="font-size:11px">{{ $telemetry['platform']['active_schools'] }} Aktif</span>
      <span class="badge {{ $telemetry['platform']['inactive_schools'] > 0 ? 'badge-bad' : 'badge-warn' }}" style="font-size:11px">{{ $telemetry['platform']['inactive_schools'] }} Nonaktif</span>
      <small style="color:var(--muted);margin-left:auto">{{ $telemetry['tier_dist'][2]['count'] ?? 0 }} Atas · {{ $telemetry['tier_dist'][1]['count'] ?? 0 }} Menengah</small>
    </div>
  </div>

  <div class="kpi-card glass kpi-success">
    <div class="label">
      <span>Total Siswa &amp; GTK</span>
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    </div>
    <div class="val">{{ number_format($telemetry['platform']['total_students'] + $telemetry['platform']['total_employees'], 0, ',', '.') }} <small style="font-size:14px;font-weight:600;color:var(--muted)">Civitas</small></div>
    <div class="note">
      <span><b>{{ number_format($telemetry['platform']['total_students'], 0, ',', '.') }}</b> Siswa</span>
      <span>·</span>
      <span><b>{{ number_format($telemetry['platform']['total_employees'], 0, ',', '.') }}</b> GTK</span>
      <small style="color:var(--muted);margin-left:auto">{{ $telemetry['platform']['total_users'] }} Akun</small>
    </div>
  </div>

  <div class="kpi-card glass kpi-purple">
    <div class="label">
      <span>Volume Persuratan &amp; Presensi</span>
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
    </div>
    <div class="val">{{ number_format($telemetry['platform']['total_letters'], 0, ',', '.') }} <small style="font-size:14px;font-weight:600;color:var(--muted)">Surat &amp; SK</small></div>
    <div class="note">
      <span class="badge badge-blue" style="font-size:11px">{{ number_format($telemetry['platform']['presensi_today'], 0, ',', '.') }} Presensi Hari Ini</span>
      <small style="color:var(--muted);margin-left:auto">Buku Agenda Terpadu</small>
    </div>
  </div>

  <div class="kpi-card glass kpi-warn">
    <div class="label">
      <span>Realisasi Kas Platform</span>
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
    </div>
    <div class="val" style="color:#0284c7">Rp {{ number_format($telemetry['platform']['collected_amount'], 0, ',', '.') }}</div>
    <div class="note">
      <span style="color:#dc2626;font-weight:600">Tunggakan: Rp {{ number_format($telemetry['platform']['outstanding_amount'], 0, ',', '.') }}</span>
      <span class="badge badge-ok" style="margin-left:auto">{{ $telemetry['platform']['collection_rate'] }}% Tertagih</span>
    </div>
  </div>

</div>

{{-- 5. AAPANEL DUAL TELEMETRY CHARTS (TRAFFIC LOAD & USER ROLES) --}}
<div class="aapanel-charts-grid" style="margin-bottom:20px">

  {{-- CHART 1: BEBAN TRAFIK 7 HARI --}}
  <div class="glass panel chart-card-wide">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:8px">
      <h2 class="panel-title" style="margin:0">
        <svg viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        Beban Trafik &amp; Throughput Platform (7 Hari Terakhir)
      </h2>
      <span class="vtx-pill" style="font-size:11px">Total Transaksi &amp; Request HTTP</span>
    </div>

    <div class="aapanel-chart-box">
      <div class="chart-bars-wrap">
        @php
          $maxReq = max(1, collect($telemetry['traffic_7d'])->max('requests'));
        @endphp
        @foreach ($telemetry['traffic_7d'] as $tDay)
          @php
            $heightPct = max(8, round(($tDay['requests'] / $maxReq) * 100));
          @endphp
          <div class="chart-bar-col">
            <div class="chart-col-tooltip">
              <strong>{{ $tDay['requests'] }} Requests</strong><br>
              <small>{{ $tDay['mb_io'] }} MB I/O</small>
            </div>
            <div class="chart-col-body">
              <div class="bar-slice bar-hadir" style="height:{{ $heightPct }}%"></div>
            </div>
            <div class="chart-col-label">{{ $tDay['date'] }}</div>
          </div>
        @endforeach
      </div>
      <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;font-size:12px;color:var(--muted)">
        <span>📊 Puncak Trafik: <b>{{ $maxReq }} hits/hari</b></span>
        <span>⚡ Latensi Rata-rata: <b>28ms</b></span>
      </div>
    </div>
  </div>

  {{-- CHART 2: MATRIKS PERAN PENGGUNA PLATFORM --}}
  <div class="glass panel">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
      <h2 class="panel-title" style="margin:0">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="7.5" r="3.5"/><path d="M5 20.5c.7-4.5 3.2-7 7-7s6.3 2.5 7 7"/></svg>
        Komposisi Pengguna (Peran)
      </h2>
      <span class="vtx-pill" style="font-size:11px">{{ $telemetry['platform']['total_users'] }} User</span>
    </div>

    <div style="display:grid;gap:12px;margin-top:6px">
      @forelse ($telemetry['role_dist'] as $roleItem)
        <div>
          <div style="display:flex;align-items:center;justify-content:space-between;font-size:12.5px;font-weight:600;margin-bottom:4px">
            <span style="color:#1e293b">{{ $roleItem['label'] }}</span>
            <span style="color:#0284c7"><b>{{ $roleItem['count'] }}</b> user ({{ $roleItem['percent'] }}%)</span>
          </div>
          <div class="progress-track" style="height:7px;border-radius:4px;background:#e2e8f0;overflow:hidden">
            <div style="height:100%;width:{{ $roleItem['percent'] }}%;background:linear-gradient(90deg, #0284c7, #38bdf8);border-radius:4px"></div>
          </div>
        </div>
      @empty
        <div style="padding:16px;text-align:center;color:var(--muted)">Belum ada data peran pengguna.</div>
      @endforelse
    </div>
  </div>

</div>

{{-- 6. MULTI-TENANT COMMAND CENTER TABLE (SEARCH & DEEP INSPECT) --}}
<div class="glass panel" style="margin-bottom:20px">
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px">
    <div>
      <h2 class="panel-title" style="margin:0">
        <svg viewBox="0 0 24 24"><path d="M3 9l9-6 9 6"/><path d="M5 9v9a2 2 0 002 2h10a2 2 0 002-2V9"/></svg>
        Pusat Kendali Seluruh Sekolah (Multi-Tenant Hub)
      </h2>
      <small style="color:var(--muted)">Super Admin memiliki hak akses mutlak (God Mode) untuk menginspeksi dan bertindak atas nama sekolah manapun.</small>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
      <input type="text" id="tenantSearchInput" class="input" placeholder="Cari nama atau subdomain sekolah..." style="width:240px;height:34px;font-size:12.5px">
      <span class="vtx-pill">{{ $schools->count() }} Tenant Terdaftar</span>
    </div>
  </div>

  <div class="table-wrap">
    <table class="tbl" id="tenantTable">
      <thead>
        <tr>
          <th>Identitas Sekolah</th>
          <th>Paket Tier</th>
          <th>Statistik Pengguna &amp; Siswa</th>
          <th>Status Operasional</th>
          <th style="text-align:right">Aksi Penuh (God Mode &amp; Kontrol)</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($schools as $school)
          <tr>
            <td>
              <b style="font-size:14.5px;color:var(--text);display:block">{{ $school->name }}</b>
              <small style="color:var(--accent);font-weight:600">{{ '@' . $school->subdomain }}</small>
              @if ($school->city || $school->province)
                <small style="color:var(--muted);display:block">{{ $school->city ?? '' }}{{ $school->city && $school->province ? ', ' : '' }}{{ $school->province ?? '' }}</small>
              @endif
            </td>
            <td>
              <span class="badge {{ $school->package_tier === 'atas' ? 'badge-ink' : ($school->package_tier === 'menengah' ? 'badge-blue' : 'badge-warn') }}" style="text-transform:capitalize">
                {{ $school->package_tier }}
              </span>
            </td>
            <td>
              <div style="font-size:13px;display:grid;gap:2px">
                <span><b>{{ $school->users_count }}</b> Akun Pengguna</span>
                <small style="color:var(--muted)">{{ $school->students_count }} Siswa · {{ $school->employees_count }} GTK · {{ $school->academic_years_count }} TA</small>
              </div>
            </td>
            <td>
              <span class="badge {{ $school->is_active ? 'badge-ok' : 'badge-bad' }}">
                {{ $school->is_active ? '🟢 Aktif' : '🔴 Nonaktif' }}
              </span>
            </td>
            <td>
              <div class="actions" style="display:flex;flex-wrap:wrap;gap:6px;justify-content:flex-end">
                <form method="POST" action="{{ route('god.impersonate', $school) }}">
                  @csrf
                  <button class="btn btn-sm btn-god" title="Masuk langsung sebagai Administrator sekolah ini (Bypass)">⚡ GOD MODE</button>
                </form>
                <button class="btn btn-sm" data-dialog="#users-{{ $school->id }}" title="Lihat semua pengguna & masuk sebagai user spesifik">
                  👥 Pengguna ({{ $school->users_count }})
                </button>
                <button class="btn btn-sm" data-dialog="#edit-{{ $school->id }}" title="Edit data sekolah">
                  ✏️ Edit
                </button>
                <form method="POST" action="{{ route('god.schools.toggle', $school) }}">
                  @csrf
                  <button class="btn btn-sm">{{ $school->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                </form>
                <button class="btn btn-sm" data-dialog="#reset-{{ $school->id }}" title="Reset kata sandi administrator">Reset Pass</button>
                <form method="POST" action="{{ route('god.schools.destroy', $school) }}" data-confirm="HAPUS PERMANEN sekolah '{{ $school->name }}' beserta SELURUH data siswa, rombel, presensi, surat, dan keuangannya?">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="empty">Belum ada sekolah yang terdaftar di platform.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- 7. RECENT MULTI-TENANT AUDIT LOG & SECURITY BEST PRACTICES (TWO COLUMNS) --}}
<div class="two-col" style="margin-bottom:20px">

  {{-- STREAM AKTIVITAS TERBARU DARI SELURUH SEKOLAH --}}
  <div class="glass panel">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
      <h2 class="panel-title" style="margin:0">
        <svg viewBox="0 0 24 24"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg>
        Aktivitas Lintas Tenant (Audit Stream)
      </h2>
      <span class="vtx-pill" style="font-size:11px">Live Updates</span>
    </div>

    <div class="activity-feed-list">
      @forelse ($telemetry['recent_logs'] as $log)
        <div class="activity-feed-item">
          <div style="display:flex;align-items:center;gap:12px;min-width:0">
            <span class="badge {{ $log['badge_color'] === 'emerald' ? 'badge-ok' : ($log['badge_color'] === 'amber' ? 'badge-warn' : 'badge-blue') }}" style="flex:none;font-size:10.5px">
              {{ $log['badge'] }}
            </span>
            <div style="min-width:0;line-height:1.3">
              <strong style="font-size:13px;color:var(--text);display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $log['title'] }}</strong>
              <small style="color:var(--muted);font-size:11.5px">{{ $log['desc'] }}</small>
            </div>
          </div>
          <span style="font-size:11px;color:#64748b;flex:none">{{ $log['time'] }}</span>
        </div>
      @empty
        <div style="padding:20px;text-align:center;color:var(--muted)">Belum ada log transaksi terbaru.</div>
      @endforelse
    </div>
  </div>

  {{-- PANDUAN KEAMANAN & ISOLASI GOD MODE --}}
  <div class="glass panel">
    <h2 class="panel-title" style="margin-bottom:14px">
      <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      Protokol Integritas &amp; Keamanan God Mode
    </h2>
    <div class="stack" style="gap:12px">
      <div class="glass-soft" style="padding:14px;border-radius:12px;border:1px solid #fef3c7;background:#fffbeb">
        <h4 style="margin:0 0 4px;font-size:13.5px;color:#b45309">⚡ True God Mode &amp; Persistent Top Bar</h4>
        <p style="margin:0;font-size:12px;color:#78350f;line-height:1.4">
          Saat masuk ke sekolah manapun, bilah navigasi persisten di bagian paling atas akan aktif. Anda dapat berpindah sekolah secara instan melalui Quick Switcher atau kembali ke panel ini dalam 1 klik.
        </p>
      </div>

      <div class="glass-soft" style="padding:14px;border-radius:12px;border:1px solid #e0f2fe;background:#f0f9ff">
        <h4 style="margin:0 0 4px;font-size:13.5px;color:#0369a1">🛡️ Multi-Tenant Scoping (BelongsToSchool)</h4>
        <p style="margin:0;font-size:12px;color:#0c4a6e;line-height:1.4">
          Setiap tenant terisolasi penuh secara global. Data surat, siswa, GTK, rombel, dan keuangan tidak akan pernah bercampur antar sekolah berkat penegakan Global Scope trait <code>BelongsToSchool</code>.
        </p>
      </div>

      <div class="glass-soft" style="padding:14px;border-radius:12px;border:1px solid #f1f5f9;background:#f8fafc">
        <h4 style="margin:0 0 4px;font-size:13.5px;color:#334155">⚡ aaPanel Server Actions</h4>
        <p style="margin:0;font-size:12px;color:#475569;line-height:1.4">
          Tombol "Bersihkan Cache" dan "Cache Produksi" di atas menjalankan optimasi engine Laravel (route, view, config cache) secara instan tanpa perlu akses SSH terminal.
        </p>
      </div>
    </div>
  </div>

</div>

{{-- MODAL DAFTARKAN SEKOLAH BARU --}}
<dialog class="dlg" id="dlg-create-school" style="max-width:540px;width:95%">
  <h3>Daftarkan Tenant Sekolah Baru</h3>
  <p style="font-size:12.5px;color:var(--muted);margin-top:2px">Sistem akan secara otomatis membuatkan database tenant dan akun Administrator.</p>
  <form method="POST" action="{{ route('god.schools.store') }}" class="stack" style="margin-top:12px">
    @csrf
    <div class="field">
      <label>Nama Sekolah / Institusi *</label>
      <input name="name" class="input" placeholder="mis. SMA Negeri 1 Surabaya" required>
    </div>
    <div class="form-grid" style="grid-template-columns:1fr 1fr">
      <div class="field">
        <label>Subdomain Unik *</label>
        <input name="subdomain" class="input" placeholder="sman1sby" required>
      </div>
      <div class="field">
        <label>Paket Langganan *</label>
        <select name="package_tier" class="select">
          <option value="dasar">Dasar</option>
          <option value="menengah" selected>Menengah</option>
          <option value="atas">Atas (Enterprise)</option>
        </select>
      </div>
    </div>
    <div class="form-grid" style="grid-template-columns:1fr 1fr">
      <div class="field">
        <label>Username Admin *</label>
        <input name="admin_username" class="input" placeholder="admin" required>
      </div>
      <div class="field">
        <label>Password Admin *</label>
        <input name="admin_password" type="password" class="input" placeholder="min. 6 karakter" minlength="6" required>
      </div>
    </div>
    <div class="dlg-actions" style="margin-top:16px">
      <button type="button" class="btn" data-close>Batal</button>
      <button class="btn btn-god" data-loading="Mendaftarkan...">🏫 Buat Sekolah &amp; Inisialisasi</button>
    </div>
  </form>
</dialog>

{{-- MODAL DIALOGS PER SEKOLAH --}}
@foreach ($schools as $school)
{{-- Dialog Reset Password --}}
<dialog class="dlg" id="reset-{{ $school->id }}">
  <h3>Reset Password Admin — {{ $school->name }}</h3>
  <form method="POST" action="{{ route('god.schools.reset', $school) }}" class="stack">
    @csrf
    <div class="field">
      <label>Kata Sandi Baru *</label>
      <input name="password" type="password" class="input" minlength="6" placeholder="min. 6 karakter" required>
    </div>
    <div class="dlg-actions">
      <button type="button" class="btn" data-close>Batal</button>
      <button class="btn btn-god" data-loading="Mereset...">Reset Kata Sandi</button>
    </div>
  </form>
</dialog>

{{-- Dialog Edit Data Sekolah --}}
<dialog class="dlg" id="edit-{{ $school->id }}" style="max-width:560px;width:95%">
  <h3>Edit Sekolah — {{ $school->name }}</h3>
  <form method="POST" action="{{ route('god.schools.update', $school) }}" class="stack">
    @csrf
    @method('PUT')
    <div class="field">
      <label>Nama Sekolah / Institusi *</label>
      <input name="name" class="input" value="{{ $school->name }}" required>
    </div>
    <div class="form-grid" style="grid-template-columns:1fr 1fr">
      <div class="field">
        <label>Subdomain Unik *</label>
        <input name="subdomain" class="input" value="{{ $school->subdomain }}" required>
      </div>
      <div class="field">
        <label>Paket Langganan *</label>
        <select name="package_tier" class="select">
          <option value="dasar" {{ $school->package_tier === 'dasar' ? 'selected' : '' }}>Dasar</option>
          <option value="menengah" {{ $school->package_tier === 'menengah' ? 'selected' : '' }}>Menengah</option>
          <option value="atas" {{ $school->package_tier === 'atas' ? 'selected' : '' }}>Atas (Enterprise)</option>
        </select>
      </div>
    </div>
    <div class="field">
      <label style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;font-weight:600">
        <input type="checkbox" name="is_active" value="1" {{ $school->is_active ? 'checked' : '' }}>
        Status Sekolah Aktif (Berlangganan)
      </label>
    </div>
    <div class="dlg-actions">
      <button type="button" class="btn" data-close>Batal</button>
      <button class="btn btn-god" data-loading="Menyimpan...">Simpan Perubahan</button>
    </div>
  </form>
</dialog>

{{-- Dialog Daftar Pengguna & Impersonasi --}}
<dialog class="dlg" id="users-{{ $school->id }}" style="max-width:700px;width:95%">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
    <div>
      <h3 style="margin:0">Pengguna Sekolah — {{ $school->name }}</h3>
      <small style="color:var(--muted)">Pilih pengguna manapun untuk masuk langsung (God Mode Impersonate)</small>
    </div>
    <button type="button" class="btn btn-sm" data-close>✕</button>
  </div>
  <div class="table-wrap" style="max-height:360px;overflow-y:auto;border:1px solid var(--line);border-radius:12px">
    <table class="tbl">
      <thead>
        <tr>
          <th>Username</th>
          <th>Nama</th>
          <th>Peran (Role)</th>
          <th>Status</th>
          <th style="text-align:right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($school->users as $u)
          <tr>
            <td><b>{{ $u->username }}</b></td>
            <td>{{ $u->name ?? '-' }}</td>
            <td><span class="badge badge-blue">{{ $u->role?->name ?? 'user' }}</span></td>
            <td>
              <span class="badge {{ $u->is_active ? 'badge-ok' : 'badge-bad' }}">
                {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
            <td style="text-align:right">
              <form method="POST" action="{{ route('god.impersonate.user', $u) }}" style="display:inline">
                @csrf
                <button class="btn btn-sm btn-god" title="Masuk langsung sebagai {{ $u->username }}">
                  ⚡ Masuk
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="empty" style="padding:24px;text-align:center">
              Belum ada akun pengguna pada sekolah ini.<br>
              <small style="color:var(--muted)">Gunakan tombol <b>⚡ GOD MODE</b> di luar untuk membuatkan akun admin otomatis.</small>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="dlg-actions" style="margin-top:16px">
    <button type="button" class="btn" data-close>Tutup</button>
  </div>
</dialog>
@endforeach

{{-- 8. ENGINE JAVASCRIPT REAL-TIME AAPANEL TELEMETRI --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
  // 1. Digital Clock WIB
  function updateLiveClock() {
    var clockEl = document.getElementById('liveClock');
    if (!clockEl) return;
    var now = new Date();
    var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
    clockEl.textContent = now.toLocaleDateString('id-ID', options) + ' WIB';
  }
  setInterval(updateLiveClock, 1000);

  // 2. Filter Pencarian Tenant Sekolah
  var searchInput = document.getElementById('tenantSearchInput');
  var tenantTable = document.getElementById('tenantTable');
  if (searchInput && tenantTable) {
    searchInput.addEventListener('input', function () {
      var q = this.value.toLowerCase().trim();
      var rows = tenantTable.querySelectorAll('tbody tr');
      rows.forEach(function (row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.indexOf(q) !== -1 ? '' : 'none';
      });
    });
  }

  // 3. Update Gauge Stroke Offset
  var circumference = 201; // 2 * Math.PI * 32
  function setGauge(ringId, percent) {
    var ring = document.getElementById(ringId);
    if (!ring) return;
    var p = Math.min(100, Math.max(0, parseFloat(percent) || 0));
    var offset = circumference - (circumference * p) / 100;
    ring.style.strokeDashoffset = offset;
  }

  // 4. AJAX Real-Time Polling Telemetri (aaPanel Ticker)
  var refreshBtn = document.getElementById('btnRefreshTelemetry');
  var refreshIcon = document.getElementById('refreshIcon');

  function fetchLiveTelemetry(showSpinner) {
    if (showSpinner && refreshIcon) refreshIcon.classList.add('spinning');

    fetch('{{ route("god.telemetry") }}', {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function (res) { return res.json(); })
    .then(function (json) {
      if (!json || !json.data) return;
      var d = json.data;

      // Update CPU
      if (d.server && d.server.cpu) {
        var cpuP = d.server.cpu.percent;
        setGauge('gaugeCpuRing', cpuP);
        document.getElementById('gaugeCpuVal').textContent = cpuP + '%';
        document.getElementById('cpuPercentBig').textContent = cpuP;
        if (d.server.cpu.load_1m) document.getElementById('load1m').textContent = d.server.cpu.load_1m;
        if (d.server.cpu.load_5m) document.getElementById('load5m').textContent = d.server.cpu.load_5m;
      }

      // Update RAM
      if (d.server && d.server.memory) {
        var memP = d.server.memory.percent;
        setGauge('gaugeMemRing', memP);
        document.getElementById('gaugeMemVal').textContent = memP + '%';
        document.getElementById('memUsedBig').textContent = d.server.memory.used;
        document.getElementById('memFreeText').textContent = d.server.memory.free;
      }

      // Update Disk
      if (d.server && d.server.disk) {
        var diskP = d.server.disk.percent;
        setGauge('gaugeDiskRing', diskP);
        document.getElementById('gaugeDiskVal').textContent = diskP + '%';
        document.getElementById('diskUsedBig').textContent = d.server.disk.used;
        document.getElementById('diskFreeText').textContent = d.server.disk.free;
      }

      // Update Traffic
      if (d.server && d.server.traffic) {
        var traf = d.server.traffic;
        document.getElementById('gaugeTrafficVal').textContent = traf.outbound_kbs;
        document.getElementById('trafficOutBig').textContent = traf.outbound_kbs;
        document.getElementById('trafficOutText').textContent = traf.outbound_kbs + ' KB/s';
        document.getElementById('trafficInText').textContent = traf.inbound_kbs + ' KB/s';
        document.getElementById('activeUsersText').textContent = traf.active_users;
        document.getElementById('totalHitsText').textContent = traf.total_hits.toLocaleString('id-ID');
      }

      // Update Uptime
      if (d.runtime && d.runtime.uptime) {
        var upt = document.getElementById('runtimeUptime');
        if (upt) upt.textContent = d.runtime.uptime;
      }
    })
    .catch(function (err) {
      console.warn('Telemetry polling error:', err);
    })
    .finally(function () {
      if (refreshIcon) refreshIcon.classList.remove('spinning');
    });
  }

  if (refreshBtn) {
    refreshBtn.addEventListener('click', function () {
      fetchLiveTelemetry(true);
    });
  }

  // Interval polling otomatis setiap 10 detik
  setInterval(function () {
    fetchLiveTelemetry(false);
  }, 10000);
});
</script>
@endsection
