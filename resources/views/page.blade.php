@extends('layouts.guest')
@section('title', $page->title)

@section('content')
<nav class="landing-nav glass-soft" aria-label="Navigasi">
  <a href="{{ route('landing') }}">← Beranda</a>
  <a href="{{ route('login') }}">Masuk</a>
</nav>

<article style="position:relative;z-index:1;max-width:760px;margin:0 auto;padding:110px 24px 60px">
  <div class="glass panel" style="animation:rise .6s cubic-bezier(.2,.7,.3,1) backwards">
    <h1 style="font-size:clamp(26px,4vw,38px);letter-spacing:-.03em;margin-bottom:14px">{{ $page->title }}</h1>
    <div class="ann-meta" style="margin-bottom:18px">
      <span class="badge badge-blue">{{ $page->updated_at->translatedFormat('d M Y') }}</span>
    </div>
    <div style="line-height:1.75;color:var(--text-2);font-size:15.5px">
      {!! nl2br(e($page->content)) !!}
    </div>
  </div>
</article>

<footer class="landing-foot">
  © {{ date('Y') }} <b style="color:var(--text-2)">Vicky Koroh</b> — Hak Cipta Dilindungi<br>
  @foreach ($footerPages as $fp)
    <a href="{{ route('page.show', $fp->slug) }}" style="margin:0 10px">{{ $fp->title }}</a>
  @endforeach
</footer>
@endsection
