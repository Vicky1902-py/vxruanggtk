<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#0f1116">
<title>@yield('title', 'Dashboard') — Ruang GTK</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="icon" href="{{ asset('img/logo.svg') }}" type="image/svg+xml">
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) }}">
</head>
<body>
<div class="intro-veil" aria-hidden="true"><div class="intro-mark"><img src="{{ asset('img/logo.svg') }}" alt=""></div></div>

{{-- Mobile overlay --}}
<div class="side-overlay" id="sideOverlay" aria-hidden="true"></div>

<div class="shell">
  {{-- Mobile top bar --}}
  <header class="mobile-header glass-soft">
    <button class="hamburger" id="hamburgerBtn" aria-label="Buka menu" aria-expanded="false" aria-controls="sidebar">
      <svg viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
    </button>
    <div style="display:flex;align-items:center;gap:8px">
      <div class="brand-mark" style="width:30px;height:30px;border-radius:9px"><img src="{{ asset('img/logo.svg') }}" alt="Logo Ruang GTK"></div>
      <b style="font-size:14px;color:var(--text)">Ruang GTK</b>
    </div>
  </header>

  <aside class="side glass-soft" id="sidebar" role="navigation" aria-label="Menu utama">
    {{-- Close button (mobile only) --}}
    <button class="side-close" id="sideClose" aria-label="Tutup menu">
      <svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
    </button>

    <div class="brand">
      <div class="brand-mark"><img src="{{ asset('img/logo.svg') }}" alt="Logo Ruang GTK"></div>
      <div>
        <b>Ruang GTK</b>
        <small>{{ auth()->user()?->school?->name ?? 'SIM Sekolah' }}</small>
      </div>
    </div>

    @if (session('god_impersonating'))
      <div class="god-banner">
        👁️ GOD MODE — {{ session('god_school_name') }}
        <a href="{{ route('god.exit') }}">Keluar God Mode</a>
      </div>
    @endif

    @php $userRole = auth()->user()?->role?->name; @endphp

    <div class="nav-label">Menu Utama</div>
    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
      Dashboard
    </a>

    <div class="nav-label">Akademik</div>

    @if (in_array($userRole, ['admin']))
    <a href="{{ route('classes.index') }}" class="{{ request()->routeIs('classes.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24"><path d="M3 9l9-6 9 6"/><path d="M5 9v9a2 2 0 002 2h10a2 2 0 002-2V9"/><path d="M9 20v-6h6v6"/></svg>
      Data Kelas
    </a>
    @endif

    @if (in_array($userRole, ['admin', 'staff_tu']))
    <a href="{{ route('students.index') }}" class="{{ request()->routeIs('students.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M3.5 20c.5-4 2.6-6 5.5-6s5 2 5.5 6"/><circle cx="17" cy="9" r="2.5"/><path d="M16 14.5c2.5.2 4 1.8 4.5 4.5"/></svg>
      Data Siswa
    </a>
    @endif

    @if (in_array($userRole, ['admin', 'guru']))
    <a href="{{ route('attendance.index') }}" class="{{ request()->routeIs('attendance.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="17" rx="2.5"/><path d="M8 2.5v3M16 2.5v3M4 9.5h16"/><path d="M9 14.5l2 2 4-4"/></svg>
      Presensi Siswa
    </a>
    @endif

    @if (in_array($userRole, ['admin', 'staff_tu']))
    <div class="nav-label">Kepegawaian</div>
    <a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="7.5" r="3.5"/><path d="M5 20.5c.7-4.5 3.2-7 7-7s6.3 2.5 7 7"/></svg>
      Data Pegawai
    </a>
    @endif

    @if ($userRole === 'admin')
    <div class="nav-label">Keuangan</div>
    <a href="{{ route('bills.index') }}" class="{{ request()->routeIs('bills.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18"/><path d="M7 15h4"/></svg>
      Tagihan &amp; Bayar
    </a>

    <div class="nav-label">Komunikasi</div>
    <a href="{{ route('announcements.index') }}" class="{{ request()->routeIs('announcements.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24"><path d="M4 11l12-6v14L4 13v-2z"/><path d="M16 8.5c2 .5 3 1.7 3 3.5s-1 3-3 3.5"/><path d="M7.5 14v4.5a1.5 1.5 0 003 0V15"/></svg>
      Pengumuman
    </a>
    @endif

    @if (session('god_impersonating'))
      <div class="nav-label">Super Admin</div>
      <a href="{{ route('god.dashboard') }}" class="{{ request()->routeIs('god.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24"><path d="M13 2L4.5 13h6L9 22l9.5-12h-6L13 2z"/></svg>
        Kontrol Global
      </a>
    @endif

    <div class="side-foot">
      <div class="who">
        {{ auth()->user()?->username }} · {{ auth()->user()?->role?->name }}<br>
        {{ auth()->user()?->school?->subdomain }}
      </div>
      <div style="padding:8px 12px 2px;font-size:11px;color:var(--muted)">© 2026 Vicky Koroh</div>
      <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
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

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
<script src="{{ asset('js/app.js') }}?v={{ @filemtime(public_path('js/app.js')) }}"></script>
</body>
</html>
