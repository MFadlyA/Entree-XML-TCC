<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /Entree/login');
    exit;
}

// Cek apakah role pengguna sesuai
if ($_SESSION['role'] !== 'Mahasiswa') {
    header('Location: /Entree/login');
    exit;
}

// Koneksi ke database
include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';

// Ambil ID pengguna dari session
$user_id = $_SESSION['user_id'];  

// Ambil ID kelompok dari database
$query = "SELECT id_kelompok FROM mahasiswa WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$_SESSION['id_kelompok'] = $row['id_kelompok'];  // Menyimpan ID Kelompok di session

// Cek apakah ada proposal bisnis yang disetujui untuk ID kelompok
$query = "SELECT COUNT(*) AS count FROM proposal_bisnis WHERE kelompok_id = ? AND status = 'Disetujui'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['id_kelompok']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Jika tidak ada proposal yang disetujui, redirect ke halaman error atau notifikasi
if ($row['count'] == 0) {
    header('Location: /Entree/mahasiswa/dashboard'); // Ganti dengan halaman notifikasi yang sesuai
    exit;
}

// Ambil laporan berdasarkan ID kelompok
$query = "SELECT * FROM laporan_bisnis WHERE id_kelompok = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['id_kelompok']);
$stmt->execute();
$laporan_result = $stmt->get_result();
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="/Entree/assets/css/laporan_bisnis.css">
</head>
    
<body>
<div class="wrapper">
        <?php 
        $activePage = 'laporan_bisnis_mahasiswa'; // Halaman ini aktif
        include 'sidebar_mahasiswa.php'; 
        ?>

        <div class="main p-3">
            <div class="main_header">
                <?php 
                    $pageTitle = "Laporan Kemajuan Bisnis"; // Judul halaman
                    include 'header_mahasiswa.php'; 
                ?>
            </div>

            <div class="main_wrapper">

                <!-- Tombol untuk membuka modal -->
                <button type="button" class="btn-hijau" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    Tambah Laporan
                </button>

                <!-- Modal -->
                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg"> <!-- Menggunakan modal-xl untuk modal yang lebih lebar -->
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">Laporan Kemajuan Bisnis</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Form -->
                                <form method="POST" action="proses_laporan" enctype="multipart/form-data" autocomplete="off">
                                    <!-- Laporan Kemajuan Pengembangan Usaha -->
                                    <div class="form-group">
                                        <label for="judul_laporan">Judul Laporan:<span style="color:red;">*</span></label>
                                        <input type="text" id="judul_laporan" name="judul_laporan" required placeholder="Masukkan Judul Laporan">
                                    </div>

                                    <!-- Jenis Laporan -->
                                    <div class="form-group">
                                        <label for="jenis_laporan">Jenis Laporan:<span style="color:red;">*</span></label>
                                        <select id="jenis_laporan" name="jenis_laporan" required>
                                            <option value="" style="color:darkgrey;" disabled selected>
                                                ~ Pilih Jenis Laporan ~
                                            </option>
                                            <option value="Laporan Kemajuan">Laporan Kemajuan</option>
                                            <option value="Laporan Akhir">Laporan Akhir</option>
                                        </select>
                                    </div>

                                    <div class="laporan-container">
                                        <!-- Laporan Penjualan Usaha -->
                                        <div class="form-group">
                                            <label for="laporan_penjualan">Laporan Penjualan:</label>
                                            <textarea id="laporan_penjualan" name="laporan_penjualan" placeholder="Masukkan Laporan Penjualan"></textarea>
                                        </div>

                                        <!-- Laporan Pemasaran Usaha -->
                                        <div class="form-group">
                                            <label for="laporan_pemasaran">Laporan Pemasaran:</label>
                                            <textarea id="laporan_pemasaran" name="laporan_pemasaran" placeholder="Masukkan Laporan Pemasaran"></textarea>
                                        </div>

                                        <!-- Laporan Produksi Usaha -->
                                        <div class="form-group">
                                            <label for="laporan_produksi">Laporan Produksi:</label>
                                            <textarea id="laporan_produksi" name="laporan_produksi" placeholder="Masukkan Laporan Produksi"></textarea>
                                        </div>

                                        <!-- Laporan SDM Usaha -->
                                        <div class="form-group">
                                            <label for="laporan_sdm">Laporan SDM:</label>
                                            <textarea id="laporan_sdm" name="laporan_sdm" placeholder="Masukkan Laporan SDM"></textarea>
                                        </div>

                                        <!-- Laporan Keuangan -->
                                        <div class="form-group">
                                            <label for="laporan_keuangan">Laporan Keuangan:</label>
                                            <textarea id="laporan_keuangan" name="laporan_keuangan" placeholder="Masukkan Laporan Keuangan"></textarea>
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="lampiran_laporan" class="form-label">
                                            Lampiran 
                                            <small class="text-muted">(Anda dapat mengunggah beberapa file sekaligus dalam format PDF.)</small>
                                        </label>
                                        <div class="input-group">
                                            <input 
                                                type="file" 
                                                class="form-control" 
                                                id="lampiran_laporan" 
                                                name="lampiran_laporan[]" 
                                                accept=".pdf" 
                                                multiple 
                                            />
                                        </div>
                                    </div>

                                    <h3 id="fileHeading" style="display: none;">File yang Dipilih:</h3>
                                    <ul id="fileList"></ul>  
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        <button type="submit" class="btn btn-success" name="kirim">Unggah Laporan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>


                    <!-- Modal Edit-->
                    <div class="modal fade" id="exampleModal_Edit" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg"> <!-- Menggunakan modal-xl untuk modal yang lebih lebar -->
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">Edit Laporan Kemajuan Bisnis</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- Form -->
                                    <form method="POST" action="edit_laporan" enctype="multipart/form-data" autocomplete="off">
                                        <!-- Laporan Kemajuan Pengembangan Usaha -->
                                        <input type="hidden" id="id_laporan" name="id_laporan">

                                        <div class="form-group">
                                            <label for="judul_laporan">Judul Laporan:<span style="color:red;">*</span></label>
                                            <input type="text" id="judul_laporan" name="judul_laporan" required>
                                        </div>

                                        <!-- Jenis Laporan -->
                                        <div class="form-group">
                                            <label for="jenis_laporan">Jenis Laporan:<span style="color:red;">*</span></label>
                                            <select id="jenis_laporan" name="jenis_laporan" required>
                                                <option value="" style="color:darkgrey;" disabled selected>
                                                    ~ Pilih Jenis Laporan ~
                                                </option>
                                                <option value="Laporan Kemajuan">Laporan Kemajuan</option>
                                                <option value="Laporan Akhir">Laporan Akhir</option>
                                            </select>
                                        </div>

                                        <div class="laporan-container">
                                            <!-- Laporan Penjualan Usaha -->
                                            <div class="form-group">
                                                <label for="laporan_penjualan">Laporan Penjualan:</label>
                                                <textarea id="laporan_penjualan" name="laporan_penjualan"></textarea>
                                            </div>

                                            <!-- Laporan Pemasaran Usaha -->
                                            <div class="form-group">
                                                <label for="laporan_pemasaran">Laporan Pemasaran:</label>
                                                <textarea id="laporan_pemasaran" name="laporan_pemasaran"></textarea>
                                            </div>

                                            <!-- Laporan Produksi Usaha -->
                                            <div class="form-group">
                                                <label for="laporan_produksi">Laporan Produksi:</label>
                                                <textarea id="laporan_produksi" name="laporan_produksi"></textarea>
                                            </div>

                                            <!-- Laporan SDM Usaha -->
                                            <div class="form-group">
                                                <label for="laporan_sdm">Laporan SDM:</label>
                                                <textarea id="laporan_sdm" name="laporan_sdm"></textarea>
                                            </div>

                                            <!-- Laporan Keuangan -->
                                            <div class="form-group">
                                                <label for="laporan_keuangan">Laporan Keuangan:</label>
                                                <textarea id="laporan_keuangan" name="laporan_keuangan"></textarea>
                                            </div>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="lampiran_laporan" class="form-label">
                                                Lampiran 
                                                <small class="text-muted">(Anda dapat mengunggah beberapa file sekaligus dalam format PDF.)</small>
                                            </label>
                                            <div class="input-group">
                                                <input 
                                                    type="file" 
                                                    class="form-control" 
                                                    id="lampiran_laporan" 
                                                    name="lampiran_laporan[]" 
                                                    accept=".pdf" 
                                                    multiple 
                                                />
                                            </div>
                                        </div>

                                        <h3 id="fileHeading" style="display: none;">File yang Dipilih:</h3>
                                        <ul id="fileList"></ul>  
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            <button type="submit" class="btn btn-success" name="kirim">Unggah Laporan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                <!-- Menampilkan laporan -->
                <?php
                if ($laporan_result->num_rows > 0) {
                    while ($laporan = $laporan_result->fetch_assoc()) {
                        $id = $laporan['id'];
                        ?>
                        <div class="card">
                            <div class="card-header">
                                <h2><?php echo htmlspecialchars($laporan['judul_laporan']); ?></h2>
                                <span data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Edit Laporan Kemajuan Bisnis">
                                    <i class="fas fa-edit edit-icon" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#exampleModal_Edit"
                                        data-id="<?php echo $laporan['id']; ?>"
                                        data-judul="<?php echo htmlspecialchars($laporan['judul_laporan']); ?>"
                                        data-jenis="<?php echo htmlspecialchars($laporan['jenis_laporan']); ?>"
                                        data-penjualan="<?php echo htmlspecialchars($laporan['laporan_penjualan']); ?>"
                                        data-pemasaran="<?php echo htmlspecialchars($laporan['laporan_pemasaran']); ?>"
                                        data-produksi="<?php echo htmlspecialchars($laporan['laporan_produksi']); ?>"
                                        data-sdm="<?php echo htmlspecialchars($laporan['laporan_sdm']); ?>"
                                        data-keuangan="<?php echo htmlspecialchars($laporan['laporan_keuangan']); ?>"
                                        data-pdf="<?php echo htmlspecialchars($laporan['laporan_pdf']); ?>">
                                    </i>
                                </span>
                            </div>
                            <a href="detail_laporan?id=<?php echo $id; ?>">
                            <div class="card-body">
                                <i class="fa-solid fa-eye detail-icon"  data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Lihat Detail Laporan Kemajuan Bisnis"></i>
                            </div>
                            </a>
                            <div class="card-footer">
                                <a href="detail_laporan?id=<?php echo $id; ?>">Lihat Umpan Balik</a>
                                <i class="fa-solid fa-trash-can delete-icon" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Hapus Laporan Kemajuan Bisnis" onclick="confirmDelete(<?php echo $laporan['id']; ?>);"></i>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>  

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const editIcons = document.querySelectorAll('.edit-icon');
            const modal = document.querySelector('#exampleModal_Edit');

            editIcons.forEach(icon => {
                icon.addEventListener('click', () => {
                    // Ambil data dari atribut
                    const id = icon.getAttribute('data-id');
                    const judul = icon.getAttribute('data-judul');
                    const jenis = icon.getAttribute('data-jenis');
                    const penjualan = icon.getAttribute('data-penjualan');
                    const pemasaran = icon.getAttribute('data-pemasaran');
                    const produksi = icon.getAttribute('data-produksi');
                    const sdm = icon.getAttribute('data-sdm');
                    const keuangan = icon.getAttribute('data-keuangan');
                    const pdf = icon.getAttribute('data-pdf');

                    // Isi modal dengan data
                    modal.querySelector('#id_laporan').value = id;
                    modal.querySelector('#judul_laporan').value = judul;
                    modal.querySelector('#jenis_laporan').value = jenis;
                    modal.querySelector('#laporan_penjualan').value = penjualan;
                    modal.querySelector('#laporan_pemasaran').value = pemasaran;
                    modal.querySelector('#laporan_produksi').value = produksi;
                    modal.querySelector('#laporan_sdm').value = sdm;
                    modal.querySelector('#laporan_keuangan').value = keuangan;
                    modal.querySelector('#lampiran_laporan').value = pdf;
                });
            });
        });

        function confirmDelete(laporanID) {
            // Menampilkan dialog konfirmasi sebelum menghapus
            const confirmation = confirm("Apakah Anda yakin ingin menghapus laporan ini?");
            if (confirmation) {
                // Jika pengguna mengkonfirmasi, redirect ke file PHP untuk menghapus
                window.location.href = 'hapus_laporan?id=' + laporanID;
            }
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inisialisasi tooltip secara manual
            const editIcon = document.querySelector('.edit-icon');
            const deleteIcon = document.querySelector('.delete-icon');
            new bootstrap.Tooltip(editIcon);
            new bootstrap.Tooltip(deleteIcon);
        });
    </script>

</body>
</html>