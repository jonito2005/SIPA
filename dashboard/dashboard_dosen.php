<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/SIPANEW/database/db.php');

if (!isset($_SESSION['email']) || $_SESSION['status'] != 'dosen') {
    header("Location: index.html");
    exit();
}

// Use the established $dbconn connection
if (!$dbconn) {
    echo "An error occurred.\n";
    exit();
}

// --- Function to add users ---
function tambahPengguna($dbconn, $namaPengguna, $statusPengguna, $emailPengguna, $passwordPengguna, $nomorHp) {
    $hashedPassword = password_hash($passwordPengguna, PASSWORD_DEFAULT);

    pg_query($dbconn, "BEGIN");

    try {
        $queryPengguna = "INSERT INTO pengguna (nama_pengguna, email_pengguna, nomor_hp, password, status) 
                          VALUES ('$namaPengguna', '$emailPengguna', '$nomorHp', '$hashedPassword', '$statusPengguna') RETURNING id_pengguna";
        $resultPengguna = @pg_query($dbconn, $queryPengguna); // Suppress direct warnings

        if (!$resultPengguna) {
            throw new Exception("Gagal menambahkan pengguna: " . pg_last_error($dbconn));
        }

        $rowPengguna = pg_fetch_assoc($resultPengguna);
        $idPenggunaBaru = $rowPengguna['id_pengguna'];

        // ... (add code to insert into mahasiswa or dosen tables based on $statusPengguna)

        pg_query($dbconn, "COMMIT");
        return ["Pengguna berhasil ditambahkan!", "success"];
    } catch (Exception $e) {
        pg_query($dbconn, "ROLLBACK");
        return ["Terjadi kesalahan: " . $e->getMessage(), "error"];
    }
}

// --- Function to add bimbingan ---
function tambahBimbingan($dbconn, $idMahasiswa, $idDosen, $tanggalBimbingan, $catatanBimbingan) {
    // Check if the student ID was found
    if ($idMahasiswa !== null) {

        // Make sure the idDosen is properly set from form input
        if (empty($idDosen)) {
            return ["Terjadi kesalahan: ID dosen tidak ditemukan.", "error"];
        }

        // Check for empty values before inserting
        if (empty($idMahasiswa) || empty($idDosen) || empty($tanggalBimbingan)) {
            return ["Terjadi kesalahan: Data bimbingan tidak lengkap.", "error"];
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

// --- Get list of students for the dropdown ---
$queryMahasiswa = "SELECT id_pengguna, nama_pengguna FROM pengguna WHERE status = 'mahasiswa'";
$resultMahasiswa = pg_query($dbconn, $queryMahasiswa);
$mahasiswa = [];
if ($resultMahasiswa) {
    while ($row = pg_fetch_assoc($resultMahasiswa)) {
        $mahasiswa[] = $row;
    }
}

// --- Get list of lecturers for the dropdown ---
$queryDosen = "SELECT id_pengguna, nama_pengguna FROM pengguna WHERE status = 'dosen'";
$resultDosen = pg_query($dbconn, $queryDosen);
$dosen = [];
if ($resultDosen) {
    while ($row = pg_fetch_assoc($resultDosen)) {
        $dosen[] = $row;
    }
}

// --- Handle form submissions ---
$pesan = '';
$pesanClass = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['namaPengguna'])) {
        list($pesan, $pesanClass) = tambahPengguna($dbconn, $_POST["namaPengguna"], $_POST["statusPengguna"], $_POST["emailPengguna"], $_POST["passwordPengguna"], $_POST["nomorHp"]);
    } elseif (isset($_POST['idMahasiswa'])) {
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
            background-color: yellow;
            color: black;
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


    </style>
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
            <div class="forms-container">
                <div class="form-container">
                    <h3>Tambah Pengguna</h3>
                    <?php if (isset($_POST['namaPengguna']) && $pesan): ?>
                        <p class="message <?php echo $pesanClass; ?>"><?php echo $pesan; ?></p>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="text" name="namaPengguna" placeholder="Nama Pengguna">
                        <input type="text" name="statusPengguna" placeholder="Status Pengguna">
                        <input type="email" name="emailPengguna" placeholder="Email Pengguna">
                        <input type="password" name="passwordPengguna" placeholder="Password Pengguna">
                        <input type="text" name="nomorHp" placeholder="Nomor HP">
                        <button type="submit">Buat Pengguna</button>
                    </form>
                </div>
                <div class="form-container">
                    <h3>Tambah Bimbingan</h3>
                    <?php if (isset($_POST['idMahasiswa']) && $pesan): ?>
                        <p class="message <?php echo $pesanClass; ?>"><?php echo $pesan; ?></p>
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
                        <textarea name="catatanBimbingan" placeholder="Catatan Bimbingan"></textarea>
                        <button type="submit">Tambah Bimbingan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
