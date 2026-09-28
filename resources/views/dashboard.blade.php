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

{{-- SECTION KHUSUS WALI MURID --}}
@if ($userRole === 'wali')
<div class="stack" style="margin-top:16px;gap:16px">
  <div class="glass panel" style="border-left:4px solid var(--accent)">
    <h2 class="panel-title">
      <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M3.5 20c.5-4 2.6-6 5.5-6s5 2 5.5 6"/><circle cx="17" cy="9" r="2.5"/><path d="M16 14.5c2.5.2 4 1.8 4.5 4.5"/></svg>
      Portal Wali Murid — Peserta Didik Binaan Anda
    </h2>
    <div class="sub" style="margin-bottom:14px">Informasi kehadiran, tagihan biaya pendidikan, dan aktivitas putra/putri Anda.</div>

    @forelse ($myChildren as $child)
      <div class="glass-soft" style="padding:16px;border-radius:var(--radius-md);margin-bottom:12px">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;border-bottom:1px solid var(--line-light);padding-bottom:10px;margin-bottom:12px">
          <div>
            <b style="font-size:16px;color:var(--text)">{{ $child->full_name }}</b>
            <div style="font-size:12px;color:var(--muted);margin-top:2px">
              Kelas {{ $child->schoolClass?->name ?? '—' }} · NIS: {{ $child->nis ?? '—' }} · NISN: {{ $child->nisn ?? '—' }}
            </div>
          </div>
          <span class="badge badge-ok">Status: {{ ucfirst($child->status) }}</span>
        </div>

        <div class="two-col" style="gap:14px">
          {{-- Kehadiran Terakhir --}}
          <div>
            <div style="font-size:12.5px;font-weight:600;color:var(--text);margin-bottom:6px">📅 Kehadiran Terakhir:</div>
            <div style="display:flex;gap:6px;flex-wrap:wrap">
              @forelse ($child->attendances as $att)
                @php
                  $color = match($att->status) {
                    'hadir' => '#10b981',
                    'izin' => '#38bdf8',
                    'sakit' => '#f59e0b',
                    default => '#ef4444'
                  };
                @endphp
                <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:4px;background:{{ $color }}20;border:1px solid {{ $color }}40;font-size:11px;color:{{ $color }}">
                  {{ $att->att_date?->format('d/m') }}: <b>{{ ucfirst($att->status) }}</b>
                </span>
              @empty
                <span style="font-size:12px;color:var(--muted)">Belum ada catatan presensi minggu ini.</span>
              @endforelse
            </div>
          </div>

          {{-- Status Tagihan SPP --}}
          <div>
            <div style="font-size:12.5px;font-weight:600;color:var(--text);margin-bottom:6px">💳 Tagihan Sekolah:</div>
            <div style="display:grid;gap:6px">
              @forelse ($child->bills as $b)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 10px;background:rgba(255,255,255,0.02);border-radius:var(--radius-sm);font-size:12px">
                  <span>{{ $b->paymentType?->name }} (Rp {{ number_format($b->amount, 0, ',', '.') }})</span>
                  <span class="badge {{ $b->status === 'lunas' ? 'badge-ok' : 'badge-bad' }}">{{ strtoupper($b->status) }}</span>
                </div>
              @empty
                <span style="font-size:12px;color:var(--green)">✓ Tidak ada tunggakan tagihan.</span>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="empty">Belum ada siswa yang ditautkan dengan akun wali Anda.</div>
    @endforelse
  </div>
</div>
@endif

{{-- STAT CARDS (Untuk Admin, Guru, TU, Bendahara, Kepsek) --}}
@if ($userRole !== 'wali')
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
    <div class="stat-label">Tunggakan SPP (Terkumpul: Rp {{ number_format($stats['paid_amount'], 0, ',', '.') }})</div>
    <div class="stat-icon">
      <svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18"/></svg>
    </div>
  </div>
</div>
@endif

<div class="two-col" style="margin-top:20px">
  {{-- Pengumuman Sekolah --}}
  <div class="glass panel">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
      <h2 class="panel-title" style="margin:0">
        <svg viewBox="0 0 24 24"><path d="M4 11l12-6v14L4 13v-2z"/><path d="M16 8.5c2 .5 3 1.7 3 3.5s-1 3-3 3.5"/></svg>
        Warta &amp; Pengumuman Sekolah
      </h2>
      <a href="{{ route('announcements.index') }}" style="font-size:12px;color:var(--accent)">Lihat Semua →</a>
    </div>
    <div class="stack">
      @forelse ($stats['announcements'] as $ann)
        <div class="glass-soft ann-item">
          <div style="display:flex;align-items:center;justify-content:space-between;gap:8px">
            <h3 style="color:var(--text);font-size:14px">{{ $ann->title }}</h3>
            <span class="badge badge-blue" style="font-size:10.5px">{{ $ann->schoolClass?->name ?? 'Umum' }}</span>
          </div>
          <p style="font-size:12.5px">{{ \Illuminate\Support\Str::limit($ann->content, 120) }}</p>
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

  {{-- Akses Cepat Modul --}}
  <div class="glass panel">
    <h2 class="panel-title">
      <svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="17" rx="2.5"/><path d="M8 2.5v3M16 2.5v3M4 9.5h16"/></svg>
      Akses Cepat Modul
    </h2>
    <div class="stack">
      @if (in_array($userRole, ['admin', 'guru', 'kepsek']))
      <div class="glass-soft ann-item">
        <div style="display:flex;align-items:center;gap:12px">
          <span class="icon-chip icon-sm is-blue">
            <svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="17" rx="2.5"/><path d="M8 2.5v3M16 2.5v3M4 9.5h16"/><path d="M9 14.5l2 2 4-4"/></svg>
          </span>
          <div>
            <h3 style="font-size:14px">Presensi Siswa (Harian &amp; Leger)</h3>
            <p style="font-size:12px;color:var(--muted)">Input harian &amp; rekap bulanan kelas.</p>
          </div>
        </div>
        <div class="ann-meta" style="margin-top:4px">
          <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-ink" style="height:28px;font-size:11.5px;padding:0 12px">
            Buka Presensi →
          </a>
        </div>
      </div>
      @endif

      @if (in_array($userRole, ['admin', 'guru', 'staff_tu', 'kepsek']))
      <div class="glass-soft ann-item">
        <div style="display:flex;align-items:center;gap:12px">
          <span class="icon-chip icon-sm is-ok">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="7.5" r="3.5"/><path d="M5 20.5c.7-4.5 3.2-7 7-7s6.3 2.5 7 7"/></svg>
          </span>
          <div>
            <h3 style="font-size:14px">Presensi Pegawai (GTK)</h3>
            <p style="font-size:12px;color:var(--muted)">Check-in mandiri &amp; rekap presensi GTK.</p>
          </div>
        </div>
        <div class="ann-meta" style="margin-top:4px">
          <a href="{{ route('attendance-employee.index') }}" class="btn btn-sm" style="height:28px;font-size:11.5px;padding:0 12px">
            Presensi GTK →
          </a>
        </div>
      </div>
      @endif

      @if (in_array($userRole, ['admin', 'staff_tu', 'kepsek']))
      <div class="glass-soft ann-item">
        <div style="display:flex;align-items:center;gap:12px">
          <span class="icon-chip icon-sm">
            <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M3.5 20c.5-4 2.6-6 5.5-6s5 2 5.5 6"/></svg>
          </span>
          <div>
            <h3 style="font-size:14px">Direktori Siswa &amp; Rombel</h3>
            <p style="font-size:12px;color:var(--muted)">Pendaftaran, penempatan kelas &amp; wali.</p>
          </div>
        </div>
        <div class="ann-meta" style="margin-top:4px">
          <a href="{{ route('students.index') }}" class="btn btn-sm" style="height:28px;font-size:11.5px;padding:0 12px">
            Kelola Siswa →
          </a>
        </div>
      </div>
      @endif

      @if (in_array($userRole, ['admin', 'bendahara', 'kepsek']))
      <div class="glass-soft ann-item">
        <div style="display:flex;align-items:center;gap:12px">
          <span class="icon-chip icon-sm is-ink">
            <svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18"/></svg>
          </span>
          <div>
            <h3 style="font-size:14px">Tagihan &amp; Pembayaran SPP</h3>
            <p style="font-size:12px;color:var(--muted)">Generate tagihan &amp; cetak kuitansi resmi.</p>
          </div>
        </div>
        <div class="ann-meta" style="margin-top:4px">
          <a href="{{ route('bills.index') }}" class="btn btn-sm" style="height:28px;font-size:11.5px;padding:0 12px">
            Keuangan Siswa →
          </a>
        </div>
      </div>
      @endif

      {{-- Profil Akun --}}
      <div class="glass-soft ann-item">
        <div style="display:flex;align-items:center;gap:12px">
          <span class="icon-chip icon-sm">
            <svg viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
          </span>
          <div>
            <h3 style="font-size:14px">Profil &amp; Keamanan Akun</h3>
            <p style="font-size:12px;color:var(--muted)">Perbarui kata sandi akun Anda.</p>
          </div>
        </div>
        <div class="ann-meta" style="margin-top:4px">
          <a href="{{ route('profile.index') }}" class="btn btn-sm" style="height:28px;font-size:11.5px;padding:0 12px">
            Atur Sandi →
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
