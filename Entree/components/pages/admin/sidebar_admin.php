<?php
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: /Entree/login');
        exit;
    }
    
    // Cek apakah role pengguna sesuai
    if ($_SESSION['role'] !== 'Admin') {
        header('Location: /Entree/login');
        exit;
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
                <li class="sidebar-item <?php echo ($activePage == 'pageadmin') ? 'active' : ''; ?>">
                    <a href="dashboard" class="sidebar-link">
                        <i class="fa-solid fa-house"></i>
                        <span>Beranda</span>
                    </a>
                </li>
                <hr>
                <li class="sidebar-header">
                    <h1>Admin PIKK</h1>
                </li>
                <li class="sidebar-item <?php echo ($activePage == 'materikewirausahaan_admin') ? 'active' : ''; ?>">
                    <a href="materi_kewirausahaan" class="sidebar-link">
                        <i class="fa-solid fa-book"></i>
                        <span>Materi Kewirausahaan</span>
                    </a>
                </li>
                <li class="sidebar-item <?php echo ($activePage == 'daftar_mentor_admin') ? 'active' : ''; ?>">
                    <a href="daftar_mentor" class="sidebar-link">
                    <i class="fa-solid fa-address-card"></i>  
                    <span>Daftar Mentor Bisnis</span>
                    </a>
                </li>
                <li class="sidebar-item <?php echo ($activePage == 'daftar_kelompok_bisnis_admin') ? 'active' : ''; ?>">
                    <a href="daftar_kelompok_bisnis" class="sidebar-link">
                        <i class="fa-solid fa-users"></i>
                        <span>Daftar Kelompok Bisnis</span>
                    </a>
                </li>
                <li class="sidebar-item <?php echo ($activePage == 'daftar_penuh_proposal') ? 'active' : ''; ?>">
                    <a href="daftar_penuh_proposal" class="sidebar-link">
                        <i class="fa-solid fa-file"></i>
                        <span>Daftar Proposal Bisnis</span>
                    </a>
                </li>
                <li class="sidebar-item <?php echo ($activePage == 'tahun_akademik') ? 'active' : ''; ?>">
                    <a href="tahun_akademik" class="sidebar-link">
                        <i class="fa-solid fa-calendar"></i>
                        <span>Tahun Akademik</span>
                    </a>
                </li>
                <li class="sidebar-item sign-out">
                    <a href="/Entree/dashboard" class="sidebar-link">
                        <i class="fa-solid fa-sign-out"></i>
                        <span>Keluar</span>
                    </a>
                </li>
            </ul>
        </aside>
    </div>
    <script>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="script.js"></script>
    <script type="text/javascript" src="/Entree/assets/js/hamburger.js"></script>
    
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