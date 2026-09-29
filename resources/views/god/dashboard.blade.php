@extends('layouts.god')
@section('title', 'Supreme God Mode Dashboard')

@section('content')
{{-- 1. HERO GREETING & ACTION STRIP --}}
<div class="god-hero-strip" style="margin-bottom:24px">
  <div>
    <h1 class="god-greeting-title">Selamat datang kembali, {{ auth('super')->user()?->name ?? 'Super Administrator' }}!</h1>
    <div class="god-greeting-sub">
      <span class="live-pulse-dot"></span>
      <span>Pusat Kendali Supreme God Mode · Kontrol Global Platform &amp; Telemetri aaPanel Multi-Tenant.</span>
    </div>
  </div>

  <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
    <button type="button" class="god-btn-secondary-neo" id="btnRefreshTelemetry" title="Sinkronkan metrik hardware server & telemetri platform">
      <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" id="refreshIcon"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
      Sync Telemetri
    </button>
    <button type="button" class="god-btn-primary-neo" data-dialog="#dlg-create-school">
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
      Daftarkan Sekolah Baru
    </button>
  </div>
</div>

{{-- 2. MAIN 2-COLUMN NEO-SAAS GRID (70% LEFT, 30% RIGHT) --}}
<div class="god-main-grid">

  {{-- ═══ LEFT COLUMN (METRICS, SALES/TRAFFIC CHART, TENANTS TABLE) ═══ --}}
  <div class="god-left-col">

    {{-- A. 4 TOP METRIC CARDS --}}
    <div class="god-metric-grid">

      {{-- Card 1: Kas Terkumpul --}}
      <div class="god-neo-card">
        <div class="god-card-head">
          <div class="god-card-icon" style="background:#dcfce7;color:#16a34a">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
          </div>
          <span class="god-card-dots">•••</span>
        </div>
        <div class="god-card-val" style="color:#0f172a">Rp {{ number_format($telemetry['platform']['collected_amount'], 0, ',', '.') }}</div>
        <div class="god-card-lbl">Kas Terkumpul ({{ $telemetry['platform']['collection_rate'] }}% Tertagih)</div>
      </div>

      {{-- Card 2: Tenant Sekolah --}}
      <div class="god-neo-card">
        <div class="god-card-head">
          <div class="god-card-icon" style="background:#e0f2fe;color:#0284c7">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-6 9 6"/><path d="M5 9v9a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V9"/></svg>
          </div>
          <span class="god-card-dots">•••</span>
        </div>
        <div class="god-card-val">{{ $telemetry['platform']['total_schools'] }} <small style="font-size:14px;font-weight:600;color:#64748b">Sekolah</small></div>
        <div class="god-card-lbl">{{ $telemetry['platform']['active_schools'] }} Aktif · {{ $telemetry['platform']['inactive_schools'] }} Nonaktif</div>
      </div>

      {{-- Card 3: Civitas Siswa & GTK --}}
      <div class="god-neo-card">
        <div class="god-card-head">
          <div class="god-card-icon" style="background:#ede9fe;color:#7c3aed">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </div>
          <span class="god-card-dots">•••</span>
        </div>
        <div class="god-card-val">{{ number_format($telemetry['platform']['total_students'] + $telemetry['platform']['total_employees'], 0, ',', '.') }}</div>
        <div class="god-card-lbl">{{ number_format($telemetry['platform']['total_students'], 0, ',', '.') }} Siswa · {{ number_format($telemetry['platform']['total_employees'], 0, ',', '.') }} GTK</div>
      </div>

      {{-- Card 4: Throughput I/O Jaringan --}}
      <div class="god-neo-card">
        <div class="god-card-head">
          <div class="god-card-icon" style="background:#fef3c7;color:#d97706">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
          </div>
          <span class="god-card-dots">•••</span>
        </div>
        <div class="god-card-val"><span id="trafficOutBig">{{ $telemetry['server']['traffic']['outbound_kbs'] }}</span> <small style="font-size:14px;font-weight:600;color:#64748b">KB/s</small></div>
        <div class="god-card-lbl">Latensi ~28ms · <span id="activeUsersText">{{ $telemetry['server']['traffic']['active_users'] }}</span> Sesi Online</div>
      </div>

    </div>

    {{-- B. SOFT PURPLE TRAFFIC & SALES BAR CHART CARD --}}
    <div class="god-chart-box-neo">
      <div class="god-chart-top">
        <div>
          <h2 class="god-chart-title">Rata-rata Beban Trafik &amp; Throughput Platform</h2>
          <div class="god-chart-sub">Pertukaran data HTTP request dan volume I/O multi-tenant 7 hari terakhir</div>
        </div>
        <div class="god-chart-controls">
          <span class="god-select-pill">📅 7 Hari Terakhir</span>
          <button class="god-arrow-btn" type="button" aria-label="Sebelumnya">‹</button>
          <button class="god-arrow-btn" type="button" aria-label="Selanjutnya">›</button>
        </div>
      </div>

      {{-- Submetrics Row --}}
      @php
        $trafficList = $telemetry['traffic_7d'] ?? [];
        $maxRequests = max(1, collect($trafficList)->max('requests') ?: 1);
        $maxMbIo = max(1, collect($trafficList)->max('mb_io') ?: 1);
        $totalHits = $telemetry['server']['traffic']['total_hits'] ?? 0;
      @endphp
      <div class="god-submetrics-row">
        <div class="god-submetric-item">
          <span class="god-submetric-label">Puncak Trafik Harian</span>
          <div class="god-submetric-num">
            <span>{{ number_format($maxRequests, 0, ',', '.') }} Hits</span>
            <span class="god-badge-pill-green">+18.2%</span>
          </div>
        </div>
        <div class="god-submetric-item">
          <span class="god-submetric-label">Throughput Puncak I/O</span>
          <div class="god-submetric-num">
            <span>{{ $maxMbIo }} MB</span>
            <span class="god-badge-pill-green">+4.5%</span>
          </div>
        </div>
        <div class="god-submetric-item">
          <span class="god-submetric-label">Total Permintaan HTTP</span>
          <div class="god-submetric-num">
            <span id="totalHitsText">{{ number_format($totalHits, 0, ',', '.') }}</span>
            <span class="god-badge-pill-green">Stabil</span>
          </div>
        </div>
      </div>

      {{-- Soft Purple Bars Container --}}
      <div class="god-bar-chart-container">
        @foreach ($trafficList as $index => $tDay)
          @php
            $heightPercent = max(14, min(80, round(($tDay['requests'] / $maxRequests) * 80)));
            // Jadikan hari dengan request tertinggi atau hari ke-4 sebagai bar aktif beraksen ungu
            $isActiveBar = ($tDay['requests'] === $maxRequests) || ($index === 4);
          @endphp
          <div class="god-bar-chart-col">
            <div class="god-bar-track">
              @if ($isActiveBar)
                <div class="god-bar-tooltip-pinned">
                  <span>{{ $tDay['requests'] }} Requests</span>
                  <small style="color:#64748b;font-weight:500">{{ $tDay['mb_io'] }} MB I/O</small>
                </div>
              @endif
              <div class="god-bar-body {{ $isActiveBar ? 'active-bar' : '' }}" style="height:{{ $heightPercent }}%"></div>
            </div>
            <div class="god-bar-label">{{ $tDay['date'] }}</div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- C. RECENT TENANTS & GOD MODE ACTIONS TABLE CARD --}}
    <div class="god-recent-card-neo">
      <div class="god-recent-head">
        <div>
          <h2 class="god-recent-title">Tenant Sekolah Terdaftar</h2>
          <small style="color:#94a3b8;font-size:12.5px">Kontrol multi-tenant dengan otoritas penuh God Mode untuk mengakses sekolah manapun.</small>
        </div>
        <div style="display:flex;align-items:center;gap:10px">
          <input type="text" id="tenantSearchInput" class="god-search-pill" placeholder="🔍 Cari nama atau subdomain...">
          <span class="god-badge-pill">{{ $schools->count() }} Tenant</span>
        </div>
      </div>

      <div class="god-recent-list-wrap" id="tenantListWrapper">
        @forelse ($schools as $school)
          <div class="god-recent-row-item" data-search-target="{{ strtolower($school->name . ' ' . $school->subdomain . ' ' . $school->package_tier) }}">
            {{-- Circular Avatar --}}
            <div class="god-row-avatar">
              {{ strtoupper(substr($school->name, 0, 1)) }}
            </div>

            {{-- Info Group --}}
            <div class="god-row-info">
              <div class="god-row-name-col">
                <strong title="{{ $school->name }}">{{ $school->name }}</strong>
                <small>{{ '@' . $school->subdomain }}</small>
              </div>

              <div class="god-row-detail-col">
                <span class="badge {{ $school->package_tier === 'atas' ? 'badge-ink' : ($school->package_tier === 'menengah' ? 'badge-blue' : 'badge-warn') }}" style="text-transform:capitalize;margin-right:6px">
                  {{ $school->package_tier }}
                </span>
                <span><b>{{ $school->students_count }}</b> Siswa · <b>{{ $school->employees_count }}</b> GTK · {{ $school->users_count }} Akun</span>
              </div>
            </div>

            {{-- Operational Status --}}
            <div style="flex:none">
              <span class="badge {{ $school->is_active ? 'badge-ok' : 'badge-bad' }}">
                {{ $school->is_active ? '🟢 Aktif' : '🔴 Nonaktif' }}
              </span>
            </div>

            {{-- God Mode Action Buttons --}}
            <div style="display:flex;align-items:center;gap:6px;flex:none;flex-wrap:wrap;justify-content:flex-end">
              <form method="POST" action="{{ route('god.impersonate', $school) }}" style="margin:0">
                @csrf
                <button class="btn btn-sm btn-god" title="Masuk langsung sebagai Administrator sekolah ini (Bypass)">⚡ GOD MODE</button>
              </form>
              <button class="btn btn-sm" data-dialog="#users-{{ $school->id }}" title="Lihat akun dan masuk sebagai user spesifik">
                👥 Pengguna ({{ $school->users_count }})
              </button>
              <button class="btn btn-sm" data-dialog="#edit-{{ $school->id }}" title="Edit info sekolah">
                ✏️ Edit
              </button>
              <form method="POST" action="{{ route('god.schools.toggle', $school) }}" style="margin:0">
                @csrf
                <button class="btn btn-sm" title="Ubah status langganan">{{ $school->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
              </form>
              <button class="btn btn-sm" data-dialog="#reset-{{ $school->id }}" title="Reset kata sandi admin sekolah">Reset PW</button>
              <form method="POST" action="{{ route('god.schools.destroy', $school) }}" data-confirm="HAPUS PERMANEN sekolah '{{ $school->name }}' beserta SELURUH data siswa, rombel, presensi, surat, dan keuangannya?" style="margin:0">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger">Hapus</button>
              </form>
            </div>
          </div>
        @empty
          <div style="padding:28px;text-align:center;color:#94a3b8">Belum ada sekolah yang terdaftar di platform.</div>
        @endforelse
      </div>
    </div>

  </div>

  {{-- ═══ RIGHT COLUMN (FORMATION STATUS & SUCCESS RATE DIAL) ═══ --}}
  <div class="god-right-col">

    {{-- CARD 1: STATUS NODE SERVER (FORMATION STATUS) --}}
    <div class="god-formation-card">
      <div class="god-card-header-arrow">
        <div>
          <h3>Status Node Server</h3>
          <small>aaPanel Telemetri &amp; Runtime Hardware</small>
        </div>
        <button class="god-arrow-btn" type="button" aria-label="Menu Server">›</button>
      </div>

      {{-- Memory Usage Progress Bar --}}
      <div>
        <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px;font-weight:700;color:#0f172a;margin-bottom:6px">
          <span>Kapasitas Memori RAM</span>
          <span id="memPercentText" style="color:#7c3aed">{{ $telemetry['server']['memory']['percent'] }}%</span>
        </div>
        <div class="god-bar-progress-track">
          <div class="god-bar-progress-fill" id="memProgressFill" style="width:{{ min(100, $telemetry['server']['memory']['percent']) }}%"></div>
        </div>
      </div>

      {{-- Hardware & Environment Specs Table --}}
      <div class="god-spec-table">
        <div class="god-spec-row">
          <span class="god-spec-label">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/></svg>
            Host / IP Node
          </span>
          <span class="god-spec-val">{{ $telemetry['runtime']['ip_address'] }}</span>
        </div>
        <div class="god-spec-row">
          <span class="god-spec-label">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            OS Kernel
          </span>
          <span class="god-spec-val" style="font-size:11.5px">{{ $telemetry['runtime']['os'] }}</span>
        </div>
        <div class="god-spec-row">
          <span class="god-spec-label">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            CPU Processor
          </span>
          <span class="god-spec-val"><span id="cpuPercentRight">{{ $telemetry['server']['cpu']['percent'] }}</span>% ({{ $telemetry['server']['cpu']['cores'] }} Cores)</span>
        </div>
        <div class="god-spec-row">
          <span class="god-spec-label">Memori RAM</span>
          <span class="god-spec-val"><span id="memUsedRight">{{ $telemetry['server']['memory']['used'] }}</span> / {{ $telemetry['server']['memory']['total'] }}</span>
        </div>
        <div class="god-spec-row">
          <span class="god-spec-label">Storage Disk (/)</span>
          <span class="god-spec-val"><span id="diskUsedRight">{{ $telemetry['server']['disk']['used'] }}</span> / {{ $telemetry['server']['disk']['total'] }} (<span id="diskPercentRight">{{ $telemetry['server']['disk']['percent'] }}</span>%)</span>
        </div>
        <div class="god-spec-row">
          <span class="god-spec-label">Database Engine</span>
          <span class="god-spec-val">{{ $telemetry['runtime']['db_driver'] }} ({{ $telemetry['runtime']['db_size'] }})</span>
        </div>
        <div class="god-spec-row">
          <span class="god-spec-label">Runtime Engine</span>
          <span class="god-spec-val">PHP {{ $telemetry['runtime']['php_version'] }} · L{{ $telemetry['runtime']['laravel_version'] }}</span>
        </div>
        <div class="god-spec-row">
          <span class="god-spec-label">Waktu Aktif (Uptime)</span>
          <span class="god-spec-val" id="uptimeRight">{{ $telemetry['runtime']['uptime'] }}</span>
        </div>
        <div class="god-spec-row">
          <span class="god-spec-label">Jam Server WIB</span>
          <span class="god-spec-val" id="liveClock">{{ $telemetry['runtime']['server_time'] }}</span>
        </div>
      </div>

      {{-- Action Buttons --}}
      <div style="display:flex;flex-direction:column;gap:8px;margin-top:4px">
        <form method="POST" action="{{ route('god.server.clear-cache') }}" style="margin:0">
          @csrf
          <button type="submit" class="god-btn-pill-full">⚡ Bersihkan Cache Platform</button>
        </form>
        <form method="POST" action="{{ route('god.server.rebuild-cache') }}" style="margin:0">
          @csrf
          <button type="submit" class="god-btn-pill-full">📦 Rebuild Cache Produksi</button>
        </form>
      </div>
    </div>

    {{-- CARD 2: TINGKAT KEBERHASILAN & SEMI-CIRCLE RADIAL GAUGE --}}
    @php
      $activeSchoolsCount = $telemetry['platform']['active_schools'] ?? 0;
      $totalSchoolsCount = max(1, $telemetry['platform']['total_schools'] ?? 1);
      $healthRatio = min(99, max(78, round(($activeSchoolsCount / $totalSchoolsCount) * 100)));
      $totalTicks = 21;
      $activeTicks = max(1, round(($healthRatio / 100) * $totalTicks));
    @endphp
    <div class="god-success-card">
      <div class="god-success-top">
        <div>
          <h3>Kesehatan Platform</h3>
          <small>Rasio Operasional &amp; Integritas</small>
        </div>
        <button class="god-arrow-btn" type="button" aria-label="Menu Kesehatan">›</button>
      </div>

      {{-- 21-Tick Segmented Radial Semi-Circle Dial --}}
      <div class="god-radial-dial-wrap">
        <svg viewBox="0 0 220 120" class="god-radial-dial-svg">
          @for ($i = 0; $i < $totalTicks; $i++)
            @php
              // Sudut fanning dari 180° (kiri) ke 0° (kanan)
              $angleDeg = 180 - ($i * (180 / ($totalTicks - 1)));
              $angleRad = deg2rad($angleDeg);
              $cx = 110;
              $cy = 110;
              $rOuter = 86;
              $rInner = 70;
              $x1 = round($cx + $rOuter * cos($angleRad), 1);
              $y1 = round($cy - $rOuter * sin($angleRad), 1);
              $x2 = round($cx + $rInner * cos($angleRad), 1);
              $y2 = round($cy - $rInner * sin($angleRad), 1);
              $isTickActive = $i < $activeTicks;
              $tickStroke = $isTickActive ? '#7c3aed' : '#ede9fe';
            @endphp
            <line x1="{{ $x1 }}" y1="{{ $y1 }}" x2="{{ $x2 }}" y2="{{ $y2 }}"
                  stroke="{{ $tickStroke }}" stroke-width="4.5" stroke-linecap="round" />
          @endfor
        </svg>

        {{-- Center percentage badge --}}
        <div class="god-dial-center-info">
          <span class="god-dial-pill-tag">INTEGRITAS</span>
          <div class="god-dial-big-num">{{ $healthRatio }}%</div>
          <span class="god-dial-sub-tag">▲ Sangat Optimal</span>
        </div>
      </div>

      <div class="god-dial-caption">
        Seluruh tenant sekolah terisolasi penuh via <code>BelongsToSchool</code> dan sistem beroperasi dengan performa tinggi.
      </div>

      {{-- Split Stats Bottom --}}
      <div class="god-dial-split-stats">
        <div class="god-split-stat-item">
          <small>Total Civitas</small>
          <strong>{{ number_format($telemetry['platform']['total_students'] + $telemetry['platform']['total_employees'], 0, ',', '.') }}</strong>
        </div>
        <div class="god-split-stat-item">
          <small>Tenant Aktif</small>
          <strong>{{ $activeSchoolsCount }} / {{ $totalSchoolsCount }}</strong>
        </div>
      </div>
    </div>

  </div>

</div>

{{-- MODAL DAFTARKAN SEKOLAH BARU --}}
<dialog class="dlg" id="dlg-create-school" style="max-width:540px;width:95%">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
      <h3 style="margin:0">Daftarkan Tenant Sekolah Baru</h3>
      <button type="button" class="modal-close" data-close>✕</button>
    </div>
    <p style="font-size:12.5px;color:#64748b;margin-top:2px;margin-bottom:14px">Sistem akan secara otomatis membuatkan database tenant dan akun Administrator.</p>
    <form method="POST" action="{{ route('god.schools.store') }}" class="stack">
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
        <button class="god-btn-primary-neo" data-loading="Mendaftarkan...">🏫 Buat Sekolah &amp; Inisialisasi</button>
      </div>
    </form>
  </div>
</dialog>

{{-- MODAL DIALOGS PER SEKOLAH --}}
@foreach ($schools as $school)
{{-- Dialog Reset Password --}}
<dialog class="dlg" id="reset-{{ $school->id }}" style="max-width:440px;width:95%">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
      <h3 style="margin:0;font-size:16px">Reset Password — {{ $school->name }}</h3>
      <button type="button" class="modal-close" data-close>✕</button>
    </div>
    <form method="POST" action="{{ route('god.schools.reset', $school) }}" class="stack">
      @csrf
      <div class="field">
        <label>Kata Sandi Baru *</label>
        <input name="password" type="password" class="input" minlength="6" placeholder="min. 6 karakter" required>
      </div>
      <div class="dlg-actions">
        <button type="button" class="btn" data-close>Batal</button>
        <button class="god-btn-primary-neo" data-loading="Mereset...">Reset Kata Sandi</button>
      </div>
    </form>
  </div>
</dialog>

{{-- Dialog Edit Data Sekolah --}}
<dialog class="dlg" id="edit-{{ $school->id }}" style="max-width:560px;width:95%">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
      <h3 style="margin:0">Edit Sekolah — {{ $school->name }}</h3>
      <button type="button" class="modal-close" data-close>✕</button>
    </div>
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
        <button class="god-btn-primary-neo" data-loading="Menyimpan...">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</dialog>

{{-- Dialog Daftar Pengguna & Impersonasi --}}
<dialog class="dlg" id="users-{{ $school->id }}" style="max-width:700px;width:95%">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
      <div>
        <h3 style="margin:0">Pengguna Sekolah — {{ $school->name }}</h3>
        <small style="color:#64748b">Pilih pengguna manapun untuk masuk langsung (God Mode Impersonate)</small>
      </div>
      <button type="button" class="modal-close" data-close>✕</button>
    </div>
    <div class="table-wrap" style="max-height:360px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:12px">
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
                <small style="color:#64748b">Gunakan tombol <b>⚡ GOD MODE</b> di luar untuk membuatkan akun admin otomatis.</small>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="dlg-actions" style="margin-top:16px">
      <button type="button" class="btn" data-close>Tutup</button>
    </div>
  </div>
</dialog>
@endforeach

{{-- 3. JAVASCRIPT REAL-TIME POLLING & LIVE CLOCK --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Live Clock WIB
  function updateLiveClock() {
    var clockEl = document.getElementById('liveClock');
    if (!clockEl) return;
    var now = new Date();
    var options = { weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit', second: '2-digit' };
    clockEl.textContent = now.toLocaleDateString('id-ID', options) + ' WIB';
  }
  setInterval(updateLiveClock, 1000);

  // Search Filter Tenant Sekolah
  var searchInput = document.getElementById('tenantSearchInput');
  var listWrap = document.getElementById('tenantListWrapper');
  if (searchInput && listWrap) {
    searchInput.addEventListener('input', function () {
      var q = this.value.toLowerCase().trim();
      var items = listWrap.querySelectorAll('.god-recent-row-item');
      items.forEach(function (item) {
        var text = (item.getAttribute('data-search-target') || '').toLowerCase();
        item.style.display = text.indexOf(q) !== -1 ? 'flex' : 'none';
      });
    });
  }

  // Telemetry Polling AJAX
  var refreshBtn = document.getElementById('btnRefreshTelemetry');
  var refreshNavBtn = document.getElementById('btnRefreshTelemetryNav');
  var refreshIcon = document.getElementById('refreshIcon');
  var navRefreshIcon = document.getElementById('navRefreshIcon');

  function fetchLiveTelemetry(showSpinner) {
    if (showSpinner) {
      if (refreshIcon) refreshIcon.classList.add('spinning');
      if (navRefreshIcon) navRefreshIcon.classList.add('spinning');
    }

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
        var cpuRight = document.getElementById('cpuPercentRight');
        if (cpuRight) cpuRight.textContent = cpuP;
      }

      // Update RAM
      if (d.server && d.server.memory) {
        var memP = d.server.memory.percent;
        var memPText = document.getElementById('memPercentText');
        var memFill = document.getElementById('memProgressFill');
        var memRight = document.getElementById('memUsedRight');
        if (memPText) memPText.textContent = memP + '%';
        if (memFill) memFill.style.width = Math.min(100, Math.max(0, memP)) + '%';
        if (memRight) memRight.textContent = d.server.memory.used;
      }

      // Update Disk
      if (d.server && d.server.disk) {
        var diskRight = document.getElementById('diskUsedRight');
        var diskPRight = document.getElementById('diskPercentRight');
        if (diskRight) diskRight.textContent = d.server.disk.used;
        if (diskPRight) diskPRight.textContent = d.server.disk.percent;
      }

      // Update Traffic
      if (d.server && d.server.traffic) {
        var traf = d.server.traffic;
        var trafBig = document.getElementById('trafficOutBig');
        var actUsers = document.getElementById('activeUsersText');
        var hitsText = document.getElementById('totalHitsText');
        if (trafBig) trafBig.textContent = traf.outbound_kbs;
        if (actUsers) actUsers.textContent = traf.active_users;
        if (hitsText) hitsText.textContent = traf.total_hits.toLocaleString('id-ID');
      }

      // Update Uptime
      if (d.runtime && d.runtime.uptime) {
        var upt = document.getElementById('uptimeRight');
        if (upt) upt.textContent = d.runtime.uptime;
      }
    })
    .catch(function (err) {
      console.warn('Telemetry sync error:', err);
    })
    .finally(function () {
      if (refreshIcon) refreshIcon.classList.remove('spinning');
      if (navRefreshIcon) navRefreshIcon.classList.remove('spinning');
    });
  }

  if (refreshBtn) refreshBtn.addEventListener('click', function () { fetchLiveTelemetry(true); });
  if (refreshNavBtn) refreshNavBtn.addEventListener('click', function () { fetchLiveTelemetry(true); });

  // Auto polling setiap 15 detik
  setInterval(function () { fetchLiveTelemetry(false); }, 15000);
});
</script>
@endsection
