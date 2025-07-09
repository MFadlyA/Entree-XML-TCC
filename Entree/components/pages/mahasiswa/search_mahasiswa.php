<?php
session_start();
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

if (isset($_GET['npm'])) {
    $npm = $_GET['npm'];

    $query = "SELECT * FROM mahasiswa WHERE npm LIKE '%$npm%'";
    $result = mysqli_query($conn, $query);

    $members = [];
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $members[] = [
                'npm' => $row['npm'],
                'nama' => $row['nama']
            ];
        }
    }

    echo json_encode(['success' => true, 'members' => $members]);
} else {
    echo json_encode(['success' => false, 'message' => 'No NPM provided']);
}
?>
