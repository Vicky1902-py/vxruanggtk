@extends('layouts.god')
@section('title', 'Halaman')

@section('content')
<div class="page-head">
  <div>
    <h1>Halaman Publik</h1>
    <div class="sub">Halaman muncul di {{ route('page.show', ['slug' => 'slug']) }} dan footer situs.</div>
  </div>
  <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
    <button type="button" class="btn btn-sm btn-god" data-dialog="#dialog-create-page">
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
      + Tambah Halaman Baru
    </button>
  </div>
</div>

{{-- Daftar Halaman Full Width --}}
<div class="stack" style="margin-top:16px">
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
        <div class="actions">
          <button class="btn btn-sm" data-dialog="#edit-{{ $page->id }}">Edit</button>
          <form method="POST" action="{{ route('god.pages.destroy', $page) }}" data-confirm="Hapus halaman {{ $page->title }}?">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-danger">Hapus</button>
          </form>
        </div>
      </div>
    </div>
  @empty
    <div class="glass panel empty">Belum ada halaman publik dibuat.</div>
  @endforelse
</div>

{{-- MODAL TAMBAH HALAMAN --}}
<dialog id="dialog-create-page" class="modal glass">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
      <h3 style="margin:0;font-size:17px;color:var(--text);display:flex;align-items:center;gap:8px">
        <svg viewBox="0 0 24 24" style="width:20px;height:20px;stroke:var(--accent);fill:none;stroke-width:2"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
        Tambah Halaman Publik Baru
      </h3>
      <button type="button" data-close class="modal-close" aria-label="Tutup">✕</button>
    </div>
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
      <div class="modal-actions" style="margin-top:16px">
        <button type="button" class="btn" data-close>Batal</button>
        <button class="btn btn-god">Buat Halaman</button>
      </div>
    </form>
  </div>
</dialog>

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
