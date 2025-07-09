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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_kelompok = $_POST['id_kelompok'];
    $id_mentor = $_POST['id_mentor'];

    // Validasi input
    if (empty($id_kelompok) || empty($id_mentor)) {
        echo "Gagal: Data kelompok atau mentor tidak valid.";
        exit;
    }

    // Update id_mentor pada kelompok_bisnis
    $sql = "UPDATE kelompok_bisnis SET id_mentor = ? WHERE id_kelompok = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $id_mentor, $id_kelompok);

    if ($stmt->execute()) {
        header("Location: daftar_kelompok_bisnis?success=1"); // Redirect dengan parameter success
        exit;
    } else {
        echo "Gagal: " . $conn->error;
    }
}
?>
