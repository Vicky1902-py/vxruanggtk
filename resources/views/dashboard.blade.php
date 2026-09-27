@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="page-head">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <span class="cs-pill" style="font-size:11px;padding:3px 12px">
        <span class="dot"></span>
        {{ $school->name }}
      </span>
      <span style="font-size:12px;color:var(--muted)">
        TA {{ optional($school->academicYears()->where('is_active', true)->first())->year_label ?? '2026/2027' }}
      </span>
    </div>
    <h1>
      Selamat datang di <span class="brand-ruanggtk"><span class="brand-ruanggtk-text" style="font-size:27px">Ruang<span class="gtk-tag">GTK</span></span><span class="brand-beam"></span></span>, {{ auth()->user()->username }} 👋
    </h1>
    <div class="sub">Ikhtisar telemetri GTK, kehadiran peserta didik, dan operasional sekolah hari ini.</div>
  </div>
  <div style="display:flex;align-items:center;gap:10px">
    <span class="badge badge-ink" style="padding:4px 14px;font-size:12.5px">{{ strtoupper(auth()->user()->role?->name ?? 'User') }}</span>
  </div>
</div>

<div class="stat-grid" style="margin-top:8px">
  <div class="glass stat">
    <div class="count-pill">
      <strong class="count-up" data-count="{{ $stats['students'] }}">0</strong>
      <small>Peserta Didik</small>
    </div>
    <div class="stat-label">Total siswa terdaftar aktif</div>
    <div class="stat-icon">
      <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M3.5 20c.5-4 2.6-6 5.5-6s5 2 5.5 6"/><circle cx="17" cy="9" r="2.5"/><path d="M16 14.5c2.5.2 4 1.8 4.5 4.5"/></svg>
    </div>
  </div>

  <div class="glass stat">
    <div class="count-pill">
      <strong class="count-up" data-count="{{ $stats['classes'] }}">0</strong>
      <small>Rombel Kelas</small>
    </div>
    <div class="stat-label">Kelas aktif tahun ajaran ini</div>
    <div class="stat-icon">
      <svg viewBox="0 0 24 24"><path d="M3 9l9-6 9 6"/><path d="M5 9v9a2 2 0 002 2h10a2 2 0 002-2V9"/></svg>
    </div>
  </div>

  <div class="glass stat">
    <div class="count-pill">
      <strong class="count-up" data-count="{{ $stats['employees'] }}">0</strong>
      <small>Tenaga GTK</small>
    </div>
    <div class="stat-label">Guru &amp; staf kependidikan</div>
    <div class="stat-icon">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="7.5" r="3.5"/><path d="M5 20.5c.7-4.5 3.2-7 7-7s6.3 2.5 7 7"/></svg>
    </div>
  </div>

  <div class="glass stat">
    <div class="count-pill">
      <strong>Rp <span class="count-up" data-count="{{ (int) $stats['unpaid_amount'] }}">0</span></strong>
      <small>{{ $stats['unpaid_bills'] }} belum lunas</small>
    </div>
    <div class="stat-label">Total tagihan terutang</div>
    <div class="stat-icon">
      <svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18"/></svg>
    </div>
  </div>
</div>

<div class="two-col" style="margin-top:20px">
  <div class="glass panel">
    <h2 class="panel-title">
      <svg viewBox="0 0 24 24"><path d="M4 11l12-6v14L4 13v-2z"/><path d="M16 8.5c2 .5 3 1.7 3 3.5s-1 3-3 3.5"/></svg>
      Warta &amp; Pengumuman Sekolah
    </h2>
    <div class="stack">
      @forelse ($stats['announcements'] as $ann)
        <div class="glass-soft ann-item">
          <div style="display:flex;align-items:center;justify-content:space-between;gap:8px">
            <h3 style="color:var(--text)">{{ $ann->title }}</h3>
            <span class="badge badge-blue" style="font-size:11px">{{ $ann->schoolClass?->name ?? 'Umum' }}</span>
          </div>
          <p>{{ \Illuminate\Support\Str::limit($ann->content, 130) }}</p>
          <div class="ann-meta" style="margin-top:4px">
            <span style="display:inline-flex;align-items:center;gap:4px">
              📅 {{ $ann->published_at?->translatedFormat('d F Y') }}
            </span>
          </div>
        </div>
      @empty
        <div class="empty">Belum ada pengumuman untuk sekolah Anda.</div>
      @endforelse
    </div>
  </div>

  <div class="glass panel">
    <h2 class="panel-title">
      <svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="17" rx="2.5"/><path d="M8 2.5v3M16 2.5v3M4 9.5h16"/></svg>
      Akses Cepat Modul
    </h2>
    <div class="stack">
      @php $userRole = auth()->user()?->role?->name; @endphp

      @if (in_array($userRole, ['admin', 'guru']))
      <div class="glass-soft ann-item">
        <div style="display:flex;align-items:center;gap:12px">
          <span class="icon-chip icon-sm is-blue">
            <svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="17" rx="2.5"/><path d="M8 2.5v3M16 2.5v3M4 9.5h16"/><path d="M9 14.5l2 2 4-4"/></svg>
          </span>
          <div>
            <h3 style="font-size:14.5px">Presensi Siswa Hari Ini</h3>
            <p style="font-size:12.5px;color:var(--muted)">Input &amp; rekap presensi kelas (H/I/S/A).</p>
          </div>
        </div>
        <div class="ann-meta" style="margin-top:4px">
          <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-ink" style="height:30px;font-size:12px;padding:0 14px">
            Buka Presensi →
          </a>
        </div>
      </div>
      @endif

      @if (in_array($userRole, ['admin', 'staff_tu']))
      <div class="glass-soft ann-item">
        <div style="display:flex;align-items:center;gap:12px">
          <span class="icon-chip icon-sm">
            <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M3.5 20c.5-4 2.6-6 5.5-6s5 2 5.5 6"/></svg>
          </span>
          <div>
            <h3 style="font-size:14.5px">Direktori Siswa &amp; Rombel</h3>
            <p style="font-size:12.5px;color:var(--muted)">Pendaftaran, penempatan kelas &amp; wali.</p>
          </div>
        </div>
        <div class="ann-meta" style="margin-top:4px">
          <a href="{{ route('students.index') }}" class="btn btn-sm" style="height:30px;font-size:12px;padding:0 14px">
            Kelola Siswa →
          </a>
        </div>
      </div>
      @endif

      @if ($userRole === 'admin')
      <div class="glass-soft ann-item">
        <div style="display:flex;align-items:center;gap:12px">
          <span class="icon-chip icon-sm is-ink">
            <svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18"/></svg>
          </span>
          <div>
            <h3 style="font-size:14.5px">Tagihan &amp; Pembayaran SPP</h3>
            <p style="font-size:12.5px;color:var(--muted)">Generate massal &amp; rekap mutasi bayar.</p>
          </div>
        </div>
        <div class="ann-meta" style="margin-top:4px">
          <a href="{{ route('bills.index') }}" class="btn btn-sm" style="height:30px;font-size:12px;padding:0 14px">
            Keuangan Siswa →
          </a>
        </div>
      </div>

      <div class="glass-soft ann-item">
        <div style="display:flex;align-items:center;gap:12px">
          <span class="icon-chip icon-sm is-ok">
            <svg viewBox="0 0 24 24"><path d="M4 11l12-6v14L4 13v-2z"/><path d="M16 8.5c2 .5 3 1.7 3 3.5s-1 3-3 3.5"/></svg>
          </span>
          <div>
            <h3 style="font-size:14.5px">Publikasi Pengumuman</h3>
            <p style="font-size:12.5px;color:var(--muted)">Rilis pengumuman kelas &amp; sekolah.</p>
          </div>
        </div>
        <div class="ann-meta" style="margin-top:4px">
          <a href="{{ route('announcements.index') }}" class="btn btn-sm" style="height:30px;font-size:12px;padding:0 14px">
            Tulis Pengumuman →
          </a>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>
@endsection
