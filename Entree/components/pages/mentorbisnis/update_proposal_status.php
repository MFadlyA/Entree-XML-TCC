<?php
// update_proposal_status.php
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

// Cek apakah data POST valid
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['action'], $data['proposal_id'], $data['group_id'])) {
    $action = $data['action'];
    $proposal_id = $data['proposal_id'];
    $group_id = $data['group_id'];

    // Menentukan status baru berdasarkan aksi
    if ($action === 'disetujui' || $action === 'ditolak') {
        // Jika proposal disetujui, pastikan proposal kelompok lain ditolak
        if ($action === 'disetujui') {
            // Update proposal dengan group_id lain menjadi 'ditolak'
            $update_other_proposals_query = "UPDATE proposal_bisnis SET status = 'ditolak' WHERE id != ? AND kelompok_id = ?";
            $stmt = $conn->prepare($update_other_proposals_query);
            $stmt->bind_param("ii", $proposal_id, $group_id);
            $stmt->execute();

            // Mengambil nilai sdg dan kategori dari proposal_bisnis berdasarkan proposal_id
            $get_proposal_details_query = "SELECT sdg, kategori, ide_bisnis FROM proposal_bisnis WHERE id = ?";
            $stmt_details = $conn->prepare($get_proposal_details_query);
            $stmt_details->bind_param("i", $proposal_id);
            $stmt_details->execute();
            $stmt_details->bind_result($sdg, $kategori, $ide_bisnis);
            $stmt_details->fetch();
            $stmt_details->close();

            // Update kategori dan status di tabel kelompok_bisnis
            $update_kelompok_query = "UPDATE kelompok_bisnis SET sdg = ?, kategori_bisnis = ?, ide_bisnis = ? WHERE id_kelompok = ?";
            $stmt_kelompok = $conn->prepare($update_kelompok_query);
            $stmt_kelompok->bind_param("sssi", $sdg, $kategori, $ide_bisnis, $group_id);
            $stmt_kelompok->execute();
        }

        // Update status proposal yang sedang diproses
        $update_query = "UPDATE proposal_bisnis SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $action, $proposal_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Status proposal berhasil diperbarui.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status proposal.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
}
?>