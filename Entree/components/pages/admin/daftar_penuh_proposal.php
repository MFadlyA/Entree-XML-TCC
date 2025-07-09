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
include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';

// Query untuk mengambil data
$query = "
    SELECT 
    k.nama_kelompok, 
    m.nama AS ketua_kelompok, -- Ambil nama ketua kelompok dari tabel mahasiswa
    p.judul_proposal, 
    p.ide_bisnis, 
    p.tahapan_bisnis, 
    p.sdg, 
    p.kategori, 
    p.other_category,
    p.proposal_pdf, 
    p.status
FROM 
    proposal_bisnis p
JOIN 
    kelompok_bisnis k 
ON 
    p.kelompok_id = k.id_kelompok
LEFT JOIN 
    mahasiswa m 
ON 
    k.npm_ketua = m.npm -- Hubungkan berdasarkan NPM

";

// Eksekusi query
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Proposal | Entree</title>
    <link rel="icon" href="\Entree\assets\img\icon_favicon.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/77a99d5f4f.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="/Entree/assets/css/detail_proposal.css">
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php 
            $activePage = 'daftar_penuh_proposal'; // Halaman ini aktif
            include 'sidebar_admin.php'; 
        ?>

        <!-- Main Content -->
        <div class="main p-3">
            <!-- Header -->
            <?php 
                $pageTitle = "Daftar Proposal Bisnis"; // Judul halaman
                include 'header_admin.php'; 
            ?>

            <div class="main_wrapper">
                <div class="container mt-4">
                    <?php
                    if (isset($_SESSION['toast_message'])) {
                        echo "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                var toastMessage = document.getElementById('toastMessage');
                                var toastContent = document.getElementById('toastMessageContent');
                                toastContent.textContent = '" . $_SESSION['toast_message'] . "'; // Set the message dynamically
                                var toast = new bootstrap.Toast(toastMessage);
                                toast.show(); // Show the toast message
                            });
                        </script>";
                        unset($_SESSION['toast_message']); // Clear the message after displaying it
                    }
                    ?>

                    <h2>Daftar Proposal</h2>
                    <div class="table-responsive">
                    <form method="post" action="/Entree/components/pages/admin/export_proposal_excel.php" class="mb-3">
                            <button type="submit" class="btn btn-success">
                                    <i class="fa-solid fa-file-excel"></i> Ekspor ke Excel
                                </button>
                            </form>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama Kelompok</th>
                                    <th>Ketua Kelompok</th> 
                                    <th>Judul Proposal</th>
                                    <!-- <th>Ide Bisnis</th>
                                    <th>Tahapan Bisnis</th>
                                    <th>SDG</th> -->
                                    <th>Kategori Bisnis</th>
                                    <th>Proposal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['nama_kelompok']); ?></td>
                                        <td><?php echo htmlspecialchars($row['ketua_kelompok']); ?></td>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['judul_proposal']); ?></td>
                                        <!-- <td><?php echo htmlspecialchars($row['ide_bisnis']); ?></td>
                                        <td><?php echo htmlspecialchars($row['tahapan_bisnis']); ?></td>
                                        <td><?php echo htmlspecialchars($row['sdg']); ?></td> -->
                                        <td>
                                            <?php
                                                if ($row['kategori'] === 'lainnya') {
                                                    echo htmlspecialchars($row['other_category']);
                                                } else {
                                                    echo htmlspecialchars($row['kategori']);
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <ul id="fileList">
                                                <li class="file-box">
                                                   <div class="file-info text-truncate" style="max-width: 200px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">
                                                        <?php
                                                            $filename = basename(urldecode($row['proposal_pdf']));
                                                            $cleanName = preg_replace('/^\d+_/', '', $filename); // hapus angka + underscore di awal
                                                            echo htmlspecialchars($cleanName);
                                                        ?>
                                                    </div>
                                                    <div class="icon-group">
                                                        <a href="/Entree/components/pages/mahasiswa/uploads/proposal/<?php echo basename($row['proposal_pdf']); ?>" target="_blank" class="detail-icon" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Lihat File">
                                                            <i class="fa-solid fa-eye"></i>
                                                        </a>
                                                        <a href="/Entree/components/pages/mahasiswa/uploads/proposal/<?php echo basename($row['proposal_pdf']); ?>" download class="btn-icon" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Unduh File">
                                                            <i class="fa-solid fa-download"></i>
                                                        </a>
                                                    </div>
                                                </li>
                                            </ul>
                                        </td>
                                        <td>
                                            <span id="status-label" class="status"
                                                style="background-color: <?php
                                                    if ($row['status'] == 'disetujui') {
                                                        echo '#2ea56f';
                                                    } elseif ($row['status'] == 'ditolak') {
                                                        echo '#dc3545';
                                                    } else {
                                                        echo 'orange';
                                                    }
                                                ?>;">
                                                <?php echo htmlspecialchars($row['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>