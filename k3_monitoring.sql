-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 10 Jul 2025 pada 16.35
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `k3_monitoring`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `inspeksi`
--

CREATE TABLE `inspeksi` (
  `id_inspeksi` int(11) NOT NULL,
  `id_kontrak` int(11) NOT NULL,
  `tanggal_inspeksi` date NOT NULL,
  `lokasi` varchar(255) NOT NULL,
  `objek_diperiksa` varchar(255) NOT NULL,
  `kondisi` enum('baik','kurang baik','tidak layak') NOT NULL,
  `temuan` text DEFAULT NULL,
  `tindakan_rekomendasi` text DEFAULT NULL,
  `petugas_id` int(11) NOT NULL,
  `status_perbaikan` enum('perlu','selesai') DEFAULT 'perlu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `inspeksi`
--

INSERT INTO `inspeksi` (`id_inspeksi`, `id_kontrak`, `tanggal_inspeksi`, `lokasi`, `objek_diperiksa`, `kondisi`, `temuan`, `tindakan_rekomendasi`, `petugas_id`, `status_perbaikan`, `created_at`) VALUES
(9, 9, '2025-06-23', 'Baturaja', 'Motor Diesel (Generator Listrik)', 'baik', 'Dari pengujian yang dilakukan, diperoleh hasil bahwa emergency stop dapat berfungsi dengan baik', 'Tidak ada', 2, 'selesai', '2025-06-23 08:12:16'),
(10, 11, '2025-06-23', 'Muara Enim', 'Instalasi Fire Protection', 'baik', 'Instalasi Fire Protection', 'Tidak Ada', 2, 'selesai', '2025-06-23 09:26:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kontrak`
--

CREATE TABLE `kontrak` (
  `id_kontrak` int(11) NOT NULL,
  `id_pengajuan` int(11) NOT NULL,
  `id_objek` int(11) DEFAULT NULL,
  `file_kontrak` varchar(255) NOT NULL,
  `tanggal_upload` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggal_berakhir` date NOT NULL,
  `status` enum('aktif','berakhir','kedaluwarsa') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kontrak`
--

INSERT INTO `kontrak` (`id_kontrak`, `id_pengajuan`, `id_objek`, `file_kontrak`, `tanggal_upload`, `tanggal_berakhir`, `status`) VALUES
(9, 11, 13, '68590b5273bfe_Amandemen 01 - SERD Fire Protection.pdf', '2025-06-23 08:07:46', '2025-09-23', 'aktif'),
(11, 12, 14, '68591db2663bc_Amandemen 01 - SERD Fire Protection.pdf', '2025-06-23 09:26:10', '2025-09-23', 'aktif');

-- --------------------------------------------------------

--
-- Struktur dari tabel `objek_inspeksi`
--

CREATE TABLE `objek_inspeksi` (
  `id_objek` int(11) NOT NULL,
  `id_pengajuan` int(11) NOT NULL,
  `jenis_objek` varchar(100) NOT NULL,
  `spesifikasi_teknis` varchar(255) DEFAULT NULL,
  `manual_operasi` varchar(255) DEFAULT NULL,
  `gambar_teknis` varchar(255) DEFAULT NULL,
  `laporan_hasil` varchar(255) DEFAULT NULL,
  `pengesahan_pemakaian` varchar(255) DEFAULT NULL,
  `catatan_pemeliharaan` varchar(255) DEFAULT NULL,
  `surat_izin_operator` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `objek_inspeksi`
--

INSERT INTO `objek_inspeksi` (`id_objek`, `id_pengajuan`, `jenis_objek`, `spesifikasi_teknis`, `manual_operasi`, `gambar_teknis`, `laporan_hasil`, `pengesahan_pemakaian`, `catatan_pemeliharaan`, `surat_izin_operator`) VALUES
(13, 11, 'Alat Berat', '68590abce34c8_Laporan Riksa Uji Motor Diesel GITET Lumut Balai.pdf', '68590abce3a32_Operation Installation Manual Book.pdf', '', '', '', '', '68590abce3dfd_Serkom Operator.pdf'),
(14, 12, 'Instalasi Proteksi Kebakaran', '68591d93cad79_Spesifikasi_Teknik_Instalasi.pdf', '68591d93cb969_Report Riksa Uji IPK Area WP B Electrical Service Building_compressed.pdf', '68591d93cc034_Technical Drawing RD-E-FD-S00-1001_Rev.1_Fire Alarm Wiring Layout for SGS Cluster  AB.pdf', '68591d93cc698_Suket Sebelumnya RD-SMG-GEM-PLC-23-0012 Suket Layak K3 Installasi Penyalur Petir.pdf', '', '', '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengajuan`
--

CREATE TABLE `pengajuan` (
  `id_pengajuan` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `nama_client` varchar(100) NOT NULL,
  `nama_perusahaan` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `npwp` varchar(20) NOT NULL,
  `kegiatan_tic` varchar(50) NOT NULL,
  `wilayah` varchar(100) NOT NULL,
  `telp` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `tanggal_pengajuan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengajuan`
--

INSERT INTO `pengajuan` (`id_pengajuan`, `id_user`, `nama_client`, `nama_perusahaan`, `alamat`, `npwp`, `kegiatan_tic`, `wilayah`, `telp`, `email`, `status`, `tanggal_pengajuan`) VALUES
(11, 9, 'PT PLN (Persero) UIP3B Sumatera UPT Baturaja', 'PT PLN (Persero) UIP3B Sumatera UPT Baturaja', 'Jalan Kapten M. Nur No.258 C Baturaja', '0987654321', 'inspeksi', 'Baturaja', '00000000002', 'ptplnuip3b@k3.com', 'approved', '2025-06-23 08:05:16'),
(12, 10, 'PT SUPREME ENERGY RANTAU REDAP', 'PT SUPREME ENERGY RANTAU REDAP', 'Rantau Redap Desa Segamit Kecamatan Semende Darat Ulu Kabupaten Muara Enim Provinsi Sumatera Selatan', '02.742.114.8-313.001', 'inspeksi', 'Muara Enim', '00000000001', 'rantauredap@k3.com', 'approved', '2025-06-23 09:25:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sertifikat`
--

CREATE TABLE `sertifikat` (
  `id_sertifikat` int(11) NOT NULL,
  `id_inspeksi` int(11) NOT NULL,
  `file_sertifikat` varchar(255) NOT NULL,
  `tanggal_terbit` date NOT NULL,
  `tanggal_berakhir` date NOT NULL,
  `status` enum('active','expired') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `sertifikat`
--

INSERT INTO `sertifikat` (`id_sertifikat`, `id_inspeksi`, `file_sertifikat`, `tanggal_terbit`, `tanggal_berakhir`, `status`, `created_at`) VALUES
(6, 9, '68590c927f11f.pdf', '2025-06-23', '2026-06-23', 'active', '2025-06-23 08:13:06'),
(8, 10, '68591df6d9c98.pdf', '2025-06-23', '2026-06-23', 'active', '2025-06-23 09:27:18'),
(9, 9, '6859f898c4099.pdf', '2025-06-24', '2026-06-24', 'active', '2025-06-24 01:00:08');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','staff','pelanggan') DEFAULT 'pelanggan',
  `avatar` varchar(255) DEFAULT 'default.svg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `nama`, `email`, `password`, `role`, `avatar`, `created_at`) VALUES
(1, 'M. Haqi Priyono', 'admin@k3.com', '$2y$10$dZFLI1zCxMaI3O54uKJD5ef1IwKI48Ymb1UWgjBNZWZKZCPlf3ami', 'admin', 'avatar_1_1748587609.jpg', '2025-05-06 16:22:21'),
(2, 'Rifky Juliansyah', 'staff@k3.com', '$2y$10$D29A.qMy167bgB1DFPFT2.gzywIDPuNt.uNxbxIQ//a76R53alcTa', 'staff', 'avatar_2_1749381008.jpg', '2025-05-06 16:22:21'),
(4, 'Andi Amary', 'staff2@k3.com', '$2y$10$YZNv/LAu7IlWfrsDIoiXd.jsX12wy5HiN8WZAFISOxnElRl.jCvcm', 'staff', 'default.svg', '2025-05-06 18:02:52'),
(6, 'Ardhi Putra', 'staff3@k3.com', '$2y$10$Bd6UJrn45dMkXBvo.eBjQeOm./XBxa2Y0TmDkdx9AIVm9e9K0jDEa', 'staff', 'default.svg', '2025-05-08 06:59:06'),
(7, 'Hendro Saputra', 'staff4@k3.com', '$2y$10$MQerApXC5sEa7i5uBYxSTOEJvoRqC3J14QYvPlrcTJo3wIP5LpHsO', 'staff', 'default.svg', '2025-05-08 06:59:34'),
(9, 'PT PLN (Persero) UIP3B Sumatera UPT Batu Raja', 'ptplnuip3b@k3.com', '$2y$10$io8TBRSvhAgEims4JtdxaefnbaqAJM0PifWGbfVYk/2PF3WCCQRLO', 'pelanggan', 'default.svg', '2025-06-22 10:42:48'),
(10, 'PT SUPREME ENERGY RANTAU REDAP', 'rantauredap@k3.com', '$2y$10$UG8K1nOr9bY6yPB2RWFyeu0RnaX8gk3N38RCvoMK1KTwRZSE5775i', 'pelanggan', 'default.svg', '2025-06-23 09:23:04');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `inspeksi`
--
ALTER TABLE `inspeksi`
  ADD PRIMARY KEY (`id_inspeksi`),
  ADD KEY `kontrak_id` (`id_kontrak`),
  ADD KEY `petugas_id` (`petugas_id`);

--
-- Indeks untuk tabel `kontrak`
--
ALTER TABLE `kontrak`
  ADD PRIMARY KEY (`id_kontrak`),
  ADD KEY `pengajuan_id` (`id_pengajuan`),
  ADD KEY `kontrak_objek_fk` (`id_objek`);

--
-- Indeks untuk tabel `objek_inspeksi`
--
ALTER TABLE `objek_inspeksi`
  ADD PRIMARY KEY (`id_objek`),
  ADD KEY `pengajuan_id` (`id_pengajuan`);

--
-- Indeks untuk tabel `pengajuan`
--
ALTER TABLE `pengajuan`
  ADD PRIMARY KEY (`id_pengajuan`),
  ADD KEY `user_id` (`id_user`);

--
-- Indeks untuk tabel `sertifikat`
--
ALTER TABLE `sertifikat`
  ADD PRIMARY KEY (`id_sertifikat`),
  ADD KEY `inspeksi_id` (`id_inspeksi`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `inspeksi`
--
ALTER TABLE `inspeksi`
  MODIFY `id_inspeksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `kontrak`
--
ALTER TABLE `kontrak`
  MODIFY `id_kontrak` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `objek_inspeksi`
--
ALTER TABLE `objek_inspeksi`
  MODIFY `id_objek` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `pengajuan`
--
ALTER TABLE `pengajuan`
  MODIFY `id_pengajuan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `sertifikat`
--
ALTER TABLE `sertifikat`
  MODIFY `id_sertifikat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `inspeksi`
--
ALTER TABLE `inspeksi`
  ADD CONSTRAINT `inspeksi_ibfk_1` FOREIGN KEY (`id_kontrak`) REFERENCES `kontrak` (`id_kontrak`),
  ADD CONSTRAINT `inspeksi_ibfk_2` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id_user`);

--
-- Ketidakleluasaan untuk tabel `kontrak`
--
ALTER TABLE `kontrak`
  ADD CONSTRAINT `kontrak_ibfk_1` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan` (`id_pengajuan`) ON DELETE CASCADE,
  ADD CONSTRAINT `kontrak_objek_fk` FOREIGN KEY (`id_objek`) REFERENCES `objek_inspeksi` (`id_objek`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `objek_inspeksi`
--
ALTER TABLE `objek_inspeksi`
  ADD CONSTRAINT `objek_inspeksi_ibfk_1` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan` (`id_pengajuan`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pengajuan`
--
ALTER TABLE `pengajuan`
  ADD CONSTRAINT `pengajuan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `sertifikat`
--
ALTER TABLE `sertifikat`
  ADD CONSTRAINT `sertifikat_ibfk_1` FOREIGN KEY (`id_inspeksi`) REFERENCES `inspeksi` (`id_inspeksi`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
