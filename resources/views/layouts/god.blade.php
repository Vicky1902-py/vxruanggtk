<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#0f1116">
<title>@yield('title', 'Kontrol Global') — Ruang GTK Super Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="icon" href="{{ asset('img/logo.svg') }}" type="image/svg+xml">
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
</head>
<body>
<div class="intro-veil" aria-hidden="true"><div class="intro-mark"><img src="{{ asset('img/logo.svg') }}" alt=""></div></div>
<div class="shell god-shell">
  <aside class="side glass-soft">
    <div class="brand">
      <div class="brand-mark god-mark"><img src="{{ asset('img/logo.svg') }}" alt="Logo Ruang GTK"></div>
      <div>
        <b>Ruang GTK</b>
        <small>Super Admin Platform</small>
      </div>
    </div>

    <div class="nav-label">Kontrol Global</div>
    <a href="{{ route('god.dashboard') }}" class="{{ request()->routeIs('god.dashboard') || request()->routeIs('god.cms') || request()->routeIs('god.pages') ? 'active' : '' }}">
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
      <div class="who">
        <b>{{ auth('super')->user()?->name }}</b>
        {{ '@' . auth('super')->user()?->username }}
      </div>
      <div style="padding:8px 12px 2px;font-size:11px;color:var(--muted)">© 2026 Vicky Koroh</div>
      <a href="{{ route('super.logout') }}" onclick="event.preventDefault();document.getElementById('super-logout-form').submit();">
        <svg viewBox="0 0 24 24"><path d="M14 4h4a2 2 0 012 2v12a2 2 0 01-2 2h-4"/><path d="M10 17l-5-5 5-5M5 12h11"/></svg>
        Keluar
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
<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
</body>
</html>
