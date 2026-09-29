@extends('layouts.app')
@section('title', 'Pusat Pengumuman')

@section('content')
@php $isAdmin = auth()->user()?->role?->name === 'admin'; @endphp
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <span class="cs-pill" style="font-size:11px;padding:3px 12px"><span class="dot" style="background:var(--emerald);box-shadow:0 0 10px var(--emerald)"></span> Pusat Informasi</span>
      <span style="font-size:12px;color:var(--muted)">Komunikasi Internal GTK &amp; Warga Sekolah</span>
    </div>
    <h1>Pengumuman Sekolah</h1>
    <div class="sub">Warta resmi sekolah, edaran akademik, dan agenda kegiatan per rombel kelas.</div>
  </div>
  <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
    @if ($isAdmin)
      <button type="button" class="btn btn-sm btn-ink" data-dialog="#dialog-create-announcement">
        <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><path d="M4 11l12-6v14L4 13v-2z"/><path d="M16 8.5c2 .5 3 1.7 3 3.5s-1 3-3 3.5"/><path d="M7.5 14v4.5a1.5 1.5 0 003 0V15"/></svg>
        + Buat Pengumuman Baru
      </button>
    @endif
  </div>
</div>

{{-- Feed Pengumuman Full Width --}}
<div class="stack" style="margin-top:16px">
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

      <p style="margin-top:8px;line-height:1.7;white-space:pre-line;color:var(--text)">{{ $ann->content }}</p>

      <div class="ann-meta" style="margin-top:10px;padding-top:10px;border-top:1px solid var(--line-light)">
        <span>🕒 Diterbitkan: {{ $ann->published_at?->translatedFormat('l, d F Y — H:i') }} WIB</span>
      </div>
    </div>
  @empty
    <div class="glass panel empty">
      <p>Belum ada pengumuman yang aktif diterbitkan.</p>
    </div>
  @endforelse
</div>

@if ($isAdmin)
{{-- MODAL BUAT PENGUMUMAN BARU --}}
<dialog id="dialog-create-announcement" class="modal glass">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
      <h3 style="margin:0;font-size:17px;color:var(--text);display:flex;align-items:center;gap:8px">
        <svg viewBox="0 0 24 24" style="width:20px;height:20px;stroke:var(--accent);fill:none;stroke-width:2"><path d="M4 11l12-6v14L4 13v-2z"/><path d="M16 8.5c2 .5 3 1.7 3 3.5s-1 3-3 3.5"/><path d="M7.5 14v4.5a1.5 1.5 0 003 0V15"/></svg>
        Buat Pengumuman Baru
      </h3>
      <button type="button" data-close class="modal-close" aria-label="Tutup">✕</button>
    </div>
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

      <div class="modal-actions" style="margin-top:16px">
        <button type="button" class="btn" data-close>Batal</button>
        <button class="btn btn-ink" data-loading="Menerbitkan pengumuman...">
          📢 Publikasikan Sekarang
        </button>
      </div>
    </form>
  </div>
</dialog>
@endif
@endsection
