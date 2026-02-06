<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

// Response array for AJAX
$response = ['status' => 'error', 'message' => '', 'new_qty' => 0, 'cart_count' => 0];

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Handle both AJAX and regular requests
    if (isset($_POST['ajax'])) {
        $response['message'] = "Please login to add items to cart.";
        echo json_encode($response);
        exit;
    }
    $_SESSION['error'] = "Please login to add items to cart.";
    header("Location: /market_ecom/index.php");
    exit;
}
$user_id = (int)$_SESSION['user_id'];

// 2. Get Product ID and Action
$product_id = (int)($_POST['product_id'] ?? $_GET['id'] ?? 0);
$action = $_POST['action'] ?? 'add'; // 'add', 'increase', 'decrease', 'set'

if ($product_id <= 0) {
    // Handle error for both request types
    if (isset($_POST['ajax'])) {
        $response['message'] = "Invalid Product ID.";
        echo json_encode($response);
        exit;
    }
    $_SESSION['error'] = "Invalid Product ID.";
    header("Location: cart.php");
    exit;
}

// 3. Find or Create the User's Cart
$cart_id = null;
$cart_stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ?");
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart_res = $cart_stmt->get_result();
if ($cart_res->num_rows > 0) {
    $cart_id = $cart_res->fetch_assoc()['id'];
} else {
    $create_cart_stmt = $conn->prepare("INSERT INTO cart (user_id) VALUES (?)");
    $create_cart_stmt->bind_param("i", $user_id);
    if ($create_cart_stmt->execute()) {
        $cart_id = $conn->insert_id;
    } else {
        die("Failed to execute cart creation: " . $create_cart_stmt->error);
    }
}

// 4. Handle Item Logic
$price_stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
$price_stmt->bind_param("i", $product_id);
$price_stmt->execute();
$price_res = $price_stmt->get_result();

if ($price_res->num_rows > 0) {
    $price = $price_res->fetch_assoc()['price'];

    $check_stmt = $conn->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $check_stmt->bind_param("ii", $cart_id, $product_id);
    $check_stmt->execute();
    $res = $check_stmt->get_result();

    $new_qty = 0;
    if ($res->num_rows > 0) { // Item exists
        $row = $res->fetch_assoc();
        $current_qty = $row['quantity'];

        if ($action === 'decrease') $new_qty = $current_qty - 1;
        elseif ($action === 'set') $new_qty = (int)($_POST['quantity'] ?? 1);
        else $new_qty = $current_qty + 1; // 'add' or 'increase'

        if ($new_qty > 0) {
            $update_stmt = $conn->prepare("UPDATE cart_items SET quantity = ?, price = ? WHERE id = ?");
            $update_stmt->bind_param("idi", $new_qty, $price, $row['id']);
            $update_stmt->execute();
        } else {
            $del_stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ?");
            $del_stmt->bind_param("i", $row['id']);
            $del_stmt->execute();
        }
    } else { // Item does not exist
        if ($action !== 'decrease') {
            $new_qty = ($action === 'set') ? (int)($_POST['quantity'] ?? 1) : 1;
            if ($new_qty > 0) {
                $insert_stmt = $conn->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, price, user_id) VALUES (?, ?, ?, ?, ?)");
                $insert_stmt->bind_param("iiidi", $cart_id, $product_id, $new_qty, $price, $user_id);
                $insert_stmt->execute();
            }
        }
    }
    $response['new_qty'] = $new_qty;

    // 5. Calculate Total Cart Count for AJAX response
    $count_stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE cart_id = ?");
    $count_stmt->bind_param("i", $cart_id);
    $count_stmt->execute();
    $count_res = $count_stmt->get_result();
    $response['cart_count'] = $count_res->fetch_assoc()['total'] ?? 0;
    $response['status'] = 'success';

} else {
    $response['message'] = "Product not found.";
}

// Return JSON for AJAX, otherwise redirect as requested
if (isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} else {
    // Default action is to redirect to the cart page
    header("Location: cart.php");
    exit;
}
