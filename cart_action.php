<?php
session_start();
include 'config.php';

// Pastikan user login
if(!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$id = intval($_GET['id']);       // book_id
$action = $_GET['action'];

$response = [
    "qty" => 0,
    "subtotal" => 0,
    "total" => 0,
    "msg" => ""
];

// Ambil data qty, harga, dan stok
$sql = "SELECT c.qty, b.harga, b.stok 
        FROM cart c 
        JOIN books b ON c.book_id = b.id 
        WHERE c.user_id = $user_id AND c.book_id = $id";
$res = $conn->query($sql);

if($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $qty = (int)$row['qty'];
    $harga = (int)$row['harga'];
    $stok = (int)$row['stok'];

    if($action == "plus") {
        if($qty < $stok) {
            $qty++;
            $conn->query("UPDATE cart SET qty = $qty WHERE user_id = $user_id AND book_id = $id");
        } else {
            $response['msg'] = "Stok habis, tidak bisa menambah lagi.";
        }
    } elseif($action == "minus") {
        $qty--;
        if($qty > 0) {
            $conn->query("UPDATE cart SET qty = $qty WHERE user_id = $user_id AND book_id = $id");
        } else {
            $conn->query("DELETE FROM cart WHERE user_id = $user_id AND book_id = $id");
            $qty = 0;
        }
    } elseif($action == "remove") {
        $conn->query("DELETE FROM cart WHERE user_id = $user_id AND book_id = $id");
        $qty = 0;
    }

    // Hitung subtotal item ini
    $response['qty'] = $qty;
    $response['subtotal'] = number_format($qty * $harga, 0, ',', '.');
}

// Hitung total semua isi cart user
$sqlTotal = "SELECT SUM(c.qty * b.harga) AS total 
             FROM cart c 
             JOIN books b ON c.book_id = b.id 
             WHERE c.user_id = $user_id";
$resTotal = $conn->query($sqlTotal);
if($resTotal) {
    $rowTotal = $resTotal->fetch_assoc();
    $response['total'] = number_format($rowTotal['total'] ?? 0, 0, ',', '.');
}

echo json_encode($response);
