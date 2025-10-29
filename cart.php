<?php
session_start();
include 'config.php';

// Cek apakah user sudah login
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data keranjang dari tabel cart
$books = [];
$total = 0;

$sql = "SELECT b.*, c.qty, (c.qty * b.harga) AS subtotal 
        FROM cart c 
        JOIN books b ON c.book_id = b.id 
        WHERE c.user_id = $user_id";
$res = $conn->query($sql);

if($res && $res->num_rows > 0) {
    while($row = $res->fetch_assoc()) {
        $total += $row['subtotal'];
        $books[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Keranjang - Digital Bookstore</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-gray-100 p-6">

<div class="max-w-4xl mx-auto bg-white shadow rounded-lg p-6 animate-fadeIn">
<h1 class="text-2xl font-bold text-center text-blue-600 mb-6">🛒 Keranjang Belanja</h1>

<?php if(empty($books)): ?>
    <p class="text-center text-gray-500">Keranjang masih kosong.</p>
    <div class="text-center mt-4">
        <a href="index.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition">
        ← Kembali Belanja
        </a>
    </div>
<?php else: ?>
<div class="overflow-x-auto">
<table class="w-full border-collapse">
<thead>
<tr class="bg-gray-50 border-b">
<th class="p-3 text-left">Buku</th>
<th class="p-3 text-center">Qty</th>
<th class="p-3 text-right">Harga</th>
<th class="p-3 text-right">Subtotal</th>
<th class="p-3 text-center">Aksi</th>
</tr>
</thead>
<tbody>
<?php foreach($books as $book): ?>
<tr data-id="<?php echo $book['id']; ?>" class="hover:bg-gray-50 transition">
<td class="p-3 flex items-center space-x-3">
<img src="uploads/<?php echo $book['sampul']; ?>" 
     alt="<?php echo htmlspecialchars($book['judul']); ?>" 
     class="w-12 h-16 object-contain bg-gray-100 rounded shadow-sm">
<div>
<p class="font-semibold"><?php echo $book['judul']; ?></p>
<p class="text-sm text-gray-500">✍ <?php echo $book['penulis']; ?></p>
</div>
</td>
<td class="p-3 text-center">
<button class="bg-gray-200 px-2 py-1 rounded qty-btn transition hover:bg-gray-300" data-action="minus">-</button>
<span class="mx-2 qty"><?php echo $book['qty']; ?></span>
<button class="bg-gray-200 px-2 py-1 rounded qty-btn transition hover:bg-gray-300" data-action="plus">+</button>
</td>
<td class="p-3 text-right">Rp <?php echo number_format($book['harga'],0,',','.'); ?></td>
<td class="p-3 text-right subtotal">Rp <?php echo number_format($book['subtotal'],0,',','.'); ?></td>
<td class="p-3 text-center">
<button class="text-red-500 hover:text-red-700 transition transform hover:scale-110 remove-btn">Hapus</button>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<div class="mt-6 p-4 border rounded-lg bg-gray-50 shadow-sm flex justify-between items-center">
  <a href="index.php" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded transition">← Lanjut Belanja</a>
  <div class="text-right">
    <p class="text-lg font-semibold text-gray-700">
      Total: <span id="total" class="text-green-600">Rp <?php echo number_format($total,0,',','.'); ?></span>
    </p>
    <a href="checkout.php" class="mt-3 inline-block bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-lg shadow transition transform hover:scale-105">
        ✅ Checkout
    </a>
  </div>
</div>
<?php endif; ?>
</div>

<script>
$(document).ready(function() {
    $('.qty-btn').click(function() {
        let tr = $(this).closest('tr');
        let id = tr.data('id');
        let action = $(this).data('action');

        $.get('cart_action.php', {id: id, action: action}, function(data) {
            let res = JSON.parse(data);
            if(res.qty <= 0) {
                tr.addClass("opacity-0 transition duration-500");
                setTimeout(() => tr.remove(), 500);
            } else {
                let qtyEl = tr.find('.qty');
                let subtotalEl = tr.find('.subtotal');

                qtyEl.text(res.qty).addClass("scale-125 text-blue-600 transition");
                subtotalEl.text('Rp ' + res.subtotal).addClass("scale-105 text-green-600 transition");

                setTimeout(() => {
                    qtyEl.removeClass("scale-125 text-blue-600");
                    subtotalEl.removeClass("scale-105 text-green-600");
                }, 300);
            }
            $('#total').text('Rp ' + res.total);
        });
    });

    $('.remove-btn').click(function() {
        let tr = $(this).closest('tr');
        let id = tr.data('id');
        $.get('cart_action.php', {id: id, action: 'remove'}, function(data) {
            let res = JSON.parse(data);
            tr.addClass("opacity-0 transition duration-500");
            setTimeout(() => tr.remove(), 500);
            $('#total').text('Rp ' + res.total);
        });
    });
});
</script>

<style>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn { animation: fadeIn 0.6s ease-in-out; }
</style>

</body>
</html>
