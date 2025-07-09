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

$stmt = $conn->prepare("SELECT proposal_bisnis.id, proposal_bisnis.judul_proposal, proposal_bisnis.status 
                        FROM proposal_bisnis 
                        JOIN kelompok_bisnis 
                        ON proposal_bisnis.kelompok_id = kelompok_bisnis.id_kelompok 
                        WHERE proposal_bisnis.kelompok_id = ?");
$stmt->bind_param("i", $_SESSION['id_kelompok']); // Sesuaikan dengan session id_kelompok Anda
$stmt->execute();
$result = $stmt->get_result();
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
        $activePage = 'proposal_bisnis_mahasiswa'; // Halaman ini aktif
        include 'sidebar_mahasiswa.php'; 
        ?>

        <div class="main p-3">
            <div class="main_header">
                <?php 
                    $pageTitle = "Proposal Bisnis"; // Judul halaman
                    include 'header_mahasiswa.php'; 
                ?>
            </div>

            <div class="main_wrapper">

                 <!-- Tombol untuk membuka modal -->
                 <button type="button" class="btn-hijau mb-2" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    Tambah Proposal Bisnis
                </button>

                <!-- Modal -->
                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg"> <!-- Menggunakan modal-xl untuk modal yang lebih lebar -->
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">Tambah Proposal Bisnis</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Form -->
                                <form method="POST" action="proses_proposal" enctype="multipart/form-data" autocomplete="off">
                                    <!-- Judul Proposal Bisnis -->
                                    <div class="form-group">
                                        <label for="judul_proposal">Judul Proposal Bisnis:<span style="color:red;">*</span></label>
                                        <input type="text" id="judul_proposal" name="judul_proposal" placeholder="Masukkan Judul Proposal Bisnis" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="ide_bisnis">Ide Bisnis:<span style="color:red;">*</span></label>
                                        <input type="text" id="ide_bisnis" name="ide_bisnis" placeholder="Masukkan Ide Bisnis" required>
                                    </div>

                                    <!-- Dropdown Tahapan Bisnis -->
                                    <div class="form-group">
                                        <label for="tahapan_bisnis">Tahapan Bisnis:<span style="color:red;">*</span></label>
                                        <select id="tahapan_bisnis" name="tahapan_bisnis" required>
                                            <option value="" style="color:darkgrey;" disabled selected>
                                                ~ Pilih Tahapan Bisnis ~
                                            </option>
                                            <option value="Tahapan Awal">Tahapan Awal</option>
                                            <option value="Tahapan Bertumbuh">Tahapan Bertumbuh</option>
                                        </select>
                                    </div>


                                    <!-- SDG Bisnis -->
                                    <div class="form-group">
                                        <label for="sdg">Tujuan SDGs:<span style="color:red;">*</span></label>
                                        <select id="sdg" name="sdg[]" required multiple>
                                            <option value="mengakhiri_kemiskinan">1. Mengakhiri Kemiskinan</option>
                                            <option value="mengakhiri_kelaparan">2. Mengakhiri Kelaparan</option>
                                            <option value="kesehatan_kesejahteraan">3. Kesehatan dan Kesejahteraan</option>
                                            <option value="pendidikan_berkualitas">4. Pendidikan Berkualitas</option>
                                            <option value="kesetaraan_gender">5. Kesetaraan Gender</option>
                                            <option value="air_bersih_sanitasi">6. Air Bersih dan Sanitasi</option>
                                            <option value="energi_bersih_terjangkau">7. Energi Bersih dan Terjangkau</option>
                                            <option value="pekerjaan_pertumbuhan_ekonomi">8. Pekerjaan Layak dan Pertumbuhan Ekonomi</option>
                                            <option value="industri_inovasi_infrastruktur">9. Industri, Inovasi, dan Infrastruktur</option>
                                            <option value="mengurangi_ketimpangan">10. Mengurangi Ketimpangan</option>
                                            <option value="kota_komunitas_berkelanjutan">11. Kota dan Komunitas Berkelanjutan</option>
                                            <option value="konsumsi_produksi_bertanggung_jawab">12. Konsumsi dan Produksi yang Bertanggung Jawab</option>
                                            <option value="penanganan_perubahan_iklim">13. Penanganan Perubahan Iklim</option>
                                            <option value="ekosistem_lautan">14. Ekosistem Lautan</option>
                                            <option value="ekosistem_daratan">15. Ekosistem Daratan</option>
                                            <option value="perdamaian_keadilan_institusi_kuat">16. Perdamaian, Keadilan, dan Kelembagaan yang Kuat</option>
                                            <option value="kemitraan_tujuan">17. Kemitraan untuk Mencapai Tujuan</option>
                                        </select>
                                    </div>


                                    <!-- Kategori Bisnis -->
                                    <div class="form-group">
                                    <label for="kategori">Kategori Bisnis:<span style="color:red;">*</span></label>
                                    <select id="kategori" name="kategori" class="form-control" required onchange="toggleOtherCategoryInput()">
                                        <option value="" style="color:darkgrey;" disabled selected>~ Pilih Kategori Bisnis Anda ~</option>
                                        <option value="Bisnis Jasa">Bisnis Jasa</option>
                                        <option value="Bisnis Manufaktur">Bisnis Manufaktur</option>
                                        <option value="Bisnis Dagang (Perdagangan)">Bisnis Dagang (Perdagangan)</option>
                                        <option value="Bisnis Agrikultur dan Perkebunan">Bisnis Agrikultur dan Perkebunan</option>
                                        <option value="Bisnis Kreatif dan Industri Kreatif">Bisnis Kreatif dan Industri Kreatif</option>
                                        <option value="Bisnis Teknologi atau Digital">Bisnis Teknologi atau Digital</option>
                                        <option value="Bisnis Energi dan Lingkungan">Bisnis Energi dan Lingkungan</option>
                                        <option value="Bisnis Konstruksi dan Real Estate">Bisnis Konstruksi dan Real Estate</option>
                                        <option value="Bisnis Pariwisata dan Perhotelan">Bisnis Pariwisata dan Perhotelan</option>
                                        <option value="Bisnis Finansial">Bisnis Finansial</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>

                                    <!-- Input untuk "Lainnya" -->
                                    <div id="other-category-container" style="display: none; margin-top: 10px;">
                                        <label for="other-category">Masukkan Kategori Bisnis Anda:<span style="color:red;">*</span></label>
                                        <input 
                                        type="text" 
                                        id="other-category" 
                                        name="other_category" 
                                        class="form-control" 
                                        placeholder="Tulis kategori Anda di sini..."
                                        />
                                    </div>
                                    </div>

                                    <script>
                                    function toggleOtherCategoryInput() {
                                        const dropdown = document.getElementById('kategori');
                                        const otherCategoryContainer = document.getElementById('other-category-container');
                                        
                                        // Tampilkan input "Lainnya" jika opsi "Lainnya" dipilih
                                        if (dropdown.value === 'lainnya') {
                                        otherCategoryContainer.style.display = 'block';
                                        } else {
                                        otherCategoryContainer.style.display = 'none';
                                        }
                                    }
                                    </script>


                                    <!-- Proposal (file input) -->
                                    <div class="form-group mb-3">
                                        <label for="proposal" class="form-label">Proposal Bisnis 
                                            <span style="color:red;">*</span>
                                            <small class="text-muted">(unggah dalam format PDF.)</small>
                                        </label>
                                        <div class="input-group">
                                            <input type="file" class="form-control" id="proposal" name="proposal" accept=".pdf" required>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="anggaran">Anggaran:<span style="color:red;">*</span></label>
                                        <input type="text" id="anggaran" name="anggaran" required placeholder="masukkan hanya angka (contoh: 100000).">
                                    </div>
                                
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success" name="kirim">Unggah Proposal</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Menampilkan proposal bisnis dalam bentuk card -->
                <div class="card-container">
                    <?php
                    // Memeriksa apakah ada data proposal yang diambil dari database
                    if (mysqli_num_rows($result) > 0) {
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
                                    <a href="detail_proposal?id=<?php echo $id; ?>">
                                        <i class="fa-solid fa-eye detail-icon" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Lihat Detail Proposal Bisnis"></i>
                                    </a>
                                    <span data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Hapus Proposal Bisnis">
                                        <i class="fa-solid fa-trash-can delete-icon" data-bs-toggle="modal" data-bs-target="#deleteModal" 
                                        data-id="<?php echo $proposal['id']; ?>"></i>
                                    </span>
                                </div>
                            </div>
                            <!-- Modal Konfirmasi Hapus -->
                            <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus Proposal</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Apakah Anda yakin ingin menghapus proposal <span id="proposalTitle" class="fw-bold"></span>?</p>
                                            <p class="text-danger">Aksi ini tidak dapat dibatalkan.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <a href="#" id="confirmDeleteButton" class="btn btn-danger">Hapus</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <script>
                                // Event listener saat modal delete terbuka
                                const deleteModal = document.getElementById('deleteModal');
                                deleteModal.addEventListener('show.bs.modal', function (event) {
                                    const button = event.relatedTarget; // Tombol yang memicu modal
                                    const proposalId = button.getAttribute('data-id'); // Ambil ID proposal
                                    const proposalTitle = button.getAttribute('data-title'); // Ambil judul proposal

                                    // Perbarui isi modal
                                    const modalTitle = deleteModal.querySelector('#proposalTitle');
                                    const confirmDeleteButton = deleteModal.querySelector('#confirmDeleteButton');
                                    modalTitle.textContent = proposalTitle;
                                    confirmDeleteButton.setAttribute('href', `hapus_proposal?id=${proposalId}`);
                                });
                            </script>
                        <?php
                        }
                    } 
                    ?>
                </div>
            </div>  
        </div>
        
        <!-- Toast Notification -->

        <?php if (isset($_SESSION['toast_message'])): ?>
            <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1055;">
                <div class="toast text-bg-success border-0" id="liveToast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header">
                        <img src="\Entree\assets\img\icon_entree_pemberitahuan.png" style="width:40%; height:40%;" class="rounded me-2" alt="Logo">
                        <strong class="me-auto"></strong>
                        <small>Baru Saja</small>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        Materi berhasil diunggah!
                    </div>
                </div>
            </div>
            <script>
                window.onload = function() {
                    showToast('<?php echo $_SESSION['toast_message']['message']; ?>', <?php echo $_SESSION['toast_message']['isSuccess'] ? 'true' : 'false'; ?>);
                };
            </script>
            <?php unset($_SESSION['toast_message']); ?>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/gh/habibmhamadi/multi-select-tag@3.1.0/dist/js/multi-select-tag.js"></script>
    <script>
        new MultiSelectTag('sdg', {
        rounded: true,    // default true
        placeholder: 'Search berdasarkan nomor urutan',  // default Search...
        tagColor: {
            textColor: '#327b2c',
            borderColor: '#92e681',
            bgColor: '#eaffe6',
        },
        onChange: function(values) {
            console.log(values)
        }
    })
    </script>
    <script>
        function showToast(message, isSuccess = true) {
            // Ubah warna toast berdasarkan status
            const toastElement = document.getElementById('liveToast');
            toastElement.classList.remove('bg-success', 'bg-danger');
            toastElement.classList.add(isSuccess ? 'bg-success' : 'bg-danger');

            // Ubah pesan toast
            toastElement.querySelector('.toast-body').textContent = message;

            // Tampilkan toast
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
        }
    </script>


</body>

</html>

<?php
// Menutup koneksi database
mysqli_close($conn);
?>
