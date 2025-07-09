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

$username = $_SESSION['username'] ?? 'Belum diisi';

$showToast = false;
if (!isset($_SESSION['show_toast'])) {
    $showToast = true;
    $_SESSION['show_toast'] = true;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda | Entree</title>
    <link rel="icon" href="\Entree\assets\img\icon_favicon.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/77a99d5f4f.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link href="\Entree\assets\css\page.css" rel="stylesheet" />
</head>

<body>
    <div class="wrapper">
        <?php 
        $activePage = 'pageadmin'; // Halaman ini aktif
        include 'sidebar_admin.php'; 
        ?>

        <div class="main p-3">

            <div class="main_header">
                <?php 
                    $pageTitle = "Beranda"; // Judul halaman
                    include 'header_admin.php'; 
                ?>
            </div>

            <div class="main_wrapper">
                 <!-- Hero Section -->
                 <header class="bg-success text-white text-center py-5">
                    <div class="container">
                        <img src="\Entree\assets\img\icon_entree_beranda.png" 
                            class="rounded me-2" 
                            alt="Logo">
                    </div>
                </header>

                <!-- Highlight Features Section -->
                <section id="features" class="py-5 pb-0">
                    <div class="container text-center">
                        <h2 class="mb-4">Fitur Unggulan</h2>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="p-4 border rounded">
                                    <i class="fa-solid fa-address-card icon" data-bs-target="#exampleModalToggle_mentor" data-bs-toggle="modal"></i>
                                    <h5 class="mt-3">Mentor Bisnis</h5>
                                    <p>Dapatkan bimbingan dari para ahli di bidangnya.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-4 border rounded">
                                    <i class="fa-solid fa-people-group icon" data-bs-target="#exampleModalToggle_inkubasi" data-bs-toggle="modal"></i>
                                    <h5 class="mt-3">Program Inkubasi</h5>
                                    <p>Melangkah lebih jauh bersama Program Inkubasi</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-4 border rounded">
                                    <i class="fa-solid fa-book icon" data-bs-target="#exampleModalToggle_materi" data-bs-toggle="modal"></i>
                                    <h5 class="mt-3">Materi Kewirausahaan</h5>
                                    <p>Belajar bersama kami untuk mengembangkan bisnis mu!</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <!-- Modal Mentor -->
        <div class="modal fade" id="exampleModalToggle_mentor" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalToggleLabel">Mentor Bisnis</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Dengan ada nya Mentor Bisnis di aplikasi Entree memungkinkan mahasiswa untuk mendapatkan bimbingan dari mentor berpengalaman yang telah teruji dalam dunia bisnis. Dengan dukungan para ahli ini, mahasiswa dapat menerima saran dan arahan yang praktis untuk mengembangkan ide bisnis mereka menjadi usaha yang sukses.
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" data-bs-target="#exampleModalToggle_mentor_2" data-bs-toggle="modal">Selanjutnya</button>
                </div>
                </div>
            </div>
            </div>
            <div class="modal fade" id="exampleModalToggle_mentor_2" aria-hidden="true" aria-labelledby="exampleModalToggleLabel2" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalToggleLabel2">Mentor Bisnis</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Tekan tombol dibawah ini untuk melihat daftar Mentor Bisnis aplikasi Entree, atau kamu juga bisa mengaksesnya langsung dari sidebar yang ada!. 
                </div>
                <a href="daftar_mentor" class="btn btn-success mx-5 mb-3" role="button">Kunjungi Halaman</a>
                <div class="modal-footer">
                    <button class="btn btn-success" data-bs-target="#exampleModalToggle_mentor" data-bs-toggle="modal">Kembali</button>
                </div>
                </div>
            </div>
            </div>
        </div>

        <!-- Modal inkubasi -->
        <div class="modal fade" id="exampleModalToggle_inkubasi" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalToggleLabel">Program Inkubasi</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Program inkubasi adalah program yang dirancang untuk mendukung pertumbuhan dan perkembangan bisnis baru atau startup melalui penyediaan layanan dan sumber daya yang dibutuhkan bagi mahasiswa aktif Universitas YARSI yang memiliki ide bisnis atau bisnis yang sudah berjalan. 

                </div>
                <div class="modal-footer">
                    <!-- <button class="btn btn-success" data-bs-target="#exampleModalToggle_inkubasi_2" data-bs-toggle="modal">Selanjutnya</button> -->
                </div>
                </div>
            </div>
            </div>
            <div class="modal fade" id="exampleModalToggle_inkubasi_2" aria-hidden="true" aria-labelledby="exampleModalToggleLabel2" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalToggleLabel2">Program Inkubasi</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Hide this modal and show the first with the button below.
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" data-bs-target="#exampleModalToggle_inkubasi" data-bs-toggle="modal">Kembali</button>
                </div>
                </div>
            </div>
            </div>
        </div>

        <!-- Modal materi -->
        <div class="modal fade" id="exampleModalToggle_materi" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalToggleLabel">Materi Kewirausahaan</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Aplikasi Entree menyediakan akses ke berbagai materi pembelajaran yang dirancang untuk memperluas wawasan mahasiswa dalam bidang kewirausahaan. Materi ini mencakup konten video, dan dokumen pembelajaran yang dirancang untuk mendukung pengembangan keterampilan bisnis dan pemahaman mereka tentang dinamika pasar.
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" data-bs-target="#exampleModalToggle_materi_2" data-bs-toggle="modal">Selanjutnya</button>
                </div>
                </div>
            </div>
            </div>
            <div class="modal fade" id="exampleModalToggle_materi_2" aria-hidden="true" aria-labelledby="exampleModalToggleLabel2" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalToggleLabel2">Materi Kewirausahaan</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Ayo Belajar! Tekan tombol dibawah ini untuk masuk ke halaman Materi Kewirausahaan, atau kamu juga bisa mengaksesnya langsung dari sidebar yang ada!.
                </div>
                <a href="materi_kewirausahaan" class="btn btn-success mx-5 mb-3" role="button">Kunjungi Halaman</a>
                <div class="modal-footer">
                    <button class="btn btn-success" data-bs-target="#exampleModalToggle_materi" data-bs-toggle="modal">Kembali</button>
                </div>
                </div>
            </div>
            </div>
        </div>
    </div>

    <?php if ($showToast): ?>
    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1055;">
        <div class="toast" id="myToast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <img src="\Entree\assets\img\icon_entree_pemberitahuan.png" style="width:40%; height:40%"; class="rounded me-2" alt="Logo">
                <strong class="me-auto"></strong>
                <small>Just now</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                Hallo! Selamat datang <?= htmlspecialchars($username); ?> di Aplikasi Entree!
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const toastEl = document.getElementById('myToast');
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        });
    </script>
    <?php endif; ?>


           
            
    </div>
</body>

</html>