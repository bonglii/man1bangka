-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 10, 2026 at 04:08 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `man1bangka`
--

-- --------------------------------------------------------

--
-- Table structure for table `agenda`
--

CREATE TABLE `agenda` (
  `id` int NOT NULL,
  `judul` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tanggal_mulai` datetime NOT NULL,
  `tanggal_selesai` datetime DEFAULT NULL,
  `lokasi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kategori` enum('lomba','seminar','kelas','organisasi','ekskul','keagamaan','umum','lainnya') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'umum',
  `warna` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#1a6b3c',
  `is_selesai` tinyint(1) DEFAULT '0',
  `organisasi_id` int DEFAULT NULL,
  `ekskul_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `agenda`
--

INSERT INTO `agenda` (`id`, `judul`, `deskripsi`, `tanggal_mulai`, `tanggal_selesai`, `lokasi`, `kategori`, `warna`, `is_selesai`, `organisasi_id`, `ekskul_id`, `created_at`) VALUES
(11, 'TOEFL', 'TEST TOEFL BAHASA INGGRIS', '2026-04-09 00:00:00', '2026-04-14 00:00:00', 'AULA MAN 1 BANGKA', 'organisasi', '#000000', 0, 1, NULL, '2026-04-02 09:54:16');

-- --------------------------------------------------------

--
-- Table structure for table `anggota_organisasi`
--

CREATE TABLE `anggota_organisasi` (
  `id` int NOT NULL,
  `organisasi_id` int NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jabatan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kelas` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `anggota_organisasi`
--

INSERT INTO `anggota_organisasi` (`id`, `organisasi_id`, `nama`, `jabatan`, `kelas`, `foto`) VALUES
(1, 1, 'Muhammad Farhan', 'Ketua OSIS', 'XII IPS 1', NULL),
(2, 1, 'Aisyah Putri Ramadhani', 'Wakil Ketua', 'XI IPA 1', NULL),
(3, 1, 'Rizky Aditya Pratama', 'Sekretaris I', 'XI IPA 3', NULL),
(4, 1, 'Dinda Permata Sari', 'Bendahara', 'XI IPA 2', NULL),
(5, 1, 'Fahmi Ramadhan', 'Bid. Ketaqwaan', 'X IPS 1', NULL),
(6, 1, 'Nurul Fadhilah', 'Bid. Seni', 'X IPS 2', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `arsip`
--

CREATE TABLE `arsip` (
  `id` int NOT NULL,
  `judul` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `semester` enum('ganjil','genap') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ganjil',
  `tahun_ajaran` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kategori` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_file` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `organisasi_id` int DEFAULT NULL,
  `ekskul_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `arsip`
--

INSERT INTO `arsip` (`id`, `judul`, `deskripsi`, `semester`, `tahun_ajaran`, `kategori`, `url_file`, `organisasi_id`, `ekskul_id`, `created_at`) VALUES
(1, 'Laporan Kegiatan Semester Ganjil 2024/2025', 'Laporan lengkap seluruh kegiatan siswa semester ganjil 2024/2025 termasuk ekskul, OSIS, dan lomba.', 'ganjil', '2024/2025', 'laporan_semester', NULL, 1, NULL, '2026-04-01 19:11:49'),
(2, 'Laporan Kegiatan Semester Genap 2023/2024', 'Dokumentasi dan laporan kegiatan semester genap tahun ajaran 2023/2024.', 'genap', '2023/2024', 'laporan_semester', NULL, 1, NULL, '2026-04-01 19:11:49'),
(3, 'Laporan OSN Provinsi Babel 2024', 'Laporan perjalanan dan hasil delegasi MAN 1 Bangka pada OSN Provinsi Bangka Belitung 2024.', 'ganjil', '2024/2025', 'lomba', NULL, NULL, NULL, '2026-04-01 19:11:49'),
(4, 'Laporan Pramuka Penegak 2024', 'Dokumentasi kegiatan pramuka penegak termasuk kemah bumi dan pelantikan anggota baru.', 'genap', '2023/2024', 'ekskul', NULL, NULL, 1, '2026-04-01 19:11:49');

-- --------------------------------------------------------

--
-- Table structure for table `dokumentasi`
--

CREATE TABLE `dokumentasi` (
  `id` int NOT NULL,
  `judul` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `jenis` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'foto',
  `url_media` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumbnail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kategori` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'kegiatan',
  `tanggal` datetime DEFAULT CURRENT_TIMESTAMP,
  `organisasi_id` int DEFAULT NULL,
  `ekskul_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ekstrakurikuler`
--

CREATE TABLE `ekstrakurikuler` (
  `id` int NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `kategori` enum('olahraga','seni','akademik','keagamaan','teknologi','lainnya') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'lainnya',
  `jadwal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tempat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pembina_id` int DEFAULT NULL,
  `kuota` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ekstrakurikuler`
--

INSERT INTO `ekstrakurikuler` (`id`, `nama`, `deskripsi`, `kategori`, `jadwal`, `tempat`, `pembina_id`, `kuota`, `created_at`) VALUES
(1, 'Pramuka', 'Kegiatan kepanduan yang melatih karakter, kemandirian, dan kerjasama antar siswa. Wajib untuk kelas X.', 'lainnya', 'Jumat, 14:00–16:00', 'Lapangan Utama', NULL, NULL, '2026-04-01 19:11:49'),
(2, 'Majelis Taklim', 'Kegiatan keagamaan Islam berupa pengajian, tahfidz Al-Quran, dan pembinaan karakter Islami.', 'keagamaan', 'Senin & Rabu, 15:00–16:30', 'Musholla Sekolah', NULL, NULL, '2026-04-01 19:11:49'),
(3, 'Bola Basket', 'Olahraga bola basket yang melatih kemampuan fisik, strategi, dan kerjasama tim.', 'olahraga', 'Sabtu, 07:00–09:00', 'Lapangan Basket', NULL, NULL, '2026-04-01 19:11:49'),
(4, 'Karya Ilmiah Remaja (KIR)', 'Kegiatan penelitian ilmiah untuk mengembangkan kemampuan berpikir kritis dan inovatif siswa.', 'akademik', 'Kamis, 14:00–16:00', 'Lab IPA', NULL, NULL, '2026-04-01 19:11:49'),
(6, 'Robotika', 'Mempelajari teknologi, pemrograman Arduino, dan merakit robot untuk menghadapi era digital.', 'teknologi', 'Sabtu, 09:00–11:00', 'Lab Komputer', NULL, NULL, '2026-04-01 19:11:49'),
(7, 'Bola Kaki/Futsal', 'Olahraga bola kaki/futsal kompetitif yang membangun semangat sportivitas dan kekompakan tim.', 'olahraga', 'Selasa, 16.00-Selesai', 'Lapangan Sepak Bola Gang Sambu, Lapangan Futsal Man 1 Bangka', 21, NULL, '2026-04-01 19:11:49'),
(8, 'Paduan Suara', 'Kegiatan seni vokal yang melatih kemampuan bernyanyi harmonis dan tampil di berbagai acara.', 'seni', 'Selasa, 14:00–16:00', 'Ruang Musik', NULL, NULL, '2026-04-01 19:11:49'),
(9, 'Silat', 'Pencak Silat', 'olahraga', 'Selasa 14.00', 'Sekolah', NULL, 5, '2026-04-01 19:48:30'),
(10, 'www', 'awdawdawdawdawd', 'akademik', 'awdawdawd', 'awdawdawdawd', NULL, 5, '2026-04-02 14:49:00');

-- --------------------------------------------------------

--
-- Table structure for table `karya_siswa`
--

CREATE TABLE `karya_siswa` (
  `id` int NOT NULL,
  `judul` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `siswa` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `penulis` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kelas` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis` enum('artikel','karya_ilmiah','poster','video','puisi','lainnya') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'artikel',
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `penghargaan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_file` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ekskul_id` int DEFAULT NULL,
  `tanggal` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `karya_siswa`
--

INSERT INTO `karya_siswa` (`id`, `judul`, `siswa`, `penulis`, `kelas`, `jenis`, `deskripsi`, `penghargaan`, `url_file`, `ekskul_id`, `tanggal`, `created_at`) VALUES
(1, 'Peran Teknologi dalam Pendidikan Islam Modern', 'Siti Nurhaliza', 'Siti Nurhaliza', 'XI IPA 2', 'artikel', 'Analisis mendalam tentang bagaimana teknologi dapat diintegrasikan dalam kurikulum pendidikan Islam.', 'Juara 2 Nasional', NULL, 4, '2026-04-02 02:11:49', '2026-04-01 19:11:49'),
(2, 'Inovasi Pupuk Organik dari Limbah Kelapa Sawit', 'Ahmad Rizky', 'Ahmad Rizky', 'XII IPA 1', 'karya_ilmiah', 'Penelitian tentang pemanfaatan limbah kelapa sawit menjadi pupuk organik berkualitas tinggi.', 'Juara 1 Provinsi', NULL, 4, '2026-04-02 02:11:49', '2026-04-01 19:11:49'),
(3, 'Stop Bullying — Sekolah Ramah Anak', 'Dinda Permata', 'Dinda Permata', 'XI IPS 1', 'poster', 'Poster kampanye anti-bullying.', NULL, NULL, NULL, '2026-04-02 02:11:49', '2026-04-01 19:11:49'),
(4, 'Bangga Budaya Bangka — Mini Dokumenter', 'Tim Multimedia', 'Tim Multimedia', 'XII IPA 3', 'video', 'Video dokumenter pendek tentang kebudayaan dan potensi wisata Pulau Bangka.', 'Best Short Film', NULL, NULL, '2026-04-02 02:11:49', '2026-04-01 19:11:49'),
(5, 'Dalam Diam Tersimpan Doa', 'Nurul Fadhilah', 'Nurul Fadhilah', 'X IPS 2', 'puisi', 'Kumpulan puisi tentang perjuangan dan harapan dalam menuntut ilmu.', 'Juara 1 Lomba Puisi Islami', NULL, NULL, '2026-04-02 02:11:49', '2026-04-01 19:11:49'),
(9, 'Makalah Lomba Berbuat Baik 2', 'wdawd', 'wdawd', '10A', 'artikel', 'aa', '-', 'php/uploads/karya/karya_69ce882c2e61c5.55516471.png', NULL, '2026-04-02 22:15:56', '2026-04-02 15:15:56');

-- --------------------------------------------------------

--
-- Table structure for table `kontak_pembina`
--

CREATE TABLE `kontak_pembina` (
  `id` int NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jabatan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bidang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kontak_pembina`
--

INSERT INTO `kontak_pembina` (`id`, `nama`, `jabatan`, `email`, `no_hp`, `bidang`, `foto`, `created_at`) VALUES
(10, 'Chardina Sari, S.Si', 'Pembina  OSIS', '', '', 'Lainnya', '', '2026-04-08 00:54:37'),
(11, 'Helda Zaitinia, S.Pd', 'Pembina OSIS', '', '', 'Lainnya', '', '2026-04-08 00:55:50'),
(12, 'Edi Kurniawan, S.Pd.I', 'Pembina Rohis', '', '', 'Keagamaan', '', '2026-04-08 00:56:42'),
(13, 'Riskayati, S.Pd.I', 'Pembina Rohis', '', '', 'Keagamaan', '', '2026-04-08 00:57:32'),
(14, 'Sintia Dewi, S.Pd', 'Pembina PMR', '', '', 'Lainnya', '', '2026-04-08 00:58:06'),
(15, 'Bimo Guntoro', 'Pembina PKS', '', '', 'Lainnya', '', '2026-04-08 00:59:22'),
(16, 'Sulistya, M.Pd', 'Pembina Pramuka, 9K', '', '', 'Lainnya', '', '2026-04-08 01:00:10'),
(17, 'Siti Kurniati, S.Pd', 'Pembina Pramuka, Seni Budaya, Geografi', '', '', 'Lainnya', '', '2026-04-08 01:00:53'),
(18, 'Ryan Bagus Sadewo, S.Pd', 'Pembina Pramuka', '', '', 'Kepramukaan', '', '2026-04-08 01:01:23'),
(19, 'Febri', 'Pembina Pramuka', '', '', 'Kepramukaan', '', '2026-04-08 01:01:40'),
(20, 'Samsu, S.Ag', 'Pembina Muhadhoroh', '', '', 'Keagamaan', '', '2026-04-08 01:02:14'),
(21, 'Nuryadi, S.Pd', 'Pembina Marching Band, Bola Kaki/Futsal, Bola Voli, Laboratorium Komputer/Multimedia', 'nuryadipjkr@gmail.com', '087774950001', 'Olahraga', 'foto_pembina/pembina_1775620245_855.jpeg', '2026-04-08 01:04:18'),
(22, 'Nenie Prastyaningrum, S.Pd', 'Pembina Marching Band', '', '', 'Seni', '', '2026-04-08 01:05:08'),
(23, 'Ghalib Mekakau, S.HI', 'Pembina Band, Paskib', '', '', 'Lainnya', '', '2026-04-08 01:31:10'),
(24, 'Ika Purwandani, S.Pd', 'Pembina UKS, KSM Matematika', '', '', 'Akademik', '', '2026-04-08 01:35:38'),
(25, 'Fariasih, S.Ag', 'Pembina UKS', '', '', 'Lainnya', '', '2026-04-08 01:36:42'),
(26, 'Lili Vembriana Hakim, S.Pd', 'Pembina 9K', '', '', 'Lainnya', '', '2026-04-08 01:53:49'),
(27, 'Mika Enjeli, S.Pd', 'Pembina PIK-R', '', '', 'Lainnya', '', '2026-04-08 03:28:17'),
(28, 'Norhayati, S.Ag', 'Pembina BBQ, Arabic Club,', '', '', 'Keagamaan', '', '2026-04-08 03:29:29'),
(29, 'Rahmat Charles, S.I.Pust', 'Pembina Perpustakaan', '', '', 'Lainnya', '', '2026-04-08 03:36:55'),
(30, 'Fitri Indaswari Oktavia, S.Si', 'Pembina Laboratorium Fisika, KSM Fisika/Astronomi', '', '', 'Akademik', '', '2026-04-08 03:38:58'),
(31, 'Amsuri, S.Pd, MSc', 'Pembina Laboratorium Kimia, KSN Kimia,', '', '', 'Akademik', '', '2026-04-08 03:40:25'),
(32, 'Fadhliah, S.Si, M.Pd', 'Pembina Laboratorium Biologi, KSM Biologi', '', '', 'Akademik', '', '2026-04-08 03:42:01'),
(33, 'Maynenda Handayani, S.E', 'Pembina Laboratorium Komputer/Multimedia, KSM Ekonomi', '', '', 'Akademik', '', '2026-04-08 03:43:38'),
(34, 'Dra. Sugiah', 'Pembina Sekolah/Madrasah Sehat', '', '', 'Lainnya', '', '2026-04-08 03:53:08'),
(35, 'Utama Dewi Maya Sari,S.Pd', 'Pembina Sekolah/Madrasah Sehat, English Club', '', '', 'Lainnya', '', '2026-04-08 04:17:48'),
(36, 'Feni Wulandari, S.Pd', 'Pembina Sekolah/Madrasah Sehat, KSM Matematika', '', '', 'Lainnya', '', '2026-04-08 04:19:26'),
(37, 'Tina, S.Pd', 'Pembina Ruang Sastra, Literasi', '', '', 'Akademik', '', '2026-04-08 04:21:37');

-- --------------------------------------------------------

--
-- Table structure for table `organisasi`
--

CREATE TABLE `organisasi` (
  `id` int NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `visi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `misi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `gambar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `organisasi`
--

INSERT INTO `organisasi` (`id`, `nama`, `deskripsi`, `visi`, `misi`, `gambar`, `created_at`) VALUES
(1, 'OSIS MAN 1 Bangka', 'Organisasi Siswa Intra Sekolah MAN 1 Bangka adalah wadah resmi pengembangan kepemimpinan, kreativitas, dan karakter siswa.', 'Terwujudnya OSIS MAN 1 Bangka yang aktif, kreatif, berprestasi, dan berkarakter Islami dalam membangun generasi emas bangsa.', 'Mengembangkan potensi siswa melalui program terstruktur; membangun komunikasi siswa dan sekolah; menumbuhkan semangat berprestasi; memperkuat nilai-nilai keislaman.', NULL, '2026-04-01 19:11:49');

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran_ekskul`
--

CREATE TABLE `pendaftaran_ekskul` (
  `id` int NOT NULL,
  `ekstrakurikuler_id` int NOT NULL,
  `nama_siswa` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kelas` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nis` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_hp` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alasan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('menunggu','diterima','ditolak') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'menunggu',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengumuman`
--

CREATE TABLE `pengumuman` (
  `id` int NOT NULL,
  `judul` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `isi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` enum('umum','lomba','ekskul','keagamaan','akademik','libur','pendaftaran','kegiatan') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'umum',
  `tanggal_publish` date NOT NULL DEFAULT (curdate()),
  `tanggal_berakhir` date DEFAULT NULL,
  `is_highlight` tinyint(1) DEFAULT '0',
  `organisasi_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengumuman`
--

INSERT INTO `pengumuman` (`id`, `judul`, `isi`, `kategori`, `tanggal_publish`, `tanggal_berakhir`, `is_highlight`, `organisasi_id`, `created_at`) VALUES
(1, 'Lomba Olimpiade Sains Nasional 2025', 'Pendaftaran OSN tingkat sekolah dibuka untuk seluruh siswa kelas X dan XI. Bidang: Matematika, Fisika, Kimia, Biologi, Informatika. Daftar ke ruang guru paling lambat 20 Februari 2025.', 'lomba', '2025-01-10', NULL, 1, NULL, '2026-04-01 19:11:48'),
(2, 'Peringatan Hari Hari', 'Seluruh siswa wajib mengikuti upacara Hardiknas pada 2 Mei 2025 pukul 07.00 WIB di lapangan utama sekolah. Harap memakai seragam lengkap.', 'umum', '2025-04-28', NULL, 0, NULL, '2026-04-01 19:11:48'),
(3, 'Pendaftaran Ekstrakurikuler Semester Genap', 'Pendaftaran ekstrakurikuler semester genap 2024/2025 dibuka 15–31 Januari 2025.', 'pendaftaran', '2025-01-15', NULL, 1, 1, '2026-04-01 19:11:48'),
(4, 'Seminar Motivasi Bersama Alumni Berprestasi', 'MAN 1 Bangka mengadakan seminar motivasi bersama alumni berprestasi pada 1 Maret 2025 pukul 08.00–12.00 di Aula Utama.', 'kegiatan', '2025-02-01', NULL, 0, 1, '2026-04-01 19:11:48');

-- --------------------------------------------------------

--
-- Table structure for table `prestasi`
--

CREATE TABLE `prestasi` (
  `id` int NOT NULL,
  `judul` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `siswa` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kelas` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis` enum('akademik','olahraga','seni','keagamaan','teknologi','lainnya') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'akademik',
  `tingkat` enum('sekolah','kabupaten','provinsi','nasional','internasional') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'kabupaten',
  `posisi` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `penyelenggara` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tahun` year DEFAULT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `url_file` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ekskul_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prestasi`
--

INSERT INTO `prestasi` (`id`, `judul`, `siswa`, `kelas`, `jenis`, `tingkat`, `posisi`, `penyelenggara`, `tahun`, `deskripsi`, `url_file`, `ekskul_id`, `created_at`) VALUES
(1, 'Juara 1 Olimpiade Matematika', 'Ahmad Rizky Pratama', 'XII IPA 1', 'akademik', 'provinsi', 'Juara 1', 'Dinas Pendidikan Provinsi Babel', '2024', NULL, NULL, 4, '2026-04-01 19:11:49'),
(2, 'Juara 2 Lomba Karya Ilmiah Remaja', 'Siti Nurhaliza', 'XI IPA 2', 'akademik', 'nasional', 'Juara 2', 'Kemendikbud RI', '2024', NULL, NULL, 4, '2026-04-01 19:11:49'),
(3, 'Siswa Berprestasi Tingkat Kabupaten', 'Muhammad Farhan', 'XII IPS 1', 'akademik', 'kabupaten', 'Terbaik 1', 'Dinas Pendidikan Bangka', '2024', NULL, NULL, NULL, '2026-04-01 19:11:49'),
(4, 'Juara 1 MTQ Cabang Tilawah', 'Aisyah Putri Ramadhani', 'XI IPA 1', 'keagamaan', 'kabupaten', 'Juara 1', 'Kemenag Bangka', '2024', NULL, NULL, 2, '2026-04-01 19:11:49'),
(5, 'Juara 3 Lomba Robotik Nasional', 'Rizky Aditya & Tim', 'X IPA 3', 'teknologi', 'nasional', 'Juara 3', 'Kemendikbud RI', '2024', NULL, NULL, 6, '2026-04-01 19:11:49'),
(6, 'Juara 1 Turnamen Basket Pelajar Babel', 'Tim Basket MAN 1 Bangka', 'XI IPA', 'olahraga', 'provinsi', 'Juara 1', 'KONI Babel', '2024', NULL, NULL, 3, '2026-04-01 19:11:49'),
(7, 'Medali Perunggu OSN Biologi', 'Dinda Permata Sari', 'XI IPA 2', 'akademik', 'nasional', 'Peringkat 3', 'Kemendikbud RI', '2024', NULL, NULL, NULL, '2026-04-01 19:11:49'),
(8, 'Juara 2 Lomba Tari Tradisional', 'Nurul Fadhilah & Tim', '', 'seni', 'kabupaten', 'Juara 2', 'Dinas Kebudayaan Bangka', '2024', '', NULL, 8, '2026-04-01 19:11:49');

-- --------------------------------------------------------

--
-- Table structure for table `program_kerja`
--

CREATE TABLE `program_kerja` (
  `id` int NOT NULL,
  `organisasi_id` int NOT NULL,
  `nama_program` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `semester` enum('ganjil','genap') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ganjil',
  `status` enum('rencana','berjalan','selesai') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'rencana',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `program_kerja`
--

INSERT INTO `program_kerja` (`id`, `organisasi_id`, `nama_program`, `deskripsi`, `semester`, `status`, `created_at`) VALUES
(1, 1, 'Masa Orientasi Siswa Baru', 'Pengenalan lingkungan sekolah bagi siswa baru kelas X.', 'ganjil', 'selesai', '2026-04-01 19:11:49'),
(2, 1, 'Peringatan HUT RI', 'Serangkaian lomba dalam rangka memperingati Hari Kemerdekaan.', 'ganjil', 'selesai', '2026-04-01 19:11:49'),
(3, 1, 'Olimpiade Internal Sekolah', 'Kompetisi akademik dan non-akademik antar kelas.', 'genap', 'berjalan', '2026-04-01 19:11:49'),
(4, 1, 'Pentas Seni Akhir Tahun', 'Penampilan seni tari, musik, dan drama dari seluruh siswa.', 'genap', 'rencana', '2026-04-01 19:11:49');

-- --------------------------------------------------------

--
-- Table structure for table `testimoni`
--

CREATE TABLE `testimoni` (
  `id` int NOT NULL,
  `nama_siswa` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kelas` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_kegiatan` enum('ekskul','lomba','seminar','organisasi','lainnya') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'lainnya',
  `nama_kegiatan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` int DEFAULT '5',
  `status` enum('aktif','nonaktif') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'aktif',
  `is_approved` tinyint(1) DEFAULT '1',
  `organisasi_id` int DEFAULT NULL,
  `ekskul_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `testimoni`
--

INSERT INTO `testimoni` (`id`, `nama_siswa`, `kelas`, `foto`, `jenis_kegiatan`, `nama_kegiatan`, `isi`, `rating`, `status`, `is_approved`, `organisasi_id`, `ekskul_id`, `created_at`) VALUES
(1, 'Dinda Permata', 'XI IPA 2', NULL, 'lomba', 'Olimpiade Matematika', 'Mengikuti OSN memberikan pengalaman luar biasa. Saya belajar banyak dari guru-guru hebat dan teman-teman yang bersemangat. MAN 1 Bangka selalu mendukung kami sepenuh hati!', 5, 'aktif', 1, NULL, 4, '2026-04-01 19:11:49'),
(2, 'Fahmi Ramadhan', 'X IPS 1', NULL, 'ekskul', 'Pramuka', 'Pramuka di MAN 1 Bangka bukan sekadar kegiatan biasa. Di sini aku belajar kepemimpinan, kemandirian, dan persahabatan yang sesungguhnya.', 5, 'aktif', 1, NULL, 1, '2026-04-01 19:11:49'),
(3, 'Nurainun Sari', 'XII IPA 3', NULL, 'organisasi', 'OSIS', 'Menjadi pengurus OSIS adalah pengalaman terbaik masa sekolahku. Aku belajar berorganisasi, berani berbicara di depan umum, dan merancang program untuk teman-teman.', 5, 'aktif', 1, 1, NULL, '2026-04-01 19:11:49'),
(4, 'Bima Sakti', 'XI IPA 1', NULL, 'seminar', 'Seminar Motivasi Alumni', 'Seminar motivasi alumni MAN 1 Bangka membuka wawasan saya tentang masa depan. Kisah sukses alumni menginspirasi saya untuk terus berjuang dan tidak menyerah!', 4, 'aktif', 1, 1, NULL, '2026-04-01 19:11:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agenda`
--
ALTER TABLE `agenda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_agenda_org` (`organisasi_id`),
  ADD KEY `fk_agenda_ekskul` (`ekskul_id`);

--
-- Indexes for table `anggota_organisasi`
--
ALTER TABLE `anggota_organisasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_anggota_org` (`organisasi_id`);

--
-- Indexes for table `arsip`
--
ALTER TABLE `arsip`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_arsip_org` (`organisasi_id`),
  ADD KEY `fk_arsip_ekskul` (`ekskul_id`);

--
-- Indexes for table `dokumentasi`
--
ALTER TABLE `dokumentasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_dok_org` (`organisasi_id`),
  ADD KEY `fk_dok_ekskul` (`ekskul_id`);

--
-- Indexes for table `ekstrakurikuler`
--
ALTER TABLE `ekstrakurikuler`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ekskul_pembina` (`pembina_id`);

--
-- Indexes for table `karya_siswa`
--
ALTER TABLE `karya_siswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_karya_ekskul` (`ekskul_id`);

--
-- Indexes for table `kontak_pembina`
--
ALTER TABLE `kontak_pembina`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `organisasi`
--
ALTER TABLE `organisasi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pendaftaran_ekskul`
--
ALTER TABLE `pendaftaran_ekskul`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pendaftaran_ekskul` (`ekstrakurikuler_id`);

--
-- Indexes for table `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pengumuman_org` (`organisasi_id`);

--
-- Indexes for table `prestasi`
--
ALTER TABLE `prestasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_prestasi_ekskul` (`ekskul_id`);

--
-- Indexes for table `program_kerja`
--
ALTER TABLE `program_kerja`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_proker_org` (`organisasi_id`);

--
-- Indexes for table `testimoni`
--
ALTER TABLE `testimoni`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_testimoni_org` (`organisasi_id`),
  ADD KEY `fk_testimoni_ekskul` (`ekskul_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agenda`
--
ALTER TABLE `agenda`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `anggota_organisasi`
--
ALTER TABLE `anggota_organisasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `arsip`
--
ALTER TABLE `arsip`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `dokumentasi`
--
ALTER TABLE `dokumentasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ekstrakurikuler`
--
ALTER TABLE `ekstrakurikuler`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `karya_siswa`
--
ALTER TABLE `karya_siswa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `kontak_pembina`
--
ALTER TABLE `kontak_pembina`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `organisasi`
--
ALTER TABLE `organisasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pendaftaran_ekskul`
--
ALTER TABLE `pendaftaran_ekskul`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengumuman`
--
ALTER TABLE `pengumuman`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `prestasi`
--
ALTER TABLE `prestasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `program_kerja`
--
ALTER TABLE `program_kerja`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `testimoni`
--
ALTER TABLE `testimoni`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agenda`
--
ALTER TABLE `agenda`
  ADD CONSTRAINT `fk_agenda_ekskul` FOREIGN KEY (`ekskul_id`) REFERENCES `ekstrakurikuler` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_agenda_org` FOREIGN KEY (`organisasi_id`) REFERENCES `organisasi` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `anggota_organisasi`
--
ALTER TABLE `anggota_organisasi`
  ADD CONSTRAINT `fk_anggota_org` FOREIGN KEY (`organisasi_id`) REFERENCES `organisasi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `arsip`
--
ALTER TABLE `arsip`
  ADD CONSTRAINT `fk_arsip_ekskul` FOREIGN KEY (`ekskul_id`) REFERENCES `ekstrakurikuler` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_arsip_org` FOREIGN KEY (`organisasi_id`) REFERENCES `organisasi` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `dokumentasi`
--
ALTER TABLE `dokumentasi`
  ADD CONSTRAINT `fk_dok_ekskul` FOREIGN KEY (`ekskul_id`) REFERENCES `ekstrakurikuler` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_dok_org` FOREIGN KEY (`organisasi_id`) REFERENCES `organisasi` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ekstrakurikuler`
--
ALTER TABLE `ekstrakurikuler`
  ADD CONSTRAINT `fk_ekskul_pembina` FOREIGN KEY (`pembina_id`) REFERENCES `kontak_pembina` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `karya_siswa`
--
ALTER TABLE `karya_siswa`
  ADD CONSTRAINT `fk_karya_ekskul` FOREIGN KEY (`ekskul_id`) REFERENCES `ekstrakurikuler` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pendaftaran_ekskul`
--
ALTER TABLE `pendaftaran_ekskul`
  ADD CONSTRAINT `fk_pendaftaran_ekskul` FOREIGN KEY (`ekstrakurikuler_id`) REFERENCES `ekstrakurikuler` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD CONSTRAINT `fk_pengumuman_org` FOREIGN KEY (`organisasi_id`) REFERENCES `organisasi` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `prestasi`
--
ALTER TABLE `prestasi`
  ADD CONSTRAINT `fk_prestasi_ekskul` FOREIGN KEY (`ekskul_id`) REFERENCES `ekstrakurikuler` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `program_kerja`
--
ALTER TABLE `program_kerja`
  ADD CONSTRAINT `fk_proker_org` FOREIGN KEY (`organisasi_id`) REFERENCES `organisasi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `testimoni`
--
ALTER TABLE `testimoni`
  ADD CONSTRAINT `fk_testimoni_ekskul` FOREIGN KEY (`ekskul_id`) REFERENCES `ekstrakurikuler` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_testimoni_org` FOREIGN KEY (`organisasi_id`) REFERENCES `organisasi` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
