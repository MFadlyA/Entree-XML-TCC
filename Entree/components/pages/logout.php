<?php
// Mulai session jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set zona waktu Indonesia (Jakarta)
date_default_timezone_set('Asia/Jakarta');

// Cek jika ada permintaan logout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout'])) {
    include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';
    
    // Ambil username pengguna yang sedang login
    $username = $_SESSION['username'];
    
    // Tentukan waktu logout
    $waktuLogout = date("Y-m-d H:i:s");

    // Query untuk memperbarui waktu logout di log_activity
    $updateLogoutQuery = "
        UPDATE log_activity
        SET logout_time = '$waktuLogout'
        WHERE username = '$username' AND logout_time IS NULL
    ";

    // Eksekusi query
    if (mysqli_query($conn, $updateLogoutQuery)) {
        // Hapus session pengguna setelah logout
        session_unset();
        session_destroy();

        // Arahkan pengguna kembali ke halaman login atau halaman utama setelah logout
        header("Location: /Entree/components/pages/startdashboard/dashboardawal.php");
        exit();
    } else {
        // Jika query gagal, beri pesan error
        echo "Error: " . mysqli_error($conn);
    }
}
?>
