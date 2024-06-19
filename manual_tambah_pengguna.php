<?php
include('db.php');

$nama_pengguna = 'John Doe';
$email_pengguna = 'john.doe@example.com';
$nomor_hp = 1234567890;
$password = md5('123'); // Hash password dengan MD5
$status = 'mahasiswa';

$query = "INSERT INTO pengguna (nama_pengguna, email_pengguna, nomor_hp, password, status) VALUES ('$nama_pengguna', '$email_pengguna', $nomor_hp, '$password', '$status')";
$result = pg_query($dbconn, $query);

if ($result) {
    echo "Pengguna berhasil ditambahkan.";
} else {
    echo "Error: " . pg_last_error($dbconn);
}
?>