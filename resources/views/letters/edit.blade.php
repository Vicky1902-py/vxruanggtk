@extends('layouts.app')
@section('title', 'Edit Surat — ' . $letter->reference_number)

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <a href="{{ route('letters.show', $letter) }}" style="color:var(--muted);text-decoration:none;font-size:12px;display:flex;align-items:center;gap:4px">
        ← Kembali ke Detail Surat
      </a>
      <span class="cs-pill" style="font-size:11px;padding:3px 12px"><span class="dot" style="background:#f59e0b"></span> Perubahan Dokumen</span>
    </div>
    <h1>Edit Surat: {{ $letter->reference_number }}</h1>
    <div class="sub">Sunting nomor surat, perihal, tujuan, kaitan data, atau isi redaksi surat.</div>
  </div>
</div>

<form method="POST" action="{{ route('letters.update', $letter) }}" class="stack" style="gap:20px">
  @csrf
  @method('PUT')

  {{-- 1. IDENTIFIKASI NOMOR & TANGGAL --}}
  <div class="glass panel" style="border-top:4px solid var(--accent)">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px">
      <h2 class="panel-title" style="margin:0">
        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        1. Identitas &amp; Nomor Surat
      </h2>
      <span class="badge" style="background:{{ $letter->category === 'sk' ? '#fef3c7' : '#e0f2fe' }};color:{{ $letter->category === 'sk' ? '#b45309' : '#0284c7' }};font-size:12px;padding:4px 12px;font-weight:700">
        {{ $letter->category === 'sk' ? '📜 Surat Keputusan (SK)' : '✉️ Surat Keluar / Dinas' }}
      </span>
    </div>

    <div class="form-grid" style="grid-template-columns:1.5fr 1fr">
      <div class="field">
        <label>Nomor Surat / SK *</label>
        <input type="text" name="reference_number" class="input" value="{{ old('reference_number', $letter->reference_number) }}" required style="font-family:monospace;font-weight:700;font-size:15px">
        <small style="font-size:11px;color:var(--muted)">Nomor surat dapat disesuaikan jika diperlukan.</small>
      </div>

      <div class="field">
        <label>Tanggal Surat *</label>
        <input type="date" name="letter_date" class="input" value="{{ old('letter_date', $letter->letter_date?->format('Y-m-d')) }}" required>
      </div>
    </div>
  </div>

  {{-- 2. METADATA SURAT --}}
  <div class="glass panel">
    <h2 class="panel-title" style="margin-bottom:16px">
      <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      2. Perihal, Tujuan &amp; Kaitan Data
    </h2>

    <div class="form-grid" style="grid-template-columns:1.5fr 1fr">
      <div class="field">
        <label>Perihal / Hal Surat *</label>
        <input type="text" name="subject" class="input" value="{{ old('subject', $letter->subject) }}" required>
      </div>

      <div class="field">
        <label>Kepada Yth. / Tujuan Surat</label>
        <input type="text" name="recipient" class="input" value="{{ old('recipient', $letter->recipient) }}">
      </div>
    </div>

    <div class="form-grid" style="grid-template-columns:1fr 1fr;margin-top:14px">
      <div class="field">
        <label>Hubungkan dengan Siswa:</label>
        <select name="student_id" class="select">
          <option value="">— Tidak Terhubung dengan Siswa Spesifik —</option>
          @foreach ($students as $st)
            <option value="{{ $st->id }}" {{ old('student_id', $letter->student_id) == $st->id ? 'selected' : '' }}>
              {{ $st->full_name }} (NIS: {{ $st->nis ?? '—' }} · {{ $st->schoolClass?->name ?? 'Tanpa Kelas' }})
            </option>
          @endforeach
        </select>
      </div>

      <div class="field">
        <label>Hubungkan dengan Guru / GTK:</label>
        <select name="employee_id" class="select">
          <option value="">— Tidak Terhubung dengan GTK Spesifik —</option>
          @foreach ($employees as $emp)
            <option value="{{ $emp->id }}" {{ old('employee_id', $letter->employee_id) == $emp->id ? 'selected' : '' }}>
              {{ $emp->full_name }} (NIP: {{ $emp->nip ?? '—' }} · {{ $emp->position?->name ?? 'GTK' }})
            </option>
          @endforeach
        </select>
      </div>
    </div>
  </div>

  {{-- 3. REDAKSI ISI SURAT --}}
  <div class="glass panel">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px">
      <h2 class="panel-title" style="margin:0">
        <svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
        3. Isi Redaksi Surat
      </h2>
    </div>

    <div class="field">
      <textarea name="content" class="input" rows="20" style="font-family:'Segoe UI', Arial, sans-serif;font-size:13.5px;line-height:1.6;padding:14px" required>{{ old('content', $letter->content) }}</textarea>
    </div>

    <div class="form-grid" style="grid-template-columns:1fr 1fr;margin-top:14px;align-items:center">
      <div class="field">
        <label>Status Dokumen *</label>
        <select name="status" class="select" required>
          <option value="diterbitkan" {{ old('status', $letter->status) === 'diterbitkan' ? 'selected' : '' }}>Diterbitkan (Resmi &amp; Siap Cetak)</option>
          <option value="draft" {{ old('status', $letter->status) === 'draft' ? 'selected' : '' }}>Draft (Konsep Sementara)</option>
          <option value="diarsipkan" {{ old('status', $letter->status) === 'diarsipkan' ? 'selected' : '' }}>Diarsipkan</option>
        </select>
      </div>

      <div style="padding-top:20px">
        <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer">
          <input type="checkbox" name="signed_by_principal" value="1" {{ old('signed_by_principal', $letter->signed_by_principal) ? 'checked' : '' }}>
          <span><b>Sertakan Pengesahan Kepala Sekolah</b> (Titi Mangsa, Nama, NIP &amp; Tanda Tangan)</span>
        </label>
      </div>
    </div>
  </div>

  {{-- SUBMIT BUTTONS --}}
  <div style="display:flex;justify-content:flex-end;gap:12px;margin-bottom:30px">
    <a href="{{ route('letters.show', $letter) }}" class="btn" style="padding:10px 20px">Batal</a>
    <button type="submit" class="btn btn-ink" style="padding:12px 28px;font-size:14px;font-weight:700" data-loading="Menyimpan Perubahan...">
      💾 Simpan Perubahan Surat
    </button>
  </div>
</form>
@endsection
