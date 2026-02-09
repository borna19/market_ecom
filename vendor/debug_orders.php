<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

echo "<h1>Debug Orders</h1>";

if (!isset($_SESSION['user_id'])) {
    die("Not logged in.");
}

$vendor_id = $_SESSION['user_id'];
echo "<p>Logged in Vendor ID: $vendor_id</p>";
echo "<p>Role: " . $_SESSION['role'] . "</p>";

// 1. Check Products for this vendor
echo "<h2>1. Products for Vendor $vendor_id</h2>";
$p_query = mysqli_query($conn, "SELECT id, name FROM products WHERE vendor_id = $vendor_id");
$product_ids = [];
if (mysqli_num_rows($p_query) > 0) {
    echo "<ul>";
    while ($row = mysqli_fetch_assoc($p_query)) {
        echo "<li>ID: " . $row['id'] . " - " . $row['name'] . "</li>";
        $product_ids[] = $row['id'];
    }
    echo "</ul>";
} else {
    echo "No products found for this vendor.<br>";
}

// 2. Check Order Items for these products
echo "<h2>2. Order Items for Vendor's Products</h2>";
if (!empty($product_ids)) {
    $p_ids_str = implode(',', $product_ids);
    $oi_query = mysqli_query($conn, "SELECT * FROM order_items WHERE product_id IN ($p_ids_str)");
    if (mysqli_num_rows($oi_query) > 0) {
        echo "<table border='1'><tr><th>Order ID</th><th>Product ID</th><th>Qty</th></tr>";
        while ($row = mysqli_fetch_assoc($oi_query)) {
            echo "<tr><td>" . $row['order_id'] . "</td><td>" . $row['product_id'] . "</td><td>" . $row['quantity'] . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "No order items found for these products.<br>";
    }
} else {
    echo "Skipping order items check (no products).<br>";
}

// 3. Check Orders table
echo "<h2>3. All Orders in Database</h2>";
$o_query = mysqli_query($conn, "SELECT * FROM orders LIMIT 5");
if (mysqli_num_rows($o_query) > 0) {
    echo "<table border='1'><tr><th>ID</th><th>User ID</th><th>Total</th><th>Status</th></tr>";
    while ($row = mysqli_fetch_assoc($o_query)) {
        echo "<tr><td>" . $row['id'] . "</td><td>" . $row['user_id'] . "</td><td>" . $row['total_amount'] . "</td><td>" . $row['status'] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "No orders in database.<br>";
}

// 4. Run the actual query used in orders.php
echo "<h2>4. Testing Actual Query</h2>";
$query = "
    SELECT o.id as order_id
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN products p ON oi.product_id = p.id
    WHERE p.vendor_id = $vendor_id
";
$result = mysqli_query($conn, $query);
if ($result) {
    echo "Query returned " . mysqli_num_rows($result) . " rows.";
} else {
    echo "Query failed: " . mysqli_error($conn);
}
?>