<?php
$host = "localhost";
$port = "5432";
$dbname = "SIPA";
$user = "postgres";
$password = "admin";

$connection_string = "host={$host} port={$port} dbname={$dbname} user={$user} password={$password}";
$dbconn = pg_connect($connection_string);

if (!$dbconn) {
    die("Connection failed: " . pg_last_error());
}
?>