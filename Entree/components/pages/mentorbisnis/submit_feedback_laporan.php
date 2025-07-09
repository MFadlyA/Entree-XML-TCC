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

// Mendekode data JSON dari request body
$data = json_decode(file_get_contents('php://input'), true);

// Validasi data JSON
if (isset($data['feedback'], $data['laporan_id'])) {
    $feedback = trim($data['feedback']);
    $laporan_id = intval($data['laporan_id']); // Pastikan ini adalah integer untuk keamanan

    // Perbarui kolom feedback di tabel laporan_bisnis
    $update_feedback_query = "UPDATE laporan_bisnis SET feedback = ? WHERE id = ?";
    $stmt = $conn->prepare($update_feedback_query);

    if ($stmt) {
        $stmt->bind_param("si", $feedback, $laporan_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Umpan Balik berhasil dikirim.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal mengirim Umpan Balik.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan pada sistem.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap. Umpan Balik tidak dapat dikirim.']);
}

$conn->close();
?>