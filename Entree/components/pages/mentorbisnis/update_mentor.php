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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_kelompok'], $_POST['id_mentor'])) {
    $id_kelompok = $_POST['id_kelompok'];
    $id_mentor = $_POST['id_mentor'];

    $updateSql = "UPDATE kelompok_bisnis SET id_mentor = ? WHERE id_kelompok = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param('si', $nama_mentor, $id_kelompok);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Mentor berhasil ditambahkan ke kelompok bisnis.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan mentor: ' . $conn->error]);
    }
}

$conn->close();
?>
