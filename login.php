<?php
session_start();
include('database/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = pg_escape_string($dbconn, $_POST['email']);
    $password = pg_escape_string($dbconn, $_POST['password']);
    $hashed_password = md5($password); // Hash password dengan MD5

    $query = "SELECT * FROM pengguna WHERE email_pengguna = '$email' AND password = '$hashed_password'";
    $result = pg_query($dbconn, $query);

    if (pg_num_rows($result) > 0) {
        $user = pg_fetch_assoc($result);
        $_SESSION['email'] = $user['email_pengguna'];
        $_SESSION['nama_pengguna'] = $user['nama_pengguna']; // Simpan nama pengguna dalam sesi
        $_SESSION['status'] = $user['status'];

        if ($user['status'] == 'mahasiswa') {
            header("Location: /SIPANEW/dashboard/dashboard_mahasiswa.php");
        } elseif ($user['status'] == 'dosen') {
            header("Location: /SIPANEW/dashboard/dashboard_dosen.php");
        } else {
            echo "<script>alert('Invalid user status'); window.location.href='index.html';</script>";
        }
    } else {
        echo "<script>alert('Email atau Password salah!'); window.location.href='index.html';</script>";
    }
}
?>

<script src="js/script.js" defer></script> // Tambahkan script.js