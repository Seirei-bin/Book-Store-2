<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT id, status_pesanan AS status, created_at 
    FROM orders 
    WHERE user_id=? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$orders = [];
while ($row = $res->fetch_assoc()) {
    // tambahkan link langsung ke detail order
    $row['link'] = "orders_detail.php?order_id=" . $row['id'];
    $orders[] = $row;
}
$stmt->close();

header('Content-Type: application/json');
echo json_encode($orders);
