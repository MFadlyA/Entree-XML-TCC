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

if (!isset($_GET['id'])) {
    echo "ID materi tidak ditemukan.";
    exit;
}

$id = $conn->real_escape_string($_GET['id']);

$baseDir = '/Entree/components/pages/admin/uploads/';

$sql = "SELECT * FROM materi_kewirausahaan WHERE id = '$id'";
$result = $conn->query($sql);

if ($result === false || $result->num_rows === 0) {
    header("Location: materikewirausahaan_admin.php?status=error");
    exit;
}

$row = $result->fetch_assoc();
$filePath = $baseDir . htmlspecialchars($row["file_path"]);

// Hapus file dari server
if (file_exists($filePath)) {
    unlink($filePath);
}

// Hapus data dari database
$deleteSql = "DELETE FROM materi_kewirausahaan WHERE id = '$id'";
if ($conn->query($deleteSql) === TRUE) {
    header("Location: materi_kewirausahaan?status=success");
} else {
    header("Location: materi_kewirausahaan?status=error");
}

$conn->close();
exit;
?>