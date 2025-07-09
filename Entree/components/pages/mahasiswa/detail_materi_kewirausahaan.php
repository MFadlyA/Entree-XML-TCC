<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';
require $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/aws_s3_config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /Entree/login');
    exit;
}

// Cek apakah role pengguna sesuai
if ($_SESSION['role'] !== 'Mahasiswa') {
    header('Location: /Entree/login');
    exit;
}

if (!isset($_GET['id'])) {
    echo "ID materi tidak ditemukan.";
    exit;
}

$id = $conn->real_escape_string($_GET['id']);

$baseDir = '/Entree/components/pages/admin/uploads/';

$sql = "SELECT * FROM materi_kewirausahaan WHERE id = '$id'";
$result = $conn->query($sql);

if ($result === false || $result->num_rows === 0) {
    echo "Materi tidak ditemukan.";
    exit;
}

$row = $result->fetch_assoc();
// Asumsikan field file_path di database menyimpan nama file saja (misal: "file.pdf")
$fileName = htmlspecialchars($row["file_path"]);
$fileUrl = $fileName; // Gabungkan dengan base URL S3
$fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

// Fungsi hanya dideklarasikan sekali
if (!function_exists('generateFilePreview')) {
    function generateFilePreview($filePath, $fileExtension, $height = 500) {
        $fileExtension = strtolower($fileExtension);
        
        // Untuk file di S3, kita gunakan URL langsung
        $fileUrl = $filePath; // Karena sekarang $filePath sudah berisi URL S3

        if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
            return '<img src="' . $fileUrl . '" alt="Pratinjau Gambar" class="img-fluid rounded shadow" style="max-height:' . $height . 'px;">';
        } elseif ($fileExtension === 'pdf') {
            return '<iframe src="' . $fileUrl . '" width="100%" height="' . $height . 'px"></iframe>';
        } elseif (in_array($fileExtension, ['mp4', 'webm', 'mov', 'avi'])) {
            return '
            <video width="100%" height="' . $height . 'px" controls>
                <source src="' . $fileUrl . '" type="video/' . $fileExtension . '">
                Browser Anda tidak mendukung pemutar video.
            </video>';
        } elseif (in_array($fileExtension, ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'])) {
            $googleDocsUrl = 'https://docs.google.com/viewer?url=' . urlencode($fileUrl) . '&embedded=true';
            return '<iframe src="' . $googleDocsUrl . '" width="100%" height="' . $height . 'px"></iframe>
            <div class="alert alert-warning">Jika Pratinjau Tidak Tersedia. Silakan gunakan tombol di bawah untuk melihat atau mengunduh file.</div>';
        } else {
            return '<div class="alert alert-danger">Pratinjau untuk jenis file ini tidak tersedia. Silakan gunakan tombol di bawah untuk melihat atau mengunduh file.</div>';
        }
    }
}
?>
<?php
function getFileIcon($fileExtension) {
    // Standarisasi ekstensi menjadi huruf kecil
    $fileExtension = strtolower($fileExtension);

    // Tentukan icon berdasarkan ekstensi
    switch ($fileExtension) {
        case 'mp4':
        case 'webm':
        case 'mov':
        case 'avi':
            return '/Entree/assets/img/icon_video.png'; // Icon video
        case 'ppt':
        case 'pptx':
            return '/Entree/assets/img/icon_ppt.png'; // Icon PPT
        case 'pdf':
            return '/Entree/assets/img/icon_pdf.png'; // Icon PDF
        case 'doc':
        case 'docx':
            return '/Entree/assets/img/icon_word.png'; // Icon Word
        case 'xls':
        case 'xlsx':
            return '/Entree/assets/img/icon_excel.png'; // Icon Excel
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            return '/Entree/assets/img/icon_image.png'; // Icon gambar
        default:
            return '/Entree/assets/img/icon_default.png'; // Icon default
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Materi Kewirausahaan | Entree</title>
    <link rel="icon" href="\Entree\assets\img\icon_favicon.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/77a99d5f4f.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="/Entree/assets/css/mahasiswa/sidebar_mahasiswa.css">
    <link rel="stylesheet" href="/Entree/assets/css/materikewirausahaan.css">
    <link rel="stylesheet" href="/Entree/assets/css/detail_materikewirausahaan.css">
</head>

<body>
    <div class="wrapper">
        <?php
        $activePage = 'materikewirausahaan_mahasiswa';
        include 'sidebar_mahasiswa.php';
        ?>

        <div class="main p-3">
            <div class="main_header">
                <?php
                $pageTitle = "Materi Kewirausahaan";
                include 'header_mahasiswa.php';
                ?>
            </div>

            <div class="main_wrapper">
                            <div class="file-preview">
                                <?= generateFilePreview($fileUrl, $fileExtension) ?>
                            </div>
                <div class="horizontal-layout">
                    <div class="layout-left">
                            <h1><?= htmlspecialchars($row["judul"]) ?></h1>
                            <p><?= htmlspecialchars($row["deskripsi"]) ?></p>
                            <div class="mt-3">
                                <div class="btn_container mb-3">
                                    <a href="<?= $fileUrl ?>" target="_blank" class="file icon" data-bs-toggle="tooltip" title="Lihat Materi">
                                        <i class="fa-solid fa-eye btn-icon"></i>
                                    </a>
                                    <a href="\Entree\components\pages\download_from_aws\download.php?table=materi_kewirausahaan&id=<?= $row['id'] ?>" target="_blank" download class="file icon" data-bs-toggle="tooltip" title="Unduh Materi">
                                        <i class="fa-solid fa-download btn-icon"></i>
                                    </a>
                                </div>
                            </div>
                    </div>
                    <div class="layout-right">
                    <div class="card-container">
                            <?php   
                            // Pastikan ada ID materi yang sedang ditampilkan
                            $currentId = isset($_GET['id']) ? (int)$_GET['id'] : null;

                            // Ambil 10 file acak, kecuali file yang sedang ditampilkan
                            $sql = "SELECT * FROM materi_kewirausahaan WHERE id != $currentId ORDER BY RAND() LIMIT 10";
                            $result = $conn->query($sql);

                            if ($result === false) {
                                echo "<p>Error pada query: " . $conn->error . "</p>";
                            } elseif ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $filePath = htmlspecialchars($row["file_path"]);
                                    $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
                                    $iconSrc = getFileIcon($fileExtension);

                                    echo '
                                    <a href="detail_materi?id=' . $row["id"] . '">
                                        <div class="card" onclick="showDetailModal(\'' . $row["id"] . '\', \'' . htmlspecialchars($row["judul"]) . '\', \'' . htmlspecialchars($row["deskripsi"]) . '\', \'' . $filePath . '\')">
                                            <div class="icon-container"> 
                                                <img src="' . $iconSrc . '" alt="File Icon" class="icon">
                                            </div>
                                            <div class="card-body" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Lihat Materi">
                                                <h5 class="card-title">' . htmlspecialchars($row["judul"]) . '</h5>
                                                <p class="card-text">' . htmlspecialchars($row["deskripsi"]) . '</p>
                                            </div>
                                        </div>
                                    </a>';
                                }
                            } else {
                                echo '
                                <div class="d-flex justify-content-center align-items-center" style="height: 60vh; width: 100%;">
                                    <div class="alert alert-warning text-center" role="alert">
                                        <p>Belum Ada Materi Kewirausahaan.</p>
                                    </div>
                                </div>
                                ';    }

                            $conn->close();
                            ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>

</html>
