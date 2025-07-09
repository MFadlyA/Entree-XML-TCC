<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /Entree/login');
    exit;
}

// Cek apakah role pengguna sesuai
if ($_SESSION['role'] !== 'Mahasiswa') {
    header('Location: /Entree/login');
    exit;
}
// submit_action.php
include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';

// Mengatur header agar respons dikirim dalam format JSON
header('Content-Type: application/json');

// Pastikan request menggunakan POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mendapatkan nilai dari POST request
    $action = $_POST['action'];
    $jadwal_id = $_POST['jadwal_id'];

    // Validasi input untuk mencegah SQL Injection
    $action = htmlspecialchars($action);
    $jadwal_id = (int)$jadwal_id;

    // Variabel untuk pesan dan status
    $message = '';
    $status = '';

    // Tentukan query berdasarkan aksi
    if ($action === 'setujui') {
        // Query untuk memperbarui status menjadi 'disetujui'
        $sql = "UPDATE jadwal SET status = 'disetujui' WHERE id = ?";
    } elseif ($action === 'tolak') {
        // Query untuk memperbarui status menjadi 'ditolak'
        $sql = "UPDATE jadwal SET status = 'ditolak' WHERE id = ?";
    } else {
        $message = "Aksi tidak valid.";
        $status = "error";
    }

    // Jika action valid
    if ($message === '') {
        // Siapkan dan eksekusi query
        $stmt = $conn->prepare($sql); // Menyiapkan statement
        if ($stmt) {
            $stmt->bind_param('i', $jadwal_id); // Mengikat parameter (integer)

            if ($stmt->execute()) {
                $status = "success";
                $message = "Status jadwal berhasil diperbarui.";
            } else {
                $status = "error";
                $message = "Terjadi kesalahan saat memperbarui status: " . $stmt->error;
            }

            // Tutup statement
            $stmt->close();
        } else {
            // Jika query tidak dapat dipersiapkan
            $status = "error";
            $message = "Terjadi kesalahan saat menyiapkan query: " . $conn->error;
        }
    }

    // Tutup koneksi
    $conn->close();

    // Kirimkan respons JSON
    echo json_encode(['status' => $status, 'message' => $message]);
} else {
    // Jika metode bukan POST
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak valid.']);
}
?>