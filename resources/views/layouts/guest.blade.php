<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#0284c7">
<title>@yield('title', \App\Models\SiteSetting::get('seo_meta_title', 'Ruang GTK — Sistem Informasi Manajemen Sekolah')) — {{ \App\Models\SiteSetting::get('site_name', 'Ruang GTK') }}</title>

{{-- SEO Meta Tags --}}
@if ($metaDesc = \App\Models\SiteSetting::get('seo_meta_description'))
  <meta name="description" content="{{ $metaDesc }}">
@endif
@if ($metaKeys = \App\Models\SiteSetting::get('seo_meta_keywords'))
  <meta name="keywords" content="{{ $metaKeys }}">
@endif

{{-- Google Search Console Verification --}}
@if ($gsc = \App\Models\SiteSetting::get('google_site_verification'))
  @if (\Illuminate\Support\Str::startsWith($gsc, '<meta'))
    {!! $gsc !!}
  @else
    <meta name="google-site-verification" content="{{ $gsc }}">
  @endif
@endif

{{-- Dynamic Favicon Browser --}}
<link rel="icon" href="{{ asset(\App\Models\SiteSetting::get('site_favicon', 'img/logo.svg')) }}" type="image/svg+xml">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) }}">

{{-- Google Analytics GA4 (gtag.js) --}}
@if ($ga4 = \App\Models\SiteSetting::get('ga4_measurement_id'))
  <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4 }}"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{{ $ga4 }}');
  </script>
@endif

{{-- Google AdSense Auto-Ads --}}
@if (\App\Models\SiteSetting::get('adsense_enabled') && ($adsenseId = \App\Models\SiteSetting::get('adsense_client_id')))
  <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $adsenseId }}" crossorigin="anonymous"></script>
@endif

{{-- Custom Head Injection --}}
@if ($customHead = \App\Models\SiteSetting::get('custom_head_code'))
  {!! $customHead !!}
@endif
</head>
<body>
@yield('content')

{{-- Custom Footer Injection --}}
@if ($customFooter = \App\Models\SiteSetting::get('custom_footer_code'))
  {!! $customFooter !!}
@endif
<script src="{{ asset('js/app.js') }}?v={{ @filemtime(public_path('js/app.js')) }}"></script>
</body>
</html>
