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

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: /Entree/auth/login/loginform.php");
    exit;
}

// Get user_id from session
$user_id = $_SESSION['user_id'];  // Assuming user_id is stored in session

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the new password and confirm password
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validate password match and strength
    if ($newPassword !== $confirmPassword) {
        // Passwords don't match, redirect back with error
        $_SESSION['error'] = 'Kata sandi dan konfirmasi kata sandi harus sama!';
        header("Location: /Entree/user/profile.php");
        exit;
    }

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Prepare SQL query to update the password based on user_id
    $sql = "UPDATE users SET password = ? WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $hashedPassword, $user_id);  // Changed username to user_id

        // Execute the query
        if ($stmt->execute()) {
            // Password updated successfully
            $_SESSION['success'] = 'Kata sandi berhasil diperbarui!';
        } else {
            // Error updating password
            $_SESSION['error'] = 'Terjadi kesalahan saat memperbarui kata sandi.';
        }

        $stmt->close();
    } else {
        // Error preparing the statement
        $_SESSION['error'] = 'Terjadi kesalahan pada server.';
    }

    // Redirect back to the profile page
    header("Location: profil");
    exit;
}
?>