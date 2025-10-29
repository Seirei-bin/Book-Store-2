<?php
session_start();
include 'config.php';

// Pastikan user login
if(!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "not_logged_in"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$book_id = intval($_POST['book_id']);
$qty = intval($_POST['qty'] ?? 1);

// Cek apakah buku sudah ada di keranjang user
$sql = "SELECT * FROM cart WHERE user_id = $user_id AND book_id = $book_id";
$res = $conn->query($sql);

if($res && $res->num_rows > 0) {
    // Jika sudah ada, tambahkan qty
    $row = $res->fetch_assoc();
    $newQty = $row['qty'] + $qty;
    $conn->query("UPDATE cart SET qty = $newQty WHERE id = ".$row['id']);
} else {
    // Jika belum ada, insert baru
    $conn->query("INSERT INTO cart (user_id, book_id, qty) VALUES ($user_id, $book_id, $qty)");
}

echo json_encode(["success" => true]);
