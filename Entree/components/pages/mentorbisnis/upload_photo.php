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
require $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/aws_s3_config.php';

if (isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileType = $file['type'];

    $allowedTypes = ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'];
    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Format file tidak valid.']);
        exit;
    }

    if ($fileError !== 0) {
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat mengunggah foto.']);
        exit;
    }

    // Persiapkan nama file baru
    $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
    $originalName = pathinfo($fileName, PATHINFO_FILENAME);
    $originalName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $originalName); // Hindari karakter aneh
    $uniquePrefix = uniqid(); // Kode unik
    $newFileName = $uniquePrefix . '_' . $originalName . '.' . $fileExt;

    $key = "foto_profil_mentor/" . $newFileName;

    try {
        $result = $s3->putObject([
            'Bucket' => $bucketName,
            'Key' => $key,
            'SourceFile' => $fileTmpName,
            'ContentType' => mime_content_type($fileTmpName),
            // 'ACL' => 'public-read',
            'ContentDisposition' => 'inline'
        ]);

        $photoUrl = $result['ObjectURL'];

        // Update di database (tabel mentor)
        $stmt = $conn->prepare("UPDATE mentor SET foto_profile = ? WHERE user_id = (SELECT id FROM users WHERE username = ?)");
        $stmt->bind_param("ss", $photoUrl, $_SESSION['username']);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'photo_url' => $photoUrl]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui database.']);
        }

    } catch (Aws\S3\Exception\S3Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Upload ke S3 gagal: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Tidak ada file yang diunggah.']);
}
?>
