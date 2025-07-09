<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /Entree/login');
    exit;
}

// Cek apakah role pengguna sesuai
if ($_SESSION['role'] !== 'Admin') {
    header('Location: /Entree/login');
    exit;
}
include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';

// Ambil parameter
$tahun = $_GET['tahun'];
$jenis = $_GET['jenis'];

// Hapus data berdasarkan parameter
$sql = "DELETE FROM tahun_akademik WHERE tahun = '$tahun' AND jenis_tahun = '$jenis'";
if ($conn->query($sql) === TRUE) {
    echo "<script>alert('Tahun akademik berhasil dihapus!'); window.location.href='tahun_akademik';</script>";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
