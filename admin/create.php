<?php
include '../config.php';
session_start();

// cek login
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// ambil daftar kategori unik dari database
// ambil daftar kategori dari tabel categories
$kategori_result = $conn->query("SELECT * FROM categories ORDER BY nama ASC");



if (isset($_POST['submit'])) {
    $judul      = $_POST['judul'];
    $penulis    = $_POST['penulis'];
    $deskripsi  = $_POST['deskripsi']; // ✅ tambahkan ini
    $harga      = $_POST['harga'];
    $stok       = $_POST['stok'];

    // cek kategori (lama atau baru)
    // cek kategori (lama atau baru)
$kategori = $_POST['kategori'];
if ($kategori === "__new" && !empty($_POST['kategori_baru'])) {
    $kategori = $_POST['kategori_baru'];

    // simpan kategori baru ke tabel categories jika belum ada
    $stmtKat = $conn->prepare("INSERT IGNORE INTO categories (nama) VALUES (?)");
    $stmtKat->bind_param("s", $kategori);
    $stmtKat->execute();
}


    // proses upload file
    $sampul = "";
    if (!empty($_FILES['sampul']['name'])) {
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true); // ✅ buat folder kalau belum ada
        }

        $fileName  = time() . "_" . basename($_FILES['sampul']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['sampul']['tmp_name'], $targetFile)) {
            $sampul = $fileName;
        }
    }

    // ✅ simpan ke database
    $stmt = $conn->prepare("INSERT INTO books (judul, penulis, deskripsi, kategori, harga, stok, sampul) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssdis", $judul, $penulis, $deskripsi, $kategori, $harga, $stok, $sampul);
    $stmt->execute();

    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Buku</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-100 p-6">

  <div class="max-w-lg mx-auto bg-white shadow-md rounded-lg p-6">
    <h1 class="text-xl font-bold mb-4 text-center">➕ Tambah Buku</h1>

    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">

      <!-- Judul -->
      <div>
        <label class="block font-medium">Judul:</label>
        <input type="text" name="judul" class="w-full border px-3 py-2 rounded focus:ring focus:ring-blue-300" required>
      </div>

      <!-- Penulis -->
      <div>
        <label class="block font-medium">Penulis:</label>
        <input type="text" name="penulis" class="w-full border px-3 py-2 rounded focus:ring focus:ring-blue-300" required>
      </div>
<!-- Deskripsi -->
<div>
  <label class="block font-medium">Deskripsi:</label>
  <textarea name="deskripsi" rows="4"
    class="w-full border px-3 py-2 rounded focus:ring focus:ring-blue-300" required></textarea>
</div>

      <!-- Kategori -->
      <div>
        <label class="block font-medium">Kategori:</label>
   <select name="kategori" class="w-full border px-3 py-2 rounded focus:ring focus:ring-blue-300" id="kategoriSelect">
  <option value="">-- Pilih kategori --</option>
  <?php while ($kat = $kategori_result->fetch_assoc()): ?>
    <option value="<?= htmlspecialchars($kat['nama']) ?>">
      <?= htmlspecialchars($kat['nama']) ?>
    </option>
  <?php endwhile; ?>
  <option value="__new">➕ Tambah kategori baru...</option>
</select>


        <input type="text" name="kategori_baru" id="kategoriBaru"
               placeholder="Masukkan kategori baru"
               class="w-full border px-3 py-2 rounded mt-2 hidden focus:ring focus:ring-blue-300">
      </div>

      <!-- Harga -->
      <div>
        <label class="block font-medium">Harga:</label>
        <input type="number" name="harga" class="w-full border px-3 py-2 rounded focus:ring focus:ring-blue-300" required>
      </div>

      <!-- Stok -->
      <div>
        <label class="block font-medium">Stok:</label>
        <input type="number" name="stok" class="w-full border px-3 py-2 rounded focus:ring focus:ring-blue-300" required>
      </div>

      <!-- Sampul -->
      <div>
        <label class="block font-medium">Sampul (gambar):</label>
        <input type="file" name="sampul" accept="image/*" class="w-full border px-3 py-2 rounded">
      </div>

      <!-- Tombol -->
      <div class="flex justify-between items-center">
        <a href="dashboard.php" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">⬅ Kembali</a>
        <button type="submit" name="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">💾 Simpan</button>
      </div>
    </form>
  </div>

  <script>
    const kategoriSelect = document.getElementById('kategoriSelect');
    const kategoriBaru   = document.getElementById('kategoriBaru');

    kategoriSelect.addEventListener('change', function() {
      if (this.value === '__new') {
        kategoriBaru.classList.remove('hidden');
        kategoriBaru.required = true;
      } else {
        kategoriBaru.classList.add('hidden');
        kategoriBaru.required = false;
      }
    });
  </script>

</body>
</html>
