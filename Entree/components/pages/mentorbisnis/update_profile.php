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

// Pastikan user sudah login
if (!isset($_SESSION['username'])) {
    echo json_encode(['message' => 'User tidak terautentikasi.']);
    exit;
}

// Ambil data dari request
$data = json_decode(file_get_contents("php://input"), true);

$phone = $data['phone'];
$skill = $data['skill'];

// Periksa apakah data valid
if (empty($phone) || empty($skill)) {
    echo json_encode(['message' => 'Nomor telepon dan keahlian harus diisi.']);
    exit;
}

// Update data mentor di database
$query = "
    UPDATE mentor 
    SET contact = ?, keahlian = ? 
    WHERE user_id = (
        SELECT id FROM users WHERE username = ? 
    )";
$stmt = $conn->prepare($query);
$stmt->bind_param("sss", $phone, $skill, $_SESSION['username']);

if ($stmt->execute()) {
    echo json_encode(['message' => 'Profil berhasil diperbarui.']);
} else {
    echo json_encode(['message' => 'Gagal memperbarui profil.']);
}

$stmt->close();
$conn->close();
?>
