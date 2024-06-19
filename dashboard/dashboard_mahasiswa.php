<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/SIPANEW/database/db.php'); 

if (!isset($_SESSION['email']) || $_SESSION['status'] != 'mahasiswa') {
    header("Location: index.html");
    exit();
}

$nama_mahasiswa = isset($_SESSION['nama_pengguna']) ? $_SESSION['nama_pengguna'] : 'Mahasiswa';
$email = $_SESSION['email'];

// Connect to PostgreSQL
$connection_string = "host=localhost port=5432 dbname=SIPA user=postgres password=admin";
$conn = pg_connect($connection_string);
if (!$conn) {
    die("Could not connect to the database: " . pg_last_error());
}

// Ambil ID mahasiswa dari sesi
$query_mahasiswa = "SELECT id_mahasiswa FROM mahasiswa WHERE id_pengguna = (SELECT id_pengguna FROM pengguna WHERE email_pengguna = $1)";
$result_mahasiswa = pg_query_params($conn, $query_mahasiswa, array($email));
if ($result_mahasiswa) {
    $mahasiswa = pg_fetch_assoc($result_mahasiswa);
    $id_mahasiswa = $mahasiswa['id_mahasiswa'];
} else {
    die("Error in SQL query: " . pg_last_error());
}

// Query untuk mengambil data KHS
$query_khs = "SELECT * FROM khs WHERE id_mahasiswa = $1";
$result_khs = pg_query_params($conn, $query_khs, array($id_mahasiswa));
if (!$result_khs) {
    die("Error in SQL query: " . pg_last_error());
}

// Query untuk mengambil data Bimbingan
$query_bimbingan = "SELECT b.*, p.nama_pengguna AS nama_dosen FROM bimbingan b
                    JOIN dosen d ON b.id_dosen = d.id_dosen
                    JOIN pengguna p ON d.id_pengguna = p.id_pengguna
                    WHERE b.id_mahasiswa = $1";
$result_bimbingan = pg_query_params($conn, $query_bimbingan, array($id_mahasiswa));
if (!$result_bimbingan) {
    die("Error in SQL query: " . pg_last_error());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa</title>
    <link rel="stylesheet" href="/SIPANEW/css/dashboardmhs.css">
</head>
<body>
    <div class="sidebar" id="sidebar">
        <h1>SIPA Pendidikan</h1>
        <ul>
            <li><a href="dashboard_mahasiswa.php">Dashboard</a></li>
            <li><a href="profilemhs.php">Profile</a></li>
            <li><a href="settingsmhs.php">Settings</a></li>
            <li><a href="logout.php">Log Out</a></li>
        </ul>
    </div>

    <div class="main-content" id="main-content">
        <div class="header">
            <h2>Selamat Datang, <?php echo $nama_mahasiswa; ?></h2>
            <div class="user-info">
                <img src="/SIPANEW/img/usermhs.png" alt="User Image" width="40" height="40">
                <span><?php echo $nama_mahasiswa; ?></span>
                <button class="toggle-btn" onclick="togglesidebar()">☰</button>
            </div>
        </div>

        <div class="content">
            <h3>Kartu Hasil Studi (KHS)</h3>
            <table border:1>
                <tr>
                    <th>Semester</th>
                    <th>IPK</th>
                    <th>Tahun Ajaran</th>
                    <th>File KHS</th>
                </tr>
                <?php while ($row = pg_fetch_assoc($result_khs)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['semester']); ?></td>
                    <td><?php echo htmlspecialchars($row['ipk']); ?></td>
                    <td><?php echo htmlspecialchars($row['tahun_ajaran']); ?></td>
                    <td><a href="<?php echo htmlspecialchars($row['file_khs']); ?>">Download</a></td>
                </tr>
                <?php endwhile; ?>
            </table>

            <h3>Bimbingan</h3>
            <table border:1>
                <tr>
                    <th>Tanggal Bimbingan</th>
                    <th>Nama Dosen</th>
                    <th>Catatan</th>
                </tr>
                <?php while ($row = pg_fetch_assoc($result_bimbingan)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['tanggal_bimbingan']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_dosen']); ?></td>
                    <td><?php echo htmlspecialchars($row['catatan']); ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
    <footer>
        &copy; 2024 SIPA Pendidikan.
    </footer>

    <script>
        function togglesidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            sidebar.classList.toggle('open');
            mainContent.classList.toggle('shifted');
        }
    </script>
</body>
</html>

<?php
// Tutup koneksi
pg_close($conn);
?>
