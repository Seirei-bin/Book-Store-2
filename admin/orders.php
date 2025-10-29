<?php
include '../config.php';
session_start();

// Cek login admin
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// Ambil semua order + join user
$result = $conn->query("
    SELECT o.*, u.nama, u.email 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Pesanan</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">

  <div class="max-w-7xl mx-auto bg-white shadow-lg rounded-lg p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-700">📦 Daftar Pesanan</h1>
      <a href="dashboard.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg shadow">
        📚 Dashboard Buku
      </a>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full border-collapse text-center text-sm">
        <thead>
          <tr class="bg-gradient-to-r from-blue-500 to-indigo-500 text-white">
            <th class="p-3">ID</th>
            <th class="p-3">Nama</th>
            <th class="p-3">Email</th>
            <th class="p-3">Total</th>
            <th class="p-3">Status</th>
            <th class="p-3">Tanggal</th>
            <th class="p-3">Bukti Pembayaran</th>
            <th class="p-3">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
              <tr class="border-b hover:bg-gray-50 transition">
                <td class="p-3 font-medium"><?= $row['id'] ?></td>
                <td class="p-3"><?= htmlspecialchars($row['nama']) ?></td>
                <td class="p-3"><?= htmlspecialchars($row['email']) ?></td>
                <td class="p-3 text-green-600 font-semibold">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>

                <!-- Status -->
                <td class="p-3">
                  <form method="POST" action="update_status.php" class="flex justify-center items-center gap-2">
                    <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                    <select name="status" class="border px-2 py-1 rounded focus:ring-2 focus:ring-blue-400">
                      <?php 
                        $statuses = ['Belum Bayar','Menunggu Konfirmasi','Diproses','Dikirim','Selesai'];
                        foreach ($statuses as $s): ?>
                          <option value="<?= $s ?>" <?= $row['status'] == $s ? 'selected' : '' ?>>
                            <?= $s ?>
                          </option>
                      <?php endforeach; ?>
                    </select>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded shadow">
                      Update
                    </button>
                  </form>
                </td>

                <td class="p-3 text-gray-600"><?= $row['created_at'] ?></td>

                <!-- Bukti Pembayaran -->
                <td class="p-3">
                <?php if(!empty($row['bukti'])): ?>
  <a href="lihat_bukti.php?file=<?= urlencode($row['bukti']) ?>" target="_blank" 
     class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded shadow">
     Lihat Bukti
  </a>
<?php else: ?>
  <span class="text-gray-400 italic">Belum upload</span>
<?php endif; ?>

                </td>

                <!-- Aksi -->
                <td class="p-3">
                  <div class="flex justify-center gap-2">
                    <!-- Detail -->
                    <a href="orders_detail.php?order_id=<?= $row['id'] ?>" 
                       class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded shadow">
                      Detail
                    </a>

                    <!-- Hapus -->
                    <form method="POST" action="delete_orders.php" 
                          onsubmit="return confirm('Yakin ingin menghapus order ini?');">
                      <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                      <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded shadow">
                        Hapus
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="p-4 text-gray-500 italic">Belum ada pesanan</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</body>
</html>
