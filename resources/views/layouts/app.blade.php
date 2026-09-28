<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#030712">
<title>@yield('title', 'Dashboard') — Ruang GTK</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="icon" href="{{ asset('img/logo.svg') }}" type="image/svg+xml">
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) }}">
</head>
<body>
<div class="intro-veil" aria-hidden="true"><div class="intro-mark"><img src="{{ asset('img/logo.svg') }}" alt="Logo Ruang GTK"></div></div>

{{-- Mobile Overlay --}}
<div class="side-overlay" id="sideOverlay" aria-hidden="true"></div>

<div class="shell">
  {{-- Mobile Top Bar --}}
  <header class="mobile-header glass-soft">
    <button class="hamburger" id="hamburgerBtn" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="sidebar">
      <svg viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
    </button>
    <div style="display:flex;align-items:center;gap:10px">
      <div class="brand-mark" style="width:34px;height:34px;border-radius:10px"><img src="{{ asset('img/logo.svg') }}" alt="Logo"></div>
      <div class="brand-ruanggtk">
        <span class="brand-ruanggtk-text" style="font-size:16px">Ruang<span class="gtk-tag">GTK</span></span>
        <span class="brand-beam"></span>
      </div>
    </div>
  </header>

  {{-- Sidebar --}}
  <aside class="side glass-soft" id="sidebar" role="navigation" aria-label="Menu utama">
    {{-- Close Button for Mobile Drawer --}}
    <button class="side-close" id="sideClose" aria-label="Tutup menu">
      <svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
    </button>

    {{-- Brand Section with Animated Typography --}}
    <div class="brand">
      <div class="brand-mark"><img src="{{ asset('img/logo.svg') }}" alt="Logo Ruang GTK"></div>
      <div style="min-width:0">
        <div class="brand-ruanggtk">
          <span class="brand-ruanggtk-text">Ruang<span class="gtk-tag">GTK</span></span>
          <span class="brand-beam"></span>
        </div>
        <small style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block">{{ auth()->user()?->school?->name ?? 'SIM Sekolah' }}</small>
      </div>
    </div>

    @if (session('god_impersonating'))
      <div class="god-banner" style="margin:0 2px 8px;padding:10px 14px;border-radius:14px;font-size:12.5px;font-weight:600;color:#fef08a;background:linear-gradient(135deg,rgba(245,158,11,0.22),rgba(239,68,68,0.15));border:1px solid rgba(245,158,11,0.45);display:grid;gap:4px">
        <span>⚡ GOD MODE · {{ session('god_school_name') }}</span>
        <a href="{{ route('god.exit') }}" style="color:#fde68a;text-decoration:underline">Keluar God Mode →</a>
      </div>
    @endif

    @php $userRole = auth()->user()?->role?->name; @endphp

    <div class="nav-label">Menu Utama</div>
    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
      Dashboard
    </a>

    {{-- Akademik --}}
    @if (in_array($userRole, ['admin', 'staff_tu', 'kepsek', 'guru']))
    <div class="nav-label">Akademik</div>

    @if (in_array($userRole, ['admin', 'staff_tu', 'kepsek']))
    <a href="{{ route('classes.index') }}" class="{{ request()->routeIs('classes.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24"><path d="M3 9l9-6 9 6"/><path d="M5 9v9a2 2 0 002 2h10a2 2 0 002-2V9"/><path d="M9 20v-6h6v6"/></svg>
      Data Kelas
    </a>
    @endif

    @if (in_array($userRole, ['admin', 'staff_tu', 'kepsek']))
    <a href="{{ route('students.index') }}" class="{{ request()->routeIs('students.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M3.5 20c.5-4 2.6-6 5.5-6s5 2 5.5 6"/><circle cx="17" cy="9" r="2.5"/><path d="M16 14.5c2.5.2 4 1.8 4.5 4.5"/></svg>
      Data Siswa
    </a>
    @endif

    @if (in_array($userRole, ['admin', 'guru', 'kepsek']))
    <a href="{{ route('attendance.index') }}" class="{{ request()->routeIs('attendance.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="17" rx="2.5"/><path d="M8 2.5v3M16 2.5v3M4 9.5h16"/><path d="M9 14.5l2 2 4-4"/></svg>
      Presensi Siswa
    </a>
    @endif
    @endif

    {{-- Kepegawaian --}}
    @if (in_array($userRole, ['admin', 'staff_tu', 'kepsek', 'guru']))
    <div class="nav-label">Kepegawaian</div>
    @if (in_array($userRole, ['admin', 'staff_tu', 'kepsek']))
    <a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="7.5" r="3.5"/><path d="M5 20.5c.7-4.5 3.2-7 7-7s6.3 2.5 7 7"/></svg>
      Data Pegawai (GTK)
    </a>
    @endif
    <a href="{{ route('attendance-employee.index') }}" class="{{ request()->routeIs('attendance-employee.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="17" rx="2.5"/><path d="M8 2.5v3M16 2.5v3M4 9.5h16"/><path d="M12 13v4M10 15h4"/></svg>
      Presensi GTK
    </a>
    @endif

    {{-- Keuangan --}}
    @if (in_array($userRole, ['admin', 'bendahara', 'kepsek']))
    <div class="nav-label">Keuangan</div>
    <a href="{{ route('bills.index') }}" class="{{ request()->routeIs('bills.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18"/><path d="M7 15h4"/></svg>
      Tagihan &amp; SPP
    </a>
    @endif

    {{-- Komunikasi & Warta --}}
    <div class="nav-label">Komunikasi</div>
    <a href="{{ route('announcements.index') }}" class="{{ request()->routeIs('announcements.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24"><path d="M4 11l12-6v14L4 13v-2z"/><path d="M16 8.5c2 .5 3 1.7 3 3.5s-1 3-3 3.5"/><path d="M7.5 14v4.5a1.5 1.5 0 003 0V15"/></svg>
      Pengumuman
    </a>

    {{-- Pengaturan Akun Pribadi --}}
    <div class="nav-label">Akun</div>
    <a href="{{ route('profile.index') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
      <svg viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
      Profil &amp; Sandi
    </a>

    @if (session('god_impersonating'))
      <div class="nav-label">Super Admin</div>
      <a href="{{ route('god.dashboard') }}" class="{{ request()->routeIs('god.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24"><path d="M13 2L4.5 13h6L9 22l9.5-12h-6L13 2z"/></svg>
        Kontrol Global
      </a>
    @endif

    {{-- User Profile Pill in Sidebar Footer --}}
    <div class="side-foot">
      <a href="{{ route('profile.index') }}" style="display:flex;align-items:center;gap:10px;padding:6px 8px;margin-bottom:8px;border-radius:var(--radius-sm);text-decoration:none;transition:background 0.2s">
        <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#0284c7,#2563eb);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:#fff;flex:none">
          {{ strtoupper(substr(auth()->user()?->username ?? 'U', 0, 2)) }}
        </div>
        <div style="min-width:0;line-height:1.3">
          <b style="font-size:13.5px;color:var(--text);display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ auth()->user()?->username }}</b>
          <small style="color:var(--muted);font-size:11.5px">{{ auth()->user()?->role?->name }} · {{ auth()->user()?->school?->subdomain }}</small>
        </div>
      </a>
      <div style="padding:4px 8px 6px;font-size:11px;color:var(--muted)">© 2026 Vicky Koroh · ConSentinel</div>
      <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();" style="color:var(--text-2)">
        <svg viewBox="0 0 24 24"><path d="M14 4h4a2 2 0 012 2v12a2 2 0 01-2 2h-4"/><path d="M10 17l-5-5 5-5M5 12h11"/></svg>
        Keluar
      </a>
    </div>
  </aside>

  {{-- Main Content Window --}}
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
