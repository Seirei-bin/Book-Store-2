<?php
include '../config.php';
session_start();

// cek login
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? 0;

// ambil data buku dulu (buat hapus file sampul)
$stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();

if ($book) {
    // hapus file sampul kalau ada
    if ($book['sampul'] && file_exists("../uploads/" . $book['sampul'])) {
        unlink("../uploads/" . $book['sampul']);
    }

    // hapus data dari database
    $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: dashboard.php");
exit;
