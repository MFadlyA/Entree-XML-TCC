<?php
// Mulai session jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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

// Ambil data pengguna yang sedang login
$npm = $_SESSION['npm'];  // Pastikan npm sudah diset dalam session

// Query untuk memeriksa apakah pengguna sudah menjadi ketua atau anggota dalam kelompok
$cekKelompokQuery = "
    SELECT k.id_kelompok, k.npm_ketua, a.npm_anggota
    FROM kelompok_bisnis k
    LEFT JOIN anggota_kelompok a ON a.id_kelompok = k.id_kelompok
    WHERE k.npm_ketua = '$npm' OR a.npm_anggota = '$npm'
";
$cekKelompokResult = mysqli_query($conn, $cekKelompokQuery);

// Jika pengguna sudah menjadi ketua atau anggota dalam kelompok
$kelompokStatus = mysqli_num_rows($cekKelompokResult) > 0 ? true : false;

// Inisialisasi variabel proposalApproved
$proposalApproved = false;

// Query untuk memeriksa status proposal
$cekProposalQuery = "
    SELECT COUNT(*) AS jumlah_disetujui
    FROM proposal_bisnis p
    LEFT JOIN kelompok_bisnis k ON p.kelompok_id = k.id_kelompok
    LEFT JOIN anggota_kelompok a ON a.id_kelompok = k.id_kelompok
    WHERE (k.npm_ketua = '$npm' OR a.npm_anggota = '$npm') AND p.status = 'disetujui';
";

$cekProposalResult = mysqli_query($conn, $cekProposalQuery);
$proposalApproved = false;

if ($cekProposalResult && mysqli_num_rows($cekProposalResult) > 0) {
    $proposalData = mysqli_fetch_assoc($cekProposalResult);
    $proposalApproved = $proposalData['jumlah_disetujui'] > 0;
}

$cekKelompokMentorQuery = "
    SELECT k.id_mentor
    FROM kelompok_bisnis k
    WHERE k.npm_ketua = '$npm' OR EXISTS (
        SELECT 1 FROM anggota_kelompok a WHERE a.npm_anggota = '$npm' AND a.id_kelompok = k.id_kelompok
    )
";

$cekKelompokMentorResult = mysqli_query($conn, $cekKelompokMentorQuery);
$kelompokDenganMentor = false;

if ($cekKelompokMentorResult && mysqli_num_rows($cekKelompokMentorResult) > 0) {
    $kelompokData = mysqli_fetch_assoc($cekKelompokMentorResult);
    $kelompokDenganMentor = !is_null($kelompokData['id_mentor']); // Cek apakah id_mentor null
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Kewirusahaan</title>
    <link rel="icon" href="\Entree\assets\img\icon_favicon.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/77a99d5f4f.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="/Entree/assets/css/sidebar.css">
</head>

<body>
    <div class="wrapper">
        <button class="toggle-btn2" type="button">
            <i class="fa-solid fa-bars"></i>
        </button>
        <aside id="sidebar">
            <div>
                <button class="toggle-btn" type="button">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="sidebar-logo d-flex-custom-sidebar justify-content-center align-items-center">
                    <img src="\Entree\assets\img\kiri_logo_sidebar.png" alt="Logo Entree" class="logo-kiri">
                    <img src="\Entree\assets\img\kanan_logo_sidebar.png" alt="" class="logo-kanan">
                </div>
            </div>
           
            <ul class="sidebar-nav">

                <li class="sidebar-item <?php echo ($activePage == 'profil') ? 'active' : ''; ?>">
                    <a href="profil" class="sidebar-link">
                        <i class="fa-solid fa-user"></i>
                        <span>Profil</span>
                    </a>
                </li>
                <li class="sidebar-item <?php echo ($activePage == 'pagemahasiswa') ? 'active' : ''; ?>">
                    <a href="dashboard" class="sidebar-link">
                        <i class="fa-solid fa-house"></i>
                        <span>Beranda</span>
                    </a>
                </li>
                <li class="sidebar-item <?php echo ($activePage == 'materikewirausahaan_mahasiswa') ? 'active' : ''; ?>">
                    <a href="materi_kewirausahaan" class="sidebar-link">
                        <i class="fa-solid fa-book"></i>
                        <span>Materi Kewirausahaan</span>
                    </a>
                </li>
                <li class="sidebar-item <?php echo ($activePage == 'daftar_mentor_mahasiswa') ? 'active' : ''; ?>">
                    <a href="daftar_mentor" class="sidebar-link">
                        <i class="fa-solid fa-address-card"></i>  
                        <span>Daftar Mentor Bisnis</span>
                    </a>
                </li>
                <hr>
                <li class="sidebar-header">
                    <h1>Mahasiswa</h1>
                </li>
                <li class="sidebar-item <?php echo ($activePage == 'kelompok_bisnis') ? 'active' : ''; ?>">
                    <a href="kelompok_bisnis" class="sidebar-link">
                        <i class="fa-solid fa-users"></i>
                        <span>Kelompok Bisnis</span>
                    </a>
                </li>

                    <!-- Sidebar -->
                    <?php if ($kelompokStatus): ?>
                    <li class="sidebar-item">
                        <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#kelola_bisnis_kelompok" aria-expanded="false" aria-controls="kelola_bisnis_kelompok">
                            <i class="fa-solid fa-user-pen"></i>
                            <span>Kelola Bisnis</span>
                        </a>
                        <ul id="kelola_bisnis_kelompok" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                            <li class="sidebar-item <?php echo ($activePage == 'proposal_bisnis_mahasiswa') ? 'active' : ''; ?>">
                                <a href="proposal" class="sidebar-link">
                                    <i class="fa-solid fa-file"></i>
                                    Proposal Bisnis
                                </a>
                            </li>
                            <!-- Fitur Laporan Kemajuan -->
                            <li class="sidebar-item <?php echo ($activePage == 'laporan_bisnis_mahasiswa') ? 'active' : ''; ?>">
                                <?php if ($proposalApproved): ?>
                                    <a href="laporan_bisnis" class="sidebar-link">
                                        <i class="fa-solid fa-file-invoice"></i>    
                                        Laporan Kemajuan
                                    </a>
                                <?php else: ?>
                                    <a href="javascript:void(0);" class="sidebar-link" data-bs-toggle="tooltip" title="Tidak tersedia kecuali: Status proposal bisnis sudah disetujui">
                                        <i class="fa-solid fa-lock"></i> <!-- Ikon gembok -->
                                        Laporan Kemajuan
                                    </a>
                                <?php endif; ?>
                            </li>

                            <!-- Fitur Jadwal Bimbingan -->
                            <li class="sidebar-item <?php echo ($activePage == 'jadwal_bimbingan_mahasiswa') ? 'active' : ''; ?>">
                                <?php if ($kelompokDenganMentor): ?>
                                    <a href="jadwal_bimbingan" class="sidebar-link">
                                        <i class="fa-solid fa-calendar"></i>
                                        Jadwal Bimbingan
                                    </a>
                                <?php else: ?>
                                    <a href="javascript:void(0);" class="sidebar-link" data-bs-toggle="tooltip" title="Tidak tersedia kecuali: Kelompok Bisnis sudah mempunyai Mentor Bisnis">
                                        <i class="fa-solid fa-lock"></i> <!-- Ikon gembok -->
                                        Jadwal Bimbingan
                                    </a>
                                <?php endif; ?>
                            </li>
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const lockedLinks = document.querySelectorAll('.sidebar-link[data-bs-toggle="tooltip"]');

                                lockedLinks.forEach(function(link) {
                                    link.addEventListener('click', function(event) {
                                        // Ambil title dari elemen yang diklik
                                        const tooltipText = event.target.getAttribute('data-bs-toggle') === 'tooltip' ? event.target.getAttribute('title') : '';
                                        
                                        if (tooltipText) {
                                            alert(tooltipText); // Menampilkan pesan tooltip sesuai dengan fitur yang diklik
                                        }
                                    });
                                });
                            });
                        </script>
                    </ul>
                </li>
                <?php endif; ?>

                <li class="sidebar-item sign-out">
                    <a href="javascript:void(0);" class="sidebar-link" id="logoutLink">
                        <i class="fa-solid fa-sign-out"></i>
                        <span>Keluar</span>
                    </a>
                </li>

                <script>
                    document.getElementById('logoutLink').addEventListener('click', function() {
                        // Kirim permintaan logout ke backend
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', '/Entree/components/pages/logout.php', true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.onload = function() {
                            if (xhr.status === 200) {
                                // Jika berhasil, arahkan ke halaman login
                                window.location.href = '/Entree/dashboard';
                            } else {
                                alert('Terjadi kesalahan saat logout.');
                            }
                        };
                        xhr.send('logout=true');
                    });
                </script>                
            </ul>
        </aside>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn2 = document.querySelector('.toggle-btn2'); // Tombol toggle
            const sidebar = document.querySelector('#sidebar'); // Sidebar

            // Fungsi untuk toggle display pada sidebar
            toggleBtn2.addEventListener('click', function () {
                sidebar.classList.toggle('active'); // Menambahkan atau menghapus kelas 'active'
            });
        });


        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn2 = document.querySelector('.toggle-btn2'); // Tombol toggle
            const sidebar = document.querySelector('#sidebar'); // Sidebar

            // Fungsi untuk toggle display pada sidebar
            toggleBtn2.addEventListener('click', function () {
                if (sidebar.style.display === 'block') {
                    sidebar.style.display = 'none'; // Sembunyikan sidebar
                } else {
                    sidebar.style.display = 'block'; // Tampilkan sidebar
                }
            });
        });
    </script>

    <!-- Tooltip -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoA6bO1PINKzG6M3Y1Zq3Gpt5juVQm9s4vo7+FAI7xgIpPb" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>

</html>
