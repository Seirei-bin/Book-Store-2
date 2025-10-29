<?php
include '../config.php';
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit;
}

if(!isset($_GET['id'])){
    die("Buku tidak ditemukan.");
}

$id = (int)$_GET['id'];
$book = $conn->query("SELECT * FROM books WHERE id=$id")->fetch_assoc();

if(!$book){
    die("Buku tidak ditemukan.");
}

// jika form disubmit
if(isset($_POST['stok'])){
    $stok_baru = (int)$_POST['stok'];
    $conn->query("UPDATE books SET stok=$stok_baru WHERE id=$id");
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Stok</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

<div class="max-w-md mx-auto bg-white shadow-md rounded-lg p-6">
  <h1 class="text-xl font-bold mb-4">Ubah Stok: <?= htmlspecialchars($book['judul']) ?></h1>
  <form method="POST">
    <label class="block mb-2 font-semibold">Stok Saat Ini: <?= $book['stok'] ?></label>
    <input type="number" name="stok" value="<?= $book['stok'] ?>" class="border px-3 py-2 w-full rounded mb-4">
    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Simpan</button>
    <a href="dashboard.php" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded ml-2">Batal</a>
  </form>
</div>

</body>
</html>
