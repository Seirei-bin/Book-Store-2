<?php
$host = "localhost";
$user = "root"; // ganti kalau beda
$pass = "";
$db   = "toko_buku";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die("Koneksi gagal: " . $conn->connect_error);
}
?>
