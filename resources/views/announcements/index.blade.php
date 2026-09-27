@extends('layouts.app')
@section('title', 'Pengumuman')

@section('content')
<div class="page-head">
  <div>
    <h1>Pengumuman</h1>
    <div class="sub">Kirim informasi ke seluruh sekolah atau kelas tertentu.</div>
  </div>
</div>

<div class="two-col">
  <div class="glass panel">
    <h2 class="panel-title"><svg viewBox="0 0 24 24"><path d="M4 11l12-6v14L4 13v-2z"/><path d="M16 8.5c2 .5 3 1.7 3 3.5s-1 3-3 3.5"/></svg> Publikasikan</h2>
    <form method="POST" action="{{ route('announcements.store') }}" class="stack">
      @csrf
      <div class="field">
        <label>Judul *</label>
        <input name="title" class="input" required maxlength="150" placeholder="mis. Libur Idul Fitri">
      </div>
      <div class="field">
        <label>Isi *</label>
        <textarea name="content" class="textarea" required placeholder="Tulis isi pengumuman..."></textarea>
      </div>
      <div class="field">
        <label>Tujuan</label>
        <select name="class_id" class="select">
          <option value="">🏫 Seluruh sekolah</option>
          @foreach ($classes as $class)
            <option value="{{ $class->id }}">Kelas {{ $class->name }}</option>
          @endforeach
        </select>
      </div>
      <button class="btn btn-ink">Publikasikan</button>
    </form>
  </div>

  <div class="stack">
    @forelse ($announcements as $ann)
      <div class="glass ann-item">
        <h3>{{ $ann->title }}</h3>
        <p>{{ \Illuminate\Support\Str::limit($ann->content, 200) }}</p>
        <div class="ann-meta">
          <span class="badge badge-blue">{{ $ann->schoolClass?->name ?? 'Seluruh Sekolah' }}</span>
          <span>{{ $ann->published_at?->translatedFormat('l, d M Y — H:i') }}</span>
          <form method="POST" action="{{ route('announcements.destroy', $ann) }}" data-confirm="Hapus pengumuman ini?" style="margin-left:auto">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-danger">Hapus</button>
          </form>
        </div>
      </div>
    @empty
      <div class="glass panel empty">Belum ada pengumuman.</div>
    @endforelse
  </div>
</div>
@endsection
