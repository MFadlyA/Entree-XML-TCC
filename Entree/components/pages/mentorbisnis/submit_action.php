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

// Ambil data dari request
$action = isset($_POST['action']) ? $_POST['action'] : null;
$jadwalId = isset($_POST['jadwal_id']) ? $_POST['jadwal_id'] : null;

if (!$action || !$jadwalId) {
    echo "Aksi atau ID jadwal tidak valid!";
    exit;
}

try {
    if ($action === "setujui") {
        // Update status jadwal menjadi 'disetujui'
        $sql = "UPDATE jadwal SET status = 'disetujui' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $jadwalId);
        $stmt->execute();

        // Redirect dengan pesan toast
        header("Location: detail_jadwal?id=$jadwalId&toast=Jadwal berhasil disetujui");
    } elseif ($action === "tolak") {
        // Update status jadwal menjadi 'ditolak'
        $sql = "UPDATE jadwal SET status = 'ditolak' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $jadwalId);
        $stmt->execute();

        // Redirect dengan pesan toast
        header("Location: detail_jadwal?id=$jadwalId&toast=Jadwal berhasil ditolak");
    } elseif ($action === "selesai") {
        // Update status jadwal menjadi 'selesai'
        $sql = "UPDATE jadwal SET status = 'selesai' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $jadwalId);
        $stmt->execute();

        // Redirect dengan pesan toast
        header("Location: detail_jadwal?id=$jadwalId&toast=Jadwal berhasil diselesaikan");
    } elseif ($action === "jadwal_alternatif") {
        // Tambah jadwal alternatif ke tabel jadwal
        $altDate = isset($_POST['alt_date']) ? $_POST['alt_date'] : null;
        $altTime = isset($_POST['alt_time']) ? $_POST['alt_time'] : null;
        $altLocation = isset($_POST['alt_location']) ? $_POST['alt_location'] : null;

        if (!$altDate || !$altTime || !$altLocation) {
            echo "Data jadwal alternatif tidak lengkap!";
            exit;
        }

        // Insert data jadwal baru
        $sql = "UPDATE jadwal 
        SET tanggal = ?, waktu = ?, lokasi = ?, status = 'alternatif'
        WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $altDate, $altTime, $altLocation, $jadwalId);
        $stmt->execute();


        // Redirect dengan pesan toast
        header("Location: detail_jadwal?id=$jadwalId&toast=Jadwal alternatif berhasil ditambahkan");
    } else {
        header("Location: detail_jadwal?id=$jadwalId&toast=Aksi tidak valid");
    }
    exit;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>