@extends('layouts.app')
@section('title', 'Presensi Siswa')

@section('content')
<div class="page-head">
  <div>
    <h1>Presensi Siswa</h1>
    <div class="sub">Input kehadiran per kelas per tanggal oleh guru.</div>
  </div>
</div>

<div class="glass panel" style="margin-bottom:16px">
  <form method="GET" class="form-grid">
    <div class="field">
      <label>Kelas</label>
      <select name="class_id" class="select" onchange="this.form.submit()">
        @foreach ($classes as $class)
          <option value="{{ $class->id }}" {{ (string) $classId === (string) $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="field">
      <label>Tanggal</label>
      <input type="date" name="date" class="input" value="{{ $date }}" onchange="this.form.submit()">
    </div>
    <noscript><button class="btn">Tampilkan</button></noscript>
  </form>
</div>

<form method="POST" action="{{ route('attendance.store') }}">
  @csrf
  <input type="hidden" name="class_id" value="{{ $classId }}">
  <input type="hidden" name="att_date" value="{{ $date }}">
  <input type="hidden" name="back" value="{{ request()->getRequestUri() }}">
  <div class="glass panel" style="display:grid;gap:8px">
    @forelse ($students as $student)
      @php $att = $existing->get($student->id); @endphp
      <div class="att-row">
        <div class="att-name">
          <b>{{ $student->full_name }}</b>
          <small>NIS {{ $student->nis ?? '—' }}</small>
        </div>
        <div class="seg" role="radiogroup" aria-label="Status {{ $student->full_name }}">
          @foreach (['hadir', 'izin', 'sakit', 'alpa'] as $st)
            <label>
              <input type="radio" name="statuses[{{ $student->id }}]" value="{{ $st }}"
                {{ ($att?->status ?? 'hadir') === $st ? 'checked' : '' }}>
              <span>{{ ucfirst($st) }}</span>
            </label>
          @endforeach
        </div>
      </div>
    @empty
      <div class="empty">Pilih kelas yang memiliki siswa.</div>
    @endforelse
  </div>
  @if ($students->isNotEmpty())
    <div style="margin-top:14px;display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      <button class="btn btn-ink" data-loading="Menyimpan presensi...">💾 Simpan Presensi</button>
      <span class="att-date-badge">
        📅 {{ \Illuminate\Support\Carbon::parse($date)->translatedFormat('l, d F Y') }}
      </span>
      <span style="font-size:13px;color:var(--muted-2)">{{ $students->count() }} siswa</span>
    </div>
  @endif
</form>
@endsection
