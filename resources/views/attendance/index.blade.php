@extends('layouts.app')
@section('title', 'Presensi Siswa')

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <span class="vtx-pill" style="font-size:11px;padding:3px 12px"><span class="dot"></span> Modul Presensi</span>
      <span style="font-size:12px;color:var(--muted)">Input Presensi Harian GTK</span>
    </div>
    <h1>Presensi Siswa</h1>
    <div class="sub">Pencatatan kehadiran peserta didik harian per kelas oleh guru kelas/wali kelas.</div>
  </div>
</div>

<div class="glass panel" style="margin-bottom:18px">
  <form method="GET" class="form-grid">
    <div class="field">
      <label>Pilih Rombongan Belajar (Kelas)</label>
      <select name="class_id" class="select" onchange="this.form.submit()">
        @foreach ($classes as $class)
          <option value="{{ $class->id }}" {{ (string) $classId === (string) $class->id ? 'selected' : '' }}>
            Kelas {{ $class->name }} ({{ $class->academicYear?->year_label ?? 'Aktif' }})
          </option>
        @endforeach
      </select>
    </div>
    <div class="field">
      <label>Tanggal Presensi</label>
      <input type="date" name="date" class="input" value="{{ $date }}" onchange="this.form.submit()">
    </div>
    <noscript><button class="btn">Tampilkan Data</button></noscript>
  </form>
</div>

<form method="POST" action="{{ route('attendance.store') }}">
  @csrf
  <input type="hidden" name="class_id" value="{{ $classId }}">
  <input type="hidden" name="att_date" value="{{ $date }}">
  <input type="hidden" name="back" value="{{ request()->getRequestUri() }}">
  
  <div class="glass panel" style="display:grid;gap:10px">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding-bottom:12px;border-bottom:1px solid var(--line-light)">
      <div style="font-size:13.5px;font-weight:600;color:var(--text)">
        Daftar Siswa ({{ $students->count() }} orang)
      </div>
      <div style="display:flex;gap:8px;font-size:12px" id="attCounterStrip">
        <span class="badge badge-ok">Hadir: <b id="countHadir">0</b></span>
        <span class="badge badge-blue">Izin: <b id="countIzin">0</b></span>
        <span class="badge badge-warn">Sakit: <b id="countSakit">0</b></span>
        <span class="badge badge-bad">Alpa: <b id="countAlpa">0</b></span>
      </div>
    </div>

    @forelse ($students as $student)
      @php $att = $existing->get($student->id); @endphp
      <div class="att-row">
        <div class="att-name">
          <b>{{ $student->full_name }}</b>
          <small>NIS: {{ $student->nis ?? '—' }} · NISN: {{ $student->nisn ?? '—' }}</small>
        </div>
        <div class="seg" role="radiogroup" aria-label="Status {{ $student->full_name }}">
          @foreach (['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'] as $st => $label)
            <label>
              <input type="radio" class="att-radio" name="statuses[{{ $student->id }}]" value="{{ $st }}"
                {{ ($att?->status ?? 'hadir') === $st ? 'checked' : '' }}>
              <span>{{ $label }}</span>
            </label>
          @endforeach
        </div>
      </div>
    @empty
      <div class="empty">
        <p>Belum ada siswa di kelas ini atau belum ada kelas yang dipilih.</p>
        @if (auth()->user()?->role?->name === 'admin')
          <p style="margin-top:10px"><a href="{{ route('students.index') }}" class="btn btn-sm">Tambahkan Siswa ke Kelas →</a></p>
        @endif
      </div>
    @endforelse
  </div>

  @if ($students->isNotEmpty())
    <div style="margin-top:16px;display:flex;gap:12px;align-items:center;flex-wrap:wrap">
      <button class="btn btn-ink" data-loading="Menyimpan Presensi...">
        💾 Simpan Data Presensi
      </button>
      <span class="vtx-pill" style="font-size:12px">
        📅 {{ \Illuminate\Support\Carbon::parse($date)->translatedFormat('l, d F Y') }}
      </span>
      <span style="font-size:13px;color:var(--muted)">Perubahan langsung tersimpan ke rekap kehadiran.</span>
    </div>
  @endif
</form>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    function updateCounters() {
      var radios = document.querySelectorAll('.att-radio:checked');
      var counts = { hadir: 0, izin: 0, sakit: 0, alpa: 0 };
      radios.forEach(function(r) {
        if (counts[r.value] !== undefined) counts[r.value]++;
      });
      var h = document.getElementById('countHadir');
      var i = document.getElementById('countIzin');
      var s = document.getElementById('countSakit');
      var a = document.getElementById('countAlpa');
      if (h) h.textContent = counts.hadir;
      if (i) i.textContent = counts.izin;
      if (s) s.textContent = counts.sakit;
      if (a) a.textContent = counts.alpa;
    }
    document.querySelectorAll('.att-radio').forEach(function(r) {
      r.addEventListener('change', updateCounters);
    });
    updateCounters();
  });
</script>
@endsection
