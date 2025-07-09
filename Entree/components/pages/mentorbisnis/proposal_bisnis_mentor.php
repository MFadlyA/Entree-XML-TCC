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

// Mendapatkan ID kelompok dari parameter URL
$id_kelompok = isset($_GET['id_kelompok']) ? $_GET['id_kelompok'] : null;

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
    // Mengambil data proposal bisnis yang terkait dengan kelompok yang login
    $sql = "SELECT * FROM proposal_bisnis WHERE kelompok_id = $id_kelompok";
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposal Bisnis | Entree</title>
    <link rel="icon" href="\Entree\assets\img\icon_favicon.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/77a99d5f4f.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="/Entree/assets/css/proposal_bisnis.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/habibmhamadi/multi-select-tag@3.1.0/dist/css/multi-select-tag.css">
</head>

<body>
    <div class="wrapper">
        <?php 
        $activePage = 'proposal_bisnis_mentor'; // Halaman ini aktif
        include 'sidebar_mentor.php'; 
        ?>

        <div class="main p-3">
            <div class="main_header">
                <?php 
                    $pageTitle = "Proposal Bisnis"; // Judul halaman
                    include 'header_mentor.php'; 
                ?>
            </div>

            <div class="main_wrapper">
                <!-- Menampilkan proposal bisnis dalam bentuk card -->
                <div class="container-of-card-container" style="min-height: 70vh;">
                    <div class="card-container">
                        <?php
                        // Memeriksa apakah ada data proposal yang diambil dari database
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($proposal = mysqli_fetch_assoc($result)) {
                                // Ambil id untuk URL
                                $id = $proposal['id'];
                                ?>
                                <div class="card" style="width: 33%; margin: 10px;">
                                    <div class="card-icon text-center py-4">
                                        <img src="\Entree\assets\img\document-file_6424455.png" alt="Dokumen" style="width: 50px; height: 50px;">
                                    </div>
                                    <div class="card-body m-0">
                                        <h5 class="card-title"><?php echo htmlspecialchars($proposal['judul_proposal']); ?></h5>
                                    </div>
                                    <table class="table table-bordered m-0 styled-table">
                                        <tbody>
                                            <tr>
                                            <td style="width: 60%;">Status Proposal Bisnis:</td>
                                                <td>
                                                    <?php
                                                        if ($proposal['status'] == 'disetujui') {
                                                            echo '<p class="alert alert-success text-white fw-bold text-center p-2 m-0" style="background-color:#2ea56f; border-radius: 5px;" role="alert">Disetujui</p>';
                                                        } elseif ($proposal['status'] == 'ditolak') {
                                                            echo '<p class="alert alert-danger text-white fw-bold text-center p-2 m-0" style="background-color:#dc3545; border-radius: 5px;" role="alert">Ditolak</p>';
                                                        } else {
                                                            echo '<p class="alert alert-warning text-white fw-bold text-center p-2 m-0" style="background-color: #ffc107; border-radius: 5px;" role="alert">Menunggu</p>';
                                                        }
                                                    ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="card-footer">
                                    <a href="detail_proposal?id=<?php echo $id; ?>&id_kelompok=<?php echo $id_kelompok; ?>">
                                        <i class="fa-solid fa-eye detail-icon" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Lihat Detail Proposal Bisnis"></i>
                                    </a>
                                    </div>
                                </div>

                                <?php
                            }
                        } else {
                            echo '<div class="alert alert-warning" role="alert">Belum ada proposal untuk kelompok ini.</div>';
                        }
                        ?>
                    </div>
                </div>
                <div onclick="window.location.href='detail_kelompok?id_kelompok=<?php echo $id_kelompok; ?>'">
                    <!-- Tombol dengan ukuran lebih kecil dan penataan posisi di tengah -->
                    <button class="btn btn-secondary mt-3" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Tekan Untuk Kembali">Kembali ke Kelompok Bisnis</button>
                </div>
            </div>  
        </div>
    </div>

</body>

</html>

<?php
// Menutup koneksi database
mysqli_close($conn);
?> 