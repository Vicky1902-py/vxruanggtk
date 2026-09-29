<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Letter;
use App\Models\LetterType;
use App\Models\School;
use App\Models\Student;
use Carbon\Carbon;

class LetterNumberService
{
    /**
     * Konversi angka bulan (1-12) ke angka Romawi.
     */
    public static function toRomanMonth(int $month): string
    {
        $romans = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        return $romans[$month] ?? 'I';
    }

    /**
     * Dapatkan inisial / kode singkat sekolah.
     */
    public static function getSchoolCode(School $school): string
    {
        if (!empty($school->subdomain)) {
            return strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $school->subdomain));
        }

        // Ambil huruf depan dari tiap kata pada nama sekolah
        $words = explode(' ', $school->name);
        $acronym = '';
        foreach ($words as $w) {
            if (!empty($w)) {
                $acronym .= strtoupper($w[0]);
            }
        }

        return $acronym ?: 'SEKOLAH';
    }

    /**
     * Deteksi dan hitung nomor urut berikutnya secara cerdas.
     * Mengunci record (jika dalam transaksi) untuk mencegah nomor ganda.
     */
    public function getNextSequence(School $school, string $category, int $year, bool $lock = false): int
    {
        $query = Letter::where('school_id', $school->id)
            ->where('category', $category)
            ->where('year', $year);

        if ($lock) {
            $query->lockForUpdate();
        }

        $max = $query->max('sequence_number');

        return ($max ?? 0) + 1;
    }

    /**
     * Generate nomor surat lengkap terformat berdasarkan jenis surat dan tanggal.
     */
    public function generate(School $school, LetterType $type, ?string $date = null, ?int $manualSequence = null): array
    {
        $dt = $date ? Carbon::parse($date) : now();
        $year = (int) $dt->format('Y');
        $month = (int) $dt->format('n');

        $sequence = $manualSequence ?: $this->getNextSequence($school, $type->category, $year);
        $paddedNumber = str_pad((string) $sequence, $type->padding_digits ?: 3, '0', STR_PAD_LEFT);

        $format = $type->numbering_format ?: '{KODE}/{NOMOR}/{SEKOLAH}/{BULAN_ROMAWI}/{TAHUN}';

        $replacements = [
            '{NOMOR}'        => $paddedNumber,
            '{KODE}'         => $type->classification_code ?: '421',
            '{JENIS}'        => $type->code ?: 'SRT',
            '{SEKOLAH}'      => self::getSchoolCode($school),
            '{BULAN_ROMAWI}' => self::toRomanMonth($month),
            '{BULAN}'        => str_pad((string) $month, 2, '0', STR_PAD_LEFT),
            '{TAHUN}'        => (string) $year,
        ];

        $referenceNumber = strtr($format, $replacements);

        return [
            'sequence_number'  => $sequence,
            'year'             => $year,
            'reference_number' => $referenceNumber,
            'category'         => $type->category,
        ];
    }

    /**
     * Gantikan variabel placeholder pada draf teks surat dengan data asli.
     */
    public function parseTemplatePlaceholders(
        string $content,
        School $school,
        array $letterMeta = [],
        ?Student $student = null,
        ?Employee $employee = null
    ): string {
        $date = isset($letterMeta['letter_date']) ? Carbon::parse($letterMeta['letter_date']) : now();

        $tags = [
            // Identitas Lembaga
            '{nama_sekolah}'    => $school->name,
            '{npsn}'            => $school->npsn ?? '—',
            '{alamat_sekolah}'  => $school->address ?? '—',
            '{kota}'            => $school->city ?? '—',
            '{provinsi}'        => $school->province ?? '—',
            '{nama_kepsek}'     => $school->principal_name ?? 'Kepala Sekolah',
            '{nip_kepsek}'      => $school->principal_nip ? 'NIP. ' . $school->principal_nip : '—',
            '{jabatan_kepsek}'  => $school->principal_title ?: 'Kepala Sekolah',

            // Meta Surat
            '{nomor_surat}'     => $letterMeta['reference_number'] ?? '.../..../....',
            '{perihal}'         => $letterMeta['subject'] ?? '',
            '{tujuan_surat}'    => $letterMeta['recipient'] ?? '',
            '{tanggal_surat}'   => $date->translatedFormat('d F Y'),
            '{hari_tanggal}'    => $date->translatedFormat('l, d F Y'),

            // Siswa (Jika ada)
            '{nama_siswa}'      => $student?->full_name ?? '[Nama Siswa]',
            '{nis_siswa}'       => $student?->nis ?? '—',
            '{nisn_siswa}'      => $student?->nisn ?? '—',
            '{kelas_siswa}'     => $student?->schoolClass ? 'Kelas ' . $student->schoolClass->name : '—',
            '{ttl_siswa}'       => ($student?->birth_place ? $student->birth_place . ', ' : '') . ($student?->birth_date ? Carbon::parse($student->birth_date)->translatedFormat('d F Y') : '—'),
            '{nama_ortu}'       => $student?->guardian?->full_name ?? ($student?->parent_name ?? '—'),
            '{alamat_siswa}'    => $student?->address ?? ($school->city ?? '—'),
            '{tujuan_keterangan}' => $letterMeta['subject'] ?? 'Persyaratan Administrasi',

            // GTK / Guru (Jika ada)
            '{nama_gtk}'        => $employee?->full_name ?? '[Nama Guru/GTK]',
            '{nip_gtk}'         => $employee?->nip ?? '—',
            '{jabatan_gtk}'     => $employee?->position?->name ?? 'Tenaga Pendidik / GTK',

            // Parameter Opsional Kegiatan / Undangan
            '{tanggal_pelaksanaan}' => $date->translatedFormat('d F Y'),
            '{waktu_pelaksanaan}'   => '08:00 WIB s.d. Selesai',
            '{tempat_pelaksanaan}'  => $school->name,
            '{waktu_acara}'         => '08:30',
            '{tempat_acara}'        => 'Aula / Ruang Pertemuan ' . $school->name,
        ];

        return strtr($content, $tags);
    }
}
