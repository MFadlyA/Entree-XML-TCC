-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 20 Jan 2025 pada 11.12
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
-- Database: `aplikasi_kewirausahaan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `anggota_kelompok`
--

CREATE TABLE `anggota_kelompok` (
  `id` int(11) NOT NULL,
  `id_kelompok` int(11) DEFAULT NULL,
  `npm_anggota` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Trigger `anggota_kelompok`
--
DELIMITER $$
CREATE TRIGGER `after_insert_anggota_kelompok` AFTER INSERT ON `anggota_kelompok` FOR EACH ROW BEGIN
    INSERT INTO anggota_kelompok_backup (
        id, id_kelompok, npm_anggota
    ) VALUES (
        NEW.id, NEW.id_kelompok, NEW.npm_anggota
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_update_anggota_kelompok` AFTER UPDATE ON `anggota_kelompok` FOR EACH ROW BEGIN
    UPDATE anggota_kelompok_backup
    SET 
        id_kelompok = NEW.id_kelompok,
        npm_anggota = NEW.npm_anggota
    WHERE id = NEW.id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `anggota_kelompok_backup`
--

CREATE TABLE `anggota_kelompok_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `id_kelompok` int(11) DEFAULT NULL,
  `npm_anggota` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwal`
--

CREATE TABLE `jadwal` (
  `id` int(11) NOT NULL,
  `nama_kegiatan` varchar(255) NOT NULL,
  `tanggal` date NOT NULL,
  `waktu` varchar(5) NOT NULL,
  `agenda` text NOT NULL,
  `lokasi` varchar(255) NOT NULL,
  `feedback_mentor` varchar(2000) NOT NULL,
  `status` enum('menunggu','ditolak','disetujui','alternatif','selesai') NOT NULL,
  `bukti_kegiatan` varchar(255) DEFAULT NULL,
  `id_klmpk` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Trigger `jadwal`
--
DELIMITER $$
CREATE TRIGGER `after_insert_jadwal` AFTER INSERT ON `jadwal` FOR EACH ROW BEGIN
    INSERT INTO jadwal_backup (
        id, nama_kegiatan, tanggal, waktu, agenda, lokasi, feedback_mentor, 
        status, bukti_kegiatan, id_klmpk
    ) VALUES (
        NEW.id, NEW.nama_kegiatan, NEW.tanggal, NEW.waktu, NEW.agenda, 
        NEW.lokasi, NEW.feedback_mentor, NEW.status, NEW.bukti_kegiatan, NEW.id_klmpk
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_update_jadwal` AFTER UPDATE ON `jadwal` FOR EACH ROW BEGIN
    UPDATE jadwal_backup
    SET 
        nama_kegiatan = NEW.nama_kegiatan,
        tanggal = NEW.tanggal,
        waktu = NEW.waktu,
        agenda = NEW.agenda,
        lokasi = NEW.lokasi,
        feedback_mentor = NEW.feedback_mentor,
        status = NEW.status,
        bukti_kegiatan = NEW.bukti_kegiatan,
        id_klmpk = NEW.id_klmpk
    WHERE id = NEW.id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwal_backup`
--

CREATE TABLE `jadwal_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `nama_kegiatan` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tanggal` date NOT NULL,
  `waktu` varchar(5) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `agenda` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `lokasi` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `feedback_mentor` varchar(2000) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `status` enum('menunggu','ditolak','disetujui','alternatif','selesai') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `bukti_kegiatan` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `id_klmpk` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kelompok_bisnis`
--

CREATE TABLE `kelompok_bisnis` (
  `id_kelompok` int(11) NOT NULL,
  `npm_ketua` varchar(20) DEFAULT NULL,
  `nama_kelompok` varchar(50) DEFAULT NULL,
  `nama_bisnis` varchar(50) DEFAULT NULL,
  `logo_bisnis` varchar(255) DEFAULT NULL,
  `tahun_akademik_id` int(11) DEFAULT NULL,
  `kategori_bisnis` varchar(255) DEFAULT NULL,
  `sdg` varchar(255) DEFAULT NULL,
  `ide_bisnis` varchar(255) DEFAULT NULL,
  `id_mentor` int(11) DEFAULT NULL,
  `status_inkubasi` enum('direkomendasikan','disetujui','ditolak','masuk','tidak masuk') DEFAULT NULL,
  `status_kelompok_bisnis` enum('aktif','tidak aktif') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Trigger `kelompok_bisnis`
--
DELIMITER $$
CREATE TRIGGER `after_kelompok_bisnis_insert` AFTER INSERT ON `kelompok_bisnis` FOR EACH ROW BEGIN
  INSERT INTO kelompok_bisnis_backup 
  (id_kelompok, npm_ketua, nama_kelompok, nama_bisnis, logo_bisnis, tahun_akademik_id, kategori_bisnis, sdg, ide_bisnis, id_mentor, status_inkubasi, status_kelompok_bisnis)
  VALUES 
  (NEW.id_kelompok, NEW.npm_ketua, NEW.nama_kelompok, NEW.nama_bisnis, NEW.logo_bisnis, NEW.tahun_akademik_id, NEW.kategori_bisnis, NEW.sdg, NEW.ide_bisnis, NEW.id_mentor, NEW.status_inkubasi, NEW.status_kelompok_bisnis);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_kelompok_bisnis_update` AFTER UPDATE ON `kelompok_bisnis` FOR EACH ROW BEGIN
  UPDATE kelompok_bisnis_backup
  SET 
    npm_ketua = NEW.npm_ketua,
    nama_kelompok = NEW.nama_kelompok,
    nama_bisnis = NEW.nama_bisnis,
    logo_bisnis = NEW.logo_bisnis,
    tahun_akademik_id = NEW.tahun_akademik_id,
    kategori_bisnis = NEW.kategori_bisnis,
    sdg = NEW.sdg,
    ide_bisnis = NEW.ide_bisnis,
    id_mentor = NEW.id_mentor,
    status_inkubasi = NEW.status_inkubasi,
    status_kelompok_bisnis = NEW.status_kelompok_bisnis
  WHERE id_kelompok = NEW.id_kelompok;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kelompok_bisnis_backup`
--

CREATE TABLE `kelompok_bisnis_backup` (
  `id_kelompok` int(11) NOT NULL,
  `npm_ketua` varchar(20) DEFAULT NULL,
  `nama_kelompok` varchar(50) DEFAULT NULL,
  `nama_bisnis` varchar(50) DEFAULT NULL,
  `logo_bisnis` varchar(255) DEFAULT NULL,
  `tahun_akademik_id` int(11) DEFAULT NULL,
  `kategori_bisnis` varchar(255) DEFAULT NULL,
  `sdg` varchar(255) DEFAULT NULL,
  `ide_bisnis` varchar(255) DEFAULT NULL,
  `id_mentor` int(11) DEFAULT NULL,
  `status_inkubasi` enum('direkomendasikan','disetujui','ditolak','masuk','tidak masuk') DEFAULT NULL,
  `status_kelompok_bisnis` enum('aktif','tidak aktif') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan_bisnis`
--

CREATE TABLE `laporan_bisnis` (
  `id` int(11) NOT NULL,
  `judul_laporan` varchar(255) NOT NULL,
  `jenis_laporan` varchar(30) NOT NULL,
  `laporan_penjualan` text DEFAULT NULL,
  `laporan_pemasaran` text DEFAULT NULL,
  `laporan_produksi` text DEFAULT NULL,
  `laporan_sdm` text DEFAULT NULL,
  `laporan_keuangan` text DEFAULT NULL,
  `laporan_pdf` text DEFAULT NULL,
  `tanggal_upload` datetime NOT NULL DEFAULT current_timestamp(),
  `id_kelompok` int(11) DEFAULT NULL,
  `feedback` varchar(2000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Trigger `laporan_bisnis`
--
DELIMITER $$
CREATE TRIGGER `after_insert_laporan_bisnis` AFTER INSERT ON `laporan_bisnis` FOR EACH ROW BEGIN
    INSERT INTO laporan_bisnis_backup (
        id, judul_laporan, jenis_laporan, laporan_penjualan, laporan_pemasaran, laporan_produksi, 
        laporan_sdm, laporan_keuangan, laporan_pdf, tanggal_upload, id_kelompok, feedback
    ) VALUES (
        NEW.id, NEW.judul_laporan, NEW.jenis_laporan, NEW.laporan_penjualan, NEW.laporan_pemasaran, 
        NEW.laporan_produksi, NEW.laporan_sdm, NEW.laporan_keuangan, NEW.laporan_pdf, 
        NEW.tanggal_upload, NEW.id_kelompok, NEW.feedback
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_update_laporan_bisnis` AFTER UPDATE ON `laporan_bisnis` FOR EACH ROW BEGIN
    UPDATE laporan_bisnis_backup
    SET 
        judul_laporan = NEW.judul_laporan,
        jenis_laporan = NEW.jenis_laporan,
        laporan_penjualan = NEW.laporan_penjualan,
        laporan_pemasaran = NEW.laporan_pemasaran,
        laporan_produksi = NEW.laporan_produksi,
        laporan_sdm = NEW.laporan_sdm,
        laporan_keuangan = NEW.laporan_keuangan,
        laporan_pdf = NEW.laporan_pdf,
        tanggal_upload = NEW.tanggal_upload,
        id_kelompok = NEW.id_kelompok,
        feedback = NEW.feedback
    WHERE id = NEW.id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan_bisnis_backup`
--

CREATE TABLE `laporan_bisnis_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `judul_laporan` varchar(255) NOT NULL,
  `jenis_laporan` varchar(30) NOT NULL,
  `laporan_penjualan` text DEFAULT NULL,
  `laporan_pemasaran` text DEFAULT NULL,
  `laporan_produksi` text DEFAULT NULL,
  `laporan_sdm` text DEFAULT NULL,
  `laporan_keuangan` text DEFAULT NULL,
  `laporan_pdf` text DEFAULT NULL,
  `tanggal_upload` datetime NOT NULL DEFAULT current_timestamp(),
  `id_kelompok` int(11) DEFAULT NULL,
  `feedback` varchar(2000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `npm` varchar(15) NOT NULL,
  `program_studi` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact` varchar(15) DEFAULT NULL,
  `fakultas` varchar(100) DEFAULT NULL,
  `id_kelompok` int(11) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `materi_kewirausahaan`
--

CREATE TABLE `materi_kewirausahaan` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `file_path` varchar(300) NOT NULL,
  `deskripsi` varchar(3000) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `mentor`
--

CREATE TABLE `mentor` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nidn` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact` varchar(15) DEFAULT NULL,
  `keahlian` varchar(100) DEFAULT NULL,
  `fakultas` varchar(100) DEFAULT NULL,
  `prodi` varchar(100) DEFAULT NULL,
  `foto_profile` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `proposal_bisnis`
--

CREATE TABLE `proposal_bisnis` (
  `id` int(11) NOT NULL,
  `judul_proposal` varchar(255) NOT NULL,
  `tahapan_bisnis` varchar(25) NOT NULL,
  `sdg` text NOT NULL,
  `kategori` varchar(255) DEFAULT NULL,
  `other_category` varchar(255) DEFAULT NULL,
  `proposal_pdf` varchar(255) NOT NULL,
  `kelompok_id` int(11) NOT NULL,
  `status` enum('menunggu','ditolak','disetujui') DEFAULT 'menunggu',
  `ide_bisnis` varchar(255) DEFAULT NULL,
  `feedback` varchar(2000) DEFAULT NULL,
  `anggaran` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Trigger `proposal_bisnis`
--
DELIMITER $$
CREATE TRIGGER `after_insert_proposal_bisnis` AFTER INSERT ON `proposal_bisnis` FOR EACH ROW BEGIN
    INSERT INTO proposal_bisnis_backup (
        id, judul_proposal, tahapan_bisnis, sdg, kategori, 
        other_category, proposal_pdf, kelompok_id, status, ide_bisnis, feedback, anggaran
    ) VALUES (
        NEW.id, NEW.judul_proposal, NEW.tahapan_bisnis, NEW.sdg, NEW.kategori, 
        NEW.other_category, NEW.proposal_pdf, NEW.kelompok_id, NEW.status, NEW.ide_bisnis, NEW.feedback, NEW.anggaran
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_update_proposal_bisnis` AFTER UPDATE ON `proposal_bisnis` FOR EACH ROW BEGIN
    UPDATE proposal_bisnis_backup
    SET 
        judul_proposal = NEW.judul_proposal,
        tahapan_bisnis = NEW.tahapan_bisnis,
        sdg = NEW.sdg,
        kategori = NEW.kategori,
        other_category = NEW.other_category,
        proposal_pdf = NEW.proposal_pdf,
        kelompok_id = NEW.kelompok_id,
        status = NEW.status,
        ide_bisnis = NEW.ide_bisnis,
        feedback = NEW.feedback,
        anggaran = NEW.anggaran
    WHERE id = NEW.id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `proposal_bisnis_backup`
--

CREATE TABLE `proposal_bisnis_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `judul_proposal` varchar(255) NOT NULL,
  `tahapan_bisnis` varchar(25) NOT NULL,
  `sdg` text NOT NULL,
  `kategori` varchar(255) DEFAULT NULL,
  `other_category` varchar(255) DEFAULT NULL,
  `proposal_pdf` varchar(255) NOT NULL,
  `kelompok_id` int(11) NOT NULL,
  `status` enum('menunggu','ditolak','disetujui') DEFAULT 'menunggu',
  `ide_bisnis` varchar(255) DEFAULT NULL,
  `feedback` varchar(2000) DEFAULT NULL,
  `anggaran` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tahun_akademik`
--

CREATE TABLE `tahun_akademik` (
  `id` int(11) NOT NULL,
  `tahun` varchar(4) NOT NULL,
  `jenis_tahun` enum('Ganjil','Genap') NOT NULL,
  `status` enum('Aktif','Tidak Aktif') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tahun_akademik`
--

INSERT INTO `tahun_akademik` (`id`, `tahun`, `jenis_tahun`, `status`) VALUES
(16, '2023', 'Ganjil', 'Tidak Aktif'),
(17, '2023', 'Genap', 'Tidak Aktif'),
(18, '2024', 'Ganjil', 'Aktif'),
(19, '2022', 'Ganjil', 'Tidak Aktif'),
(20, '2022', 'Genap', 'Tidak Aktif'),
(21, '2021', 'Ganjil', 'Tidak Aktif'),
(22, '2021', 'Genap', 'Tidak Aktif');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Mahasiswa','Tutor','Dosen Pengampu','Admin') NOT NULL,
  `first_login` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `first_login`) VALUES
(85, 'akunAdmin', '$2y$10$UxdFSeTIYqyXF90rzaXPNubfSPwdgKjvcz5ne/TMRt9hO8qt7BViK', 'Admin', 0);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `anggota_kelompok`
--
ALTER TABLE `anggota_kelompok`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kelompok` (`id_kelompok`),
  ADD KEY `npm_anggota` (`npm_anggota`);

--
-- Indeks untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_id_klmpk` (`id_klmpk`);

--
-- Indeks untuk tabel `kelompok_bisnis`
--
ALTER TABLE `kelompok_bisnis`
  ADD PRIMARY KEY (`id_kelompok`),
  ADD KEY `npm_ketua` (`npm_ketua`),
  ADD KEY `fk_id_mentor` (`id_mentor`);

--
-- Indeks untuk tabel `laporan_bisnis`
--
ALTER TABLE `laporan_bisnis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kelompok` (`id_kelompok`);

--
-- Indeks untuk tabel `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `npm` (`npm`),
  ADD UNIQUE KEY `unique_contact` (`contact`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_id_kelompok` (`id_kelompok`);

--
-- Indeks untuk tabel `materi_kewirausahaan`
--
ALTER TABLE `materi_kewirausahaan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `mentor`
--
ALTER TABLE `mentor`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nidn` (`nidn`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `proposal_bisnis`
--
ALTER TABLE `proposal_bisnis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kelompok_id` (`kelompok_id`);

--
-- Indeks untuk tabel `tahun_akademik`
--
ALTER TABLE `tahun_akademik`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `username_2` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `anggota_kelompok`
--
ALTER TABLE `anggota_kelompok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kelompok_bisnis`
--
ALTER TABLE `kelompok_bisnis`
  MODIFY `id_kelompok` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT untuk tabel `laporan_bisnis`
--
ALTER TABLE `laporan_bisnis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT untuk tabel `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT untuk tabel `materi_kewirausahaan`
--
ALTER TABLE `materi_kewirausahaan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT untuk tabel `mentor`
--
ALTER TABLE `mentor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `proposal_bisnis`
--
ALTER TABLE `proposal_bisnis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT untuk tabel `tahun_akademik`
--
ALTER TABLE `tahun_akademik`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `anggota_kelompok`
--
ALTER TABLE `anggota_kelompok`
  ADD CONSTRAINT `anggota_kelompok_ibfk_1` FOREIGN KEY (`id_kelompok`) REFERENCES `kelompok_bisnis` (`id_kelompok`) ON DELETE CASCADE,
  ADD CONSTRAINT `anggota_kelompok_ibfk_2` FOREIGN KEY (`npm_anggota`) REFERENCES `mahasiswa` (`npm`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  ADD CONSTRAINT `fk_id_klmpk` FOREIGN KEY (`id_klmpk`) REFERENCES `kelompok_bisnis` (`id_kelompok`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `kelompok_bisnis`
--
ALTER TABLE `kelompok_bisnis`
  ADD CONSTRAINT `fk_id_mentor` FOREIGN KEY (`id_mentor`) REFERENCES `mentor` (`id`),
  ADD CONSTRAINT `kelompok_bisnis_ibfk_1` FOREIGN KEY (`npm_ketua`) REFERENCES `mahasiswa` (`npm`);

--
-- Ketidakleluasaan untuk tabel `laporan_bisnis`
--
ALTER TABLE `laporan_bisnis`
  ADD CONSTRAINT `laporan_bisnis_ibfk_1` FOREIGN KEY (`id_kelompok`) REFERENCES `kelompok_bisnis` (`id_kelompok`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD CONSTRAINT `fk_id_kelompok` FOREIGN KEY (`id_kelompok`) REFERENCES `kelompok_bisnis` (`id_kelompok`),
  ADD CONSTRAINT `mahasiswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `mentor`
--
ALTER TABLE `mentor`
  ADD CONSTRAINT `mentor_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `proposal_bisnis`
--
ALTER TABLE `proposal_bisnis`
  ADD CONSTRAINT `proposal_bisnis_ibfk_1` FOREIGN KEY (`kelompok_id`) REFERENCES `kelompok_bisnis` (`id_kelompok`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
