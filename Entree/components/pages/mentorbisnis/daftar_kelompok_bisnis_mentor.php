<?php
include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';

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

$user_id = $_SESSION['user_id']; // Ambil user ID dari sesi
$user_role = $_SESSION['role']; // Ambil peran pengguna (Tutor/Dosen Pengampu)

// Ambil kata kunci pencarian jika ada
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Ambil status inkubasi dari dropdown
$status_inkubasi = isset($_GET['status_inkubasi']) ? $_GET['status_inkubasi'] : 'semua'; // Ambil status inkubasi yang dipilih

// Ambil ID tahun akademik yang statusnya aktif
$sql_tahun_akademik = "SELECT id FROM tahun_akademik WHERE status = 'aktif'";
$result_tahun_akademik = $conn->query($sql_tahun_akademik);

if ($result_tahun_akademik->num_rows > 0) {
    $tahun_akademik_aktif = $result_tahun_akademik->fetch_assoc()['id'];
} else {
    die("Tidak ada tahun akademik yang aktif.");
}

// Modifikasi query berdasarkan peran pengguna, tahun akademik aktif, dan pencarian
if ($user_role === 'Tutor') {
    // Jika pengguna adalah Tutor, hanya ambil kelompok yang di-mentor oleh pengguna
    $sql = "
        SELECT * FROM kelompok_bisnis
        WHERE id_mentor = (SELECT id FROM mentor WHERE user_id = ?)
        AND tahun_akademik_id = ?
        AND nama_kelompok LIKE ?
    ";
    
    // Jika ada status inkubasi yang dipilih, tambahkan filter berdasarkan status inkubasi
    if ($status_inkubasi !== 'semua') {
        $sql .= " AND status_inkubasi = ?";
    }
} else {
    // Jika bukan Tutor (misalnya Dosen Pengampu), ambil semua kelompok
    $sql = "
        SELECT * FROM kelompok_bisnis
        WHERE tahun_akademik_id = ?
        AND nama_kelompok LIKE ?
    ";
    
    // Jika ada status inkubasi yang dipilih, tambahkan filter berdasarkan status inkubasi
    if ($status_inkubasi !== 'semua') {
        $sql .= " AND status_inkubasi = ?";
    }
}

// Siapkan query
$stmt = $conn->prepare($sql);

// Bind parameter untuk pencarian dan status inkubasi
$search_param = "%" . $search . "%";

// Bind parameter berdasarkan kondisi
if ($status_inkubasi !== 'semua') {
    if ($user_role === 'Tutor') {
        $stmt->bind_param('ssss', $user_id, $tahun_akademik_aktif, $search_param, $status_inkubasi);
    } else {
        $stmt->bind_param('sss', $tahun_akademik_aktif, $search_param, $status_inkubasi);
    }
} else {
    if ($user_role === 'Tutor') {
        $stmt->bind_param('sss', $user_id, $tahun_akademik_aktif, $search_param);
    } else {
        $stmt->bind_param('ss', $tahun_akademik_aktif, $search_param);
    }
}

// Eksekusi query
$stmt->execute();
$result = $stmt->get_result();

// Menampilkan notifikasi jika ada sukses
if (isset($_GET['success']) && $_GET['success'] == 1) {
    echo "
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Mentor berhasil ditambahkan ke kelompok bisnis.',
            confirmButtonText: 'OK',
            timer: 3000
        });
    </script>
    ";
}
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <div class="toast-container position-fixed top-0 end-0 p-3" id="toast-container">
        <div id="success-toast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    Mentor berhasil ditambahkan ke kelompok bisnis.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>
        // Check if the success parameter is present in the URL
        window.onload = function() {
            // Get URL query parameters
            const urlParams = new URLSearchParams(window.location.search);

            // If success=1 is in the query string, show the toast
            if (urlParams.has('success') && urlParams.get('success') === '1') {
                var toastEl = new bootstrap.Toast(document.getElementById('success-toast'));
                toastEl.show();
            }
        };
    </script>

</head>

<body>
    <div class="wrapper">
        <?php
        $activePage = 'daftar_kelompok_bisnis_mentor';
        include 'sidebar_mentor.php';
        ?>

        <div class="main p-3">
            <div class="main_header">
                <?php
                $pageTitle = "Daftar Kelompok Bisnis";
                include 'header_mentor.php';
                ?>
            </div>

            <div class="main_wrapper">
                <div class="nav_main_wrapper">
                    <nav class="navbar navbar-expand-lg m-0 p-0">
                        <div class="container-fluid m-0 p-0">
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

                            <!-- Form pencarian -->
                            <form action="" method="get">
                                <div class="input-group">
                                    <div class="d-flex" role="search">
                                        <input type="text" class="form-control me-2" placeholder="Cari Kelompok Bisnis" name="search" aria-label="Search" value="<?= htmlspecialchars($search); ?>">
                                        <button class="btn btn-outline-success" type="submit">Cari</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </nav>
                </div>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $id_kelompok = $row['id_kelompok'];
                        $id_mentor = $row['id_mentor']; // Get mentor from the group
                        
                        // Query untuk mendapatkan nama mentor berdasarkan id_mentor
                        $mentorQuery = "
                            SELECT m.nama AS nama_mentor
                            FROM mentor m
                            WHERE m.id = ? LIMIT 1";
                        $mentorStmt = $conn->prepare($mentorQuery);
                        $mentorStmt->bind_param("i", $id_mentor);
                        $mentorStmt->execute();
                        $mentorResult = $mentorStmt->get_result();
                        $mentor = $mentorResult->fetch_assoc();
                        $namaMentor = $mentor['nama_mentor'] ?? 'Nama mentor tidak tersedia';
                        
                        // Query untuk mendapatkan status proposal berdasarkan kelompok_id
                        $sql = "SELECT status FROM proposal_bisnis WHERE kelompok_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $id_kelompok);
                        $stmt->execute();
                        $proposalResult = $stmt->get_result();
                
                        // Inisialisasi variabel status proposal
                        $status_proposal = 'menunggu'; // Default status adalah 'menunggu'
                        $status_approved = false;
                        $status_rejected = false;
                        $status_color = 'orange'; // Default warna untuk status 'menunggu'

                
                        while ($proposalRow = $proposalResult->fetch_assoc()) {
                            // Cek setiap status proposal
                            if ($proposalRow['status'] == 'disetujui') {
                                $status_approved = true;
                                $status_color = '#2ea56f'; // Warna hijau untuk disetujui
                                break; // Jika ada yang disetujui, tidak perlu cek lebih lanjut
                            } elseif ($proposalRow['status'] == 'ditolak') {
                                $status_rejected = true;
                                $status_color = '#dc3545'; // Warna merah untuk ditolak
                            }
                        }
                
                        // Tentukan status akhir proposal
                        if ($status_approved) {
                            $status_proposal = 'disetujui'; // Jika ada yang disetujui
                        } elseif ($status_rejected) {
                            $status_proposal = 'ditolak'; // Jika ada yang ditolak tapi tidak ada yang disetujui
                        }

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
                                        <td>Status Proposal Bisnis</td>
                                        <td>';
                                            if ($status_proposal == 'disetujui') {
                                                echo '<p class="alert alert-success fw-bold text-center m-0 mx-5 p-2" role="alert">Disetujui</p>';
                                            } else {
                                                echo '<p class="alert alert-warning fw-bold text-center m-0 mx-5 p-2" role="alert">Menunggu</p>';
                                            }
                                        echo '</td>
                                    </tr>
                                     <tr>
                                        <td>Program Inkubasi Bisnis</td>
                                        <td>';
                                            if ($row['status_inkubasi'] == 'direkomendasikan') {
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
                                            } elseif ($row['status_inkubasi'] == 'disetujui') {
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
                                            } elseif ($row['status_inkubasi'] == 'ditolak') {
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
                                            } elseif ($row['status_inkubasi'] == 'tidak masuk') {
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
                                            } elseif ($row['status_inkubasi'] == 'masuk') {
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
                
                        if ($_SESSION['role'] == 'Dosen Pengampu') {
                            // Cek apakah mentor sudah tersedia di kelompok bisnis
                            if (!empty($row['id_mentor'])) {
                                echo '<button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#mentorModal' . $id_kelompok . '">Ubah Mentor</button>';
                            } else {
                                echo '<button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#mentorModal' . $id_kelompok . '">Tambah Mentor</button>';
                            }
                        }
                            

                        echo '<a href="detail_kelompok?id_kelompok=' . $id_kelompok . '">
                                <i class="fa-solid fa-eye detail-icon" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Lihat Kelompok Bisnis"></i>
                            </a>';

                        echo '</div>';
                        echo '</div>';

                        echo '<div class="modal fade" id="mentorModal' . $id_kelompok . '" tabindex="-1" aria-labelledby="mentorModalLabel' . $id_kelompok . '" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">';
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
                                                <p>NIDN: ' . htmlspecialchars($mentor['nidn']) . '</p>
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
                    echo "
                    <script>
                        Swal.fire({
                            icon: 'info',
                            title: 'Tidak ada kelompok bisnis yang tersedia.',
                            text: 'Silakan coba lagi nanti.',
                            confirmButtonText: 'OK',
                            timer: 3000
                        });
                    </script>
                    ";
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

        dropdownItems.forEach(item => {
            item.addEventListener("click", function(event) {
                event.preventDefault();

                const selectedText = this.textContent;
                const selectedClass = this.getAttribute("data-status");

                dropdownButton.classList.remove("btn-secondary", "btn-success", "btn-warning", "btn-info");

                dropdownButton.classList.add(selectedClass);

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
$conn->close();
?>