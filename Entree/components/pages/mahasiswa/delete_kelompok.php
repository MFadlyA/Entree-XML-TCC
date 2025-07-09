<?php
session_start();
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

// Ambil data JSON yang dikirim
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id_kelompok']) && is_numeric($data['id_kelompok'])) {
    $id_kelompok = (int)$data['id_kelompok']; // Pastikan id_kelompok adalah integer

    // Mengaktifkan transaksi
    $conn->autocommit(false);  // Matikan auto-commit

    try {
        // Mengupdate id_kelompok menjadi NULL di tabel mahasiswa
        $stmt = $conn->prepare("UPDATE mahasiswa SET id_kelompok = NULL WHERE id_kelompok = ?");
        if ($stmt === false) {
            throw new Exception('Error preparing mahasiswa update query: ' . $conn->error);
        }
        $stmt->bind_param("i", $id_kelompok);
        $stmt->execute();

        // Hapus data di proposal_bisnis
        $stmt = $conn->prepare("DELETE FROM proposal_bisnis WHERE kelompok_id = ?");
        if ($stmt === false) {
            throw new Exception('Error preparing proposal_bisnis delete query: ' . $conn->error);
        }
        $stmt->bind_param("i", $id_kelompok);
        $stmt->execute();

        // Hapus data di laporan_bisnis
        $stmt = $conn->prepare("DELETE FROM laporan_bisnis WHERE id_kelompok = ?");
        if ($stmt === false) {
            throw new Exception('Error preparing laporan_bisnis delete query: ' . $conn->error);
        }
        $stmt->bind_param("i", $id_kelompok);
        $stmt->execute();

        // Hapus data di jadwal
        $stmt = $conn->prepare("DELETE FROM jadwal WHERE id_klmpk = ?");
        if ($stmt === false) {
            throw new Exception('Error preparing jadwal delete query: ' . $conn->error);
        }
        $stmt->bind_param("i", $id_kelompok);
        $stmt->execute();

        // Hapus data di anggota_kelompok
        $stmt = $conn->prepare("DELETE FROM anggota_kelompok WHERE id_kelompok = ?");
        if ($stmt === false) {
            throw new Exception('Error preparing anggota_kelompok delete query: ' . $conn->error);
        }
        $stmt->bind_param("i", $id_kelompok);
        $stmt->execute();

        // Hapus data di kelompok_bisnis
        $stmt = $conn->prepare("DELETE FROM kelompok_bisnis WHERE id_kelompok = ?");
        if ($stmt === false) {
            throw new Exception('Error preparing kelompok_bisnis delete query: ' . $conn->error);
        }
        $stmt->bind_param("i", $id_kelompok);
        $stmt->execute();

        // Update status_kelompok_bisnis di kelompok_bisnis_backup menjadi nonaktif
        $stmt = $conn->prepare("UPDATE kelompok_bisnis_backup SET status_kelompok_bisnis = 'tidak aktif' WHERE id_kelompok = ?");
        if ($stmt === false) {
            throw new Exception('Error preparing kelompok_bisnis_backup update query: ' . $conn->error);
        }
        $stmt->bind_param("i", $id_kelompok);
        $stmt->execute();

        // Commit transaksi
        $conn->commit();

        // Kirim respon sukses
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        // Rollback transaksi jika ada error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } finally {
        // Aktifkan kembali auto-commit
        $conn->autocommit(true);
    }
} else {
    // Mengirimkan respon jika id_kelompok tidak valid
    echo json_encode(['success' => false, 'message' => 'ID Kelompok tidak ditemukan atau tidak valid.']);
}
?>
