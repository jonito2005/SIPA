<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/SIPANEW/database/db.php');

if (!isset($_SESSION['email'])) {
    header("Location: index.html");
    exit();
}

$email = $_SESSION['email'];
$query = "SELECT d.nidn, p.nama_pengguna AS nama_dosen, d.alamat_dosen, p.email_pengguna, p.nomor_hp 
          FROM dosen d 
          JOIN pengguna p ON d.id_pengguna = p.id_pengguna 
          WHERE p.email_pengguna = '$email'";
$result = pg_query($dbconn, $query);

if (!$result) {
    echo "An error occurred.\n";
    exit;
}

$dosen = pg_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Dosen</title>
    <link rel="stylesheet" href="/SIPANEW/css/style.css">
    <script src="/SIPANEW/js/script.js"></script>
</head>
<body>
    <?php include('sidebar.php'); ?>
    <div class="main-content" id="main-content">
        <div class="header">
            <h2>Profile Dosen</h2>
            <div class="user-info">
                <img src="/SIPANEW/img/user.png" alt="User Image" width="40" height="40">
                <span><?php echo $_SESSION['nama_pengguna']; ?></span>
                <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
            </div>
        </div>
        <div class="content">
            <div class="profile-container">
                <img src="/SIPANEW/img/user.png" alt="Profile Image" class="profile-image">
                <h1 class="profile-name"><?php echo $dosen['nama_dosen']; ?></h1>
                <p class="profile-description">NIDN: <?php echo $dosen['nidn']; ?></p>
                <p class="profile-description">Alamat: <?php echo $dosen['alamat_dosen']; ?></p>
                <p class="profile-description">Email: <?php echo $dosen['email_pengguna']; ?></p>
                <p class="profile-description">Nomor HP: <?php echo $dosen['nomor_hp']; ?></p>
            </div>
        </div>
    </div>
</body>
</html>