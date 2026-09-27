@extends('layouts.guest')
@section('title', 'Sistem Informasi Manajemen Sekolah')

@section('content')
<nav class="landing-nav glass-soft" aria-label="Navigasi utama">
  <div style="display:flex;align-items:center;gap:8px;padding:0 8px">
    <img src="{{ asset('img/logo.svg') }}" alt="Logo" style="width:24px;height:24px;border-radius:7px">
    <b style="font-size:14px;color:var(--text)">Ruang GTK</b>
  </div>
  <a href="#fitur">Fitur</a>
  <a href="#modul">Modul</a>
  <a href="{{ route('login') }}" class="btn btn-sm btn-ink" style="height:32px;padding:0 16px;margin-left:4px">
    Masuk
  </a>
</nav>

<section class="landing-hero">
  <div>
    <div style="margin-bottom:18px">
      <span class="vtx-pill">
        <span class="dot"></span>
        Platform GTK &amp; SIM Sekolah Multi-Tenant
      </span>
    </div>

    <h1>
      Satu ruang cerdas untuk <br>
      <span class="vtx-gradient-text">seluruh sekolah &amp; GTK.</span>
    </h1>
    <p class="lede">{{ $tagline }}</p>

    <div class="landing-cta">
      <a href="{{ route('login') }}" class="btn btn-ink" style="height:48px;padding:0 28px;font-size:15px">
        <span>Masuk ke Aplikasi</span>
        <svg viewBox="0 0 24 24" style="width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </a>
      <a href="#fitur" class="btn" style="height:48px;padding:0 24px;font-size:15px">
        Jelajahi Fitur Utama
      </a>
    </div>

    <!-- Quick Metrics Strip -->
    <div style="display:flex;gap:24px;margin-top:40px;flex-wrap:wrap">
      <div>
        <b style="font-size:20px;color:#fff;display:block">100%</b>
        <small style="color:var(--muted);font-size:12px">Isolasi Data Subdomain</small>
      </div>
      <div style="width:1px;background:var(--line);height:32px;align-self:center"></div>
      <div>
        <b style="font-size:20px;color:var(--emerald);display:block">Real-time</b>
        <small style="color:var(--muted);font-size:12px">Presensi Siswa Harian</small>
      </div>
      <div style="width:1px;background:var(--line);height:32px;align-self:center"></div>
      <div>
        <b style="font-size:20px;color:var(--amber);display:block">Otomatis</b>
        <small style="color:var(--muted);font-size:12px">Rekap Tagihan &amp; SPP</small>
      </div>
    </div>
  </div>

  <div class="hero-visual">
    <img src="{{ asset($heroImage) }}" alt="Ilustrasi Command Center Ruang GTK" loading="eager">
  </div>
</section>

<section id="fitur" style="position:relative;z-index:1;padding:50px 0 10px">
  <div style="text-align:center;margin-bottom:12px">
    <span class="vtx-pill" style="font-size:11px">✨ Fitur Terintegrasi</span>
  </div>
  <h2 class="landing-section-title">Semua yang dibutuhkan sekolah.</h2>
  <p class="landing-section-sub">Satu ekosistem modern yang menggabungkan seluruh operasional pendidikan dan GTK.</p>
</section>

<div class="features-grid">
  <div class="glass landing-feature reveal" data-reveal-delay="0">
    <img src="{{ asset('img/tenant.svg') }}" alt="Multi-Tenant" loading="lazy" style="width:100%;border-radius:14px;margin-bottom:6px">
    <div style="display:flex;align-items:center;gap:12px">
      <span class="icon-chip is-blue">
        <svg viewBox="0 0 24 24"><path d="M12 3l9 5-9 5-9-5 9-5z"/><path d="M3 13l9 5 9-5"/><path d="M3 17l9 5 9-5"/></svg>
      </span>
      <h3>Multi-Tenant Cloud</h3>
    </div>
    <p>Setiap sekolah memiliki subdomain unik dengan isolasi database penuh — aman, mandiri, dan terlindungi.</p>
  </div>

  <div class="glass landing-feature reveal" data-reveal-delay="90">
    <img src="{{ asset('img/academic.svg') }}" alt="Akademik & Presensi" loading="lazy" style="width:100%;border-radius:14px;margin-bottom:6px">
    <div style="display:flex;align-items:center;gap:12px">
      <span class="icon-chip">
        <svg viewBox="0 0 24 24"><path d="M22 9L12 4 2 9l10 5 10-5z"/><path d="M6 11.5V16c0 1.3 2.7 2.5 6 2.5s6-1.2 6-2.5v-4.5"/></svg>
      </span>
      <h3>Akademik &amp; Presensi</h3>
    </div>
    <p>Manajemen siswa per tahun ajaran, presensi harian per kelas oleh guru kelas dengan status akurat.</p>
  </div>

  <div class="glass landing-feature reveal" data-reveal-delay="180">
    <img src="{{ asset('img/finance.svg') }}" alt="Keuangan" loading="lazy" style="width:100%;border-radius:14px;margin-bottom:6px">
    <div style="display:flex;align-items:center;gap:12px">
      <span class="icon-chip is-ink">
        <svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18"/><path d="M7 15h4"/></svg>
      </span>
      <h3>Keuangan &amp; SPP</h3>
    </div>
    <p>Generate tagihan massal per kelas, catat pembayaran manual, dan pantau status tunggakan secara transparan.</p>
  </div>

  <div class="glass landing-feature reveal" data-reveal-delay="270">
    <img src="{{ asset('img/comms.svg') }}" alt="Komunikasi" loading="lazy" style="width:100%;border-radius:14px;margin-bottom:6px">
    <div style="display:flex;align-items:center;gap:12px">
      <span class="icon-chip is-ok">
        <svg viewBox="0 0 24 24"><path d="M4 11l12-6v14L4 13v-2z"/><path d="M16 8.5c2 .5 3 1.7 3 3.5s-1 3-3 3.5"/><path d="M7.5 14v4.5a1.5 1.5 0 003 0V15"/></svg>
      </span>
      <h3>Pusat Pengumuman</h3>
    </div>
    <p>Kirim edaran penting ke seluruh sekolah atau ditujukan ke kelas tertentu langsung di dashboard GTK.</p>
  </div>
</div>

<section id="modul" style="position:relative;z-index:1;max-width:1200px;margin:16px auto 0;padding:20px 24px 80px">
  <div class="glass panel reveal">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:20px">
      <div>
        <h2 style="font-size:22px;font-weight:700">Modul Operasional Sekolah</h2>
        <p style="color:var(--muted);font-size:13.5px;margin-top:4px">Fondasi sistem informasi manajemen siap pakai untuk setiap institusi pendidikan.</p>
      </div>
      <span class="vtx-pill" style="font-size:11px">Fase 1 Production</span>
    </div>

    <div class="stat-grid">
      <div class="glass landing-feature reveal" data-reveal-delay="0">
        <div style="display:flex;align-items:center;gap:12px">
          <span class="icon-chip icon-sm">
            <svg viewBox="0 0 24 24"><ellipse cx="12" cy="5.5" rx="8" ry="3"/><path d="M4 5.5v13c0 1.7 3.6 3 8 3s8-1.3 8-3v-13"/><path d="M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/></svg>
          </span>
          <h3 style="font-size:15px">Master Data</h3>
        </div>
        <p>Tahun ajaran aktif, struktur kelas, profil siswa, tenaga kependidikan (GTK), dan wali murid.</p>
      </div>

      <div class="glass landing-feature reveal" data-reveal-delay="90">
        <div style="display:flex;align-items:center;gap:12px">
          <span class="icon-chip icon-sm is-blue">
            <svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="17" rx="2.5"/><path d="M8 2.5v3M16 2.5v3M4 9.5h16"/><path d="M9 14.5l2 2 4-4"/></svg>
          </span>
          <h3 style="font-size:15px">Presensi Siswa</h3>
        </div>
        <p>Pencatatan kehadiran harian oleh guru kelas (Hadir, Izin, Sakit, Alpa) dengan ringkasan instan.</p>
      </div>

      <div class="glass landing-feature reveal" data-reveal-delay="180">
        <div style="display:flex;align-items:center;gap:12px">
          <span class="icon-chip icon-sm is-ink">
            <svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18"/></svg>
          </span>
          <h3 style="font-size:15px">Manajemen Tagihan</h3>
        </div>
        <p>Pembuatan tagihan massal per jenjang kelas dan pencatatan pembayaran siswa yang terdokumentasi.</p>
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

    <div style="margin-top:20px;padding:16px 20px;border-radius:var(--radius-sm);background:rgba(255,255,255,0.03);border:1px solid var(--line-light);display:flex;align-items:center;gap:14px;flex-wrap:wrap">
      <span class="badge badge-blue">Roadmap Lanjutan</span>
      <span style="font-size:13px;color:var(--muted-2)">
        Payment Gateway otomatis (VA &amp; QRIS), Notifikasi WhatsApp Wali Murid, Presensi Selfie + Geolocation GPS, Tabungan Siswa, dan Payroll GTK.
      </span>
    </div>
  </div>
</section>

<footer class="landing-foot">
  <div style="display:flex;align-items:center;justify-content:center;gap:10px;flex-wrap:wrap">
    <img src="{{ asset('img/logo.svg') }}" alt="" style="width:24px;height:24px;border-radius:7px;vertical-align:middle">
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
