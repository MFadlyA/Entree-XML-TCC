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

// Mendapatkan role dan user ID dari session
$userRole = $_SESSION['role'];
$userId = $_SESSION['user_id'];

$status_jadwal = isset($_GET['status']) ? $_GET['status'] : 'semua'; // Ambil status inkubasi yang dipilih

// Ambil ID dari tabel mentor
$sql_id = "SELECT id FROM mentor WHERE user_id = '$userId'";
$result_id = $conn->query($sql_id);

if (!$result_id) {
    die("Error query sql_id: " . $conn->error);
}

if ($result_id->num_rows > 0) {
    $row_id = $result_id->fetch_assoc();
    $id_mentor = $row_id['id']; // Ambil nilai ID mentor
}

// Ambil kelompok yang di-mentor oleh pengguna
$sql_kelompok = "SELECT id_kelompok FROM kelompok_bisnis WHERE id_mentor = '$id_mentor'";
$result_kelompoktutor = $conn->query($sql_kelompok);

// Ambil kelompok yang di-mentor oleh pengguna dalam bentuk array
$kelompokIds = [];
while ($row = $result_kelompoktutor->fetch_assoc()) {
    $kelompokIds[] = $row['id_kelompok'];
}

// Ambil nama kelompok yang di-mentor oleh pengguna
$sql_nama_kelompok = "SELECT id_kelompok, nama_kelompok FROM kelompok_bisnis WHERE id_mentor = '$id_mentor'";
$result_nama_kelompok = $conn->query($sql_nama_kelompok);

$namaKelompok = []; // Inisialisasi array
if ($result_nama_kelompok->num_rows > 0) {
    while ($row = $result_nama_kelompok->fetch_assoc()) {
        $namaKelompok[$row['id_kelompok']] = $row['nama_kelompok']; // Simpan ID dan nama kelompok
    }
} else {
    $namaKelompok = null; // Jika tidak ada data
}

// Logika pencarian
$searchKeyword = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Logika filter berdasarkan status
$status_filter = '';
if ($status_jadwal !== 'semua') {
    $status_filter = " AND j.status = '$status_jadwal'"; // Menambahkan filter status jika tidak 'semua'
}

if (count($kelompokIds) > 0) {
    $ids = implode(',', $kelompokIds); // Menggabungkan id_kelompok menjadi string
    $sql = "SELECT j.*, kb.nama_kelompok 
            FROM jadwal j 
            JOIN kelompok_bisnis kb ON j.id_klmpk = kb.id_kelompok
            WHERE j.id_klmpk IN ($ids) $status_filter";
    if (!empty($searchKeyword)) {
        $sql .= " AND kb.nama_kelompok LIKE '%$searchKeyword%'"; // Tambahkan kondisi pencarian nama kelompok
    }
    $sql .= " ORDER BY j.tanggal DESC, j.waktu DESC"; // Mengurutkan dari tanggal terbaru dan waktu terbaru
} else {
    $sql = "SELECT * FROM jadwal WHERE 1 = 0"; // Tidak ada data yang ditampilkan
}

$result = $conn->query($sql);
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <div class="wrapper">
   
        <?php 
        $activePage = 'jadwal_bimbingan'; // Halaman ini adalah Profil
        include 'sidebar_mentor.php'; 
        ?>

        <div class="main p-3">
            <div class="main_header">
                <?php 
                    $pageTitle = "Jadwal Bimbingan"; // Judul halaman
                    include 'header_mentor.php'; 
                ?>
            </div>

            <div class="main_wrapper">
                    <div class="nav_main_wrapper">
                        <nav class="navbar">
                             <!-- Tombol untuk Membuka Modal -->
                             <button type="button" class="btn btn-success btn-tambah" data-bs-toggle="modal" data-bs-target="#altScheduleModal">
                                Tambah Jadwal Bimbingan
                            </button>

                            <form class="d-flex" role="search" method="get" action="">
                                <input 
                                    class="form-control me-2" 
                                    type="search" 
                                    name="search" 
                                    placeholder="Cari nama kelompok..." 
                                    aria-label="Search"
                                    value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
                                >
                                <button class="btn btn-outline-success" type="submit">Cari</button>
                            </form>
                            
                            <form method="GET" action="jadwal_bimbingan" id="formStatus">
                                <div class="dropdown">
                                        <button class="btn btn-secondary dropdown-toggle text-white" type="button" 
                                                id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                <span id="selectedStatus">Filter Status</span> <!-- Menampilkan status yang dipilih -->
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <li><a class="dropdown-item" role="button" data-status="semua">Semua Jadwal</a></li>
                                            <li><a class="dropdown-item" role="button" data-status="menunggu">Menunggu</a></li>
                                            <li><a class="dropdown-item" role="button" data-status="disetujui">Disetujui</a></li>
                                            <li><a class="dropdown-item" role="button" data-status="selesai">Selesai</a></li>
                                        </ul>
                                        <input type="hidden" name="status" id="status" value="semua"> <!-- Input tersembunyi untuk mengirim status -->
                                    </div>
                                </div>
                            </form>

                            <script>
                                document.querySelectorAll('.dropdown-item').forEach(function(item) {
                                    item.addEventListener('click', function(e) {
                                        e.preventDefault(); // Mencegah link membuka halaman baru
                                        
                                        var status = this.getAttribute('data-status'); // Ambil status yang dipilih
                                        document.getElementById('selectedStatus').innerText = this.innerText; // Update teks pada tombol dropdown
                                        document.getElementById('status').value = status; // Update nilai input tersembunyi dengan status yang dipilih
                                        
                                        // Kirim formulir setelah status diperbarui
                                        document.getElementById('formStatus').submit();
                                    });
                                });
                            </script>

                            <!-- Modal Jadwal Alternatif -->
                            <div class="modal fade" id="altScheduleModal" tabindex="-1" aria-labelledby="altScheduleModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="altScheduleModalLabel">Tambah Jadwal Bimbingan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="submit_alternative_schedule" method="POST">
                                            <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="altGroupInput" class="form-label">Pilih Kelompok:<span style="color:red;">*</span></label>
                                                <select class="form-select" id="altGroupInput" name="alt_group" required>
                                                    <option value="" disabled selected>Pilih kelompok</option>
                                                    <?php
                                                    // PHP untuk memasukkan data kelompok ke dalam dropdown
                                                    $sql_kelompok = "SELECT id_kelompok, nama_kelompok FROM kelompok_bisnis WHERE id_mentor = '$id_mentor'";
                                                    $result_kelompok = $conn->query($sql_kelompok);

                                                    if ($result_kelompok->num_rows > 0) {
                                                        while ($row = $result_kelompok->fetch_assoc()) {
                                                            $nama_kelompok = htmlspecialchars($row['nama_kelompok']);
                                                            $id_kelompok = htmlspecialchars($row['id_kelompok']);
                                                            echo "<option value=\"$id_kelompok\">$nama_kelompok</option>";
                                                        }
                                                    } else {
                                                        echo "<option value=\"\" disabled>Tidak ada kelompok tersedia</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                                <div class="mb-3">
                                                    <label for="nama_kegiatan" class="form-label">Nama Kegiatan:<span style="color:red;">*</span></label>
                                                    <input type="text" name="nama_kegiatan" id="nama_kegiatan" class="form-control" required placeholder="Masukkan Nama Kegiatan"
                                                        value="<?php echo isset($edit_data['nama_kegiatan']) ? $edit_data['nama_kegiatan'] : ''; ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="altDateInput" class="form-label">Tanggal:<span style="color:red;">*</span></label>
                                                    <input type="date" class="form-control" id="altDateInput" name="alt_date" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="altTimeInput" class="form-label">Waktu:<span style="color:red;">*</span></label>
                                                    <input type="time" class="form-control" id="altTimeInput" name="alt_time" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="agenda" class="form-label">agenda:<span style="color:red;">*</span></label>
                                                    <textarea name="agenda" id="agenda" class="form-control" placeholder="Masukkan Agenda"><?php echo isset($edit_data['agenda']) ? $edit_data['agenda'] : ''; ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="altLocationInput" class="form-label">Lokasi:<span style="color:red;">*</span></label>
                                                    <input type="text" class="form-control" id="altLocationInput" name="alt_location" placeholder="Masukkan lokasi" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary btn-cancel" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-success btn-submit">Buat Jadwal</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                        </nav>
                    </div>
                    
                <div class="container mt-4">
                    <h2>Daftar Jadwal</h2>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Kelompok</th>
                                    <th>Nama Kegiatan</th>
                                    <th>Tanggal</th>
                                    <th>Waktu</th>
                                    <th>Lokasi</th>
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
                                            <td>
                                            <?php
                                                // Tampilkan nama kelompok berdasarkan id_klmpk dari tabel jadwal
                                                $id_klmpk = $row['id_klmpk'];
                                                if (isset($namaKelompok[$id_klmpk])) {
                                                    echo htmlspecialchars($namaKelompok[$id_klmpk]);
                                                } else {
                                                    echo "Nama Kelompok Tidak Ditemukan"; // Handle jika ID tidak ada
                                                }
                                            ?>
                                            </td>
                                            <td style="max-width: 200px;"><?php echo htmlspecialchars($row['nama_kegiatan']); ?></td>
                                            <td><?php echo htmlspecialchars($row['tanggal']); ?></td>
                                            <td><?php echo htmlspecialchars($row['waktu']); ?></td>
                                            <td style="max-width: 250px;"><?php echo htmlspecialchars($row['lokasi']); ?></td>
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
                                                <a href="detail_jadwal?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">
                                                    <i class="fa-solid fa-eye" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Lihat Detail Jadwal"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <script>
                                            Swal.fire({
                                                icon: 'info',
                                                title: 'Tidak ada Jadwal Bimbingan yang tersedia.',
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
    <script>
        const dropdownButton = document.getElementById("dropdownMenuButton");
        const dropdownItems = document.querySelectorAll(".dropdown-item");

        // Tambahkan event listener untuk setiap item di dropdown
        dropdownItems.forEach(item => {
            item.addEventListener("click", function(event) {
                event.preventDefault(); // Mencegah link default

                // Ambil teks dan status warna dari atribut data-status
                const selectedText = this.textContent;
                const selectedClass = this.getAttribute("data-status");

                // Reset semua kelas warna tombol
                dropdownButton.classList.remove("btn-secondary", "btn-success", "btn-warning", "btn-info");

                // Tambahkan kelas warna sesuai pilihan
                dropdownButton.classList.add(selectedClass);

                // Ubah teks tombol sesuai pilihan dropdown
                dropdownButton.textContent = selectedText;
            });
        });
    </script>
</body>
</html>
