<?php
session_start();
include '../config.php';

// === Cek login admin ===
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// === Cek parameter order_id ===
$order_id = (int)($_GET['order_id'] ?? 0);
if ($order_id <= 0) {
    die("<p class='text-red-500 text-center mt-4'>Order tidak ditemukan.</p>");
}

// === Ambil data order + user ===
$stmt = $conn->prepare("
    SELECT o.*, u.nama, u.email, u.no_hp, u.alamat
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
if (!$stmt) die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    die("<p class='text-red-500 text-center mt-4'>Order tidak ditemukan.</p>");
}

// === Ambil detail buku ===
$stmt2 = $conn->prepare("
    SELECT od.*, b.judul, b.sampul, b.penulis, b.harga
    FROM order_details od
    JOIN books b ON od.book_id = b.id
    WHERE od.order_id = ?
");
if (!$stmt2) die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
$stmt2->bind_param("i", $order_id);
$stmt2->execute();
$details = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt2->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Order #<?= $order_id ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

<div class="max-w-6xl mx-auto bg-white shadow-md rounded-lg p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">📦 Detail Order #<?= $order_id ?></h1>
        <a href="orders.php" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded">← Kembali ke Orders</a>
    </div>

    <!-- Data Pelanggan -->
    <h2 class="font-semibold mb-2">Data Pelanggan</h2>
    <p>Nama: <?= htmlspecialchars($order['nama']) ?></p>
    <p>Email: <?= htmlspecialchars($order['email']) ?></p>
    <p>Alamat: <?= htmlspecialchars($order['alamat']) ?></p>
    <p>Telepon: <?= htmlspecialchars($order['no_hp']) ?></p>

    <!-- Status & Tanggal -->
    <p class="mt-2">Status: <span class="font-semibold"><?= htmlspecialchars($order['status']) ?></span></p>
    <p>Tanggal: <?= htmlspecialchars($order['created_at']) ?></p>

    <!-- Detail Buku -->
    <h2 class="font-semibold mt-4 mb-2">Detail Buku</h2>
    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-50">
                    <th class="p-3 text-left">Buku</th>
                    <th class="p-3 text-center">Qty</th>
                    <th class="p-3 text-right">Harga</th>
                    <th class="p-3 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $total = 0; foreach($details as $d): 
                    $total += $d['subtotal'];
                ?>
                <tr class="border-b">
                    <td class="p-3 flex items-center space-x-3">
                        <img src="../uploads/<?= $d['sampul'] ?>" alt="<?= $d['judul'] ?>" class="w-12 h-16 object-contain rounded bg-gray-100">
                        <div>
                            <p class="font-semibold"><?= $d['judul'] ?></p>
                            <p class="text-sm text-gray-500">✍ <?= $d['penulis'] ?></p>
                        </div>
                    </td>
                    <td class="p-3 text-center"><?= $d['qty'] ?></td>
                    <td class="p-3 text-right">Rp <?= number_format($d['harga'],0,',','.') ?></td>
                    <td class="p-3 text-right font-semibold">Rp <?= number_format($d['subtotal'],0,',','.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <p class="text-lg font-semibold text-gray-700 mt-4">Total Bayar: <span class="text-green-600">Rp <?= number_format($total,0,',','.') ?></span></p>
</div>

</body>
</html>
