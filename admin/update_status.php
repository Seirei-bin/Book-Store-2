<?php
include '../config.php';
session_start();

// Cek login admin
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['order_id'], $_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];

    // Ambil data order + user_id
    $stmt = $conn->prepare("SELECT status, user_id FROM orders WHERE id=?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $old_status = $result['status'];
    $user_id = $result['user_id'];
    $stmt->close();

    // Update status order
    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    $stmt->close();

    // Ambil detail order
    $details = $conn->query("SELECT book_id, qty FROM order_details WHERE order_id=$order_id");

    // Kurangi stok kalau baru diproses / dikirim
    if (($new_status == 'Diproses' || $new_status == 'Dikirim') && ($old_status != 'Diproses' && $old_status != 'Dikirim')) {
        while ($row = $details->fetch_assoc()) {
            $book_id = $row['book_id'];
            $qty = $row['qty'];
            $conn->query("UPDATE books SET stok = GREATEST(stok - $qty, 0) WHERE id = $book_id");
        }
    }

    // Kembalikan stok kalau status berubah ke batal / belum bayar
    if (($old_status == 'Diproses' || $old_status == 'Dikirim') && ($new_status != 'Diproses' && $new_status != 'Dikirim')) {
        $details = $conn->query("SELECT book_id, qty FROM order_details WHERE order_id=$order_id");
        while ($row = $details->fetch_assoc()) {
            $book_id = $row['book_id'];
            $qty = $row['qty'];
            $conn->query("UPDATE books SET stok = stok + $qty WHERE id = $book_id");
        }
    }

    // === Tambahkan Notifikasi ke User ===
    $msg = "Pesanan #$order_id statusnya sekarang: $new_status";
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $msg);
    $stmt->execute();
    $stmt->close();
}

// Kembali ke halaman orders
header("Location: orders.php");
exit;
?>
