<?php
session_start();
include 'config.php';

// ambil id buku
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$id = (int)$_GET['id'];

// ambil data buku
$stmt = $conn->prepare("
    SELECT books.*, categories.nama AS kategori_nama
    FROM books
    LEFT JOIN categories ON books.category_id = categories.id
    WHERE books.id=?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();
$stmt->close();


if (!$book) {
    echo "<script>alert('Buku tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

// hitung jumlah di keranjang (untuk badge navbar)
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $res = $conn->query("SELECT SUM(qty) as total FROM cart WHERE user_id=$uid");
    $cart_count = $res->fetch_assoc()['total'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($book['judul']); ?> - Estrella Pustaka</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-50 text-gray-800">

  <!-- Navbar -->
  <nav class="bg-white shadow-md px-6 py-4 flex justify-between items-center sticky top-0 z-50">
    <h1 class="text-2xl font-bold text-[#3B82F6]">
      <a href="index.php">Estrella Pustaka</a>
    </h1>
    <div class="flex items-center gap-3">
      <a href="cart.php" class="relative bg-[#3B82F6] hover:bg-[#8B5CF6] text-white px-4 py-2 rounded-lg font-medium">
        🛒 Keranjang
        <?php if($cart_count > 0): ?>
          <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full">
            <?= $cart_count ?>
          </span>
        <?php endif; ?>
      </a>

      <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Dropdown Profil -->
        <div class="relative inline-block text-left">
          <button onclick="toggleDropdown()" 
                  class="flex items-center space-x-2 bg-gray-200 px-3 py-2 rounded hover:bg-gray-300">
            👤 <span><?= htmlspecialchars($_SESSION['username']); ?></span>
          </button>
          <div id="dropdown" class="hidden absolute right-0 mt-2 w-40 bg-white border rounded shadow-md">
            <a href="profile.php" class="block px-4 py-2 hover:bg-gray-100">Profil</a>
            <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100 text-red-500">Logout</a>
          </div>
        </div>
      <?php else: ?>
        <a href="login.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium">Login</a>
      <?php endif; ?>
    </div>
  </nav>

  <!-- Konten -->
  <div class="max-w-5xl mx-auto px-6 py-10 grid md:grid-cols-2 gap-8">
    <!-- Gambar -->
    <div>
      <?php if ($book['sampul']): ?>
        <img src="uploads/<?= htmlspecialchars($book['sampul']); ?>" 
             alt="<?= htmlspecialchars($book['judul']); ?>" 
             class="w-full h-[450px] object-contain rounded-xl shadow bg-white">
      <?php else: ?>
        <div class="w-full h-[450px] flex items-center justify-center bg-gray-200 rounded-xl text-gray-500">
          No Image
        </div>
      <?php endif; ?>
    </div>

    <!-- Detail Buku -->
    <div class="flex flex-col">
      <h2 class="text-3xl font-bold mb-2"><?= htmlspecialchars($book['judul']); ?></h2>
      <p class="text-gray-600 text-lg mb-4">✍ <?= htmlspecialchars($book['penulis']); ?></p>
      <p class="text-sm text-gray-500 mb-2">
    Kategori: <?= htmlspecialchars($book['kategori_nama'] ?? '-'); ?>
</p>
      <p class="text-2xl text-[#10B981] font-bold mb-4">Rp <?= number_format($book['harga'],0,',','.'); ?></p>
      <p class="text-sm <?= $book['stok']>0 ? 'text-gray-600' : 'text-red-500' ?>">
        Stok: <?= $book['stok']>0 ? $book['stok'] : 'Habis'; ?>
      </p>

      <div class="mt-6 space-y-3">
        <?php if ($book['stok'] > 0): ?>
          <a href="index.php?add=<?= $book['id']; ?>" 
             class="block bg-[#3B82F6] hover:bg-[#8B5CF6] text-white px-4 py-3 rounded-lg font-medium text-center transition">
            + Tambah ke Keranjang
          </a>
          <a href="buy_now.php?book_id=<?= $book['id']; ?>" 
             class="block bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg font-medium text-center transition">
            🛒 Beli Sekarang
          </a>
        <?php else: ?>
          <button disabled class="block w-full bg-gray-400 text-white px-4 py-3 rounded-lg font-medium cursor-not-allowed">
            ❌ Stok Habis
          </button>
        <?php endif; ?>
      </div>

      <!-- Deskripsi -->
      <?php if (!empty($book['deskripsi'])): ?>
        <div class="mt-8">
          <h3 class="text-xl font-semibold mb-2">📖 Deskripsi</h3>
          <p class="text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($book['deskripsi'])); ?></p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script>
    function toggleDropdown() {
      document.getElementById("dropdown").classList.toggle("hidden");
    }
  </script>
</body>
</html>
