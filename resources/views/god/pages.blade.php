@extends('layouts.god')
@section('title', 'Halaman Publik')

@section('content')
<div class="god-hero-strip" style="margin-bottom:20px">
  <div>
    <h1 class="god-greeting-title">Halaman Publik &amp; Legalitas</h1>
    <div class="god-greeting-sub">
      <span>Kelola halaman statis yang dapat diakses publik di <code>https://ruanggtk.my.id/halaman/{slug}</code> dan tautan footer situs.</span>
    </div>
  </div>
  <div>
    <button type="button" class="god-btn-primary-neo" data-dialog="#dialog-create-page">
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
      + Tambah Halaman Baru
    </button>
  </div>
</div>

{{-- Modern Neo-SaaS Card List --}}
<div class="god-recent-card-neo">
  <div class="god-recent-head">
    <div>
      <h2 class="god-recent-title">Daftar Halaman Publik</h2>
      <small style="color:#94a3b8;font-size:12.5px">Seluruh halaman aktif terindeks otomatis ke dalam <code>sitemap.xml</code> mesin pencari.</small>
    </div>
    <span class="god-badge-pill">{{ $pages->count() }} Halaman</span>
  </div>

  <div class="god-recent-list-wrap">
    @forelse ($pages as $page)
      <div class="god-recent-row-item">
        {{-- Avatar Icon --}}
        <div class="god-row-avatar" style="background:#ede9fe;color:#7c3aed">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        </div>

        {{-- Info Center --}}
        <div class="god-row-info">
          <div class="god-row-name-col" style="min-width:180px;max-width:240px">
            <strong title="{{ $page->title }}">{{ $page->title }}</strong>
            <small style="color:#7c3aed">/halaman/{{ $page->slug }}</small>
          </div>

          <div class="god-row-detail-col" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            <span class="badge {{ $page->status === 'published' ? 'badge-ok' : 'badge-warn' }}">
              {{ $page->status === 'published' ? '🟢 Published' : '🟡 Draft' }}
            </span>
            @if ($page->show_in_footer)
              <span class="badge badge-blue">Footer Link</span>
            @endif
            <span style="color:#94a3b8;font-size:12px">Urutan: <b>{{ $page->sort_order }}</b></span>
          </div>
        </div>

        {{-- Actions Right --}}
        <div style="display:flex;align-items:center;gap:6px;flex:none">
          <a href="{{ route('page.show', $page->slug) }}" target="_blank" class="btn btn-sm" title="Buka pratinjau halaman">
            👁️ Pratinjau
          </a>
          <button type="button" class="btn btn-sm" data-dialog="#edit-{{ $page->id }}" title="Edit judul dan isi konten">
            ✏️ Edit
          </button>
          <form method="POST" action="{{ route('god.pages.destroy', $page) }}" data-confirm="Hapus halaman '{{ $page->title }}'?" style="margin:0">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-danger">Hapus</button>
          </form>
        </div>
      </div>
    @empty
      <div style="padding:32px;text-align:center;color:#94a3b8">
        Belum ada halaman publik yang dibuat. Silakan tambahkan halaman baru seperti Profil, Ketentuan Layanan, atau Privasi.
      </div>
    @endforelse
  </div>
</div>

{{-- MODAL TAMBAH HALAMAN --}}
<dialog id="dialog-create-page" class="dlg" style="max-width:680px;width:95%">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
      <h3 style="margin:0;font-size:17px;color:#0f172a;display:flex;align-items:center;gap:8px">
        <svg viewBox="0 0 24 24" width="20" height="20" stroke="#7c3aed" fill="none" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
        Tambah Halaman Publik Baru
      </h3>
      <button type="button" data-close class="modal-close" aria-label="Tutup">✕</button>
    </div>
    <form method="POST" action="{{ route('god.pages.store') }}" class="stack">
      @csrf
      <div class="field">
        <label>Judul Halaman *</label>
        <input name="title" class="input" placeholder="mis. Kebijakan Privasi &amp; Keamanan" required>
      </div>
      <div class="field">
        <label>Slug URL (biarkan kosong untuk generate otomatis)</label>
        <input name="slug" class="input" placeholder="kebijakan-privasi">
      </div>
      <div class="field">
        <label>Konten Halaman * (Mendukung Teks / HTML)</label>
        <textarea name="content" class="textarea" style="min-height:160px;font-family:inherit" placeholder="Tuliskan isi halaman publik di sini..." required></textarea>
      </div>
      <div class="form-grid" style="grid-template-columns:1fr 1fr">
        <div class="field">
          <label>Status Publikasi</label>
          <select name="status" class="select">
            <option value="published">Published (Dapat diakses)</option>
            <option value="draft">Draft (Disembunyikan)</option>
          </select>
        </div>
        <div class="field">
          <label>Urutan Tampilan</label>
          <input name="sort_order" type="number" class="input" value="0" min="0">
        </div>
      </div>
      <div class="field">
        <label style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;font-weight:600">
          <input type="checkbox" name="show_in_footer" value="1" checked>
          Tampilkan link halaman ini di Footer landing page
        </label>
      </div>
      <div class="dlg-actions" style="margin-top:16px">
        <button type="button" class="btn" data-close>Batal</button>
        <button class="god-btn-primary-neo" data-loading="Menyimpan...">💾 Terbitkan Halaman</button>
      </div>
    </form>
  </div>
</dialog>

{{-- MODAL EDIT HALAMAN --}}
@foreach ($pages as $page)
<dialog class="dlg" id="edit-{{ $page->id }}" style="max-width:680px;width:95%">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
      <h3 style="margin:0;font-size:17px;color:#0f172a">Edit Halaman — {{ $page->title }}</h3>
      <button type="button" class="modal-close" data-close>✕</button>
    </div>
    <form method="POST" action="{{ route('god.pages.update', $page) }}" class="stack">
      @csrf @method('PUT')
      <div class="field">
        <label>Judul Halaman *</label>
        <input name="title" class="input" value="{{ $page->title }}" required>
      </div>
      <div class="field">
        <label>Slug URL *</label>
        <input name="slug" class="input" value="{{ $page->slug }}" required>
      </div>
      <div class="field">
        <label>Konten Halaman * (Mendukung Teks / HTML)</label>
        <textarea name="content" class="textarea" style="min-height:160px;font-family:inherit" required>{{ $page->content }}</textarea>
      </div>
      <div class="form-grid" style="grid-template-columns:1fr 1fr">
        <div class="field">
          <label>Status</label>
          <select name="status" class="select">
            <option value="published" {{ $page->status === 'published' ? 'selected' : '' }}>Published</option>
            <option value="draft" {{ $page->status === 'draft' ? 'selected' : '' }}>Draft</option>
          </select>
        </div>
        <div class="field">
          <label>Urutan</label>
          <input name="sort_order" type="number" class="input" value="{{ $page->sort_order }}" min="0">
        </div>
      </div>
      <div class="field">
        <label style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;font-weight:600">
          <input type="checkbox" name="show_in_footer" value="1" {{ $page->show_in_footer ? 'checked' : '' }}>
          Tampilkan link halaman ini di Footer landing page
        </label>
      </div>
      <div class="dlg-actions">
        <button type="button" class="btn" data-close>Batal</button>
        <button class="god-btn-primary-neo" data-loading="Menyimpan...">💾 Simpan Perubahan</button>
      </div>
    </form>
  </div>
</dialog>
@endforeach
@endsection
