<?php
session_start();
include 'config.php';

// === Cek login user ===
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// === Ambil semua order user ===
$stmt = $conn->prepare("
    SELECT o.*, 
           (SELECT SUM(subtotal) FROM order_details od WHERE od.order_id = o.id) as total
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Riwayat Pemesanan</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

<div class="max-w-6xl mx-auto bg-white shadow-md rounded-lg p-6">
    <h1 class="text-2xl font-bold mb-6">📝 Riwayat Pemesanan</h1>

    <?php if(count($orders) > 0): ?>
    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-50">
                    <th class="p-3 text-left">#Order</th>
                    <th class="p-3 text-left">Tanggal</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-right">Total Bayar</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($orders as $o): ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3"><?= $o['id'] ?></td>
                    <td class="p-3"><?= $o['created_at'] ?></td>
                    <td class="p-3">
                        <span class="font-semibold text-blue-600"><?= htmlspecialchars($o['status']) ?></span>
                    </td>
                    <td class="p-3 text-right">Rp <?= number_format($o['total'],0,',','.') ?></td>
                    <td class="p-3 text-center">
                        <a href="orders_detail.php?order_id=<?= $o['id'] ?>" 
                           class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded">
                           Lihat
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p class="text-center text-gray-500">Belum ada riwayat pemesanan.</p>
    <?php endif; ?>

    <div class="mt-6">
        <a href="index.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded">← Kembali ke Beranda</a>
    </div>
</div>

</body>
</html>
