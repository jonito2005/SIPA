<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/SIPANEW/database/db.php');

if (!isset($_SESSION['email'])) {
    header("Location: index.html");
    exit();
}

$filters = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['nama_mahasiswa'])) {
        $filters[] = "p.nama_pengguna ILIKE '%" . pg_escape_string($dbconn, $_POST['nama_mahasiswa']) . "%'";
    }
    if (!empty($_POST['nim'])) {
        $filters[] = "m.nim = '" . pg_escape_string($dbconn, $_POST['nim']) . "'";
    }
    if (!empty($_POST['prodi'])) {
        $filters[] = "m.program_studi ILIKE '%" . pg_escape_string($dbconn, $_POST['prodi']) . "%'";
    }
    if (!empty($_POST['bimbingan'])) {
        $filters[] = "b.catatan ILIKE '%" . pg_escape_string($dbconn, $_POST['bimbingan']) . "%'";
    }
    if (!empty($_POST['semester'])) {
        $filters[] = "k.semester = " . pg_escape_string($dbconn, $_POST['semester']);
    }
    if (!empty($_POST['tahun_ajaran'])) {
        $filters[] = "k.tahun_ajaran = '" . pg_escape_string($dbconn, $_POST['tahun_ajaran']) . "'";
    }
    if (!empty($_POST['ipk'])) {
        $filters[] = "k.ipk = " . pg_escape_string($dbconn, $_POST['ipk']);
    }
}

$query = "SELECT m.nim, p.nama_pengguna AS nama_mahasiswa, m.program_studi, b.catatan, k.semester, k.tahun_ajaran, k.ipk
          FROM mahasiswa m
          LEFT JOIN pengguna p ON m.id_pengguna = p.id_pengguna
          LEFT JOIN bimbingan b ON m.id_mahasiswa = b.id_mahasiswa
          LEFT JOIN khs k ON m.id_mahasiswa = k.id_mahasiswa";

if (!empty($filters)) {
    $query .= " WHERE " . implode(" AND ", $filters);
}

$result = pg_query($dbconn, $query);

if (!$result) {
    echo "An error occurred.\n";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Mahasiswa</title>
    <link rel="stylesheet" href="/SIPANEW/css/laporan.css">
    <script src="/SIPANEW/js/script.js"></script>
</head>
<body>
    <?php include('sidebar.php'); ?>
    <div class="main-content" id="main-content">
        <div class="header">
            <h2>Laporan Mahasiswa</h2>
            <div class="user-info">
                <img src="/SIPANEW/img/user.png" alt="User Image" width="40" height="40">
                <span><?php echo $_SESSION['nama_pengguna']; ?></span>
                <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
            </div>
        </div>
       
            </form>
            <table>
                <thead>
                    <tr>
                        <th>NIM</th>
                        <th>Nama Mahasiswa</th>
                        <th>Program Studi</th>
                        <th>Bimbingan</th>
                        <th>Semester</th>
                        <th>Tahun Ajaran</th>
                        <th>IPK</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = pg_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nim']); ?></td>
                            <td><?php echo htmlspecialchars($row['nama_mahasiswa']); ?></td>
                            <td><?php echo htmlspecialchars($row['program_studi']); ?></td>
                            <td><?php echo htmlspecialchars($row['catatan']); ?></td>
                            <td><?php echo htmlspecialchars($row['semester']); ?></td>
                            <td><?php echo htmlspecialchars($row['tahun_ajaran']); ?></td>
                            <td><?php echo htmlspecialchars($row['ipk']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>