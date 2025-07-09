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
    // Ambil data dari form
    $id = $_POST['id'];
    $judul = $conn->real_escape_string($_POST['judul']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);

    // SQL untuk mengupdate materi
    $sql = "UPDATE materi_kewirausahaan SET judul = '$judul', deskripsi = '$deskripsi' WHERE id = '$id'";

    if ($conn->query($sql) === TRUE) {
        // Pada file edit_materi.php setelah update berhasil
        header("Location: detail_materi?id=" . $id . "&status=success");
        exit();
    } else {
        // Pada file edit_materi.php setelah gagal update
        header("Location: detail_materi?id=" . $id . "&status=error");
        exit();
    }
}

$conn->close();
?>
