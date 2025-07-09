<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /Entree/login');
    exit;
}

// Cek apakah role pengguna sesuai
if ($_SESSION['role'] !== 'Mahasiswa') {
    header('Location: /Entree/login');
    exit;
}
include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';

$npm_ketua = $_SESSION['npm']; // NPM ketua yang sedang login

$cekKelompokQuery = "SELECT * FROM kelompok_bisnis WHERE npm_ketua = '$npm_ketua' LIMIT 1";
$cekKelompokResult = mysqli_query($conn, $cekKelompokQuery);
$kelompokTerdaftar = mysqli_fetch_assoc($cekKelompokResult);

if ($kelompokTerdaftar) {
    header("Location: detail_kelompok_bisnis?id=" . $kelompokTerdaftar['id_kelompok']);
    exit();
}

$cekAnggotaQuery = "SELECT * FROM anggota_kelompok WHERE npm_anggota = '$npm_ketua' LIMIT 1";
$cekAnggotaResult = mysqli_query($conn, $cekAnggotaQuery);
$anggotaTerdaftar = mysqli_fetch_assoc($cekAnggotaResult);

if ($anggotaTerdaftar) {
    $kelompokID = $anggotaTerdaftar['id_kelompok'];
    header("Location: detail_kelompok_bisnis?id=" . $kelompokID);
    exit();
}

// Query untuk mengambil NPM ketua yang sudah terdaftar di tabel kelompok_bisnis
$cekKetuaKelompokQuery = "SELECT npm_ketua FROM kelompok_bisnis";
$cekKetuaKelompokResult = mysqli_query($conn, $cekKetuaKelompokQuery);

$npmKetuaTerdaftar = [];
while ($row = mysqli_fetch_assoc($cekKetuaKelompokResult)) {
    $npmKetuaTerdaftar[] = $row['npm_ketua'];
}

// Menambahkan ketua yang sedang login ke dalam daftar (ketua yang login tidak bisa menjadi anggota kelompok baru)
$npmKetuaTerdaftar[] = $npm_ketua;

// Query untuk mengambil NPM anggota yang sudah ada di kelompok lain
$cekAnggotaDiKelompokQuery = "SELECT npm_anggota FROM anggota_kelompok";
$cekAnggotaDiKelompokResult = mysqli_query($conn, $cekAnggotaDiKelompokQuery);

$npmAnggotaDiKelompok = [];
while ($row = mysqli_fetch_assoc($cekAnggotaDiKelompokResult)) {
    $npmAnggotaDiKelompok[] = $row['npm_anggota'];
}

// Gabungkan NPM ketua dan anggota yang sudah ada dalam kelompok lain
$npmAnggotaDiKelompok = array_merge($npmAnggotaDiKelompok, $npmKetuaTerdaftar);

// Query untuk menampilkan mahasiswa yang belum ada dalam kelompok dan sudah memiliki email dan nomor telepon
$query = "SELECT npm, nama, email, contact FROM mahasiswa WHERE npm NOT IN ('" . implode("','", $npmAnggotaDiKelompok) . "') AND email IS NOT NULL AND contact IS NOT NULL";
$result = mysqli_query($conn, $query);

$mahasiswaList = [];
while ($row = mysqli_fetch_assoc($result)) {
    if ($row['email'] && $row['contact']) {  // Pastikan email dan nomor telepon tidak kosong
        $mahasiswaList[] = $row;
    }
}

// Menghitung tahun ajaran berdasarkan tahun dan semester
$currentYear = date("Y");
$currentMonth = date("m");

// Menentukan semester dan tahun ajaran
if ($currentMonth >= 7) { // Semester Ganjil
    $tahun_akademik = $currentYear . "/" . ($currentYear + 1) . " Ganjil";
} else { // Semester Genap
    $tahun_akademik = ($currentYear - 1) . "/" . $currentYear . " Genap";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelompok Bisnis | Entree</title>
    <link rel="icon" href="\Entree\assets\img\icon_favicon.png" type="image/x-icon">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/77a99d5f4f.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="/Entree/assets/css/mahasiswa/kelompok_bisnis_mahasiswa.css">
</head>
<body>
    <div class="wrapper">
        <?php 
        $activePage = 'kelompok_bisnis'; 
        include 'sidebar_mahasiswa.php'; 
        ?>

        <div class="main p-3">
            <div class="main_header">
                <?php 
                    $pageTitle = "Kelompok Bisnis";
                    include 'header_mahasiswa.php'; 
                ?>
            </div>

            <div class="main_wrapper">

                <!-- Tombol untuk membuka modal -->
                <button type="button" class="btn-hijau" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    Tambah Kelompok Bisnis
                </button>

                <!-- Modal -->
                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg"> <!-- Menggunakan modal-xl untuk modal yang lebih lebar -->
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">Tambah Kelompok Bisnis</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Form dengan autocomplete="off" -->
                                    <form method="POST" action="proses_kelompok_bisnis" enctype="multipart/form-data" autocomplete="off">
                                        <div class="form-group">
                                            <label for="nama_kelompok">Nama Kelompok:<span style="color:red;">*</span></label>
                                            <input type="text" id="nama_kelompok" name="nama_kelompok" placeholder="Masukkan Nama Kelompok" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="jumlah_anggota">Jumlah Anggota:<span style="color:red;">*</span></label>
                                            <select id="jumlah_anggota" name="jumlah_anggota" required>
                                                <option value="0">Pilih Jumlah Anggota kelompok</option>
                                                <option value="1">1 Anggota</option>
                                                <option value="2">2 Anggota</option>
                                                <option value="3">3 Anggota</option>
                                                <option value="4">4 Anggota</option>
                                                <option value="5">5 Anggota</option>
                                            </select>
                                        </div>

                                        <div id="anggota_fields"></div>

                                        <div class="form-group">
                                            <label for="nama_bisnis">Nama Bisnis:<span style="color:red;">*</span></label>
                                            <input type="text" id="nama_bisnis" name="nama_bisnis" placeholder="Masukkan Nama Bisnis:" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="logo_bisnis">Logo Bisnis:<span style="color:red;">*</span></label>
                                            <input type="file" class="form-control" id="logo_bisnis" name="logo_bisnis" accept=".png, .jpg" required>
                                            <div id="logo_preview" class="mt-2"></div>
                                        </div>

                                        <input type="hidden" name="tahun_akademik" value="<?php echo $tahun_akademik; ?>">
                               
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success" name="kirim">Tambah Kelompok Bisnis</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <script>
        var jumlahAnggota = document.getElementById("jumlah_anggota");
        var anggotaFieldsContainer = document.getElementById("anggota_fields");

        jumlahAnggota.onchange = function() {
            anggotaFieldsContainer.innerHTML = "";
            for (var i = 1; i <= jumlahAnggota.value; i++) {
                var formGroup = document.createElement("div");
                formGroup.className = "form-group";

                var label = document.createElement("label");
                label.for = "npm_anggota_" + i;
                label.innerHTML = "NPM Anggota " + i + ":<span style='color:red;'>*</span>";

                var input = document.createElement("input");
                input.type = "text";
                input.id = "npm_anggota_" + i;
                input.name = "npm_anggota_" + i;
                input.placeholder = "Ketik NPM Anggota " + i;
                input.required = true;
                input.setAttribute("autocomplete", "off");

                // Tambahkan event listener untuk pencarian anggota
                input.addEventListener('input', function() {
                    var npmField = this;
                    var value = npmField.value.toLowerCase();
                    var selectedNPMs = Array.from(anggotaFieldsContainer.querySelectorAll("input"))
                        .map(function(input) { return input.value.split(" - ")[0].toLowerCase(); });

                    var matchingMembers = <?php echo json_encode($mahasiswaList); ?>.filter(function(mahasiswa) {
                        return mahasiswa.npm.toLowerCase().includes(value) && !selectedNPMs.includes(mahasiswa.npm.toLowerCase());
                    });

                    var datalist = document.getElementById(npmField.id + "-datalist");
                    if (!datalist) {
                        datalist = document.createElement("datalist");
                        datalist.id = npmField.id + "-datalist";
                        npmField.setAttribute("list", datalist.id);
                        npmField.parentNode.appendChild(datalist);
                    }

                    datalist.innerHTML = ""; // Clear previous options
                    matchingMembers.forEach(function(mahasiswa) {
                        var option = document.createElement("option");
                        option.value = mahasiswa.npm + " - " + mahasiswa.nama;
                        datalist.appendChild(option);
                    });
                });

                formGroup.appendChild(label);
                formGroup.appendChild(input);
                anggotaFieldsContainer.appendChild(formGroup);
            }
        }
        
        // Validasi sebelum form disubmit
        document.querySelector('form').addEventListener('submit', function(event) {
            var invalidNPMs = [];
            anggotaFieldsContainer.querySelectorAll("input").forEach(function(input) {
                var selectedNPM = input.value.split(" - ")[0];  // Get only NPM from the value
                if (!isNPMValid(selectedNPM)) {  // Check if the NPM is valid
                    invalidNPMs.push(input.value);
                }
            });

            if (invalidNPMs.length > 0) {
                event.preventDefault();  // Prevent form submission
                alert("NPM berikut sudah terdaftar dalam kelompok lain atau tidak valid: " + invalidNPMs.join(", "));
            }
        });

        // Function to check if NPM is valid
        function isNPMValid(npm) {
            var valid = <?php echo json_encode($mahasiswaList); ?>.some(function(mahasiswa) {
                return mahasiswa.npm === npm;
            });
            return valid;
        }
    </script>
</body>
</html>
