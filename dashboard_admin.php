<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/SIPANEW/database/db.php');

if (!isset($_SESSION['email']) || $_SESSION['status'] != 'admin') {
    header("Location: index.html");
    exit();
}

// Use the established $dbconn connection
if (!$dbconn) {
    echo "An error occurred.\n";
    exit();
}

// --- Function to add users ---
function tambahPengguna($dbconn, $namaPengguna, $statusPengguna, $emailPengguna, $passwordPengguna, $nomorHp, $additionalData) {
    $hashedPassword = md5($passwordPengguna); // Menggunakan MD5 untuk enkripsi password

    pg_query($dbconn, "BEGIN");

    try {
        $queryPengguna = "INSERT INTO pengguna (nama_pengguna, email_pengguna, nomor_hp, password, status) 
                          VALUES ('$namaPengguna', '$emailPengguna', '$nomorHp', '$hashedPassword', '$statusPengguna') RETURNING id_pengguna";
        $resultPengguna = @pg_query($dbconn, $queryPengguna); // Suppress direct warnings

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

        if ($statusPengguna == 'mahasiswa') {
            $queryMahasiswa = "INSERT INTO mahasiswa (id_pengguna, nim, alamat_mahasiswa, tahun_lulus, program_studi) 
                               VALUES ('$idPenggunaBaru', '{$additionalData['nim']}', '{$additionalData['alamat_mahasiswa']}', '{$additionalData['tahun_lulus']}', '{$additionalData['program_studi']}')";
            $resultMahasiswa = pg_query($dbconn, $queryMahasiswa);
            if (!$resultMahasiswa) {
                throw new Exception("Gagal menambahkan mahasiswa: " . pg_last_error($dbconn));
            }
        } elseif ($statusPengguna == 'dosen') {
            $queryDosen = "INSERT INTO dosen (id_pengguna, nidn, alamat_dosen) 
                           VALUES ('$idPenggunaBaru', '{$additionalData['nidn']}', '{$additionalData['alamat_dosen']}')";
            $resultDosen = pg_query($dbconn, $queryDosen);
            if (!$resultDosen) {
                throw new Exception("Gagal menambahkan dosen: " . pg_last_error($dbconn));
            }
        }

        pg_query($dbconn, "COMMIT");
        return ["Pengguna berhasil ditambahkan!", "success"];
    } catch (Exception $e) {
        pg_query($dbconn, "ROLLBACK");
        if ($e->getMessage() == "Berhasil Menambahkan Pengguna.") {
            return ["Pengguna berhasil ditambahkan!", "success"];
        } else {
            return ["Terjadi kesalahan: " . $e->getMessage(), "error"];
        }
    }
}

// --- Function to edit users ---
function editPengguna($dbconn, $idPengguna, $namaPengguna, $statusPengguna, $emailPengguna, $nomorHp, $additionalData) {
    pg_query($dbconn, "BEGIN");

    try {
        $queryPengguna = "UPDATE pengguna SET nama_pengguna = '$namaPengguna', email_pengguna = '$emailPengguna', nomor_hp = '$nomorHp', status = '$statusPengguna' WHERE id_pengguna = '$idPengguna'";
        $resultPengguna = @pg_query($dbconn, $queryPengguna); // Suppress direct warnings

        if (!$resultPengguna) {
            throw new Exception("Gagal mengedit pengguna: " . pg_last_error($dbconn));
        }

        if ($statusPengguna == 'mahasiswa') {
            $queryMahasiswa = "UPDATE mahasiswa SET nim = '{$additionalData['nim']}', alamat_mahasiswa = '{$additionalData['alamat_mahasiswa']}', tahun_lulus = '{$additionalData['tahun_lulus']}', program_studi = '{$additionalData['program_studi']}' WHERE id_pengguna = '$idPengguna'";
            $resultMahasiswa = pg_query($dbconn, $queryMahasiswa);
            if (!$resultMahasiswa) {
                throw new Exception("Gagal mengedit mahasiswa: " . pg_last_error($dbconn));
            }
        } elseif ($statusPengguna == 'dosen') {
            $queryDosen = "UPDATE dosen SET nidn = '{$additionalData['nidn']}', alamat_dosen = '{$additionalData['alamat_dosen']}' WHERE id_pengguna = '$idPengguna'";
            $resultDosen = pg_query($dbconn, $queryDosen);
            if (!$resultDosen) {
                throw new Exception("Gagal mengedit dosen: " . pg_last_error($dbconn));
            }
        }

        pg_query($dbconn, "COMMIT");
        return ["Pengguna berhasil diedit!", "success"];
    } catch (Exception $e) {
        pg_query($dbconn, "ROLLBACK");
        return ["Terjadi kesalahan: " . $e->getMessage(), "error"];
    }
}

// --- Function to delete users ---
function hapusPengguna($dbconn, $idPengguna) {
    pg_query($dbconn, "BEGIN");

    try {
        $queryPengguna = "DELETE FROM pengguna WHERE id_pengguna = '$idPengguna'";
        $resultPengguna = @pg_query($dbconn, $queryPengguna); // Suppress direct warnings

        if (!$resultPengguna) {
            throw new Exception("Gagal menghapus pengguna: " . pg_last_error($dbconn));
        }

        pg_query($dbconn, "COMMIT");
        return ["Pengguna berhasil dihapus!", "success"];
    } catch (Exception $e) {
        pg_query($dbconn, "ROLLBACK");
        return ["Terjadi kesalahan: " . $e->getMessage(), "error"];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['namaPengguna'])) {
        $additionalData = [];
        if ($_POST["statusPengguna"] == 'mahasiswa') {
            $additionalData = [
                'nim' => $_POST["nim"],
                'alamat_mahasiswa' => $_POST["alamat_mahasiswa"],
                'tahun_lulus' => $_POST["tahun_lulus"],
                'program_studi' => $_POST["program_studi"]
            ];
        } elseif ($_POST["statusPengguna"] == 'dosen') {
            $additionalData = [
                'nidn' => $_POST["nidn"],
                'alamat_dosen' => $_POST["alamat_dosen"]
            ];
        }
        list($pesan, $pesanClass) = tambahPengguna($dbconn, $_POST["namaPengguna"], $_POST["statusPengguna"], $_POST["emailPengguna"], $_POST["passwordPengguna"], $_POST["nomorHp"], $additionalData);
    } elseif (isset($_POST['idPenggunaEdit'])) {
        $additionalData = [];
        if ($_POST["statusPengguna"] == 'mahasiswa') {
            $additionalData = [
                'nim' => $_POST["nim"],
                'alamat_mahasiswa' => $_POST["alamat_mahasiswa"],
                'tahun_lulus' => $_POST["tahun_lulus"],
                'program_studi' => $_POST["program_studi"]
            ];
        } elseif ($_POST["statusPengguna"] == 'dosen') {
            $additionalData = [
                'nidn' => $_POST["nidn"],
                'alamat_dosen' => $_POST["alamat_dosen"]
            ];
        }
        list($pesan, $pesanClass) = editPengguna($dbconn, $_POST["idPenggunaEdit"], $_POST["namaPengguna"], $_POST["statusPengguna"], $_POST["emailPengguna"], $_POST["nomorHp"], $additionalData);
    } elseif (isset($_POST['idPenggunaHapus'])) {
        list($pesan, $pesanClass) = hapusPengguna($dbconn, $_POST["idPenggunaHapus"]);
    } elseif (isset($_POST['idMahasiswa'])) {
        list($pesan, $pesanClass) = tambahBimbingan($dbconn, $_POST["idMahasiswa"], $_POST["idDosen"], $_POST["tanggalBimbingan"], $_POST["catatanBimbingan"]);
    } elseif (isset($_POST['idMahasiswaKHS'])) {
        list($pesan, $pesanClass) = tambahKHS($dbconn, $_POST["idMahasiswaKHS"], $_POST["semester"], $_POST["ipk"], $_POST["tahunAjaran"], $_POST["fileKHS"]);
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

// --- Function to add KHS ---
function tambahKHS($dbconn, $idMahasiswa, $semester, $ipk, $tahunAjaran, $fileKHS) {
    // Check if the student ID was found
    if ($idMahasiswa !== null) {

        // Check for empty values before inserting
        if (empty($idMahasiswa) || empty($semester) || empty($ipk) || empty($tahunAjaran) || empty($fileKHS)) {
            return ["Terjadi kesalahan: Data KHS tidak lengkap.", "error"];
        }

        $queryKHS = "INSERT INTO khs (id_mahasiswa, semester, ipk, tahun_ajaran, file_khs) 
                     VALUES ('$idMahasiswa', '$semester', '$ipk', '$tahunAjaran', '$fileKHS')";
        $resultKHS = @pg_query($dbconn, $queryKHS); // Suppress direct warnings

        if ($resultKHS) {
            return ["KHS berhasil ditambahkan!", "success"];
        } else {
            $error = pg_last_error($dbconn);
            if (strpos($error, 'Mahasiswa sudah memiliki KHS untuk semester dan tahun ajaran ini') !== false) {
                return ["KHS Berhasil ditambahkan ke Mahasiswa", "success"];
            } else {
                return ["Terjadi kesalahan: " . $error, "error"];
            }
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

// --- Get list of users for management ---
$queryPengguna = "SELECT * FROM pengguna";
$resultPengguna = pg_query($dbconn, $queryPengguna);
$pengguna = [];
if ($resultPengguna) {
    while ($row = pg_fetch_assoc($resultPengguna)) {
        $pengguna[] = $row;
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


    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <h1>SIPA Pendidikan</h1>
        <ul>
            <li><a href="dashboard_admin.php">Dashboard</a></li>
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
        <input type="text" name="namaPengguna" placeholder="Nama Pengguna" required>
        <input type="email" name="emailPengguna" placeholder="Email Pengguna" required>
        <input type="password" name="passwordPengguna" placeholder="Password Pengguna" required>
        <input type="text" name="nomorHp" placeholder="Nomor HP" required>
        <div class="select-wrapper">
            <select name="statusPengguna" id="statusPengguna" onchange="toggleAdditionalFields()" required>
                <option value="">Pilih Status Pengguna</option>
                <option value="mahasiswa">Mahasiswa</option>
                <option value="dosen">Dosen</option>
            </select>
            <div class="select-arrow"></div>
        </div>
        <div id="mahasiswaFields" style="display: none;">
            <input type="text" name="nim" placeholder="NIM">
            <input type="text" name="program_studi" placeholder="Program Studi">
            <input type="text" name="alamat_mahasiswa" placeholder="Alamat Mahasiswa">
            <input type="number" name="tahun_lulus" placeholder="Tahun Lulus">
        </div>
        <div id="dosenFields" style="display: none;">
            <input type="text" name="nidn" placeholder="NIDN">
            <input type="text" name="alamat_dosen" placeholder="Alamat Dosen">
        </div>
        <button type="submit">Buat Pengguna</button>
    </form>
</div>
                <div class="form-container">
                    <h3>Kelola Pengguna</h3>
                    <?php if (isset($_POST['idPenggunaEdit']) && $pesan): ?>
                        <p class="message <?php echo $pesanClass; ?>"><?php echo $pesan; ?></p>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="select-wrapper"> 
                            <select name="idPenggunaEdit" onchange="populateEditForm(this.value)">
                                <option value="">Pilih Pengguna</option>
                                <?php foreach ($pengguna as $user): ?>
                                    <option value="<?php echo $user['id_pengguna']; ?>"><?php echo $user['nama_pengguna']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="select-arrow"></div> 
                        </div>
                        <input type="text" name="namaPengguna" placeholder="Nama Pengguna" required>
                        <input type="email" name="emailPengguna" placeholder="Email Pengguna" required>
                        <input type="text" name="nomorHp" placeholder="Nomor HP" required>
                        <div class="select-wrapper">
                            <select name="statusPengguna" id="statusPenggunaEdit" onchange="toggleAdditionalFieldsEdit()" required>
                                <option value="">Pilih Status Pengguna</option>
                                <option value="mahasiswa">Mahasiswa</option>
                                <option value="dosen">Dosen</option>
                            </select>
                            <div class="select-arrow"></div>
                        </div>
                        <div id="mahasiswaFieldsEdit" style="display: none;">
                            <input type="text" name="nim" placeholder="NIM">
                            <input type="text" name="program_studi" placeholder="Program Studi">
                            <input type="text" name="alamat_mahasiswa" placeholder="Alamat Mahasiswa">
                            <input type="number" name="tahun_lulus" placeholder="Tahun Lulus">
                        </div>
                        <div id="dosenFieldsEdit" style="display: none;">
                            <input type="text" name="nidn" placeholder="NIDN">
                            <input type="text" name="alamat_dosen" placeholder="Alamat Dosen">
                        </div>
                        <button type="submit">Edit Pengguna</button>
                    </form>
                    <form method="POST">
                        <div class="select-wrapper"> 
                            <select name="idPenggunaHapus">
                                <option value="">Pilih Pengguna</option>
                                <?php foreach ($pengguna as $user): ?>
                                    <option value="<?php echo $user['id_pengguna']; ?>"><?php echo $user['nama_pengguna']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="select-arrow"></div> 
                        </div>
                        <button type="submit">Hapus Pengguna</button>
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
                <div class="form-container">
                    <h3>Tambah KHS</h3>
                    <?php if (isset($_POST['idMahasiswaKHS']) && $pesan): ?>
                        <p class="message <?php echo $pesanClass; ?>"><?php echo $pesan; ?></p>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="select-wrapper"> 
                            <select name="idMahasiswaKHS">
                                <option value="">Pilih Mahasiswa</option>
                                <?php foreach ($mahasiswa as $mhs): ?>
                                    <option value="<?php echo $mhs['id_pengguna']; ?>"><?php echo $mhs['nama_pengguna']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="select-arrow"></div> 
                        </div>
                        <input type="number" name="semester" placeholder="Semester" required>
                        <input type="number" name="ipk" placeholder="IPK" required>
                        <input type="text" name="tahunAjaran" placeholder="Tahun Ajaran" required>
                        <input type="text" name="fileKHS" placeholder="File KHS" required>
                        <button type="submit">Tambah KHS</button>
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
        function toggleAdditionalFields() {
        var statusPengguna = document.getElementById('statusPengguna').value;
        var mahasiswaFields = document.getElementById('mahasiswaFields');
        var dosenFields = document.getElementById('dosenFields');

        if (statusPengguna === 'mahasiswa') {
            mahasiswaFields.style.display = 'block';
            dosenFields.style.display = 'none';
        } else if (statusPengguna === 'dosen') {
            mahasiswaFields.style.display = 'none';
            dosenFields.style.display = 'block';
        } else {
            mahasiswaFields.style.display = 'none';
            dosenFields.style.display = 'none';
            }
        }
        function toggleAdditionalFieldsEdit() {
        var statusPengguna = document.getElementById('statusPenggunaEdit').value;
        var mahasiswaFields = document.getElementById('mahasiswaFieldsEdit');
        var dosenFields = document.getElementById('dosenFieldsEdit');

        if (statusPengguna === 'mahasiswa') {
            mahasiswaFields.style.display = 'block';
            dosenFields.style.display = 'none';
        } else if (statusPengguna === 'dosen') {
            mahasiswaFields.style.display = 'none';
            dosenFields.style.display = 'block';
        } else {
            mahasiswaFields.style.display = 'none';
            dosenFields.style.display = 'none';
            }
        }
        function populateEditForm(idPengguna) {
            // Fetch user data and populate the form fields
            // This function should be implemented to fetch user data via AJAX or similar method
        }
    </script>
</body>
</html>