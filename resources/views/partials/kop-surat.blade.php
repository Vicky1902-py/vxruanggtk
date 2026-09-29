@php
  $school = $school ?? (auth()->check() ? auth()->user()->school : null);
  $logoLeft = $school ? $school->logo_school : asset('img/logo.svg');
  $logoRight = $school ? $school->logo_government : asset('img/tutwuri.svg');
  $h1 = $school ? $school->getHeader1() : 'PEMERINTAH PROVINSI JAWA TIMUR';
  $h2 = $school ? $school->getHeader2() : 'DINAS PENDIDIKAN';
  $h3 = $school ? $school->getHeader3() : 'SMK / SMA NEGERI';
  $h4 = $school ? $school->getHeader4() : 'Jl. Pendidikan Nusantara No. 1 · Email: info@sekolah.sch.id';
  $npsn = $school?->npsn;
  $status = $school?->status_sekolah;
@endphp
<div class="official-kop-surat">
  <div class="kop-body">
    {{-- Logo Kiri: Lambang Sekolah --}}
    <div class="kop-logo kop-logo-left">
      <img src="{{ $logoLeft }}" alt="Logo Sekolah">
    </div>

    {{-- Teks Tengah: Hierarki Pemerintahan & Satuan Pendidikan --}}
    <div class="kop-text">
      <div class="kop-line-1">{{ $h1 }}</div>
      <div class="kop-line-2">{{ $h2 }}</div>
      <div class="kop-line-3">{{ $h3 }}</div>
      @if ($npsn)
        <div class="kop-line-npsn">NPSN: {{ $npsn }} @if($status) · Status: {{ $status }} @endif</div>
      @endif
      <div class="kop-line-4">{{ $h4 }}</div>
    </div>

    {{-- Logo Kanan: Lambang Pemprov / Pemkab / Dinas / Tut Wuri Handayani --}}
    <div class="kop-logo kop-logo-right">
      <img src="{{ $logoRight }}" alt="Logo Pemerintah / Dinas">
    </div>
  </div>
  <div class="kop-separator"></div>
</div>

<style>
.official-kop-surat {
  width: 100%;
  margin-bottom: 20px;
  background: #ffffff;
}
.kop-body {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding-bottom: 10px;
}
.kop-logo {
  width: 72px;
  height: 72px;
  flex: none;
  display: flex;
  align-items: center;
  justify-content: center;
}
.kop-logo img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  display: block;
}
.kop-text {
  flex: 1;
  text-align: center;
  color: #0f172a;
}
.kop-line-1 {
  font-size: 13px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  line-height: 1.25;
  color: #1e293b;
}
.kop-line-2 {
  font-size: 14px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  line-height: 1.25;
  color: #0f172a;
}
.kop-line-3 {
  font-size: 17px;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  line-height: 1.3;
  color: #0284c7;
  margin: 2px 0;
}
.kop-line-npsn {
  font-size: 11px;
  font-weight: 600;
  color: #475569;
  letter-spacing: 0.4px;
}
.kop-line-4 {
  font-size: 11px;
  color: #475569;
  line-height: 1.35;
  margin-top: 3px;
}
.kop-separator {
  border-bottom: 3.5px double #0f172a;
  margin-top: 4px;
  margin-bottom: 8px;
}

@media print {
  .official-kop-surat {
    background: transparent !important;
  }
  .kop-line-3 {
    color: #000000 !important;
  }
  .kop-separator {
    border-bottom: 3.5px double #000000 !important;
  }
}
</style>
