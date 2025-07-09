<?php
session_start();
// Mengimpor koneksi database
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

// Mendapatkan ID proposal dari URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Menyiapkan query untuk menghapus proposal berdasarkan ID
    $sql = "DELETE FROM laporan_bisnis WHERE id = ?";

    // Menyiapkan statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameter
        $stmt->bind_param("i", $id);

        // Eksekusi query
        if ($stmt->execute()) {
            // Jika berhasil menghapus, redirect ke halaman daftar proposal
            header("Location: laporan_bisnis?message=Laporan Kemajuan berhasil dihapus");
            exit();
        } else {
            // Jika gagal menghapus, tampilkan pesan error
            echo "Terjadi kesalahan saat menghapus laporan.";
        }

        // Menutup statement
        $stmt->close();
    } else {
        echo "Terjadi kesalahan pada query database.";
    }
} else {
    // Jika ID tidak ditemukan, tampilkan pesan error
    echo "ID proposal tidak ditemukan.";
}

// Menutup koneksi database
$conn->close();
?>
