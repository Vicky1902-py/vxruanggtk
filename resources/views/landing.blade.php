@extends('layouts.guest')
@section('title', 'Sistem Informasi Manajemen Sekolah')

@section('content')
{{-- Centered Frosted Glass Navigation Pill (ConSentinel Style) --}}
<nav class="landing-nav glass-soft" aria-label="Navigasi utama">
  <a href="{{ route('landing') }}" class="brand-ruanggtk" style="padding:0 8px">
    <img src="{{ asset('img/logo.svg') }}" alt="Logo" style="width:26px;height:26px;border-radius:8px">
    <span class="brand-ruanggtk-text" style="font-size:16px">Ruang<span class="gtk-tag">GTK</span></span>
    <span class="brand-beam"></span>
  </a>
  <a href="#fitur">Fitur</a>
  <a href="#modul">Modul</a>
  <a href="{{ route('login') }}" class="btn btn-sm btn-ink" style="height:32px;padding:0 18px;margin-left:4px">
    Masuk Portal
  </a>
</nav>

{{-- Hero Section --}}
<section class="landing-hero">
  <div>
    <div style="margin-bottom:20px">
      <span class="cs-pill">
        <span class="dot"></span>
        Sistem Informasi Manajemen Sekolah &amp; GTK Terintegrasi
      </span>
    </div>

    <h1 class="hero-animated-title">
      Satu ruang cerdas untuk <br>
      <span class="highlight-caustic">Ruang GTK</span> &amp; institusi Anda.
    </h1>

    <p class="lede">{{ $tagline }}</p>

    <div class="landing-cta">
      <a href="{{ route('login') }}" class="btn btn-ink" style="height:48px;padding:0 30px;font-size:15px">
        <span>Masuk ke Aplikasi</span>
        <svg viewBox="0 0 24 24" style="width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </a>
      <a href="#fitur" class="btn" style="height:48px;padding:0 24px;font-size:15px">
        Eksplorasi Fitur
      </a>
    </div>

    <!-- Telemetry Metrics Strip -->
    <div style="display:flex;gap:26px;margin-top:44px;flex-wrap:wrap">
      <div>
        <b style="font-size:22px;font-weight:800;color:#0f172a;display:block">100%</b>
        <small style="color:#475569;font-size:12.5px;font-weight:600">Isolasi Data Subdomain</small>
      </div>
      <div style="width:1px;background:#cbd5e1;height:34px;align-self:center"></div>
      <div>
        <b style="font-size:22px;font-weight:800;color:#0284c7;display:block">Real-time</b>
        <small style="color:#475569;font-size:12.5px;font-weight:600">Presensi Siswa &amp; GTK</small>
      </div>
      <div style="width:1px;background:#cbd5e1;height:34px;align-self:center"></div>
      <div>
        <b style="font-size:22px;font-weight:800;color:#16a34a;display:block">Otomatis</b>
        <small style="color:#475569;font-size:12.5px;font-weight:600">Manajemen Keuangan &amp; SPP</small>
      </div>
    </div>
  </div>

  <div class="hero-visual">
    <img src="{{ asset($heroImage) }}" alt="ConSentinel Security Viewport Ruang GTK" loading="eager">
  </div>
</section>

{{-- Features Section --}}
<section id="fitur" style="position:relative;z-index:1;padding:50px 0 10px">
  <div style="text-align:center;margin-bottom:12px">
    <span class="cs-pill" style="font-size:11px">🛡️ Ekosistem Pendidikan Terpadu</span>
  </div>
  <h2 class="landing-section-title">Semua yang dibutuhkan sekolah.</h2>
  <p class="landing-section-sub">Solusi terintegrasi untuk seluruh operasional pendidikan, manajemen GTK, dan transparansi siswa.</p>
</section>

<div class="features-grid">
  <div class="glass landing-feature reveal" data-reveal-delay="0">
    <img src="{{ asset('img/tenant.svg') }}" alt="Multi-Tenant" loading="lazy" style="width:100%;border-radius:14px;margin-bottom:6px">
    <div style="display:flex;align-items:center;gap:12px">
      <span class="icon-chip is-blue">
        <svg viewBox="0 0 24 24"><path d="M12 3l9 5-9 5-9-5 9-5z"/><path d="M3 13l9 5 9-5"/><path d="M3 17l9 5 9-5"/></svg>
      </span>
      <h3>Isolasi Multi-Tenant</h3>
    </div>
    <p>Setiap sekolah memiliki subdomain mandiri dan basis data terisolasi penuh — privasi tinggi, stabil, dan aman.</p>
  </div>

  <div class="glass landing-feature reveal" data-reveal-delay="90">
    <img src="{{ asset('img/academic.svg') }}" alt="Akademik & Presensi" loading="lazy" style="width:100%;border-radius:14px;margin-bottom:6px">
    <div style="display:flex;align-items:center;gap:12px">
      <span class="icon-chip">
        <svg viewBox="0 0 24 24"><path d="M22 9L12 4 2 9l10 5 10-5z"/><path d="M6 11.5V16c0 1.3 2.7 2.5 6 2.5s6-1.2 6-2.5v-4.5"/></svg>
      </span>
      <h3>Akademik &amp; Presensi</h3>
    </div>
    <p>Manajemen siswa per rombel tahun ajaran aktif, pencatatan presensi harian oleh guru kelas secara akurat.</p>
  </div>

  <div class="glass landing-feature reveal" data-reveal-delay="180">
    <img src="{{ asset('img/finance.svg') }}" alt="Keuangan" loading="lazy" style="width:100%;border-radius:14px;margin-bottom:6px">
    <div style="display:flex;align-items:center;gap:12px">
      <span class="icon-chip is-ink">
        <svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18"/><path d="M7 15h4"/></svg>
      </span>
      <h3>Keuangan &amp; SPP</h3>
    </div>
    <p>Generate tagihan massal per jenjang kelas, pencatatan pembayaran manual, dan kontrol rekapitulasi tunggakan.</p>
  </div>

  <div class="glass landing-feature reveal" data-reveal-delay="270">
    <img src="{{ asset('img/comms.svg') }}" alt="Komunikasi" loading="lazy" style="width:100%;border-radius:14px;margin-bottom:6px">
    <div style="display:flex;align-items:center;gap:12px">
      <span class="icon-chip is-ok">
        <svg viewBox="0 0 24 24"><path d="M4 11l12-6v14L4 13v-2z"/><path d="M16 8.5c2 .5 3 1.7 3 3.5s-1 3-3 3.5"/><path d="M7.5 14v4.5a1.5 1.5 0 003 0V15"/></svg>
      </span>
      <h3>Pusat Pengumuman</h3>
    </div>
    <p>Distribusi informasi edaran resmi ke seluruh sekolah atau kelas tertentu langsung di dashboard GTK.</p>
  </div>
</div>

{{-- Modules Section --}}
<section id="modul" style="position:relative;z-index:1;max-width:1200px;margin:16px auto 0;padding:20px 24px 80px">
  <div class="glass panel reveal">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:20px">
      <div>
        <h2 style="font-size:22px;font-weight:700">Modul Operasional Sekolah</h2>
        <p style="color:var(--muted);font-size:13.5px;margin-top:4px">Fondasi sistem informasi manajemen siap pakai untuk setiap institusi pendidikan.</p>
      </div>
      <span class="cs-pill" style="font-size:11px">Fase 1 Production</span>
    </div>

    <div class="stat-grid">
      <div class="glass landing-feature reveal" data-reveal-delay="0">
        <div style="display:flex;align-items:center;gap:12px">
          <span class="icon-chip icon-sm">
            <svg viewBox="0 0 24 24"><ellipse cx="12" cy="5.5" rx="8" ry="3"/><path d="M4 5.5v13c0 1.7 3.6 3 8 3s8-1.3 8-3v-13"/><path d="M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/></svg>
          </span>
          <h3 style="font-size:15px">Master Data</h3>
        </div>
        <p>Tahun ajaran aktif, struktur rombel, profil peserta didik, tenaga kependidikan (GTK), dan wali murid.</p>
      </div>

      <div class="glass landing-feature reveal" data-reveal-delay="90">
        <div style="display:flex;align-items:center;gap:12px">
          <span class="icon-chip icon-sm is-blue">
            <svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="17" rx="2.5"/><path d="M8 2.5v3M16 2.5v3M4 9.5h16"/><path d="M9 14.5l2 2 4-4"/></svg>
          </span>
          <h3 style="font-size:15px">Presensi Siswa</h3>
        </div>
        <p>Pencatatan kehadiran harian oleh guru kelas (Hadir, Izin, Sakit, Alpa) dengan ringkasan otomatis instan.</p>
      </div>

      <div class="glass landing-feature reveal" data-reveal-delay="180">
        <div style="display:flex;align-items:center;gap:12px">
          <span class="icon-chip icon-sm is-ink">
            <svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18"/></svg>
          </span>
          <h3 style="font-size:15px">Manajemen Tagihan</h3>
        </div>
        <p>Pembuatan tagihan massal per rombel dan pencatatan pembayaran siswa yang terdokumentasi rapi.</p>
      </div>

      <div class="glass landing-feature reveal" data-reveal-delay="270">
        <div style="display:flex;align-items:center;gap:12px">
          <span class="icon-chip icon-sm is-ok">
            <svg viewBox="0 0 24 24"><path d="M4 11l12-6v14L4 13v-2z"/><path d="M16 8.5c2 .5 3 1.7 3 3.5s-1 3-3 3.5"/></svg>
          </span>
          <h3 style="font-size:15px">Pengumuman GTK</h3>
        </div>
        <p>Distribusi informasi internal sekolah dan kelas, tersaji langsung di dashboard setiap staf dan guru.</p>
      </div>
    </div>

    <div style="margin-top:20px;padding:16px 20px;border-radius:var(--radius-sm);background:#f0f9ff;border:1px solid #bae6fd;display:flex;align-items:center;gap:14px;flex-wrap:wrap">
      <span class="badge badge-blue">Roadmap Lanjutan</span>
      <span style="font-size:13px;color:#334155;line-height:1.5">
        Payment Gateway otomatis (VA &amp; QRIS), Notifikasi WhatsApp Wali Murid, Presensi Selfie + GPS Geolocation, Tabungan Siswa, dan Payroll GTK.
      </span>
    </div>
  </div>
</section>

{{-- Footer --}}
<footer class="landing-foot">
  <div style="display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap">
    <a href="{{ route('landing') }}" class="brand-ruanggtk">
      <img src="{{ asset('img/logo.svg') }}" alt="" style="width:24px;height:24px;border-radius:7px;vertical-align:middle">
      <span class="brand-ruanggtk-text" style="font-size:16px">Ruang<span class="gtk-tag">GTK</span></span>
    </a>
    <span>© {{ date('Y') }} <b style="color:var(--text)">Vicky Koroh</b> — Hak Cipta Dilindungi · {{ $footerText }}</span>
  </div>
  @if ($footerPages->isNotEmpty())
    <div style="margin-top:10px;display:flex;gap:20px;justify-content:center;flex-wrap:wrap">
      @foreach ($footerPages as $fp)
        <a href="{{ route('page.show', $fp->slug) }}">{{ $fp->title }}</a>
      @endforeach
    </div>
  @endif
</footer>
@endsection
