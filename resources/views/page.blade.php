@extends('layouts.guest')
@section('title', $page->title)

@section('content')
<nav class="landing-nav glass-soft" aria-label="Navigasi">
  <a href="{{ route('landing') }}" class="brand-ruanggtk" style="padding:0 8px">
    <img src="{{ asset('img/logo.svg') }}" alt="Logo" style="width:24px;height:24px;border-radius:7px">
    <span class="brand-ruanggtk-text" style="font-size:15px">Ruang<span class="gtk-tag">GTK</span></span>
    <span class="brand-beam"></span>
  </a>
  <a href="{{ route('landing') }}">← Beranda</a>
  <a href="{{ route('login') }}" class="btn btn-sm btn-ink" style="height:32px;padding:0 16px;margin-left:4px">Masuk</a>
</nav>

<article style="position:relative;z-index:1;max-width:820px;margin:0 auto;padding:115px 24px 60px">
  <div class="glass panel">
    <div style="margin-bottom:12px">
      <span class="cs-pill" style="font-size:11px"><span class="dot"></span> Halaman Publik</span>
    </div>
    <h1 style="font-size:clamp(26px,4vw,38px);letter-spacing:-.035em;margin-bottom:14px;color:#ffffff">{{ $page->title }}</h1>
    <div class="ann-meta" style="margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid var(--line-light)">
      <span class="badge badge-blue">Diperbarui: {{ $page->updated_at->translatedFormat('d F Y') }}</span>
    </div>
    <div style="line-height:1.8;color:var(--text-2);font-size:15.5px">
      {!! nl2br(e($page->content)) !!}
    </div>
  </div>
</article>

<footer class="landing-foot">
  <div style="display:flex;align-items:center;justify-content:center;gap:10px;flex-wrap:wrap">
    <a href="{{ route('landing') }}" class="brand-ruanggtk">
      <img src="{{ asset('img/logo.svg') }}" alt="" style="width:22px;height:22px;border-radius:6px;vertical-align:middle">
      <span class="brand-ruanggtk-text" style="font-size:15px">Ruang<span class="gtk-tag">GTK</span></span>
    </a>
    <span>© {{ date('Y') }} <b style="color:var(--text)">Vicky Koroh</b> — Hak Cipta Dilindungi</span>
  </div>
  @if ($footerPages->isNotEmpty())
    <div style="margin-top:10px;display:flex;gap:16px;justify-content:center;flex-wrap:wrap">
      @foreach ($footerPages as $fp)
        <a href="{{ route('page.show', $fp->slug) }}">{{ $fp->title }}</a>
      @endforeach
    </div>
  @endif
</footer>
@endsection
