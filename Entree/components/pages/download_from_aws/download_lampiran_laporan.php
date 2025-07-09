<?php
require $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/aws_s3_config.php'; // koneksi S3

if (!isset($_GET['file'])) {
    die("URL file tidak ditemukan.");
}

$fileUrl = $_GET['file'];

// Validasi URL S3
if (strpos($fileUrl, 'https://entree-uploads.s3.') !== 0) {
    die("URL tidak valid.");
}

// Ambil key dari URL
$parsedUrl = parse_url($fileUrl);
$fileKey = isset($parsedUrl['path']) ? ltrim($parsedUrl['path'], '/') : '';
$fileKey = urldecode($fileKey);

// Ambil nama file asli tanpa angka atau timestamp di depannya
$fileName = basename($fileKey);

// Menghilangkan angka atau timestamp yang ada di depan nama file
$fileName = preg_replace('/^\d+_[^_]+_/', '', $fileName);

try {
    $s3Result = $s3->getObject([
        'Bucket' => 'entree-uploads',
        'Key'    => $fileKey
    ]);

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . $s3Result['ContentLength']);
    echo $s3Result['Body'];
    exit;
} catch (Aws\S3\Exception\S3Exception $e) {
    error_log("S3 Error: " . $e->getMessage());
    die("Gagal mengakses file: " . htmlspecialchars($e->getAwsErrorMessage()));
}
?>
