<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';
require $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/aws_s3_config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'Mahasiswa') {
    header('Location: /Entree/login');
    exit;
}

if (isset($_POST['kirim'])) {
    $id_laporan = $_POST['id_laporan'];
    $judul_laporan = $_POST['judul_laporan'];
    $jenis_laporan = $_POST['jenis_laporan'];
    $laporan_penjualan = $_POST['laporan_penjualan'];
    $laporan_pemasaran = $_POST['laporan_pemasaran'];
    $laporan_produksi = $_POST['laporan_produksi'];
    $laporan_sdm = $_POST['laporan_sdm'];
    $laporan_keuangan = $_POST['laporan_keuangan'];

    // Proses upload lampiran ke AWS S3
    $lampiran_laporan = null;
    if (isset($_FILES['lampiran_laporan']) && $_FILES['lampiran_laporan']['error'][0] == 0) {
        $lampiran_laporan = [];
        foreach ($_FILES['lampiran_laporan']['name'] as $key => $file_name) {
            $file_tmp = $_FILES['lampiran_laporan']['tmp_name'][$key];
            $file_error = $_FILES['lampiran_laporan']['error'][$key];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($file_error === 0 && $file_ext === 'pdf') {
                $key_s3 = 'laporan_bisnis/' . time() . '_' . uniqid() . '_' . basename($file_name);

                try {
                    $result = $s3->putObject([
                        'Bucket' => $bucketName,
                        'Key' => $key_s3,
                        'SourceFile' => $file_tmp,
                        'ContentType' => mime_content_type($file_tmp),
                        'ContentDisposition' => 'inline'
                    ]);

                    $lampiran_laporan[] = $result['ObjectURL'];
                } catch (Aws\S3\Exception\S3Exception $e) {
                    echo "Gagal upload ke S3: " . $e->getMessage();
                    exit;
                }
            }
        }

        // Encode sebagai JSON
        $lampiran_laporan = json_encode($lampiran_laporan);
    }

    // Query update
    $sql = "UPDATE laporan_bisnis SET 
                judul_laporan = ?, 
                jenis_laporan = ?, 
                laporan_penjualan = ?, 
                laporan_pemasaran = ?, 
                laporan_produksi = ?, 
                laporan_sdm = ?, 
                laporan_keuangan = ?";

    if ($lampiran_laporan) {
        $sql .= ", laporan_pdf = ?";
    }

    $sql .= " WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        if ($lampiran_laporan) {
            $stmt->bind_param(
                'ssssssssi',
                $judul_laporan,
                $jenis_laporan,
                $laporan_penjualan,
                $laporan_pemasaran,
                $laporan_produksi,
                $laporan_sdm,
                $laporan_keuangan,
                $lampiran_laporan,
                $id_laporan
            );
        } else {
            $stmt->bind_param(
                'sssssssi',
                $judul_laporan,
                $jenis_laporan,
                $laporan_penjualan,
                $laporan_pemasaran,
                $laporan_produksi,
                $laporan_sdm,
                $laporan_keuangan,
                $id_laporan
            );
        }

        if ($stmt->execute()) {
            header("Location: laporan_bisnis");
            exit;
        } else {
            echo "Terjadi kesalahan saat update: " . $stmt->error;
        }

        $stmt->close();
    }

    $conn->close();
}
?>
