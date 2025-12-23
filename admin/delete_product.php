<?php
session_start();
include __DIR__ . '/../includes/e_db.php'; // DB connection

// Only admin can delete
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Get product ID from URL
$product_id = $_GET['id'] ?? null;

if ($product_id) {
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
}

header("Location: manage_products.php");
exit;
