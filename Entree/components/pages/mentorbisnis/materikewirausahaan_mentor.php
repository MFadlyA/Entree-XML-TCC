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
    <title>Materi Kewirausahaan | Entree</title>
    <link rel="icon" href="\Entree\assets\img\icon_favicon.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/77a99d5f4f.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="/Entree/assets/css/materikewirausahaan.css">
</head>

<body>
    <div class="wrapper">
        <?php 
        $activePage = 'materikewirausahaan_mentor'; // Halaman ini aktif
        include 'sidebar_mentor.php'; 
        ?>

        <div class="main p-3">
            <div class="main_header">
                <?php 
                    $pageTitle = "Materi Kewirausahaan"; // Judul halaman
                    include 'header_mentor.php'; 
                ?>
            </div>

            <div class="main_wrapper">

               <!-- Form pencarian -->
                <form action="" method="get">
                    <div class="input-group mb-3">
                        <div class="d-flex justify-content-end" role="search">
                            <input type="text" class="form-control me-2" placeholder="Cari Materi Kewirausahaan" name="search" aria-label="Search" value="<?= htmlspecialchars($_GET['search'] ?? ''); ?>">
                            <button class="btn btn-outline-success px-5" type="submit">Cari</button>
                        </div>
                    </div>
                </form>

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
                                <a href="detail_materi_kewirausahaan?id=' . $row["id"] . '">
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
</body>

</html>