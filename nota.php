<?php
session_start();
include 'config.php';

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if(!$order_id) die("Order tidak ditemukan.");

// Ambil data order + user
$stmt = $conn->prepare("SELECT o.*, u.nama, u.email, u.no_hp, u.alamat 
                        FROM orders o 
                        JOIN users u ON o.user_id=u.id 
                        WHERE o.id=?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();
if(!$order) die("Order tidak ditemukan.");

// Ambil detail buku
$stmt2 = $conn->prepare("SELECT od.*, b.judul, b.sampul, b.penulis, b.harga 
                         FROM order_details od 
                         JOIN books b ON od.book_id=b.id 
                         WHERE od.order_id=?");
$stmt2->bind_param("i", $order_id);
$stmt2->execute();
$details = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt2->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Nota Pesanan</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
<div class="max-w-6xl mx-auto bg-white shadow-lg rounded-xl p-8">

  <!-- Progress Step -->
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
      <span class="px-3 py-1 bg-blue-500 text-white rounded-full">3</span>
      <span>Pembayaran</span>
    </div>
    <div class="w-12 border-t border-gray-300 mx-2"></div>
    <div class="flex items-center space-x-2">
      <span class="px-3 py-1 bg-gray-300 text-gray-600 rounded-full">4</span>
      <span>Selesai</span>
    </div>
  </div>

  <h1 class="text-3xl font-bold text-center text-blue-600 mb-8">🧾 Pembayaran</h1>

  <!-- Layout 2 kolom -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <!-- Kolom kiri: Data & Pesanan -->
    <div class="md:col-span-2 space-y-6">

      <!-- Data Pelanggan -->
      <div class="bg-gray-50 p-5 rounded-lg border">
        <h2 class="font-semibold mb-3">📌 Data Pelanggan</h2>
        <p><strong>Nama:</strong> <?= htmlspecialchars($order['nama']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
        <p><strong>No HP:</strong> <?= htmlspecialchars($order['no_hp']) ?></p>
        <p><strong>Alamat:</strong> <?= htmlspecialchars($order['alamat']) ?></p>
      </div>

      <!-- Detail Pesanan -->
      <div class="bg-gray-50 p-5 rounded-lg border">
        <h2 class="font-semibold mb-3">📚 Detail Pesanan</h2>
        <table class="w-full border-collapse text-sm">
          <thead>
            <tr class="bg-gray-100">
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
            <tr class="border-t hover:bg-gray-50 transition">
              <td class="p-3 flex items-center space-x-3">
                <img src="uploads/<?= htmlspecialchars($d['sampul']) ?>" 
                     alt="<?= htmlspecialchars($d['judul']) ?>" 
                     class="w-12 h-16 object-contain rounded bg-gray-100">
                <div>
                  <p class="font-semibold"><?= htmlspecialchars($d['judul']) ?></p>
                  <p class="text-xs text-gray-500">✍ <?= htmlspecialchars($d['penulis']) ?></p>
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

    </div>

    <!-- Kolom kanan: Ringkasan & Bayar -->
    <div class="space-y-6">

      <!-- Ringkasan Total -->
      <div class="bg-green-50 border border-green-200 p-5 rounded-lg">
        <p class="text-lg font-semibold text-gray-700">
          Total Bayar:
          <span class="text-green-700 text-xl">
            Rp <?= number_format($total,0,',','.') ?>
          </span>
        </p>
      </div>

      <!-- Form Pembayaran -->
      <form action="upload_bukti.php" method="POST" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="order_id" value="<?= $order_id ?>">

        <!-- Metode Pembayaran -->
        <div>
          <h2 class="font-semibold mb-2">💳 Pilih Metode Pembayaran</h2>
          <div class="grid grid-cols-1 gap-3">
            <label class="border rounded-lg p-3 flex items-center space-x-3 cursor-pointer hover:bg-gray-50">
              <input type="radio" name="metode" value="QRIS" required>
              <img src="images/qris.png" class="w-16 h-8">
              <span>QRIS (semua e-wallet & bank)</span>
            </label>
            <label class="border rounded-lg p-3 flex items-center space-x-3 cursor-pointer hover:bg-gray-50">
              <input type="radio" name="metode" value="BNI">
              <img src="images/bni.jpg" class="w-16 h-8">
              <span>BNI Virtual Account</span>
            </label>
            <label class="border rounded-lg p-3 flex items-center space-x-3 cursor-pointer hover:bg-gray-50">
              <input type="radio" name="metode" value="BCA">
              <img src="images/bca.png" class="w-16 h-8">
              <span>BCA Transfer</span>
            </label>
            <label class="border rounded-lg p-3 flex items-center space-x-3 cursor-pointer hover:bg-gray-50">
              <input type="radio" name="metode" value="Mandiri">
              <img src="images/mandiri.png" class="w-16 h-8">
              <span>Mandiri Transfer</span>
            </label>
          </div>

          <!-- Instruksi -->
          <div id="instruksi" class="mt-4 p-4 border rounded-lg bg-gray-50 hidden">
            <h3 class="font-semibold mb-2">Instruksi Pembayaran</h3>
            <p id="detail-pembayaran" class="text-gray-700"></p>
          </div>
        </div>

        <!-- Upload Bukti -->
        <div>
          <h2 class="font-semibold mb-2">📤 Upload Bukti Pembayaran</h2>
          <input type="file" name="bukti" accept="image/*" required 
                 class="border p-2 rounded w-full" 
                 onchange="previewBukti(event)">
          <img id="preview-img" class="mt-3 w-40 hidden rounded border">
        </div>

        <!-- Tombol Submit -->
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-3 rounded-lg">
          🚀 Konfirmasi Pembayaran
        </button>
      </form>

    </div>
  </div>

</div>

<script>
const radios = document.querySelectorAll('input[name="metode"]');
const instruksi = document.getElementById('instruksi');
const detail = document.getElementById('detail-pembayaran');

radios.forEach(r => {
  r.addEventListener('change', () => {
    instruksi.classList.remove('hidden');
    const totalBayar = <?= $total ?>;
    const totalFormatted = new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR'}).format(totalBayar);

    if(r.value === 'QRIS'){
      detail.innerHTML = `Silakan scan QRIS berikut:<br><strong>Total: ${totalFormatted}</strong><br>
                          <img src='images/qris-statis.png' class='w-40 mt-2 rounded border'>`;
    } else if(r.value === 'BNI'){
      detail.innerHTML = `Transfer ke BNI 123456789 a.n Estrella Pustaka<br><strong>Total: ${totalFormatted}</strong>`;
    } else if(r.value === 'BCA'){
      detail.innerHTML = `Transfer ke BCA 987654321 a.n Estrella Pustaka<br><strong>Total: ${totalFormatted}</strong>`;
    } else if(r.value === 'Mandiri'){
      detail.innerHTML = `Transfer ke Mandiri 567890123 a.n Estrella Pustaka<br><strong>Total: ${totalFormatted}</strong>`;
    }
  });
});

// Preview bukti transfer
function previewBukti(e){
  const file = e.target.files[0];
  const img = document.getElementById('preview-img');
  if(file){
    img.src = URL.createObjectURL(file);
    img.classList.remove('hidden');
  }
}
</script>
</body>

</html>
