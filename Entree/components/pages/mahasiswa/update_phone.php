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
include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'User belum login.']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $data = json_decode(file_get_contents("php://input"), true);
    $new_phone = $data['phone'] ?? '';
    $new_address = $data['alamat'] ?? '';

    // Validasi format nomor telepon
    if ($new_phone && !preg_match('/^\d{10,15}$/', $new_phone)) {
        echo json_encode(['status' => 'error', 'message' => 'Nomor telepon tidak valid.']);
        exit;
    }

    // Ambil data lama (nomor telepon dan alamat) dari database
    $query_check = "SELECT contact, alamat FROM mahasiswa WHERE user_id = ?";
    $stmt_check = $conn->prepare($query_check);

    if (!$stmt_check) {
        echo json_encode(['status' => 'error', 'message' => 'Error in query preparation.']);
        exit;
    }

    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row = $result_check->fetch_assoc();

    if (!$row) {
        echo json_encode(['status' => 'error', 'message' => 'Data mahasiswa tidak ditemukan.']);
        exit;
    }

    $current_phone = $row['contact'];
    $current_address = $row['alamat'];

    // Periksa apakah data baru sama dengan data lama
    if ($new_phone === $current_phone && $new_address === $current_address) {
        echo json_encode(['status' => 'error', 'message' => 'Tolong masukkan data yang ingin diperbarui.']);
        exit;
    }

    // Siapkan query update
    $update_fields = [];
    $update_params = [];
    $param_types = '';

    if ($new_phone && $new_phone !== $current_phone) {
        $update_fields[] = "contact = ?";
        $update_params[] = $new_phone;
        $param_types .= 's';
    }

    if ($new_address && $new_address !== $current_address) {
        $update_fields[] = "alamat = ?";
        $update_params[] = $new_address;
        $param_types .= 's';
    }

    $update_params[] = $user_id;
    $param_types .= 'i';

    $query_update = "UPDATE mahasiswa SET " . implode(", ", $update_fields) . " WHERE user_id = ?";
    $stmt_update = $conn->prepare($query_update);

    if (!$stmt_update) {
        echo json_encode(['status' => 'error', 'message' => 'Error in query preparation.']);
        exit;
    }

    $stmt_update->bind_param($param_types, ...$update_params);

    if ($stmt_update->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Profil berhasil diperbarui.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan saat memperbarui profil.']);
    }

    $stmt_check->close();
    $stmt_update->close();
    $conn->close();
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metode HTTP tidak valid.']);
    exit;
}
?>
