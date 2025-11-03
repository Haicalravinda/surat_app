-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 03 Nov 2025 pada 08.25
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.3.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `surat_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `bagian`
--

CREATE TABLE `bagian` (
  `id_bagian` int(11) NOT NULL,
  `nama_bagian` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `bagian`
--

INSERT INTO `bagian` (`id_bagian`, `nama_bagian`) VALUES
(1, 'Bagian A'),
(2, 'Bagian B');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id_notif` int(11) NOT NULL,
  `id_surat` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notifikasi`
--

INSERT INTO `notifikasi` (`id_notif`, `id_surat`, `id_user`, `status`, `created_at`) VALUES
(12, 4, 8, 'read', '2025-11-03 12:27:04'),
(13, 4, 12, 'read', '2025-11-03 12:27:04'),
(14, 4, 13, 'read', '2025-11-03 12:27:04'),
(15, 4, 6, 'read', '2025-11-03 12:27:04'),
(16, 4, 14, 'read', '2025-11-03 12:27:04'),
(17, 4, 15, 'unread', '2025-11-03 12:27:04'),
(18, 4, 17, 'read', '2025-11-03 12:27:04'),
(19, 4, 18, 'read', '2025-11-03 12:27:04'),
(20, 4, 2, 'read', '2025-11-03 12:27:04'),
(21, 5, 10, 'unread', '2025-11-03 12:34:06'),
(22, 5, 16, 'read', '2025-11-03 12:34:06'),
(23, 6, 11, 'read', '2025-11-03 12:36:22'),
(24, 6, 12, 'read', '2025-11-03 12:36:22'),
(25, 6, 13, 'unread', '2025-11-03 12:36:22'),
(26, 6, 14, 'unread', '2025-11-03 12:36:22'),
(27, 6, 15, 'unread', '2025-11-03 12:36:22'),
(28, 6, 2, 'read', '2025-11-03 12:36:22'),
(29, 7, 8, 'read', '2025-11-03 14:13:00'),
(30, 7, 6, 'unread', '2025-11-03 14:13:00'),
(31, 7, 17, 'unread', '2025-11-03 14:13:00'),
(32, 7, 18, 'unread', '2025-11-03 14:13:00'),
(33, 7, 2, 'read', '2025-11-03 14:13:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `subbag`
--

CREATE TABLE `subbag` (
  `id_subbag` int(11) NOT NULL,
  `id_bagian` int(11) NOT NULL,
  `nama_subbag` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `subbag`
--

INSERT INTO `subbag` (`id_subbag`, `id_bagian`, `nama_subbag`) VALUES
(1, 1, 'Subag A1'),
(2, 1, 'Subag A2'),
(3, 2, 'Subag B1'),
(4, 2, 'Subag B2');

-- --------------------------------------------------------

--
-- Struktur dari tabel `surat`
--

CREATE TABLE `surat` (
  `id_surat` int(11) NOT NULL,
  `pengirim_id` int(11) NOT NULL,
  `penerima_id` int(11) NOT NULL,
  `judul` varchar(30) NOT NULL,
  `isi` text NOT NULL,
  `tanggal_kirim` datetime NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `surat`
--

INSERT INTO `surat` (`id_surat`, `pengirim_id`, `penerima_id`, `judul`, `isi`, `tanggal_kirim`, `status`) VALUES
(4, 7, 8, 'mas sevaaa', 'ok', '2025-11-03 12:27:04', 'read'),
(5, 19, 10, 'tes no 2', 'masss iki yaa', '2025-11-03 12:34:06', 'unread'),
(6, 1, 11, 'no 3', 'oaskaksaksa', '2025-11-03 12:36:22', 'read'),
(7, 1, 8, 'test mass', 'bismillah', '2025-11-03 14:13:00', 'read');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(30) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(20) NOT NULL,
  `jabatan` enum('kepala','sekretaris','kabag','kasubag','staff') NOT NULL,
  `bagian_id` int(11) DEFAULT NULL,
  `subbag_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `nama`, `username`, `password`, `jabatan`, `bagian_id`, `subbag_id`, `created_at`) VALUES
(1, 'Bapak Kepala', 'kepala', '$2y$10$o5x5ii5/.U.3j', 'kepala', NULL, NULL, '2025-11-03 10:56:16'),
(2, 'Ibu Sekretaris', 'sekretaris', '$2y$10$T6CLOUmnYzsk.', 'sekretaris', NULL, NULL, '2025-11-03 10:56:16'),
(6, 'ananda andra', 'andra', '$2y$10$U0Q5V7v13OPjH', 'kasubag', 2, 3, '2025-11-03 11:08:45'),
(7, 'Arkhan', 'arkhan', '$2y$10$oXcsaXARPf36l', 'staff', 1, 1, '2025-11-03 11:10:38'),
(8, 'seva nonda', 'sevaa', '$2y$10$K3/33ZrmJ8wbD', 'staff', 2, 3, '2025-11-03 11:11:23'),
(10, 'rizky', 'rizky', '$2y$10$SL0tse6wAVFwg', 'staff', 2, 4, '2025-11-03 11:12:49'),
(11, 'yanto', 'yanto', '$2y$10$QFY3mt0vqv1eH', 'staff', 1, 1, '2025-11-03 11:34:41'),
(12, 'dimas ahmad', 'dimas', '$2y$10$Si76eqvI.8Smh', 'kasubag', 1, 1, '2025-11-03 12:19:13'),
(13, 'aryo', 'aryoo', '$2y$10$jynOIEdzcv/jb', 'kasubag', 1, 1, '2025-11-03 12:20:10'),
(14, 'ikmal', 'ikmal', '$2y$10$C2FEEQubNIqKo', 'kabag', 1, NULL, '2025-11-03 12:21:56'),
(15, 'yoga', 'yoga', '$2y$10$SCCs7Si2fkQvg', 'kabag', 1, NULL, '2025-11-03 12:22:31'),
(16, 'zico', 'zico', '$2y$10$p.kVXVwgKeB6K', 'kasubag', 2, 4, '2025-11-03 12:23:09'),
(17, 'inas', 'inas', '$2y$10$HjKrESo4ZaBbH', 'kabag', 2, NULL, '2025-11-03 12:24:01'),
(18, 'vivi', 'vivi', '$2y$10$6YO91QWULUIZr', 'kabag', 2, NULL, '2025-11-03 12:24:18'),
(19, 'azzam', 'azzam', '$2y$10$oxDiyTQWHoCkx', 'staff', 2, 4, '2025-11-03 12:32:49');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `bagian`
--
ALTER TABLE `bagian`
  ADD PRIMARY KEY (`id_bagian`);

--
-- Indeks untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id_notif`),
  ADD KEY `id_surat` (`id_surat`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `subbag`
--
ALTER TABLE `subbag`
  ADD PRIMARY KEY (`id_subbag`),
  ADD KEY `id_bagian` (`id_bagian`);

--
-- Indeks untuk tabel `surat`
--
ALTER TABLE `surat`
  ADD PRIMARY KEY (`id_surat`),
  ADD KEY `pengirim_id` (`pengirim_id`),
  ADD KEY `penerima_id` (`penerima_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `bagian_id` (`bagian_id`),
  ADD KEY `subbag_id` (`subbag_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `bagian`
--
ALTER TABLE `bagian`
  MODIFY `id_bagian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id_notif` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT untuk tabel `subbag`
--
ALTER TABLE `subbag`
  MODIFY `id_subbag` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `surat`
--
ALTER TABLE `surat`
  MODIFY `id_surat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`id_surat`) REFERENCES `surat` (`id_surat`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifikasi_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `subbag`
--
ALTER TABLE `subbag`
  ADD CONSTRAINT `subbag_ibfk_1` FOREIGN KEY (`id_bagian`) REFERENCES `bagian` (`id_bagian`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `surat`
--
ALTER TABLE `surat`
  ADD CONSTRAINT `surat_ibfk_1` FOREIGN KEY (`pengirim_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `surat_ibfk_2` FOREIGN KEY (`penerima_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`bagian_id`) REFERENCES `bagian` (`id_bagian`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`subbag_id`) REFERENCES `subbag` (`id_subbag`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
