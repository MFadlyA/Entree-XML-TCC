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

// Ambil data yang dikirimkan melalui POST
$id_kelompok = $_POST['id_kelompok'];
$status_inkubasi = $_POST['status_inkubasi'];

// Query untuk update status inkubasi
$sql = "UPDATE kelompok_bisnis SET status_inkubasi = ? WHERE id_kelompok = ?";

// Persiapkan statement
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status_inkubasi, $id_kelompok);

// Eksekusi query dan cek hasil
if ($stmt->execute()) {
    // Kirimkan respons sukses
    echo json_encode(["success" => true]);
} else {
    // Kirimkan respons gagal
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

// Tutup koneksi
$stmt->close();
$conn->close();
?>