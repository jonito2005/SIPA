<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/SIPANEW/database/db.php');

if (!isset($_SESSION['email']) || $_SESSION['status'] != 'dosen') {
    header("Location: index.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari formulir
    $namaPengguna = $_POST["namaPengguna"];
    $statusPengguna = $_POST["statusPengguna"];
    $emailPengguna = $_POST["emailPengguna"];
    $passwordPengguna = $_POST["passwordPengguna"];
    $nomorHp = $_POST["nomorHp"]; // Tambahkan input nomor HP

    // Validasi input (tambahkan validasi sesuai kebutuhan)

    // Hash password
    $hashedPassword = password_hash($passwordPengguna, PASSWORD_DEFAULT);

    // Mulai transaksi
    pg_query($conn, "BEGIN");

    try {
        // Simpan data ke tabel pengguna
        $queryPengguna = "INSERT INTO pengguna (nama_pengguna, email_pengguna, nomor_hp, password, status) 
                          VALUES ('$namaPengguna', '$emailPengguna', '$nomorHp', '$hashedPassword', '$statusPengguna') RETURNING id_pengguna";
        $resultPengguna = pg_query($conn, $queryPengguna);

        if (!$resultPengguna) {
            throw new Exception("Gagal menambahkan pengguna.");
        }

        $rowPengguna = pg_fetch_assoc($resultPengguna);
        $idPenggunaBaru = $rowPengguna['id_pengguna'];

        // Simpan data ke tabel mahasiswa atau dosen berdasarkan status
        if ($statusPengguna == 'mahasiswa') {
            // ... (ambil data mahasiswa dari formulir)
            $queryMahasiswa = "INSERT INTO mahasiswa (id_pengguna, ...) VALUES ('$idPenggunaBaru', ...)";
            $resultMahasiswa = pg_query($conn, $queryMahasiswa);
            if (!$resultMahasiswa) {
                throw new Exception("Gagal menambahkan mahasiswa.");
            }
        } elseif ($statusPengguna == 'dosen') {
            // ... (ambil data dosen dari formulir)
            $queryDosen = "INSERT INTO dosen (id_pengguna, ...) VALUES ('$idPenggunaBaru', ...)";
            $resultDosen = pg_query($conn, $queryDosen);
            if (!$resultDosen) {
                throw new Exception("Gagal menambahkan dosen.");
            }
        }

        // Commit transaksi jika semua berhasil
        pg_query($conn, "COMMIT");

        echo "Pengguna berhasil ditambahkan!";
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        pg_query($conn, "ROLLBACK");

        echo "Terjadi kesalahan: " . $e->getMessage();
    }
}
?>
