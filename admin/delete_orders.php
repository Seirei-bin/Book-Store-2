<?php
include '../config.php';
session_start();

// Cek login admin
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

if(isset($_POST['order_id'])){
    $order_id = (int)$_POST['order_id'];

    // Hapus detail order dulu
    $stmt1 = $conn->prepare("DELETE FROM order_details WHERE order_id=?");
    $stmt1->bind_param("i", $order_id);
    $stmt1->execute();
    $stmt1->close();

    // Hapus order
    $stmt2 = $conn->prepare("DELETE FROM orders WHERE id=?");
    $stmt2->bind_param("i", $order_id);
    $stmt2->execute();
    $stmt2->close();

    // Redirect ke daftar orders
    header("Location: orders.php");
    exit;
}
?>
