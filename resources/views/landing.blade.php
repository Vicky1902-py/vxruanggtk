@extends('layouts.guest')
@section('title', 'Sistem Informasi Manajemen Sekolah')

@section('content')
<nav class="landing-nav glass-soft" aria-label="Navigasi utama">
  <a href="#fitur">Fitur</a>
  <a href="#modul">Modul</a>
  <a href="{{ route('login') }}">Masuk</a>
</nav>

<section class="landing-hero">
  <div>
    <h1>Satu ruang untuk<br>seluruh sekolah.<span class="ghost" aria-hidden="true"><br>GTK.</span></h1>
    <p class="lede">{{ $tagline }}</p>
    <div class="landing-cta">
      <a href="{{ route('login') }}" class="btn btn-ink">
        Masuk ke Aplikasi
        <svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </a>
      <a href="#fitur" class="btn">Lihat Fitur</a>
    </div>
  </div>

  <div class="hero-visual">
    <div class="hv-ring"></div>
    <img src="{{ asset($heroImage) }}" alt="Ilustrasi dashboard Ruang GTK"
      style="position:absolute;inset:8%;width:84%;height:84%;object-fit:contain;filter:drop-shadow(0 24px 50px rgba(0,0,0,.45));animation:breathe 7s ease-in-out infinite,rise .8s cubic-bezier(.2,.7,.3,1) .25s backwards">
  </div>
</section>

<section id="fitur" style="position:relative;z-index:1;padding:40px 0 8px">
  <h2 class="landing-section-title">Semua yang dibutuhkan sekolah.</h2>
  <p class="landing-section-sub">Satu sistem terintegrasi untuk seluruh operasional pendidikan.</p>
</section>

<div class="features-grid">
  <div class="glass landing-feature reveal" data-reveal-delay="0">
    <img src="{{ asset('img/tenant.svg') }}" alt="" loading="lazy" style="width:100%;border-radius:14px;margin-bottom:6px">
    <div class="feature-top">
      <span class="icon-chip is-blue"><svg viewBox="0 0 24 24"><path d="M12 3l9 5-9 5-9-5 9-5z"/><path d="M3 13l9 5 9-5"/><path d="M3 17l9 5 9-5"/></svg></span>
      <h3>Multi-Tenant</h3>
    </div>
    <p>Setiap sekolah punya subdomain dan datanya terisolasi penuh — aman dan rapi.</p>
  </div>
  <div class="glass landing-feature reveal" data-reveal-delay="90">
    <img src="{{ asset('img/academic.svg') }}" alt="" loading="lazy" style="width:100%;border-radius:14px;margin-bottom:6px">
    <div class="feature-top">
      <span class="icon-chip"><svg viewBox="0 0 24 24"><path d="M22 9L12 4 2 9l10 5 10-5z"/><path d="M6 11.5V16c0 1.3 2.7 2.5 6 2.5s6-1.2 6-2.5v-4.5"/></svg></span>
      <h3>Akademik</h3>
    </div>
    <p>Manajemen data siswa per tahun ajaran, presensi harian per kelas oleh guru.</p>
  </div>
  <div class="glass landing-feature reveal" data-reveal-delay="180">
    <img src="{{ asset('img/finance.svg') }}" alt="" loading="lazy" style="width:100%;border-radius:14px;margin-bottom:6px">
    <div class="feature-top">
      <span class="icon-chip is-ink"><svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18"/><path d="M7 15h4"/></svg></span>
      <h3>Keuangan</h3>
    </div>
    <p>Generate tagihan massal per kelas, catat pembayaran, pantau tunggakan.</p>
  </div>
  <div class="glass landing-feature reveal" data-reveal-delay="270">
    <img src="{{ asset('img/comms.svg') }}" alt="" loading="lazy" style="width:100%;border-radius:14px;margin-bottom:6px">
    <div class="feature-top">
      <span class="icon-chip is-ok"><svg viewBox="0 0 24 24"><path d="M4 11l12-6v14L4 13v-2z"/><path d="M16 8.5c2 .5 3 1.7 3 3.5s-1 3-3 3.5"/><path d="M7.5 14v4.5a1.5 1.5 0 003 0V15"/></svg></span>
      <h3>Komunikasi</h3>
    </div>
    <p>Kirim pengumuman ke seluruh sekolah atau ke kelas tertentu saja.</p>
  </div>
</div>

<section id="modul" style="position:relative;z-index:1;max-width:1080px;margin:14px auto 0;padding:20px 24px 70px">
  <div class="glass panel reveal">
    <h2 class="panel-title" style="font-size:19px">Modul Fase 1 (MVP)</h2>
    <div class="stat-grid">
      <div class="glass landing-feature reveal" data-reveal-delay="0">
        <div class="feature-top">
          <span class="icon-chip icon-sm"><svg viewBox="0 0 24 24"><ellipse cx="12" cy="5.5" rx="8" ry="3"/><path d="M4 5.5v13c0 1.7 3.6 3 8 3s8-1.3 8-3v-13"/><path d="M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/></svg></span>
          <h3>Master Data</h3>
        </div>
        <p>Tahun ajaran, kelas, siswa, pegawai, wali murid.</p>
      </div>
      <div class="glass landing-feature reveal" data-reveal-delay="90">
        <div class="feature-top">
          <span class="icon-chip icon-sm is-blue"><svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="17" rx="2.5"/><path d="M8 2.5v3M16 2.5v3M4 9.5h16"/><path d="M9 14.5l2 2 4-4"/></svg></span>
          <h3>Presensi</h3>
        </div>
        <p>Kehadiran siswa harian per kelas dengan status lengkap.</p>
      </div>
      <div class="glass landing-feature reveal" data-reveal-delay="180">
        <div class="feature-top">
          <span class="icon-chip icon-sm is-ink"><svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18"/></svg></span>
          <h3>Tagihan</h3>
        </div>
        <p>SPP &amp; jenis tagihan lain, pembayaran manual tercatat rapi.</p>
      </div>
      <div class="glass landing-feature reveal" data-reveal-delay="270">
        <div class="feature-top">
          <span class="icon-chip icon-sm is-ok"><svg viewBox="0 0 24 24"><path d="M4 11l12-6v14L4 13v-2z"/><path d="M16 8.5c2 .5 3 1.7 3 3.5s-1 3-3 3.5"/></svg></span>
          <h3>Pengumuman</h3>
        </div>
        <p>Informasi sekolah &amp; kelas, tersaji di dashboard semua pihak.</p>
      </div>
    </div>
    <p style="margin-top:14px;font-size:13px;color:var(--muted-2)">
      Roadmap Fase 2–3: payment gateway (VA/QRIS), notifikasi WhatsApp, presensi selfie+GPS,
      tabungan, payroll, RAPBS, jurnal akuntansi, aplikasi wali murid.
    </p>
  </div>
</section>

<footer class="landing-foot">
  <div style="display:flex;align-items:center;justify-content:center;gap:8px;flex-wrap:wrap">
    <img src="{{ asset('img/logo.svg') }}" alt="" style="width:22px;height:22px;border-radius:6px;vertical-align:middle">
    <span>© {{ date('Y') }} <b style="color:var(--text-2)">Vicky Koroh</b> — Hak Cipta Dilindungi · {{ $footerText }}</span>
  </div>
  @if ($footerPages->isNotEmpty())
    <div style="margin-top:8px;display:flex;gap:16px;justify-content:center;flex-wrap:wrap">
      @foreach ($footerPages as $fp)
        <a href="{{ route('page.show', $fp->slug) }}">{{ $fp->title }}</a>
      @endforeach
    </div>
  @endif
</footer>
@endsection
