<?php
include '../config.php';
session_start();

// cek login
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? 0;

// ambil data buku
$stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();

if (!$book) {
    echo "Buku tidak ditemukan!";
    exit;
}

// ambil semua kategori dari tabel categories
$kategori_result = $conn->query("SELECT * FROM categories ORDER BY nama ASC");

// fungsi untuk auto tentukan tipe bind_param
function getParamTypes($params) {
    $types = '';
    foreach ($params as $param) {
        if (is_int($param)) {
            $types .= 'i';
        } elseif (is_float($param) || is_double($param)) {
            $types .= 'd';
        } elseif (is_null($param)) {
            $types .= 's';
        } else {
            $types .= 's';
        }
    }
    return $types;
}

if (isset($_POST['submit'])) {
    $judul      = $_POST['judul'];
    $penulis    = $_POST['penulis'];
    $categoryId = (int) $_POST['kategori']; // ambil id kategori
    $harga      = (float) $_POST['harga'];
    $stok       = (int) $_POST['stok'];
    $deskripsi  = $_POST['deskripsi'];

    // proses upload sampul baru (jika ada)
    $sampul = $book['sampul']; // default pakai sampul lama
    if (!empty($_FILES['sampul']['name'])) {
        $targetDir = "../uploads/";
        $fileName = time() . "_" . basename($_FILES['sampul']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['sampul']['tmp_name'], $targetFile)) {
            // hapus sampul lama kalau ada
            if ($book['sampul'] && file_exists("../uploads/" . $book['sampul'])) {
                unlink("../uploads/" . $book['sampul']);
            }
            $sampul = $fileName;
        }
    }

    // data yang akan diupdate
    $params = [$judul, $penulis, $categoryId, $harga, $stok, $deskripsi, $sampul, $id];
    $types  = getParamTypes($params);

    // update data
    $stmt = $conn->prepare("UPDATE books 
                            SET judul=?, penulis=?, category_id=?, harga=?, stok=?, deskripsi=?, sampul=? 
                            WHERE id=?");
    $stmt->bind_param($types, ...$params);
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
  <title>Edit Buku</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-100 p-6">

  <div class="max-w-lg mx-auto bg-white shadow-md rounded-lg p-6">
    <h1 class="text-xl font-bold mb-4 text-center">✏️ Edit Buku</h1>

    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label class="block">Judul:</label>
        <input type="text" name="judul" value="<?= htmlspecialchars($book['judul']) ?>" class="w-full border px-3 py-2 rounded" required>
      </div>

      <div>
        <label class="block">Penulis:</label>
        <input type="text" name="penulis" value="<?= htmlspecialchars($book['penulis']) ?>" class="w-full border px-3 py-2 rounded" required>
      </div>

      <div>
        <label class="block">Kategori:</label>
        <select name="kategori" class="w-full border px-3 py-2 rounded" required>
          <?php while ($kat = $kategori_result->fetch_assoc()): ?>
            <option value="<?= $kat['id'] ?>" <?= $book['category_id'] == $kat['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($kat['nama']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div>
        <label class="block">Harga:</label>
        <input type="number" name="harga" value="<?= $book['harga'] ?>" class="w-full border px-3 py-2 rounded" required>
      </div>

      <div>
        <label class="block">Stok:</label>
        <input type="number" name="stok" value="<?= $book['stok'] ?>" class="w-full border px-3 py-2 rounded" required>
      </div>

      <div>
        <label class="block">Deskripsi:</label>
        <textarea name="deskripsi" rows="4" class="w-full border px-3 py-2 rounded"><?= htmlspecialchars($book['deskripsi']) ?></textarea>
      </div>

      <div>
        <label class="block">Sampul saat ini:</label>
        <?php if ($book['sampul']): ?>
          <img src="../uploads/<?= $book['sampul'] ?>" alt="Sampul" class="h-32 mb-2 rounded shadow">
        <?php else: ?>
          <p class="text-gray-500">Belum ada sampul</p>
        <?php endif; ?>
        <input type="file" name="sampul" accept="image/*" class="w-full">
      </div>

      <div class="flex justify-between">
        <a href="dashboard.php" class="bg-gray-400 text-white px-4 py-2 rounded">⬅ Kembali</a>
        <button type="submit" name="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">💾 Update</button>
      </div>
    </form>
  </div>

</body>
</html>
