@extends('layouts.god')
@section('title', 'Halaman')

@section('content')
<div class="page-head">
  <div>
    <h1>Halaman Publik</h1>
    <div class="sub">Halaman muncul di {{ route('page.show', ['slug' => 'slug']) }} dan footer situs.</div>
  </div>
</div>

<div class="two-col">
  <div class="glass panel">
    <h2 class="panel-title"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg> Halaman Baru</h2>
    <form method="POST" action="{{ route('god.pages.store') }}" class="stack">
      @csrf
      <div class="field"><label>Judul *</label><input name="title" class="input" required></div>
      <div class="field"><label>Slug (kosongkan = otomatis)</label><input name="slug" class="input" placeholder="tentang-kami"></div>
      <div class="field"><label>Konten * (mendukung HTML)</label><textarea name="content" class="textarea" style="min-height:140px" required></textarea></div>
      <div class="form-grid" style="grid-template-columns:1fr 1fr">
        <div class="field">
          <label>Status</label>
          <select name="status" class="select">
            <option value="published">Published</option>
            <option value="draft">Draft</option>
          </select>
        </div>
        <div class="field">
          <label>Urutan Footer</label>
          <input name="sort_order" type="number" class="input" value="0" min="0">
        </div>
      </div>
      <label style="display:flex;gap:8px;align-items:center;font-size:14px;color:var(--text-2)">
        <input type="checkbox" name="show_in_footer" value="1" checked> Tampilkan di footer
      </label>
      <button class="btn btn-god">Buat Halaman</button>
    </form>
  </div>

  <div class="stack">
    @forelse ($pages as $page)
      <div class="glass ann-item">
        <div class="with-chip">
          <span class="icon-chip icon-sm {{ $page->status === 'published' ? 'is-ok' : '' }}">
            <svg viewBox="0 0 24 24"><path d="M6 2.5h9L19 7v14H6z"/><path d="M14 2.5V7h5"/></svg>
          </span>
          <div style="flex:1;min-width:0">
            <h3>{{ $page->title }}</h3>
            <div class="ann-meta">
              <span class="badge {{ $page->status === 'published' ? 'badge-ok' : 'badge-warn' }}">{{ $page->status }}</span>
              <span>/halaman/{{ $page->slug }}</span>
              @if ($page->show_in_footer)<span class="badge badge-blue">footer</span>@endif
            </div>
          </div>
          <button class="btn btn-sm" data-dialog="#edit-{{ $page->id }}">Edit</button>
          <form method="POST" action="{{ route('god.pages.destroy', $page) }}" data-confirm="Hapus halaman {{ $page->title }}?">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-danger">Hapus</button>
          </form>
        </div>
      </div>
    @empty
      <div class="glass panel empty">Belum ada halaman. Buat lewat form di samping.</div>
    @endforelse
  </div>
</div>

@foreach ($pages as $page)
<dialog class="dlg" id="edit-{{ $page->id }}">
  <h3>Edit — {{ $page->title }}</h3>
  <form method="POST" action="{{ route('god.pages.update', $page) }}" class="stack">
    @csrf @method('PUT')
    <div class="field"><label>Judul *</label><input name="title" class="input" value="{{ $page->title }}" required></div>
    <div class="field"><label>Slug *</label><input name="slug" class="input" value="{{ $page->slug }}" required></div>
    <div class="field"><label>Konten *</label><textarea name="content" class="textarea" style="min-height:140px" required>{{ $page->content }}</textarea></div>
    <div class="form-grid" style="grid-template-columns:1fr 1fr">
      <div class="field">
        <label>Status</label>
        <select name="status" class="select">
          <option value="published" {{ $page->status === 'published' ? 'selected' : '' }}>Published</option>
          <option value="draft" {{ $page->status === 'draft' ? 'selected' : '' }}>Draft</option>
        </select>
      </div>
      <div class="field"><label>Urutan</label><input name="sort_order" type="number" class="input" value="{{ $page->sort_order }}" min="0"></div>
    </div>
    <label style="display:flex;gap:8px;align-items:center;font-size:14px;color:var(--text-2)">
      <input type="checkbox" name="show_in_footer" value="1" {{ $page->show_in_footer ? 'checked' : '' }}> Tampilkan di footer
    </label>
    <div class="dlg-actions">
      <button type="button" class="btn" data-close>Batal</button>
      <button class="btn btn-god">Simpan</button>
    </div>
  </form>
</dialog>
@endforeach
@endsection
