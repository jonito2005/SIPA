PHP
<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/SIPANEW/database/db.php');

if (!isset($_SESSION['email']) || $_SESSION['status'] != 'dosen') {
    header("Location: index.html");
    exit();
}

<?php
// ... (kode awal sama seperti tambah_pengguna.php)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari formulir
    $namaMahasiswa = $_POST["namaMahasiswa"];
    $tanggalBimbingan = $_POST["tanggalBimbingan"];
    $catatanBimbingan = $_POST["catatanBimbingan"];

    // ... (validasi input)

    // Cari ID mahasiswa berdasarkan nama
    $queryMahasiswa = "SELECT id_mahasiswa FROM mahasiswa WHERE nama_pengguna = '$namaMahasiswa'"; 
    $resultMahasiswa = pg_query($conn, $queryMahasiswa);

    if (pg_num_rows($resultMahasiswa) > 0) {
        $rowMahasiswa = pg_fetch_assoc($resultMahasiswa);
        $idMahasiswa = $rowMahasiswa['id_mahasiswa'];

        // Ambil ID dosen dari session
        $idDosen = $_SESSION['id_pengguna']; // Asumsikan ID dosen disimpan di session

        // Simpan data ke tabel bimbingan
        $queryBimbingan = "INSERT INTO bimbingan (id_mahasiswa, id_dosen, tanggal_bimbingan, catatan) 
                           VALUES ('$idMahasiswa', '$idDosen', '$tanggalBimbingan', '$catatanBimbingan')";
        $resultBimbingan = pg_query($conn, $queryBimbingan);

        if ($resultBimbingan) {
            echo "Bimbingan berhasil ditambahkan!";
        } else {
            echo "Terjadi kesalahan: " . pg_last_error($conn); 
        }
    } else {
        echo "Mahasiswa tidak ditemukan.";
    }
}
?>
