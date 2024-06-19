<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/SIPANEW/database/db.php');

if (!isset($_SESSION['email'])) {
    header("Location: index.html");
    exit();
}

$email = $_SESSION['email'];
$query = "SELECT d.nim, p.nama_pengguna AS nama_mahasiswa, d.alamat_mahasiswa, p.email_pengguna, p.nomor_hp 
          FROM mahasiswa d 
          JOIN pengguna p ON d.id_pengguna = p.id_pengguna 
          WHERE p.email_pengguna = '$email'";
$result = pg_query($dbconn, $query);

if (!$result) {
    echo "An error occurred.\n";
    exit;
}

$mahasiswa = pg_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Mahasiswa</title>
    <link rel="stylesheet" href="/SIPANEW/css/style.css">
    <script src="/SIPANEW/js/script.js"></script>
</head>
<body>
    <?php include('sidebarmhs.php'); ?>
    <div class="main-content" id="main-content">
        <div class="header">
            <h2>Profile Mahasiswa</h2>
            <div class="user-info">
                <img src="/SIPANEW/img/user.png" alt="User Image" width="40" height="40">
                <span><?php echo $_SESSION['nama_pengguna']; ?></span>
                <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
            </div>
        </div>
        <div class="content">
            <div class="profile-container">
                <img src="/SIPANEW/img/user.png" alt="Profile Image" class="profile-image">
                <h1 class="profile-name"><?php echo $mahasiswa['nama_mahasiswa']; ?></h1>
                <p class="profile-description">NIM: <?php echo $mahasiswa['nim']; ?></p>
                <p class="profile-description">Alamat: <?php echo $mahasiswa['alamat_mahasiswa']; ?></p>
                <p class="profile-description">Email: <?php echo $mahasiswa['email_pengguna']; ?></p>
                <p class="profile-description">Nomor HP: <?php echo $mahasiswa['nomor_hp']; ?></p>
            </div>
        </div>
    </div>
</body>
</html>