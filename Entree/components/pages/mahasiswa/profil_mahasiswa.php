<?php
session_start();

include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /Entree/login');
    exit;
}

// Cek apakah role pengguna sesuai
if ($_SESSION['role'] !== 'Mahasiswa') {
    header('Location: /Entree/login');
    exit;
}

$user_id = $_SESSION['user_id']; // Ambil user_id dari session

// Mengambil data mahasiswa dari database berdasarkan user_id
$query_mahasiswa = "SELECT * FROM mahasiswa WHERE user_id = '$user_id'";
$result_mahasiswa = $conn->query($query_mahasiswa);

if ($result_mahasiswa && $result_mahasiswa->num_rows > 0) {
    $mahasiswa = $result_mahasiswa->fetch_assoc();
} else {
    die("Data mahasiswa tidak ditemukan.");
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="/Entree/assets/css/profil.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>

<body>
    <div class="wrapper">
        <?php 
        $activePage = 'profil'; 
        include 'sidebar_mahasiswa.php'; 
        ?>

        <div class="main p-3">
            <div class="main_header">
                <?php 
                    $pageTitle = "Profil"; 
                    include 'header_mahasiswa.php'; 
                ?>
            </div>

            <div class="main_wrapper">
                <button class="edit-btn" type="button" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Edit Profil" >
                    <i class="fas fa-edit"></i>
                </button>
                <div class="profile-header">
                    <div class="profile-item">
                        <h2>Nama Pengguna</h2>
                        <p><?= htmlspecialchars($_SESSION['username']); ?></p>
                    </div>
                </div>

                <div class="profile-grid">
                    <div class="profile-item">
                        <h2>Nama Lengkap</h2>
                        <p><?= htmlspecialchars($mahasiswa['nama'] ?? 'Belum diisi'); ?></p>
                    </div>
                    <div class="profile-item">
                        <h2>NPM</h2>
                        <p><?= htmlspecialchars($mahasiswa['npm'] ?? 'Belum diisi'); ?></p>
                    </div>
                    <div class="profile-item">
                        <h2>Fakultas</h2>
                        <p><?= htmlspecialchars($mahasiswa['fakultas'] ?? 'Belum diisi'); ?></p>
                    </div>
                    <div class="profile-item">
                        <h2>Program Studi</h2>
                        <p><?= htmlspecialchars($mahasiswa['program_studi'] ?? 'Belum diisi'); ?></p>
                    </div>
                    <div class="profile-item">
                        <h2>Alamat Email</h2>
                        <p><?= htmlspecialchars($mahasiswa['email'] ?? 'Belum diisi'); ?></p>
                    </div>
                    <div class="profile-item" id="phone-item">
                        <h2>Nomor Telepon</h2>
                        <p class="phone-text"><?= htmlspecialchars($mahasiswa['contact'] ?? 'Belum diisi'); ?></p>
                        <input type="text" class="phone-input" style="display: none;" value="<?= htmlspecialchars($mahasiswa['contact'] ?? ''); ?>">
                    </div>
                    <div class="profile-item" id="alamat-item">
                        <h2>Alamat</h2>
                        <p class="alamat-text"><?= htmlspecialchars($mahasiswa['alamat'] ?? 'Belum diisi'); ?></p>
                        <input type="text" class="alamat-input" style="display: none; width:100%;" value="<?= htmlspecialchars($mahasiswa['alamat'] ?? ''); ?>">
                    </div>
                </div>

                <div class="action-buttons" style="display: none;">
                    <button class="save-btn">Simpan</button>
                    <button class="cancel-btn">Batal</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('.edit-btn').addEventListener('click', function () {
            const phoneText = document.querySelector('.phone-text');
            const phoneInput = document.querySelector('.phone-input');
            const alamatText = document.querySelector('.alamat-text');
            const alamatInput = document.querySelector('.alamat-input');
            const actionButtons = document.querySelector('.action-buttons');
                
            phoneText.style.display = 'none';
            phoneInput.style.display = 'block';
            alamatText.style.display = 'none';
            alamatInput.style.display = 'block';
            actionButtons.style.display = 'flex';

            phoneInput.focus();
        });

        document.querySelector('.save-btn').addEventListener('click', function () {
            const phoneText = document.querySelector('.phone-text');
            const phoneInput = document.querySelector('.phone-input');
            const alamatText = document.querySelector('.alamat-text');
            const alamatInput = document.querySelector('.alamat-input');
            const actionButtons = document.querySelector('.action-buttons');

            const newPhone = phoneInput.value;
            const newAlamat = alamatInput.value;

            // Validasi input nomor telepon
            if (!newPhone.match(/^\d{10,15}$/)) {
                showToast('Nomor telepon tidak valid. Masukkan 10-15 digit angka.', 'error');
                return;
            }

            fetch('/Entree/components/pages/mahasiswa/update_phone.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ phone: newPhone, alamat: newAlamat })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    phoneText.textContent = newPhone || 'Belum diisi';
                    alamatText.textContent = newAlamat || 'Belum diisi';
                    showToast(data.message, 'success');
                } else {
                    showToast(`Error: ${data.message}`, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan saat memperbarui data.', 'error');
            })
            .finally(() => {
                phoneText.style.display = 'block';
                phoneInput.style.display = 'none';
                alamatText.style.display = 'block';
                alamatInput.style.display = 'none';
                actionButtons.style.display = 'none';
            });
        });

        document.querySelector('.cancel-btn').addEventListener('click', function () {
            const phoneText = document.querySelector('.phone-text');
            const phoneInput = document.querySelector('.phone-input');
            const alamatText = document.querySelector('.alamat-text');
            const alamatInput = document.querySelector('.alamat-input');
            const actionButtons = document.querySelector('.action-buttons');

            // Kembalikan tampilan awal
            phoneInput.value = phoneText.textContent.trim();
            alamatInput.value = alamatText.textContent.trim();
            phoneText.style.display = 'block';
            phoneInput.style.display = 'none';
            alamatText.style.display = 'block';
            alamatInput.style.display = 'none';
            actionButtons.style.display = 'none';
        });

        function showToast(message, type) {
            Toastify({
                text: message,
                duration: 3000,
                close: true,
                gravity: 'top',
                position: 'center',
                backgroundColor: type === 'success' ? 'green' : 'red',
            }).showToast();
        }
    </script>
</body>

</html>
