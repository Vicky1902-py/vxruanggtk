@extends('layouts.app')
@section('title', 'Pusat Pengumuman')

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <span class="cs-pill" style="font-size:11px;padding:3px 12px"><span class="dot" style="background:var(--emerald);box-shadow:0 0 10px var(--emerald)"></span> Pusat Informasi</span>
      <span style="font-size:12px;color:var(--muted)">Komunikasi Internal GTK &amp; Warga Sekolah</span>
    </div>
    <h1>Pengumuman Sekolah</h1>
    <div class="sub">Warta resmi sekolah, edaran akademik, dan agenda kegiatan per rombel kelas.</div>
  </div>
</div>

@php $isAdmin = auth()->user()?->role?->name === 'admin'; @endphp

<div class="{{ $isAdmin ? 'two-col' : '' }}">
  {{-- Form Publikasi Pengumuman (Khusus Admin) --}}
  @if ($isAdmin)
  <div class="glass panel">
    <h2 class="panel-title">
      <svg viewBox="0 0 24 24"><path d="M4 11l12-6v14L4 13v-2z"/><path d="M16 8.5c2 .5 3 1.7 3 3.5s-1 3-3 3.5"/><path d="M7.5 14v4.5a1.5 1.5 0 003 0V15"/></svg>
      Buat Pengumuman Baru
    </h2>
    <form method="POST" action="{{ route('announcements.store') }}" class="stack">
      @csrf
      <div class="field">
        <label>Judul Pengumuman *</label>
        <input name="title" class="input" required maxlength="150" placeholder="mis. Jadwal Penilaian Akhir Semester">
      </div>

      <div class="field">
        <label>Isi Pesan / Edaran *</label>
        <textarea name="content" class="textarea" required placeholder="Tuliskan detail pengumuman yang akan dibaca oleh GTK, siswa, dan wali..." style="min-height:130px"></textarea>
      </div>

      <div class="field">
        <label>Target Sasaran Pengumuman</label>
        <select name="class_id" class="select">
          <option value="">🏫 Seluruh Warga Sekolah (Umum)</option>
          @foreach ($classes as $class)
            <option value="{{ $class->id }}">Khusus Kelas {{ $class->name }}</option>
          @endforeach
        </select>
      </div>

      <button class="btn btn-ink" data-loading="Menerbitkan pengumuman...">
        📢 Publikasikan Sekarang
      </button>
    </form>
  </div>
  @endif

  {{-- Feed Pengumuman --}}
  <div class="stack" style="{{ ! $isAdmin ? 'max-width:860px;margin:0 auto' : '' }}">
    @forelse ($announcements as $ann)
      <div class="glass ann-item">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px">
          <div>
            <span class="badge {{ $ann->schoolClass ? 'badge-blue' : 'badge-ok' }}" style="font-size:11px;margin-bottom:6px">
              {{ $ann->schoolClass ? 'Kelas ' . $ann->schoolClass->name : '🌐 Seluruh Sekolah' }}
            </span>
            <h3 style="color:var(--text);font-size:16px;margin-top:4px">{{ $ann->title }}</h3>
          </div>
          @if ($isAdmin)
          <form method="POST" action="{{ route('announcements.destroy', $ann) }}" data-confirm="Hapus pengumuman '{{ $ann->title }}'?" style="flex:none">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-danger" style="height:30px;padding:0 12px;font-size:12px">Hapus</button>
          </form>
          @endif
        </div>

        <p style="margin-top:6px;line-height:1.65;white-space:pre-line">{{ $ann->content }}</p>

        <div class="ann-meta" style="margin-top:8px;padding-top:10px;border-top:1px solid var(--line-light)">
          <span>🕒 Diterbitkan: {{ $ann->published_at?->translatedFormat('l, d F Y — H:i') }} WIB</span>
        </div>
      </div>
    @empty
      <div class="glass panel empty">
        <p>Belum ada pengumuman yang aktif diterbitkan.</p>
      </div>
    @endforelse
  </div>
</div>
@endsection
