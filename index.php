<?php
session_start();
include 'config.php';

// === Tambah ke Keranjang ===
if (isset($_GET['add'])) {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('Silakan login dulu untuk menambahkan ke keranjang!'); window.location='login.php';</script>";
        exit;
    }

    $book_id = (int)$_GET['add'];
    $user_id = $_SESSION['user_id'];

    // Ambil stok
    $stmt = $conn->prepare("SELECT stok, judul FROM books WHERE id=?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $stok_result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$stok_result) {
        echo "<script>alert('Buku tidak ditemukan!'); window.history.back();</script>";
        exit;
    }

    // cek qty di cart
    $stmt = $conn->prepare("SELECT qty FROM cart WHERE user_id=? AND book_id=?");
    $stmt->bind_param("ii", $user_id, $book_id);
    $stmt->execute();
    $cart_item = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $jumlah_di_cart = $cart_item['qty'] ?? 0;

    if ($jumlah_di_cart >= $stok_result['stok']) {
        echo "<script>alert('Stok buku \"{$stok_result['judul']}\" tidak cukup!'); window.history.back();</script>";
        exit;
    }

    if ($jumlah_di_cart > 0) {
        $stmt = $conn->prepare("UPDATE cart SET qty = qty + 1 WHERE user_id=? AND book_id=?");
        $stmt->bind_param("ii", $user_id, $book_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, book_id, qty) VALUES (?,?,1)");
        $stmt->bind_param("ii", $user_id, $book_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: index.php");
    exit;
}

// === Search, Filter & Sortir ===
$q        = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';
$kategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0; // ✅ UBAH ke category_id
$sort     = isset($_GET['sort']) ? $_GET['sort'] : 'terbaru';

// === Pagination ===
$limit  = 8;
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// === Query Buku dengan JOIN ke categories ✅ UBAH
$sql_base = "FROM books 
             LEFT JOIN categories ON books.category_id = categories.id 
             WHERE 1=1";

if ($q !== '') $sql_base .= " AND (books.judul LIKE '%$q%' OR books.penulis LIKE '%$q%')";
if ($kategori !== 0) $sql_base .= " AND categories.id = $kategori"; // ✅ UBAH filter

$total_result = $conn->query("SELECT COUNT(*) as total $sql_base");
$total_data   = $total_result->fetch_assoc()['total'];
$total_pages  = ceil($total_data / $limit);

$order = "books.id DESC";
switch ($sort) {
    case 'murah': $order = "books.harga ASC"; break;
    case 'mahal': $order = "books.harga DESC"; break;
    case 'stok':  $order = "books.stok DESC"; break;
}

$sql = "SELECT books.*, categories.nama AS kategori_nama $sql_base ORDER BY $order LIMIT $limit OFFSET $offset"; // ✅ UBAH SELECT
$result = $conn->query($sql);

// === Hitung isi keranjang (untuk badge) ===
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $res = $conn->query("SELECT SUM(qty) as total FROM cart WHERE user_id=$uid");
    $cart_count = $res->fetch_assoc()['total'] ?? 0;
}

// === Ambil kategori dinamis dari tabel categories ✅ UBAH
$kategori_res = $conn->query("SELECT * FROM categories ORDER BY nama");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Estrella Pustaka</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style> 
    body { font-family: 'Poppins', sans-serif; } 

    /* Animasi fade-in untuk card */
    .fade-in { animation: fadeIn 0.8s ease-in-out; }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Animasi shake untuk notif */
    .shake { animation: shake 0.4s ease-in-out infinite; }
    @keyframes shake {
      0%, 100% { transform: rotate(0deg); }
      25% { transform: rotate(-10deg); }
      75% { transform: rotate(10deg); }
    }
  </style>
</head>
<body class="bg-white text-gray-800">

  <!-- Navbar -->
  <nav class="bg-white shadow-md px-6 py-4 flex justify-between items-center sticky top-0 z-50">
    <h1 class="text-2xl font-bold text-[#3B82F6]">Estrella Pustaka</h1>
  
    <div class="flex items-center gap-3">
      <!-- Badge keranjang -->
      <a href="cart.php" class="relative bg-[#3B82F6] hover:bg-[#8B5CF6] text-white px-4 py-2 rounded-lg font-medium">
        🛒 Keranjang
        <?php if($cart_count > 0): ?>
          <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full">
            <?= $cart_count ?>
          </span>
        <?php endif; ?>
      </a>

      <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Notifikasi -->
        <div class="relative">
          <button onclick="toggleNotif()" class="relative bg-gray-200 px-3 py-2 rounded hover:bg-gray-300">
            <span class="shake inline-block">🔔</span>
            <span id="notif-count" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full hidden">0</span>
          </button>
          <div id="notif-dropdown" class="hidden absolute right-0 mt-2 w-72 bg-white border rounded shadow-md max-h-72 overflow-y-auto">
            <div id="notif-list" class="p-1 text-sm text-gray-600">Memuat notifikasi...</div>
          </div>
        </div>

        <!-- Dropdown Profil -->
        <div class="relative inline-block text-left">
          <button onclick="toggleDropdown()" class="flex items-center space-x-2 bg-gray-200 px-3 py-2 rounded hover:bg-gray-300">
            👤 <span><?php echo $_SESSION['username']; ?></span>
          </button>
          <div id="dropdown" class="hidden absolute right-0 mt-2 w-40 bg-white border rounded shadow-md">
            <a href="profile.php" class="block px-4 py-2 hover:bg-gray-100">Profil</a>
            <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100 text-red-500">Logout</a>
          </div>
        </div>
      <?php else: ?>
        <a href="login.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium">
          Login
        </a>
      <?php endif; ?>
    </div>
  </nav>

  <!-- Container -->
  <div class="max-w-7xl mx-auto px-6 py-10">
    <h2 class="text-3xl font-bold mb-6 text-center text-[#8B5CF6]">
      Selamat Datang di Estrella Pustaka
    </h2>

    <!-- Search + Filter + Sort -->
    <form method="GET" action="index.php" class="mb-6 flex flex-wrap gap-2 items-center">
     <select name="kategori" onchange="this.form.submit()" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">
    <option value="0">Semua Kategori</option>
    <?php while($k = $kategori_res->fetch_assoc()): ?>
        <option value="<?= $k['id'] ?>" <?= ($kategori==$k['id'])?'selected':'' ?>> <!-- ✅ UBAH ke $k['id'] -->
            <?= htmlspecialchars($k['nama']) ?> <!-- ✅ UBAH tampil nama kategori -->
        </option>
    <?php endwhile; ?>
</select>


      <select name="sort" onchange="this.form.submit()" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400">

        <option value="terbaru" <?= ($sort=='terbaru')?'selected':'' ?>>Terbaru</option>
        <option value="murah"   <?= ($sort=='murah')?'selected':'' ?>>Harga Termurah</option>
        <option value="mahal"   <?= ($sort=='mahal')?'selected':'' ?>>Harga Termahal</option>
        <option value="stok"    <?= ($sort=='stok')?'selected':'' ?>>Stok Terbanyak</option>
      </select>

      <input type="text" name="q" placeholder="Cari judul atau penulis..."
        class="border rounded-lg px-4 py-2 w-full md:w-[650px] focus:ring-2 focus:ring-blue-400 focus:outline-none"
        value="<?= htmlspecialchars($q) ?>">

      <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition animate-pulse">
        🔍 Cari
      </button>
      <?php if ($q || $kategori || $sort!='terbaru'): ?>
        <a href="index.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium transition">
          ✖ Reset
        </a>
      <?php endif; ?>

      
    </form>

    <!-- Grid Buku -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
      <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
          <div class="bg-[#F4F4F5] rounded-xl shadow hover:shadow-lg transition p-4 flex flex-col fade-in hover:scale-105 duration-300">
            <a href="detail.php?id=<?= $row['id']; ?>">
              <img src="uploads/<?= $row['sampul']; ?>" alt="<?= htmlspecialchars($row['judul']); ?>" 
                   class="w-full h-52 object-contain rounded-md mb-3 bg-white">
            </a>

            <h3 class="text-lg font-semibold">
              <a href="detail.php?id=<?= $row['id']; ?>"><?= htmlspecialchars($row['judul']); ?></a>
            </h3>
            <p class="text-sm text-gray-500">✍ <?= htmlspecialchars($row['penulis']); ?></p>
            <p class="font-bold text-[#10B981] mt-2">Rp <?= number_format($row['harga'],0,',','.'); ?></p>
            <p class="text-sm mt-1 <?= $row['stok'] > 0 ? 'text-gray-600' : 'text-red-500' ?>">
              Stok: <?= $row['stok'] > 0 ? $row['stok'] : 'Habis'; ?>
            </p>

            <?php if($row['stok'] > 0): ?>
              <a href="?add=<?= $row['id']; ?>" 
                 class="mt-auto bg-[#3B82F6] hover:bg-[#8B5CF6] text-white px-3 py-2 rounded-lg text-center block transition">
                 + Tambah ke Keranjang
              </a>
              <a href="buy_now.php?book_id=<?= $row['id']; ?>" 
                 class="mt-2 bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-lg text-center block transition">
                 🛒 Beli Sekarang
              </a>
            <?php else: ?>
              <button class="mt-auto bg-gray-400 text-white px-3 py-2 rounded-lg text-center block cursor-not-allowed" disabled>
                 Habis
              </button>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-center text-gray-500 col-span-4">Buku tidak ditemukan.</p>
      <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="flex justify-center mt-8 space-x-2">
      <?php if ($page > 1): ?>
        <a href="?page=<?= $page-1 ?>&q=<?= urlencode($q) ?>&kategori=<?= urlencode($kategori) ?>&sort=<?= $sort ?>" 
           class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded">← Prev</a>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?>&q=<?= urlencode($q) ?>&kategori=<?= urlencode($kategori) ?>&sort=<?= $sort ?>"
           class="px-3 py-1 rounded <?= $i == $page ? 'bg-blue-500 text-white' : 'bg-gray-200 hover:bg-gray-300' ?>">
           <?= $i ?>
        </a>
      <?php endfor; ?>

      <?php if ($page < $total_pages): ?>
        <a href="?page=<?= $page+1 ?>&q=<?= urlencode($q) ?>&kategori=<?= urlencode($kategori) ?>&sort=<?= $sort ?>" 
           class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded">Next →</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

<script>
function toggleDropdown() {
    document.getElementById("dropdown").classList.toggle("hidden");
}
function toggleNotif() {
    document.getElementById("notif-dropdown").classList.toggle("hidden");
}

// === AJAX Notifikasi ===
function loadNotif() {
  $.get("notif.php", function(data) {
    let res = (typeof data === "string") ? JSON.parse(data) : data;

    $("#notif-list").html(res.html);

    // badge count
    if(res.count > 0){
      $("#notif-count").text(res.count).removeClass("hidden");
    } else {
      $("#notif-count").addClass("hidden");
    }

    // Klik notifikasi buka halaman detail order
    $(document).on("click", "#notif-list div", function(){
        let orderId = $(this).find("span.font-semibold").text();
        window.location.href = "admin/orders_detail.php?order_id=" + orderId;
    });

  });
}

// auto refresh notif tiap 5 detik
setInterval(loadNotif, 5000);
loadNotif();
</script>
</body>
</html>
