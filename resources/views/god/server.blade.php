@extends('layouts.god')
@section('title', 'Pemeliharaan Server & Database')

@section('content')
<div class="god-hero-strip" style="margin-bottom:20px">
  <div>
    <h1 class="god-greeting-title">Server, Database &amp; Trafik Langsung</h1>
    <div class="god-greeting-sub">
      <span class="live-pulse-dot"></span>
      <span>Cadangkan database 1-klik, pembersihan &amp; defragmentasi storage, serta pemantauan trafik real-time.</span>
    </div>
  </div>
  <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
    <a href="{{ route('god.server.backup') }}" class="god-btn-primary-neo" title="Download cadangan database instan">
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      📥 Unduh Backup Database (.{{ $dbDriver === 'sqlite' ? 'sqlite' : 'sql' }})
    </a>
    <form method="POST" action="{{ route('god.server.clean-database') }}" style="margin:0" data-confirm="Jalankan pembersihan database, defragmentasi storage, dan pembersihan cache sistem?">
      @csrf
      <button type="submit" class="god-btn-secondary-neo" title="Optimalkan tabel database dan bersihkan cache">
        🧹 Bersihkan Database
      </button>
    </form>
  </div>
</div>

<div class="god-main-grid">

  {{-- KOLOM KIRI: LIVE TRAFFIC STREAM (REAL DATA MONITOR) --}}
  <div class="god-left-col">

    {{-- LIVE TRAFFIC FEED CARD --}}
    <div class="god-recent-card-neo">
      <div class="god-recent-head">
        <div>
          <div style="display:flex;align-items:center;gap:8px">
            <span class="live-pulse-dot"></span>
            <h2 class="god-recent-title" style="margin:0">Trafik Langsung Real-Time (Live Feed)</h2>
          </div>
          <small style="color:#94a3b8;font-size:12.5px">Aliran request HTTP yang masuk ke platform secara langsung (IP, Browser, URL, Status &amp; Waktu Eksekusi).</small>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
          <button type="button" class="god-btn-secondary-neo" id="btnRefreshLiveTraffic" style="height:32px;padding:0 12px;font-size:12px">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" id="trafficSpinIcon"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
            Refresh Feed
          </button>
          <span class="god-badge-pill" id="liveFeedCounter">{{ count($liveTraffic) }} Request Aktif</span>
        </div>
      </div>

      <div class="table-wrap" style="border:1px solid #e2e8f0;border-radius:14px;overflow-x:auto;max-height:480px;overflow-y:auto">
        <table class="tbl" id="liveTrafficTable" style="margin:0">
          <thead>
            <tr>
              <th style="width:85px">Waktu</th>
              <th style="width:70px">Metode</th>
              <th>Jalur URL (Path)</th>
              <th style="width:75px">Status</th>
              <th style="width:85px">Latensi</th>
              <th>IP &amp; Pengguna</th>
              <th>Perangkat / Browser</th>
            </tr>
          </thead>
          <tbody id="liveTrafficTbody">
            @forelse ($liveTraffic as $item)
              @php
                $statusColor = ($item['status'] >= 200 && $item['status'] < 300)
                  ? 'badge-ok'
                  : (($item['status'] >= 300 && $item['status'] < 400) ? 'badge-blue' : 'badge-bad');
                $methodColor = ($item['method'] === 'POST')
                  ? 'badge-ink'
                  : (($item['method'] === 'DELETE') ? 'badge-bad' : 'badge-warn');
              @endphp
              <tr>
                <td><small style="color:#64748b;font-weight:600">{{ $item['time'] }}</small></td>
                <td><span class="badge {{ $methodColor }}" style="font-size:10px;height:22px;padding:0 8px">{{ $item['method'] }}</span></td>
                <td><code style="background:#f1f5f9;color:#0f172a;padding:2px 6px;border-radius:4px;font-size:12px;font-weight:600">{{ $item['path'] }}</code></td>
                <td><span class="badge {{ $statusColor }}" style="font-size:10.5px;height:22px;padding:0 8px">{{ $item['status'] }}</span></td>
                <td><span style="font-size:12px;color:{{ $item['duration_ms'] > 100 ? '#d97706' : '#16a34a' }};font-weight:700">{{ $item['duration_ms'] }}ms</span></td>
                <td>
                  <b style="font-size:12.5px;color:#0f172a;display:block">{{ $item['user'] }}</b>
                  <small style="color:#94a3b8;font-size:11px">{{ $item['ip'] }}</small>
                </td>
                <td><small style="color:#475569;font-size:11.5px">{{ $item['client'] }}</small></td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="empty" style="padding:24px;text-align:center">Belum ada rekaman request aktif.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- PEMELIHARAAN & OPTIMASI DATABASE CARD --}}
    <div class="god-recent-card-neo">
      <div class="god-recent-head">
        <div>
          <h2 class="god-recent-title">🧹 Pembersihan &amp; Optimasi Database</h2>
          <small style="color:#94a3b8;font-size:12.5px">Reclaim ruang disk penyimpanan database, bersihkan sesi kadaluarsa, dan optimasi tabel.</small>
        </div>
      </div>

      <div class="form-grid" style="grid-template-columns:1fr 1fr;gap:16px">
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:16px">
          <h4 style="margin:0 0 6px;font-size:13.5px;color:#0f172a">📦 Unduh Snapshot Database Penuh</h4>
          <p style="font-size:12px;color:#64748b;line-height:1.4;margin-bottom:12px">
            Menghasilkan dump database lengkap berisi seluruh data sekolah, konfigurasi, civitas, dan histori keuangan.
          </p>
          <a href="{{ route('god.server.backup') }}" class="god-btn-primary-neo" style="width:100%;justify-content:center">
            📥 Unduh Backup Database (.{{ $dbDriver === 'sqlite' ? 'sqlite' : 'sql' }})
          </a>
        </div>

        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:16px">
          <h4 style="margin:0 0 6px;font-size:13.5px;color:#0f172a">🧹 Bersihkan &amp; Defragmentasi Storage</h4>
          <p style="font-size:12px;color:#64748b;line-height:1.4;margin-bottom:12px">
            Menjalankan <code>{{ $dbDriver === 'sqlite' ? 'VACUUM' : 'OPTIMIZE TABLE' }}</code>, menghapus sesi kedaluwarsa, dan merefresh cache sistem.
          </p>
          <form method="POST" action="{{ route('god.server.clean-database') }}" style="margin:0">
            @csrf
            <button type="submit" class="god-btn-secondary-neo" style="width:100%;justify-content:center">
              🧹 Jalankan Pembersihan Sekarang
            </button>
          </form>
        </div>
      </div>
    </div>

  </div>

  {{-- KOLOM KANAN: STATUS DATABASE & HARDWARE AAPANEL --}}
  <div class="god-right-col">

    {{-- STATUS DATABASE SPESIFIKASI --}}
    <div class="god-formation-card">
      <div class="god-card-header-arrow">
        <div>
          <h3>Metrik Database Platform</h3>
          <small>Informasi kapasitas &amp; engine</small>
        </div>
      </div>

      <div class="god-spec-table">
        <div class="god-spec-row">
          <span class="god-spec-label">Driver Database</span>
          <span class="god-spec-val" style="text-transform:uppercase">{{ $dbDriver }}</span>
        </div>
        <div class="god-spec-row">
          <span class="god-spec-label">Ukuran Database</span>
          <span class="god-spec-val" style="color:#7c3aed">{{ $telemetry['runtime']['db_size'] }}</span>
        </div>
        <div class="god-spec-row">
          <span class="god-spec-label">Total Sekolah (Tenant)</span>
          <span class="god-spec-val">{{ $telemetry['platform']['total_schools'] }} Sekolah</span>
        </div>
        <div class="god-spec-row">
          <span class="god-spec-label">Total Akun Pengguna</span>
          <span class="god-spec-val">{{ $telemetry['platform']['total_users'] }} User</span>
        </div>
        <div class="god-spec-row">
          <span class="god-spec-label">Surat &amp; Dokumen</span>
          <span class="god-spec-val">{{ number_format($telemetry['platform']['total_letters'], 0, ',', '.') }}</span>
        </div>
        <div class="god-spec-row">
          <span class="god-spec-label">Integritas Scoping</span>
          <span class="god-spec-val" style="color:#16a34a">BelongsToSchool Active</span>
        </div>
      </div>

      <div style="display:flex;flex-direction:column;gap:8px;margin-top:6px">
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

    {{-- HARDWARE TELEMETRY AAPANEL --}}
    <div class="god-formation-card">
      <div class="god-card-header-arrow">
        <div>
          <h3>Status Node &amp; Hardware</h3>
          <small>aaPanel Environment Telemetry</small>
        </div>
      </div>

      <div class="god-spec-table">
        <div class="god-spec-row">
          <span class="god-spec-label">Host Node</span>
          <span class="god-spec-val">{{ $telemetry['runtime']['ip_address'] }}</span>
        </div>
        <div class="god-spec-row">
          <span class="god-spec-label">OS Kernel</span>
          <span class="god-spec-val" style="font-size:11.5px">{{ $telemetry['runtime']['os'] }}</span>
        </div>
        <div class="god-spec-row">
          <span class="god-spec-label">CPU Processor</span>
          <span class="god-spec-val">{{ $telemetry['server']['cpu']['percent'] }}% ({{ $telemetry['server']['cpu']['cores'] }} Cores)</span>
        </div>
        <div class="god-spec-row">
          <span class="god-spec-label">Memori RAM</span>
          <span class="god-spec-val">{{ $telemetry['server']['memory']['used'] }} / {{ $telemetry['server']['memory']['total'] }}</span>
        </div>
        <div class="god-spec-row">
          <span class="god-spec-label">Storage Disk</span>
          <span class="god-spec-val">{{ $telemetry['server']['disk']['used'] }} / {{ $telemetry['server']['disk']['total'] }} ({{ $telemetry['server']['disk']['percent'] }}%)</span>
        </div>
        <div class="god-spec-row">
          <span class="god-spec-label">Waktu Aktif (Uptime)</span>
          <span class="god-spec-val">{{ $telemetry['runtime']['uptime'] }}</span>
        </div>
      </div>
    </div>

  </div>

</div>

{{-- SCRIPT AUTO REFRESH LIVE TRAFFIC --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
  var refreshBtn = document.getElementById('btnRefreshLiveTraffic');
  var spinIcon = document.getElementById('trafficSpinIcon');
  var tbody = document.getElementById('liveTrafficTbody');
  var counter = document.getElementById('liveFeedCounter');

  function updateLiveFeed() {
    if (spinIcon) spinIcon.classList.add('spinning');

    fetch('{{ route("god.server.live-traffic") }}', {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function (res) { return res.json(); })
    .then(function (json) {
      if (!json || !json.data || !tbody) return;
      var rows = json.data;
      if (counter) counter.textContent = rows.length + ' Request Aktif';

      if (rows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="empty" style="padding:24px;text-align:center">Belum ada request aktif.</td></tr>';
        return;
      }

      var html = '';
      rows.forEach(function (item) {
        var statusColor = (item.status >= 200 && item.status < 300)
          ? 'badge-ok'
          : ((item.status >= 300 && item.status < 400) ? 'badge-blue' : 'badge-bad');
        var methodColor = (item.method === 'POST')
          ? 'badge-ink'
          : ((item.method === 'DELETE') ? 'badge-bad' : 'badge-warn');
        var latColor = item.duration_ms > 100 ? '#d97706' : '#16a34a';

        html += '<tr>'
          + '<td><small style="color:#64748b;font-weight:600">' + item.time + '</small></td>'
          + '<td><span class="badge ' + methodColor + '" style="font-size:10px;height:22px;padding:0 8px">' + item.method + '</span></td>'
          + '<td><code style="background:#f1f5f9;color:#0f172a;padding:2px 6px;border-radius:4px;font-size:12px;font-weight:600">' + item.path + '</code></td>'
          + '<td><span class="badge ' + statusColor + '" style="font-size:10.5px;height:22px;padding:0 8px">' + item.status + '</span></td>'
          + '<td><span style="font-size:12px;color:' + latColor + ';font-weight:700">' + item.duration_ms + 'ms</span></td>'
          + '<td><b style="font-size:12.5px;color:#0f172a;display:block">' + item.user + '</b><small style="color:#94a3b8;font-size:11px">' + item.ip + '</small></td>'
          + '<td><small style="color:#475569;font-size:11.5px">' + item.client + '</small></td>'
          + '</tr>';
      });

      tbody.innerHTML = html;
    })
    .catch(function (err) {
      console.warn('Live traffic feed error:', err);
    })
    .finally(function () {
      if (spinIcon) spinIcon.classList.remove('spinning');
    });
  }

  if (refreshBtn) {
    refreshBtn.addEventListener('click', updateLiveFeed);
  }

  // Polling otomatis setiap 8 detik
  setInterval(updateLiveFeed, 8000);
});
</script>
@endsection
