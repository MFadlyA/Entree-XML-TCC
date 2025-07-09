<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'Admin') {
    header('Location: /Entree/login');
    exit;
}

include $_SERVER['DOCUMENT_ROOT'] . '/Entree/config/db_connection.php';

// Query ambil data
$query = "
    SELECT 
    k.nama_kelompok, 
    m.nama AS ketua_kelompok, 
    p.judul_proposal, 
    p.kategori, 
    p.other_category, 
    p.status
FROM proposal_bisnis p
JOIN kelompok_bisnis k ON p.kelompok_id = k.id_kelompok
LEFT JOIN mahasiswa m ON k.npm_ketua = m.npm
";

$result = mysqli_query($conn, $query);
if (!$result) {
    die("Query error: " . mysqli_error($conn));
}

// Header Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=daftar_proposal.xls");

// Print kolom
echo "Nama Kelompok\tKetua Kelompok\tJudul Proposal\tKategori\tStatus\n";

// Cetak data
while ($row = mysqli_fetch_assoc($result)) {
    $kategori = $row['kategori'] === 'lainnya' ? $row['other_category'] : $row['kategori'];

    echo $row['nama_kelompok'] . "\t" .
         $row['ketua_kelompok'] . "\t" .
         $row['judul_proposal'] . "\t" .
         $kategori . "\t" .
         $row['status'] . "\n";
}
exit;
?>
