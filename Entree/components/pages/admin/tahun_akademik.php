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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tahun Akademik | Entree</title>
    <link rel="icon" href="\Entree\assets\img\icon_favicon.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/77a99d5f4f.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="/Entree/assets/css/admin/tahun_akademik.css">
</head>

<body>
    <div class="wrapper">
        <?php 
        $activePage = 'tahun_akademik'; // Halaman ini aktif
        include 'sidebar_admin.php'; 
        ?>

        <div class="main p-3">

            <div class="main_header">
                <?php 
                    $pageTitle = "Tahun Akademik"; // Judul halaman
                    include 'header_admin.php'; 
                ?>
            </div>

            <div class="main_wrapper">

                <!-- Tombol untuk Membuka Modal -->
                <button type="button" class="btn-tambah" data-bs-toggle="modal" data-bs-target="#tahunAkademikModal">
                    Tambah Tahun Akademik
                </button>

               <!-- Tambahkan Form pada Modal -->
                <div class="modal fade" id="tahunAkademikModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <!-- Modal Header -->
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalTitle">Tambah Tahun Akademik</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <!-- Form Input -->
                            <form action="tambah_tahun_akademik" method="POST">
                                <!-- Modal Body -->
                                <div class="modal-body">
                                    <!-- Dropdown Tahun -->
                                    <div class="mb-3">
                                        <label for="tahunDropdown" class="form-label">Pilih Tahun:<span style="color:red;">*</span></label>
                                        <select class="form-select" id="tahunDropdown" name="tahun_awal" required>
                                            <option value="" disabled selected>Pilih Tahun</option>
                                            <?php
                                            $currentYear = date('Y');
                                            for ($i = 2010; $i <= $currentYear; $i++) {
                                                echo "<option value='$i'>$i</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <!-- Radio Button Jenis Tahun -->
                                    <div class="mb-3">
                                        <label class="form-label">Jenis Tahun:<span style="color:red;">*</span></label>
                                        <div>
                                            <input type="radio" name="jenis_tahun" id="ganjil" value="Ganjil" required>
                                            <label for="ganjil">Ganjil</label>
                                            <input type="radio" name="jenis_tahun" id="genap" value="Genap" required>
                                            <label for="genap">Genap</label>
                                        </div>
                                    </div>

                                    <!-- Dropdown Status -->
                                    <div class="mb-3">
                                        <label for="statusDropdown" class="form-label">Status Tahun Akademik:<span style="color:red;">*</span></label>
                                        <select class="form-select" id="statusDropdown" name="status" required>
                                            <option value="" disabled selected>Pilih Status</option>
                                            <option value="Aktif">Aktif</option>
                                            <option value="Tidak Aktif">Tidak Aktif</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Modal Footer -->
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-success">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div>
                    <!-- Heading untuk daftar file -->
                    <h5 id="fileHeading">Daftar Tahun Akademik</h5>

                    <!-- Daftar file -->
                    <ul id="fileList">
                        <?php
                        // Koneksi ke database
                        include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';

                        // Query untuk mengambil data tahun akademik
                        $sql = "SELECT tahun, jenis_tahun, status FROM tahun_akademik ORDER BY tahun DESC";
                        $result = $conn->query($sql);

                        // Tampilkan data
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $tahun_berikutnya = $row['tahun'] + 1;
                                $status_tahun = strcasecmp(trim($row['status']), 'Aktif') == 0 ? 'Aktif' : 'Tidak Aktif';
                                echo "
                                <li>
                                    <div class='file-info'>
                                        {$row['tahun']}/{$tahun_berikutnya} {$row['jenis_tahun']}
                                        <span>($status_tahun)</span>
                                    </div>
                                    <div class='icon-group'>
                                        <a 
                                            href='hapus_tahun_akademik?tahun={$row['tahun']}&jenis={$row['jenis_tahun']}' 
                                            onclick='return confirm(\"Hapus tahun akademik ini?\");' 
                                            data-bs-toggle='tooltip' 
                                            data-bs-custom-class='custom-tooltip' 
                                            data-bs-title='Hapus Tahun Akademik'>
                                            <i class='fa-solid fa-trash-can' style='color: red;'></i> 
                                        </a>
                                    </div>
                                </li>";
                                
                            }
                        } else {
                            echo "<li>Belum ada data tahun akademik.</li>";
                        }

                        $conn->close();
                        ?>
                    </ul>
                </div>
                
            </div>
        </div>

    </div>
    <!-- JavaScript -->
    <script>
        let tahunTerpilih = ''; // Variabel untuk menyimpan tahun yang dipilih

        // Fungsi saat Dropdown Tahun berubah
        function filterJenisTahun() {
            tahunTerpilih = document.getElementById('tahunDropdown').value;
            document.getElementById('jenisTahunSection').style.display = 'block';
            document.getElementById('hasilTahun').innerHTML = ''; // Reset hasil
        }

        // Fungsi untuk menampilkan hasil berdasarkan jenis tahun
        function tampilkanHasil() {
            // Ambil tahun terpilih dari dropdown atau input
            const tahunTerpilih = document.getElementById('tahunDropdown').value; // Pastikan ada id 'tahunDropdown'
            const jenisTahun = document.querySelector('input[name="jenisTahun"]:checked'); // Cek input radio yang dipilih

            if (tahunTerpilih && jenisTahun) {
                const tahunBerikutnya = parseInt(tahunTerpilih) + 1; // Hitung tahun berikutnya
                const jenisTahunValue = jenisTahun.value;

                // Format jenis tahun agar huruf pertama menjadi kapital
                const jenisTahunFormatted = jenisTahunValue.charAt(0).toUpperCase() + jenisTahunValue.slice(1);

                // Format tahun akademik
                const tahunAkademik = `${tahunTerpilih}/${tahunBerikutnya} ${jenisTahunFormatted}`;

                // Tampilkan hasil tahun akademik
                document.getElementById('hasilTahun').innerHTML = `
                    <div class="alert alert-info">
                        Tahun Akademik: <strong>${tahunAkademik}</strong>
                    </div>
                `;
            } else {
                alert("Harap pilih tahun dan jenis tahun terlebih dahulu!");
            }
        }

        // Fungsi untuk menyimpan pilihan dan menambahkannya ke daftar
        function simpanTahun() {
            const hasilElement = document.getElementById('hasilTahun');
            const hasil = hasilElement.innerText.trim(); // Ambil teks dari elemen hasilTahun

            if (hasil) {
                // Ambil tahun akademik yang baru
                const tahunAkademikBaru = hasil.replace("Tahun Akademik: ", "").trim();

                // Menambahkan tahun akademik baru ke daftar
                const ul = document.getElementById('fileList');
                const li = document.createElement('li');

                // Isi elemen li dengan HTML yang benar menggunakan backticks
                li.innerHTML = `
                    <div class="file-info">
                        ${tahunAkademikBaru} 
                        <span>(Aktif)</span>
                    </div>
                    <div class="icon-group">
                        <i class="fa-solid fa-trash-can" style="cursor: pointer; color: red;"></i>
                    </div>
                `;

                // Menambahkan elemen baru ke bagian atas daftar
                ul.prepend(li);

                // Menandai tahun akademik aktif (reset status aktif lainnya)
                const listItems = ul.querySelectorAll('li');
                listItems.forEach(item => {
                    const tahun = item.querySelector('.file-info').textContent.trim();
                    if (tahun === tahunAkademikBaru) {
                        item.querySelector('.file-info span').textContent = '(Aktif)';
                    } else {
                        item.querySelector('.file-info span').textContent = '';
                    }
                });

                // Reset form atau tampilan setelah disimpan
                document.getElementById('tahunDropdown').value = ''; // Reset dropdown tahun
                hasilElement.innerHTML = ''; // Kosongkan hasil tampilan
                alert("Tahun Akademik berhasil ditambahkan!");

            } else {
                alert("Harap pilih tahun dan jenis tahun terlebih dahulu!");
            }
        }
    </script>
</body>

</html>