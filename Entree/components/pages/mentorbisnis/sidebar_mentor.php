<?php
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: /Entree/login');
        exit;
    }
    
    // Cek apakah role pengguna sesuai
    if ($_SESSION['role'] !== 'Tutor' && $_SESSION['role'] !== 'Dosen Pengampu') {
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
                <li class="sidebar-item <?php echo ($activePage == 'profil_mentor') ? 'active' : ''; ?>">
                    <a href="profil" class="sidebar-link">
                        <i class="fa-solid fa-user"></i>
                        <span>Profil</span>
                    </a>
                </li>
                <li class="sidebar-item <?php echo ($activePage == 'pagementor') ? 'active' : ''; ?>">
                    <a href="dashboard" class="sidebar-link">
                        <i class="fa-solid fa-house"></i>
                        <span>Beranda</span>
                    </a>
                </li>
                <li class="sidebar-item <?php echo ($activePage == 'materikewirausahaan_mentor') ? 'active' : ''; ?>">
                    <a href="materi_kewirausahaan" class="sidebar-link">
                        <i class="fa-solid fa-book"></i>
                        <span>Materi Kewirausahaan</span>
                    </a>
                </li>
                <li class="sidebar-item <?php echo ($activePage == 'daftar_mentor_mentor') ? 'active' : ''; ?>">
                    <a href="daftar_mentor" class="sidebar-link">
                        <i class="fa-solid fa-address-card"></i>  
                        <span>Daftar Mentor Bisnis</span>
                    </a>
                </li>
                <hr>
                <li class="sidebar-header">
                    <h1>Mentor Bisnis</h1>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                    data-bs-target="#kelola_bisnis_kelompok" aria-expanded="false" aria-controls="kelola_bisnis_kelompok">
                        <i class="fa-solid fa-user-pen"></i>
                        <span>Kelola Bimbingan</span>
                    </a>
                    <ul id="kelola_bisnis_kelompok" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item <?php echo ($activePage == 'daftar_kelompok_bisnis_mentor') ? 'active' : ''; ?>">
                            <a href="daftar_kelompok_bisnis" class="sidebar-link">
                                <i class="fa-solid fa-users"></i>
                                Daftar Kelompok Bisnis
                            </a>
                        </li>
                        <li class="sidebar-item <?php echo ($activePage == 'jadwal_bimbingan') ? 'active' : ''; ?>">
                            <a href="jadwal_bimbingan" class="sidebar-link">
                                <i class="fa-solid fa-calendar"></i>
                                Jadwal Bimbingan
                            </a>
                        </li>
                    </ul>
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