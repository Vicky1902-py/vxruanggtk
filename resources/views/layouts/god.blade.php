<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#0284c7">
<title>@yield('title', 'Kontrol Global') — Ruang GTK Super Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="icon" href="{{ asset('img/logo.svg') }}" type="image/svg+xml">
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) }}">
</head>
<body>
<header class="mobile-header glass-soft">
  <button class="hamburger" id="hamburgerBtn" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="sidebar">
    <svg viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
  </button>
  <div style="display:flex;align-items:center;gap:10px">
    <div class="brand-mark god-mark" style="width:34px;height:34px;border-radius:10px"><img src="{{ asset('img/logo.svg') }}" alt="Logo"></div>
    <b style="font-size:15px;color:var(--text)">GOD MODE</b>
  </div>
</header>

<div class="side-overlay" id="sideOverlay" aria-hidden="true"></div>

<div class="shell god-shell" id="appShell">

  <aside class="side glass-soft" id="sidebar" role="navigation" aria-label="Menu Super Admin">
    <button class="side-close" id="sideClose" aria-label="Tutup menu">
      <svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
    </button>

    <div class="brand">
      <div class="brand-mark god-mark"><img src="{{ asset('img/logo.svg') }}" alt="Logo Ruang GTK"></div>
      <div class="brand-info" style="min-width:0;flex:1">
        <div class="brand-ruanggtk">
          <span class="brand-ruanggtk-text">Ruang<span class="gtk-tag">GTK</span></span>
          <span class="brand-beam"></span>
        </div>
        <small style="color:#fdba74">Super Admin Platform</small>
      </div>
      <button type="button" class="aux-toggle" id="auxToggleBtn" title="Kecilkan / Lebarkan Sidebar (aaPanel Mode)" aria-label="Toggle Auxiliary Sidebar">
        <svg viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
      </button>
    </div>

    <div class="nav-label">Kontrol Global</div>
    <a href="{{ route('god.dashboard') }}" class="{{ request()->routeIs('god.dashboard') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 14l4-4 3 3 5-6"/></svg>
      Dashboard Global
    </a>
    <a href="{{ route('god.cms') }}" class="{{ request()->routeIs('god.cms') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="14" rx="2.5"/><path d="M3 9h18"/><path d="M8 21h8"/></svg>
      CMS Situs
    </a>
    <a href="{{ route('god.pages') }}" class="{{ request()->routeIs('god.pages') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24"><path d="M6 2.5h9L19 7v14H6z"/><path d="M14 2.5V7h5"/><path d="M9 12h6M9 15.5h6"/></svg>
      Halaman
    </a>
    <a href="{{ route('god.admins') }}" class="{{ request()->routeIs('god.admins') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="7.5" r="3.5"/><path d="M5 20.5c.7-4.5 3.2-7 7-7s6.3 2.5 7 7"/></svg>
      Super Admin
    </a>

    <div class="side-foot">
      <div style="display:flex;align-items:center;gap:10px;padding:0 8px 12px">
        <div style="width:34px;height:34px;border-radius:50%;background:var(--grad-gold);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:#0b1120;flex:none">
          ⚡
        </div>
        <div style="min-width:0;line-height:1.3">
          <b style="font-size:13.5px;color:var(--text);display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ auth('super')->user()?->name }}</b>
          <small style="color:var(--muted);font-size:11.5px">{{ '@' . auth('super')->user()?->username }}</small>
        </div>
      </div>
      <div style="padding:4px 8px 6px;font-size:11px;color:var(--muted)">© 2026 Vicky Koroh · God Mode</div>
      <a href="{{ route('super.logout') }}" onclick="event.preventDefault();document.getElementById('super-logout-form').submit();" style="color:var(--text-2)">
        <svg viewBox="0 0 24 24"><path d="M14 4h4a2 2 0 012 2v12a2 2 0 01-2 2h-4"/><path d="M10 17l-5-5 5-5M5 12h11"/></svg>
        Keluar Global
      </a>
    </div>
  </aside>

  <main class="main">
    @if (session('toast'))
      <div class="toast" role="status">{{ session('toast') }}</div>
    @endif
    @yield('content')
  </main>
</div>

<form id="super-logout-form" action="{{ route('super.logout') }}" method="POST" class="hidden">@csrf</form>
<script src="{{ asset('js/app.js') }}?v={{ @filemtime(public_path('js/app.js')) }}"></script>
</body>
</html>
