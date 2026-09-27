# 🚀 Deploy Ruang GTK ke ruanggtk.my.id (cPanel Rumahweb)

Aplikasi: Laravel 12 multi-tenant · Deploy otomatis via Git Version Control.
Dokumen root domain Anda: **`/public_html/ruanggtk.my.id`** (repo di-clone ke sini).

---

## Ringkasan Arsitektur Deploy

```
GitHub (Vicky1902-py/vxruanggtk)
        │  git pull (cPanel Git™)
        ▼
/home/chaf2674/public_html/ruanggtk.my.id   ← repo lengkap
        │  .htaccess shield → semua request
        ▼
   …/public/index.php                        ← front controller
        │
        ▼
MySQL: chaf2674_ruanggtk                     ← database produksi
```

Setiap klik **Deploy** di cPanel menjalankan otomatis (`​.cpanel.yml`):
`composer install` → salin `.env` (jika belum ada) → `key:generate` (jika perlu)
→ `migrate` → seed produksi (idempoten) → `storage:link` → cache optimasi.

---

## Langkah 1 — Database (cPanel → **MySQL® Databases**)

1. *Create New Database*: nama `ruanggtk` → jadi **`chaf2674_ruanggtk`**
2. *MySQL Users*: buat user `gtkadmin` + password kuat → jadi **`chaf2674_gtkadmin`**
   📋 **Catat password ini** — diperlukan di Langkah 3.
3. *Add User To Database*: `chaf2674_gtkadmin` → `chaf2674_ruanggtk` → **ALL PRIVILEGES**

## Langkah 2 — PHP (cPanel → **MultiPHP Manager**)

Set `ruanggtk.my.id` ke **PHP 8.2+**. Cek juga di **Terminal**: `php -v` dan
`which composer composer2` (catat mana yang tersedia).

## Langkah 3 — Siapkan `.env` (cPanel → **File Manager**)

> ⚠️ Lakukan **SEBELUM** deploy pertama supaya `migrate` langsung sukses.

1. Buat folder/isi repo dulu? Repo akan muncul setelah Langkah 4. Jadi:
   **Lakukan Langkah 4 dulu** (clone repo), lalu kembali ke sini.
2. Di folder `/public_html/ruanggtk.my.id`: klik kanan `.env.production.example`
   → **Copy** → rename hasil copy menjadi **`.env`**
3. Edit `.env`:
   ```env
   DB_PASSWORD=<password_dari_langkah_1>
   ```
   (Nama DB, user, dan APP_URL sudah benar dari template. `APP_KEY` biarkan
   kosong — diisi otomatis saat deploy.)

## Langkah 4 — Clone Repo (cPanel → **Git™ Version Control**)

1. **Create Repository**:
   - Clone URL: `https://github.com/Vicky1902-py/vxruanggtk.git`
   - Repo Path: **`/home/chaf2674/public_html/ruanggtk.my.id`** ← sesuai docroot
2. **Create** → tunggu tarikan selesai.
3. Jika diminta autentikasi: tab *Manage* → salin SSH key cPanel → GitHub repo →
   *Settings → Deploy keys → Add deploy key*.

## Langkah 5 — Deploy Pertama

1. Masih di Git™ Version Control → pilih repo → tab **Pull or Deploy**
2. **Deploy from a repository** → tunggu semua task hijau.
3. Jika task `composer` gagal (command not found): **Terminal**:
   ```bash
   cd ~/public_html/ruanggtk.my.id
   composer2 install --no-dev --optimize-autoloader --no-interaction
   ```
   lalu klik Deploy sekali lagi.

## Langkah 6 — Amankan Akun

Seed produksi membuat akun platform:

| Akun | Login di | Username | Password (AWAL) |
|---|---|---|---|
| Super admin | `/super` | `god` | `godmode123` ⚠️ |

1. Buka **https://ruanggtk.my.id/super** → login
2. Menu **Super Admin** → **Reset PW** → ganti password kuat
3. Nanti saat sekolah nyata didaftarkan (menu *Daftar Sekolah Baru*), password
   admin sekolah langsung diisi password kuat oleh Anda.

## Langkah 7 — Buat Sekolah Pertama

Di panel global → form **Daftar Sekolah Baru**:
- Nama + subdomain (mis. `sman1` → login sekolah pakai subdomain ini)
- Username & password admin sekolah
Sekolah muncul otomatis di landing/dashboard; admin sekolah login di `/masuk`.

## Deploy Berikutnya (alur harian)

```
Edit (lokal/Codebuff) → git commit → git push
Anda: cPanel → Git™ Version Control → Deploy from a repository (1 klik)
```

Migrasi baru & perubahan kode langsung jalan — tidak perlu langkah manual.

---

## Troubleshooting

| Gejala | Solusi |
|---|---|
| 500 putih | Terminal: `php artisan config:clear` lalu cek `storage/logs/laravel.log` |
| `Access denied for user` | `DB_PASSWORD` belum diisi / user belum diberi ALL PRIVILEGES |
| CSS/JS tampak lama | Hard refresh (Ctrl+Shift+R) — versi otomatis via `?v=` |
| `419 Page Expired` | Pastikan `APP_URL=https://ruanggtk.my.id` (pakai https) |
| `composer` tidak ada | Gunakan `composer2` (CloudLinux) |
| Halaman blank setelah edit .env | Terminal: `php artisan config:cache` ulang |

## Catatan Keamanan

- `.env` & `.git` diblokir web oleh `.htaccess` (FilesMatch dotfiles + RedirectMatch 404 .git)
- Hanya isi folder `public/` yang tersaji ke pengunjung
- Semua request ditulis ke `storage/logs` — pantas dipantau berkala
