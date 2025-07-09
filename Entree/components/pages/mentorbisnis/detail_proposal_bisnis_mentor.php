<?php
// Memastikan koneksi ke database dan session sudah aktif
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

// Mengambil id_proposal dan id_kelompok dari URL
$id_proposal = isset($_GET['id']) ? intval($_GET['id']) : null;
$id_kelompok = isset($_GET['id_kelompok']) ? intval($_GET['id_kelompok']) : null;

// Validasi mentor yang login
$mentor_name = isset($_SESSION['nama']) ? $_SESSION['nama'] : null;

// Periksa apakah proposal bisnis dengan ID ini milik kelompok pengguna
$query = "SELECT id FROM proposal_bisnis WHERE id = ? AND kelompok_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_proposal, $id_kelompok);
$stmt->execute();
$result = $stmt->get_result();

// Jika proposal tidak ditemukan atau tidak sesuai, redirect ke dashboard
if ($result->num_rows === 0) {
    header('Location: /Entree/mentor/dashboard');
    exit;
}

if ($id_kelompok) {
    // Query untuk memeriksa apakah kelompok dengan id_kelompok ada untuk mentor yang sedang login
    $sql_check = "SELECT k.* 
                  FROM kelompok_bisnis k 
                  WHERE k.id_kelompok = ?";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("i", $id_kelompok);
    $stmt->execute();
    $result = $stmt->get_result();
    $kelompok = $result->fetch_assoc();

    if (!$kelompok) {
        // Redirect jika kelompok tidak ditemukan atau mentor tidak memiliki akses
        header('Location: /Entree/mentor/dashboard');
        exit;
    }
}

// Query untuk mengambil data proposal bisnis
if ($id_proposal) {
    $stmt = $conn->prepare("SELECT * FROM proposal_bisnis WHERE id = ?");
    $stmt->bind_param("i", $id_proposal);
    $stmt->execute();
    $result = $stmt->get_result();

    // Memeriksa apakah proposal ditemukan
    if ($result->num_rows > 0) {
        $proposal = $result->fetch_assoc();
    } else {
        echo "Proposal tidak ditemukan.";
        exit;
    }
}

// Query untuk mengambil id_mentor berdasarkan id_kelompok
if ($id_kelompok) {
    $stmt = $conn->prepare("SELECT id_mentor FROM kelompok_bisnis WHERE id_kelompok = ?");
    $stmt->bind_param("i", $id_kelompok);
    $stmt->execute();
    $result = $stmt->get_result();

    // Memeriksa apakah data ditemukan
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id_mentor = $row['id_mentor'];
    }
} else {
    echo "ID Kelompok tidak valid.";
}

$mentorQuery = "
        SELECT m.nama AS nama_mentor
        FROM mentor m
        WHERE m.id = '" . $id_mentor . "' LIMIT 1";
    $mentorResult = mysqli_query($conn, $mentorQuery);
    $mentor = mysqli_fetch_assoc($mentorResult);
    $namaMentor = $mentor['nama_mentor'] ?? 'Nama mentor tidak tersedia';

// Cek apakah mentor yang login sama dengan mentor yang terdaftar di kelompok
$is_mentor_matched = ($mentor_name === $namaMentor);

// Proses SDG menjadi label deskriptif
$sdg_mapping = [
    "mengakhiri_kemiskinan" => "1. Mengakhiri Kemiskinan",
    "mengakhiri_kelaparan" => "2. Mengakhiri Kelaparan",
    "kesehatan_kesejahteraan" => "3. Kesehatan dan Kesejahteraan",
    "pendidikan_berkualitas" => "4. Pendidikan Berkualitas",
    "kesetaraan_gender" => "5. Kesetaraan Gender",
    "air_bersih_sanitasi" => "6. Air Bersih dan Sanitasi",
    "energi_bersih_terjangkau" => "7. Energi Bersih dan Terjangkau",
    "pekerjaan_pertumbuhan_ekonomi" => "8. Pekerjaan Layak dan Pertumbuhan Ekonomi",
    "industri_inovasi_infrastruktur" => "9. Industri, Inovasi, dan Infrastruktur",
    "mengurangi_ketimpangan" => "10. Mengurangi Ketimpangan",
    "kota_komunitas_berkelanjutan" => "11. Kota dan Komunitas Berkelanjutan",
    "konsumsi_produksi_bertanggung_jawab" => "12. Konsumsi dan Produksi yang Bertanggung Jawab",
    "penanganan_perubahan_iklim" => "13. Penanganan Perubahan Iklim",
    "ekosistem_lautan" => "14. Ekosistem Lautan",
    "ekosistem_daratan" => "15. Ekosistem Daratan",
    "perdamaian_keadilan_institusi_kuat" => "16. Perdamaian, Keadilan, dan Kelembagaan yang Kuat",
    "kemitraan_tujuan" => "17. Kemitraan untuk Mencapai Tujuan"
];

// Proses SDG menjadi label deskriptif
$sdg_selected = explode(",", $proposal['sdg']);
$sdg_labels = array_map(function ($key) use ($sdg_mapping) {
    return $sdg_mapping[$key] ?? $key;
}, $sdg_selected);

$fileUrl = htmlspecialchars($proposal["proposal_pdf"]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Proposal Bisnis | Entree</title>
    <link rel="icon" href="\Entree\assets\img\icon_favicon.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/77a99d5f4f.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="/Entree/assets/css/detail_proposal.css">
</head>
<style>
    .Feedback {
        margin: 20px 0;
        padding: 15px;
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 5px;
        line-height: 1.6;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
</style>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php 
        $activePage = 'proposal_bisnis_mentor'; // Halaman ini aktif
        include 'sidebar_mentor.php'; 
        ?>

        <div class="main p-3">
            <div class="main_header">
                <?php 
                    $pageTitle = "Proposal Bisnis"; // Judul halaman
                    include 'header_mentor.php'; 
                ?>
            </div>

            <!-- Content Wrapper -->
            <div class="main_wrapper">
                <h2><?php echo htmlspecialchars($proposal['judul_proposal']); ?></h2>
                <div class="description">
                    <strong>Ide Bisnis:</strong>
                    <p><?php echo htmlspecialchars($proposal['ide_bisnis'] ?? 'Tidak ada ide bisnis.'); ?></p>
                </div>

                <!-- Table Section -->
                <table class="styled-table">
                    <tr>
                        <td><strong>Tahapan Bisnis:</strong></td>
                        <td class="file-box">
                            <?php echo htmlspecialchars($proposal['tahapan_bisnis']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Tujuan SDGs:</strong></td>
                        <td class="file-box m-0 p-0">
                            <ul class="m-0 p-0 mx-2">
                                <?php foreach ($sdg_labels as $label): ?>
                                    <li class="m-2"><?php echo htmlspecialchars($label); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Kategori Bisnis:</strong></td>
                        <td class="file-box">
                            <?php
                            if ($proposal['kategori'] === 'lainnya') {
                                echo htmlspecialchars($proposal['other_category']);
                            } else {
                                echo htmlspecialchars($proposal['kategori']);
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Anggaran:</strong></td>
                        <td class="file-box">
                            Rp. <?php echo htmlspecialchars($proposal['anggaran']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>File Proposal Bisnis:</strong></td>
                        <td class="file-box">
                            <ul id="fileList">
                                <li class="file-box">
                                    <div class="file-info">
                                        <?php
                                            $filename = basename(urldecode($proposal['proposal_pdf']));
                                            $cleanName = preg_replace('/^\d+_/', '', $filename); // hapus angka + underscore di awal
                                            echo htmlspecialchars($cleanName);
                                        ?>
                                    </div>
                                    <div class="icon-group">
                                        <a href="<?= $fileUrl ?>" target="_blank" class="detail-icon" data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="Lihat File">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="\Entree\components\pages\download_from_aws\download.php?table=proposal_bisnis&id=<?= $proposal['id'] ?>" target="_blank" download class="file icon" data-bs-toggle="tooltip" title="Unduh Materi">
                                            <i class="fa-solid fa-download btn-icon"></i>
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td class="file-box">
                            <?php
                                if ($proposal['status'] == 'disetujui') {
                                    echo '<p class="alert alert-success text-white fw-bold text-center p-2 m-0 px-3" style="background-color:#2ea56f; font-size: 17px; width:fit-content;" role="alert">Disetujui</p>';
                                } elseif ($proposal['status'] == 'ditolak') {
                                    echo '<p class="alert alert-danger text-white fw-bold text-center p-2 m-0 px-3" style="background-color:#dc3545; font-size: 17px; width:fit-content;" role="alert">Ditolak</p>';
                                } else {
                                    echo '<p class="alert alert-warning text-white fw-bold text-center p-2 m-0 px-3" style="background-color: #ffc107; font-size: 17px; width:fit-content;" role="alert">Menunggu</p>';
                                }
                            ?>
                        </td>
                    </tr>
                    <?php if ($is_mentor_matched): ?>
                        <tr>
                            <td><strong>Setujui atau Tolak Proposal:</strong></td>
                            <td class="file-box">
                                <div class="action-buttons m-0">
                                    <button type="button" class="accept-btn">Setujui Proposal</button>
                                    <button type="button" class="reject-btn">Tolak Proposal</button>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </table>
                <!-- Feedback Section -->
            <?php if ($is_mentor_matched): ?>
                <?php if (!empty($proposal['feedback'])): ?>
                    <strong>Umpan Balik dari Mentor Bisnis:</strong>
                    <div class="feedback-box">
                        <p><?php echo htmlspecialchars($proposal['feedback'] ?? 'Belum ada umpan balik.'); ?></p>
                    </div>
                <?php endif; ?>

                <div class="accordion accordion-flush" id="accordionFlushExample">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                        <button class="accordion-button collapsed text-white" style="background-color:#2ea56f; border-radius:5px;" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                            Masukkan Umpan Balik Anda:
                        </button>
                        </h2>
                        <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body">
                            <form id="feedbackForm"> 
                                <div class="mb-3">
                                    <textarea class="form-control" id="feedbackInput" name="feedback" rows="5" placeholder="Tulis umpan balik Anda di sini..." required></textarea>
                                </div>
                                <input type="hidden" id="proposalId" value="<?php echo htmlspecialchars($proposal['id']); ?>">
                                <div id="feedbackMessage" class="mt-3"></div>
                                <div class="btn_container d-flex justify-content-end">
                                    <button type="submit" class="btn">Kirim Umpan Balik</button>
                                </div>
                            </form>
                            
                        </div>
                        </div>
                    </div>
                </div>
                
                <script>
                    document.getElementById('feedbackForm').addEventListener('submit', async function (event) {
                        event.preventDefault();

                        const feedback = document.getElementById('feedbackInput').value;
                        const proposalId = document.getElementById('proposalId').value;

                        try {
                            const response = await fetch('submit_feedback', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    feedback: feedback,
                                    proposal_id: proposalId,
                                }),
                            });

                            const result = await response.json();
                            const messageDiv = document.getElementById('feedbackMessage');

                            if (result.success) {
                                messageDiv.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                            } else {
                                messageDiv.innerHTML = `<div class="alert alert-danger">${result.message}</div>`;
                            }
                            setTimeout(function() {
                                messageDiv.innerHTML = '';
                            }, 1500);
                        } catch (error) {
                            console.error('Error:', error);
                            document.getElementById('feedbackMessage').innerHTML = `<div class="alert alert-danger">Terjadi kesalahan. Silakan coba lagi.</div>`;
                        }
                    });
                </script>
            <?php else: ?>
                <strong>Umpan Balik dari Mentor Bisnis:</strong>
                <div class="feedback-box">
                    <p><?php echo htmlspecialchars($proposal['feedback'] ?? 'Belum ada Umpan Balik.'); ?></p>
                </div>
            <?php endif; ?>
                <!-- Tambahkan tombol "Kembali" di luar form -->
            <div class="mt-4 text-left">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='proposal_bisnis?id_kelompok=<?php echo $id_kelompok ?>'">Kembali</button>
            </div>
            </div>
        </div>
    </div>

    <script>
    document.querySelector('.accept-btn').addEventListener('click', () => {
    if (confirm("Apakah Anda yakin ingin menyetujui proposal ini?")) {
        // Get proposal ID and group ID
        const proposalId = <?php echo $id_proposal; ?>;
        const groupId = <?php echo $id_kelompok; ?>;

            // Mengirim data ke server menggunakan fetch
            fetch('update_proposal_status', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'disetujui',  // atau 'ditolak'
                    proposal_id: proposalId,
                    group_id: groupId
                }),
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);  // Debugging untuk melihat respons
                if (data.success) {
                    alert("Proposal berhasil diperbarui!");
                    location.reload();  // Reload the page to reflect changes
                } else {
                    alert("Gagal memperbarui status proposal.");
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Terjadi kesalahan.");
            });
        }
    });

    document.querySelector('.reject-btn').addEventListener('click', () => {
        if (confirm("Apakah Anda yakin ingin menolak proposal ini?")) {
            // Get proposal ID and group ID
            const proposalId = <?php echo $id_proposal; ?>;
            const groupId = <?php echo $id_kelompok; ?>;

            // Make an AJAX request to update the proposal status
            fetch('update_proposal_status', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'ditolak',
                    proposal_id: proposalId,
                    group_id: groupId
                }),
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Proposal berhasil ditolak!");
                    location.reload();  // Reload the page to reflect changes
                } else {
                    alert("Gagal memperbarui status proposal: " + data.message); // Display error message
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Terjadi kesalahan: " + error.message); // Display detailed error
            });
        }
    });
</script>
</body>

</html>
