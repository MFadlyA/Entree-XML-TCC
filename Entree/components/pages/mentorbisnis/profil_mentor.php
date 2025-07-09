<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /Entree/login');
    exit;
}

// Cek apakah role pengguna sesuai
if ($_SESSION['role'] !== 'Tutor' && $_SESSION['role'] !== 'Dosen Pengampu') {
    header('Location: /Entree/login');
    exit;
}
include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['username'])) {
    header("Location: /Entree/auth/login/loginform.php");
    exit;
}

// Ambil data mentor beserta perannya dari tabel mentor dan users
$query_mentor = "
    SELECT mentor.*, users.role AS peran 
    FROM mentor 
    JOIN users ON mentor.user_id = users.id
    WHERE users.username = ?";
$stmt = $conn->prepare($query_mentor);
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result_mentor = $stmt->get_result();

if ($result_mentor->num_rows > 0) {
    $mentor = $result_mentor->fetch_assoc();
} else {
    die("Mentor tidak ditemukan.");
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
</head>

<body>
    <div class="wrapper">
        <?php
        $activePage = 'profil_mentor'; // Halaman ini adalah Profil
        include 'sidebar_mentor.php'; 
        ?>

        <div class="main p-3">
            <div class="main_header">
                <?php 
                    $pageTitle = "Profil"; // Judul halaman
                    include 'header_mentor.php'; 
                ?>
            </div>

            <div class="main_wrapper">
                <button class="edit-btn" type="button" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Edit Profil">
                    <i class="fas fa-edit"></i>
                </button>
                <!-- Profile Header with Edit Photo -->
                <div class="profile-header">
                    <div class="profile-item">
                        <?php
                            $profile_img = !empty($mentor['foto_profile']) ? $mentor['foto_profile'] : '\Entree\assets\img\default-profile-mentor.png';
                        ?>
                        <img alt="Profile picture of the mentor" height="50" width="50" id="profile-photo"
                            src="<?= htmlspecialchars($profile_img) ?>" 
                            onerror="this.src='<?= htmlspecialchars($profile_img) ?>'"/>
                        <button class="edit-photo-btn btn btn-outline-success mt-2" type="button" style="display: none;">
                            <i class="fas fa-camera"></i> Edit Foto
                        </button>
                        <!-- Hidden file input for uploading image -->
                        <input type="file" id="profile-photo-input" style="display: none;" accept="image/png, image/jpeg, image/gif"/>
                    </div>
                    <div class="profile-item">
                        <h2>Nama Pengguna</h2>
                        <p><?= htmlspecialchars($_SESSION['username']); ?></p>
                    </div>
                </div>
                <!-- Grid profile data -->
                <div class="profile-grid">
                    <div class="profile-item">
                        <h2>Nama Lengkap</h2>
                        <p><?= htmlspecialchars($mentor['nama']); ?></p>
                    </div>
                    <div class="profile-item">
                        <h2>NIK</h2>
                        <p><?= htmlspecialchars($mentor['nidn']); ?></p>
                    </div>
                    <div class="profile-item">
                        <h2>Peran</h2>
                        <p><?= htmlspecialchars($mentor['peran']); ?></p>
                    </div>
                    <div class="profile-item">
                        <h2>Keahlian</h2>
                        <p class="skill-text"><?= htmlspecialchars($mentor['keahlian']); ?></p>
                        <input type="text" class="skill-input" style="display: none;" value="<?= htmlspecialchars($mentor['keahlian']); ?>">
                    </div>
                    <div class="profile-item">
                        <h2>Fakultas</h2>
                        <p><?= htmlspecialchars($mentor['fakultas']); ?></p>
                    </div>
                    <div class="profile-item">
                        <h2>Program Studi</h2>
                        <p><?= htmlspecialchars($mentor['prodi']); ?></p>
                    </div>
                    <div class="profile-item">
                        <h2>Alamat Email</h2>
                        <p><?= htmlspecialchars($mentor['email']); ?></p>
                    </div>
                    <div class="profile-item">
                        <h2>Nomor Telepon</h2>
                        <p class="phone-text"><?= htmlspecialchars($mentor['contact']); ?></p>
                        <input type="text" class="phone-input" style="display: none;" value="<?= htmlspecialchars($mentor['contact']); ?>">
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
    document.querySelector('.edit-photo-btn').addEventListener('click', function () {
        const photoInput = document.querySelector('#profile-photo-input');
        photoInput.click(); // Menyembunyikan input dan memicu klik
    });

    // Ketika file foto dipilih
    document.querySelector('#profile-photo-input').addEventListener('change', function (event) {
        const file = event.target.files[0];
        if (file) {
            const allowedTypes = ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                alert('Hanya file gambar dengan format PNG, JPG, JPEG, dan GIF yang diperbolehkan.');
                return;
            }
            uploadProfilePicture(file); // Fungsi untuk mengunggah foto
        }
    });

    // Fungsi untuk mengunggah foto ke server
    function uploadProfilePicture(file) {
        const formData = new FormData();
        formData.append('profile_picture', file);

        fetch('/Entree/components/pages/mentorbisnis/upload_photo.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Jika berhasil, tampilkan foto yang baru
                document.querySelector('#profile-photo').src = data.photo_url;
                alert('Foto berhasil diperbarui!');
            } else {
                alert('Terjadi kesalahan saat mengunggah foto.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengunggah foto.');
        });
    }

    document.querySelector('.edit-btn').addEventListener('click', function () {
    // Ambil elemen terkait
        const phoneText = document.querySelector('.phone-text');
        const phoneInput = document.querySelector('.phone-input');
        const skillText = document.querySelector('.skill-text');
        const skillInput = document.querySelector('.skill-input');
        const actionButtons = document.querySelector('.action-buttons');
        const editPhotoBtn = document.querySelector('.edit-photo-btn'); // Ambil tombol edit foto

        // Sembunyikan teks, tampilkan input, dan tombol aksi
        phoneText.style.display = 'none';
        phoneInput.style.display = 'block';
        skillText.style.display = 'none';
        skillInput.style.display = 'block';
        actionButtons.style.display = 'flex';

        // Tampilkan tombol Edit Foto setelah klik Edit
        editPhotoBtn.style.display = 'block';

        // Fokuskan input pertama
        phoneInput.focus();
    });


    // Tombol Simpan
    document.querySelector('.save-btn').addEventListener('click', function () {
        const phoneText = document.querySelector('.phone-text');
        const phoneInput = document.querySelector('.phone-input');
        const skillText = document.querySelector('.skill-text');
        const skillInput = document.querySelector('.skill-input');
        const actionButtons = document.querySelector('.action-buttons');
        const editPhotoBtn = document.querySelector('.edit-photo-btn'); // Ambil tombol edit foto

        // Validasi nomor telepon (10-15 digit angka)
        const newPhone = phoneInput.value.trim();
        if (!newPhone.match(/^\d{10,15}$/)) {
            alert('Nomor telepon tidak valid. Masukkan 10-15 digit angka.');
            return;
        }

        // Ambil nilai keahlian
        const newSkill = skillInput.value.trim();
        if (newSkill === '') {
            alert('Keahlian tidak boleh kosong.');
            return;
        }

        // Perbarui tampilan teks
        phoneText.textContent = newPhone || 'Belum diisi';
        skillText.textContent = newSkill || 'Belum diisi';

        // Sembunyikan input dan tombol aksi
        phoneText.style.display = 'block';
        phoneInput.style.display = 'none';
        skillText.style.display = 'block';
        skillInput.style.display = 'none';
        actionButtons.style.display = 'none';
        editPhotoBtn.style.display = "none";

        // Kirim data ke server menggunakan AJAX
        updateProfile(newPhone, newSkill);
    });

    // Tombol Batal
    document.querySelector('.cancel-btn').addEventListener('click', function () {
        const phoneText = document.querySelector('.phone-text');
        const phoneInput = document.querySelector('.phone-input');
        const skillText = document.querySelector('.skill-text');
        const skillInput = document.querySelector('.skill-input');
        const actionButtons = document.querySelector('.action-buttons');
        const editPhotoBtn = document.querySelector('.edit-photo-btn'); // Ambil tombol edit foto

        // Kembalikan nilai awal
        phoneInput.value = phoneText.textContent.trim();
        skillInput.value = skillText.textContent.trim();

        // Kembalikan tampilan awal
        phoneText.style.display = 'block';
        phoneInput.style.display = 'none';
        skillText.style.display = 'block';
        skillInput.style.display = 'none';
        actionButtons.style.display = 'none';
        editPhotoBtn.style.display = "none";
    });

    // Fungsi untuk kirim data ke server
    function updateProfile(phone, skill) {
        fetch('/Entree/components/pages/mentorbisnis/update_profile.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ phone: phone, skill: skill })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message); // Tampilkan pesan sukses/gagal
        })
        .catch(error => {
            console.error('Error:', error); // Tangani error
        });
    }

    </script>
</body>

</html>