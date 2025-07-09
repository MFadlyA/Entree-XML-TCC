<?php
    include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';
    require $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/aws_s3_config.php';

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

    // Ambil ID dari parameter URL
    $toastMessage = isset($_GET['toast']) ? $_GET['toast'] : null;
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    $userRole = $_SESSION['role'];
    $userId = $_SESSION['user_id'];

    // Periksa apakah jadwal milik kelompok pengguna
    $query = "SELECT id_klmpk FROM jadwal WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header('Location: /Entree/mentor/dashboard'); // Redirect jika jadwal tidak ditemukan
        exit;
    }

    // 1. Ambil ID dari tabel mentor_bisnis
    $sql_id = "SELECT id FROM mentor WHERE user_id = '$userId'";
    $result_id = $conn->query($sql_id);

    if (!$result_id) {
        die("Error query sql_id: " . $conn->error); // Handle error query
    }

    if ($result_id->num_rows > 0) {
        $row_id = $result_id->fetch_assoc();
        $id_mentor = $row_id['id']; // Ambil nilai ID mentor
    }

    // 2. Cek apakah id_klmpk pada jadwal berhubungan dengan kelompok bisnis yang benar dan id_mentor sesuai
    $groupCheckQuery = "SELECT k.id_mentor 
                        FROM kelompok_bisnis k 
                        INNER JOIN jadwal j ON k.id_kelompok = j.id_klmpk
                        WHERE j.id = ? AND k.id_mentor = ?";
    $stmt_group_check = $conn->prepare($groupCheckQuery);
    $stmt_group_check->bind_param("ii", $id, $id_mentor);
    $stmt_group_check->execute();
    $result_group_check = $stmt_group_check->get_result();

    // Jika hasil query ada, maka id_mentor pada kelompok bisnis sama dengan id_mentor yang sedang login
    if ($result_group_check->num_rows > 0) {
        // ID mentor pada kelompok bisnis cocok, fitur aksi dan feedback bisa diakses
        $canTakeAction = true;
    } else {
        // Tidak cocok, berarti tidak bisa menggunakan fitur aksi dan feedback
        $canTakeAction = false;
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

    // Mendapatkan ID kelompok dari parameter URL
    $id_kelompok = isset($_GET['id_kelompok']) ? $_GET['id_kelompok'] : null;

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
    <link rel="stylesheet" href="/Entree/assets/css/detail_proposal.css">
    <link rel="stylesheet" href="/Entree/assets/css/detail_jadwal_bimbingan.css">

</head>

<body>
    <div class="wrapper">
        <?php 
        $activePage = 'jadwal_bimbingan'; 
        include 'sidebar_mentor.php'; 
        ?>

        <div class="main p-3">
            <div class="main_header">
                <?php 
                    $pageTitle = "Jadwal Bimbingan"; // Judul halaman
                    include 'header_mentor.php'; 
                ?>
            </div>

            <div class="main_wrapper">
                <div class="container mt-5">
                    <h1>Detail Jadwal</h1>
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
                            <th>Status:</th>
                            <td class="file-box">
                                <?php if ($canTakeAction): ?>
                                    <div class="dropdown d-flex align-items-center gap-2">
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
                                        <!-- Kondisi menampilkan tombol jika status tidak sama dengan selesai -->
                                        <?php if ($data['status'] != 'selesai'): ?>
                                        <button class="btn btn-secondary dropdown-toggle" type="button" id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            Pilih Aksi
                                        </button>
                                            <ul class="dropdown-menu" aria-labelledby="actionDropdown">
                                                <li><button class="dropdown-item accept-btn">Setujui Jadwal</button></li>
                                                <li><button class="dropdown-item reject-btn">Tolak Jadwal</button></li>
                                                <li><button class="dropdown-item alt-schedule-btn">Berikan Jadwal Alternatif</button></li>
                                                <li><button class="dropdown-item done-btn">Selesai</button></li>
                                            </ul>
                                        <?php endif; ?>
                                        <tr>
                                            <th><strong>Bukti Kegiatan:</strong></td>
                                            <td class="file-box">
                                                <ul id="fileList">
                                                    <li class="file-box">
                                                        <div class="file-info">
                                                            <?php 
                                                                $filename = basename(urldecode($data['bukti_kegiatan'] ?? ''));
                                                                $cleanName = preg_replace('/^\d+_/', '', $filename); // hapus angka + underscore di awal
                                                                echo htmlspecialchars($cleanName ?: 'Mahasiswa Belum Mengunggah bukti kegiatan');
                                                            ?>
                                                        </div>
                                                        <?php if (!empty($data['bukti_kegiatan'])): ?>
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
                                                            </div>
                                                        <?php endif; ?>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                    </div>  
                                    <?php else: ?>
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

                    <!-- Modal Jadwal Alternatif -->
                    <div class="modal fade" id="altScheduleModal" tabindex="-1" aria-labelledby="altScheduleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="altScheduleModalLabel">Berikan Jadwal Alternatif</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="submit_action" method="POST">
                                    <input type="hidden" name="action" value="jadwal_alternatif">
                                    <input type="hidden" name="jadwal_id" value="<?php echo htmlspecialchars($id); ?>">
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="altDateInput" class="form-label">Tanggal:</label>
                                            <input type="date" class="form-control" id="altDateInput" name="alt_date" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="altTimeInput" class="form-label">Waktu:</label>
                                            <input type="time" class="form-control" id="altTimeInput" name="alt_time" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="altLocationInput" class="form-label">Lokasi:</label>
                                            <input type="text" class="form-control" id="altLocationInput" name="alt_location" placeholder="Masukkan lokasi" required>
                                        </div>
                                        <input type="hidden" name="jadwal_id" value="<?php echo htmlspecialchars($id); ?>">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-cancel" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-success btn-submit">Kirim</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                   <?php if ($data['status'] == 'selesai' && $canTakeAction): ?>
                        <div class="accordion accordion-flush" id="accordionFlushExample">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                <button class="accordion-button collapsed text-white" style="background-color:#2ea56f; border-radius:5px;" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                                    Masukkan Umpan Balik Anda:
                                </button>
                                </h2>
                                <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                                <div class="accordion-body">
                                    <form action="submit_feedback_jadwal" method="POST">
                                        <div class="mb-3">
                                            <textarea class="form-control" id="feedbackInput" name="feedback" rows="5" placeholder="Tulis umpan balik Anda di sini..." required></textarea>
                                        </div>
                                        <input type="hidden" name="jadwal_id" value="<?php echo htmlspecialchars($id); ?>">
                                        <div class="btn_container mentor-buttons">
                                            <button type="submit">Kirim Umpan Balik</button>
                                        </div>
                                    </form>
                                </div>
                                </div>
                            </div>
                        </div>

                    <?php endif; ?>


                    <?php if ($toastMessage): ?>
                         <!-- Toast Container -->
                        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050">
                            <div id="toastMessage" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                                <div class="d-flex">
                                    <div class="toast-body">
                                        <?php echo htmlspecialchars($toastMessage); ?>
                                    </div>
                                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                                </div>
                            </div>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                var toastEl = document.querySelector('.toast');
                                var toast = new bootstrap.Toast(toastEl);
                                toast.show();
                            });
                        </script>
                    <?php endif; ?>

                </div> 
                
                <?php if ($id_kelompok != null): ?>
                    <div>
                        <button 
                            class="btn btn-secondary mt-2" 
                            data-bs-toggle="tooltip" 
                            data-bs-custom-class="custom-tooltip" 
                            data-bs-title="Tekan Untuk Kembali" 
                            onclick="window.location.href='detail_kelompok?id_kelompok=<?php echo htmlspecialchars($id_kelompok); ?>'">
                            Kembali
                        </button>
                    </div>
                <?php else: ?>
                    <div class="mt-2">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='jadwal_bimbingan'">Kembali</button>
                    </div>
                <?php endif; ?>
                
            </div>
    </div>
    <script>
        document.querySelector('.accept-btn').addEventListener('click', () => {
            if (confirm("Apakah Anda yakin ingin menyetujui Jadwal ini?")) {
                submitAction("setujui");
            }
        });

        document.querySelector('.reject-btn').addEventListener('click', () => {
            if (confirm("Apakah Anda yakin ingin menolak Jadwal ini?")) {
                submitAction("tolak");
            }
        });

        document.querySelector('.done-btn').addEventListener('click', () => {
            if (confirm("Apakah Anda yakin ingin menandai Jadwal ini selesai?")) {
                submitAction("selesai");
            }
        });

        document.querySelector('.alt-schedule-btn').addEventListener('click', () => {
            const altScheduleModal = new bootstrap.Modal(document.getElementById('altScheduleModal'));
            altScheduleModal.show();
        });

        // Fungsi untuk submit form
        function submitAction(action) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'submit_action';

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = action;
            form.appendChild(actionInput);

            const jadwalIdInput = document.createElement('input');
            jadwalIdInput.type = 'hidden';
            jadwalIdInput.name = 'jadwal_id';
            jadwalIdInput.value = "<?php echo htmlspecialchars($id); ?>";
            form.appendChild(jadwalIdInput);

            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>
