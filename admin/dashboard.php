<?php
include '../config.php';
session_start();

// cek login admin
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// pagination
$limit = 5;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// search & filter
$q        = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';
$kategori = isset($_GET['kategori']) ? $conn->real_escape_string($_GET['kategori']) : '';

// base query dengan JOIN ke categories ✅ UBAH
$sql_base = "FROM books 
             LEFT JOIN categories ON books.category_id = categories.id 
             WHERE 1=1";

if ($q != '') {
    $sql_base .= " AND (books.judul LIKE '%$q%' OR books.penulis LIKE '%$q%')";
}

if ($kategori != '') {
    $sql_base .= " AND categories.nama = '$kategori'"; // ✅ UBAH filter pakai categories.nama
}

// hitung total data
$sql_count = "SELECT COUNT(*) as total " . $sql_base;
$total_result = $conn->query($sql_count);
$total_data   = $total_result->fetch_assoc()['total'];
$total_pages  = ceil($total_data / $limit);

// query data dengan limit ✅ UBAH
$sql = "SELECT books.*, categories.nama AS kategori_nama " . $sql_base . " ORDER BY books.id DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// ambil kategori unik ✅ UBAH: ambil dari tabel categories
$kategori_result = $conn->query("SELECT * FROM categories ORDER BY nama ASC");

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-gray-100 min-h-screen">

  <!-- Navbar -->
  <nav class="bg-blue-600 p-4 flex justify-between items-center text-white shadow-md">
    <h1 class="text-xl font-bold">📊 Dashboard Admin</h1>
    <div class="space-x-4">
      <a href="orders.php" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded">📦 Orders</a>
      <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">Logout</a>
    </div>
  </nav>

  <div class="max-w-7xl mx-auto bg-white shadow-md rounded-lg p-6 mt-6">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-2xl font-bold">📚 Manajemen Buku</h2>
      <div class="flex gap-2">
        <a href="create.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
          + Tambah Buku
        </a>
        <a href="kategori_create.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
          + Kategori Baru
        </a>
      </div>
    </div>

    <!-- Search + Filter -->
    <form method="GET" action="dashboard.php" class="mb-6 flex flex-col md:flex-row gap-2">
      <input type="text" name="q" placeholder="Cari judul atau penulis..."
        class="border rounded-lg px-4 py-2 w-full md:w-1/3 focus:ring-2 focus:ring-blue-400 focus:outline-none"
        value="<?= htmlspecialchars($q) ?>">

      <select name="kategori" class="border rounded-lg px-4 py-2 w-full md:w-1/4 focus:ring-2 focus:ring-blue-400 focus:outline-none">
        <option value="">Semua Kategori</option>
        <?php while ($kat = $kategori_result->fetch_assoc()): ?>
          <option value="<?= htmlspecialchars($kat['nama']) ?>" <?= $kategori == $kat['nama'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($kat['nama']) ?>
          </option>
        <?php endwhile; ?>
      </select>

      <button type="submit" 
        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition flex items-center gap-1">
        🔍 Cari
      </button>

      <?php if (!empty($q) || !empty($kategori)): ?>
        <a href="dashboard.php" 
          class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium transition flex items-center gap-1">
          ✖ Reset
        </a>
      <?php endif; ?>
    </form>

    <!-- Tabel Buku -->
    <div class="overflow-x-auto">
      <table class="w-full border-collapse text-center">
        <thead>
          <tr class="bg-gray-200">
            <th class="border p-2">Sampul</th>
            <th class="border p-2">Judul</th>
            <th class="border p-2">Penulis</th>
            <th class="border p-2">Deskripsi</th>
            <th class="border p-2">Kategori</th>
            <th class="border p-2">Harga</th>
            <th class="border p-2">Stok</th>
            <th class="border p-2">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr class="hover:bg-gray-50">
                <td class="border p-2">
                  <?php if ($row['sampul']): ?>
                    <img src="../uploads/<?= htmlspecialchars($row['sampul']) ?>" class="w-16 h-20 object-cover mx-auto rounded">
                  <?php else: ?>
                    <span class="text-gray-400">-</span>
                  <?php endif; ?>
                </td>
                <td class="border p-2"><?= htmlspecialchars($row['judul']) ?></td>
                <td class="border p-2"><?= htmlspecialchars($row['penulis']) ?></td>
                <td class="border p-2 max-w-xs text-sm text-gray-600 text-left">
                  <?= nl2br(htmlspecialchars(substr($row['deskripsi'],0,100))) ?>...
                </td>

                <td class="border p-2"><?= htmlspecialchars($row['kategori_nama']) ?></td> <!-- ✅ UBAH dari $row['kategori'] -->

                <td class="border p-2">Rp <?= number_format($row['harga'],0,',','.') ?></td>
                <td class="border p-2"><?= (int)$row['stok'] ?></td>
                <td class="border p-2">
                  <div class="flex flex-wrap gap-2 justify-center">
                    <a href="edit.php?id=<?= $row['id'] ?>" 
                       class="px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-sm font-medium rounded shadow">
                       ✏️ Edit
                    </a>
                    <a href="edit_stock.php?id=<?= $row['id'] ?>" 
                       class="px-3 py-1 bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-medium rounded shadow">
                       📦 Stok
                    </a>
                    <a href="delete.php?id=<?= $row['id'] ?>" 
                       onclick="return confirm('Yakin hapus?')" 
                       class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded shadow">
                       🗑 Hapus
                    </a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="border p-4 text-gray-500">Tidak ada data ditemukan</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="flex justify-center mt-6 space-x-2">
      <?php 
        $start = max(1, $page - 2);
        $end   = min($total_pages, $page + 2);
      ?>
      <?php if ($page > 1): ?>
        <a href="dashboard.php?page=<?= $page-1 ?>&q=<?= urlencode($q) ?>&kategori=<?= urlencode($kategori) ?>"
           class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded">← Prev</a>
      <?php endif; ?>

      <?php for ($i = $start; $i <= $end; $i++): ?>
        <a href="dashboard.php?page=<?= $i ?>&q=<?= urlencode($q) ?>&kategori=<?= urlencode($kategori) ?>"
           class="px-3 py-1 rounded <?= $i == $page ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300' ?>">
           <?= $i ?>
        </a>
      <?php endfor; ?>

      <?php if ($page < $total_pages): ?>
        <a href="dashboard.php?page=<?= $page+1 ?>&q=<?= urlencode($q) ?>&kategori=<?= urlencode($kategori) ?>"
           class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded">Next →</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  </div>
</body>
</html>
