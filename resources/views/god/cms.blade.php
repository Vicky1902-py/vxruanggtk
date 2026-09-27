@extends('layouts.god')
@section('title', 'CMS Situs')

@section('content')
<div class="page-head">
  <div>
    <h1>CMS Situs</h1>
    <div class="sub">Kelola konten landing page dan halaman publik Ruang GTK.</div>
  </div>
  <a href="{{ route('landing') }}" target="_blank" class="btn btn-sm">👁️ Lihat Situs</a>
</div>

<div class="two-col">
  <div class="glass panel">
    <h2 class="panel-title"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="14" rx="2.5"/><path d="M3 9h18"/></svg> Pengaturan Landing</h2>
    <form method="POST" action="{{ route('god.cms.save') }}" class="stack">
      @csrf
      <div class="field">
        <label>Tagline Hero</label>
        <textarea name="site_tagline" class="textarea" required>{{ $settings['site_tagline'] }}</textarea>
      </div>
      <div class="field">
        <label>Gambar Hero (path/URL)</label>
        <input name="site_hero_image" class="input" value="{{ $settings['site_hero_image'] }}" placeholder="img/hero.svg">
      </div>
      <div class="field">
        <label>Teks Footer</label>
        <input name="site_footer_text" class="input" value="{{ $settings['site_footer_text'] }}">
      </div>
      <button class="btn btn-god" data-loading="Menyimpan Pengaturan...">💾 Simpan Pengaturan</button>
    </form>
  </div>

  <div class="glass panel">
    <h2 class="panel-title"><svg viewBox="0 0 24 24"><path d="M12 3l9 5-9 5-9-5 9-5z"/></svg> Pratinjau</h2>
    <div class="stack">
      <div class="glass-soft ann-item">
        <h3>Tagline saat ini</h3>
        <p>{{ $settings['site_tagline'] }}</p>
      </div>
      <div class="glass-soft ann-item" style="justify-items:center">
        <h3>Gambar hero</h3>
        <img src="{{ asset($settings['site_hero_image']) }}" alt="Pratinjau hero" style="max-width:100%;border-radius:14px;border:1px solid var(--line)">
      </div>
      <div class="glass-soft ann-item">
        <h3>Footer</h3>
        <p>{{ $settings['site_footer_text'] }} · © {{ date('Y') }}</p>
      </div>
    </div>
  </div>
</div>
@endsection
