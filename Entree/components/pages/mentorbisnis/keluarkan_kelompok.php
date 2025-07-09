<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';

// Pastikan ID kelompok diterima dengan aman
if (isset($_POST['kelompok_id'])) {
    $kelompokId = $_POST['kelompok_id'];

    // Pastikan ID kelompok valid
    if (!empty($kelompokId)) {
        // Update status inkubasi menjadi NULL
        $sql = "UPDATE kelompok_bisnis SET status_inkubasi = NULL WHERE id_kelompok = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $kelompokId); // Bind parameter untuk ID kelompok
        if ($stmt->execute()) {
            echo "Status inkubasi berhasil diubah menjadi NULL";
        } else {
            echo "Terjadi kesalahan saat mengupdate status.";
        }
    } else {
        echo "ID kelompok tidak valid.";
    }
} else {
    echo "ID kelompok tidak ditemukan.";
}
?>
