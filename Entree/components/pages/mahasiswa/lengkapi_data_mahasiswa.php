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

$user_id = $_SESSION['user_id'];

$query_user = "SELECT role FROM users WHERE id = '$user_id'";
$result_user = $conn->query($query_user);

if ($result_user && $result_user->num_rows > 0) {
    $user = $result_user->fetch_assoc();
    $role = $user['role'];
} else {
    die("User tidak ditemukan atau role tidak valid.");
}

if ($role == 'Mahasiswa') {
    $query = "SELECT * FROM mahasiswa WHERE user_id = '$user_id'";
} else {
    die("Role tidak valid.");
}

function getFakultasProdi($npm) {
    $kodeProdi = substr($npm, 0, 3);

    $prodi = '';
    $fakultas = '';

    switch ($kodeProdi) {
        case '110':
            $prodi = 'Kedokteran';
            $fakultas = 'Kedokteran';
            break;
        case '111':
            $prodi = 'Kedokteran Gigi';
            $fakultas = 'Kedokteran';
            break;
        case '120':
            $prodi = 'Manajemen';
            $fakultas = 'Ekonomi';
            break;
        case '121':
            $prodi = 'Akuntansi';
            $fakultas = 'Ekonomi';
            break;
        case '130':
            $prodi = 'Ilmu Hukum';
            $fakultas = 'Hukum';
            break;
        case '140':
            $prodi = 'Teknik Informatika';
            $fakultas = 'Teknologi Informasi';
            break;
        case '150':
            $prodi = 'Ilmu Perpustakaan';
            $fakultas = 'Teknologi Informasi';
            break;
        case '160':
            $prodi = 'Psikologi';
            $fakultas = 'Psikologi';
            break;
        default:
            $prodi = 'Prodi tidak ditemukan';
            $fakultas = 'Fakultas tidak ditemukan';
    }

    return ['fakultas' => $fakultas, 'prodi' => $prodi];
}

$fakultas = '';
$prodi = '';
if (isset($_SESSION['npm'])) {
    $fakultasProdi = getFakultasProdi($_SESSION['npm']);
    $fakultas = $fakultasProdi['fakultas'];
    $prodi = $fakultasProdi['prodi'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $npm = $_POST['npm'];
    $alamat = $_POST['alamat'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $insert_query = "INSERT INTO mahasiswa (user_id, nama, npm, program_studi, email, contact, fakultas, alamat) VALUES ('$user_id', '$nama', '$npm', '$prodi', '$email', '$contact', '$fakultas', '$alamat')";
    $conn->query($insert_query);
        
    // Update first_login pada tabel users
    $update_user_query = "UPDATE users SET first_login = 0 WHERE id = '$user_id'";
    $conn->query($update_user_query);

    // Redirect ke halaman mahasiswa
    header("Location: /Entree/mahasiswa/dashboard");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lengkapi Data Mahasiswa | Entree</title>
    <link rel="icon" href="\Entree\assets\img\icon_favicon.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&amp;display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="/Entree/assets/css/lengkapi_dataa.css">
</head>
<body>
    <div class="container">
        <div class="image-container">
            <img alt="Illustration of user registration" src="/Entree/assets/img/background.png" />
        </div>
        
        <div class="form-container">
            <h2>Lengkapi Data Anda Sebagai <?= ucfirst($role) ?></h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nama">Nama</label>
                    <input id="nama" name="nama" type="text" value="<?= htmlspecialchars($_SESSION['displayName']) ?>" readonly />
                </div>
                <div class="form-group">
                    <label for="npm">NPM</label>
                    <input id="npm" name="npm" type="text" value="<?= htmlspecialchars($_SESSION['npm']) ?>" />
                </div>
                <div class="form-group">
                    <label for="fakultas">Fakultas</label>
                    <input id="fakultas" name="fakultas" type="text" value="<?= htmlspecialchars($fakultas) ?>" readonly />
                </div>
                <div class="form-group">
                    <label for="program_studi">Program Studi</label>
                    <input id="program_studi" name="program_studi" type="text" value="<?= htmlspecialchars($prodi) ?>" readonly />
                </div>
                <div class="form-group">
                    <label for="contact">Nomor Telepon</label>
                    <input id="contact" name="contact" type="text" value="<?= htmlspecialchars($_SESSION['contact']) ?>" readonly/>
                </div>
                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <input id="alamat" name="alamat" type="text" value="<?= htmlspecialchars($_SESSION['street']) ?>" required />
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>" required />
                </div>       
                <button type="submit" class="submit-btn">Tambahkan</button>
            </form>
        </div>
    </div>
</body>

</html>


