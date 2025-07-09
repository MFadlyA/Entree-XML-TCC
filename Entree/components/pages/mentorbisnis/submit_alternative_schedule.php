<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /Entree/login');
    exit;
}

// Cek apakah role pengguna sesuai
if ($_SESSION['role'] !== 'Tutor' && $_SESSION['role'] !== 'Dosen Pengampu') {
    header('Location: /Entree/login');
    exit;
}

include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';

// Periksa apakah form telah disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $id_kelompok = isset($_POST['alt_group']) ? htmlspecialchars($_POST['alt_group']) : '';
    $nama_kegiatan = isset($_POST['nama_kegiatan']) ? htmlspecialchars($_POST['nama_kegiatan']) : '';
    $tanggal = isset($_POST['alt_date']) ? htmlspecialchars($_POST['alt_date']) : '';
    $waktu = isset($_POST['alt_time']) ? htmlspecialchars($_POST['alt_time']) : '';
    $agenda = isset($_POST['agenda']) ? htmlspecialchars($_POST['agenda']) : '';
    $lokasi = isset($_POST['alt_location']) ? htmlspecialchars($_POST['alt_location']) : '';

    // Validasi data
    if (empty($id_kelompok) || empty($nama_kegiatan) || empty($tanggal) || empty($waktu) || empty($lokasi)) {
        die("Semua field harus diisi!");
    }

    // Nilai default untuk kolom feedback_mentor (karena tidak boleh NULL)
    $feedback_mentor = ''; // atau bisa juga "Belum ada feedback"
    $status = 'disetujui';

    // Query untuk memasukkan data jadwal ke dalam tabel jadwal
    $sql = "INSERT INTO jadwal (nama_kegiatan, tanggal, waktu, agenda, lokasi, feedback_mentor, status, id_klmpk) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    // Persiapan query
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Gagal mempersiapkan query: " . $conn->error);
    }

    // Bind parameter
    $stmt->bind_param("sssssssi", $nama_kegiatan, $tanggal, $waktu, $agenda, $lokasi, $feedback_mentor, $status, $id_kelompok);

    // Eksekusi query
    if ($stmt->execute()) {
        // Jika berhasil, arahkan kembali ke halaman jadwal
        header("Location: jadwal_bimbingan");
        exit();
    } else {
        // Jika gagal, tampilkan pesan error
        echo "Error: " . $stmt->error;
    }

    // Tutup statement dan koneksi
    $stmt->close();
    $conn->close();
} else {
    // Jika diakses tanpa form submission
    die("Akses tidak valid.");
}
