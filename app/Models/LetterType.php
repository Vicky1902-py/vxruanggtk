<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LetterType extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'code',
        'category',
        'classification_code',
        'numbering_format',
        'padding_digits',
        'default_template_body',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'padding_digits' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function letters(): HasMany
    {
        return $this->hasMany(Letter::class);
    }

    /**
     * Inisialisasi jenis surat & template bawaan standar untuk sekolah jika belum ada.
     */
    public static function seedDefaultTemplatesForSchool(School $school): void
    {
        $defaults = [
            [
                'name' => 'Surat Keputusan (SK) Kepala Sekolah',
                'code' => 'SK',
                'category' => 'sk',
                'classification_code' => '800',
                'numbering_format' => '{KODE}/{NOMOR}/SK-{SEKOLAH}/{BULAN_ROMAWI}/{TAHUN}',
                'padding_digits' => 3,
                'default_template_body' => "<p style=\"text-align:center;font-weight:bold;margin-bottom:14px;\">KEPUTUSAN KEPALA {nama_sekolah}<br>Nomor: {nomor_surat}<br><br>TENTANG<br>{perihal}</p>

<p><b>MEMPERHATIKAN :</b><br>
Bahwa dalam rangka kelancaran proses pembelajaran dan administrasi pendidikan pada {nama_sekolah} Tahun Pelajaran berjalan, dipandang perlu menetapkan keputusan ini.</p>

<p><b>MENGINGAT :</b><br>
1. Undang-Undang Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional.<br>
2. Peraturan Pemerintah Republik Indonesia tentang Standar Nasional Pendidikan.<br>
3. Kalender Pendidikan dan Program Kerja {nama_sekolah}.</p>

<p style=\"text-align:center;font-weight:bold;margin:16px 0;\">MEMUTUSKAN</p>

<p><b>MENETAPKAN :</b></p>
<p><b>PERTAMA :</b> Menetapkan pembagian tugas kepada nama yang tercantum dalam lampiran keputusan ini untuk melaksanakan tugas pokok dan fungsinya dengan penuh tanggung jawab.</p>
<p><b>KEDUA :</b> Segala biaya yang timbul akibat pelaksanaan keputusan ini dibebankan pada anggaran yang sesuai.</p>
<p><b>KETIGA :</b> Keputusan ini berlaku sejak tanggal ditetapkan dengan ketentuan apabila terdapat kekeliruan di kemudian hari akan diadakan perbaikan sebagaimana mestinya.</p>",
            ],
            [
                'name' => 'Surat Tugas (ST) GTK / Guru',
                'code' => 'ST',
                'category' => 'surat_keluar',
                'classification_code' => '800',
                'numbering_format' => '{KODE}/{NOMOR}/ST-GTK/{BULAN_ROMAWI}/{TAHUN}',
                'padding_digits' => 3,
                'default_template_body' => "<p style=\"text-align:center;font-weight:bold;margin-bottom:16px;\">SURAT TUGAS<br>Nomor: {nomor_surat}</p>

<p>Yang bertanda tangan di bawah ini Kepala {nama_sekolah}, dengan ini menugaskan kepada:</p>

<table style=\"width:100%;margin:12px 0;font-size:13px;line-height:1.6;\">
  <tr><td style=\"width:160px;\">Nama</td><td style=\"width:10px;\">:</td><td><b>{nama_gtk}</b></td></tr>
  <tr><td>NIP / NUPTK</td><td>:</td><td>{nip_gtk}</td></tr>
  <tr><td>Jabatan</td><td>:</td><td>{jabatan_gtk}</td></tr>
  <tr><td>Unit Kerja</td><td>:</td><td>{nama_sekolah}</td></tr>
</table>

<p>Untuk melaksanakan tugas:</p>
<p style=\"padding-left:14px;\"><b>{perihal}</b></p>

<table style=\"width:100%;margin:12px 0;font-size:13px;line-height:1.6;\">
  <tr><td style=\"width:160px;\">Hari / Tanggal</td><td style=\"width:10px;\">:</td><td>{tanggal_pelaksanaan}</td></tr>
  <tr><td>Waktu</td><td>:</td><td>{waktu_pelaksanaan}</td></tr>
  <tr><td>Tempat</td><td>:</td><td>{tempat_pelaksanaan}</td></tr>
</table>

<p>Demikian surat tugas ini dibuat untuk dilaksanakan dengan penuh dedikasi dan rasa tanggung jawab, serta memberikan laporan setelah selesai melaksanakan tugas.</p>",
            ],
            [
                'name' => 'Surat Keterangan Aktif Siswa',
                'code' => 'KET-AKTIF',
                'category' => 'surat_keluar',
                'classification_code' => '422',
                'numbering_format' => '{KODE}/{NOMOR}/KET-AKTIF/{SEKOLAH}/{BULAN_ROMAWI}/{TAHUN}',
                'padding_digits' => 3,
                'default_template_body' => "<p style=\"text-align:center;font-weight:bold;margin-bottom:16px;\">SURAT KETERANGAN AKTIF BELAJAR<br>Nomor: {nomor_surat}</p>

<p>Yang bertanda tangan di bawah ini Kepala {nama_sekolah}, menerangkan dengan sesungguhnya bahwa:</p>

<table style=\"width:100%;margin:12px 0;font-size:13px;line-height:1.6;\">
  <tr><td style=\"width:170px;\">Nama Siswa</td><td style=\"width:10px;\">:</td><td><b>{nama_siswa}</b></td></tr>
  <tr><td>NIS / NISN</td><td>:</td><td>{nis_siswa} / {nisn_siswa}</td></tr>
  <tr><td>Kelas / Rombel</td><td>:</td><td>{kelas_siswa}</td></tr>
  <tr><td>Tempat, Tgl Lahir</td><td>:</td><td>{ttl_siswa}</td></tr>
  <tr><td>Nama Orang Tua / Wali</td><td>:</td><td>{nama_ortu}</td></tr>
  <tr><td>Alamat Tempat Tinggal</td><td>:</td><td>{alamat_siswa}</td></tr>
</table>

<p>Adalah benar-benar peserta didik yang terdaftar aktif mengikuti proses kegiatan belajar mengajar pada {nama_sekolah} pada Tahun Pelajaran berjalan dan memiliki kelakuan baik.</p>

<p>Surat keterangan ini dibuat dengan sebenarnya untuk dipergunakan sebagai kelengkapan administrasi pengurusan: <b>{tujuan_keterangan}</b>.</p>

<p>Demikian surat keterangan ini kami berikan agar dapat dipergunakan sebagaimana mestinya.</p>",
            ],
            [
                'name' => 'Surat Keterangan Berkelakuan Baik',
                'code' => 'KET-BAIK',
                'category' => 'surat_keluar',
                'classification_code' => '422',
                'numbering_format' => '{KODE}/{NOMOR}/KET-BAIK/{SEKOLAH}/{BULAN_ROMAWI}/{TAHUN}',
                'padding_digits' => 3,
                'default_template_body' => "<p style=\"text-align:center;font-weight:bold;margin-bottom:16px;\">SURAT KETERANGAN BERKELAKUAN BAIK<br>Nomor: {nomor_surat}</p>

<p>Kepala {nama_sekolah} dengan ini menerangkan bahwa:</p>

<table style=\"width:100%;margin:12px 0;font-size:13px;line-height:1.6;\">
  <tr><td style=\"width:170px;\">Nama Siswa</td><td style=\"width:10px;\">:</td><td><b>{nama_siswa}</b></td></tr>
  <tr><td>NIS / NISN</td><td>:</td><td>{nis_siswa} / {nisn_siswa}</td></tr>
  <tr><td>Kelas</td><td>:</td><td>{kelas_siswa}</td></tr>
  <tr><td>Alamat</td><td>:</td><td>{alamat_siswa}</td></tr>
</table>

<p>Berdasarkan catatan kesiswaan dan bimbingan konseling pada {nama_sekolah}, peserta didik tersebut selama menjadi siswa di sekolah kami menunjukkan sikap, perilaku, budi pekerti yang baik, serta tidak pernah melanggar tata tertib sekolah yang berat maupun terlibat narkoba/tindak pidana.</p>

<p>Demikian surat keterangan ini diberikan kepada yang bersangkutan untuk dapat dipergunakan sebagaimana perlunya.</p>",
            ],
            [
                'name' => 'Surat Undangan Dinas / Rapat Komite',
                'code' => 'UND',
                'category' => 'surat_keluar',
                'classification_code' => '005',
                'numbering_format' => '{KODE}/{NOMOR}/UND/{SEKOLAH}/{BULAN_ROMAWI}/{TAHUN}',
                'padding_digits' => 3,
                'default_template_body' => "<p>Kepada Yth.<br><b>{tujuan_surat}</b><br>di Tempat</p>

<p>Dengan hormat,</p>
<p>Sehubungan dengan pelaksanaan program kerja sekolah dan agenda evaluasi kegiatan belajar mengajar pada {nama_sekolah}, bersama ini kami mengharap kehadiran Bapak/Ibu pada:</p>

<table style=\"width:100%;margin:12px 0;font-size:13px;line-height:1.6;\">
  <tr><td style=\"width:160px;\">Hari / Tanggal</td><td style=\"width:10px;\">:</td><td>{hari_tanggal}</td></tr>
  <tr><td>Waktu</td><td>:</td><td>{waktu_acara} WIB s.d. Selesai</td></tr>
  <tr><td>Tempat</td><td>:</td><td>{tempat_acara}</td></tr>
  <tr><td>Acara</td><td>:</td><td><b>{perihal}</b></td></tr>
</table>

<p>Mengingat pentingnya acara tersebut, kami mohon Bapak/Ibu hadir tepat pada waktunya. Atas perhatian dan kerja sama yang baik, kami sampaikan terima kasih.</p>",
            ],
            [
                'name' => 'Surat Panggilan Orang Tua / Wali Murid',
                'code' => 'SP-WALI',
                'category' => 'surat_keluar',
                'classification_code' => '422',
                'numbering_format' => '{KODE}/{NOMOR}/SP/{SEKOLAH}/{BULAN_ROMAWI}/{TAHUN}',
                'padding_digits' => 3,
                'default_template_body' => "<p>Kepada Yth.<br>Orang Tua / Wali dari Siswa: <b>{nama_siswa}</b> ({kelas_siswa})<br>di Tempat</p>

<p>Dengan hormat,</p>
<p>Dalam rangka pembinaan dan koordinasi perkembangan akademik serta kedisiplinan putra/putri Bapak/Ibu di {nama_sekolah}, kami mengharap kehadiran Bapak/Ibu untuk hadir di sekolah pada:</p>

<table style=\"width:100%;margin:12px 0;font-size:13px;line-height:1.6;\">
  <tr><td style=\"width:160px;\">Hari / Tanggal</td><td style=\"width:10px;\">:</td><td>{hari_tanggal}</td></tr>
  <tr><td>Pukul</td><td>:</td><td>{waktu_acara} WIB</td></tr>
  <tr><td>Tempat</td><td>:</td><td>Ruang Bimbingan Konseling (BK) / Ruang TU {nama_sekolah}</td></tr>
  <tr><td>Menemui</td><td>:</td><td>Guru BK / Wali Kelas</td></tr>
  <tr><td>Keperluan</td><td>:</td><td>{perihal}</td></tr>
</table>

<p>Demikian surat panggilan ini kami sampaikan, atas kehadiran dan kerja samanya kami ucapkan terima kasih.</p>",
            ],
        ];

        foreach ($defaults as $item) {
            self::firstOrCreate(
                [
                    'school_id' => $school->id,
                    'code' => $item['code'],
                ],
                $item
            );
        }
    }
}
