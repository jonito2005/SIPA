<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . '/SIPANEW/database/db.php');

if (!isset($_SESSION['email'])) {
    header("Location: index.html");
    exit();
}

$email = $_SESSION['email'];
$query = "SELECT * FROM pengguna WHERE email_pengguna = '$email'";
$result = pg_query($dbconn, $query);
$user = pg_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_pengguna = pg_escape_string($dbconn, $_POST['nama_pengguna']);
    $email_pengguna = pg_escape_string($dbconn, $_POST['email_pengguna']);
    $password = pg_escape_string($dbconn, $_POST['password']);
    $hashed_password = md5($password); // Hash password dengan MD5

    // Handle file upload
    $foto_profil = $_FILES['foto_profil']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($foto_profil);
    move_uploaded_file($_FILES['foto_profil']['tmp_name'], $target_file);

    $update_query = "UPDATE pengguna SET nama_pengguna = '$nama_pengguna', email_pengguna = '$email_pengguna', password = '$hashed_password', foto_profil = '$target_file' WHERE email_pengguna = '$email'";
    $update_result = pg_query($dbconn, $update_query);

    if ($update_result) {
        $_SESSION['email'] = $email_pengguna;
        echo "<script>alert('Profile updated successfully'); window.location.href='settings.php';</script>";
    } else {
        echo "<script>alert('Error updating profile'); window.location.href='settings.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="/SIPANEW/css/settings.css">
    <script src="/SIPANEW/js/script.js"></script>
</head>
<body>
    <?php include('sidebarmhs.php'); ?>
    <div class="main-content" id="main-content">
        <div class="header">
            <h2>Update Profile</h2>
            <div class="user-info">
                <img src="/SIPANEW/img/user.png" alt="User Image" width="40" height="40">
                <span><?php echo $_SESSION['nama_pengguna']; ?></span>
                <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
            </div>
        </div>
        <div class="content">
            <form action="settings.php" method="POST" enctype="multipart/form-data">
                <div class="input-group">
                    <label for="nama_pengguna">Nama Pengguna</label>
                    <input type="text" id="nama_pengguna" name="nama_pengguna" value="<?php echo $user['nama_pengguna']; ?>" required>
                </div>
                <div class="input-group">
                    <label for="email_pengguna">Email</label>
                    <input type="email" id="email_pengguna" name="email_pengguna" value="<?php echo $user['email_pengguna']; ?>" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="input-group">
                    <label for="foto_profil">Foto Profil</label>
                    <input type="file" id="foto_profil" name="foto_profil" accept="image/*">
                </div>
                <button type="submit">Update Profile</button>
            </form>
        </div>
    </div>
</body>
</html>