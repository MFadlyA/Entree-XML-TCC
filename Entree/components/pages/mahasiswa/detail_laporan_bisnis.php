<?php
session_start();
// Koneksi ke database
include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /Entree/login');
    exit;
}

// Cek apakah role pengguna sesuai
if ($_SESSION['role'] !== 'Mahasiswa') {
    header('Location: /Entree/login');
    exit;
}

// Mengambil id dari URL
$id_laporan = isset($_GET['id']) ? $_GET['id'] : '';

// Ambil ID kelompok pengguna dari database
$user_id = $_SESSION['user_id'];
$query = "SELECT id_kelompok FROM mahasiswa WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$user_kelompok_id = $row['id_kelompok']; // ID kelompok pengguna dari database

// Ambil ID laporan dari parameter URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: /Entree/mahasiswa/dashboard.php'); // Redirect jika ID tidak ada di URL
    exit;
}
$requested_laporan_id = intval($_GET['id']);

// Periksa apakah laporan milik kelompok pengguna
$query = "SELECT id_kelompok FROM laporan_bisnis WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $requested_laporan_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: /Entree/mahasiswa/dashboard'); // Redirect jika laporan tidak ditemukan
    exit;
}

$row = $result->fetch_assoc();
$laporan_kelompok_id = $row['id_kelompok'];

// Periksa apakah ID kelompok dari laporan cocok dengan ID kelompok pengguna
if ($laporan_kelompok_id !== $user_kelompok_id) {
    header('Location: /Entree/mahasiswa/dashboard'); // Redirect jika tidak sesuai
    exit;
}

// Jika ID cocok, lanjutkan untuk memproses detail laporan
$query = "SELECT * FROM laporan_bisnis WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $requested_laporan_id);
$stmt->execute();
$laporan_result = $stmt->get_result();

if ($laporan_result->num_rows === 0) {
    header('Location: /Entree/mahasiswa/dashboard'); // Redirect jika data laporan tidak ditemukan
    exit;
}

// Fetch data laporan berdasarkan id
$query = "SELECT * FROM laporan_bisnis WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_laporan); // Menggunakan "i" untuk integer
$stmt->execute();
$result = $stmt->get_result();

// Memeriksa apakah laporan ditemukan
if ($result->num_rows > 0) {
    $laporan = $result->fetch_assoc();
} else {
    // Menangani kasus jika laporan tidak ditemukan
    echo "Laporan tidak ditemukan.";
    exit;
}

// Mendapatkan nama-nama file PDF yang diupload
$laporan_pdf = $laporan['laporan_pdf']; // Nama file-file PDF disimpan dalam kolom ini
$pdf_files_clean = [];

// Jika kolom tidak kosong, bersihkan nama file dari simbol tidak diinginkan
if (!empty($laporan_pdf)) {
    $pdf_files = explode(',', $laporan_pdf); // Pisahkan file PDF berdasarkan koma
    $pdf_files_clean = array_map(function ($file) {
        return trim($file, ' "[]'); // Menghapus spasi, tanda kutip, dan []
    }, $pdf_files);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Laporan Bisnis | Entree</title>
    <link rel="icon" href="\Entree\assets\img\icon_favicon.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/77a99d5f4f.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="/Entree/assets/css/detail_laporan_bisnis.css">
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php 
            $activePage = 'laporan_bisnis_mahasiswa'; // Halaman ini aktif
            include 'sidebar_mahasiswa.php'; 
        ?>

        <!-- Main Content -->
        <div class="main p-3">
            <!-- Header -->
            <?php 
                $pageTitle = "Detail Laporan Kemajuan Bisnis"; // Judul halaman
                include 'header_mahasiswa.php'; 
            ?>

            <!-- Content Wrapper -->
            <div class="main_wrapper">

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                    <h2><?php echo htmlspecialchars($laporan['judul_laporan']); ?></h2>
                    <div>
                        <?php
                            if ($laporan['jenis_laporan'] == 'Laporan Kemajuan') {
                                echo '<p class="alert alert-success text-white fw-bold text-center m-0 p-2 px-3 mb-4" style="background-color:#2ea56f; width:fit-content;" role="alert">Laporan Kemajuan</p>';
                            } elseif ($laporan['jenis_laporan'] == 'Laporan Akhir') {
                                echo '<p class="alert alert-info text-white fw-bold text-center m-0 p-2 px-3 mb-4" style="background-color:#007bff; width:fit-content;" role="alert">Laporan Akhir</p>';
                            } else {
                                echo '<p class="alert alert-warning text-white fw-bold text-center m-0 p-2 px-3 mb-4" style="background-color:orange; width:fit-content;" role="alert">Tidak ada Jenis Laporan</p>';
                            }
                        ?>
                    </div> 
                </div>

                <?php if (!empty($laporan['laporan_penjualan'])): ?>
                    <p>Laporan Penjualan:</p>
                    <div class="file-box">
                        <p><?php echo htmlspecialchars($laporan['laporan_penjualan']); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($laporan['laporan_pemasaran'])): ?>
                    <p>Laporan Pemasaran:</p>
                    <div class="file-box">
                        <p><?php echo htmlspecialchars($laporan['laporan_pemasaran']); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($laporan['laporan_produksi'])): ?>
                    <p>Laporan Produksi:</p>
                    <div class="file-box">
                        <p><?php echo htmlspecialchars($laporan['laporan_produksi']); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($laporan['laporan_sdm'])): ?>
                    <p>Laporan SDM:</p>
                    <div class="file-box">
                        <p><?php echo htmlspecialchars($laporan['laporan_sdm']); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($laporan['laporan_keuangan'])): ?>
                    <p>Laporan Keuangan:</p>
                    <div class="file-box">
                        <p><?php echo htmlspecialchars($laporan['laporan_keuangan']); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Menampilkan Lampiran PDF dari AWS S3 -->
                <div>
                    <h3 id="fileHeading">Lampiran</h3>
                    <ul id="fileList">
                        <?php
                        // Asumsikan $row['laporan_pdf'] berisi JSON array berisi URL dari AWS S3
                        $lampiran_array = json_decode($laporan['laporan_pdf'], true);

                        if (!empty($lampiran_array) && is_array($lampiran_array)): ?>
                            <?php foreach ($lampiran_array as $file_url): ?>
                                <?php
                                // Ambil nama file dari URL
                                $file_name = basename(urldecode(parse_url($file_url, PHP_URL_PATH)));

                                // Pecah berdasarkan underscore
                                $parts = explode('_', $file_name, 3); // Maksimal 3 bagian
                                if (count($parts) === 3) {
                                    $clean_name = $parts[2]; // Ambil bagian nama asli file
                                } else {
                                    $clean_name = $file_name; // Fallback
                                }

                                // Ganti underscore dengan spasi dan hilangkan ekstensi
                                $clean_name = preg_replace([
                                    '/_/',         // underscore jadi spasi
                                    '/\.[^.]+$/'   // hapus ekstensi
                                ], [
                                    ' ',
                                    ''
                                ], $clean_name);

                                // Format kapitalisasi
                                $clean_name = ucwords(strtolower($clean_name));
                                ?>
                                
                                <li>
                                    <div class="file-info">
                                        <?= htmlspecialchars($clean_name ?: 'Dokumen tanpa judul') ?>
                                        <span class="file-meta">(PDF)</span>
                                    </div>
                                    <div class="icon-group">
                                        <a href="<?= htmlspecialchars($file_url) ?>" 
                                        target="_blank" 
                                        class="fa-solid fa-eye detail-icon" 
                                        data-bs-toggle="tooltip" 
                                        title="Lihat Dokumen"></a>
                                        <a href="\Entree\components\pages\download_from_aws\download_lampiran_laporan.php?file=<?= urlencode($file_url) ?>"                                        download 
                                        class="fa-solid fa-download btn-icon" 
                                        data-bs-toggle="tooltip" 
                                        title="Unduh Dokumen"></a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="no-files">Tidak ada dokumen lampiran</li>
                        <?php endif; ?>   
                    </ul>
                </div>
                
                <p>Umpan Balik Dari Mentor Bisnis:</p>
                <div class="feedback-box">
                    <p><?php echo htmlspecialchars($laporan['feedback'] ?? 'Belum ada umpan balik.'); ?></p>
                </div>
                <a href="laporan_bisnis" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
    </div>


</body>

</html>