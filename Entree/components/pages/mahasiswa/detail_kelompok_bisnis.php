<?php
session_start();
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

// Ambil kelompok_id dari URL
$kelompok_id = $_GET['id'] ?? null;

// Mendapatkan NPM mahasiswa dari session
$npm_mahasiswa = $_SESSION['npm'] ?? null; // Pastikan variabel tidak null

// Ambil ID kelompok dari session pengguna
$user_id = $_SESSION['user_id'];
$query = "SELECT id_kelompok FROM mahasiswa WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$user_kelompok_id = $row['id_kelompok']; // ID kelompok pengguna dari database

// Ambil ID kelompok dari parameter URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: /Entree/mahasiswa/dashboard'); // Redirect jika ID tidak ada di URL
    exit;
}
$requested_kelompok_id = intval($_GET['id']);

// Periksa apakah ID kelompok dari URL cocok dengan ID kelompok pengguna
if ($requested_kelompok_id !== $user_kelompok_id) {
    header('Location: /Entree/mahasiswa/dashboard'); // Redirect ke dashboard jika ID tidak cocok
    exit;
}

// Jika ID cocok, lanjutkan untuk memproses data kelompok
$query = "SELECT * FROM kelompok_bisnis WHERE id_kelompok = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $requested_kelompok_id);
$stmt->execute();
$kelompok_result = $stmt->get_result();

if ($kelompok_result->num_rows === 0) {
    header('Location: /Entree/mahasiswa/dashboard'); // Redirect jika data kelompok tidak ditemukan
    exit;
}

// Cek apakah mahasiswa adalah ketua atau anggota kelompok
$cekKelompokQuery = "
    SELECT kb.*, ak.id_kelompok AS anggota_id
    FROM kelompok_bisnis kb
    LEFT JOIN anggota_kelompok ak ON kb.id_kelompok = ak.id_kelompok
    WHERE kb.npm_ketua = '$npm_mahasiswa' OR ak.npm_anggota = '$npm_mahasiswa' LIMIT 1";
$cekKelompokResult = mysqli_query($conn, $cekKelompokQuery);
$kelompokTerdaftar = mysqli_fetch_assoc($cekKelompokResult) ?: []; // Defaultkan ke array kosong jika null

// Ambil detail mentor berdasarkan nama mentor dari kelompok bisnis
$mentorData = [];
if (!empty($kelompokTerdaftar['id_mentor'])) {
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
    WHERE m.id = '" . $kelompokTerdaftar['id_mentor'] . "' 
    LIMIT 1";
    
    $mentorResult = mysqli_query($conn, $mentorQuery);
    $mentorData = mysqli_fetch_assoc($mentorResult); 
}

$mentorAda = !empty($kelompokTerdaftar['id_mentor']);

// Cek status_inkubasi dari tabel kelompok_bisnis
$sql = "SELECT status_inkubasi FROM kelompok_bisnis WHERE id_kelompok = $kelompok_id";
$result = $conn->query($sql);

$status_inkubasi = null;
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $status_inkubasi = $row['status_inkubasi'];
}

// Tentukan apakah tombol terkunci
$is_locked = is_null($status_inkubasi);

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
$sdg_selected = explode(",", $kelompokTerdaftar['sdg'] ?? '');
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
</head>
<body>
    <div class="wrapper">
        <?php 
        $activePage = 'kelompok_bisnis'; 
        include 'sidebar_mahasiswa.php'; 
        ?>

        <div class="main p-3">
            <div class="main_header">
                <?php 
                    $pageTitle = "Detail Kelompok Bisnis"; // Judul halaman
                    include 'header_mahasiswa.php'; 
                ?>
            </div>

            <div class="main_wrapper">
                <?php if ($kelompokTerdaftar) { ?>
                    <div class="container">
                        <div class="left">
                            <!-- Logo Bisnis -->
                            <div class="d-flex flex-column align-items-center">
                                <?php
                                $logo_bisnis = !empty($kelompokTerdaftar['logo_bisnis']) ? $kelompokTerdaftar['logo_bisnis'] : '\Entree\assets\img\default-logo-bisnis.jpg';
                                $is_default_logo = empty($kelompokTerdaftar['logo_bisnis']);
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
                            <button class="btn btn-danger mt-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseWidthExample_2" aria-expanded="false" aria-controls="collapseWidthExample">
                                <i class="fas fa-trash-alt"></i> 
                            </button>
                            </p>
                            <div style="min-height: 120px;">
                            <div class="collapse collapse-horizontal" id="collapseWidthExample_2">
                                <div class="card card-body" style="width: 300px;">
                                    <!-- Tombol Hapus -->
                                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        Hapus Kelompok
                                    </button>

                                </div>
                            </div>
                            </div>

                        <!-- Modal Konfirmasi Hapus -->
                        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Penghapusan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Apakah Anda yakin ingin menghapus kelompok bisnis ini beserta semua data terkait (proposal, laporan, jadwal, anggota)?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="button" class="btn btn-danger" id="confirmDeleteButton">Hapus</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                           
                        </div>
                        <div class="right">
                            <!-- Tombol Edit hanya tampil jika mentor belum ditugaskan -->
                            <?php if (!$mentorAda) { ?>
                                <div class="title-edit">
                                    <h1 id="nama-kelompok-text"><?php echo htmlspecialchars($kelompokTerdaftar['nama_kelompok']); ?></h1>
                                    <input type="text" id="nama-kelompok-input" value="<?php echo htmlspecialchars($kelompokTerdaftar['nama_kelompok']); ?>" style="display: none;" />
                                    <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#editModal">
                                        <i class="fas fa-edit edit-btn" 
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            title="Edit Kelompok Bisnis">
                                        </i> 
                                    </button>
                                </div>
                                <?php } else { ?>
                                <!-- Jika sudah ada mentor, tombol edit tidak ditampilkan -->
                                <div class="title-edit">
                                    <h1 id="nama-kelompok-text"><?php echo htmlspecialchars($kelompokTerdaftar['nama_kelompok']); ?></h1>
                                    <!-- Tombol Program Inkubasi -->
                                    <?php if (is_null($status_inkubasi)): ?>
                                        <button type="button" 
                                                class="btn btn-secondary mt-3" 
                                                id="programInkubasiButton" 
                                                <?php if ($is_locked): ?>
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top" 
                                                    title="Tidak tersedia kecuali Kelompok Bisnis anda direkomendasikan oleh tutor anda"
                                                <?php endif; ?>>
                                            Program Inkubasi
                                        </button>
                                    <?php elseif ($status_inkubasi === 'direkomendasikan'): ?>
                                        <button type="button" 
                                                class="btn btn-secondary mt-3" 
                                                id="programInkubasiButton" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#recommendationModal">
                                            Program Inkubasi
                                        </button>
                                    <?php elseif ($status_inkubasi === 'masuk'): ?>
                                        <p 
                                            class="alert alert-info fw-bold text-center m-0 p-2" 
                                            role="alert" 
                                            data-bs-toggle="popover" 
                                            title="Status Program Inkubasi" 
                                            data-bs-content="Kelompok Bisnis ini Terdaftar dalam Program Inkubasi."
                                            style="cursor: pointer;">
                                                Program Inkubasi
                                        </p>
                                    <?php elseif ($status_inkubasi === 'disetujui'): ?>
                                        <p 
                                            class="alert alert-success fw-bold text-center m-0 p-2" 
                                            role="alert" 
                                            data-bs-toggle="popover" 
                                            title="Status Direkomendasi dan Bersedia"
                                            data-bs-content="Kelompok bisnis ini Direkomendasi oleh Mentor Bisnisnya dan Bersedia untuk masuk ke Program Inkubasi, Perlu Persetujuan Admin PIKK untuk Kelompok Bisnis ini masuk ke dalam Program Inkubasi. "
                                            style="cursor: pointer;">
                                                Direkomendasi dan Bersedia
                                        </p>
                                    <?php elseif ($status_inkubasi === 'ditolak'): ?>
                                        <p 
                                            class="alert alert-danger fw-bold text-center m-0 p-2" 
                                            role="alert" 
                                            data-bs-toggle="popover" 
                                            title="Status Ditolak Kelompok Bisnis" 
                                            data-bs-content="Kelompok Bisnis ini Menolak untuk Masuk ke dalam Program Inkubasi."
                                            style="cursor: pointer;">
                                                Ditolak Kelompok Bisnis
                                        </p>
                                    <?php elseif ($status_inkubasi === 'tidak masuk'): ?>
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

                                    <!-- Modal -->
                                    <div class="modal fade" id="recommendationModal" tabindex="-1" aria-labelledby="recommendationModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="recommendationModalLabel">Program Inkubasi</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form id="recommendationForm">
                                                    <div class="modal-body">
                                                        <p>Kelompok anda direkomendasikan untuk masuk ke dalam Program Inkubasi Bisnis.</p>
                                                        <p>Apakah kelompok anda menyetujui untuk masuk ke dalam Program?</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-danger btn-cancel">Tolak</button>
                                                        <button type="submit" class="btn btn-success btn-submit">Setujui</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <script>
                                        document.getElementById('recommendationForm').addEventListener('submit', function(event) {
                                            event.preventDefault(); // Prevent form submission

                                            const isApprove = event.submitter.classList.contains('btn-submit');
                                            const kelompokId = <?php echo $kelompok_id; ?>; // Assuming you have the id_kelompok available in PHP
                                            
                                            fetch('/Entree/components/pages/mahasiswa/update_status_inkubasi.php', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/x-www-form-urlencoded',
                                                },
                                                body: `id_kelompok=${kelompokId}&status_inkubasi=${isApprove ? 'disetujui' : 'ditolak'}`
                                            })
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data.success) {
                                                    // Update button status
                                                    updateButtonStatus(isApprove);
                                                    
                                                    // Close the modal
                                                    const modal = bootstrap.Modal.getInstance(document.getElementById('recommendationModal'));
                                                    modal.hide();

                                                    // Display the Toast message
                                                    const toastElement = new bootstrap.Toast(document.getElementById('toastMessage'));
                                                    toastElement.show();
        
                                                } else {
                                                    alert('Gagal memperbarui status inkubasi');
                                                }
                                            })
                                            .catch(error => console.error('Error:', error));
                                        });

                                        function updateButtonStatus(isApprove) {
                                            const programInkubasiButton = document.getElementById('programInkubasiButton');
                                            
                                            if (isApprove === null) {
                                                programInkubasiButton.classList.remove('btn-success', 'btn-danger');
                                                programInkubasiButton.classList.add('btn-secondary');
                                                programInkubasiButton.textContent = 'Program Inkubasi';
                                                programInkubasiButton.disabled = false;
                                            } else {
                                                programInkubasiButton.classList.remove('btn-secondary');
                                                programInkubasiButton.disabled = true;
                                                if (isApprove) {
                                                    programInkubasiButton.classList.add('btn-success');
                                                    programInkubasiButton.textContent = 'Disetujui Kelompok Bisnis';
                                                } else {
                                                    programInkubasiButton.classList.add('btn-danger');
                                                    programInkubasiButton.textContent = 'Ditolak Kelompok Bisnis';
                                                }
                                            }
                                        }

                                        // Memeriksa status inkubasi saat halaman dimuat
                                        window.onload = function() {
                                            // Ambil status inkubasi dari server
                                            fetch('/Entree/components/pages/mahasiswa/get_status_inkubasi.php') // Sesuaikan dengan endpoint yang mengambil status inkubasi dari database
                                                .then(response => response.json())
                                                .then(data => {
                                                    if (data.status === 'disetujui') {
                                                        updateButtonStatus(true);
                                                        localStorage.setItem('inkubasiStatus', 'disetujui');
                                                    } else if (data.status === 'ditolak') {
                                                        updateButtonStatus(false);
                                                        localStorage.setItem('inkubasiStatus', 'ditolak');
                                                    } else {
                                                        // Jika status belum disetujui atau ditolak, status tetap program inkubasi
                                                        updateButtonStatus(null);
                                                    }
                                                })
                                                .catch(error => console.error('Error fetching status:', error));
                                        };
                                    </script>

                                    <!-- JavaScript untuk Tooltip -->
                                    <script>
                                        document.addEventListener("DOMContentLoaded", function() {
                                            const isLocked = <?php echo $is_locked ? 'true' : 'false'; ?>;
                                            const button = document.getElementById("programInkubasiButton");

                                            if (isLocked) {
                                                // Inisialisasi tooltip
                                                const tooltip = new bootstrap.Tooltip(button, {
                                                    title: "Tidak tersedia kecuali? Kelompok Bisnis anda direkomendasikan oleh tutor anda",
                                                    placement: "top",
                                                    trigger: "hover"
                                                });

                                                // Cegah aksi klik pada tombol jika terkunci
                                                button.addEventListener("click", function(event) {
                                                    event.preventDefault();
                                                });
                                            }
                                        });
                                    </script>
                                    
                                </div>
                            <?php } ?>

                            <!-- Menambahkan Nama Bisnis -->
                                <div class="ide_bisnis">
                                    <p><strong>Nama Bisnis:</strong> <?php echo htmlspecialchars($kelompokTerdaftar['nama_bisnis'] ?? '--'); ?></p>
                                </div>
                                <div class="ide_bisnis">
                                    <p><strong>Ide Bisnis:</strong> <?php echo htmlspecialchars($kelompokTerdaftar['ide_bisnis'] ?? '--'); ?></p>
                                </div>
                                <div class="category">
                                    <p><strong>Kategori Bisnis:</strong> <?php echo htmlspecialchars($kelompokTerdaftar['kategori_bisnis'] ?? '--'); ?></p>
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
                                        <?php
                                        // Mendapatkan nama ketua kelompok berdasarkan npm ketua
                                        $ketuaQuery = "SELECT nama FROM mahasiswa WHERE npm = '" . ($kelompokTerdaftar['npm_ketua'] ?? '') . "' LIMIT 1";
                                        $ketuaResult = mysqli_query($conn, $ketuaQuery);
                                        $ketuaData = mysqli_fetch_assoc($ketuaResult) ?: [];
                                        echo htmlspecialchars($ketuaData['nama'] ?? 'Data tidak tersedia');
                                        ?>
                                    </p>

                                    <p><strong>Anggota Kelompok:</strong></p>
                                    <?php
                                    // Menampilkan anggota kelompok
                                    $anggotaQuery = "
                                        SELECT ak.npm_anggota, m.nama
                                        FROM anggota_kelompok ak
                                        JOIN mahasiswa m ON ak.npm_anggota = m.npm
                                        WHERE ak.id_kelompok = '" . ($kelompokTerdaftar['id_kelompok'] ?? '') . "'";
                                    $anggotaResult = mysqli_query($conn, $anggotaQuery);
                                    if (mysqli_num_rows($anggotaResult) > 0) {
                                        while ($anggota = mysqli_fetch_assoc($anggotaResult)) {
                                            echo "<p><i class='fas fa-user'></i> " . htmlspecialchars($anggota['nama'] ?? 'Nama tidak tersedia') . " (" . htmlspecialchars($anggota['npm_anggota'] ?? 'NPM tidak tersedia') . ")</p>";
                                        }
                                    } else {
                                        echo "<p>Belum ada anggota kelompok.</p>";
                                    }
                                    ?>
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
                                <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form id="editForm" enctype="multipart/form-data">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editModalLabel">Edit Kelompok Bisnis</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="namaKelompok" class="form-label">Nama Kelompok</label>
                                                        <input type="text" class="form-control" id="namaKelompok" name="nama_kelompok" value="<?php echo htmlspecialchars($kelompokTerdaftar['nama_kelompok']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="namaBisnis" class="form-label">Nama Bisnis</label>
                                                        <input type="text" class="form-control" id="namaBisnis" name="nama_bisnis" value="<?php echo htmlspecialchars($kelompokTerdaftar['nama_bisnis'] ?? ''); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="logoBisnis" class="form-label">Logo Bisnis</label>
                                                        <input type="file" class="form-control" id="logoBisnis" name="logo_bisnis" accept=".png, .jpg, .jpeg">
                                                    </div>
                                                    <p class="text-muted alert alert-info text-center" role="alert">Fitur Edit Kelompok Bisnis hanya tersedia jika Kelompok Bisnis belum mempunyai Mentor Bisnis</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-success">Simpan</button>
                                                </div>
                                            </form>
                                        </div>                                              
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="alert alert-warning">Kelompok Bisnis belum terdaftar.</div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="toastMessage" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">Status Program Inkubasi</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                Status Program Inkubasi berhasil diperbarui.
            </div>
        </div>
    </div>

    <script>
        // Handle form submission
        document.getElementById('editForm').addEventListener('submit', function (event) {
            event.preventDefault();

            const formData = new FormData(this); // Gunakan FormData untuk menangani file upload

            // Menambahkan id_kelompok ke formData
            formData.append('id_kelompok', "<?php echo htmlspecialchars($kelompokTerdaftar['id_kelompok']); ?>");

            // Kirim data ke server
            fetch('/Entree/components/pages/mahasiswa/update_kelompok.php', {
                method: 'POST',
                body: formData // Mengirimkan formData, yang sudah berisi data form termasuk file
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Data berhasil diperbarui.');
                    location.reload(); // Muat ulang halaman setelah berhasil
                } else {
                    alert('Terjadi kesalahan saat memperbarui data.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan.');
            });
        });

        document.getElementById("confirmDeleteButton").addEventListener("click", function() {
            var kelompokId = <?php echo $kelompokTerdaftar['id_kelompok']; ?>;
            
            // Kirim permintaan POST untuk menghapus kelompok dan data terkait
            fetch('delete_kelompok', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id_kelompok: kelompokId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Kelompok bisnis dan data terkait berhasil dihapus.');
                    window.location.href = 'kelompok_bisnis'; // Ganti dengan halaman yang sesuai
                } else {
                    alert('Terjadi kesalahan saat menghapus kelompok.');
                }
            })
            .catch(error => {
                alert('Terjadi kesalahan: ' + error);
            });
        });
    </script>
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