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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mentor_id = $_POST['mentor_id'];
    $action = $_POST['action'];

    // Ambil user_id dari tabel mentor
    $query = "SELECT user_id FROM mentor WHERE id = '$mentor_id'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $mentor = $result->fetch_assoc();
        $user_id = $mentor['user_id'];

        // Ambil role dari tabel users
        $role_query = "SELECT role FROM users WHERE id = '$user_id'";
        $role_result = $conn->query($role_query);

        if ($role_result->num_rows > 0) {
            $user = $role_result->fetch_assoc();
            $current_role = $user['role'];

            // Tentukan role baru berdasarkan aksi
            if ($action === 'naik' && $current_role === 'Tutor') {
                $new_role = 'Dosen Pengampu';
            } elseif ($action === 'turun' && $current_role === 'Dosen Pengampu') {
                $new_role = 'Tutor';
            } else {
                $_SESSION['message'] = "Aksi tidak valid.";
                header("Location: daftar_mentor");
                exit;
            }

            // Update role di tabel users
            $update_query = "UPDATE users SET role = '$new_role' WHERE id = '$user_id'";
            if ($conn->query($update_query)) {
                $_SESSION['message'] = "Role berhasil diperbarui.";
            } else {
                $_SESSION['message'] = "Terjadi kesalahan saat memperbarui role.";
            }
        } else {
            $_SESSION['message'] = "User tidak ditemukan.";
        }
    } else {
        $_SESSION['message'] = "Mentor tidak ditemukan.";
    }

    header("Location: daftar_mentor");
    exit;
}
