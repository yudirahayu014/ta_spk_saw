-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 16, 2026 at 01:38 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `spk_kedisiplinan`
--

-- --------------------------------------------------------

--
-- Table structure for table `karyawan`
--

CREATE TABLE `karyawan` (
  `id` int(10) UNSIGNED NOT NULL,
  `kode` varchar(10) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `departemen` varchar(100) NOT NULL,
  `jabatan` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `karyawan`
--

INSERT INTO `karyawan` (`id`, `kode`, `nama`, `departemen`, `jabatan`, `created_at`, `updated_at`) VALUES
(1, 'EMP001', 'Budi Santoso', 'Teknologi Informasi', 'Staff IT', '2026-06-15 15:54:57', '2026-06-15 15:54:57'),
(2, 'EMP002', 'Siti Rahayu', 'Sumber Daya Manusia', 'Staff HRD', '2026-06-15 15:54:57', '2026-06-15 15:54:57'),
(3, 'EMP003', 'Ahmad Fauzi', 'Keuangan', 'Staf Keuangan', '2026-06-15 15:54:57', '2026-06-15 15:54:57'),
(6, 'MP1923', 'YUDI GEMBENG', 'FRONTEND', 'IT', '2026-06-16 11:34:56', '2026-06-16 11:34:56');

-- --------------------------------------------------------

--
-- Table structure for table `kriteria`
--

CREATE TABLE `kriteria` (
  `id` int(10) UNSIGNED NOT NULL,
  `kode` varchar(10) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `bobot` decimal(5,2) NOT NULL COMMENT 'Bobot dalam persen (total = 100)',
  `atribut` enum('benefit','cost') NOT NULL COMMENT 'benefit=max, cost=min',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kriteria`
--

INSERT INTO `kriteria` (`id`, `kode`, `nama`, `bobot`, `atribut`, `keterangan`, `created_at`) VALUES
(1, 'C1', 'Tingkat Kehadiran (%)', 30.00, 'benefit', 'Persentase kehadiran karyawan dalam sebulan (semakin tinggi semakin baik)', '2026-06-15 15:54:57'),
(2, 'C2', 'Jumlah Keterlambatan (hari)', 25.00, 'cost', 'Total hari karyawan datang terlambat (semakin sedikit semakin baik)', '2026-06-15 15:54:57'),
(3, 'C3', 'Izin Tidak Resmi (hari)', 20.00, 'cost', 'Jumlah izin tanpa surat keterangan resmi (semakin sedikit semakin baik)', '2026-06-15 15:54:57'),
(4, 'C4', 'Ketepatan Waktu Lembur (%)', 15.00, 'benefit', 'Persentase kesediaan lembur sesuai jadwal (semakin tinggi semakin baik)', '2026-06-15 15:54:57'),
(5, 'C5', 'Pelanggaran Tata Tertib', 10.00, 'cost', 'Jumlah pelanggaran SOP dan tata tertib (semakin sedikit semakin baik)', '2026-06-15 15:54:57');

-- --------------------------------------------------------

--
-- Table structure for table `nilai_matriks`
--

CREATE TABLE `nilai_matriks` (
  `id` int(10) UNSIGNED NOT NULL,
  `karyawan_id` int(10) UNSIGNED NOT NULL,
  `kriteria_id` int(10) UNSIGNED NOT NULL,
  `nilai` decimal(8,2) NOT NULL,
  `periode` varchar(20) NOT NULL DEFAULT '2025-01' COMMENT 'Format: YYYY-MM',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nilai_matriks`
--

INSERT INTO `nilai_matriks` (`id`, `karyawan_id`, `kriteria_id`, `nilai`, `periode`, `updated_at`) VALUES
(1, 1, 1, 95.00, '2025-01', '2026-06-15 15:54:57'),
(2, 1, 2, 2.00, '2025-01', '2026-06-15 15:54:57'),
(3, 1, 3, 0.00, '2025-01', '2026-06-15 15:54:57'),
(4, 1, 4, 90.00, '2025-01', '2026-06-15 15:54:57'),
(5, 1, 5, 1.00, '2025-01', '2026-06-15 15:54:57'),
(6, 2, 1, 88.00, '2025-01', '2026-06-15 15:54:57'),
(7, 2, 2, 4.00, '2025-01', '2026-06-15 15:54:57'),
(8, 2, 3, 1.00, '2025-01', '2026-06-15 15:54:57'),
(9, 2, 4, 75.00, '2025-01', '2026-06-15 15:54:57'),
(10, 2, 5, 0.00, '2025-01', '2026-06-15 15:54:57'),
(11, 3, 1, 92.00, '2025-01', '2026-06-15 15:54:57'),
(12, 3, 2, 1.00, '2025-01', '2026-06-15 15:54:57'),
(13, 3, 3, 2.00, '2025-01', '2026-06-15 15:54:57'),
(14, 3, 4, 85.00, '2025-01', '2026-06-15 15:54:57'),
(15, 3, 5, 2.00, '2025-01', '2026-06-15 15:54:57');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `created_at`) VALUES
(1, 'Administrator', 'admin@spk.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-06-15 15:54:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `karyawan`
--
ALTER TABLE `karyawan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode` (`kode`);

--
-- Indexes for table `kriteria`
--
ALTER TABLE `kriteria`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode` (`kode`);

--
-- Indexes for table `nilai_matriks`
--
ALTER TABLE `nilai_matriks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_karyawan_kriteria_periode` (`karyawan_id`,`kriteria_id`,`periode`),
  ADD KEY `fk_nilai_kriteria` (`kriteria_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `karyawan`
--
ALTER TABLE `karyawan`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `kriteria`
--
ALTER TABLE `kriteria`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `nilai_matriks`
--
ALTER TABLE `nilai_matriks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `nilai_matriks`
--
ALTER TABLE `nilai_matriks`
  ADD CONSTRAINT `fk_nilai_karyawan` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_nilai_kriteria` FOREIGN KEY (`kriteria_id`) REFERENCES `kriteria` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
