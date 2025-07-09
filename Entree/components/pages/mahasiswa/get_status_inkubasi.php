<?php
session_start();
// Ambil status inkubasi dari database untuk kelompok tertentu
include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /Entree/login');
    exit;
}

// Cek apakah role pengguna sesuai
if ($_SESSION['role'] !== 'Mahasiswa') {
    header('Location: /Entree/login');
    exit;
}

$id_kelompok = $_GET['kelompok_id']; // Ambil ID kelompok dari parameter URL

// Query untuk mengambil status inkubasi
$query = "SELECT status_inkubasi FROM kelompok_bisnis WHERE id_kelompok = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_kelompok);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(['status' => $row['status_inkubasi']]);
} else {
    echo json_encode(['status' => null]);
}
?>
