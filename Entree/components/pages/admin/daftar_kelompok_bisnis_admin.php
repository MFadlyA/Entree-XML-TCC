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

// Ambil kata kunci pencarian jika ada
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Ambil status inkubasi dari dropdown
$status_inkubasi = isset($_GET['status_inkubasi']) ? $_GET['status_inkubasi'] : 'semua'; // Ambil status inkubasi yang dipilih

// Ambil tahun akademik yang dipilih dari dropdown
$tahun_akademik = isset($_GET['tahun_akademik']) ? $_GET['tahun_akademik'] : ''; // Format: 2024/2025 Ganjil

$tahun_akademik_id = null; // Inisialisasi variabel tahun_akademik_id

// Jika tahun akademik dipilih, pisahkan tahun dan jenis tahun
if ($tahun_akademik) {
    list($tahun_awal, $tahun_akhir_jenis) = explode('/', $tahun_akademik);
    list($tahun_akhir, $jenis_tahun) = explode(' ', $tahun_akhir_jenis);
    
    // Query untuk mendapatkan tahun_akademik_id berdasarkan tahun dan jenis_tahun
    $tahunAkademikSql = "SELECT id FROM tahun_akademik WHERE tahun = ? AND jenis_tahun = ?";
    $stmtTahunAkademik = $conn->prepare($tahunAkademikSql);
    $stmtTahunAkademik->bind_param('ss', $tahun_awal, $jenis_tahun);
    $stmtTahunAkademik->execute();
    $resultTahunAkademik = $stmtTahunAkademik->get_result();
    
    if ($resultTahunAkademik->num_rows > 0) {
        $tahun_akademik_id = $resultTahunAkademik->fetch_assoc()['id'];
    }
}

// Membuat query dasar berdasarkan pencarian
$sql = "SELECT * FROM kelompok_bisnis_backup WHERE nama_kelompok LIKE ?";

// Jika status inkubasi dipilih selain 'semua', tambahkan filter untuk status inkubasi
if ($status_inkubasi != 'semua') {
    $sql .= " AND status_inkubasi = ?";
}

// Jika tahun akademik dipilih dan ditemukan id, tambahkan filter berdasarkan tahun_akademik_id
if ($tahun_akademik_id) {
    $sql .= " AND tahun_akademik_id = ?";
}

// Siapkan query
$stmt = $conn->prepare($sql);

// Bind parameter untuk pencarian
$search_param = "%" . $search . "%";

// Jika status inkubasi ada, bind parameter status inkubasi dan tahun_akademik_id
if ($status_inkubasi != 'semua' && $tahun_akademik_id) {
    $stmt->bind_param('sss', $search_param, $status_inkubasi, $tahun_akademik_id);
} elseif ($status_inkubasi != 'semua') {
    $stmt->bind_param('ss', $search_param, $status_inkubasi);
} elseif ($tahun_akademik_id) {
    $stmt->bind_param('ss', $search_param, $tahun_akademik_id);
} else {
    $stmt->bind_param('s', $search_param);
}

// Eksekusi query
$stmt->execute();
$result = $stmt->get_result();

// Query untuk mengambil data tahun akademik
$tahunAkademik = "SELECT tahun, jenis_tahun FROM tahun_akademik ORDER BY tahun DESC";
$resulTahun = $conn->query($tahunAkademik);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kelompok Bisnis | Entree</title>
    <link rel="icon" href="\Entree\assets\img\icon_favicon.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/77a99d5f4f.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="/Entree/assets/css/daftar_kelompok.css">
    <style>
        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php 
        $activePage = 'daftar_kelompok_bisnis_admin'; 
        include 'sidebar_admin.php'; 
        ?>

        <div class="main p-3">
            <div class="main_header">
                <?php 
                    $pageTitle = "Daftar Kelompok Bisnis"; 
                    include 'header_admin.php'; 
                ?>
            </div>

            <div class="main_wrapper">
                <div class="nav_main_wrapper">
                    <nav class="navbar navbar-expand-lg">
                        <div class="container-fluid">
                        <form method="get" action="daftar_kelompok_bisnis" id="formTahunAkademik">
                            <select name="tahun_akademik" class="form-select filter-tahun" required id="tahunAkademikDropdown">
                                <option value="" disabled selected>Pilih Tahun Akademik</option>
                                <?php
                                // Query untuk mendapatkan tahun akademik dari database
                                $tahunAkademikSql = "SELECT tahun, jenis_tahun FROM tahun_akademik ORDER BY tahun DESC";
                                $resultTahun = $conn->query($tahunAkademikSql);

                                while ($row = $resultTahun->fetch_assoc()) {
                                    $tahunAkademikOption = $row['tahun'] . '/' . ($row['tahun'] + 1) . ' ' . $row['jenis_tahun'];
                                    echo "<option value='$tahunAkademikOption'>$tahunAkademikOption</option>";
                                }
                                ?>
                            </select>
                        </form>

                            <form action="" method="get">
                                    <div class="input-group">
                                        <div class="d-flex" role="search">
                                        <input type="text" class="form-control me-2" placeholder="Cari Kelompok Bisnis" name="search" aria-label="Search" value="<?= htmlspecialchars($search); ?>">
                                        <button class="btn btn-outline-success" type="submit">Cari</button>
                                    </div>
                                </div>
                            </form>

                            <form method="GET" action="daftar_kelompok_bisnis" id="formStatus">
                                <div class="dropdown">
                                    <button class="btn btn-secondary dropdown-toggle text-white" type="button" 
                                            id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span id="selectedStatus">Filter Kelompok</span> <!-- Menampilkan status yang dipilih -->
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <li><a class="dropdown-item" role="button" data-status="semua">Semua Kelompok</a></li>
                                        <li><a class="dropdown-item" role="button" data-status="direkomendasikan">Direkomendasi</a></li>
                                        <li><a class="dropdown-item" role="button" data-status="disetujui">Direkomendasi dan Bersedia</a></li>
                                        <li><a class="dropdown-item" role="button" data-status="masuk">Program Inkubasi</a></li>
                                    </ul>
                                    <input type="hidden" name="status_inkubasi" id="status_inkubasi" value="semua"> <!-- Input tersembunyi untuk mengirim status -->
                                </div>
                            </form>


                            <script>
                                // Menangani klik dropdown item dan update teks serta input tersembunyi
                                document.querySelectorAll('.dropdown-item').forEach(function(item) {
                                    item.addEventListener('click', function(e) {
                                        e.preventDefault(); // Mencegah link membuka halaman baru
                                        
                                        var status = this.getAttribute('data-status'); // Ambil status yang dipilih
                                        document.getElementById('selectedStatus').innerText = this.innerText; // Update teks pada tombol dropdown
                                        document.getElementById('status_inkubasi').value = status; // Update nilai input tersembunyi dengan status yang dipilih
                                    });
                                });
                            </script>
                        </div>
                    </nav>
                </div>
                            

                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $id_kelompok = $row['id_kelompok'];
                        $id_mentor = $row['id_mentor'];
                
                        // Query untuk mengambil nama mentor
                        $mentorQuery = "
                            SELECT m.nama AS nama_mentor
                            FROM mentor m
                            WHERE m.id = '$id_mentor' LIMIT 1";
                        $mentorResult = mysqli_query($conn, $mentorQuery);
                        $mentor = mysqli_fetch_assoc($mentorResult);
                        $namaMentor = $mentor['nama_mentor'] ?? 'Nama mentor tidak tersedia';
                
                        // Query untuk mengambil status inkubasi dan status kelompok bisnis
                        $query = "SELECT status_inkubasi, status_kelompok_bisnis FROM kelompok_bisnis_backup WHERE id_kelompok = '$id_kelompok' LIMIT 1";
                        $resultStatus = mysqli_query($conn, $query);
                        $statusRow = mysqli_fetch_assoc($resultStatus);
                        
                        // Pastikan status inkubasi tidak null, jika null set ke string kosong
                        $status_inkubasi = $statusRow['status_inkubasi'] ?? 'Tidak Ada';
                        $status_kelompok_bisnis = $statusRow['status_kelompok_bisnis'] ?? 'Tidak Ada';
                
                        echo '
                            <div class="card" style="width: 45%; margin: 10px;">
                                <div class="card-icon text-center py-4">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                                <div class="card-body m-0">
                                    <h5 class="card-title">' . htmlspecialchars($row['nama_kelompok']) . '</h5>
                                </div>
                                <table class="table table-bordered m-0 styled-table">
                                    <tbody>
                                        <tr>
                                            <td>Mentor Bisnis</td>';
                                            if ($namaMentor == "Nama mentor tidak tersedia") {
                                                echo '<td style="max-width:300px"><div class="alert alert-danger fw-bold text-center m-0 mx-5 p-2" role="alert">Nama mentor tidak tersedia</div></td>';
                                            } else {
                                                echo '<td style="max-width:300px" >' . $namaMentor . '</td>';
                                            }
                                    echo '</tr>
                                        <tr>
                                            <td>Status Kelompok Bisnis</td>
                                            <td>';
                
                                            // Menampilkan status dengan kelas yang sesuai
                                            if ($status_kelompok_bisnis == 'aktif') {
                                                echo '<p class="alert alert-success fw-bold text-center m-0 mx-5 p-2" role="alert">Aktif</p>';
                                            } else {
                                                echo '<p class="alert alert-secondary fw-bold text-center m-0 mx-5 p-2" role="alert">Tidak Aktif</p>';
                                            }
                
                                            echo '
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Program Inkubasi Bisnis</td>
                                        <td>';
                                            if ($status_inkubasi == 'direkomendasikan') {
                                                echo '
                                                <p 
                                                    class="alert alert-success fw-bold text-center m-0 mx-5 p-2" 
                                                    role="alert" 
                                                    data-bs-toggle="popover" 
                                                    title="Status Direkomendasikan" 
                                                    data-bs-content="Kelompok Bisnis ini direkomendasikan oleh Mentor bisnis nya untuk masuk ke dalam Program Inkubasi."
                                                    style="cursor: pointer;">
                                                        Direkomendasikan
                                                </p>';
                                            } elseif ($status_inkubasi == 'disetujui') {
                                                echo '
                                                <p 
                                                    class="alert alert-success fw-bold text-center m-0 mx-5 p-2" 
                                                    role="alert" 
                                                    data-bs-toggle="popover" 
                                                    title="Status Direkomendasi dan Bersedia" 
                                                    data-bs-content="Kelompok bisnis ini Direkomendasi oleh Mentor Bisnisnya dan Bersedia untuk masuk ke Program Inkubasi, Perlu Persetujuan Admin PIKK untuk Kelompok Bisnis ini masuk ke dalam Program Inkubasi. "
                                                    style="cursor: pointer;">
                                                        Direkomendasi dan Bersedia
                                                </p>';
                                            } elseif ($status_inkubasi == 'ditolak') {
                                                echo '
                                                <p 
                                                    class="alert alert-danger fw-bold text-center m-0 mx-5 p-2" 
                                                    role="alert" 
                                                    data-bs-toggle="popover" 
                                                    title="Status Ditolak Kelompok Bisnis" 
                                                    data-bs-content="Kelompok Bisnis ini Menolak untuk Masuk ke dalam Program Inkubasi."
                                                    style="cursor: pointer;">
                                                        Ditolak Kelompok Bisnis
                                                </p>';
                                            } elseif ($status_inkubasi == 'tidak masuk') {
                                                echo '
                                                <p 
                                                    class="alert alert-danger fw-bold text-center m-0 mx-5 p-2" 
                                                    role="alert" 
                                                    data-bs-toggle="popover" 
                                                    title="Status Ditolak Admin PIKK" 
                                                    data-bs-content="Kelompok Bisnis ini Ditolak oleh Admin PIKK untuk Masuk Kedalam Program Inkubasi."
                                                    style="cursor: pointer;">
                                                        Ditolak Admin PIKK
                                                </p>';
                                            } elseif ($status_inkubasi == 'masuk') {
                                                echo '
                                                <p 
                                                    class="alert alert-info fw-bold text-center m-0 mx-5 p-2" 
                                                    role="alert" 
                                                    data-bs-toggle="popover" 
                                                    title="Status Program Inkubasi" 
                                                    data-bs-content="Kelompok Bisnis ini Terdaftar dalam Program Inkubasi."
                                                    style="cursor: pointer;">
                                                        Program Inkubasi
                                                </p>';
                                            } else {
                                                echo '
                                                <p 
                                                    class="alert alert-secondary fw-bold text-center m-0 mx-5 p-2" 
                                                    role="alert" 
                                                    data-bs-toggle="popover" 
                                                    title="Status Tidak Ada" 
                                                    data-bs-content="Status Program Kelompok Bisnis ini belum ditentukan."
                                                    style="cursor: pointer;">
                                                        Tidak Ada
                                                </p>';
                                            }
                                        echo '</td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div class="card-footer">';

                                echo '
                                <a href="detail_kelompok?id_kelompok=' . $id_kelompok . '">
                                    <i class="fa-solid fa-eye detail-icon" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Lihat Kelompok Bisnis"></i>
                                </a>';
                                
                                // Menambahkan kondisi PHP untuk tombol "Tambah Mentor"
                                if ($status_inkubasi == 'masuk') {
                                    echo '
                                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#mentorModal' . $id_kelompok . '">Ubah Mentor Inkubasi</button>';
                                }
                                    
                                echo '</div>
                            </div>';
                
                        echo '<div class="modal fade" id="mentorModal' . $id_kelompok . '" tabindex="-1" aria-labelledby="mentorModalLabel' . $id_kelompok . '" aria-hidden="true">';
                        echo '<div class="modal-dialog modal-dialog-centered modal-xl">';
                        echo '<div class="modal-content">';
                        echo '<div class="modal-header">';
                        echo '<h5 class="modal-title" id="mentorModalLabel' . $id_kelompok . '">Pilih Mentor Bisnis</h5>';
                        echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
                        echo '</div>';
                        echo '<div class="modal-body">';
                        echo '<form method="POST" action="" class="add-mentor-form">';
                        echo '<input type="hidden" name="id_kelompok" value="' . $id_kelompok . '">';

                        // Query untuk mendapatkan data mentor
                        $mentorQuery = "
                        SELECT 
                            mentor.*, 
                            users.role AS peran 
                        FROM mentor 
                        JOIN users ON mentor.user_id = users.id
                        ";
                        $result_mentor = $conn->query($mentorQuery);

                        echo '<form action="" method="get" class="mb-4">
                            <div class="input-group">
                                <input type="text" class="form-control d-none" placeholder="Cari mentor..." name="search" value="' . htmlspecialchars($search) . '">
                                <button class="btn btn-success d-none" type="submit">Cari</button>
                            </div>
                        </form>';   

                           
                        if ($result_mentor->num_rows > 0) {
                            echo '<div class="clearfix">';
                            while ($mentor = $result_mentor->fetch_assoc()) {
                                echo '
                                    <div class="accordion" id="accordionExample">
                                        <div class="card-mentor mb-3">
                                            <a data-bs-toggle="collapse" href="#collapse' . $mentor['id'] . '" role="button" 
                                                aria-expanded="false" aria-controls="collapse' . $mentor['id'] . '">
                                                <div class="card-mentor-header">
                                                    <img alt="Profile picture of the mentor" class="w-12 h-12 rounded-full me-2" height="50" width="50"
                                                        src="' . (!empty($mentor['foto_profile']) ? htmlspecialchars($mentor['foto_profile']) : '\Entree\assets\img\default-profile-mentor.png') . '"
                                                        onerror="this.src=\'\Entree\assets\img\default-profile-mentor.png\'"/>
                                                    <div class="nama-mentor">
                                                        <h2 class="font-bold mb-0">' . htmlspecialchars($mentor['nama']) . '</h2>
                                                        <p class="mb-0">Peran: ' . htmlspecialchars($mentor['peran'] ?? 'Belum ada peran') . '</p>
                                                    </div>
                                                    <div class="klik d-flex flex-column align-items-center">
                                                        <span class="toggle-text" id="toggle-text-' . $mentor['id'] . '">
                                                            Klik untuk melihat detail data mentor
                                                        </span>
                                                        <i class="fa-solid fa-caret-down"></i>
                                                    </div>
                                                </div>
                                            </a>
                                            <div id="collapse' . $mentor['id'] . '" class="collapse" data-bs-parent="#accordionExample">
                                                <div class="card-mentor-body">
                                                    <p>NIK: ' . htmlspecialchars($mentor['nidn']) . '</p>
                                                    <p>Keahlian: ' . htmlspecialchars($mentor['keahlian']) . '</p>
                                                    <p>Fakultas: ' . htmlspecialchars($mentor['fakultas']) . '</p>
                                                    <p>Prodi: ' . htmlspecialchars($mentor['prodi']) . '</p>
                                                    <p>Email: ' . htmlspecialchars($mentor['email']) . '</p>
                                                    <p>Nomor Telepon: ' . htmlspecialchars($mentor['contact']) . '</p>

                                                    <div class="btn-div d-flex justify-content-center mt-4">
                                                        <form method="POST" action="update_kelompok_bisnis">
                                                            <input type="hidden" name="id_kelompok" value="' . $id_kelompok . '">
                                                            <input type="hidden" name="id_mentor" value="' . $mentor['id'] . '">
                                                            <button type="submit" class="btn btn-success mt-2">Pilih sebagai Mentor</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>';
                            }
                            echo '</div>';
                        } else {
                            echo '<p>Tidak ada mentor yang ditemukan.</p>';
                        }
                        
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '
                    <div class="d-flex justify-content-center align-items-center" style="height: 60vh; width: 100%;">
                        <div class="alert alert-warning text-center" role="alert">
                            <p>Tidak ada kelompok bisnis yang tersedia.</p>
                        </div>
                    </div>
                    ';
                }
                ?>
            </div>
        </div>
    </div>
    <script>
        document.querySelectorAll('.dropdown-item').forEach(function (item) {
            item.addEventListener('click', function (e) {
                e.preventDefault(); // Mencegah default link behavior

                // Ambil nilai status yang dipilih
                var status = this.getAttribute('data-status');

                // Update teks pada tombol dropdown
                document.getElementById('selectedStatus').innerText = this.innerText;

                // Update nilai input tersembunyi
                document.getElementById('status_inkubasi').value = status;

                // Submit form secara otomatis
                document.getElementById('formStatus').submit();
            });
        });

        // Menangani perubahan pada dropdown Tahun Akademik
        document.getElementById('tahunAkademikDropdown').addEventListener('change', function () {
            // Submit form otomatis
            document.getElementById('formTahunAkademik').submit();
        });

    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Pilih semua elemen collapse yang digunakan
            const collapses = document.querySelectorAll('.collapse');

            collapses.forEach(function (collapse) {
                collapse.addEventListener('show.bs.collapse', function () {
                    const mentorId = this.id.replace('collapse', '');
                    const toggleText = document.getElementById('toggle-text-' + mentorId);
                    const caretIcon = toggleText.nextElementSibling; // Mengambil ikon setelah span
                    
                    if (toggleText) {
                        toggleText.style.display = 'none'; // Hilangkan teks
                    }
                    if (caretIcon) {
                        caretIcon.style.display = 'none'; // Hilangkan ikon
                    }
                });

                collapse.addEventListener('hide.bs.collapse', function () {
                    const mentorId = this.id.replace('collapse', '');
                    const toggleText = document.getElementById('toggle-text-' + mentorId);
                    const caretIcon = toggleText.nextElementSibling; // Mengambil ikon setelah span

                    if (toggleText) {
                        toggleText.style.display = 'inline'; // Tampilkan teks
                    }
                    if (caretIcon) {
                        caretIcon.style.display = 'inline'; // Tampilkan ikon
                    }
                });
            });
        });

    </script>
     <script>
        document.addEventListener("DOMContentLoaded", function () {
            const namaMentorElements = document.querySelectorAll('.nama-mentor');
            let maxHeight = 0;

            namaMentorElements.forEach((element) => {
                const height = element.offsetHeight;
                if (height > maxHeight) {
                    maxHeight = height;
                }
            });

            namaMentorElements.forEach((element) => {
                element.style.height = maxHeight + 'px';
            });
        });

        window.addEventListener('resize', function () {
            const namaMentorElements = document.querySelectorAll('.nama-mentor');
            let maxHeight = 0;

            namaMentorElements.forEach((element) => {
                element.style.height = ''; // Reset height
                const height = element.offsetHeight;
                if (height > maxHeight) {
                    maxHeight = height;
                }
            });

            namaMentorElements.forEach((element) => {
                element.style.height = maxHeight + 'px';
            });
        });
    </script>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                var popover = new bootstrap.Popover(popoverTriggerEl);
                var intervalId;

                popoverTriggerEl.addEventListener('shown.bs.popover', function () {
                    var popoverElement = document.querySelector('.popover');

                    intervalId = setInterval(function () {
                        if (popoverElement) {
                            var rect = popoverElement.getBoundingClientRect();
                            if (rect.top < 0 || rect.bottom > window.innerHeight) {
                                popover.hide();
                                clearInterval(intervalId); // Stop checking once popover is hidden
                            }
                        }
                    }, 100); // Check every 100 milliseconds
                });

                popoverTriggerEl.addEventListener('hidden.bs.popover', function () {
                    clearInterval(intervalId); // Clear the interval if the popover is manually closed
                });

                return popover;
            });
        });
    </script>

</body>

</html>

<?php
// Menutup koneksi database
$conn->close();
?>