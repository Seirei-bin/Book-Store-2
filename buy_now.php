<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login dulu untuk membeli!'); window.location='login.php';</script>";
    exit;
}

if (!isset($_GET['book_id'])) {
    header("Location: index.php");
    exit;
}

$book_id = (int)$_GET['book_id'];

// Ambil data buku
$stmt = $conn->prepare("SELECT * FROM books WHERE id=?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$book || $book['stok'] <= 0) {
    echo "<script>alert('Buku tidak tersedia!'); window.location='index.php';</script>";
    exit;
}

// Simpan ke session buy_now
$_SESSION['buy_now'] = [
    "id"      => $book['id'],
    "judul"   => $book['judul'],
    "harga"   => $book['harga'],
    "sampul"  => $book['sampul'],
    "penulis" => $book['penulis'],
    "qty"     => 1,
    "subtotal"=> $book['harga']
];

// Redirect ke checkout dengan mode buy_now
header("Location: checkout.php?mode=buy_now");
exit;
?>
