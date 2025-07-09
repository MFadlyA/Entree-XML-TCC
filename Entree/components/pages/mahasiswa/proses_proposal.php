<?php
// Koneksi ke database
include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';
require $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/aws_s3_config.php';

// Pastikan mahasiswa sudah login dan memiliki session
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

if (!isset($_SESSION['npm'])) {
    header('Location: login.php');
    exit;
}

$npm_mahasiswa = $_SESSION['npm'];

// Ambil id_kelompok dari database
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
    echo "<script>alert('Anda tidak terdaftar dalam kelompok!');</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul_proposal = $_POST['judul_proposal'];
    $tahapan_bisnis = $_POST['tahapan_bisnis'];
    $sdg = implode(',', $_POST['sdg']);
    $kategori = $_POST['kategori'];
    $other_category = isset($_POST['other_category']) ? $_POST['other_category'] : null;
    $proposal_file = $_FILES['proposal'];
    $ide_bisnis = $_POST['ide_bisnis'];
    $anggaran = $_POST['anggaran'];

    // Validasi dan upload file ke S3
    if ($proposal_file['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $proposal_file['tmp_name'];
        $fileName = basename($proposal_file['name']);
        $key = "proposal_bisnis/" . time() . "_" . $fileName; // Buat path unik di bucket

        try {
            // Upload ke AWS S3
            $result = $s3->putObject([
                'Bucket' => $bucketName,
                'Key' => $key,
                'SourceFile' => $fileTmpPath,
                'ContentType' => mime_content_type($fileTmpPath),
                'ContentDisposition' => 'inline'
            ]);

            $fileUrl = $result['ObjectURL'];

            // Simpan data ke database
            $sql = "INSERT INTO proposal_bisnis (judul_proposal, tahapan_bisnis, sdg, kategori, other_category, proposal_pdf, kelompok_id, ide_bisnis, anggaran)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssd", $judul_proposal, $tahapan_bisnis, $sdg, $kategori, $other_category, $fileUrl, $id_kelompok, $ide_bisnis, $anggaran);

            if ($stmt->execute()) {
                $_SESSION['toast_message'] = [
                    'message' => 'Proposal berhasil diajukan!',
                    'isSuccess' => true
                ];
                header('Location: proposal');
                exit;
            } else {
                $_SESSION['toast_message'] = [
                    'message' => 'Terjadi kesalahan dalam mengajukan proposal!',
                    'isSuccess' => false
                ];
                header('Location: proposal');
                exit;
            }

            $stmt->close();
        } catch (Aws\S3\Exception\S3Exception $e) {
            echo "<script>alert('Upload ke S3 gagal: " . $e->getMessage() . "');</script>";
        }
    } else {
        echo "<script>alert('Silakan pilih file proposal PDF!');</script>";
    }
}
?>
