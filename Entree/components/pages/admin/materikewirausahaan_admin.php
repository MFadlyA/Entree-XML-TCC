<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /Entree/login');
    exit;
}

// Cek apakah role pengguna sesuai
if ($_SESSION['role'] !== 'Admin') {
    header('Location: /Entree/login');
    exit;
}

function getFileIcon($fileExtension) {
    $fileExtension = strtolower($fileExtension);
    switch ($fileExtension) {
        case 'mp4':
        case 'webm':
        case 'mov':
        case 'avi':
            return '/Entree/assets/img/icon_video.png';
        case 'ppt':
        case 'pptx':
            return '/Entree/assets/img/icon_ppt.png';
        case 'pdf':
            return '/Entree/assets/img/icon_pdf.png';
        case 'doc':
        case 'docx':
            return '/Entree/assets/img/icon_word.png';
        case 'xls':
        case 'xlsx':
            return '/Entree/assets/img/icon_excel.png';
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            return '/Entree/assets/img/icon_image.png';
        default:
            return '/Entree/assets/img/icon_default.png';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';
    require $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/aws_s3_config.php';

    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];

    if (isset($_FILES['materi']) && $_FILES['materi']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['materi']['tmp_name'];
        $fileName = basename($_FILES['materi']['name']);

        // Buat key (path di bucket S3)
        $key = "materi_kewirausahaan/" . time() . "_" . $fileName;

        try {
            // Upload ke S3
            $result = $s3->putObject([
                'Bucket'     => $bucketName,
                'Key'        => $key,
                'SourceFile' => $fileTmpPath,
                'ContentType' => mime_content_type($fileTmpPath),
                'ContentDisposition' => 'inline',
                // 'ACL'        => 'public-read' // atau 'private' jika ingin lebih aman
            ]);

            $fileUrl = $result['ObjectURL']; // URL publik file di S3

            // Simpan URL ke DB
            $sql = "INSERT INTO materi_kewirausahaan (judul, file_path, deskripsi) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $judul, $fileUrl, $deskripsi);

            if ($stmt->execute()) {
                $_SESSION['toast_success'] = true;
                header("Location: materi_kewirausahaan");
                exit();
            } else {
                echo "Gagal simpan ke database.";
            }
            $stmt->close();
        } catch (Aws\S3\Exception\S3Exception $e) {
            echo "Upload gagal: " . $e->getMessage();
        }
    } else {
        echo "Tidak ada file yang diunggah.";
    }
}
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materi Kewirausahaan | Entree</title>
    <link rel="icon" href="\Entree\assets\img\icon_favicon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Entree/assets/css/materikewirausahaan.css">
</head>

<body>
    <div class="wrapper">
        <?php 
        $activePage = 'materikewirausahaan_admin';
        include 'sidebar_admin.php'; 
        ?>

        <div class="main p-3">
            <div class="main_header">
                <?php 
                $pageTitle = "Materi Kewirausahaan";
                include 'header_admin.php'; 
                ?>
            </div>

            <div class="main_wrapper">
               
            

                 <!-- Form pencarian -->
                 <form action="" method="get">
                    <div class="input-group mb-3">
                        <div class="d-flex admin" role="search">  
                            <button type="button" class="btn-hijau m-0 me-3" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                Tambah Materi
                            </button>
                            <div class="right-admin">
                                <input  type="text" class="form-control me-2 input-admin" placeholder="Cari Materi Kewirausahaan" name="search" aria-label="Search" value="<?= htmlspecialchars($_GET['search'] ?? ''); ?>">
                                <button class="btn btn-outline-success" type="submit">Cari</button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">Tambah Materi Kewirausahaan</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" action="" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="judul">Judul Materi:<span style="color:red;">*</span></label>
                                        <input type="text" id="judul" name="judul" required placeholder="Masukkan Judul Materi">
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                    <label for="materi" class="form-label">Unggah Materi:<span style="color:red;">*</span><span style="color:grey;"><small>(PDF, Word, PPT, Video, Exel, Gambar)</small></span></label>
                                    <div class="input-group">
                                    <input type="file" class="form-control" id="materi" name="materi" accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.avi,.mov,.mkv,.xls,.xlsx,.jpg,.jpeg,.png" required />
                                    </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="deskripsi">Deskripsi Materi:<span style="color:red;">*</span><span style="color:grey;"><small>(maksimal 3000 karakter)</small></span></label>
                                        <textarea id="deskripsi" name="deskripsi" required placeholder="Masukkan Deskripsi Materi"></textarea>
                                    </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                <button type="submit" class="btn btn-success" name="kirim">Unggah Materi</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- Toast Notification -->
                <?php if (isset($_SESSION['toast_success'])): ?>
                    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1055;">
                        <div class="toast text-bg-success border-0" id="toastSuccess" role="alert" aria-live="assertive" aria-atomic="true">
                            <div class="toast-header">
                                <img src="\Entree\assets\img\icon_entree_pemberitahuan.png" style="width:40%; height:40%;" class="rounded me-2" alt="Logo">
                                <strong class="me-auto"></strong>
                                <small>Just now</small>
                                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                            <div class="toast-body">
                                Materi berhasil diunggah!
                            </div>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const toastSuccess = document.getElementById('toastSuccess');
                            if (toastSuccess) {
                                const toast = new bootstrap.Toast(toastSuccess);
                                toast.show();
                            }
                        });
                    </script>
                    <?php unset($_SESSION['toast_success']); ?>
                <?php endif; ?>

                <!-- PHP untuk menampilkan materi -->
                <div class="card-container">
                    <?php
                        include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';

                        // Ambil parameter pencarian
                        $search = $_GET['search'] ?? '';

                        // Filter input untuk mencegah SQL Injection
                        $search = $conn->real_escape_string($search);

                        // Tambahkan kondisi pencarian jika ada input
                        if ($search) {
                            $sql = "SELECT * FROM materi_kewirausahaan WHERE judul LIKE '%$search%' OR deskripsi LIKE '%$search%'";
                        } else {
                            $sql = "SELECT * FROM materi_kewirausahaan";
                        }

                        $result = $conn->query($sql);

                        if ($result === false) {
                            echo "<p>Error pada query: " . $conn->error . "</p>";
                        } elseif ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $filePath = htmlspecialchars($row["file_path"]);
                                $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);

                                // Gunakan fungsi getFileIcon untuk mendapatkan jalur icon
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
                                    <p>Belum Ada Materi Kewirausahaan yang sesuai dengan pencarian Anda.</p>
                                </div>
                            </div>
                            ';
                        }

                        $conn->close();
                    ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
