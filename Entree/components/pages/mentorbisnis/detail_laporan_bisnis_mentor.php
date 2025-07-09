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

// Mendapatkan ID laporan dan ID kelompok dari parameter URL dan validasi
$id_laporan = isset($_GET['id']) ? $_GET['id'] : null;
$id_kelompok = isset($_GET['id_kelompok']) ? $_GET['id_kelompok'] : null;
$mentor_name = isset($_SESSION['nama']) ? $_SESSION['nama'] : null;

// Periksa apakah proposal bisnis dengan ID ini milik kelompok pengguna
$query = "SELECT id FROM laporan_bisnis WHERE id = ? AND id_kelompok = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_laporan, $id_kelompok);
$stmt->execute();
$result = $stmt->get_result();

// Jika proposal tidak ditemukan atau tidak sesuai, redirect ke dashboard
if ($result->num_rows === 0) {
    header('Location: /Entree/mentor/dashboard');
    exit;
}

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

// Memeriksa apakah ID laporan dan ID kelompok ada
if ($id_laporan) {
    // Ambil data laporan berdasarkan ID laporan
    $sql = "SELECT * FROM laporan_bisnis WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_laporan);  // Mengikat parameter id_laporan dan id_kelompok
    $stmt->execute();
    $result = $stmt->get_result();

    // Menutup prepared statement setelah eksekusi
    $stmt->close();

    if ($result->num_rows > 0) {
        // Ambil data laporan
        $laporan = $result->fetch_assoc();
        // Tampilkan detail laporan di sini
    } else {
        echo "Laporan tidak ditemukan.";
    }
} else {
    echo "ID laporan atau ID kelompok tidak ditemukan!";
    exit;
}

if ($id_kelompok) {
    $stmt = $conn->prepare("SELECT id_mentor FROM kelompok_bisnis WHERE id_kelompok = ?");
    $stmt->bind_param("i", $id_kelompok);
    $stmt->execute();
    $result = $stmt->get_result();

    // Memeriksa apakah data ditemukan
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id_mentor = $row['id_mentor'];
    }
} else {
    echo "ID Kelompok tidak valid.";
}

$mentorQuery = "
        SELECT m.nama AS nama_mentor
        FROM mentor m
        WHERE m.id = '" . $id_mentor . "' LIMIT 1";
    $mentorResult = mysqli_query($conn, $mentorQuery);
    $mentor = mysqli_fetch_assoc($mentorResult);
    $namaMentor = $mentor['nama_mentor'] ?? 'Nama mentor tidak tersedia';

// Cek apakah mentor yang login sama dengan mentor yang terdaftar di kelompok
$is_mentor_matched = ($mentor_name === $namaMentor);

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="/Entree/assets/css/detail_laporan_bisnis.css">
</head>
<style>
    .Feedback {
        margin: 20px 0;
        padding: 15px;
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 5px;
        line-height: 1.6;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
</style>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php 
            $activePage = 'laporan_bisnis_mentor'; // Halaman ini aktif
            include 'sidebar_mentor.php'; 
        ?>

        <!-- Main Content -->
        <div class="main p-3">
            <!-- Header -->
            <?php 
                $pageTitle = "Detail Laporan Kemajuan Bisnis"; // Judul halaman
                include 'header_mentor.php'; 
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
                
                <?php if ($is_mentor_matched): ?>
                    <?php if (!empty($laporan['feedback'])): ?>
                        <strong>Umpan Balik dari Mentor Bisnis:</strong>
                        <div class="feedback-box">
                            <p><?php echo htmlspecialchars($laporan['feedback'] ?? 'Belum ada umpan balik.'); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="accordion accordion-flush" id="accordionFlushExample">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                            <button class="accordion-button collapsed text-white" style="background-color:#2ea56f; border-radius:5px;" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                                Masukkan Umpan Balik Anda:
                            </button>
                            </h2>
                            <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">
                            <form id="feedbackForm">
                                <div class="mb-3">
                                    <textarea class="form-control" id="feedbackInput" name="feedback" rows="5" placeholder="Tulis umpan balik Anda di sini..." required></textarea>
                                </div>
                                <input type="hidden" id="laporanId" value="<?php echo htmlspecialchars($laporan['id']); ?>">
                                <div id="feedbackMessage" class="mt-3"></div>
                                <div class="btn_container d-flex justify-content-end">
                                    <button type="submit" class="btn">Kirim Umpan Balik</button>
                                </div>
                            </form>                                
                            </div>
                            </div>
                        </div>
                    </div>
                    
                    <script>
                        document.getElementById('feedbackForm').addEventListener('submit', async function (event) {
                            event.preventDefault();

                            const feedback = document.getElementById('feedbackInput').value;
                            const laporanId = document.getElementById('laporanId').value;

                            // Validasi feedback sebelum dikirim
                            if (feedback.trim() === "") {
                                alert("Feedback tidak boleh kosong!");
                                return;
                            }

                            try {
                                const response = await fetch('submit_feedback_laporan', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        feedback: feedback,
                                        laporan_id: laporanId,
                                    }),
                                });

                                const result = await response.json();
                                const messageDiv = document.getElementById('feedbackMessage');

                                if (result.success) {
                                    messageDiv.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                                } else {
                                    messageDiv.innerHTML = `<div class="alert alert-danger">${result.message}</div>`;
                                }
                            } catch (error) {
                                console.error('Error:', error);
                                document.getElementById('feedbackMessage').innerHTML = `<div class="alert alert-danger">Terjadi kesalahan. Silakan coba lagi.</div>`;
                            }
                        });
                    </script>
                <?php else: ?>
                    <p>Umpan Balik Dari Mentor Bisnis:</p>
                    <div class="feedback-box">
                        <p><?php echo htmlspecialchars($laporan['feedback'] ?? 'Belum ada Umpan Balik.'); ?></p>
                    </div>
                <?php endif; ?>
                <a href="laporan_bisnis?id_kelompok=<?php echo $id_kelompok; ?>" class="btn btn-secondary mt-4">Kembali</a>
            </div>
        </div>
    </div>

</body>
</html>
