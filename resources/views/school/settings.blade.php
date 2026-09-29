@extends('layouts.app')
@section('title', 'Pengaturan Sekolah & Kop Surat')

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <span class="cs-pill" style="font-size:11px;padding:3px 12px"><span class="dot" style="background:#0284c7"></span> Administrasi Lembaga</span>
      <span style="font-size:12px;color:var(--muted)">Pengaturan Identitas &amp; Dokumen Kedinasan</span>
    </div>
    <h1>Pengaturan Sekolah &amp; Kop Surat</h1>
    <div class="sub">Kelola identitas resmi sekolah, susunan kop surat dinas dengan dual logo (Sekolah &amp; Pemprov), serta data Kepala Sekolah.</div>
  </div>
</div>

{{-- 1. LIVE PREVIEW KOP SURAT RESMI --}}
<div class="glass panel" style="margin-bottom:20px;border-top:4px solid var(--accent)">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px">
    <h2 class="panel-title" style="margin:0">
      <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
      Pratinjau Kop Surat Resmi Sekolah (Live Preview)
    </h2>
    <span class="badge badge-blue">Tampil pada Kuitansi, Slip Gaji, BKU &amp; Surat Resmi</span>
  </div>

  <div style="background:#ffffff;padding:24px 30px;border-radius:var(--radius-sm);border:1px solid #cbd5e1;box-shadow:0 2px 10px rgba(0,0,0,0.03)">
    @include('partials.kop-surat', ['school' => $school])
    <div style="text-align:center;font-size:11.5px;color:var(--muted);font-style:italic;margin-top:6px">
      * Format di atas adalah tampilan standar yang otomatis dicetak pada kuitansi pembayaran, slip gaji GTK, buku kas umum, dan laporan dinas.
    </div>
  </div>
</div>

{{-- 2. FORM PENGATURAN LENGKAP --}}
<form method="POST" action="{{ route('school.settings.update') }}" enctype="multipart/form-data" class="stack" style="gap:20px">
  @csrf
  @method('PUT')

  {{-- A. IDENTITAS SATUAN PENDIDIKAN --}}
  <div class="glass panel">
    <h2 class="panel-title" style="margin-bottom:16px">
      <svg viewBox="0 0 24 24"><path d="M3 9l9-6 9 6"/><path d="M5 9v9a2 2 0 002 2h10a2 2 0 002-2V9"/><path d="M9 20v-6h6v6"/></svg>
      1. Identitas Satuan Pendidikan &amp; Kontak Resmi
    </h2>

    <div class="form-grid" style="grid-template-columns:2fr 1fr 1fr">
      <div class="field">
        <label>Nama Resmi Sekolah *</label>
        <input type="text" name="name" class="input" value="{{ old('name', $school->name) }}" required placeholder="SMK Negeri 1 Surabaya">
      </div>
      <div class="field">
        <label>NPSN (Nomor Pokok Sekolah Nasional)</label>
        <input type="text" name="npsn" class="input" value="{{ old('npsn', $school->npsn) }}" placeholder="20532219">
      </div>
      <div class="field">
        <label>Status Sekolah *</label>
        <select name="status_sekolah" class="select" required>
          <option value="Negeri" {{ old('status_sekolah', $school->status_sekolah) === 'Negeri' ? 'selected' : '' }}>Negeri</option>
          <option value="Swasta" {{ old('status_sekolah', $school->status_sekolah) === 'Swasta' ? 'selected' : '' }}>Swasta</option>
        </select>
      </div>
    </div>

    <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr;margin-top:12px">
      <div class="field">
        <label>Jenjang Pendidikan *</label>
        <select name="level" class="select" required>
          @foreach (['SMK' => 'SMK / MAK', 'SMA' => 'SMA / MA', 'SMP' => 'SMP / MTs', 'SD' => 'SD / MI', 'SLB' => 'SLB', 'PKBM' => 'PKBM / Kursus'] as $lvlKey => $lvlLabel)
            <option value="{{ $lvlKey }}" {{ old('level', $school->level) === $lvlKey ? 'selected' : '' }}>{{ $lvlLabel }}</option>
          @endforeach
        </select>
      </div>
      <div class="field">
        <label>Email Resmi Sekolah</label>
        <input type="email" name="email" class="input" value="{{ old('email', $school->email) }}" placeholder="info@sekolah.sch.id">
      </div>
      <div class="field">
        <label>Nomor Telepon / Fax</label>
        <input type="text" name="phone" class="input" value="{{ old('phone', $school->phone) }}" placeholder="(031) 8292038">
      </div>
    </div>

    <div class="form-grid" style="grid-template-columns:1.5fr 1fr 1fr 1fr;margin-top:12px">
      <div class="field">
        <label>Alamat Lengkap (Jalan / Nomor / RT / RW)</label>
        <input type="text" name="address" class="input" value="{{ old('address', $school->address) }}" placeholder="Jl. SMEA No. 4, Wonokromo">
      </div>
      <div class="field">
        <label>Kota / Kabupaten</label>
        <input type="text" name="city" class="input" value="{{ old('city', $school->city) }}" placeholder="Kota Surabaya">
      </div>
      <div class="field">
        <label>Provinsi</label>
        <input type="text" name="province" class="input" value="{{ old('province', $school->province) }}" placeholder="Jawa Timur">
      </div>
      <div class="field">
        <label>Kode Pos</label>
        <input type="text" name="postal_code" class="input" value="{{ old('postal_code', $school->postal_code) }}" placeholder="60243">
      </div>
    </div>

    <div class="field" style="margin-top:12px">
      <label>Website Resmi</label>
      <input type="text" name="website" class="input" value="{{ old('website', $school->website) }}" placeholder="https://www.smkn1surabaya.sch.id">
    </div>
  </div>

  {{-- B. SUSUNAN KOP SURAT DINAS & LOGO GANDA --}}
  <div class="glass panel">
    <h2 class="panel-title" style="margin-bottom:16px">
      <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
      2. Susunan Kop Surat Dinas &amp; Dual Logo (Kiri Sekolah, Kanan Pemprov)
    </h2>

    <div class="two-col" style="gap:20px;margin-bottom:18px">
      {{-- Logo Kiri --}}
      <div class="glass-soft" style="padding:16px;border-radius:var(--radius-sm)">
        <div style="font-weight:700;font-size:13.5px;color:var(--text);margin-bottom:8px">
          🛡️ Logo Kiri: Lambang Resmi Sekolah
        </div>
        <div style="display:flex;align-items:center;gap:16px">
          <div style="width:68px;height:68px;border:1px solid #cbd5e1;border-radius:8px;padding:6px;background:#fff;display:flex;align-items:center;justify-content:center;flex:none">
            <img src="{{ $school->logo_school }}" alt="Logo Sekolah" style="max-width:100%;max-height:100%;object-fit:contain">
          </div>
          <div style="flex:1">
            <label style="font-size:12px;color:var(--muted);display:block;margin-bottom:4px">Pilih File Logo Sekolah Baru:</label>
            <input type="file" name="logo_school" class="input" accept="image/*" style="font-size:12px;height:38px;padding:6px 10px">
            <small style="color:var(--muted);font-size:11px;display:block;margin-top:2px">Format: PNG transparan, SVG, atau JPG (Maks. 2MB)</small>
          </div>
        </div>
      </div>

      {{-- Logo Kanan --}}
      <div class="glass-soft" style="padding:16px;border-radius:var(--radius-sm)">
        <div style="font-weight:700;font-size:13.5px;color:var(--text);margin-bottom:8px">
          🏛️ Logo Kanan: Pemerintah Provinsi / Kabupaten / Dinas / Tut Wuri
        </div>
        <div style="display:flex;align-items:center;gap:16px">
          <div style="width:68px;height:68px;border:1px solid #cbd5e1;border-radius:8px;padding:6px;background:#fff;display:flex;align-items:center;justify-content:center;flex:none">
            <img src="{{ $school->logo_government }}" alt="Logo Pemprov / Dinas" style="max-width:100%;max-height:100%;object-fit:contain">
          </div>
          <div style="flex:1">
            <label style="font-size:12px;color:var(--muted);display:block;margin-bottom:4px">Pilih File Logo Pemprov / Dinas Baru:</label>
            <input type="file" name="logo_government" class="input" accept="image/*" style="font-size:12px;height:38px;padding:6px 10px">
            <small style="color:var(--muted);font-size:11px;display:block;margin-top:2px">Format: PNG transparan, SVG, atau JPG (Maks. 2MB)</small>
          </div>
        </div>
      </div>
    </div>

    {{-- Teks Berjenjang Kop Surat --}}
    <div class="field" style="margin-bottom:12px">
      <label>Kop Baris 1: Pemerintah Provinsi / Kabupaten / Yayasan (Huruf Kapital)</label>
      <input type="text" name="header_line_1" class="input" value="{{ old('header_line_1', $school->header_line_1) }}" placeholder="PEMERINTAH PROVINSI JAWA TIMUR">
      <small style="font-size:11px;color:var(--muted)">Kosongkan untuk otomatis menggunakan nama provinsi.</small>
    </div>

    <div class="field" style="margin-bottom:12px">
      <label>Kop Baris 2: Dinas / Lembaga Terkait (Huruf Kapital)</label>
      <input type="text" name="header_line_2" class="input" value="{{ old('header_line_2', $school->header_line_2) }}" placeholder="DINAS PENDIDIKAN">
    </div>

    <div class="field" style="margin-bottom:12px">
      <label>Kop Baris 3: Nama Satuan Pendidikan (Huruf Kapital &amp; Menonjol)</label>
      <input type="text" name="header_line_3" class="input" value="{{ old('header_line_3', $school->header_line_3) }}" placeholder="SEKOLAH MENENGAH KEJURUAN NEGERI 1 SURABAYA">
      <small style="font-size:11px;color:var(--muted)">Kosongkan untuk otomatis menggunakan nama resmi sekolah.</small>
    </div>

    <div class="field">
      <label>Kop Baris 4: Alamat Lengkap, Kontak, Email &amp; Laman Web</label>
      <textarea name="header_line_4" class="input" rows="2" placeholder="Jl. SMEA No. 4, Wonokromo, Surabaya Telp. (031) 8292038 Email: info@smkn1sby.sch.id Website: smkn1surabaya.sch.id">{{ old('header_line_4', $school->header_line_4) }}</textarea>
      <small style="font-size:11px;color:var(--muted)">Tampil di bagian bawah kop surat sebelum garis ganda pemisah.</small>
    </div>
  </div>

  {{-- C. PEJABAT KEPALA SEKOLAH --}}
  <div class="glass panel">
    <h2 class="panel-title" style="margin-bottom:16px">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="7.5" r="3.5"/><path d="M5 20.5c.7-4.5 3.2-7 7-7s6.3 2.5 7 7"/></svg>
      3. Pejabat Kepala Sekolah &amp; Pengesahan
    </h2>

    <div class="form-grid" style="grid-template-columns:1.5fr 1fr 1fr">
      <div class="field">
        <label>Nama Kepala Sekolah (Lengkap dengan Gelar) *</label>
        <input type="text" name="principal_name" class="input" value="{{ old('principal_name', $school->principal_name) }}" placeholder="Drs. Hendra Kusuma, M.Pd.">
      </div>
      <div class="field">
        <label>NIP / NUPTK Kepala Sekolah</label>
        <input type="text" name="principal_nip" class="input" value="{{ old('principal_nip', $school->principal_nip) }}" placeholder="19720315 199802 1 004">
      </div>
      <div class="field">
        <label>Sebutan Jabatan</label>
        <input type="text" name="principal_title" class="input" value="{{ old('principal_title', $school->principal_title ?: 'Kepala Sekolah') }}" placeholder="Kepala Sekolah / Plt. Kepala Sekolah">
      </div>
    </div>

    <div class="field" style="margin-top:14px">
      <label>Tanda Tangan / Stempel Resmi Digital (Opsional)</label>
      <div style="display:flex;align-items:center;gap:14px">
        @if ($school->signature_url)
          <div style="width:100px;height:50px;border:1px solid #cbd5e1;padding:4px;border-radius:4px;background:#fff;display:flex;align-items:center;justify-content:center">
            <img src="{{ asset($school->signature_url) }}" alt="Stempel / TTD" style="max-width:100%;max-height:100%;object-fit:contain">
          </div>
        @endif
        <div style="flex:1">
          <input type="file" name="signature" class="input" accept="image/*" style="font-size:12px;height:38px;padding:6px 10px">
          <small style="color:var(--muted);font-size:11px">Format PNG transparan untuk stempel dan paraf resmi (Maks. 2MB)</small>
        </div>
      </div>
    </div>
  </div>

  {{-- SUBMIT BUTTON --}}
  <div style="display:flex;justify-content:flex-end;gap:12px;margin-bottom:30px">
    <button type="submit" class="btn btn-ink" style="padding:12px 30px;font-size:14px;font-weight:700" data-loading="Menyimpan Pengaturan...">
      💾 Simpan Pengaturan Sekolah &amp; Kop Surat
    </button>
  </div>
</form>
@endsection
