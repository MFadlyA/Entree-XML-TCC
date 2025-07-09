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

// Mendapatkan ID kelompok dari parameter URL dan validasi
$id_kelompok = isset($_GET['id_kelompok']) ? (int)$_GET['id_kelompok'] : null;

if ($id_kelompok) {
    // Query untuk memeriksa apakah kelompok dengan id_kelompok ada untuk mentor yang sedang login
    $sql_check = "SELECT k.* 
                  FROM kelompok_bisnis k 
                  WHERE k.id_kelompok = ?";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("i", $id_kelompok);
    $stmt->execute();
    $result = $stmt->get_result();
    $kelompok = $result->fetch_assoc();

    if (!$kelompok) {
        // Redirect jika kelompok tidak ditemukan atau mentor tidak memiliki akses
        header('Location: /Entree/mentor/dashboard');
        exit;
    }
}

if ($id_kelompok) {
    // Mengambil data laporan bisnis yang terkait dengan kelompok yang login
    $sql = "SELECT * FROM laporan_bisnis WHERE id_kelompok = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_kelompok);  // Mengikat parameter id_kelompok
    $stmt->execute();
    $result = $stmt->get_result();

    // Menutup prepared statement setelah eksekusi
    $stmt->close();
} else {
    // Jika id_kelompok tidak diberikan, tampilkan pesan atau redirect
    echo "ID kelompok tidak ditemukan!";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Bisnis | Entree</title>
    <link rel="icon" href="\Entree\assets\img\icon_favicon.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/77a99d5f4f.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="/Entree/assets/css/laporan_bisnis.css">
</head>

<body>
    <div class="wrapper">
        <?php 
        $activePage = 'laporan_bisnis_mentor'; // Halaman ini aktif
        include 'sidebar_mentor.php'; 
        ?>

        <div class="main p-3">
            <div class="main_header">
                <?php 
                    $pageTitle = "Laporan Kemajuan Bisnis"; // Judul halaman
                    include 'header_mentor.php'; 
                ?>
            </div>

            <div class="main_wrapper">
                <div class="container-of-card-container m-0 p-0" style="min-height: 70vh;">
                    <?php
                        // Mengecek apakah ada hasil laporan yang diambil
                        if ($result->num_rows > 0) {
                            while ($laporan = $result->fetch_assoc()) {
                                $id = $laporan['id'];
                                ?>
                                <div class="card">
                                    <div class="card-header">
                                        <h2><?php echo htmlspecialchars($laporan['judul_laporan']); ?></h2>
                                    </div>
                                    <a href="detail_laporan?id=<?php echo $id; ?>&id_kelompok=<?php echo $id_kelompok; ?>">
                                        <div class="card-body">
                                            <i class="fa-solid fa-eye detail-icon" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Lihat Detail Laporan Kemajuan Bisnis"></i>
                                        </div>
                                    </a>
                                    <div class="card-footer">
                                        <a href="detail_laporan?id=<?php echo $id; ?>&id_kelompok=<?php echo $id_kelompok; ?>">Lihat Umpan Balik</a>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            // Jika tidak ada laporan, tampilkan pesan
                            echo '<div class="d-flex justify-content-center" >
                                    <div class="alert alert-warning text-center" role="alert" style="width: fit-content;">
                                        Belum ada laporan kemajuan bisnis untuk kelompok ini.
                                    </div>
                                </div>
                                ';
                        }
                        ?> 
                </div>
                <div onclick="window.location.href='detail_kelompok?id_kelompok=<?php echo $id_kelompok; ?>'">
                    <!-- Tombol dengan ukuran lebih kecil dan penataan posisi di tengah -->
                    <button class="btn btn-secondary" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Tekan Untuk Kembali">Kembali ke Kelompok Bisnis</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>