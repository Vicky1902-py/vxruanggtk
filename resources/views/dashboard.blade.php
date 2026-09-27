@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="page-head">
  <div>
    <h1>Selamat datang, {{ auth()->user()->username }} 👋</h1>
    <div class="sub">{{ $school->name }} · Tahun Ajaran {{ optional($school->academicYears()->where('is_active', true)->first())->year_label ?? '—' }}</div>
  </div>
  <span class="badge badge-ink">{{ auth()->user()->role?->name }}</span>
</div>

<div class="stat-grid">
  <div class="glass stat">
    <div class="count-pill"><strong class="count-up" data-count="{{ $stats['students'] }}">0</strong><small>Siswa</small></div>
    <div class="stat-label">Total siswa terdaftar</div>
    <div class="stat-icon"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M3.5 20c.5-4 2.6-6 5.5-6s5 2 5.5 6"/><circle cx="17" cy="9" r="2.5"/><path d="M16 14.5c2.5.2 4 1.8 4.5 4.5"/></svg></div>
  </div>
  <div class="glass stat">
    <div class="count-pill"><strong class="count-up" data-count="{{ $stats['classes'] }}">0</strong><small>Kelas</small></div>
    <div class="stat-label">Kelas aktif tahun ini</div>
    <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M3 9l9-6 9 6"/><path d="M5 9v9a2 2 0 002 2h10a2 2 0 002-2V9"/></svg></div>
  </div>
  <div class="glass stat">
    <div class="count-pill"><strong class="count-up" data-count="{{ $stats['employees'] }}">0</strong><small>Pegawai</small></div>
    <div class="stat-label">Guru &amp; tenaga kependidikan</div>
    <div class="stat-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="7.5" r="3.5"/><path d="M5 20.5c.7-4.5 3.2-7 7-7s6.3 2.5 7 7"/></svg></div>
  </div>
  <div class="glass stat">
    <div class="count-pill"><strong>Rp <span class="count-up" data-count="{{ (int) $stats['unpaid_amount'] }}">0</span></strong><small>{{ $stats['unpaid_bills'] }} tagihan</small></div>
    <div class="stat-label">Tunggakan belum lunas</div>
    <div class="stat-icon"><svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18"/></svg></div>
  </div>
</div>

<div class="two-col" style="margin-top:18px">
  <div class="glass panel">
    <h2 class="panel-title">
      <svg viewBox="0 0 24 24"><path d="M4 11l12-6v14L4 13v-2z"/><path d="M16 8.5c2 .5 3 1.7 3 3.5s-1 3-3 3.5"/></svg>
      Pengumuman Terbaru
    </h2>
    <div class="stack">
      @forelse ($stats['announcements'] as $ann)
        <div class="glass-soft ann-item">
          <h3>{{ $ann->title }}</h3>
          <p>{{ \Illuminate\Support\Str::limit($ann->content, 110) }}</p>
          <div class="ann-meta">
            <span class="badge">{{ $ann->schoolClass?->name ?? 'Seluruh Sekolah' }}</span>
            <span>{{ $ann->published_at?->translatedFormat('d M Y') }}</span>
          </div>
        </div>
      @empty
        <div class="empty">Belum ada pengumuman.</div>
      @endforelse
    </div>
  </div>

  <div class="glass panel">
    <h2 class="panel-title">
      <svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="17" rx="2.5"/><path d="M8 2.5v3M16 2.5v3M4 9.5h16"/></svg>
      Akses Cepat
    </h2>
    <div class="stack">
      @php $userRole = auth()->user()?->role?->name; @endphp

      @if (in_array($userRole, ['admin', 'guru']))
      <div class="glass-soft ann-item">
        <h3 class="with-chip"><span class="icon-chip icon-sm is-blue"><svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="17" rx="2.5"/><path d="M8 2.5v3M16 2.5v3M4 9.5h16"/><path d="M9 14.5l2 2 4-4"/></svg></span>Presensi Siswa</h3>
        <p>Input kehadiran harian per kelas — hadir, izin, sakit, alpa.</p>
        <div class="ann-meta"><a href="{{ route('attendance.index') }}">Buka modul →</a></div>
      </div>
      @endif

      @if (in_array($userRole, ['admin', 'staff_tu']))
      <div class="glass-soft ann-item">
        <h3 class="with-chip"><span class="icon-chip icon-sm"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M3.5 20c.5-4 2.6-6 5.5-6s5 2 5.5 6"/></svg></span>Data Siswa</h3>
        <p>Kelola data siswa, kelas, dan status keaktifan.</p>
        <div class="ann-meta"><a href="{{ route('students.index') }}">Buka modul →</a></div>
      </div>
      @endif

      @if ($userRole === 'admin')
      <div class="glass-soft ann-item">
        <h3 class="with-chip"><span class="icon-chip icon-sm is-ink"><svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18"/></svg></span>Tagihan &amp; Pembayaran</h3>
        <p>Generate tagihan per kelas dan catat pembayaran siswa.</p>
        <div class="ann-meta"><a href="{{ route('bills.index') }}">Buka modul →</a></div>
      </div>
      <div class="glass-soft ann-item">
        <h3 class="with-chip"><span class="icon-chip icon-sm is-ok"><svg viewBox="0 0 24 24"><path d="M4 11l12-6v14L4 13v-2z"/><path d="M16 8.5c2 .5 3 1.7 3 3.5s-1 3-3 3.5"/></svg></span>Pengumuman</h3>
        <p>Kirim informasi ke seluruh sekolah atau kelas tertentu.</p>
        <div class="ann-meta"><a href="{{ route('announcements.index') }}">Buka modul →</a></div>
      </div>
      @endif
    </div>
  </div>
</div>
@endsection
