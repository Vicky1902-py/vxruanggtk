@extends('layouts.app')
@section('title', 'Buat Surat / SK Baru')

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <a href="{{ route('letters.index') }}" style="color:var(--muted);text-decoration:none;font-size:12px;display:flex;align-items:center;gap:4px">
        ← Kembali ke Buku Agenda
      </a>
      <span class="cs-pill" style="font-size:11px;padding:3px 12px"><span class="dot" style="background:#0284c7"></span> Pembuat Dokumen Resmi</span>
    </div>
    <h1>Buat Surat Keluar / Surat Keputusan (SK)</h1>
    <div class="sub">Pilih jenis persuratan, sistem akan otomatis menentukan urutan buku agenda dan membuat nomor surat terformat.</div>
  </div>
</div>

<form method="POST" action="{{ route('letters.store') }}" class="stack" style="gap:20px" id="letterForm">
  @csrf

  {{-- 1. IDENTIFIKASI & GENERATOR NOMOR CERDAS --}}
  <div class="glass panel" style="border-top:4px solid var(--accent)">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px">
      <h2 class="panel-title" style="margin:0">
        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        1. Jenis Persuratan &amp; Penomoran Otomatis
      </h2>
      <span id="categoryBadge" class="badge" style="background:#e0f2fe;color:#0284c7;font-size:12px;padding:4px 12px;font-weight:700">
        {{ $selectedType?->category === 'sk' ? '📜 Buku Agenda SK' : '✉️ Buku Agenda Surat Keluar' }}
      </span>
    </div>

    <div class="form-grid" style="grid-template-columns:1.5fr 1fr">
      <div class="field">
        <label>Pilih Jenis Persuratan *</label>
        <select name="letter_type_id" id="letterTypeSelect" class="select" required style="font-size:14px;font-weight:600">
          @foreach ($letterTypes as $lt)
            <option value="{{ $lt->id }}" data-category="{{ $lt->category }}" {{ old('letter_type_id', $selectedType?->id) == $lt->id ? 'selected' : '' }}>
              [{{ strtoupper($lt->category === 'sk' ? 'SK' : 'Dinas') }}] {{ $lt->name }} ({{ $lt->code }})
            </option>
          @endforeach
        </select>
        <small style="font-size:11px;color:var(--muted)">Sistem otomatis mendeteksi apakah surat ini masuk ke buku agenda SK atau Surat Keluar umum.</small>
      </div>

      <div class="field">
        <label>Tanggal Surat *</label>
        <input type="date" name="letter_date" id="letterDateInput" class="input" value="{{ old('letter_date', date('Y-m-d')) }}" required>
      </div>
    </div>

    {{-- KOTAK NOMOR SURAT YANG DI-GENERATE SISTEM --}}
    <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:var(--radius-sm);padding:14px 18px;margin-top:14px">
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
        <div>
          <div style="font-size:11.5px;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px">Nomor Surat Otomatis yang Dihasilkan:</div>
          <div id="previewNumberDisplay" style="font-family:monospace;font-size:20px;font-weight:800;color:var(--accent);margin-top:2px">
            {{ $previewData['reference_number'] ?? 'Memuat nomor...' }}
          </div>
        </div>
        <div style="text-align:right">
          <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer;color:var(--text)">
            <input type="checkbox" id="toggleCustomNumber">
            <span>Kustomisasi Nomor secara Manual</span>
          </label>
        </div>
      </div>

      <div id="customNumberBox" style="display:none;margin-top:10px;padding-top:10px;border-top:1px dashed #cbd5e1">
        <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px">Ketik Nomor Manual (Khusus jika ada penomoran arsip lama):</label>
        <input type="text" name="custom_reference_number" id="customNumberInput" class="input" placeholder="mis. 800/012.A/SK-SMK/IX/2026" style="font-family:monospace">
      </div>
    </div>
  </div>

  {{-- 2. METADATA SURAT & TARGET SUBJEK --}}
  <div class="glass panel">
    <h2 class="panel-title" style="margin-bottom:16px">
      <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      2. Perihal, Tujuan &amp; Kaitan Data (Opsional)
    </h2>

    <div class="form-grid" style="grid-template-columns:1.5fr 1fr">
      <div class="field">
        <label>Perihal / Hal Surat *</label>
        <input type="text" name="subject" id="subjectInput" class="input" value="{{ old('subject') }}" placeholder="mis. Pembagian Tugas Mengajar Semester Ganjil TA 2026/2027" required>
      </div>

      <div class="field">
        <label>Kepada Yth. / Tujuan Surat</label>
        <input type="text" name="recipient" id="recipientInput" class="input" value="{{ old('recipient') }}" placeholder="mis. Dewan Guru & Karyawan / Orang Tua Siswa">
      </div>
    </div>

    <div class="form-grid" style="grid-template-columns:1fr 1fr;margin-top:14px">
      <div class="field">
        <label>Hubungkan dengan Siswa (Jika Surat Kesiswaan / Keterangan):</label>
        <select name="student_id" id="studentSelect" class="select">
          <option value="">— Tidak Terhubung dengan Siswa Spesifik —</option>
          @foreach ($students as $st)
            <option value="{{ $st->id }}" {{ old('student_id') == $st->id ? 'selected' : '' }}>
              {{ $st->full_name }} (NIS: {{ $st->nis ?? '—' }} · {{ $st->schoolClass?->name ?? 'Tanpa Kelas' }})
            </option>
          @endforeach
        </select>
        <small style="font-size:11px;color:var(--muted)">Memilih siswa akan otomatis mengisikan nama, NIS, NISN, kelas, dan data orang tua ke dalam teks surat.</small>
      </div>

      <div class="field">
        <label>Hubungkan dengan Guru / GTK (Jika SK Tugas / Surat Tugas):</label>
        <select name="employee_id" id="employeeSelect" class="select">
          <option value="">— Tidak Terhubung dengan GTK Spesifik —</option>
          @foreach ($employees as $emp)
            <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
              {{ $emp->full_name }} (NIP: {{ $emp->nip ?? '—' }} · {{ $emp->position?->name ?? 'GTK' }})
            </option>
          @endforeach
        </select>
        <small style="font-size:11px;color:var(--muted)">Memilih GTK akan otomatis mengisi nama, NIP, dan jabatan guru/pegawai ke dalam draft surat.</small>
      </div>
    </div>
  </div>

  {{-- 3. EDITOR ISI SURAT LENGKAP --}}
  <div class="glass panel">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px">
      <div>
        <h2 class="panel-title" style="margin:0">
          <svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
          3. Isi Redaksi Surat
        </h2>
        <div style="font-size:12px;color:var(--muted);margin-top:2px">
          Anda dapat menyunting teks di bawah ini sesuai kebutuhan. Tag seperti <code>{nama_siswa}</code> akan otomatis digantikan data sebenarnya saat dicetak.
        </div>
      </div>
      <button type="button" class="btn btn-sm" id="btnReloadTemplate" style="display:flex;align-items:center;gap:4px">
        🔄 Muat Ulang Template Bawaan
      </button>
    </div>

    {{-- TOOLBAR CHIPS INSERT TAG --}}
    <div style="background:#f1f5f9;padding:8px 12px;border-radius:6px;margin-bottom:10px;display:flex;align-items:center;gap:6px;flex-wrap:wrap">
      <span style="font-size:11px;font-weight:700;color:var(--muted)">Sisipkan Tag:</span>
      <button type="button" class="btn btn-sm tag-chip" data-tag="{nomor_surat}" style="height:24px;font-size:11px;padding:0 8px">{nomor_surat}</button>
      <button type="button" class="btn btn-sm tag-chip" data-tag="{nama_sekolah}" style="height:24px;font-size:11px;padding:0 8px">{nama_sekolah}</button>
      <button type="button" class="btn btn-sm tag-chip" data-tag="{nama_kepsek}" style="height:24px;font-size:11px;padding:0 8px">{nama_kepsek}</button>
      <button type="button" class="btn btn-sm tag-chip" data-tag="{nama_siswa}" style="height:24px;font-size:11px;padding:0 8px">{nama_siswa}</button>
      <button type="button" class="btn btn-sm tag-chip" data-tag="{nis_siswa}" style="height:24px;font-size:11px;padding:0 8px">{nis_siswa}</button>
      <button type="button" class="btn btn-sm tag-chip" data-tag="{kelas_siswa}" style="height:24px;font-size:11px;padding:0 8px">{kelas_siswa}</button>
      <button type="button" class="btn btn-sm tag-chip" data-tag="{nama_gtk}" style="height:24px;font-size:11px;padding:0 8px">{nama_gtk}</button>
      <button type="button" class="btn btn-sm tag-chip" data-tag="{nip_gtk}" style="height:24px;font-size:11px;padding:0 8px">{nip_gtk}</button>
      <button type="button" class="btn btn-sm tag-chip" data-tag="{hari_tanggal}" style="height:24px;font-size:11px;padding:0 8px">{hari_tanggal}</button>
    </div>

    <div class="field">
      <textarea name="content" id="letterContentTextarea" class="input" rows="18" style="font-family:'Segoe UI', Arial, sans-serif;font-size:13.5px;line-height:1.6;padding:14px" required>{{ old('content', $selectedType?->default_template_body) }}</textarea>
    </div>

    <div class="form-grid" style="grid-template-columns:1fr 1fr;margin-top:14px;align-items:center">
      <div class="field">
        <label>Status Dokumen *</label>
        <select name="status" class="select" required>
          <option value="diterbitkan" {{ old('status') === 'diterbitkan' ? 'selected' : '' }}>Diterbitkan (Resmi &amp; Siap Cetak)</option>
          <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft (Konsep Sementara)</option>
          <option value="diarsipkan" {{ old('status') === 'diarsipkan' ? 'selected' : '' }}>Diarsipkan</option>
        </select>
      </div>

      <div style="padding-top:20px">
        <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer">
          <input type="checkbox" name="signed_by_principal" value="1" {{ old('signed_by_principal', true) ? 'checked' : '' }}>
          <span><b>Sertakan Pengesahan Kepala Sekolah</b> (Titi Mangsa, Nama, NIP &amp; Tanda Tangan)</span>
        </label>
      </div>
    </div>
  </div>

  {{-- SUBMIT BUTTONS --}}
  <div style="display:flex;justify-content:flex-end;gap:12px;margin-bottom:30px">
    <a href="{{ route('letters.index') }}" class="btn" style="padding:10px 20px">Batal</a>
    <button type="submit" class="btn btn-ink" style="padding:12px 28px;font-size:14px;font-weight:700" data-loading="Menyimpan & Menerbitkan Surat...">
      💾 Simpan &amp; Terbitkan Surat Resmi
    </button>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const typeSelect = document.getElementById('letterTypeSelect');
  const dateInput = document.getElementById('letterDateInput');
  const studentSelect = document.getElementById('studentSelect');
  const employeeSelect = document.getElementById('employeeSelect');
  const subjectInput = document.getElementById('subjectInput');
  const recipientInput = document.getElementById('recipientInput');
  const contentArea = document.getElementById('letterContentTextarea');
  const previewDisplay = document.getElementById('previewNumberDisplay');
  const categoryBadge = document.getElementById('categoryBadge');
  const toggleCustom = document.getElementById('toggleCustomNumber');
  const customBox = document.getElementById('customNumberBox');
  const customInput = document.getElementById('customNumberInput');
  const btnReload = document.getElementById('btnReloadTemplate');

  // Toggle custom number
  toggleCustom.addEventListener('change', function() {
    customBox.style.display = this.checked ? 'block' : 'none';
    if (!this.checked) customInput.value = '';
  });

  // Fetch updated number and optionally template via AJAX
  function fetchNumberPreview(loadTemplate = false) {
    const typeId = typeSelect.value;
    const date = dateInput.value;
    const studentId = studentSelect.value;
    const employeeId = employeeSelect.value;
    const subject = subjectInput.value;
    const recipient = recipientInput.value;

    if (!typeId) return;

    const url = new URL("{{ route('letters.preview-number') }}", window.location.origin);
    url.searchParams.set('type_id', typeId);
    url.searchParams.set('date', date);
    if (studentId) url.searchParams.set('student_id', studentId);
    if (employeeId) url.searchParams.set('employee_id', employeeId);
    if (subject) url.searchParams.set('subject', subject);
    if (recipient) url.searchParams.set('recipient', recipient);

    fetch(url)
      .then(res => res.json())
      .then(data => {
        if (data.reference_number) {
          previewDisplay.innerText = data.reference_number;
          if (data.category === 'sk') {
            categoryBadge.innerText = '📜 Buku Agenda SK';
            categoryBadge.style.background = '#fef3c7';
            categoryBadge.style.color = '#b45309';
          } else {
            categoryBadge.innerText = '✉️ Buku Agenda Surat Keluar';
            categoryBadge.style.background = '#e0f2fe';
            categoryBadge.style.color = '#0284c7';
          }

          if (loadTemplate && data.template_body) {
            contentArea.value = data.template_body;
          }
        }
      })
      .catch(err => console.error(err));
  }

  typeSelect.addEventListener('change', () => fetchNumberPreview(true));
  dateInput.addEventListener('change', () => fetchNumberPreview(false));
  studentSelect.addEventListener('change', () => fetchNumberPreview(false));
  employeeSelect.addEventListener('change', () => fetchNumberPreview(false));
  btnReload.addEventListener('click', () => fetchNumberPreview(true));

  // Tag chip click to insert into textarea at cursor
  document.querySelectorAll('.tag-chip').forEach(btn => {
    btn.addEventListener('click', function() {
      const tag = this.dataset.tag;
      const start = contentArea.selectionStart;
      const end = contentArea.selectionEnd;
      const text = contentArea.value;
      contentArea.value = text.substring(0, start) + tag + text.substring(end);
      contentArea.focus();
      contentArea.selectionStart = contentArea.selectionEnd = start + tag.length;
    });
  });
});
</script>
@endsection
