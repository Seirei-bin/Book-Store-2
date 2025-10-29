<?php
session_start();
include '../config.php';

// Cek login admin
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// Cek parameter
if (!isset($_GET['file'])) {
    die("<p class='text-red-500 text-center mt-4'>File bukti tidak ditemukan.</p>");
}

$file = basename($_GET['file']); // mencegah directory traversal
$path = "../uploads/bukti/" . $file;

if (!file_exists($path)) {
    die("<p class='text-red-500 text-center mt-4'>File bukti tidak ditemukan.</p>");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lihat Bukti Pembayaran</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

<div class="max-w-4xl mx-auto bg-white shadow rounded-lg p-6 text-center">
    <h1 class="text-2xl font-bold mb-4 text-blue-600">📄 Bukti Pembayaran</h1>

    <img src="<?= $path ?>" alt="Bukti Pembayaran" class="mx-auto rounded border">

    <div class="mt-6">
        <a href="orders.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded shadow">
            ← Kembali ke Orders
        </a>
    </div>
</div>

</body>
</html>
