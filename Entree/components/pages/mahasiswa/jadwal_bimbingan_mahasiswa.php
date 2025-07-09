<?php
session_start(); // Start the session if not already started

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /Entree/login');
    exit;
}

// Cek apakah role pengguna sesuai
if ($_SESSION['role'] !== 'Mahasiswa') {
    header('Location: /Entree/login');
    exit;
}

include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';

// Check if id_kelompok exists in the session
if (isset($_SESSION['id_kelompok'])) {
    // Ambil nilai id_kelompok dari session
    $id_kelompok = $_SESSION['id_kelompok'];
} else {
    header('Location: /Entree/mahasiswa/dashboard'); // Redirect jika id_kelompok tidak ditemukan
    exit;
}

// Ambil jadwal berdasarkan id_kelompok
$sql = "SELECT * FROM jadwal WHERE id_klmpk = ? ORDER BY tanggal, waktu";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_kelompok); // Bind parameter id_kelompok
$stmt->execute();
$result = $stmt->get_result();

// Cek jika ada pesan toast yang perlu ditampilkan
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kegiatan = $_POST['nama_kegiatan'];
    $tanggal = $_POST['tanggal'];
    $waktu = $_POST['waktu'];
    $agenda = $_POST['agenda'];
    $lokasi = $_POST['lokasi'];
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    $feedback_mentor = ""; // hindari NULL 
    $status = "menunggu"; // status default

    if ($id) {
        // Update jadwal
        $sql = "UPDATE jadwal SET nama_kegiatan = ?, tanggal = ?, waktu = ?, agenda = ?, lokasi = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $nama_kegiatan, $tanggal, $waktu, $agenda, $lokasi, $id);
        $message = $stmt->execute() ? "Jadwal berhasil diperbarui!" : "Gagal memperbarui jadwal.";
    } else {
        $sql = "INSERT INTO jadwal (nama_kegiatan, tanggal, waktu, agenda, lokasi, feedback_mentor, status, id_klmpk) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssi", $nama_kegiatan, $tanggal, $waktu, $agenda, $lokasi, $feedback_mentor, $status, $id_kelompok);
        $message = $stmt->execute() ? "Jadwal berhasil ditambahkan!" : "Gagal menambahkan jadwal.";
    }

    $_SESSION['toast_message'] = $message; // Set message in session
    $stmt->close();
    // Redirect to prevent form resubmission
    header("Location: jadwal_bimbingan");
    exit();
}

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    // Validasi kepemilikan jadwal sebelum menghapus
    $sql_check = "SELECT id_klmpk FROM jadwal WHERE id = ?";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row && $row['id_klmpk'] == $id_kelompok) {
        $sql_delete = "DELETE FROM jadwal WHERE id = ?";
        $stmt = $conn->prepare($sql_delete);
        $stmt->bind_param("i", $delete_id);
        $message = $stmt->execute() ? "Jadwal berhasil dihapus!" : "Gagal menghapus jadwal.";
    } else {
        $message = "Akses tidak diizinkan.";
    }

    $_SESSION['toast_message'] = $message; // Set message in session
    header("Location: jadwal_bimbingan"); // Redirect to prevent form resubmission
    exit();
}

// Ambil data untuk diedit
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $sql_edit = "SELECT * FROM jadwal WHERE id = ?";
    $stmt = $conn->prepare($sql_edit);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();

    // Validasi kepemilikan jadwal sebelum mengedit
    if ($edit_data['id_klmpk'] != $id_kelompok) {
        header("Location: /Entree/mahasiswa/dashboard"); // Redirect jika tidak memiliki akses
        exit();
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Bimbingan | Entree</title>
    <link rel="icon" href="\Entree\assets\img\icon_favicon.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/77a99d5f4f.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="/Entree/assets/css/jadwal_bimbingan.css">
</head>

<body>
    <div class="wrapper">

        <?php 
        $activePage = 'jadwal_bimbingan_mahasiswa'; // Halaman ini adalah Profil
        include 'sidebar_mahasiswa.php'; 
        ?>

        <div class="main p-3">
            <div class="main_header">
                <?php 
                    $pageTitle = "Jadwal Bimbingan"; // Judul halaman
                    include 'header_mahasiswa.php'; 
                ?>
            </div>

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

                    <h2><?php echo isset($edit_data) ? "Perbarui Jadwal" : "Tambah Jadwal"; ?></h2>
                    <form action="" method="post">
                        <input type="hidden" name="id" value="<?php echo isset($edit_data['id']) ? $edit_data['id'] : ''; ?>">
                        <div class="mb-3">
                            <label for="nama_kegiatan" class="form-label">Nama Kegiatan:<span style="color:red;">*</span></label>
                            <input type="text" name="nama_kegiatan" id="nama_kegiatan" class="form-control" placeholder="Masukkan Nama Kegiatan" required
                                value="<?php echo isset($edit_data['nama_kegiatan']) ? $edit_data['nama_kegiatan'] : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="tanggal" class="form-label">Tanggal:<span style="color:red;">*</span></label>
                            <input type="date" name="tanggal" id="tanggal" class="form-control" required
                                value="<?php echo isset($edit_data['tanggal']) ? $edit_data['tanggal'] : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="waktu" class="form-label">Waktu:<span style="color:red;">*</span></label>
                            <input type="time" name="waktu" id="waktu" class="form-control" required
                                value="<?php echo isset($edit_data['waktu']) ? $edit_data['waktu'] : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="agenda" class="form-label">Agenda:<span style="color:red;">*</span></label>
                            <textarea name="agenda" id="agenda" class="form-control" placeholder="Masukkan Agenda" required><?php echo isset($edit_data['agenda']) ? $edit_data['agenda'] : ''; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="lokasi" class="form-label">Lokasi:<span style="color:red;">*</span></label>
                            <input type="text" name="lokasi" id="lokasi" class="form-control" placeholder="Masukkan Lokasi" required
                                value="<?php echo isset($edit_data['lokasi']) ? $edit_data['lokasi'] : ''; ?>">
                        </div>
                        <button type="submit" class="btn btn-success text-white fw-bold" style="background-color:#2ea56f"><?php echo isset($edit_data) ? "Perbarui" : "Simpan"; ?></button>
                        <?php if ($edit_data): ?>
                            <a href="jadwal_bimbingan" class="btn btn-secondary">Batal</a>
                        <?php endif; ?>
                    </form>
                    <hr>

                    <h2>Daftar Jadwal</h2>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
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
                                                <td><?php echo htmlspecialchars($row['nama_kegiatan']); ?></td>
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
                                                <td class="" style="width: 180px;">
                                                    <div class="btn-aksi-mahasiswa">
                                                        <!-- Tampilkan tombol Edit hanya jika status = "Menunggu" -->
                                                        <?php if ($row['status'] === 'menunggu'): ?>
                                                            <a href="?edit_id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                                                <i class="fas fa-edit" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Edit Jadwal Bimbingan"></i> 
                                                            </a>

                                                            <!-- Tombol Hapus -->
                                                        <a href="#" class="btn btn-danger btn-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteConfirmModal"
                                                            onclick="setDeleteUrl(<?php echo $row['id']; ?>)">
                                                            <i class="fa-solid fa-trash-can" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Hapus Jadwal Bimbingan"></i>
                                                        </a>
                                                        <?php endif; ?>

                                                        <!-- Tombol Detail -->
                                                        <a href="detail_jadwal?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">
                                                            <i class="fa-solid fa-eye" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Lihat Jadwal Bimbingan"></i>
                                                        </a>
                                                        <!-- Dropdown untuk Pilih Aksi jika status = "Alternatif" -->
                                                        <?php if ($row['status'] === 'alternatif'): ?>
                                                            <div class="accordion accordion-flush" id="accordionFlushExample">
                                                                <div class="accordion-item">
                                                                    <h2 class="accordion-header">
                                                                    <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne" style="background-color: #007bff; color: white; border-radius: 8px;">
                                                                        Aksi  
                                                                    </button>
                                                                    </h2>
                                                                    <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                                                                    <div class="accordion-body">
                                                                        <div class="d-flex justify-content-between">
                                                                            <button class="btn btn-success setujui-btn text-white fw-bold mx-2" data-id="<?php echo $row['id']; ?>" style="background-color:#2ea56f">Setujui</button>
                                                                            <button class="btn btn-danger tolak-btn text-white fw-bold mx-2" data-id="<?php echo $row['id']; ?>" style="background-color:#dc3545">Tolak</button>
                                                                        </div>
                                                                    </div>
                                                                    </div>
                                                                </div>
                                                        <?php endif; ?>
                                                   </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <script>
                                            Swal.fire({
                                                icon: 'info',
                                                title: 'Tidak ada kelompok jadwak bimbingan yang tersedia.',
                                                text: 'Silakan coba lagi nanti.',
                                                confirmButtonText: 'OK',
                                                timer: 3000
                                            });
                                        </script>
                                    <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="toast align-items-center text-white bg-success border-0" id="toastMessage" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
        <div class="d-flex">
            <div class="toast-body" id="toastMessageContent">
                <!-- Toast message will be inserted here -->
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>

    <!-- Modal Konfirmasi -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteConfirmLabel">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Apakah Anda yakin ingin menghapus jadwal ini?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button>
                        <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Ya, Hapus</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi -->
    <div class="modal" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Konfirmasi Aksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin mengkonfirmasi status jadwal alternatif dari tutor anda ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="confirmBtn">Iya</button>
            </div>
            </div>
        </div>
    </div>


    <script>
        // Fungsi untuk menetapkan URL hapus ke tombol modal
        function setDeleteUrl(id) {
            const deleteUrl = `?delete_id=${id}`; // URL dengan parameter ID
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            confirmDeleteBtn.setAttribute('href', deleteUrl); // Set URL pada tombol modal
        }
    </script>
    <script>
        // Fungsi untuk membuka modal dan mengirimkan data
        function openConfirmModal(jadwal_id, action) {
            // Set aksi dan id jadwal
            document.getElementById('confirmBtn').onclick = function() {
                // Kirim data ke backend menggunakan fetch API
                fetch('submit_action', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=' + action + '&jadwal_id=' + jadwal_id
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert(data.message);  // Tampilkan pesan berhasil
                        location.reload();  // Refresh halaman jika perlu
                    } else {
                        alert(data.message);  // Tampilkan pesan error
                    }
                })
                .catch(error => {
                    alert('Terjadi kesalahan: ' + error);
                });
            };

            // Tampilkan modal
            var myModal = new bootstrap.Modal(document.getElementById('confirmModal'));
            myModal.show();
        }

        // Fungsi untuk memicu modal ketika tombol setujui atau tolak diklik
        document.querySelectorAll('.setujui-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                const jadwalId = this.getAttribute('data-id');
                openConfirmModal(jadwalId, 'setujui');
            });
        });

        document.querySelectorAll('.tolak-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                const jadwalId = this.getAttribute('data-id');
                openConfirmModal(jadwalId, 'tolak');
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
</body>
</html>
