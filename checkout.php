<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

$books = [];
$total = 0;

// 🔑 Mode buy_now
if (isset($_GET['mode']) && $_GET['mode'] === 'buy_now' && isset($_SESSION['buy_now'])) {
    $books[] = $_SESSION['buy_now'];
    $total   = $_SESSION['buy_now']['subtotal'];
} else {
    // Normal checkout (dari cart)
    $sql = "SELECT b.*, c.qty, (c.qty * b.harga) AS subtotal 
            FROM cart c 
            JOIN books b ON c.book_id = b.id 
            WHERE c.user_id = $user_id";
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) {
        $books[] = $row;
        $total  += $row['subtotal'];
    }
}

// 🔥 Proses checkout
if (isset($_POST['checkout']) && !empty($books)) {
    try {
        $conn->begin_transaction();

        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'Belum Bayar')");
        $stmt->bind_param("ii", $user_id, $total);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();

        // Insert order details
        $stmt2 = $conn->prepare("INSERT INTO order_details (order_id, book_id, qty, subtotal) VALUES (?, ?, ?, ?)");
        foreach ($books as $b) {
            $stmt2->bind_param("iiii", $order_id, $b['id'], $b['qty'], $b['subtotal']);
            $stmt2->execute();
        }
        $stmt2->close();

        $conn->commit();

        // 🚫 Jangan hapus cart dulu! (hapusnya nanti pas upload bukti di upload_bukti.php)
        // 🚫 Jangan unset buy_now dulu! (hapusnya juga nanti pas upload bukti)

        header("Location: nota.php?order_id=$order_id");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $error = "Checkout gagal, silakan coba lagi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Checkout - Digital Bookstore</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
<div class="max-w-5xl mx-auto bg-white shadow-lg rounded-xl p-8">

  <!-- Step / Progress -->
  <div class="flex items-center justify-center mb-8 text-sm font-medium text-gray-600">
    <div class="flex items-center space-x-2">
      <span class="px-3 py-1 bg-blue-500 text-white rounded-full">1</span>
      <span>Keranjang</span>
    </div>
    <div class="w-12 border-t border-gray-300 mx-2"></div>
    <div class="flex items-center space-x-2">
      <span class="px-3 py-1 bg-blue-500 text-white rounded-full">2</span>
      <span>Checkout</span>
    </div>
    <div class="w-12 border-t border-gray-300 mx-2"></div>
    <div class="flex items-center space-x-2">
      <span class="px-3 py-1 bg-gray-300 text-gray-600 rounded-full">3</span>
      <span>Pembayaran</span>
    </div>
    <div class="w-12 border-t border-gray-300 mx-2"></div>
    <div class="flex items-center space-x-2">
      <span class="px-3 py-1 bg-gray-300 text-gray-600 rounded-full">4</span>
      <span>Selesai</span>
    </div>
  </div>

  <h1 class="text-3xl font-bold text-center text-blue-600 mb-8">🛒 Checkout</h1>

  <?php if (empty($books)): ?>
    <p class="text-center text-gray-500">Keranjang kosong. Silakan belanja dulu.</p>
    <div class="text-center mt-4">
      <a href="index.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
        ← Kembali Belanja
      </a>
    </div>
  <?php else: ?>

    <!-- Data Pembeli -->
    <div class="mb-6 bg-gray-50 p-5 rounded-lg border">
      <h2 class="text-lg font-semibold mb-3">📌 Data Pembeli</h2>
      <p><strong>Nama:</strong> <?= htmlspecialchars($user['nama']) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
      <p><strong>No HP:</strong> <?= htmlspecialchars($user['no_hp']) ?></p>
      <p><strong>Alamat:</strong> <?= htmlspecialchars($user['alamat']) ?></p>
    </div>

    <!-- Tabel Pesanan -->
    <div class="overflow-hidden rounded-lg border">
      <table class="w-full border-collapse">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-3 text-left">Buku</th>
            <th class="p-3 text-center">Qty</th>
            <th class="p-3 text-right">Harga</th>
            <th class="p-3 text-right">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($books as $b): ?>
          <tr class="border-t hover:bg-gray-50 transition">
            <td class="p-3 flex items-center space-x-3">
              <img src="uploads/<?= htmlspecialchars($b['sampul']) ?>" 
                   alt="<?= htmlspecialchars($b['judul']) ?>" 
                   class="w-12 h-16 object-contain bg-gray-100 rounded">
              <div>
                <p class="font-semibold"><?= htmlspecialchars($b['judul']) ?></p>
                <p class="text-sm text-gray-500">✍ <?= htmlspecialchars($b['penulis']) ?></p>
              </div>
            </td>
            <td class="p-3 text-center"><?= $b['qty'] ?></td>
            <td class="p-3 text-right">Rp <?= number_format($b['harga'], 0, ',', '.') ?></td>
            <td class="p-3 text-right font-semibold">Rp <?= number_format($b['subtotal'], 0, ',', '.') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Ringkasan Total -->
    <div class="mt-6 bg-green-50 border border-green-200 p-4 rounded-lg text-right">
      <p class="text-lg font-semibold text-gray-700">
        Total: <span class="text-green-700 text-xl">Rp <?= number_format($total, 0, ',', '.') ?></span>
      </p>
    </div>

    <!-- Tombol -->
    <form method="POST" class="flex space-x-4 mt-6">
      <button type="submit" name="checkout" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium flex items-center justify-center space-x-2">
        <span>✅ Checkout Sekarang</span>
      </button>

      <?php if (isset($_GET['mode']) && $_GET['mode'] === 'buy_now'): ?>
        <a href="index.php" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium flex items-center justify-center space-x-2">
          ← Kembali ke Beranda
        </a>
      <?php else: ?>
        <a href="cart.php" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium flex items-center justify-center space-x-2">
          ← Kembali ke Keranjang
        </a>
      <?php endif; ?>
    </form>

  <?php endif; ?>
</div>
</body>

</html>
