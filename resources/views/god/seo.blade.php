@extends('layouts.god')
@section('title', 'SEO & Google AdSense')

@section('content')
<div class="god-hero-strip" style="margin-bottom:20px">
  <div>
    <h1 class="god-greeting-title">SEO, Google Search Console &amp; AdSense</h1>
    <div class="god-greeting-sub">
      <span>Konfigurasi optimasi mesin pencari, verifikasi Google Search Console, Google Analytics GA4, iklan AdSense &amp; berkas ads.txt.</span>
    </div>
  </div>
  <div style="display:flex;align-items:center;gap:10px">
    <a href="{{ url('/sitemap.xml') }}" target="_blank" class="god-btn-secondary-neo" title="Buka file XML Sitemap">
      🗺️ Buka sitemap.xml
    </a>
    <a href="{{ url('/ads.txt') }}" target="_blank" class="god-btn-secondary-neo" title="Buka berkas ads.txt">
      📄 Buka ads.txt
    </a>
  </div>
</div>

<form method="POST" action="{{ route('god.seo.save') }}">
  @csrf

  <div class="god-main-grid">

    {{-- KOLOM KIRI: GOOGLE SEARCH CONSOLE & ADSENSE --}}
    <div class="god-left-col">

      {{-- 1. GOOGLE SEARCH CONSOLE & ANALYTICS --}}
      <div class="god-recent-card-neo">
        <div class="god-recent-head">
          <div>
            <h2 class="god-recent-title">🔍 Google Search Console &amp; Analytics</h2>
            <small style="color:#94a3b8;font-size:12.5px">Verifikasi kepemilikan domain di Google Search Console dan lacak pengunjung via Google Analytics.</small>
          </div>
          <span class="badge badge-ok">SEO Ready</span>
        </div>

        <div class="stack" style="gap:14px;margin-top:8px">
          <div class="field">
            <label>Kode Verifikasi Google Search Console (HTML Tag atau Token)</label>
            <input name="google_site_verification" class="input" value="{{ $settings['google_site_verification'] }}" placeholder="mis. vqZFN9IGSVSgQCrnK0thI35F6NdxLCMTZk atau tag <meta name='google-site-verification' content='...'>">
            <small style="color:#64748b;font-size:11.5px">Sistem otomatis menyuntikkan tag <code>&lt;meta name="google-site-verification"&gt;</code> ke dalam tag <code>&lt;head&gt;</code> situs publik.</small>
          </div>

          <div class="field">
            <label>Google Analytics 4 (GA4) Measurement ID</label>
            <input name="ga4_measurement_id" class="input" value="{{ $settings['ga4_measurement_id'] }}" placeholder="mis. G-XXXXXXXXXX">
            <small style="color:#64748b;font-size:11.5px">Script Google Tag (gtag.js) akan dimuat otomatis di landing page &amp; halaman publik.</small>
          </div>

          <div class="field">
            <label>Meta Title SEO Landing Page</label>
            <input name="seo_meta_title" class="input" value="{{ $settings['seo_meta_title'] }}" placeholder="Judul yang muncul di hasil pencarian Google">
          </div>

          <div class="field">
            <label>Meta Description SEO</label>
            <textarea name="seo_meta_description" class="textarea" style="min-height:80px" placeholder="Deskripsi ringkas yang tampil pada snippet hasil pencarian Google">{{ $settings['seo_meta_description'] }}</textarea>
          </div>

          <div class="field">
            <label>Meta Keywords SEO (Pisahkan dengan koma)</label>
            <input name="seo_meta_keywords" class="input" value="{{ $settings['seo_meta_keywords'] }}" placeholder="ruang gtk, aplikasi sekolah, sim sekolah, spp sekolah">
          </div>
        </div>
      </div>

      {{-- 2. GOOGLE ADSENSE & MONETISASI --}}
      <div class="god-recent-card-neo">
        <div class="god-recent-head">
          <div>
            <h2 class="god-recent-title">💰 Google AdSense &amp; Iklan Mandiri</h2>
            <small style="color:#94a3b8;font-size:12.5px">Pasang iklan Google AdSense resmi, auto-ads, dan kelola isi berkas ads.txt.</small>
          </div>
          <span class="badge {{ $settings['adsense_enabled'] ? 'badge-ok' : 'badge-warn' }}">
            {{ $settings['adsense_enabled'] ? '🟢 AdSense Aktif' : '⚪ AdSense Nonaktif' }}
          </span>
        </div>

        <div class="stack" style="gap:14px;margin-top:8px">
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px">
            <label style="display:inline-flex;align-items:center;gap:10px;cursor:pointer;font-weight:700;color:#0f172a;font-size:13.5px">
              <input type="checkbox" name="adsense_enabled" value="1" {{ $settings['adsense_enabled'] ? 'checked' : '' }} style="width:16px;height:16px;accent-color:#7c3aed">
              Aktifkan Google AdSense pada Platform
            </label>
            <small style="color:#64748b;display:block;margin-top:4px;margin-left:26px;font-size:12px">
              Saat dicentang, script penayangan iklan akan disuntikkan secara otomatis ke halaman publik.
            </small>
          </div>

          <div class="form-grid" style="grid-template-columns:1fr 1fr;gap:14px">
            <div class="field">
              <label>AdSense Publisher / Client ID *</label>
              <input name="adsense_client_id" class="input" value="{{ $settings['adsense_client_id'] }}" placeholder="ca-pub-1234567890123456">
              <small style="color:#64748b;font-size:11.5px">Ditemukan di dashboard AdSense Anda (Akun &gt; Setelan Akun).</small>
            </div>

            <div class="field">
              <label>Auto-Ads (Iklan Otomatis Google)</label>
              <div style="height:42px;display:flex;align-items:center">
                <label style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;font-weight:600;font-size:13px">
                  <input type="checkbox" name="adsense_auto_ads" value="1" {{ $settings['adsense_auto_ads'] ? 'checked' : '' }} style="accent-color:#7c3aed">
                  Izinkan Google menempatkan Auto Ads secara otomatis
                </label>
              </div>
            </div>
          </div>

          <div class="field">
            <label>Pengaturan Berkas <code>ads.txt</code> (Root Domain)</label>
            <textarea name="ads_txt" class="textarea" style="min-height:90px;font-family:monospace;font-size:12px" placeholder="google.com, pub-XXXXXXXXXXXXXXXX, DIRECT, f08c47fec0942fa0">{{ $settings['ads_txt'] }}</textarea>
            <small style="color:#64748b;font-size:11.5px">
              Konten ini langsung disajikan sebagai file teks murni di <a href="{{ url('/ads.txt') }}" target="_blank" style="color:#7c3aed;font-weight:600">https://ruanggtk.my.id/ads.txt</a> sesuai standar IAB / Google Crawler.
            </small>
          </div>

          <div class="field">
            <label>Kode Unit Iklan Manual (Banner / In-Article)</label>
            <textarea name="adsense_banner_code" class="textarea" style="min-height:90px;font-family:monospace;font-size:12px" placeholder="<ins class='adsbygoogle' ...></ins>">{{ $settings['adsense_banner_code'] }}</textarea>
            <small style="color:#64748b;font-size:11.5px">Opsional: Masukkan script banner AdSense responsif untuk ditempatkan di landing page.</small>
          </div>
        </div>
      </div>

    </div>

    {{-- KOLOM KANAN: STATUS SITEMAP & CUSTOM CODE INJECTION --}}
    <div class="god-right-col">

      {{-- SITEMAP XML STATUS CARD --}}
      <div class="god-formation-card">
        <div class="god-card-header-arrow">
          <div>
            <h3>Sitemap XML &amp; Indexing</h3>
            <small>Peta situs otomatis untuk Google Bot</small>
          </div>
        </div>

        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:14px">
          <div style="display:flex;align-items:center;gap:8px;color:#15803d;font-weight:700;font-size:13px;margin-bottom:4px">
            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#16a34a"></span>
            Dynamic Sitemap.xml Siap
          </div>
          <p style="font-size:12px;color:#166534;margin:0;line-height:1.4">
            Sitemap memperbarui dirinya secara otomatis setiap kali halaman baru diterbitkan atau tenant sekolah bertambah.
          </p>
        </div>

        <div class="god-spec-table">
          <div class="god-spec-row">
            <span class="god-spec-label">URL Sitemap</span>
            <span class="god-spec-val" style="font-size:11.5px"><a href="{{ url('/sitemap.xml') }}" target="_blank" style="color:#7c3aed">/sitemap.xml</a></span>
          </div>
          <div class="god-spec-row">
            <span class="god-spec-label">Format Output</span>
            <span class="god-spec-val">XML 0.9 Protocol</span>
          </div>
          <div class="god-spec-row">
            <span class="god-spec-label">Frekuensi Pembaruan</span>
            <span class="god-spec-val">Daily (Harian)</span>
          </div>
        </div>

        <a href="{{ url('/sitemap.xml') }}" target="_blank" class="god-btn-pill-full">
          🗺️ Periksa Berkas Sitemap.xml
        </a>
      </div>

      {{-- CUSTOM CODE INJECTION (HEAD & FOOTER) --}}
      <div class="god-formation-card">
        <div class="god-card-header-arrow">
          <div>
            <h3>Injeksi Kode Kustom</h3>
            <small>Skrip pelacakan &amp; verifikasi pihak ketiga</small>
          </div>
        </div>

        <div class="field">
          <label>Kode Kustom Dalam <code>&lt;head&gt;</code></label>
          <textarea name="custom_head_code" class="textarea" style="min-height:90px;font-family:monospace;font-size:11.5px" placeholder="<script>...</script> atau <meta ...>">{{ $settings['custom_head_code'] }}</textarea>
          <small style="color:#64748b;font-size:11px">Mis. Facebook Pixel, TikTok Pixel, atau Webmaster meta tag lainnya.</small>
        </div>

        <div class="field">
          <label>Kode Kustom Sebelum <code>&lt;/body&gt;</code></label>
          <textarea name="custom_footer_code" class="textarea" style="min-height:90px;font-family:monospace;font-size:11.5px" placeholder="<script>...</script>">{{ $settings['custom_footer_code'] }}</textarea>
          <small style="color:#64748b;font-size:11px">Mis. Live Chat widget (Tawk.to, Crisp, dll).</small>
        </div>

        <button type="submit" class="god-btn-primary-neo" style="width:100%;justify-content:center;margin-top:6px" data-loading="Menyimpan...">
          💾 Simpan Semua Pengaturan SEO &amp; AdSense
        </button>
      </div>

    </div>

  </div>
</form>
@endsection
