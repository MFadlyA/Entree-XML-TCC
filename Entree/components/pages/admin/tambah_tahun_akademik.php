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

// Ambil data dari form
$tahun_awal = $_POST['tahun_awal'];
$jenis_tahun = $_POST['jenis_tahun'];
$status = $_POST['status'];

// Cek apakah data tahun akademik sudah ada
$sql_cek = "SELECT * FROM tahun_akademik WHERE tahun = '$tahun_awal' AND jenis_tahun = '$jenis_tahun'";
$result_cek = $conn->query($sql_cek);

if ($result_cek->num_rows > 0) {
    // Data sudah ada, tampilkan pesan error
    echo "<script>
            alert('Tahun Akademik dengan data yang sama sudah ada!');
            window.location.href = 'tahun_akademik.php';
          </script>";
} else {
    // Jika status adalah 'Aktif', nonaktifkan tahun akademik yang sudah aktif sebelumnya
    if ($status == 'Aktif') {
        $sql_update = "UPDATE tahun_akademik SET status = 'Tidak Aktif' WHERE status = 'Aktif'";
        if ($conn->query($sql_update) === TRUE) {
            // Berhasil menonaktifkan tahun akademik yang aktif
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }

    // Query untuk menambahkan tahun akademik baru
    $sql = "INSERT INTO tahun_akademik (tahun, jenis_tahun, status) VALUES ('$tahun_awal', '$jenis_tahun', '$status')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
                alert('Tahun Akademik berhasil ditambahkan!');
                window.location.href = 'tahun_akademik';
            </script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>