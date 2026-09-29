<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#7c3aed">
<title>@yield('title', 'Kontrol Global') — Ruang GTK Super Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="icon" href="{{ asset('img/logo.svg') }}" type="image/svg+xml">
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) }}">
</head>
<body class="god-canvas-body">
{{-- Ambient Soft Glowing Blurs --}}
<div class="god-ambient-blur blur-violet"></div>
<div class="god-ambient-blur blur-blush"></div>

{{-- Main Floating Canvas Card (Neo-SaaS Canvas) --}}
<div class="god-canvas-wrapper">

  {{-- TOP NAVBAR WITH SEGMENTED CAPSULE --}}
  <header class="god-nav-header">
    <div class="god-nav-left">
      <a href="{{ route('god.dashboard') }}" style="display:flex;align-items:center;gap:12px;text-decoration:none">
        <div class="god-glyph-logo">
          <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
            <rect width="40" height="40" rx="12" fill="url(#god_glyph_grad)" />
            <path d="M13 20L18 25L27 15" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
            <defs>
              <linearGradient id="god_glyph_grad" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
                <stop stop-color="#7C3AED" />
                <stop offset="1" stop-color="#9333EA" />
              </linearGradient>
            </defs>
          </svg>
        </div>
        <div class="god-brand-text">
          <span class="god-brand-title">Ruang<span class="god-brand-accent">GTK</span></span>
          <span class="god-badge-pill">GOD MODE</span>
        </div>
      </a>
    </div>

    {{-- Center Segmented Capsule Menu --}}
    <nav class="god-nav-segmented" role="navigation" aria-label="Menu Utama God Mode">
      <a href="{{ route('god.dashboard') }}" class="god-seg-item {{ request()->routeIs('god.dashboard') ? 'active' : '' }}">
        Dashboard
      </a>
      <a href="{{ route('god.cms') }}" class="god-seg-item {{ request()->routeIs('god.cms') ? 'active' : '' }}">
        CMS Situs
      </a>
      <a href="{{ route('god.pages') }}" class="god-seg-item {{ request()->routeIs('god.pages') ? 'active' : '' }}">
        Halaman
      </a>
      <a href="{{ route('god.admins') }}" class="god-seg-item {{ request()->routeIs('god.admins') ? 'active' : '' }}">
        Super Admin
      </a>
    </nav>

    {{-- Right Utility Bar --}}
    <div class="god-nav-right">
      <button type="button" class="god-icon-btn" id="btnRefreshTelemetryNav" title="Sinkronisasi Telemetri Platform">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" id="navRefreshIcon">
          <path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
        </svg>
      </button>

      <div class="god-icon-btn" title="Notifikasi Sistem Operasional" style="cursor:default">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
          <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
        <span class="god-notif-dot"></span>
      </div>

      <div class="god-profile-chip">
        <div class="god-avatar-circle">
          <img src="{{ asset('img/logo.svg') }}" alt="Avatar">
        </div>
        <div class="god-profile-meta">
          <strong>{{ auth('super')->user()?->name ?? 'Super Administrator' }}</strong>
          <small>Super Admin</small>
        </div>
        <a href="{{ route('super.logout') }}"
           onclick="event.preventDefault();document.getElementById('super-logout-form').submit();"
           class="god-logout-trigger"
           title="Keluar dari sesi God Mode">
          <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
        </a>
      </div>
    </div>
  </header>

  {{-- Flash Toast Notification --}}
  @if (session('toast'))
    <div class="toast show" role="status">{{ session('toast') }}</div>
  @endif

  {{-- Main Content Canvas --}}
  <main class="god-content-area" style="min-width:0;width:100%">
    @yield('content')
  </main>

  {{-- Footer --}}
  <footer style="margin-top:20px;padding-top:16px;border-top:1px solid rgba(226,232,240,0.6);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;font-size:12px;color:#94a3b8">
    <div style="display:flex;align-items:center;gap:8px">
      <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#10b981"></span>
      <span>Ruang GTK Platform · Supreme God Mode Active</span>
    </div>
    <div>© 2026 Vicky Koroh · Multi-Tenant BelongsToSchool Global Isolation</div>
  </footer>

</div>

<form id="super-logout-form" action="{{ route('super.logout') }}" method="POST" class="hidden">@csrf</form>
<script src="{{ asset('js/app.js') }}?v={{ @filemtime(public_path('js/app.js')) }}"></script>
</body>
</html>
