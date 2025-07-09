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
$userId = $_SESSION['user_id'];

// 1. Ambil ID dari tabel mentor_bisnis
$sql_id = "SELECT id FROM mentor WHERE user_id = '$userId'";
$result_id = $conn->query($sql_id);

if (!$result_id) {
    die("Error query sql_id: " . $conn->error); // Handle error query
}

if ($result_id->num_rows > 0) {
    $row_id = $result_id->fetch_assoc();
    $id_mentor = $row_id['id']; // Ambil nilai ID mentor
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

// Query untuk memastikan mentor adalah mentor bisnis kelompok
$isMentorQuery = "SELECT * FROM kelompok_bisnis WHERE id_kelompok = ? AND id_mentor = ?";
$stmt = $conn->prepare($isMentorQuery);
$stmt->bind_param("ii", $id_kelompok, $id_mentor);
$stmt->execute();
$result = $stmt->get_result();
$isMentor = $result->num_rows > 0;

if ($id_kelompok) {
    // Query untuk mengambil detail kelompok bisnis berdasarkan id
    $sql = "SELECT * FROM kelompok_bisnis WHERE id_kelompok = $id_kelompok";
    $result = $conn->query($sql);
    $kelompok = $result->fetch_assoc();
    $statusInkubasi = $kelompok['status_inkubasi'] ?? null;

    // Query untuk mengambil nama ketua kelompok berdasarkan npm_ketua
    $ketuaQuery = "SELECT nama FROM mahasiswa WHERE npm = '" . $kelompok['npm_ketua'] . "' LIMIT 1";
    $ketuaResult = $conn->query($ketuaQuery);
    $ketua = $ketuaResult->fetch_assoc();

    // Query untuk mengambil anggota kelompok berdasarkan id_kelompok
    $anggotaSql = "SELECT m.nama, m.npm 
                   FROM anggota_kelompok ak 
                   JOIN mahasiswa m ON ak.npm_anggota = m.npm 
                   WHERE ak.id_kelompok = $id_kelompok";
    $anggotaResult = $conn->query($anggotaSql);
} else {
    echo "Kelompok bisnis tidak ditemukan.";
    exit;
}

// Ambil detail mentor berdasarkan nama mentor dari kelompok bisnis
$mentorData = [];
if (!empty($kelompok['id_mentor'])) {
    // Query untuk detail mentor
    $mentorQuery = "
    SELECT m.nama AS nama_mentor, 
        m.fakultas, 
        m.prodi, 
        m.contact, 
        m.email,
        m.keahlian, 
        m.foto_profile,
        u.role 
    FROM mentor m
    JOIN users u ON m.user_id = u.id
    WHERE m.id = '" . $kelompok['id_mentor'] . "' 
    LIMIT 1";
    
    $mentorResult = mysqli_query($conn, $mentorQuery);
    $mentorData = mysqli_fetch_assoc($mentorResult); 
}

$mentorAda = !empty($kelompok['id_mentor']);

// $mentorQuery = "
//     SELECT m.nama AS nama_mentor
//     FROM mentor m
//     WHERE m.id = '" . $kelompok['id_mentor'] . "' LIMIT 1";
// $mentorResult = mysqli_query($conn, $mentorQuery);
// $mentor = mysqli_fetch_assoc($mentorResult);
// $namaMentor = $mentor['nama_mentor'] ?? 'Nama mentor tidak tersedia';

$sql = "SELECT * FROM jadwal WHERE id_klmpk = ? ORDER BY tanggal, waktu";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_kelompok); // Bind parameter id_kelompok
$stmt->execute();
$result = $stmt->get_result();

// Variabel untuk menghitung jadwal status 'selesai'
$totalSelesai = 0;

// Loop data hasil query
while ($row = $result->fetch_assoc()) {
    if ($row['status'] === 'selesai') {
        $totalSelesai++; // Tambahkan 1 jika status 'selesai'
    }
}

// Mapping SDG
$sdg_mapping = [
    "mengakhiri_kemiskinan" => "1. Mengakhiri Kemiskinan",
    "mengakhiri_kelaparan" => "2. Mengakhiri Kelaparan",
    "kesehatan_kesejahteraan" => "3. Kesehatan dan Kesejahteraan",
    "pendidikan_berkualitas" => "4. Pendidikan Berkualitas",
    "kesetaraan_gender" => "5. Kesetaraan Gender",
    "air_bersih_sanitasi" => "6. Air Bersih dan Sanitasi",
    "energi_bersih_terjangkau" => "7. Energi Bersih dan Terjangkau",
    "pekerjaan_pertumbuhan_ekonomi" => "8. Pekerjaan Layak dan Pertumbuhan Ekonomi",
    "industri_inovasi_infrastruktur" => "9. Industri, Inovasi, dan Infrastruktur",
    "mengurangi_ketimpangan" => "10. Mengurangi Ketimpangan",
    "kota_komunitas_berkelanjutan" => "11. Kota dan Komunitas Berkelanjutan",
    "konsumsi_produksi_bertanggung_jawab" => "12. Konsumsi dan Produksi yang Bertanggung Jawab",
    "penanganan_perubahan_iklim" => "13. Penanganan Perubahan Iklim",
    "ekosistem_lautan" => "14. Ekosistem Lautan",
    "ekosistem_daratan" => "15. Ekosistem Daratan",
    "perdamaian_keadilan_institusi_kuat" => "16. Perdamaian, Keadilan, dan Kelembagaan yang Kuat",
    "kemitraan_tujuan" => "17. Kemitraan untuk Mencapai Tujuan"
];

// Proses SDG menjadi label deskriptif
$sdg_selected = explode(",", $kelompok['sdg'] ?? '');
$sdg_labels = array_map(function($key) use ($sdg_mapping) {
    return $sdg_mapping[$key] ?? $key;
}, $sdg_selected);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kelompok Bisnis | Entree</title>
    <link rel="icon" href="\Entree\assets\img\icon_favicon.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/77a99d5f4f.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="/Entree/assets/css/detail_kelompok.css">
    <link rel="stylesheet" href="/Entree/assets/css/jadwal_bimbingan.css">
</head>
<style>
    /* CSS untuk toast */
    .toast {
        position: fixed;
        top: 20px; /* Posisi di ujung kanan atas */
        right: 20px; /* Posisi di ujung kanan */
        padding: 10px 20px;
        background-color: #28a745; /* Hijau untuk toast sukses */
        color: white;
        border-radius: 5px;
        opacity: 0;
        transition: opacity 0.5s ease-in-out;
        z-index: 9999; /* Pastikan toast muncul di atas elemen lain */
    }

    .toast.show {
        opacity: 1;
    }
</style>

<body>
    <div class="wrapper">
        <?php 
        $activePage = 'daftar_kelompok_bisnis_mentor'; 
        include 'sidebar_mentor.php'; 
        ?>

        <div class="main p-3">
            <div class="main_header">
                <?php 
                    $pageTitle = "Detail Kelompok Bisnis"; 
                    include 'header_mentor.php'; 
                ?>
            </div>

            <div class="main_wrapper">
            <?php if ($kelompok) { ?>
                <div class="container">
                    <div class="left">
                        <!-- Logo Bisnis -->
                        <div class="d-flex flex-column align-items-center">
                            <?php
                            $logo_bisnis = !empty($kelompok['logo_bisnis']) ? $kelompok['logo_bisnis'] : '\Entree\assets\img\default-logo-bisnis.jpg';
                            $is_default_logo = empty($kelompok['logo_bisnis']);
                            ?>
                            
                            <img alt="Logo bisnis kelompok" 
                                class="w-12 h-12 rounded me-2 object-fit-cover" 
                                src="<?= htmlspecialchars($logo_bisnis) ?>" 
                                onerror="this.src='<?= htmlspecialchars($logo_bisnis) ?>'"/>
                            
                            <?php if ($is_default_logo): ?>
                                <span class="text-muted alert alert-warning text-center" role="alert">Belum ada logo bisnis</span>
                            <?php endif; ?>
                        </div>

                        <p>
                        <?php if ($isMentor && $statusInkubasi === 'masuk') : ?>
                            <button class="btn btn-danger mt-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseWidthExample_2" aria-expanded="false" aria-controls="collapseWidthExample">
                                <i class="fas fa-trash-alt"></i> 
                            </button>
                        <?php endif; ?>
                            </p>
                            <div style="min-height: 120px;">
                                <div class="collapse collapse-horizontal" id="collapseWidthExample_2">
                                    <div class="card card-body" style="width: 300px;">
                                        <!-- Tombol Hapus -->
                                        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                            Keluarkan Kelompok Dari Program Inkubasi
                                        </button>

                                    </div>
                                </div>
                            </div>

                        <!-- Modal Konfirmasi Hapus -->
                        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Pengeluaran</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Apakah Anda yakin ingin mengeluarkan kelompok bisnis ini dari Program Inkubasi Bisnis?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="button" class="btn btn-danger" id="confirmDeleteButton" data-id="<?php echo $id_kelompok; ?>">Keluarkan</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        // Menangani klik tombol "Keluarkan" pada modal
                        document.getElementById('confirmDeleteButton').addEventListener('click', function() {
                            // Ambil ID kelompok dari data-id tombol
                            var kelompokId = this.getAttribute('data-id');

                            // Kirim permintaan ke server untuk mengupdate status inkubasi menjadi NULL
                            if (kelompokId) {
                                var xhr = new XMLHttpRequest();
                                xhr.open('POST', 'keluarkan_kelompok', true);
                                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                                xhr.onload = function() {
                                    if (xhr.status === 200) {
                                        // Tanggapan berhasil, reload halaman atau tampilkan pesan sukses
                                        alert('Kelompok berhasil dikeluarkan');
                                        location.reload(); // Reload halaman untuk memperbarui data
                                    } else {
                                        // Tangani kesalahan jika gagal mengupdate
                                        alert('Terjadi kesalahan, coba lagi.');
                                    }
                                };
                                xhr.send('kelompok_id=' + kelompokId); // Kirim ID kelompok ke server
                            }
                        });
                    </script>

                    <div class="right">
                        <div class="title-edit">
                        <h1 id="nama-kelompok-text"><?php echo htmlspecialchars($kelompok['nama_kelompok']); ?></h1>
                            <input type="text" id="nama-kelompok-input" value="<?php echo htmlspecialchars($kelompok['nama_kelompok']); ?>" style="display: none;" />
                        
                            <?php if (is_null($statusInkubasi)): ?>
                                <?php if ($isMentor): ?>
                                    <button type="button" class="btn btn-secondary mt-3" data-bs-toggle="modal" data-bs-target="#recommendationModal">
                                        Program Inkubasi
                                    </button>
                                <?php else: ?>
                                    <p 
                                        class="alert alert-secondary fw-bold text-center m-0 p-2" 
                                        role="alert" 
                                        data-bs-toggle="popover" 
                                        title="Status Tidak Ada" 
                                        data-bs-content="Status Program Kelompok Bisnis ini belum ditentukan."
                                        style="cursor: pointer;">
                                            Tidak Ada
                                    </p>
                                <?php endif; ?>
                            <?php elseif ($statusInkubasi === 'masuk'): ?>
                                <p 
                                    class="alert alert-info fw-bold text-center m-0 p-2" 
                                    role="alert" 
                                    data-bs-toggle="popover" 
                                    title="Status Program Inkubasi" 
                                    data-bs-content="Kelompok Bisnis ini Terdaftar dalam Program Inkubasi."
                                    style="cursor: pointer;">
                                        Program Inkubasi
                                </p>
                            <?php elseif ($statusInkubasi === 'direkomendasikan'): ?>
                                <p 
                                    class="alert alert-success fw-bold text-center m-0 p-2" 
                                    role="alert" 
                                    data-bs-toggle="popover" 
                                    title="Status Direkomendasikan" 
                                    data-bs-content="Kelompok Bisnis ini direkomendasikan oleh Mentor bisnisnya untuk masuk ke dalam Program Inkubasi."
                                    style="cursor: pointer;">
                                        Direkomendasikan
                                </p>
                            <?php elseif ($statusInkubasi === 'disetujui'): ?>
                                <p 
                                    class="alert alert-success fw-bold text-center m-0 p-2" 
                                    role="alert" 
                                    data-bs-toggle="popover" 
                                    title="Status Direkomendasi dan Bersedia" 
                                    data-bs-content="Kelompok bisnis ini Direkomendasi oleh Mentor Bisnisnya dan Bersedia untuk masuk ke Program Inkubasi, Perlu Persetujuan Admin PIKK untuk Kelompok Bisnis ini masuk ke dalam Program Inkubasi. "
                                    style="cursor: pointer;">
                                        Direkomendasi dan Bersedia
                                </p>
                            <?php elseif ($statusInkubasi === 'ditolak'): ?>
                                <p 
                                    class="alert alert-danger fw-bold text-center m-0 p-2" 
                                    role="alert" 
                                    data-bs-toggle="popover" 
                                    title="Status Ditolak Kelompok Bisnis" 
                                    data-bs-content="Kelompok Bisnis ini Menolak untuk Masuk ke dalam Program Inkubasi."
                                    style="cursor: pointer;">
                                        Ditolak Kelompok Bisnis
                                </p>
                            <?php elseif ($statusInkubasi === 'tidak masuk'): ?>
                                <p 
                                    class="alert alert-danger fw-bold text-center m-0 p-2" 
                                    role="alert" 
                                    data-bs-toggle="popover" 
                                    title="Status Ditolak Admin PIKK" 
                                    data-bs-content="Kelompok Bisnis ini Ditolak oleh Admin PIKK untuk Masuk Kedalam Program Inkubasi."
                                    style="cursor: pointer;">
                                        Ditolak Admin PIKK
                                </p>
                            <?php endif; ?>

                            <!-- Modal Rekomendasi -->
                            <div class="modal fade" id="recommendationModal" tabindex="-1" aria-labelledby="recommendationModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="recommendationModalLabel">Rekomendasi Program Inkubasi</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Apakah Anda ingin merekomendasikan kelompok ini untuk masuk ke program inkubasi bisnis?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary btn-cancel" data-bs-dismiss="modal">Batal</button>
                                            <button type="button" class="btn btn-secondary btn-submit" id="submitRecommendation" data-bs-dismiss="modal">Iya</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <script>
                                document.getElementById('submitRecommendation').addEventListener('click', function() {
                                    // Ambil ID kelompok dari URL
                                    const urlParams = new URLSearchParams(window.location.search);
                                    const kelompokId = <?php echo $id_kelompok; ?>

                                    if (kelompokId) {
                                        // Kirim data ke server menggunakan AJAX
                                        fetch('update_kelompok_status', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/x-www-form-urlencoded',
                                            },
                                            body: `id_kelompok=${kelompokId}&status_inkubasi=direkomendasikan`
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                // Tampilkan pesan toast
                                                let toastMessage = document.createElement('div');
                                                toastMessage.classList.add('toast', 'show');
                                                toastMessage.classList.add('toast-success');
                                                toastMessage.textContent = 'Kelompok bisnis berhasil direkomendasikan ke dalam program inkubasi bisnis.';
                                                document.body.appendChild(toastMessage);
                                                setTimeout(() => toastMessage.classList.remove('show'), 3000); // Hilangkan toast setelah 3 detik
                                            } else {
                                                alert('Gagal mengupdate status');
                                            }
                                        })
                                        .catch(error => console.error('Error:', error));
                                    } else {
                                        alert('ID kelompok tidak ditemukan');
                                    }
                                });
                            </script>
                        </div>
                        
                        <div class="Nama_bisnis">
                            <p><strong>Nama Bisnis:</strong> <?php echo htmlspecialchars($kelompok['nama_bisnis'] ?? '--'); ?></p>
                        </div>
                        <div class="Ide_Bisnis">
                            <p><strong>Ide Bisnis:</strong> <?php echo htmlspecialchars($kelompok['ide_bisnis'] ?? '--'); ?></p>
                        </div>
                        <div class="category">
                            <p><strong>Kategori Bisnis:</strong> <?php echo htmlspecialchars($kelompok['kategori_bisnis'] ?? '--'); ?></p>
                        </div>
                        <div class="sdg">
                            <strong>Tujuan Sustainable Development Goals (SDGs):</strong>  
                            <ul>
                                <?php foreach ($sdg_labels as $label): ?>
                                    <li><?php echo htmlspecialchars($label); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="bottom">
                            <div class="members">
                                <p><strong>Ketua Kelompok:</strong> 
                                    <?php echo htmlspecialchars($ketua['nama']) . ' (' . htmlspecialchars($kelompok['npm_ketua']) . ')'; ?>
                                </p>

                                <p><strong>Anggota Kelompok:</strong></p>
                                <?php while ($anggota = $anggotaResult->fetch_assoc()) { ?>
                                    <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($anggota['nama']) . " (" . htmlspecialchars($anggota['npm']) . ")"; ?></p>
                                <?php } ?>
                            </div>
                            <!-- Mentor Bisnis Section -->
                            <div class="tutor">
                                <div class="d-flex align-items-center mentor">
                                    <strong class="me-2">Mentor Bisnis:</strong>
                                    <?php if ($mentorAda) { ?>
                                        <a class="text-decoration-none" data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                                            <div class="card d-inline-block" 
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            title="Lihat Detail Mentor Bisnis">
                                                <div class="card-body p-0">
                                                    <p class="card-text m-0 text-center">
                                                        <?php echo htmlspecialchars($mentorData['nama_mentor']); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </a>
                                    <?php } else { ?>
                                        <!-- Jika belum ada mentor, tampilkan teks info -->
                                        <span class="text-muted alert alert-warning text-center" role="alert">Belum ada mentor bisnis</span>
                                    <?php } ?>
                                </div>
                                
                                <!-- Collapsible mentor details (hanya aktif jika mentor ada) -->
                                <?php if ($mentorAda) { ?>
                                    <div class="collapse" id="collapseExample">
                                        <div class="card-mentor p-3">
                                            <?php
                                                $profile_img = !empty($mentorData['foto_profile']) ? $mentorData['foto_profile'] : '\Entree\assets\img\default-profile-mentor.png';
                                            ?>
                                            <img alt="Profile picture of the mentor" class="card-img-top mx-auto d-block mt-3" height="50" width="50" id="profile-photo"
                                                src="<?= htmlspecialchars($profile_img) ?>" 
                                                onerror="this.src='<?= htmlspecialchars($profile_img) ?>'"/>
                                            <h2 class="card-mentor-title text-center"><?php echo htmlspecialchars($mentorData['nama_mentor']); ?></h2>
                                            <div class="card-mentor-body">
                                                <p class="card-mentor-text"><strong>Peran:</strong> <?php echo htmlspecialchars($mentorData['role']); ?></p>
                                                <p class="card-mentor-text"><strong>Keahlian:</strong> <?php echo htmlspecialchars($mentorData['keahlian']); ?></p>
                                                <p class="card-mentor-text"><strong>Fakultas:</strong> <?php echo htmlspecialchars($mentorData['fakultas']); ?></p>
                                                <p class="card-mentor-text"><strong>Prodi:</strong> <?php echo htmlspecialchars($mentorData['prodi']); ?></p>
                                                <p class="card-mentor-text"><strong>Nomor Telepon:</strong> <?php echo htmlspecialchars($mentorData['contact']); ?></p>
                                                <p class="card-mentor-text"><strong>Alamat Email:</strong> <?php echo htmlspecialchars($mentorData['email']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="cards-container">
                            <!-- Card 1 -->
                            <div class="card" onclick="window.location.href='proposal_bisnis?id_kelompok=<?php echo $id_kelompok; ?>'" 
                            data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Lihat Proposal Bisnis Kelompok Disini"
                            >
                                <h5>Proposal Bisnis</h5>
                            </div>
                            <!-- Card 2 -->
                            <div class="card" onclick="window.location.href='laporan_bisnis?id_kelompok=<?php echo $id_kelompok; ?>'"
                            title="Laporan Kemajuan Bisnis Kelompok Disini"
                            data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Lihat Laporan Kemajuan Bisnis Kelompok Disini"
                            >
                                <h5>Laporan Kemajuan Bisnis</h5>
                            </div>
                        </div>

                        <div class="accordion accordion-flush mt-4" id="accordionFlushExample">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                                    Lihat Riwayat Bimbingan
                                </button>
                                </h2>
                                <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                                <div class="accordion-body">
                                    <div class="container">
                                        <?php
                                            $sql = "SELECT * FROM jadwal WHERE id_klmpk = ? ORDER BY tanggal, waktu";
                                            $stmt = $conn->prepare($sql);
                                            $stmt->bind_param("i", $id_kelompok); // Bind parameter id_kelompok
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                        ?>

                                    <div class="table-container">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Nama Kelompok</th>
                                                    <th>Nama Kegiatan</th>
                                                    <th>Tanggal</th>
                                                    <th>Status</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($result->num_rows > 0): ?>
                                                    <?php $no = 1; ?>
                                                    <?php while ($row = $result->fetch_assoc()): ?>
                                                        <tr>
                                                            <td><?php echo $no++; ?></td>
                                                            <td><?php echo htmlspecialchars($kelompok['nama_kelompok']); ?></td>
                                                            <td style="max-width: 200px;"><?php echo htmlspecialchars($row['nama_kegiatan']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['tanggal']); ?></td>
                                                            <td style="width: 150px;">
                                                                    <?php
                                                                    if ($row['status'] == 'disetujui') {
                                                                        echo '<p class="alert alert-success text-white fw-bold text-center m-0 p-2" style="background-color:#2ea56f" role="alert">Disetujui</p>';
                                                                    } elseif ($row['status'] == 'ditolak') {
                                                                        echo '<p class="alert alert-danger text-white fw-bold text-center m-0 p-2" style="background-color:#dc3545" role="alert">Ditolak</p>';
                                                                    } elseif ($row['status'] == 'selesai') {
                                                                        echo '<p class="alert alert-info text-white fw-bold text-center m-0 p-2" style="background-color:#007bff" role="alert">Selesai</p>';
                                                                    } elseif ($row['status'] == 'alternatif') {
                                                                        echo '<p class="alert alert-warning text-white fw-bold text-center m-0 p-2" style="background-color:#ffc107" role="alert">Alternatif</p>';
                                                                    } else {
                                                                        echo '<p class="alert alert-warning text-white fw-bold text-center m-0 p-2" style="background-color:orange" role="alert">Menunggu</p>';
                                                                    }
                                                                ?>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <a href="detail_jadwal?id=<?php echo $row['id']; ?>&id_kelompok=<?php echo $id_kelompok; ?>" class="btn btn-info btn-sm">
                                                                    <i class="fa-solid fa-eye" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Lihat Detail Jadwal"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="8">
                                                            <div class="alert alert-warning text-center m-0" role="alert">
                                                                Tidak ada jadwal tersedia.
                                                            </div>
                                                        </td>                                                    
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                        <?php if ($totalSelesai > 0): ?>
                                            <div class="alert alert-info mt-3" role="alert">
                                                <strong>Total Jadwal dengan Status "Selesai":</strong> <?php echo $totalSelesai; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                </div>
                                </div>
                            </div>
    
                        </div>

                    </div>
                </div>
            <?php } else { ?>
                <div class="alert alert-danger" role="alert">Data kelompok tidak ditemukan.</div>
            <?php } ?>


                <!-- Tombol Back -->
                <div class="mt-3">
                    <a href="daftar_kelompok_bisnis" class="btn btn-secondary mt-3">Kembali ke Daftar Kelompok Bisnis</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        });
    </script>

</body>

</html>

<?php
// Menutup koneksi database
$conn->close();
?>