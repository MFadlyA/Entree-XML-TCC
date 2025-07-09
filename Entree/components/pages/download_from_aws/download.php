<?php
session_start();

if (!isset($_SESSION['logged_in'])) {
    header('Location: /Entree/login');
    exit;
}

if (!isset($_GET['id']) || !isset($_GET['table'])) {
    die("Parameter tidak lengkap.");
}

$table = $_GET['table'];
$id = $_GET['id'];

// Daftar tabel dan kolom file-nya
$tableMap = [
    'materi_kewirausahaan' => 'file_path',
    'proposal_bisnis'      => 'proposal_pdf',
    'jadwal'               => 'bukti_kegiatan',
];

// Validasi nama tabel
if (!array_key_exists($table, $tableMap)) {
    die("Tabel tidak dikenali.");
}

// Path konfigurasi
$dbConfigPath  = $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';
$awsConfigPath = $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/aws_s3_config.php';

if (!file_exists($dbConfigPath) || !file_exists($awsConfigPath)) {
    die("Konfigurasi tidak ditemukan.");
}

require $dbConfigPath;
require $awsConfigPath;

// Cegah SQL Injection
$safeTable = $conn->real_escape_string($table);
$safeId    = $conn->real_escape_string($id);
$fileField = $tableMap[$table];

// Ambil file path dari database
$query = "SELECT `$fileField` AS file_url FROM `$safeTable` WHERE id = '$safeId'";
$result = $conn->query($query);

if (!$result || $result->num_rows === 0) {
    die("Data tidak ditemukan.");
}

$row = $result->fetch_assoc();
$fileUrl = $row['file_url'];

if (!$fileUrl) {
    die("File tidak tersedia.");
}

// Parse dan siapkan path S3
$parsedUrl = parse_url($fileUrl);
$fileKey = isset($parsedUrl['path']) ? ltrim($parsedUrl['path'], '/') : '';
$fileKey = urldecode($fileKey);

// Ambil nama file asli dan bersihkan angka atau timestamp di depannya
$fileName = basename($fileKey);

// Menghilangkan angka atau timestamp yang ada di depan nama file
$fileName = preg_replace('/^\d+_[^_]+_/', '', $fileName);

try {
    $s3Result = $s3->getObject([
        'Bucket' => 'entree-uploads',
        'Key'    => $fileKey
    ]);

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . $s3Result['ContentLength']);
    echo $s3Result['Body'];
    exit;

} catch (Aws\S3\Exception\S3Exception $e) {
    error_log("S3 Error: " . $e->getMessage());
    die("Gagal mengakses file dari S3: " . htmlspecialchars($e->getAwsErrorMessage()));
}
?>
