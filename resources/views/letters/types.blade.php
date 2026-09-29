@extends('layouts.app')
@section('title', 'Format & Jenis Persuratan')

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <a href="{{ route('letters.index') }}" style="color:var(--muted);text-decoration:none;font-size:12px;display:flex;align-items:center;gap:4px">
        ← Kembali ke Buku Agenda
      </a>
      <span class="cs-pill" style="font-size:11px;padding:3px 12px"><span class="dot" style="background:#0284c7"></span> Konfigurasi Format</span>
    </div>
    <h1>Format Penomoran &amp; Jenis Surat</h1>
    <div class="sub">Atur pola penomoran dinas, kode klasifikasi kearsipan, dan draf template bawaan untuk setiap jenis surat.</div>
  </div>
  <div>
    <button type="button" class="btn btn-sm btn-ink" data-dialog="#modal-add-type" style="display:flex;align-items:center;gap:6px">
      <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      + Tambah Jenis Surat Baru
    </button>
  </div>
</div>

{{-- PANDUAN VARIABEL PENOMORAN --}}
<div class="glass panel" style="margin-bottom:20px;border-left:4px solid var(--accent);padding:16px 20px">
  <div style="font-weight:700;font-size:13.5px;color:var(--text);margin-bottom:6px">
    💡 Panduan Variabel Pola Format Penomoran:
  </div>
  <div style="font-size:12.5px;color:var(--muted);line-height:1.6">
    Format penomoran surat dapat menggunakan tag dinamis:
    <code>{NOMOR}</code> = Urutan angka (contoh: 001),
    <code>{KODE}</code> = Kode klasifikasi dinas (contoh: 800, 421.5),
    <code>{JENIS}</code> = Kode jenis surat (contoh: SK, ST),
    <code>{SEKOLAH}</code> = Kode singkat sekolah (contoh: SMKN1),
    <code>{BULAN_ROMAWI}</code> = Angka Romawi bulan (contoh: I, IX, XII),
    <code>{BULAN}</code> = Angka 2 digit bulan (contoh: 09),
    <code>{TAHUN}</code> = 4 digit tahun (contoh: 2026).
  </div>
</div>

{{-- TABEL MASTER JENIS PERSURATAN --}}
<div class="glass panel" style="padding:0;overflow:hidden">
  <div class="table-wrap">
    <table class="tbl">
      <thead>
        <tr>
          <th style="width:40px;text-align:center">No</th>
          <th>Nama Jenis Surat</th>
          <th style="width:100px">Kode</th>
          <th style="width:150px">Kategori Agenda</th>
          <th style="width:100px">Klasifikasi</th>
          <th>Pola Format Nomor</th>
          <th style="width:90px;text-align:center">Total Arsip</th>
          <th style="width:110px;text-align:right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($letterTypes as $idx => $lt)
          <tr>
            <td style="text-align:center;font-size:12px;color:var(--muted)">{{ $idx + 1 }}</td>
            <td>
              <div style="font-weight:700;color:var(--text)">{{ $lt->name }}</div>
              @if (!$lt->is_active)
                <span class="badge" style="background:#fee2e2;color:#b91c1c;font-size:10px">Nonaktif</span>
              @endif
            </td>
            <td>
              <code style="font-weight:700;color:var(--accent)">{{ $lt->code }}</code>
            </td>
            <td>
              @if ($lt->category === 'sk')
                <span class="badge" style="background:#fef3c7;color:#b45309;font-size:11px">📜 Buku SK</span>
              @else
                <span class="badge" style="background:#e0f2fe;color:#0284c7;font-size:11px">✉️ Surat Keluar</span>
              @endif
            </td>
            <td>
              <span style="font-family:monospace;font-weight:600">{{ $lt->classification_code ?: '—' }}</span>
            </td>
            <td>
              <code style="background:#f1f5f9;padding:4px 8px;border-radius:4px;font-size:12px;display:inline-block">
                {{ $lt->numbering_format }}
              </code>
            </td>
            <td style="text-align:center;font-weight:700">
              {{ $lt->letters_count }}
            </td>
            <td>
              <div class="actions" style="justify-content:flex-end;gap:4px">
                <button type="button" class="btn btn-sm" data-dialog="#modal-edit-type-{{ $lt->id }}" title="Edit Format" style="height:30px;padding:0 10px;font-size:12px">
                  ✏️ Edit
                </button>
                @if ($lt->letters_count === 0)
                  <form method="POST" action="{{ route('letter-types.destroy', $lt) }}" data-confirm="Hapus jenis surat {{ $lt->name }}?">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus" style="height:30px;padding:0 8px;font-size:12px">
                      🗑️
                    </button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="empty">Belum ada jenis surat yang dikonfigurasi.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- MODAL TAMBAH JENIS SURAT BARU --}}
<dialog class="dlg" id="modal-add-type" style="max-width:620px">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
      <h3 style="margin:0;font-size:17px;color:var(--text)">Tambah Jenis Persuratan Baru</h3>
      <button type="button" data-close class="modal-close" aria-label="Tutup">✕</button>
    </div>

    <form method="POST" action="{{ route('letter-types.store') }}" class="stack" style="gap:14px">
      @csrf

      <div class="field">
        <label>Nama Jenis Surat *</label>
        <input type="text" name="name" class="input" placeholder="mis. Surat Keterangan Penghasilan Guru" required>
      </div>

      <div class="form-grid" style="grid-template-columns:1fr 1fr">
        <div class="field">
          <label>Kode Singkat Surat *</label>
          <input type="text" name="code" class="input" placeholder="mis. KET-GAJI" required>
        </div>
        <div class="field">
          <label>Kategori Agenda Penomoran *</label>
          <select name="category" class="select" required>
            <option value="surat_keluar">✉️ Surat Keluar / Dinas Biasa</option>
            <option value="sk">📜 Surat Keputusan (SK) Kepala Sekolah</option>
          </select>
        </div>
      </div>

      <div class="form-grid" style="grid-template-columns:1fr 1fr">
        <div class="field">
          <label>Kode Klasifikasi Kearsipan (Opsional)</label>
          <input type="text" name="classification_code" class="input" placeholder="mis. 800, 421.5, 422">
        </div>
        <div class="field">
          <label>Jumlah Digit Nomor Urut *</label>
          <input type="number" name="padding_digits" class="input" value="3" min="1" max="6" required>
        </div>
      </div>

      <div class="field">
        <label>Pola Format Penomoran *</label>
        <input type="text" name="numbering_format" class="input" value="{KODE}/{NOMOR}/{SEKOLAH}/{BULAN_ROMAWI}/{TAHUN}" required style="font-family:monospace">
      </div>

      <div class="field">
        <label>Draft / Template Isi Bawaan (HTML / Text)</label>
        <textarea name="default_template_body" class="input" rows="6" placeholder="Ketik draft pembuka atau isi surat..."></textarea>
      </div>

      <div class="dlg-actions" style="margin-top:10px">
        <button type="button" class="btn" data-close>Batal</button>
        <button type="submit" class="btn btn-ink">Simpan Jenis Surat</button>
      </div>
    </form>
  </div>
</dialog>

{{-- MODAL EDIT JENIS SURAT --}}
@foreach ($letterTypes as $lt)
<dialog class="dlg" id="modal-edit-type-{{ $lt->id }}" style="max-width:620px">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
      <h3 style="margin:0;font-size:17px;color:var(--text)">Edit Jenis Surat: {{ $lt->name }}</h3>
      <button type="button" data-close class="modal-close" aria-label="Tutup">✕</button>
    </div>

    <form method="POST" action="{{ route('letter-types.update', $lt) }}" class="stack" style="gap:14px">
      @csrf
      @method('PUT')

      <div class="field">
        <label>Nama Jenis Surat *</label>
        <input type="text" name="name" class="input" value="{{ $lt->name }}" required>
      </div>

      <div class="form-grid" style="grid-template-columns:1fr 1fr">
        <div class="field">
          <label>Kode Singkat Surat *</label>
          <input type="text" name="code" class="input" value="{{ $lt->code }}" required>
        </div>
        <div class="field">
          <label>Kategori Agenda Penomoran *</label>
          <select name="category" class="select" required>
            <option value="surat_keluar" {{ $lt->category === 'surat_keluar' ? 'selected' : '' }}>✉️ Surat Keluar / Dinas Biasa</option>
            <option value="sk" {{ $lt->category === 'sk' ? 'selected' : '' }}>📜 Surat Keputusan (SK) Kepala Sekolah</option>
          </select>
        </div>
      </div>

      <div class="form-grid" style="grid-template-columns:1fr 1fr">
        <div class="field">
          <label>Kode Klasifikasi Kearsipan</label>
          <input type="text" name="classification_code" class="input" value="{{ $lt->classification_code }}">
        </div>
        <div class="field">
          <label>Jumlah Digit Nomor Urut *</label>
          <input type="number" name="padding_digits" class="input" value="{{ $lt->padding_digits }}" min="1" max="6" required>
        </div>
      </div>

      <div class="field">
        <label>Pola Format Penomoran *</label>
        <input type="text" name="numbering_format" class="input" value="{{ $lt->numbering_format }}" required style="font-family:monospace">
      </div>

      <div class="field">
        <label>Draft / Template Isi Bawaan (HTML / Text)</label>
        <textarea name="default_template_body" class="input" rows="8">{{ $lt->default_template_body }}</textarea>
      </div>

      <div class="field">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
          <input type="checkbox" name="is_active" value="1" {{ $lt->is_active ? 'checked' : '' }}>
          <span>Status Jenis Surat Aktif</span>
        </label>
      </div>

      <div class="dlg-actions" style="margin-top:10px">
        <button type="button" class="btn" data-close>Batal</button>
        <button type="submit" class="btn btn-ink">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</dialog>
@endforeach
@endsection
