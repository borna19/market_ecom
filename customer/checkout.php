<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

if (!isset($_SESSION['user_id'])) {
    die("Login required");
}

$user_id = (int)$_SESSION['user_id'];

/* Order Now (single product) */
if (isset($_GET['buy']) && is_numeric($_GET['buy'])) {
    $_SESSION['cart'] = [
        (int)$_GET['buy'] => 1
    ];
}

if (empty($_SESSION['cart'])) {
    header("Location: shop.php");
    exit;
}

$total = 0;
$vendor_id = 0;

// calculate total & vendor
foreach ($_SESSION['cart'] as $pid => $qty) {
    $q = mysqli_query(
        $conn,
        "SELECT price, vendor_id FROM products WHERE id=$pid"
    );
    $p = mysqli_fetch_assoc($q);

    $total += $p['price'] * $qty;
    $vendor_id = $p['vendor_id']; 
}

// PLACE ORDER
mysqli_query(
    $conn,
    "INSERT INTO orders 
    (user_id, vendor_id, total_amount, status, delivery_type, shipping_address) 
    VALUES 
    ($user_id, $vendor_id, $total, 'Placed', 'Home Delivery', 'Not Provided')"
);

$order_id = mysqli_insert_id($conn);

// INSERT ORDER ITEMS
foreach ($_SESSION['cart'] as $pid => $qty) {

    $p = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT price FROM products WHERE id=$pid")
    );

    mysqli_query(
        $conn,
        "INSERT INTO order_items 
        (order_id, product_id, quantity, unit_price) 
        VALUES 
        ($order_id, $pid, $qty, {$p['price']})"
    );
}

unset($_SESSION['cart']);


header("Location: orders.php");
exit;
