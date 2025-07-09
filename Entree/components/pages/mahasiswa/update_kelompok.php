<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /Entree/login');
    exit;
}

if ($_SESSION['role'] !== 'Mahasiswa') {
    header('Location: /Entree/login');
    exit;
}

include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';
require $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/aws_s3_config.php'; // pastikan ini inisialisasi $s3 dan $bucketName

$data = $_POST;
$id_kelompok = $data['id_kelompok'];
$nama_kelompok = $data['nama_kelompok'];
$nama_bisnis = $data['nama_bisnis'];
$logo_bisnis = null;

// Cek apakah file logo diunggah
if (isset($_FILES['logo_bisnis']) && $_FILES['logo_bisnis']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['logo_bisnis']['tmp_name'];
    $fileName = time() . '_' . basename($_FILES['logo_bisnis']['name']);
    $mimeType = mime_content_type($fileTmpPath);

    // Validasi mime type logo
    $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
    if (!in_array($mimeType, $allowedTypes)) {
        echo json_encode(['status' => 'error', 'message' => 'Logo hanya boleh berupa PNG atau JPEG.']);
        exit;
    }

    $key = "logo_kelompok_bisnis/" . $fileName;

    try {
        $result = $s3->putObject([
            'Bucket' => $bucketName,
            'Key' => $key,
            'SourceFile' => $fileTmpPath,
            'ContentType' => $mimeType,
            'ContentDisposition' => 'inline',
        ]);

        $logo_bisnis = $result['ObjectURL']; // Simpan URL logo
    } catch (Aws\S3\Exception\S3Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal upload ke S3: ' . $e->getMessage()]);
        exit;
    }
}

// Update database
$sql = "UPDATE kelompok_bisnis 
        SET nama_kelompok = ?, nama_bisnis = ?, logo_bisnis = IFNULL(?, logo_bisnis) 
        WHERE id_kelompok = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sssi', $nama_kelompok, $nama_bisnis, $logo_bisnis, $id_kelompok);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui data kelompok bisnis.']);
}
?>
