<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#0284c7">
<title>@yield('title', 'Dashboard') — Ruang GTK</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="icon" href="{{ asset('img/logo.svg') }}" type="image/svg+xml">
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) }}">
</head>
<body>
{{-- Mobile Top Bar (Hanya tampil di layar ponsel < 900px) --}}
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

{{-- Mobile Drawer Overlay --}}
<div class="side-overlay" id="sideOverlay" aria-hidden="true"></div>

<div class="shell" id="appShell">
  {{-- Sidebar (Selalu Kolom 1 di Desktop) --}}
  <aside class="side glass-soft" id="sidebar" role="navigation" aria-label="Menu utama">
    {{-- Close Button for Mobile Drawer --}}
    <button class="side-close" id="sideClose" aria-label="Tutup menu">
      <svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
    </button>

    {{-- Brand Section with Animated Typography & Auxiliary Toggle --}}
    <div class="brand">
      <div class="brand-mark"><img src="{{ asset('img/logo.svg') }}" alt="Logo Ruang GTK"></div>
      <div class="brand-info" style="min-width:0;flex:1">
        <div class="brand-ruanggtk">
          <span class="brand-ruanggtk-text">Ruang<span class="gtk-tag">GTK</span></span>
          <span class="brand-beam"></span>
        </div>
        <small style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block">{{ auth()->user()?->school?->name ?? 'SIM Sekolah' }}</small>
      </div>
      {{-- aaPanel Auxiliary Toggle Button --}}
      <button type="button" class="aux-toggle" id="auxToggleBtn" title="Kecilkan / Lebarkan Sidebar (aaPanel Mode)" aria-label="Toggle Auxiliary Sidebar">
        <svg viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
      </button>
    </div>

    @if (session('god_impersonating'))
      <div class="god-banner" style="margin:0 2px 8px;padding:10px 14px;border-radius:14px;font-size:12.5px;font-weight:700;color:#92400e;background:#fef3c7;border:1px solid #fcd34d;display:grid;gap:4px">
        <span>⚡ GOD MODE · {{ session('god_school_name') }}</span>
        <a href="{{ route('god.exit') }}" style="color:#b45309;text-decoration:underline;font-weight:600">Keluar God Mode →</a>
      </div>
    @endif

    @php $userRole = auth()->user()?->role?->name; @endphp

    <div class="nav-label">Menu Utama</div>
    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" data-tooltip="Dashboard">
      <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
      <span>Dashboard</span>
    </a>

    {{-- Akademik --}}
    @if (in_array($userRole, ['admin', 'staff_tu', 'kepsek', 'guru']))
    <div class="nav-label">Akademik</div>

    @if (in_array($userRole, ['admin', 'staff_tu', 'kepsek']))
    <a href="{{ route('majors.index') }}" class="{{ request()->routeIs('majors.*') ? 'active' : '' }}" data-tooltip="Data Jurusan">
      <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20M4 4.5A2.5 2.5 0 016.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15z"/></svg>
      <span>Data Jurusan</span>
    </a>

    <a href="{{ route('classes.index') }}" class="{{ request()->routeIs('classes.*') ? 'active' : '' }}" data-tooltip="Data Kelas & Rombel">
      <svg viewBox="0 0 24 24"><path d="M3 9l9-6 9 6"/><path d="M5 9v9a2 2 0 002 2h10a2 2 0 002-2V9"/><path d="M9 20v-6h6v6"/></svg>
      <span>Data Kelas</span>
    </a>

    <a href="{{ route('students.index') }}" class="{{ request()->routeIs('students.*') ? 'active' : '' }}" data-tooltip="Data Siswa">
      <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M3.5 20c.5-4 2.6-6 5.5-6s5 2 5.5 6"/><circle cx="17" cy="9" r="2.5"/><path d="M16 14.5c2.5.2 4 1.8 4.5 4.5"/></svg>
      <span>Data Siswa</span>
    </a>
    @endif

    @if (in_array($userRole, ['admin', 'guru', 'kepsek']))
    <a href="{{ route('attendance.index') }}" class="{{ request()->routeIs('attendance.*') ? 'active' : '' }}" data-tooltip="Presensi Siswa">
      <svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="17" rx="2.5"/><path d="M8 2.5v3M16 2.5v3M4 9.5h16"/><path d="M9 14.5l2 2 4-4"/></svg>
      <span>Presensi Siswa</span>
    </a>
    @endif

    @if (in_array($userRole, ['admin', 'guru', 'staff_tu', 'kepsek', 'bk']))
    <a href="{{ route('welfare.index') }}" class="{{ request()->routeIs('welfare.*') ? 'active' : '' }}" data-tooltip="Kedisiplinan & BK">
      <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      <span>Kedisiplinan &amp; BK</span>
    </a>
    @endif
    @endif

    {{-- Kepegawaian --}}
    @if (in_array($userRole, ['admin', 'staff_tu', 'kepsek', 'guru']))
    <div class="nav-label">Kepegawaian</div>
    @if (in_array($userRole, ['admin', 'staff_tu', 'kepsek']))
    <a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'active' : '' }}" data-tooltip="Data Pegawai (GTK)">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="7.5" r="3.5"/><path d="M5 20.5c.7-4.5 3.2-7 7-7s6.3 2.5 7 7"/></svg>
      <span>Data Pegawai (GTK)</span>
    </a>
    @endif
    <a href="{{ route('attendance-employee.index') }}" class="{{ request()->routeIs('attendance-employee.*') ? 'active' : '' }}" data-tooltip="Presensi GTK">
      <svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="17" rx="2.5"/><path d="M8 2.5v3M16 2.5v3M4 9.5h16"/><path d="M12 13v4M10 15h4"/></svg>
      <span>Presensi GTK</span>
    </a>
    @endif

    {{-- Keuangan --}}
    @if (in_array($userRole, ['admin', 'bendahara', 'kepsek']))
    <div class="nav-label">Keuangan</div>
    <a href="{{ route('bills.index') }}" class="{{ request()->routeIs('bills.*') ? 'active' : '' }}" data-tooltip="Tagihan & SPP">
      <svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18"/><path d="M7 15h4"/></svg>
      <span>Tagihan &amp; SPP</span>
    </a>
    <a href="{{ route('savings.index') }}" class="{{ request()->routeIs('savings.*') ? 'active' : '' }}" data-tooltip="Tabungan Siswa">
      <svg viewBox="0 0 24 24"><path d="M19 5c-1.5 0-2.8 1.2-3 2.7-.4 2.8 1.4 5.3 4.2 5.3h.8v4H3V7h11.2c.4-1.2 1.5-2 2.8-2z"/><circle cx="17" cy="9" r="1"/></svg>
      <span>Tabungan Siswa</span>
    </a>
    <a href="{{ route('payrolls.index') }}" class="{{ request()->routeIs('payrolls.*') ? 'active' : '' }}" data-tooltip="Penggajian GTK">
      <svg viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
      <span>Penggajian GTK</span>
    </a>
    <a href="{{ route('reports.financial') }}" class="{{ request()->routeIs('reports.financial*') ? 'active' : '' }}" data-tooltip="Laporan Kas (BKU)">
      <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20M4 4.5A2.5 2.5 0 016.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15z"/></svg>
      <span>Laporan Kas (BKU)</span>
    </a>
    @endif

    {{-- Komunikasi & Warta --}}
    <div class="nav-label">Komunikasi</div>
    <a href="{{ route('announcements.index') }}" class="{{ request()->routeIs('announcements.*') ? 'active' : '' }}" data-tooltip="Pengumuman">
      <svg viewBox="0 0 24 24"><path d="M4 11l12-6v14L4 13v-2z"/><path d="M16 8.5c2 .5 3 1.7 3 3.5s-1 3-3 3.5"/><path d="M7.5 14v4.5a1.5 1.5 0 003 0V15"/></svg>
      <span>Pengumuman</span>
    </a>

    {{-- Administrasi Persuratan & SK --}}
    @if (in_array($userRole, ['admin', 'staff_tu', 'kepsek']))
    <div class="nav-label">Persuratan</div>
    <a href="{{ route('letters.index') }}" class="{{ request()->routeIs('letters.index', 'letters.show', 'letters.edit') ? 'active' : '' }}" data-tooltip="Buku Agenda & SK">
      <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
      <span>Buku Agenda &amp; SK</span>
    </a>
    <a href="{{ route('letters.create') }}" class="{{ request()->routeIs('letters.create') ? 'active' : '' }}" data-tooltip="Buat Surat / SK Baru">
      <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      <span>Buat Surat / SK</span>
    </a>
    <a href="{{ route('letter-types.index') }}" class="{{ request()->routeIs('letter-types.*') ? 'active' : '' }}" data-tooltip="Format & Jenis Surat">
      <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
      <span>Format Penomoran</span>
    </a>
    @endif

    {{-- Pengaturan Sistem / User (Khusus Admin) --}}
    @if ($userRole === 'admin')
    <div class="nav-label">Pengaturan</div>
    <a href="{{ route('school.settings') }}" class="{{ request()->routeIs('school.settings*') ? 'active' : '' }}" data-tooltip="Pengaturan Sekolah">
      <svg viewBox="0 0 24 24"><path d="M3 9l9-6 9 6"/><path d="M5 9v9a2 2 0 002 2h10a2 2 0 002-2V9"/><path d="M9 20v-6h6v6"/></svg>
      <span>Pengaturan Sekolah</span>
    </a>
    <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}" data-tooltip="Manajemen Pengguna">
      <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      <span>Pengguna Sekolah</span>
    </a>
    @endif

    {{-- Pengaturan Akun Pribadi --}}
    <div class="nav-label">Akun</div>
    <a href="{{ route('profile.index') }}" class="{{ request()->routeIs('profile.*') ? 'active' : '' }}" data-tooltip="Profil & Sandi">
      <svg viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
      <span>Profil &amp; Sandi</span>
    </a>

    @if (session('god_impersonating'))
      <div class="nav-label">Super Admin</div>
      <a href="{{ route('god.dashboard') }}" class="{{ request()->routeIs('god.*') ? 'active' : '' }}" data-tooltip="Kontrol Global">
        <svg viewBox="0 0 24 24"><path d="M13 2L4.5 13h6L9 22l9.5-12h-6L13 2z"/></svg>
        <span>Kontrol Global</span>
      </a>
    @endif

    {{-- User Profile Pill in Sidebar Footer --}}
    <div class="side-foot">
      <a href="{{ route('profile.index') }}" class="user-pill" style="display:flex;align-items:center;gap:10px;padding:6px 8px;margin-bottom:8px;border-radius:var(--radius-sm);text-decoration:none;transition:background 0.2s" data-tooltip="{{ auth()->user()?->username }}">
        <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#0284c7,#2563eb);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:#fff;flex:none">
          {{ strtoupper(substr(auth()->user()?->username ?? 'U', 0, 2)) }}
        </div>
        <div class="user-meta" style="min-width:0;line-height:1.3">
          <b style="font-size:13.5px;color:var(--text);display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ auth()->user()?->username }}</b>
          <small style="color:var(--muted);font-size:11.5px">{{ auth()->user()?->role?->name }} · {{ auth()->user()?->school?->subdomain }}</small>
        </div>
      </a>
      <div class="copyright-txt" style="padding:4px 8px 6px;font-size:11px;color:var(--muted)">© 2026 Vicky Koroh · Ruang GTK</div>
      <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();" style="color:var(--text-2)" data-tooltip="Keluar">
        <svg viewBox="0 0 24 24"><path d="M14 4h4a2 2 0 012 2v12a2 2 0 01-2 2h-4"/><path d="M10 17l-5-5 5-5M5 12h11"/></svg>
        <span>Keluar</span>
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
