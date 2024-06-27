<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/SIPANEW/database/db.php');

if (!isset($_SESSION['email']) || $_SESSION['status'] != 'dosen') {
    header("Location: index.html");
    exit();
}

// Gunakan koneksi $dbconn yang sudah dibuat
if (!$dbconn) {
    echo "Terjadi kesalahan.\n";
    exit();
}

// --- Fungsi untuk menambahkan mahasiswa ---
function tambahMahasiswa($dbconn, $namaPengguna, $emailPengguna, $passwordPengguna, $nomorHp, $nim, $alamatMahasiswa, $tahunLulus, $programStudi) {
    $hashedPassword = md5($passwordPengguna);

    pg_query($dbconn, "BEGIN");

    try {
        $queryPengguna = "INSERT INTO pengguna (nama_pengguna, email_pengguna, nomor_hp, password, status) 
                          VALUES ('$namaPengguna', '$emailPengguna', '$nomorHp', '$hashedPassword', 'mahasiswa') RETURNING id_pengguna";
        $resultPengguna = @pg_query($dbconn, $queryPengguna); // Hapus peringatan langsung

        if (!$resultPengguna) {
            $error = pg_last_error($dbconn);
            if (strpos($error, 'duplicate key value violates unique constraint') !== false) {
                throw new Exception("Berhasil Menambahkan Pengguna.");
            } else {
                throw new Exception("Gagal menambahkan pengguna: " . $error);
            }
        }

        $rowPengguna = pg_fetch_assoc($resultPengguna);
        $idPenggunaBaru = $rowPengguna['id_pengguna'];

        $queryMahasiswa = "INSERT INTO mahasiswa (id_pengguna, nim, alamat_mahasiswa, tahun_lulus, program_studi) 
                           VALUES ('$idPenggunaBaru', '$nim', '$alamatMahasiswa', '$tahunLulus', '$programStudi')";
        $resultMahasiswa = pg_query($dbconn, $queryMahasiswa);
        if (!$resultMahasiswa) {
            throw new Exception("Gagal menambahkan mahasiswa: " . pg_last_error($dbconn));
        }

        pg_query($dbconn, "COMMIT");
        return ["Mahasiswa berhasil ditambahkan!", "success"];
    } catch (Exception $e) {
        pg_query($dbconn, "ROLLBACK");
        if ($e->getMessage() == "Berhasil Menambahkan Pengguna.") {
            return ["Mahasiswa berhasil ditambahkan!", "success"];
        } else {
            return ["Terjadi kesalahan: " . $e->getMessage(), "error"];
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['namaPengguna'])) {
        list($pesan, $pesanClass) = tambahMahasiswa($dbconn, $_POST["namaPengguna"], $_POST["emailPengguna"], $_POST["passwordPengguna"], $_POST["nomorHp"], $_POST["nim"], $_POST["alamat_mahasiswa"], $_POST["tahun_lulus"], $_POST["program_studi"]);
    } elseif (isset($_POST['idMahasiswa'])) {
        list($pesan, $pesanClass) = tambahBimbingan($dbconn, $_POST["idMahasiswa"], $_POST["idDosen"], $_POST["tanggalBimbingan"], $_POST["catatanBimbingan"]);
    }
}

// --- Fungsi untuk menambahkan bimbingan ---
function tambahBimbingan($dbconn, $idMahasiswa, $idDosen, $tanggalBimbingan, $catatanBimbingan) {
    // Periksa apakah ID mahasiswa ditemukan
    if ($idMahasiswa !== null) {

        // Pastikan idDosen diatur dengan benar dari sesi
        if (empty($idDosen)) {
            return ["Terjadi kesalahan: ID dosen tidak ditemukan.", "error"];
        }

        // Periksa nilai kosong sebelum memasukkan
        if (empty($idMahasiswa) || empty($idDosen) || empty($tanggalBimbingan)) {
            return ["Terjadi kesalahan: Data bimbingan tidak lengkap.", "error"];
        }

        // Periksa apakah bimbingan sudah ada
        $queryCheck = "SELECT * FROM bimbingan WHERE id_mahasiswa = '$idMahasiswa' AND id_dosen = '$idDosen' AND tanggal_bimbingan = '$tanggalBimbingan'";
        $resultCheck = pg_query($dbconn, $queryCheck);
        if (pg_num_rows($resultCheck) > 0) {
            return ["Bimbingan berhasil ditambahkan!", "success"];
        }

        $queryBimbingan = "INSERT INTO bimbingan (id_mahasiswa, id_dosen, tanggal_bimbingan, catatan) 
                           VALUES ('$idMahasiswa', '$idDosen', '$tanggalBimbingan', '$catatanBimbingan')";
        $resultBimbingan = pg_query($dbconn, $queryBimbingan);

        if ($resultBimbingan) {
            return ["Bimbingan berhasil ditambahkan!", "success"];
        } else {
            return ["Terjadi kesalahan: " . pg_last_error($dbconn), "error"];
        }
    } else {
        return ["Mahasiswa dengan ID '$idMahasiswa' tidak ditemukan.", "error"];
    }
}

// --- Dapatkan daftar mahasiswa untuk dropdown ---
$queryMahasiswa = "SELECT id_pengguna, nama_pengguna FROM pengguna WHERE status = 'mahasiswa'";
$resultMahasiswa = pg_query($dbconn, $queryMahasiswa);
$mahasiswa = [];
if ($resultMahasiswa) {
    while ($row = pg_fetch_assoc($resultMahasiswa)) {
        $mahasiswa[] = $row;
    }
}

// --- Dapatkan daftar dosen untuk dropdown ---
$queryDosen = "SELECT id_pengguna, nama_pengguna FROM pengguna WHERE status = 'dosen'";
$resultDosen = pg_query($dbconn, $queryDosen);
$dosen = [];
if ($resultDosen) {
    while ($row = pg_fetch_assoc($resultDosen)) {
        $dosen[] = $row;
    }
}

// --- Dapatkan statistik ---
$queryJumlahMahasiswa = "SELECT COUNT(*) AS jumlah_mahasiswa FROM pengguna WHERE status = 'mahasiswa'";
$resultJumlahMahasiswa = pg_query($dbconn, $queryJumlahMahasiswa);
$jumlahMahasiswa = pg_fetch_assoc($resultJumlahMahasiswa)['jumlah_mahasiswa'];

$queryJumlahDosen = "SELECT COUNT(*) AS jumlah_dosen FROM pengguna WHERE status = 'dosen'";
$resultJumlahDosen = pg_query($dbconn, $queryJumlahDosen);
$jumlahDosen = pg_fetch_assoc($resultJumlahDosen)['jumlah_dosen'];

$queryIpkTertinggi = "SELECT MAX(ipk) AS ipk_tertinggi FROM khs";
$resultIpkTertinggi = pg_query($dbconn, $queryIpkTertinggi);
$ipkTertinggi = pg_fetch_assoc($resultIpkTertinggi)['ipk_tertinggi'];

// --- Tangani pengiriman formulir ---
$pesan = '';
$pesanClass = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['namaPengguna'])) {
        list($pesan, $pesanClass) = tambahMahasiswa($dbconn, $_POST["namaPengguna"], $_POST["emailPengguna"], $_POST["passwordPengguna"], $_POST["nomorHp"], $_POST["nim"], $_POST["alamat_mahasiswa"], $_POST["tahun_lulus"], $_POST["program_studi"]);
    } elseif (isset($_POST['idMahasiswa'])) { // Hanya tangani formulir bimbingan di sini
        list($pesan, $pesanClass) = tambahBimbingan($dbconn, $_POST["idMahasiswa"], $_POST["idDosen"], $_POST["tanggalBimbingan"], $_POST["catatanBimbingan"]);
    } 
}

$nama_dosen = isset($_SESSION['nama_pengguna']) ? $_SESSION['nama_pengguna'] : 'Dosen';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dosen</title>
    <link rel="stylesheet" href="/SIPANEW/css/dashboard.css">
    <style>
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .success {
            background-color: green;
            color: white;
        }
        .error {
            background-color: red;
            color: white;
        }
        .select-wrapper {
         position: relative;
         display: inline-block;
         margin-bottom: 15px; /* Menambah ruang antar dropdown */
         width: 100%; /* Mengatur lebar wrapper sesuai dengan kontainer */
         max-width: 300px; /* Batas maksimal lebar dropdown */
        }

        .select-wrapper {
    position: relative;
    display: inline-block;
    margin-bottom: 15px;
    width: 100%; 
    max-width: 550px; /* Menambah lebar maksimal */
}

.select-wrapper select {
    padding: 12px; 
    border: 1px solid #ccc;
    border-radius: 5px;
    appearance: none;
    background-color: #fff;
    width: 100%;
    max-width: 100%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    font-size: 16px;
    box-sizing: border-box; 
}

.select-arrow {
    position: absolute;
    top: 50%;
    right: 15px; 
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid #000;
    pointer-events: none;
}

/* Gaya Statistik */
.stats-container {
    display: flex;
    justify-content: space-between;
    width: 80%;
    margin: 20px auto;
}

.stat-box {
    background-color: #2f4f4f;
    padding: 20px;
    border-radius: 8px;
    color: white;
    margin-bottom: 20px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    flex: 1;
    margin: 0 10px;
    text-align: center;
}

.stat-box h3 {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 18px;
}

.stat-box p {
    font-size: 24px;
    font-weight: bold;
    color: #ff4500;
}

/* Warna latar belakang yang berbeda untuk setiap statistik */
.stat-box:nth-child(1) {
    background-color: #004d40;
}

.stat-box:nth-child(2) {
    background-color: #004d40;
}

.stat-box:nth-child(3) {
    background-color: #004d40;
}
</style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <h1>SIPA Pendidikan</h1>
        <ul>
            <li><a href="dashboard_dosen.php">Dashboard</a></li>
            <li><a href="laporan.php">Laporan</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="settings.php">Settings</a></li>
            <li><a href="logout.php">Log Out</a></li>
        </ul>
    </div>
    <div class="main-content" id="main-content">
        <div class="header">
            <h2>Selamat Datang, <?php echo $nama_dosen; ?></h2>
            <div class="user-info">
                <img src="/SIPANEW/img/user.png" alt="User Image" width="40" height="40">
                <span><?php echo $nama_dosen; ?></span>
                <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
            </div>
        </div>
        <div class="content">
            <div class="stats-container">
                <div class="stat-box">
                    <h3>Jumlah Mahasiswa</h3>
                    <p><?php echo $jumlahMahasiswa; ?></p>
                </div>
                <div class="stat-box">
                    <h3>Jumlah Dosen</h3>
                    <p><?php echo $jumlahDosen; ?></p>
                </div>
                <div class="stat-box">
                    <h3>IPK Tertinggi</h3>
                    <p><?php echo $ipkTertinggi; ?></p>
                </div>
            </div>
            <div class="forms-container">
                <div class="form-container">
                    <h3>Tambah Mahasiswa</h3>
                    <?php if (isset($_POST['namaPengguna']) && $pesan): ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    icon: '<?php echo $pesanClass; ?>',
                                    title: '<?php echo $pesanClass == "success" ? "Berhasil" : "Gagal"; ?>',
                                    text: '<?php echo $pesan; ?>'
                                });
                            });
                        </script>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="text" name="namaPengguna" placeholder="Nama Pengguna" required>
                        <input type="email" name="emailPengguna" placeholder="Email Pengguna" required>
                        <input type="password" name="passwordPengguna" placeholder="Password Pengguna" required>
                        <input type="text" name="nomorHp" placeholder="Nomor HP" required>
                        <input type="text" name="nim" placeholder="NIM" required>
                        <input type="text" name="program_studi" placeholder="Program Studi" required>
                        <input type="text" name="alamat_mahasiswa" placeholder="Alamat Mahasiswa" required>
                        <input type="number" name="tahun_lulus" placeholder="Tahun Lulus" required>
                        <button type="submit">Buat Mahasiswa</button>
                    </form>
                    </div>
                <div class="form-container">
                    <h3>Tambah Bimbingan</h3>
                    <?php if (isset($_POST['idMahasiswa']) && $pesan): ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    icon: '<?php echo $pesanClass; ?>',
                                    title: '<?php echo $pesanClass == "success" ? "Berhasil" : "Gagal"; ?>',
                                    text: '<?php echo $pesan; ?>'
                                });
                            });
                        </script>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="select-wrapper"> 
                            <select name="idMahasiswa">
                                <option value="">Pilih Mahasiswa</option>
                                <?php foreach ($mahasiswa as $mhs): ?>
                                    <option value="<?php echo $mhs['id_pengguna']; ?>"><?php echo $mhs['nama_pengguna']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="select-arrow"></div> 
                        </div>
                        <div class="select-wrapper"> 
                            <select name="idDosen">
                                <option value="">Pilih Dosen</option>
                                <?php foreach ($dosen as $dsn): ?>
                                    <option value="<?php echo $dsn['id_pengguna']; ?>"><?php echo $dsn['nama_pengguna']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="select-arrow"></div> 
                        </div>
                        <input type="date" name="tanggalBimbingan" placeholder="Tanggal Bimbingan">
                        <textarea name="catatanBimbingan" placeholder="Catatan Bimbingan" placeholder="Catatan Bimbingan"></textarea>
                        <button type="submit">Tambah Bimbingan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <footer>
        &copy; 2024 SIPA Pendidikan.
    </footer>

    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById('sidebar');
            var mainContent = document.getElementById('main-content');
            sidebar.classList.toggle('open');
            mainContent.classList.toggle('shifted');
}
</script>
</body>
</html>
