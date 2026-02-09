<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

if (!isset($_SESSION['user_id'])) {
    die("Please login as vendor first.");
}
$vendor_id = $_SESSION['user_id'];

echo "<h1>Diagnostic Data</h1>";
echo "Current Vendor ID: " . $vendor_id . "<br><br>";

// 1. Check Orders Table for this vendor
echo "<h2>Orders Table (WHERE vendor_id = $vendor_id)</h2>";
$q1 = mysqli_query($conn, "SELECT * FROM orders WHERE vendor_id = $vendor_id");
if (!$q1) {
    echo "Query Failed: " . mysqli_error($conn);
} else {
    if (mysqli_num_rows($q1) > 0) {
        echo "<table border='1'><tr>";
        while ($field = mysqli_fetch_field($q1)) echo "<th>{$field->name}</th>";
        echo "</tr>";
        while ($row = mysqli_fetch_assoc($q1)) {
            echo "<tr>";
            foreach ($row as $val) echo "<td>$val</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No orders found in 'orders' table for this vendor_id.<br>";

        // Check if there are ANY orders
        $q_any = mysqli_query($conn, "SELECT * FROM orders LIMIT 5");
        echo "<h3>Sample of ANY orders in DB:</h3>";
        if (mysqli_num_rows($q_any) > 0) {
            echo "<table border='1'><tr><th>id</th><th>vendor_id</th><th>user_id</th></tr>";
            while ($row = mysqli_fetch_assoc($q_any)) {
                echo "<tr><td>{$row['id']}</td><td>{$row['vendor_id']}</td><td>{$row['user_id']}</td></tr>";
            }
            echo "</table>";
        } else {
            echo "Orders table is completely empty.";
        }
    }
}

// 2. Check Order Items
echo "<h2>Order Items</h2>";
$q2 = mysqli_query($conn, "SELECT * FROM order_items LIMIT 10");
if (mysqli_num_rows($q2) > 0) {
    echo "<table border='1'><tr><th>id</th><th>order_id</th><th>product_id</th></tr>";
    while ($row = mysqli_fetch_assoc($q2)) {
        echo "<tr><td>{$row['id']}</td><td>{$row['order_id']}</td><td>{$row['product_id']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "Order items table is empty.";
}

// 3. Check Products
echo "<h2>Products (WHERE vendor_id = $vendor_id)</h2>";
$q3 = mysqli_query($conn, "SELECT id, name, vendor_id FROM products WHERE vendor_id = $vendor_id");
if (mysqli_num_rows($q3) > 0) {
    echo "<table border='1'><tr><th>id</th><th>name</th><th>vendor_id</th></tr>";
    while ($row = mysqli_fetch_assoc($q3)) {
        echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['vendor_id']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "No products found for this vendor.";
}
?>