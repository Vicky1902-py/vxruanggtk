@extends('layouts.god')
@section('title', 'Branding & CMS Situs')

@section('content')
<div class="god-hero-strip" style="margin-bottom:20px">
  <div>
    <h1 class="god-greeting-title">Branding, Logo &amp; CMS Situs</h1>
    <div class="god-greeting-sub">
      <span>Sesuaikan identitas brand, logo utama landing, favicon browser, hero visual, dan informasi kontak publik.</span>
    </div>
  </div>
  <div style="display:flex;align-items:center;gap:10px">
    <a href="{{ route('landing') }}" target="_blank" class="god-btn-secondary-neo">
      👁️ Lihat Situs Publik
    </a>
  </div>
</div>

<div class="god-main-grid">

  {{-- FORM PENGATURAN BRANDING & CMS --}}
  <div class="god-left-col">
    <div class="god-recent-card-neo">
      <div class="god-recent-head">
        <div>
          <h2 class="god-recent-title">Identitas Brand &amp; Konten Landing</h2>
          <small style="color:#94a3b8;font-size:12.5px">Pembaruan langsung diterapkan ke landing page, navbar, dan favicon browser.</small>
        </div>
      </div>

      <form method="POST" action="{{ route('god.cms.save') }}" enctype="multipart/form-data" class="stack" style="gap:16px;margin-top:8px">
        @csrf

        {{-- 1. Logo Utama & Favicon --}}
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:16px">
          <h4 style="margin:0 0 12px;font-size:14px;color:#0f172a;display:flex;align-items:center;gap:8px">
            🎨 Logo Utama &amp; Favicon Browser
          </h4>

          <div class="form-grid" style="grid-template-columns:1fr 1fr;gap:14px">
            {{-- Logo Utama --}}
            <div class="field">
              <label>Unggah Logo Utama Landing (PNG/SVG/WebP)</label>
              <input type="file" name="site_logo_file" class="input" accept="image/*" style="padding:6px 12px">
              <input type="text" name="site_logo" class="input" value="{{ $settings['site_logo'] }}" placeholder="atau masukkan URL/path logo" style="margin-top:6px;font-size:12px">
              <small style="color:#64748b;font-size:11.5px">Muncul di header navbar landing &amp; halaman publik.</small>
            </div>

            {{-- Favicon --}}
            <div class="field">
              <label>Unggah Favicon Browser (.ico, .png, .svg)</label>
              <input type="file" name="site_favicon_file" class="input" accept=".ico,image/*" style="padding:6px 12px">
              <input type="text" name="site_favicon" class="input" value="{{ $settings['site_favicon'] }}" placeholder="atau masukkan URL/path favicon" style="margin-top:6px;font-size:12px">
              <small style="color:#64748b;font-size:11.5px">Muncul pada tab browser pengguna.</small>
            </div>
          </div>
        </div>

        {{-- 2. Teks Brand & Headline --}}
        <div class="form-grid" style="grid-template-columns:1fr 1fr;gap:14px">
          <div class="field">
            <label>Nama Brand / Aplikasi *</label>
            <input name="site_name" class="input" value="{{ $settings['site_name'] }}" placeholder="Ruang GTK" required>
          </div>
          <div class="field">
            <label>Headline Utama Hero Landing *</label>
            <input name="site_headline" class="input" value="{{ $settings['site_headline'] }}" placeholder="Satu ruang cerdas untuk Ruang GTK & institusi Anda." required>
          </div>
        </div>

        {{-- 3. Tagline & Deskripsi Hero --}}
        <div class="field">
          <label>Tagline Deskripsi Hero *</label>
          <textarea name="site_tagline" class="textarea" style="min-height:90px" required>{{ $settings['site_tagline'] }}</textarea>
        </div>

        {{-- 4. Gambar Hero --}}
        <div class="field">
          <label>Unggah Gambar Hero Landing (PNG/SVG/WebP/JPG)</label>
          <input type="file" name="site_hero_file" class="input" accept="image/*" style="padding:6px 12px">
          <input type="text" name="site_hero_image" class="input" value="{{ $settings['site_hero_image'] }}" placeholder="atau masukkan URL/path gambar hero" style="margin-top:6px;font-size:12px">
        </div>

        {{-- 5. Footer & Kontak Publik --}}
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:16px">
          <h4 style="margin:0 0 12px;font-size:14px;color:#0f172a;display:flex;align-items:center;gap:8px">
            📞 Informasi Footer &amp; Kontak Publik
          </h4>

          <div class="field" style="margin-bottom:12px">
            <label>Teks Footer &amp; Hak Cipta *</label>
            <input name="site_footer_text" class="input" value="{{ $settings['site_footer_text'] }}" required>
          </div>

          <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr;gap:12px">
            <div class="field">
              <label>Email Kontak</label>
              <input name="contact_email" class="input" type="email" value="{{ $settings['contact_email'] }}">
            </div>
            <div class="field">
              <label>Nomor Telepon / WA</label>
              <input name="contact_phone" class="input" value="{{ $settings['contact_phone'] }}">
            </div>
            <div class="field">
              <label>Alamat / Lokasi</label>
              <input name="contact_address" class="input" value="{{ $settings['contact_address'] }}">
            </div>
          </div>
        </div>

        <div style="display:flex;justify-content:flex-end;margin-top:8px">
          <button type="submit" class="god-btn-primary-neo" data-loading="Menyimpan...">
            💾 Simpan Seluruh Pengaturan Branding
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- LIVE PRATINJAU BRANDING --}}
  <div class="god-right-col">
    <div class="god-formation-card">
      <div class="god-card-header-arrow">
        <div>
          <h3>Pratinjau Identitas Aktif</h3>
          <small>Visualisasi langsung di browser</small>
        </div>
      </div>

      {{-- Preview Logo & Favicon --}}
      <div style="display:flex;align-items:center;justify-content:space-around;padding:16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px">
        <div style="text-align:center">
          <small style="color:#64748b;display:block;margin-bottom:6px;font-weight:600">Logo Landing</small>
          <div style="width:64px;height:64px;margin:0 auto;display:flex;align-items:center;justify-content:center;background:#ffffff;border-radius:14px;border:1px solid #ddd6fe;padding:6px;box-shadow:0 4px 12px rgba(124,58,237,0.1)">
            <img src="{{ asset($settings['site_logo']) }}" alt="Logo" style="max-width:100%;max-height:100%;object-fit:contain">
          </div>
        </div>

        <div style="text-align:center">
          <small style="color:#64748b;display:block;margin-bottom:6px;font-weight:600">Favicon Browser</small>
          <div style="width:48px;height:48px;margin:0 auto;display:flex;align-items:center;justify-content:center;background:#ffffff;border-radius:12px;border:1px solid #e2e8f0;padding:6px">
            <img src="{{ asset($settings['site_favicon']) }}" alt="Favicon" style="max-width:100%;max-height:100%;object-fit:contain">
          </div>
        </div>
      </div>

      {{-- Preview Hero Visual --}}
      <div>
        <small style="color:#64748b;display:block;margin-bottom:6px;font-weight:600">Gambar Hero Visual Landing</small>
        <div style="border-radius:14px;overflow:hidden;border:1px solid #e2e8f0;background:#ffffff;padding:8px;text-align:center">
          <img src="{{ asset($settings['site_hero_image']) }}" alt="Hero Visual" style="width:100%;max-height:160px;object-fit:contain;border-radius:10px">
        </div>
      </div>

      {{-- Preview Headline & Tagline --}}
      <div style="background:#f5f3ff;border:1px solid #ddd6fe;border-radius:14px;padding:14px">
        <strong style="color:#7c3aed;font-size:13px;display:block;margin-bottom:4px">{{ $settings['site_headline'] }}</strong>
        <p style="font-size:12px;color:#475569;line-height:1.4">{{ $settings['site_tagline'] }}</p>
      </div>

      {{-- Preview Footer --}}
      <div style="font-size:11.5px;color:#64748b;text-align:center;padding-top:8px;border-top:1px solid #f1f5f9">
        {{ $settings['site_footer_text'] }} · © {{ date('Y') }}
      </div>
    </div>
  </div>

</div>
@endsection
