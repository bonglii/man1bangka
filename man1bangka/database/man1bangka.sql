-- ============================================================
-- man1bangka_relasi_penuh.sql — Database dengan Relasi Lengkap
-- MAN 1 Bangka | Perbaikan: Semua tabel kini memiliki relasi FK
-- ============================================================
-- RINGKASAN PERUBAHAN dari versi sebelumnya:
--  1. agenda        → tambah FK organisasi_id (→ organisasi)
--  2. pengumuman    → tambah FK organisasi_id (→ organisasi)
--  3. dokumentasi   → tambah FK organisasi_id (→ organisasi)
--                     tambah FK ekskul_id     (→ ekstrakurikuler)
--  4. arsip         → tambah FK organisasi_id (→ organisasi)
--                     tambah FK ekskul_id     (→ ekstrakurikuler)
--  5. karya_siswa   → tambah FK ekskul_id     (→ ekstrakurikuler)
--  6. prestasi      → tambah FK ekskul_id     (→ ekstrakurikuler)
--  7. testimoni     → tambah FK organisasi_id (→ organisasi)
--                     tambah FK ekskul_id     (→ ekstrakurikuler)
-- Semua FK menggunakan ON DELETE SET NULL agar data tidak
-- terhapus otomatis jika referensi dihapus (data tetap aman).
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- ============================================================
-- DATABASE
-- ============================================================
CREATE DATABASE IF NOT EXISTS `man1bangka`;
USE `man1bangka`;

-- ============================================================
-- TABEL: organisasi (induk utama)
-- ============================================================
CREATE TABLE `organisasi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `deskripsi` text,
  `visi` text,
  `misi` text,
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: kontak_pembina (induk untuk ekstrakurikuler)
-- ============================================================
CREATE TABLE `kontak_pembina` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `bidang` varchar(255) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: ekstrakurikuler (anak dari kontak_pembina)
-- ============================================================
CREATE TABLE `ekstrakurikuler` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `deskripsi` text,
  `kategori` enum('olahraga','seni','akademik','keagamaan','teknologi','lainnya') DEFAULT 'lainnya',
  `jadwal` varchar(255) DEFAULT NULL,
  `tempat` varchar(255) DEFAULT NULL,
  `pembina_id` int DEFAULT NULL,
  `kuota` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_ekskul_pembina` (`pembina_id`),
  CONSTRAINT `fk_ekskul_pembina` FOREIGN KEY (`pembina_id`)
    REFERENCES `kontak_pembina` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: anggota_organisasi (anak dari organisasi)
-- ============================================================
CREATE TABLE `anggota_organisasi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `organisasi_id` int NOT NULL,
  `nama` varchar(255) NOT NULL,
  `jabatan` varchar(100) NOT NULL,
  `kelas` varchar(20) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_anggota_org` (`organisasi_id`),
  CONSTRAINT `fk_anggota_org` FOREIGN KEY (`organisasi_id`)
    REFERENCES `organisasi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: program_kerja (anak dari organisasi)
-- ============================================================
CREATE TABLE `program_kerja` (
  `id` int NOT NULL AUTO_INCREMENT,
  `organisasi_id` int NOT NULL,
  `nama_program` varchar(255) NOT NULL,
  `deskripsi` text,
  `semester` enum('ganjil','genap') DEFAULT 'ganjil',
  `status` enum('rencana','berjalan','selesai') DEFAULT 'rencana',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_proker_org` (`organisasi_id`),
  CONSTRAINT `fk_proker_org` FOREIGN KEY (`organisasi_id`)
    REFERENCES `organisasi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: pendaftaran_ekskul (anak dari ekstrakurikuler)
-- ============================================================
CREATE TABLE `pendaftaran_ekskul` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ekstrakurikuler_id` int NOT NULL,
  `nama_siswa` varchar(255) NOT NULL,
  `kelas` varchar(20) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `alasan` text,
  `status` enum('menunggu','diterima','ditolak') DEFAULT 'menunggu',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_pendaftaran_ekskul` (`ekstrakurikuler_id`),
  CONSTRAINT `fk_pendaftaran_ekskul` FOREIGN KEY (`ekstrakurikuler_id`)
    REFERENCES `ekstrakurikuler` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: agenda
-- PERBAIKAN: Tambah FK organisasi_id → organisasi
-- Agenda kini dapat dikaitkan dengan kegiatan organisasi tertentu.
-- NULL = agenda umum sekolah (tidak spesifik ke satu organisasi)
-- ============================================================
CREATE TABLE `agenda` (
  `id` int NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text,
  `tanggal_mulai` datetime NOT NULL,
  `tanggal_selesai` datetime DEFAULT NULL,
  `lokasi` varchar(255) DEFAULT NULL,
  `kategori` enum('lomba','seminar','kelas','organisasi','ekskul','keagamaan','umum','lainnya') DEFAULT 'umum',
  `warna` varchar(20) DEFAULT '#1a6b3c',
  `is_selesai` tinyint(1) DEFAULT '0',
  `organisasi_id` int DEFAULT NULL,     -- FK ke organisasi ✅ (NULL = agenda umum)
  `ekskul_id` int DEFAULT NULL,         -- FK ke ekstrakurikuler ✅ (NULL = bukan kegiatan ekskul)
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_agenda_org` (`organisasi_id`),
  KEY `fk_agenda_ekskul` (`ekskul_id`),
  CONSTRAINT `fk_agenda_org` FOREIGN KEY (`organisasi_id`)
    REFERENCES `organisasi` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_agenda_ekskul` FOREIGN KEY (`ekskul_id`)
    REFERENCES `ekstrakurikuler` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: pengumuman
-- PERBAIKAN: Tambah FK organisasi_id → organisasi
-- Pengumuman dapat berasal dari OSIS/organisasi tertentu.
-- NULL = pengumuman resmi sekolah
-- ============================================================
CREATE TABLE `pengumuman` (
  `id` int NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `isi` text NOT NULL,
  `kategori` enum('umum','lomba','ekskul','keagamaan','akademik','libur','pendaftaran','kegiatan') DEFAULT 'umum',
  `tanggal_publish` date NOT NULL DEFAULT (curdate()),
  `tanggal_berakhir` date DEFAULT NULL,
  `is_highlight` tinyint(1) DEFAULT '0',
  `organisasi_id` int DEFAULT NULL,     -- FK ke organisasi ✅ (NULL = dari pihak sekolah)
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_pengumuman_org` (`organisasi_id`),
  CONSTRAINT `fk_pengumuman_org` FOREIGN KEY (`organisasi_id`)
    REFERENCES `organisasi` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: dokumentasi
-- PERBAIKAN: Tambah FK organisasi_id & ekskul_id
-- Dokumentasi foto/video dikaitkan ke organisasi atau ekskul.
-- ============================================================
CREATE TABLE `dokumentasi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text,
  `jenis` varchar(20) DEFAULT 'foto',
  `url_media` varchar(500) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `kategori` varchar(100) DEFAULT 'kegiatan',
  `tanggal` datetime DEFAULT CURRENT_TIMESTAMP,
  `organisasi_id` int DEFAULT NULL,     -- FK ke organisasi ✅
  `ekskul_id` int DEFAULT NULL,         -- FK ke ekstrakurikuler ✅
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_dok_org` (`organisasi_id`),
  KEY `fk_dok_ekskul` (`ekskul_id`),
  CONSTRAINT `fk_dok_org` FOREIGN KEY (`organisasi_id`)
    REFERENCES `organisasi` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_dok_ekskul` FOREIGN KEY (`ekskul_id`)
    REFERENCES `ekstrakurikuler` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: arsip
-- PERBAIKAN: Tambah FK organisasi_id & ekskul_id
-- Arsip dokumen dikaitkan ke organisasi atau ekskul penghasil.
-- ============================================================
CREATE TABLE `arsip` (
  `id` int NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text,
  `semester` enum('ganjil','genap') DEFAULT 'ganjil',
  `tahun_ajaran` varchar(20) DEFAULT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `url_file` varchar(500) DEFAULT NULL,
  `organisasi_id` int DEFAULT NULL,     -- FK ke organisasi ✅
  `ekskul_id` int DEFAULT NULL,         -- FK ke ekstrakurikuler ✅
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_arsip_org` (`organisasi_id`),
  KEY `fk_arsip_ekskul` (`ekskul_id`),
  CONSTRAINT `fk_arsip_org` FOREIGN KEY (`organisasi_id`)
    REFERENCES `organisasi` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_arsip_ekskul` FOREIGN KEY (`ekskul_id`)
    REFERENCES `ekstrakurikuler` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: karya_siswa
-- PERBAIKAN: Tambah FK ekskul_id → ekstrakurikuler
-- Karya yang dihasilkan dari kegiatan ekskul tertentu.
-- NULL = karya mandiri / tidak dari ekskul
-- ============================================================
CREATE TABLE `karya_siswa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `siswa` varchar(255) NOT NULL,
  `penulis` varchar(255) DEFAULT NULL,
  `kelas` varchar(20) DEFAULT NULL,
  `jenis` enum('artikel','karya_ilmiah','poster','video','puisi','lainnya') DEFAULT 'artikel',
  `deskripsi` text,
  `penghargaan` varchar(255) DEFAULT NULL,
  `url_file` varchar(500) DEFAULT NULL,
  `ekskul_id` int DEFAULT NULL,         -- FK ke ekstrakurikuler ✅
  `tanggal` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_karya_ekskul` (`ekskul_id`),
  CONSTRAINT `fk_karya_ekskul` FOREIGN KEY (`ekskul_id`)
    REFERENCES `ekstrakurikuler` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: prestasi
-- PERBAIKAN: Tambah FK ekskul_id → ekstrakurikuler
-- Prestasi yang diraih melalui kegiatan ekskul tertentu.
-- NULL = prestasi akademik / individual
-- ============================================================
CREATE TABLE `prestasi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `siswa` varchar(255) NOT NULL,
  `kelas` varchar(20) DEFAULT NULL,
  `jenis` enum('akademik','olahraga','seni','keagamaan','teknologi','lainnya') DEFAULT 'akademik',
  `tingkat` enum('sekolah','kabupaten','provinsi','nasional','internasional') DEFAULT 'kabupaten',
  `posisi` varchar(100) DEFAULT NULL,
  `penyelenggara` varchar(255) DEFAULT NULL,
  `tahun` year DEFAULT NULL,
  `deskripsi` text,
  `url_file` varchar(500) DEFAULT NULL,
  `ekskul_id` int DEFAULT NULL,         -- FK ke ekstrakurikuler ✅
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_prestasi_ekskul` (`ekskul_id`),
  CONSTRAINT `fk_prestasi_ekskul` FOREIGN KEY (`ekskul_id`)
    REFERENCES `ekstrakurikuler` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: testimoni
-- PERBAIKAN: Tambah FK organisasi_id & ekskul_id
-- Testimoni dikaitkan ke organisasi atau ekskul yang relevan.
-- ============================================================
CREATE TABLE `testimoni` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_siswa` varchar(255) NOT NULL,
  `kelas` varchar(20) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `jenis_kegiatan` enum('ekskul','lomba','seminar','organisasi','lainnya') DEFAULT 'lainnya',
  `nama_kegiatan` varchar(255) DEFAULT NULL,
  `isi` text NOT NULL,
  `rating` int DEFAULT '5',
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `is_approved` tinyint(1) DEFAULT '1',
  `organisasi_id` int DEFAULT NULL,     -- FK ke organisasi ✅
  `ekskul_id` int DEFAULT NULL,         -- FK ke ekstrakurikuler ✅
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_testimoni_org` (`organisasi_id`),
  KEY `fk_testimoni_ekskul` (`ekskul_id`),
  CONSTRAINT `fk_testimoni_org` FOREIGN KEY (`organisasi_id`)
    REFERENCES `organisasi` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_testimoni_ekskul` FOREIGN KEY (`ekskul_id`)
    REFERENCES `ekstrakurikuler` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DATA SEED
-- ============================================================

INSERT INTO `organisasi` VALUES
(1,'OSIS MAN 1 Bangka','Organisasi Siswa Intra Sekolah MAN 1 Bangka adalah wadah resmi pengembangan kepemimpinan, kreativitas, dan karakter siswa.',
'Terwujudnya OSIS MAN 1 Bangka yang aktif, kreatif, berprestasi, dan berkarakter Islami dalam membangun generasi emas bangsa.',
'Mengembangkan potensi siswa melalui program terstruktur; membangun komunikasi siswa dan sekolah; menumbuhkan semangat berprestasi; memperkuat nilai-nilai keislaman.',
NULL,'2026-04-01 19:11:49');

INSERT INTO `kontak_pembina` (`id`,`nama`,`jabatan`,`email`,`no_hp`,`bidang`,`foto`,`created_at`) VALUES
(1,'Bpk. Drs. H. Syamsul Bahri','Waka Kesiswaan','syamsul@man1bangka.sch.id','08567890123','Koordinator Kesiswaan',NULL,'2026-04-01 19:11:49'),
(4,'Bpk. Rizky Pratama, S.Pd','Pembina Olahraga','rizkypratama@man1bangka.sch.id','08345678901','Olahraga',NULL,'2026-04-01 19:11:49'),
(6,'Bpk. Hendra Saputra, S.T','Pembina Robotika','hendra@man1bangka.sch.id','08567890124','Teknologi',NULL,'2026-04-01 19:11:49'),
(7,'Bpk. Ahmad Fauzi, S.Pd','Pembina Pramuka',NULL,NULL,'Kepanduan',NULL,'2026-04-01 19:11:49'),
(8,'Ibu Siti Aisyah, M.Pd.I','Pembina Majelis Taklim',NULL,NULL,'Keagamaan',NULL,'2026-04-01 19:11:49'),
(9,'Ibu Nur Hidayah, M.Sc','Pembina KIR',NULL,NULL,'Akademik',NULL,'2026-04-01 19:11:49');

INSERT INTO `ekstrakurikuler` (`id`,`nama`,`deskripsi`,`kategori`,`jadwal`,`tempat`,`pembina_id`,`kuota`,`created_at`) VALUES
(1,'Pramuka','Kegiatan kepanduan yang melatih karakter, kemandirian, dan kerjasama antar siswa. Wajib untuk kelas X.','lainnya','Jumat, 14:00–16:00','Lapangan Utama',7,NULL,'2026-04-01 19:11:49'),
(2,'Majelis Taklim','Kegiatan keagamaan Islam berupa pengajian, tahfidz Al-Quran, dan pembinaan karakter Islami.','keagamaan','Senin & Rabu, 15:00–16:30','Musholla Sekolah',8,NULL,'2026-04-01 19:11:49'),
(3,'Bola Basket','Olahraga bola basket yang melatih kemampuan fisik, strategi, dan kerjasama tim.','olahraga','Sabtu, 07:00–09:00','Lapangan Basket',4,NULL,'2026-04-01 19:11:49'),
(4,'Karya Ilmiah Remaja (KIR)','Kegiatan penelitian ilmiah untuk mengembangkan kemampuan berpikir kritis dan inovatif siswa.','akademik','Kamis, 14:00–16:00','Lab IPA',9,NULL,'2026-04-01 19:11:49'),
(6,'Robotika','Mempelajari teknologi, pemrograman Arduino, dan merakit robot untuk menghadapi era digital.','teknologi','Sabtu, 09:00–11:00','Lab Komputer',6,NULL,'2026-04-01 19:11:49'),
(7,'Futsal','Olahraga futsal kompetitif yang membangun semangat sportivitas dan kekompakan tim.','olahraga','Minggu, 08:00–10:00','Lapangan Futsal',4,NULL,'2026-04-01 19:11:49'),
(8,'Paduan Suara','Kegiatan seni vokal yang melatih kemampuan bernyanyi harmonis dan tampil di berbagai acara.','seni','Selasa, 14:00–16:00','Ruang Musik',NULL,NULL,'2026-04-01 19:11:49'),
(9,'Silat','Pencak Silat','olahraga','Selasa 14.00','Sekolah',NULL,5,'2026-04-01 19:48:30'),
(10,'www','awdawdawdawdawd','akademik','awdawdawd','awdawdawdawd',4,5,'2026-04-02 14:49:00');

INSERT INTO `anggota_organisasi` VALUES
(1,1,'Muhammad Farhan','Ketua OSIS','XII IPS 1',NULL),
(2,1,'Aisyah Putri Ramadhani','Wakil Ketua','XI IPA 1',NULL),
(3,1,'Rizky Aditya Pratama','Sekretaris I','XI IPA 3',NULL),
(4,1,'Dinda Permata Sari','Bendahara','XI IPA 2',NULL),
(5,1,'Fahmi Ramadhan','Bid. Ketaqwaan','X IPS 1',NULL),
(6,1,'Nurul Fadhilah','Bid. Seni','X IPS 2',NULL);

INSERT INTO `program_kerja` VALUES
(1,1,'Masa Orientasi Siswa Baru','Pengenalan lingkungan sekolah bagi siswa baru kelas X.','ganjil','selesai','2026-04-01 19:11:49'),
(2,1,'Peringatan HUT RI','Serangkaian lomba dalam rangka memperingati Hari Kemerdekaan.','ganjil','selesai','2026-04-01 19:11:49'),
(3,1,'Olimpiade Internal Sekolah','Kompetisi akademik dan non-akademik antar kelas.','genap','berjalan','2026-04-01 19:11:49'),
(4,1,'Pentas Seni Akhir Tahun','Penampilan seni tari, musik, dan drama dari seluruh siswa.','genap','rencana','2026-04-01 19:11:49');

-- agenda: dikaitkan ke organisasi (OSIS) via organisasi_id=1
INSERT INTO `agenda` (`id`,`judul`,`deskripsi`,`tanggal_mulai`,`tanggal_selesai`,`lokasi`,`kategori`,`warna`,`is_selesai`,`organisasi_id`,`ekskul_id`,`created_at`) VALUES
(11,'Test TOFL','TEST TEFL BAHASA INGGRIS','2026-04-09 00:00:00','2026-04-14 00:00:00','AULA MAN 1 BANGKA','organisasi','#1a6b3c',0,1,NULL,'2026-04-02 09:54:16');

-- pengumuman: dikaitkan ke OSIS untuk pengumuman ekstrakurikuler
INSERT INTO `pengumuman` (`id`,`judul`,`isi`,`kategori`,`tanggal_publish`,`tanggal_berakhir`,`is_highlight`,`organisasi_id`,`created_at`) VALUES
(1,'Lomba Olimpiade Sains Nasional 2025','Pendaftaran OSN tingkat sekolah dibuka untuk seluruh siswa kelas X dan XI. Bidang: Matematika, Fisika, Kimia, Biologi, Informatika. Daftar ke ruang guru paling lambat 20 Februari 2025.','lomba','2025-01-10',NULL,1,NULL,'2026-04-01 19:11:48'),
(2,'Peringatan Hari Hari','Seluruh siswa wajib mengikuti upacara Hardiknas pada 2 Mei 2025 pukul 07.00 WIB di lapangan utama sekolah. Harap memakai seragam lengkap.','umum','2025-04-28',NULL,0,NULL,'2026-04-01 19:11:48'),
(3,'Pendaftaran Ekstrakurikuler Semester Genap','Pendaftaran ekstrakurikuler semester genap 2024/2025 dibuka 15–31 Januari 2025.','pendaftaran','2025-01-15',NULL,1,1,'2026-04-01 19:11:48'),
(4,'Seminar Motivasi Bersama Alumni Berprestasi','MAN 1 Bangka mengadakan seminar motivasi bersama alumni berprestasi pada 1 Maret 2025 pukul 08.00–12.00 di Aula Utama.','kegiatan','2025-02-01',NULL,0,1,'2026-04-01 19:11:48');

-- arsip: dikaitkan ke ekskul Pramuka (id=1) dan OSIS (id=1)
INSERT INTO `arsip` (`id`,`judul`,`deskripsi`,`semester`,`tahun_ajaran`,`kategori`,`url_file`,`organisasi_id`,`ekskul_id`,`created_at`) VALUES
(1,'Laporan Kegiatan Semester Ganjil 2024/2025','Laporan lengkap seluruh kegiatan siswa semester ganjil 2024/2025 termasuk ekskul, OSIS, dan lomba.','ganjil','2024/2025','laporan_semester',NULL,1,NULL,'2026-04-01 19:11:49'),
(2,'Laporan Kegiatan Semester Genap 2023/2024','Dokumentasi dan laporan kegiatan semester genap tahun ajaran 2023/2024.','genap','2023/2024','laporan_semester',NULL,1,NULL,'2026-04-01 19:11:49'),
(3,'Laporan OSN Provinsi Babel 2024','Laporan perjalanan dan hasil delegasi MAN 1 Bangka pada OSN Provinsi Bangka Belitung 2024.','ganjil','2024/2025','lomba',NULL,NULL,NULL,'2026-04-01 19:11:49'),
(4,'Laporan Pramuka Penegak 2024','Dokumentasi kegiatan pramuka penegak termasuk kemah bumi dan pelantikan anggota baru.','genap','2023/2024','ekskul',NULL,NULL,1,'2026-04-01 19:11:49');

-- karya_siswa: dikaitkan ke ekskul KIR (id=4) dan Robotika (id=6)
INSERT INTO `karya_siswa` (`id`,`judul`,`siswa`,`penulis`,`kelas`,`jenis`,`deskripsi`,`penghargaan`,`url_file`,`ekskul_id`,`tanggal`,`created_at`) VALUES
(1,'Peran Teknologi dalam Pendidikan Islam Modern','Siti Nurhaliza','Siti Nurhaliza','XI IPA 2','artikel','Analisis mendalam tentang bagaimana teknologi dapat diintegrasikan dalam kurikulum pendidikan Islam.','Juara 2 Nasional',NULL,4,'2026-04-02 02:11:49','2026-04-01 19:11:49'),
(2,'Inovasi Pupuk Organik dari Limbah Kelapa Sawit','Ahmad Rizky','Ahmad Rizky','XII IPA 1','karya_ilmiah','Penelitian tentang pemanfaatan limbah kelapa sawit menjadi pupuk organik berkualitas tinggi.','Juara 1 Provinsi',NULL,4,'2026-04-02 02:11:49','2026-04-01 19:11:49'),
(3,'Stop Bullying — Sekolah Ramah Anak','Dinda Permata','Dinda Permata','XI IPS 1','poster','Poster kampanye anti-bullying.',NULL,NULL,NULL,'2026-04-02 02:11:49','2026-04-01 19:11:49'),
(4,'Bangga Budaya Bangka — Mini Dokumenter','Tim Multimedia','Tim Multimedia','XII IPA 3','video','Video dokumenter pendek tentang kebudayaan dan potensi wisata Pulau Bangka.','Best Short Film',NULL,NULL,'2026-04-02 02:11:49','2026-04-01 19:11:49'),
(5,'Dalam Diam Tersimpan Doa','Nurul Fadhilah','Nurul Fadhilah','X IPS 2','puisi','Kumpulan puisi tentang perjuangan dan harapan dalam menuntut ilmu.','Juara 1 Lomba Puisi Islami',NULL,NULL,'2026-04-02 02:11:49','2026-04-01 19:11:49'),
(9,'Makalah Lomba Berbuat Baik','wdawd','wdawd','10A','artikel','awdawdawdawd','-','php/uploads/karya/karya_69ce882c2e61c5.55516471.png',NULL,'2026-04-02 22:15:56','2026-04-02 15:15:56');

-- prestasi: dikaitkan ke ekskul terkait
INSERT INTO `prestasi` (`id`,`judul`,`siswa`,`kelas`,`jenis`,`tingkat`,`posisi`,`penyelenggara`,`tahun`,`deskripsi`,`url_file`,`ekskul_id`,`created_at`) VALUES
(1,'Juara 1 Olimpiade Matematika','Ahmad Rizky Pratama','XII IPA 1','akademik','provinsi','Juara 1','Dinas Pendidikan Provinsi Babel','2024',NULL,NULL,4,'2026-04-01 19:11:49'),
(2,'Juara 2 Lomba Karya Ilmiah Remaja','Siti Nurhaliza','XI IPA 2','akademik','nasional','Juara 2','Kemendikbud RI','2024',NULL,NULL,4,'2026-04-01 19:11:49'),
(3,'Siswa Berprestasi Tingkat Kabupaten','Muhammad Farhan','XII IPS 1','akademik','kabupaten','Terbaik 1','Dinas Pendidikan Bangka','2024',NULL,NULL,NULL,'2026-04-01 19:11:49'),
(4,'Juara 1 MTQ Cabang Tilawah','Aisyah Putri Ramadhani','XI IPA 1','keagamaan','kabupaten','Juara 1','Kemenag Bangka','2024',NULL,NULL,2,'2026-04-01 19:11:49'),
(5,'Juara 3 Lomba Robotik Nasional','Rizky Aditya & Tim','X IPA 3','teknologi','nasional','Juara 3','Kemendikbud RI','2024',NULL,NULL,6,'2026-04-01 19:11:49'),
(6,'Juara 1 Turnamen Basket Pelajar Babel','Tim Basket MAN 1 Bangka','XI IPA','olahraga','provinsi','Juara 1','KONI Babel','2024',NULL,NULL,3,'2026-04-01 19:11:49'),
(7,'Medali Perunggu OSN Biologi','Dinda Permata Sari','XI IPA 2','akademik','nasional','Peringkat 3','Kemendikbud RI','2024',NULL,NULL,NULL,'2026-04-01 19:11:49'),
(8,'Juara 2 Lomba Tari Tradisional','Nurul Fadhilah & Tim','','seni','kabupaten','Juara 2','Dinas Kebudayaan Bangka','2024','',NULL,8,'2026-04-01 19:11:49');

-- testimoni: dikaitkan ke organisasi atau ekskul yang disebut
INSERT INTO `testimoni` (`id`,`nama_siswa`,`kelas`,`foto`,`jenis_kegiatan`,`nama_kegiatan`,`isi`,`rating`,`status`,`is_approved`,`organisasi_id`,`ekskul_id`,`created_at`) VALUES
(1,'Dinda Permata','XI IPA 2',NULL,'lomba','Olimpiade Matematika','Mengikuti OSN memberikan pengalaman luar biasa. Saya belajar banyak dari guru-guru hebat dan teman-teman yang bersemangat. MAN 1 Bangka selalu mendukung kami sepenuh hati!',5,'aktif',1,NULL,4,'2026-04-01 19:11:49'),
(2,'Fahmi Ramadhan','X IPS 1',NULL,'ekskul','Pramuka','Pramuka di MAN 1 Bangka bukan sekadar kegiatan biasa. Di sini aku belajar kepemimpinan, kemandirian, dan persahabatan yang sesungguhnya.',5,'aktif',1,NULL,1,'2026-04-01 19:11:49'),
(3,'Nurainun Sari','XII IPA 3',NULL,'organisasi','OSIS','Menjadi pengurus OSIS adalah pengalaman terbaik masa sekolahku. Aku belajar berorganisasi, berani berbicara di depan umum, dan merancang program untuk teman-teman.',5,'aktif',1,1,NULL,'2026-04-01 19:11:49'),
(4,'Bima Sakti','XI IPA 1',NULL,'seminar','Seminar Motivasi Alumni','Seminar motivasi alumni MAN 1 Bangka membuka wawasan saya tentang masa depan. Kisah sukses alumni menginspirasi saya untuk terus berjuang dan tidak menyerah!',4,'aktif',1,1,NULL,'2026-04-01 19:11:49');

COMMIT;
