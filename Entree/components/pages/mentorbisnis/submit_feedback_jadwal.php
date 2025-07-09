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

// Ambil data dari session dan form
$feedback = isset($_POST['feedback']) ? $_POST['feedback'] : null;
$jadwalId = isset($_POST['jadwal_id']) ? $_POST['jadwal_id'] : null;

// Cek apakah feedback dan jadwal ID valid
if (!$feedback || !$jadwalId) {
    echo "Umpan Balik atau Jadwal ID tidak valid!";
    exit;
}

// Query untuk menyimpan feedback ke database
$sql = "UPDATE jadwal SET feedback_mentor = ? WHERE id = ?";

// Persiapkan query
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $feedback, $jadwalId);

// Eksekusi query
if ($stmt->execute()) {
    // Jika berhasil, tampilkan toast sukses
    header("Location: detail_jadwal?id=$jadwalId&toast=Umpan Balik berhasil dikirim");
    exit;
} else {
    // Jika gagal, tampilkan pesan error
    echo "Terjadi kesalahan saat menyimpan Umpan Balik: " . $stmt->error;
    exit;
}
?>
