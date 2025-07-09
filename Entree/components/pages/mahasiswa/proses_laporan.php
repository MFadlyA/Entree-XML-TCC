<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';
require $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/aws_s3_config.php';

// Validasi login dan role
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'Mahasiswa') {
    header('Location: /Entree/login');
    exit;
}

if (!isset($_SESSION['npm'])) {
    header('Location: login.php');
    exit;
}

$npm_mahasiswa = $_SESSION['npm'];

// Ambil id_kelompok dari anggota_kelompok
$query_kelompok = "SELECT k.id_kelompok 
                   FROM kelompok_bisnis k
                   JOIN anggota_kelompok a ON k.id_kelompok = a.id_kelompok
                   WHERE k.npm_ketua = '$npm_mahasiswa' OR a.npm_anggota = '$npm_mahasiswa'";
$result_kelompok = mysqli_query($conn, $query_kelompok);

if ($result_kelompok && mysqli_num_rows($result_kelompok) > 0) {
    $kelompok = mysqli_fetch_assoc($result_kelompok);
    $id_kelompok = $kelompok['id_kelompok'];
    $_SESSION['id_kelompok'] = $id_kelompok;
} else {
    echo "Anda tidak terdaftar dalam kelompok!";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil inputan form
    $judul_laporan = mysqli_real_escape_string($conn, $_POST['judul_laporan']);
    $jenis_laporan = mysqli_real_escape_string($conn, $_POST['jenis_laporan']);
    $laporan_penjualan = isset($_POST['laporan_penjualan']) ? htmlspecialchars(str_replace(["\r", "\n"], ' ', $_POST['laporan_penjualan'])) : null;
    $laporan_pemasaran = isset($_POST['laporan_pemasaran']) ? htmlspecialchars(str_replace(["\r", "\n"], ' ', $_POST['laporan_pemasaran'])) : null;
    $laporan_produksi = isset($_POST['laporan_produksi']) ? htmlspecialchars(str_replace(["\r", "\n"], ' ', $_POST['laporan_produksi'])) : null;
    $laporan_sdm = isset($_POST['laporan_sdm']) ? htmlspecialchars(str_replace(["\r", "\n"], ' ', $_POST['laporan_sdm'])) : null;
    $laporan_keuangan = isset($_POST['laporan_keuangan']) ? htmlspecialchars(str_replace(["\r", "\n"], ' ', $_POST['laporan_keuangan'])) : null;
    $tanggal_upload = date('Y-m-d H:i:s');

    // Proses upload file ke S3
    $uploaded_files = [];
    if (isset($_FILES['lampiran_laporan']) && count($_FILES['lampiran_laporan']['name']) > 0) {
        foreach ($_FILES['lampiran_laporan']['name'] as $key => $file_name) {
            $file_tmp = $_FILES['lampiran_laporan']['tmp_name'][$key];
            $file_error = $_FILES['lampiran_laporan']['error'][$key];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($file_error === 0 && $file_ext === 'pdf') {
                // Generate key unik untuk S3
                $key = 'laporan_bisnis/' . time() . '_' . uniqid() . '_' . basename($file_name);

                try {
                    $result = $s3->putObject([
                        'Bucket' => $bucketName,
                        'Key' => $key,
                        'SourceFile' => $file_tmp,
                        'ContentType' => mime_content_type($file_tmp),
                        'ContentDisposition' => 'inline',
                    ]);

                    // Simpan URL publik
                    $uploaded_files[] = $result['ObjectURL'];

                } catch (Aws\S3\Exception\S3Exception $e) {
                    echo "Gagal upload ke S3: " . $e->getMessage();
                    exit;
                }
            }
        }
    }

    $lampiran_json = count($uploaded_files) > 0 ? json_encode($uploaded_files) : null;

    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO laporan_bisnis (
        judul_laporan, jenis_laporan, laporan_penjualan, laporan_pemasaran, laporan_produksi,
        laporan_sdm, laporan_keuangan, laporan_pdf, tanggal_upload, id_kelompok
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "sssssssssi",
        $judul_laporan,
        $jenis_laporan,
        $laporan_penjualan,
        $laporan_pemasaran,
        $laporan_produksi,
        $laporan_sdm,
        $laporan_keuangan,
        $lampiran_json,
        $tanggal_upload,
        $id_kelompok
    );

    if ($stmt->execute()) {
        header("Location: laporan_bisnis");
        exit;
    } else {
        echo "Gagal menyimpan ke database!";
    }
} else {
    header("Location: laporan_bisnis");
    exit;
}
?>
