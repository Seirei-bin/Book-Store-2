<?php
include '../config.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['submit'])) {
    $nama = trim($_POST['nama']);
    if ($nama != '') {
        // simpan ke tabel categories
        $stmt = $conn->prepare("INSERT INTO categories (nama) VALUES (?)");
        $stmt->bind_param("s", $nama);
        $stmt->execute();
        header("Location: dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Kategori</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-md mx-auto bg-white shadow-md rounded-lg p-6">
        <h1 class="text-xl font-bold mb-4 text-center">➕ Tambah Kategori</h1>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block font-medium">Nama Kategori:</label>
                <input type="text" name="nama" class="w-full border px-3 py-2 rounded focus:ring focus:ring-green-300" required>
            </div>
            <div class="flex justify-between items-center">
                <a href="dashboard.php" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">⬅ Kembali</a>
                <button type="submit" name="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">💾 Simpan</button>
            </div>
        </form>
    </div>
</body>
</html>
