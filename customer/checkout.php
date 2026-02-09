<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

// Require customer
$role = strtolower($_SESSION['role'] ?? '');
if (!isset($_SESSION['user_id']) || $role !== 'customer') {
    $_SESSION['message'] = 'Please login as customer.';
    header('Location: /market_ecom/index.php');
    exit;
}
$user_id = (int)$_SESSION['user_id'];

// --- CORRECTED: Fetch Cart Items ---
$cart_query = "
    SELECT ci.id as cart_item_id, ci.quantity, p.id as product_id, p.name, p.price, p.vendor_id
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    JOIN cart c ON ci.cart_id = c.id
    WHERE c.user_id = ?
";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_res = $stmt->get_result();

$cart_items = [];
$total = 0;
if ($cart_res) {
    while ($row = $cart_res->fetch_assoc()) {
        $cart_items[] = $row;
        $total += $row['price'] * $row['quantity'];
    }
}

if (empty($cart_items)) {
    $_SESSION['error'] = "Your cart is empty.";
    header("Location: cart.php");
    exit;
}

// Fetch Addresses
$addr_stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ?");
$addr_stmt->bind_param("i", $user_id);
$addr_stmt->execute();
$addr_res = $addr_stmt->get_result();
$addresses = $addr_res ? $addr_res->fetch_all(MYSQLI_ASSOC) : [];

// Handle Order Placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $address_id = $_POST['address_id'] ?? null;
    $delivery_type = $_POST['delivery_type'] ?? 'delivery';

    if (!$address_id) {
        $error = "Please select a delivery address.";
    } else {
        // Get address details
        $addr_q = $conn->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
        $addr_q->bind_param("ii", $address_id, $user_id);
        $addr_q->execute();
        $addr = $addr_q->get_result()->fetch_assoc();
        $shipping_address = $addr['house'] . ', ' . $addr['city'] . ', ' . $addr['state'] . ' - ' . $addr['pincode'];

        // Get Cart ID
        $cart_id_stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ?");
        $cart_id_stmt->bind_param("i", $user_id);
        $cart_id_stmt->execute();
        $cart_id = $cart_id_stmt->get_result()->fetch_assoc()['id'];

        // Create Order
        $vendor_id = $cart_items[0]['vendor_id']; // Assuming single vendor cart for simplicity
        $order_stmt = $conn->prepare("INSERT INTO orders (user_id, vendor_id, total_amount, status, delivery_type, shipping_address) VALUES (?, ?, ?, 'pending', ?, ?)");
        $order_stmt->bind_param("iidss", $user_id, $vendor_id, $total, $delivery_type, $shipping_address);

        if ($order_stmt->execute()) {
            $order_id = $order_stmt->insert_id;

            // Insert Order Items
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
            foreach ($cart_items as $item) {
                $item_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                $item_stmt->execute();
            }

            // --- CORRECTED: Clear Cart ---
            $clear_stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
            $clear_stmt->bind_param("i", $cart_id);
            $clear_stmt->execute();

            $_SESSION['success'] = "Order placed successfully! Order ID: #$order_id";
            header("Location: orders.php");
            exit;
        } else {
            $error = "Failed to place order. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout - Customer</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f8fafc;
        }
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }
        .form-check-input:checked {
            background-color: #22c55e;
            border-color: #22c55e;
        }
        .btn-primary {
            background-color: #22c55e;
            border-color: #22c55e;
        }
        .btn-primary:hover {
            background-color: #16a34a;
            border-color: #16a34a;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../includes/customer_header.php'; ?>

<div class="container py-5">
    <h2 class="fw-bold text-dark mb-4"><i class="fa-solid fa-bag-shopping me-2"></i> Checkout</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i> <?= $error ?></div>
    <?php endif; ?>

    <form method="POST" id="checkoutForm">
        <div class="row g-4">
            <!-- Left Column: Address & Delivery -->
            <div class="col-lg-8">

                <!-- Address Selection -->
                <div class="card mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-location-dot me-2 text-primary"></i> Select Delivery Address</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($addresses)): ?>
                            <div class="text-center py-3">
                                <p class="text-muted">No addresses found.</p>
                                <a href="add_address.php" class="btn btn-outline-primary btn-sm">
                                    <i class="fa-solid fa-plus"></i> Add New Address
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($addresses as $addr): ?>
                                    <div class="col-md-6">
                                        <div class="card h-100 border">
                                            <div class="card-body">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="address_id" id="addr_<?= $addr['id'] ?>" value="<?= $addr['id'] ?>" required>
                                                    <label class="form-check-label" for="addr_<?= $addr['id'] ?>">
                                                        <strong><?= htmlspecialchars($addr['house']) ?></strong><br>
                                                        <small class="text-muted">
                                                            <?= htmlspecialchars($addr['city']) ?>, <?= htmlspecialchars($addr['state']) ?> - <?= htmlspecialchars($addr['pincode']) ?><br>
                                                            Landmark: <?= htmlspecialchars($addr['landmark']) ?>
                                                        </small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mt-3">
                                <a href="add_address.php" class="btn btn-link text-decoration-none p-0">
                                    <i class="fa-solid fa-plus"></i> Add another address
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Delivery Method -->
                <div class="card mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-truck me-2 text-primary"></i> Delivery Method</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="delivery_type" id="delivery_home" value="delivery" checked>
                            <label class="form-check-label" for="delivery_home">
                                Home Delivery <span class="text-muted small">(Standard shipping)</span>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="delivery_type" id="delivery_pickup" value="pickup">
                            <label class="form-check-label" for="delivery_pickup">
                                Store Pickup <span class="text-muted small">(Collect from vendor)</span>
                            </label>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Order Summary -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-receipt me-2 text-primary"></i> Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush mb-3">
                            <?php foreach ($cart_items as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <h6 class="my-0"><?= htmlspecialchars($item['name']) ?></h6>
                                        <small class="text-muted">Qty: <?= $item['quantity'] ?></small>
                                    </div>
                                    <span class="text-muted">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                                </li>
                            <?php endforeach; ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 fw-bold">
                                <span>Total (INR)</span>
                                <span class="text-success">₹<?= number_format($total, 2) ?></span>
                            </li>
                        </ul>

                        <div class="d-grid">
                            <button type="button" id="payButton" class="btn btn-primary btn-lg">
                                Pay Now <i class="fa-solid fa-credit-card ms-2"></i>
                            </button>
                        </div>
                        <div class="text-center mt-3">
                            <a href="cart.php" class="text-decoration-none text-muted small">Back to Cart</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="place_order" value="1">
        <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('payButton').onclick = function(e){
        e.preventDefault();

        // Basic validation
        var addressSelected = document.querySelector('input[name="address_id"]:checked');
        if(!addressSelected) {
            alert("Please select a delivery address.");
            return;
        }

        var options = {
            "key": "rzp_test_Rt2rbLMCwihrZf", // Enter the Key ID generated from the Dashboard
            "amount": "<?php echo $total * 100; ?>", // Amount is in currency subunits. Default currency is INR.
            "currency": "INR",
            "name": "Farmers Market",
            "description": "Order Payment",
            "image": "https://example.com/your_logo",
            "handler": function (response){
                document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                document.getElementById('checkoutForm').submit();
            },
            "prefill": {
                "name": "<?php echo $_SESSION['name'] ?? ''; ?>",
                "email": "<?php echo $_SESSION['email'] ?? ''; ?>",
                "contact": ""
            },
            "theme": {
                "color": "#22c55e"
            }
        };
        var rzp1 = new Razorpay(options);
        rzp1.on('payment.failed', function (response){
                alert("Payment Failed: " + response.error.description);
        });
        rzp1.open();
    }
</script>
</body>
</html>
