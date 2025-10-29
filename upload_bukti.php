<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)$_POST['order_id'];

    // Folder upload
    $targetDir = "uploads/bukti/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    // Nama file unik
    $fileName = time() . "_" . basename($_FILES["bukti"]["name"]);
    $targetFile = $targetDir . $fileName;

    // Cek format file
    $allowedTypes = ['image/jpeg','image/png','image/jpg'];
    if (!in_array($_FILES['bukti']['type'], $allowedTypes)) {
        echo "<script>alert('Format file harus JPG atau PNG!');window.location='nota.php?order_id=$order_id';</script>";
        exit;
    }

    // Upload file
    if (move_uploaded_file($_FILES["bukti"]["tmp_name"], $targetFile)) {
        // Simpan ke database + metode pembayaran
        $stmt = $conn->prepare("UPDATE orders 
                                SET bukti=?, metode_pembayaran=?, status='Menunggu Konfirmasi' 
                                WHERE id=?");
        $stmt->bind_param("ssi", $fileName, $_POST['metode'], $order_id);
        $stmt->execute();
        $stmt->close();

        // 🔥 Kurangi stok buku sesuai qty order
        $result = $conn->query("SELECT book_id, qty FROM order_details WHERE order_id=$order_id");
        while ($row = $result->fetch_assoc()) {
            $book_id = (int)$row['book_id'];
            $qty     = (int)$row['qty'];

            $stokRes = $conn->query("SELECT stok FROM books WHERE id=$book_id");
            if ($stokRes && $stokRes->num_rows > 0) {
                $stok = (int)$stokRes->fetch_assoc()['stok'];
                $stokBaru = max(0, $stok - $qty);
                $conn->query("UPDATE books SET stok=$stokBaru WHERE id=$book_id");
            }
        }

        // Kosongkan keranjang user
        $conn->query("DELETE FROM cart WHERE user_id=" . $_SESSION['user_id']);

        // Ambil data order + user untuk tampilan
        $order = $conn->query("SELECT o.*, u.nama, u.email, u.no_hp, u.alamat 
                               FROM orders o 
                               JOIN users u ON o.user_id=u.id 
                               WHERE o.id=$order_id")->fetch_assoc();

        $details = $conn->query("SELECT od.*, b.judul, b.penulis, b.sampul, b.harga 
                                 FROM order_details od 
                                 JOIN books b ON od.book_id=b.id 
                                 WHERE od.order_id=$order_id");

        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <title>Terima Kasih</title>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        <body class="bg-gray-100 p-6">
            <div class="max-w-3xl mx-auto bg-white shadow-lg rounded-2xl p-8">
                <div class="text-center mb-6">
                    <div class="text-green-500 text-6xl mb-4">✅</div>
                    <h1 class="text-2xl font-bold text-gray-800">Terima Kasih!</h1>
                    <p class="text-gray-600 mt-2">
                        Bukti pembayaran berhasil diupload.<br>
                        Pesanan Anda sedang <span class="font-semibold text-blue-600">menunggu konfirmasi admin</span>.
                    </p>
                </div>

                <!-- Data Pelanggan -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold mb-2 text-gray-800">📌 Data Pelanggan</h2>
                    <div class="grid grid-cols-2 gap-2 text-sm text-gray-700">
                        <p><strong>Nama:</strong> <?= htmlspecialchars($order['nama']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                        <p><strong>No HP:</strong> <?= htmlspecialchars($order['no_hp']) ?></p>
                        <p><strong>Alamat:</strong> <?= htmlspecialchars($order['alamat']) ?></p>
                    </div>
                </div>

                <!-- Detail Pesanan -->
                <div>
                    <h2 class="text-lg font-semibold mb-2 text-gray-800">📚 Detail Pesanan</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="p-3 text-left">Buku</th>
                                    <th class="p-3 text-center">Qty</th>
                                    <th class="p-3 text-right">Harga</th>
                                    <th class="p-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $total = 0; while($d = $details->fetch_assoc()): 
                                    $total += $d['subtotal']; ?>
                                    <tr class="border-b">
                                        <td class="p-3 flex items-center space-x-3">
                                            <img src="uploads/<?= htmlspecialchars($d['sampul']) ?>" 
                                                 class="w-12 h-16 object-contain rounded bg-gray-100" 
                                                 alt="<?= htmlspecialchars($d['judul']) ?>">
                                            <div>
                                                <p class="font-semibold"><?= htmlspecialchars($d['judul']) ?></p>
                                                <p class="text-xs text-gray-500">✍ <?= htmlspecialchars($d['penulis']) ?></p>
                                            </div>
                                        </td>
                                        <td class="p-3 text-center"><?= $d['qty'] ?></td>
                                        <td class="p-3 text-right">Rp <?= number_format($d['harga'],0,',','.') ?></td>
                                        <td class="p-3 text-right font-semibold">Rp <?= number_format($d['subtotal'],0,',','.') ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Total -->
                <p class="text-lg font-semibold text-gray-800 mt-4">
                    Total Bayar: <span class="text-green-600">Rp <?= number_format($total,0,',','.') ?></span>
                </p>

                <!-- Tombol -->
                <div class="mt-6 text-center">
                    <a href="index.php" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                        ⬅️ Kembali ke Beranda
                    </a>
                </div>
            </div>
        </body>
        </html>
        <?php
    } else {
        echo "<script>alert('Upload bukti gagal, coba lagi.');window.location='nota.php?order_id=$order_id';</script>";
    }
}
?>
