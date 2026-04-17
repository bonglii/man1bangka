# 🏫 MAN 1 Bangka — Website Kegiatan Siswa

Website resmi portal kegiatan siswa **Madrasah Aliyah Negeri 1 Bangka** dengan Panel Admin lengkap berbasis PHP + MySQL.

> **Dikembangkan oleh:** Estefania — 2322500043, ISB Atma Luhur (2026)

---

## 📋 Persyaratan

| Komponen         | Versi Minimum             |
| ---------------- | ------------------------- |
| PHP              | 7.4+ (disarankan 8.x)     |
| MySQL / MariaDB  | 5.7+ / 10.3+              |
| Web Server Lokal | XAMPP, WAMP, atau Laragon |

---

## ⚙️ Cara Setup

### 1. Letakkan Folder

Salin folder `man1bangka/` ke direktori web server Anda:

- **XAMPP** : `C:/xampp/htdocs/man1bangka/`
- **Laragon**: `C:/laragon/www/man1bangka/`
- **WAMP** : `C:/wamp64/www/man1bangka/`

### 2. Import Database

1. Buka **phpMyAdmin** di browser
2. Buat database baru bernama `man1bangka`
3. Import file `database/man1bangka.sql`

### 3. Edit Konfigurasi

Edit file `php/config.php` dan sesuaikan nilai berikut:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // username MySQL Anda
define('DB_PASS', '');            // password MySQL Anda
define('DB_NAME', 'man1bangka');
```

### 4. Akses Aplikasi

| Halaman        | URL                                  |
| -------------- | ------------------------------------ |
| Website Publik | `http://localhost/man1bangka/`       |
| Panel Admin    | `http://localhost/man1bangka/admin/` |

**Kredensial Admin Default:**
| Field | Nilai |
|---|---|
| Username | `admin` |
| Password | `man1bangka2026` (tersimpan sebagai bcrypt hash di `admin/login.php`) |

> ⚠️ **Cara mengganti password sebelum deploy:**
> 1. Jalankan perintah berikut di terminal server:
>    ```
>    php -r "echo password_hash('password_baru_anda', PASSWORD_DEFAULT);"
>    ```
> 2. Salin hash yang dihasilkan.
> 3. Buka `admin/login.php`, cari konstanta `ADMIN_PASS_HASH`, tempel hash baru.
>
> Password **tidak** disimpan sebagai teks biasa — sistem menggunakan `password_verify()` dengan bcrypt.

---

## 📁 Struktur Folder & Penjelasan File

```
man1bangka/
├── admin/                        # Panel Admin (PHP)
│   ├── index.php                 # Dashboard: statistik & quick-add agenda
│   ├── agenda.php                # CRUD agenda kegiatan
│   ├── media.php                 # Upload & kelola foto/video galeri
│   ├── pengumuman.php            # CRUD pengumuman + highlight beranda
│   ├── pendaftaran.php           # Kelola data pendaftaran ekskul
│   ├── ekskul.php                # CRUD data ekstrakurikuler
│   ├── pembina.php               # CRUD data guru pembina
│   ├── prestasi.php              # CRUD prestasi + upload foto/video (preview gambar, video note)
│   ├── karya.php                 # CRUD karya siswa + upload file
│   ├── testimoni.php             # Moderasi testimoni: approve / tolak / hapus + tambah manual
│   ├── login.php                 # Form & proses login admin
│   ├── logout.php                # Hapus sesi & redirect ke login
│   ├── auth.php                  # Middleware cek sesi (di-require setiap halaman admin)
│   ├── sidebar.php               # Komponen sidebar navigasi (di-include setiap halaman)
│   └── assets/
│       ├── admin.css             # Stylesheet panel admin
│       ├── admin.js              # JavaScript interaktif panel admin
│       └── img/
│           └── logo.png          # Logo MAN 1 Bangka
│
├── halaman/                      # Halaman Publik
│   ├── index.php                 # Beranda (versi PHP, navbar dinamis)
│   ├── navbar.php                # Komponen navbar dinamis (di-include semua halaman)
│   ├── agenda.php                # Agenda kegiatan
│   ├── ekstrakurikuler.php       # Daftar ekstrakurikuler
│   ├── dokumentasi.php           # Galeri foto & video
│   ├── pengumuman.php            # Daftar pengumuman
│   ├── prestasi.php              # Prestasi siswa
│   ├── karya-siswa.php           # Karya siswa
│   ├── organisasi.php            # Struktur organisasi
│   ├── pendaftaran.php           # Form pendaftaran ekskul
│   ├── arsip.php                 # Arsip dokumen
│   ├── kontak.php                # Kontak & lokasi sekolah
│   ├── testimoni.php             # Testimoni siswa
│   └── sidebar.php               # Komponen sidebar halaman publik (legacy)
│
├── php/
│   ├── api.php                   # REST API endpoint utama (semua modul)
│   ├── config.php                # Konfigurasi DB, koneksi MySQLi & PDO, helper functions
│   └── uploads/                  # Folder penyimpanan file upload
│       ├── .htaccess             # Proteksi: blokir eksekusi PHP di folder uploads
│       ├── foto/                 # Foto galeri/dokumentasi
│       ├── video/                # Video galeri/dokumentasi
│       ├── karya/                # File karya siswa
│       ├── arsip/                # File arsip/dokumen
│       ├── foto_pembina/         # Foto profil guru pembina
│       └── prestasi/             # Foto/video prestasi siswa (JPG, PNG, WEBP, MP4)
│
├── assets/
│   ├── css/
│   │   └── style.css             # Stylesheet halaman publik
│   └── js/
│       └── main.js               # JavaScript halaman publik (fetch API, interaksi UI)
│
├── database/
│   └── man1bangka.sql            # Skema & data awal database
│
└── index.html                    # Landing page utama (root)
```

---

## 🖥️ Fitur Panel Admin

| Menu              | Fungsi                                                                                           | File                    |
| ----------------- | ------------------------------------------------------------------------------------------------ | ----------------------- |
| Dashboard         | Statistik ringkasan 8 tabel + form quick-add agenda                                              | `admin/index.php`       |
| Agenda Kegiatan   | Tambah / Edit / Hapus agenda, filter per bulan                                                   | `admin/agenda.php`      |
| Upload Media      | Upload foto & video → galeri/dokumentasi                                                         | `admin/media.php`       |
| Pengumuman        | Kelola pengumuman + tandai highlight beranda                                                     | `admin/pengumuman.php`  |
| Pendaftaran Siswa | Lihat & update status pendaftaran ekskul                                                         | `admin/pendaftaran.php` |
| Ekstrakurikuler   | Kelola data ekskul (nama, kategori, pembina, jadwal)                                             | `admin/ekskul.php`      |
| Guru Pembina      | Kelola data & kontak guru pembina ekskul                                                         | `admin/pembina.php`     |
| Prestasi Siswa    | Tambah / Edit / Hapus prestasi + upload foto/video (preview gambar, video note, validasi format) | `admin/prestasi.php`    |
| Karya Siswa       | Upload dan kelola karya siswa                                                                    | `admin/karya.php`       |
| Testimoni         | Approve / tolak / hapus testimoni siswa + tambah manual dari admin                               | `admin/testimoni.php`   |

---

## 🔌 REST API

Semua data publik diambil oleh halaman frontend melalui `php/api.php` menggunakan parameter `?module=` dan `?action=`.

**Base URL:** `http://localhost/man1bangka/php/api.php`

### Endpoint yang Tersedia

| Module          | Action      | Method | Parameter Tambahan                             | Keterangan                                     |
| --------------- | ----------- | ------ | ---------------------------------------------- | ---------------------------------------------- |
| `pengumuman`    | `list`      | GET    | `limit`, `kategori`                            | Daftar pengumuman                              |
| `pengumuman`    | `highlight` | GET    | —                                              | Maks 3 pengumuman highlight beranda            |
| `agenda`        | `list`      | GET    | `bulan`, `tahun`                               | Agenda per bulan & tahun                       |
| `agenda`        | `upcoming`  | GET    | —                                              | 5 agenda mendatang (fallback: 7 hari terakhir) |
| `agenda`        | `tambah`    | POST   | `judul`, `tanggal_mulai`, ...                  | Tambah agenda baru                             |
| `ekskul`        | `list`      | GET    | `kategori`                                     | Daftar ekskul + data pembina                   |
| `prestasi`      | `list`      | GET    | `tingkat`                                      | Daftar prestasi                                |
| `dokumentasi`   | `list`      | GET    | `jenis`, `kategori`, `limit`                   | Media galeri                                   |
| `dokumentasi`   | `foto`      | GET    | `kategori`, `limit`                            | Shortcut: foto saja                            |
| `dokumentasi`   | `video`     | GET    | `kategori`, `limit`                            | Shortcut: video saja                           |
| `karya`         | `list`      | GET    | `jenis`                                        | Daftar karya siswa                             |
| `arsip`         | `list`      | GET    | `tahun_ajaran`, `semester`                     | Daftar file arsip                              |
| `organisasi`    | `list`      | GET    | —                                              | Data organisasi + anggota + program kerja      |
| `testimoni`     | `list`      | GET    | —                                              | Testimoni aktif (maks 12)                      |
| `testimoni`     | `tambah`    | POST   | `nama_siswa`, `isi`, `rating`, ...             | Kirim testimoni baru                           |
| `pembina`       | `list`      | GET    | —                                              | Daftar guru pembina                            |
| `daftar_ekskul` | —           | POST   | `ekstrakurikuler_id`, `nama_siswa`, `nis`, ... | Form pendaftaran ekskul                        |

**Contoh Request:**

```
GET  /php/api.php?module=pengumuman&action=list&limit=10
GET  /php/api.php?module=agenda&action=list&bulan=4&tahun=2026
GET  /php/api.php?module=ekskul&action=list&kategori=olahraga
POST /php/api.php?module=daftar_ekskul
POST /php/api.php?module=testimoni&action=tambah
```

**Format Response:**

```json
{
  "status": "success",
  "message": "",
  "data": [ ... ]
}
```

---

## 📤 Upload File

File yang diunggah melalui panel admin tersimpan di `php/uploads/` dengan subfolder terpisah per jenis:

| Folder          | Jenis File          | Dipakai oleh         |
| --------------- | ------------------- | -------------------- |
| `foto/`         | JPG, PNG, WEBP, GIF | Media / Galeri Foto  |
| `video/`        | MP4, WEBM           | Media / Galeri Video |
| `karya/`        | Berbagai format     | Modul Karya Siswa    |
| `arsip/`        | PDF, dokumen        | Modul Arsip          |
| `foto_pembina/` | JPG, PNG            | Modul Guru Pembina   |
| `prestasi/`     | JPG, PNG, WEBP, MP4 | Modul Prestasi Siswa |

Folder `php/uploads/` dilindungi oleh `.htaccess` yang memblokir eksekusi file PHP di dalam folder tersebut untuk mencegah eksploitasi upload.

---

## 🏗️ Arsitektur Teknis

### Alur Data

```
Browser (HTML/JS)
     │
     ├─── fetch/XHR ──► php/api.php ──► MySQL DB
     │                      │
     │                  config.php
     │               (koneksi MySQLi)
     │
     └─── Form POST ──► admin/*.php ──► MySQL DB
                            │
                        config.php
                       (koneksi PDO)
```

### Dua Jenis Koneksi Database

| Koneksi                        | Dipakai oleh | Alasan                                                          |
| ------------------------------ | ------------ | --------------------------------------------------------------- |
| **MySQLi** (`getConnection()`) | `api.php`    | Efisien untuk loop `fetch_assoc()` pada banyak baris            |
| **PDO** (`getPDO()`)           | Panel Admin  | Prepared statement lebih ekspresif, mendukung named placeholder |

### Pola Keamanan

- **Autentikasi Admin:** Session PHP via `auth.php` (di-require di baris pertama setiap halaman admin)
- **SQL Injection:** Semua operasi tulis di admin menggunakan PDO prepared statement
- **XSS:** Output di HTML menggunakan `htmlspecialchars()`, input dari form disanitasi via `sanitize()`
- **Upload:** Validasi ekstensi whitelist + `.htaccess` blokir PHP execution di folder uploads
- **PRG Pattern:** Semua POST redirect ke GET setelah berhasil (mencegah double-submit)

---

## 🛠️ Teknologi

| Layer        | Teknologi                                   |
| ------------ | ------------------------------------------- |
| Backend      | PHP 7.4+ (MySQLi + PDO)                     |
| API          | REST API custom (parameter-based routing)   |
| Frontend     | HTML5, CSS3, JavaScript Vanilla (Fetch API) |
| Database     | MySQL / MariaDB                             |
| Ikon         | Font Awesome 6.4                            |
| Font         | Inter (Google Fonts)                        |
| Server Lokal | XAMPP / WAMP / Laragon                      |

---

## 📝 Catatan Pengembangan

- Semua file PHP telah dilengkapi **komentar dokumentasi** pada setiap fungsi, blok logika, dan keputusan teknis.
- File `php/api.php` menggunakan routing berbasis `switch-case` dengan `$module` sebagai key.
- File `php/config.php` adalah satu-satunya sumber konfigurasi — tidak ada koneksi DB yang di-hardcode di tempat lain.
- Folder `admin/assets/` berisi CSS dan JS khusus panel admin, terpisah dari `assets/` milik halaman publik.

---

> Dikembangkan oleh **Estefania — 2322500043, ISB Atma Luhur** — 2026

---

## 🔧 Riwayat Perbaikan (Patch v2 — April 2026)

Perbaikan menyeluruh dari hasil audit kode. Total **16 bug diperbaiki** di **20 file**.

### 🔴 Bug Kritis

| #   | File                                   | Perbaikan                                                                      |
| --- | -------------------------------------- | ------------------------------------------------------------------------------ |
| 1   | `halaman/index.php`                    | Ditulis ulang sebagai halaman publik SSR — sebelumnya duplikat admin dashboard |
| 2   | `php/api.php`                          | Testimoni publik kini masuk `status='nonaktif'` (wajib disetujui admin)        |
| 12  | `assets/js/main.js`                    | Ditambah `esc()` — semua data API di-escape sebelum masuk `innerHTML`          |
| 13  | `php/config.php` + semua `admin/*.php` | CSRF token protection: `getCsrfToken()`, `verifyCsrf()`, `csrfField()`         |

### 🟡 Bug Sedang & Minor

| #   | File                                           | Perbaikan                                                                            |
| --- | ---------------------------------------------- | ------------------------------------------------------------------------------------ |
| 3   | `database/man1bangka.sql`                      | Hapus data uji coba (`www`, `wdawd`) dari seed                                       |
| 4   | `admin/login.php`                              | Hapus kotak kredensial default dari halaman login                                    |
| 5   | `php/config.php`                               | Perbaiki path `UPLOAD_DIR`                                                           |
| 6   | `php/api.php`                                  | Hapus `$conn->close()` dead code                                                     |
| 7   | `halaman/kontak.html`, `pendaftaran.html`      | Form kontak & lomba kini terhubung ke backend (`pesan_kontak`, `pendaftaran_lomba`)  |
| 8   | `admin/karya.php`, `admin/prestasi.php`        | File lama di-`unlink()` saat edit dengan upload baru                                 |
| 10  | `admin/pendaftaran.php`, `admin/testimoni.php` | PRG pattern (redirect setelah POST) diterapkan                                       |
| 14  | `admin/index.php`                              | Ditambah Google Fonts Inter di `<head>`                                              |
| 15  | `admin/assets/admin.js`                        | `eval()` diganti `new Function()` + `karya.php`/`prestasi.php` dikecualikan dari SPA |
| 16  | `admin/auth.php`                               | `!isset()` → `empty()` untuk pengecekan session yang lebih aman                      |

### Tabel Baru di Database

| Tabel               | Fungsi                                       |
| ------------------- | -------------------------------------------- |
| `pesan_kontak`      | Menyimpan pesan dari form kontak publik      |
| `pendaftaran_lomba` | Menyimpan pendaftaran lomba dari form publik |

### Perubahan Struktur Halaman (Navbar Dinamis)

| File                 | Perubahan                                                                     |
| -------------------- | ----------------------------------------------------------------------------- |
| `halaman/navbar.php` | File baru — navbar terpusat yang di-include semua halaman                     |
| `halaman/*.html`     | Dikonversi ke `.php` — navbar statis diganti `<?php include 'navbar.php'; ?>` |
| `assets/js/main.js`  | Page routing diperbarui: `.html` → `.php`                                     |

> Patch oleh review kode menyeluruh — April 2026

### Perubahan Tambahan (Audit Lanjutan)

| Item                                    | Perubahan                                                                         |
| --------------------------------------- | --------------------------------------------------------------------------------- |
| `halaman/index.php`                     | Diubah menjadi redirect 301 ke root `index.html` (file duplikat tidak diperlukan) |
| `php/uploads/*/`                        | `.htaccess` proteksi dikopi ke semua subfolder upload                             |
| `assets/js/main.js`                     | Page routing `.html` → `.php` diperbaiki (loadPengumuman, loadEkskul, dll)        |
| `database/man1bangka.sql`               | Ditambah tabel `pesan_kontak` dan `pendaftaran_lomba` yang sebelumnya hilang      |
| `php/uploads/{foto,video,karya,arsip}/` | Folder dengan nama literal (brace expansion salah) dihapus                        |
