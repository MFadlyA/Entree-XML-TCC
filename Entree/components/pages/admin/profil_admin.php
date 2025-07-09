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

if (!isset($_SESSION['username'])) {
    header("Location: /Entree/auth/login/loginform.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil | Entree</title>
    <link rel="icon" href="\Entree\assets\img\icon_favicon.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/77a99d5f4f.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="/Entree/assets/css/profil.css">
</head>

<body>
    <div class="wrapper">
        <?php 
        $activePage = 'profil'; 
        include 'sidebar_admin.php'; 
        ?>

        <div class="main p-3">
            <div class="main_header">
                <?php 
                    $pageTitle = "Profil"; 
                    include 'header_admin.php'; 
                ?>
            </div>

            <div class="main_wrapper">
                <button class="edit-btn" type="button" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Edit Profil">
                    <i class="fas fa-edit"></i>
                </button>
                <!-- Toast Success -->
                <div class="toast align-items-center text-white bg-success border-0" id="toastSuccess" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">                    <div class="d-flex">
                        <div class="toast-body">
                            <?php echo isset($_SESSION['success']) ? $_SESSION['success'] : ''; ?>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>

                <!-- Toast Error -->
                <div class="toast align-items-center text-white bg-success border-0" id="toastError" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">                    <div class="d-flex">
                        <div class="toast-body">
                            <?php echo isset($_SESSION['error']) ? $_SESSION['error'] : ''; ?>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
                <div class="profile-header">
                    <div class="profile-item">
                        <h2>Nama Pengguna</h2>
                        <p><?= htmlspecialchars($_SESSION['username']); ?></p>
                    </div>
                </div>
                <button class="btn change-password-btn" style="background-color: #2ea56f; color: white; display: none;" 
                    data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    Ganti Kata Sandi
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Ganti Kata Sandi -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">Ganti Kata Sandi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="process_change_password" method="POST" onsubmit="return validatePassword()">
                    <div class="modal-body">
                        <!-- Input Password Baru -->
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">Kata Sandi Baru</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="newPassword" name="new_password" required>
                                <span class="input-group-text" id="togglePassword1" style="cursor: pointer;">
                                    <i class="bi bi-eye"></i> <!-- Ikon mata -->
                                </span>
                            </div>
                            <small class="form-text text-muted">
                                Pastikan password baru Anda memiliki minimal 6 karakter, menggabungkan huruf besar, huruf kecil, angka, dan simbol, serta tidak mengandung informasi pribadi yang mudah ditebak.
                            </small>
                        </div>

                        <!-- Konfirmasi Password Baru -->
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Konfirmasi Kata Sandi Baru</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                                <span class="input-group-text" id="togglePassword2" style="cursor: pointer;">
                                    <i class="bi bi-eye"></i> <!-- Ikon mata -->
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-cancel" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-secondary btn-submit">Simpan Perubahan</button>
                    </div>
                    <!-- Modal untuk Password Kurang dari 6 Karakter -->
                    <div class="modal fade" id="passwordLengthModal" tabindex="-1" aria-labelledby="passwordLengthModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="passwordLengthModalLabel">Password Terlalu Pendek</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Password baru harus memiliki minimal 6 karakter.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                        </div>
                    </div>
                    </div>

                    <!-- Modal untuk Password Tidak Memenuhi Kriteria -->
                    <div class="modal fade" id="passwordCriteriaModal" tabindex="-1" aria-labelledby="passwordCriteriaModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="passwordCriteriaModalLabel">Password Tidak Memenuhi Kriteria</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Password baru harus menggabungkan huruf besar, huruf kecil, angka, dan simbol.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                        </div>
                    </div>
                    </div>

                    <!-- Modal untuk Password Tidak Cocok -->
                    <div class="modal fade" id="passwordMismatchModal" tabindex="-1" aria-labelledby="passwordMismatchModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="passwordMismatchModalLabel">Password Tidak Cocok</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Password baru dan konfirmasi password tidak cocok.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                        </div>
                    </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Pemberitahuan (Alert) -->
    <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="alertModalLabel">Pemberitahuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Kata sandi dan konfirmasi kata sandi harus sama!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tambahkan script untuk validasi dan toggle password visibility -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js"></script>
    <script>
        document.getElementById('togglePassword1').addEventListener('click', function() {
            var passwordField = document.getElementById('newPassword');
            var icon = this.querySelector('i');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });

        document.getElementById('togglePassword2').addEventListener('click', function() {
            var passwordField = document.getElementById('confirmPassword');
            var icon = this.querySelector('i');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });

        // Validate password match and strength
        function validatePassword() {
            var newPassword = document.getElementById('newPassword').value;
            var confirmPassword = document.getElementById('confirmPassword').value;

            // Password Strength Validation (minimum 6 characters, includes uppercase, lowercase, number, symbol)
            var passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/;
            
            // Check password length
            if (newPassword.length < 6) {
                var passwordLengthModal = new bootstrap.Modal(document.getElementById('passwordLengthModal'));
                passwordLengthModal.show();
                return false;
            }

            // Check password pattern
            if (!passwordPattern.test(newPassword)) {
                var passwordCriteriaModal = new bootstrap.Modal(document.getElementById('passwordCriteriaModal'));
                passwordCriteriaModal.show();
                return false;
            }

            // Check if passwords match
            if (newPassword !== confirmPassword) {
                var passwordMismatchModal = new bootstrap.Modal(document.getElementById('passwordMismatchModal'));
                passwordMismatchModal.show();
                return false;
            }

            return true;
        }
    </script>
    <script>
        // JavaScript untuk menampilkan tombol "Ganti Kata Sandi"
        document.querySelector('.edit-btn').addEventListener('click', function () {
            const changePasswordBtn = document.querySelector('.change-password-btn');
            changePasswordBtn.style.display = 'block'; // Tampilkan tombol "Ganti Kata Sandi"
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            <?php if (isset($_SESSION['success'])): ?>
                // Show success toast
                var toast = new bootstrap.Toast(document.getElementById('toastSuccess'));
                toast.show();
                <?php unset($_SESSION['success']); ?> // Clear session after showing
            <?php elseif (isset($_SESSION['error'])): ?>
                // Show error toast
                var toast = new bootstrap.Toast(document.getElementById('toastError'));
                toast.show();
                <?php unset($_SESSION['error']); ?> // Clear session after showing
            <?php endif; ?>
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
