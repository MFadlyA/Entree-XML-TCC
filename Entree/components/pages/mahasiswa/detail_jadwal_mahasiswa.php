<?php
    session_start();
    // Koneksi ke database
    include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';
    require $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/aws_s3_config.php';

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: /Entree/login');
        exit;
    }

    // Cek apakah role pengguna sesuai
    if ($_SESSION['role'] !== 'Mahasiswa') {
        header('Location: /Entree/login');
        exit;
    }

    // Ambil ID dari parameter URL
    $id = isset($_GET['id']) ? $_GET['id'] : null;

    // Ambil ID kelompok pengguna dari database
    $user_id = $_SESSION['user_id'];
    $query = "SELECT id_kelompok FROM mahasiswa WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $user_kelompok_id = $row['id_kelompok']; // ID kelompok pengguna dari database

    // Ambil ID jadwal dari parameter URL
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        header('Location: /Entree/mahasiswa/dashboard.php'); // Redirect jika ID tidak ada di URL
        exit;
    }
    $requested_jadwal_id = intval($_GET['id']);

    // Periksa apakah jadwal milik kelompok pengguna
    $query = "SELECT id_klmpk FROM jadwal WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $requested_jadwal_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header('Location: /Entree/mahasiswa/dashboard'); // Redirect jika jadwal tidak ditemukan
        exit;
    }

    $row = $result->fetch_assoc();
    $jadwal_kelompok_id = $row['id_klmpk'];

    // Periksa apakah ID kelompok dari jadwal cocok dengan ID kelompok pengguna
    if ($jadwal_kelompok_id !== $user_kelompok_id) {
        header('Location: /Entree/mahasiswa/dashboard'); // Redirect jika tidak sesuai
        exit;
    }

    // Jika ID cocok, lanjutkan untuk memproses detail jadwal
    $query = "SELECT * FROM jadwal WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $requested_jadwal_id);
    $stmt->execute();
    $jadwal_result = $stmt->get_result();

    if ($jadwal_result->num_rows === 0) {
        header('Location: /Entree/mahasiswa/dashboard'); // Redirect jika data jadwal tidak ditemukan
        exit;
    }

    if ($id) {
        // Query untuk mengambil detail jadwal
        $sql = "SELECT * FROM jadwal WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Cek apakah data ditemukan
        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
        } else {
            echo "Jadwal tidak ditemukan!";
            exit;
        }
    } else {
        echo "ID tidak valid!";
        exit;
    }

    // Cek jika form disubmit dan status jadwal adalah 'selesai'
    $message = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['bukti_kegiatan']) && $data['status'] == 'selesai') {
        $file = $_FILES["bukti_kegiatan"];
        $fileName = basename($file["name"]);
        $fileTmpPath = $file["tmp_name"];
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
        // Cek tipe file yang diperbolehkan
        $allowedTypes = array("pdf", "jpg", "jpeg", "png", "gif");
        if (in_array($fileType, $allowedTypes)) {
            // Buat key (nama file di S3)
            $key = "bukti_kegiatan_bimbingan/" . time() . "_" . $fileName;
    
            try {
                // Upload ke S3
                $result = $s3->putObject([
                    'Bucket' => $bucketName,
                    'Key' => $key,
                    'SourceFile' => $fileTmpPath,
                    'ContentType' => mime_content_type($fileTmpPath),
                    'ContentDisposition' => 'inline'
                ]);
    
                $fileUrl = $result['ObjectURL']; // URL file di S3
    
                // Simpan URL file ke database
                $sqlUpdate = "UPDATE jadwal SET bukti_kegiatan = ? WHERE id = ?";
                $stmtUpdate = $conn->prepare($sqlUpdate);
                $stmtUpdate->bind_param("si", $fileUrl, $id);
                if ($stmtUpdate->execute()) {
                    $message = "Bukti kegiatan telah dikirim!";
                } else {
                    $message = "Terjadi kesalahan saat menyimpan bukti kegiatan.";
                }
            } catch (Aws\S3\Exception\S3Exception $e) {
                $message = "Upload ke S3 gagal: " . $e->getMessage();
            }
        } else {
            $message = "Format file tidak didukung. Harap unggah PDF atau gambar.";
        }
    }

    // fitur hapus file 
    // Koneksi ke database
    include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';

    // Cek apakah pengguna masuk dan memiliki role 'Mahasiswa'
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'Mahasiswa') {
        header('Location: /Entree/login');
        exit;
    }

    // Ambil ID dari parameter URL
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;

    // Validasi ID
    if ($id === null) {
        header('Location: /Entree/mahasiswa/dashboard');
        exit;
    }

    // Ambil informasi jadwal berdasarkan ID
    $query = "SELECT * FROM jadwal WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
    } else {
        // Redirect jika data jadwal tidak ditemukan
        header('Location: /Entree/mahasiswa/dashboard');
        exit;
    }

    // Tangani penghapusan file
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
        // Ambil informasi file dari database
        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/Entree/components/pages/mahasiswa/uploads/bukti_kegiatan/' . basename($data['bukti_kegiatan']);

        // Hapus file dari server jika ada
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Hapus referensi file dari database
        $queryDelete = "UPDATE jadwal SET bukti_kegiatan = NULL WHERE id = ?";
        $stmtDelete = $conn->prepare($queryDelete);
        $stmtDelete->bind_param("i", $id);
        $stmtDelete->execute();

        // Redirect dengan pesan sukses
        $_SESSION['message'] = "Bukti kegiatan berhasil dihapus.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    $fileUrl = htmlspecialchars($data["bukti_kegiatan"]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Jadwal Bimbingan | Entree</title>
    <link rel="icon" href="\Entree\assets\img\icon_favicon.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/77a99d5f4f.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="/Entree/assets/css/jadwal_bimbingan.css">
    <link rel="stylesheet" href="/Entree/assets/css/detail_jadwal_bimbingan.css">
    <link rel="stylesheet" href="/Entree/assets/css/detail_proposal.css">
</head>

<body>
    <div class="wrapper">
        <?php 
        $activePage = 'jadwal_bimbingan_mahasiswa'; 
        include 'sidebar_mahasiswa.php'; 
        ?>

        <div class="main p-3">
            <div class="main_header">
                <?php 
                    $pageTitle = "Jadwal Bimbingan"; // Judul halaman
                    include 'header_mahasiswa.php'; 
                ?>
            </div>

            <div class="main_wrapper">
                <div class="container mt-5">
                    <h1>Detail Jadwal</h1>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <table class="table table-bordered">
                            <tr>
                                <th>Nama Kegiatan</th>
                                <td><?php echo htmlspecialchars($data['nama_kegiatan']); ?></td>
                            </tr>
                            <tr>
                                <th>Tanggal</th>
                                <td><?php echo htmlspecialchars($data['tanggal']); ?></td>
                            </tr>
                            <tr>
                                <th>Waktu</th>
                                <td><?php echo htmlspecialchars($data['waktu']); ?></td>
                            </tr>
                            <tr>
                                <th>Agenda</th>
                                <td><?php echo htmlspecialchars($data['agenda']); ?></td>
                            </tr>
                            <tr>
                                <th>Lokasi</th>
                                <td><?php echo htmlspecialchars($data['lokasi']); ?></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <?php
                                    if ($data['status'] == 'disetujui') {
                                        echo '<p class="alert alert-success text-white fw-bold text-center m-0 p-2 px-3" style="background-color:#2ea56f; width:fit-content;" role="alert">Disetujui</p>';
                                    } elseif ($data['status'] == 'ditolak') {
                                        echo '<p class="alert alert-danger text-white fw-bold text-center m-0 p-2 px-3" style="background-color:#dc3545; width:fit-content;" role="alert">Ditolak</p>';
                                    } elseif ($data['status'] == 'selesai') {
                                        echo '<p class="alert alert-info text-white fw-bold text-center m-0 p-2 px-3" style="background-color:#007bff; width:fit-content;" role="alert">Selesai</p>';
                                    } elseif ($data['status'] == 'alternatif') {
                                        echo '<p class="alert alert-warning text-white fw-bold text-center m-0 p-2" style="background-color:#ffc107; width:fit-content;" role="alert">Alternatif</p>';
                                    } else {
                                        echo '<p class="alert alert-warning text-white fw-bold text-center m-0 p-2 px-3" style="background-color:orange; width:fit-content;" role="alert">Menunggu</p>';
                                    }
                                ?>
                                </td>
                            </tr>
                            <tr>
                            <th>Bukti Kegiatan</th>
                                <td>
                                    <?php if (!empty($data['bukti_kegiatan'])): ?>
                                        <!-- Tampilkan daftar file jika sudah ada data -->
                                        <ul id="fileList">
                                            <li class="file-box">
                                                <div class="file-info">
                                                    <?php                                                     
                                                    $filename = basename(urldecode($data['bukti_kegiatan']));
                                                    $cleanName = preg_replace('/^\d+_/', '', $filename); // hapus angka + underscore di awal
                                                    echo htmlspecialchars($cleanName);          
                                                    ?>
                                                </div>
                                                <div class="icon-group">
                                                    <!-- Ikon Lihat File -->
                                                    <a href="<?= $fileUrl ?>" 
                                                    target="_blank" 
                                                    class="detail-icon" 
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-custom-class="custom-tooltip" 
                                                    data-bs-title="Lihat File">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                    <!-- Ikon Unduh File -->
                                                    <a href="\Entree\components\pages\download_from_aws\download.php?table=jadwal&id=<?= $data['id'] ?>" 
                                                    download 
                                                    class="btn-icon" 
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-custom-class="custom-tooltip" 
                                                    data-bs-title="Unduh File">
                                                        <i class="fa-solid fa-download"></i>
                                                    </a>
                                                     <!-- Ikon Hapus File -->
                                                     <a href="#" 
                                                        class="btn-icon btn-danger" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteFileModal" 
                                                        data-bs-id="<?php echo $id; ?>" 
                                                        role="button">
                                                            <i class="fa-solid fa-trash"></i>
                                                    </a>
                                                </div>
                                            </li>
                                        </ul>
                                    <?php else: ?>
                                        <!-- Tampilkan input file jika belum ada data -->
                                        <input data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Masukkan Bukti Kegiatan Anda dalam format Pdf atau Gambar di sini"
                                            type="file" 
                                            class="form-control" 
                                            id="customFile" 
                                            name="bukti_kegiatan" 
                                            accept=".pdf, .jpg, .jpeg, .png, .gif" 
                                            data-status="<?php echo htmlspecialchars($data['status']); ?>">
                                        <p id="uploadMessage" class="alert alert-warning mb-2 mt-3 d-none">
                                            <small class="d-flex justify-content-center">
                                                Unggahan file hanya diperbolehkan jika status jadwal adalah "Selesai".
                                            </small>
                                        </p>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if (!empty($data['feedback_mentor'])): ?>
                                <tr>
                                    <th>Umpan Balik dari Mentor Bisnis</th>
                                    <td><?php echo htmlspecialchars($data['feedback_mentor'] ?? 'Belum ada umpan balik.'); ?></td>
                                </tr>
                            <?php endif; ?>
                        </table>

                        <div class="action-buttons  justify-content-end mt-3" style="display: none;">
                            <button type="submit" class="btn btn-success text-white fw-bold me-2" style="background-color:#2ea56f">Simpan</button>
                        </div>

                        <!-- Menampilkan pesan Unggah jika ada -->
                        <?php if (!empty($message)): ?>
                            <div id="message" class="alert alert-success">
                                <?php echo $message; ?>
                            </div>
                            
                            <script type="text/javascript">
                                // Menghilangkan pesan setelah 2 detik (2000 ms)
                                setTimeout(function() {
                                    document.getElementById("message").style.display = "none";
                                }, 3000); // 3000 ms = 3 detik
                            </script>
                        <?php endif; ?>

                        <!-- Tampilkan pesan Hapus jika ada -->
                        <?php if (!empty($_SESSION['message'])): ?>
                            <div id="message" class="alert alert-success">
                                <?php 
                                echo $_SESSION['message']; 
                                unset($_SESSION['message']); // Hapus pesan setelah ditampilkan
                                ?>
                            </div>
                        <?php endif; ?>
                        </div>
                    </form>
                    <a href="jadwal_bimbingan" class="btn btn-secondary mx-3">Kembali</a>
                </div>       
            </div>

               <!-- Modal Konfirmasi Hapus -->
                <div class="modal fade" id="deleteFileModal" tabindex="-1" aria-labelledby="deleteFileModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="" method="POST">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteFileModalLabel">Konfirmasi Hapus File</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Apakah Anda yakin ingin menghapus file ini? Tindakan ini tidak dapat dibatalkan.</p>
                                    <input type="hidden" name="delete_file" value="1">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
    
    <script>
        const fileInput = document.getElementById("customFile");
        const actionButtons = document.querySelector(".action-buttons");
        const uploadMessage = document.getElementById("uploadMessage");

        // Event listener untuk input file
        fileInput.addEventListener("change", function () {
            const status = fileInput.getAttribute("data-status");
            if (status === "selesai" && fileInput.files.length > 0) {
                actionButtons.style.display = "flex"; // Tampilkan tombol jika ada file dan status selesai
            } else {
                actionButtons.style.display = "none"; // Sembunyikan tombol
            }
        });

        // Event listener untuk mencegah klik jika status tidak selesai
        fileInput.addEventListener("click", function (e) {
            const status = fileInput.getAttribute("data-status");
            if (status !== "selesai") {
                e.preventDefault(); // Mencegah dialog file terbuka
                uploadMessage.classList.remove("d-none"); // Tampilkan pesan
            } else {
                uploadMessage.classList.add("d-none"); // Sembunyikan pesan jika status selesai
            }
        });

        
        document.getElementById("customFile").addEventListener("click", function (e) {
            const status = this.getAttribute("data-status");
            if (status !== "selesai") {
                e.preventDefault(); // Mencegah dialog file terbuka
                document.getElementById("uploadMessage").classList.remove("d-none"); // Tampilkan pesan
            }
        });
    </script>
</body>
</html>
