<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

// Require customer
$role = strtolower($_SESSION['role'] ?? '');
if (!isset($_SESSION['user_id']) || $role !== 'customer') {
    $_SESSION['message'] = 'Please login as a customer.';
    header('Location: /market_ecom/index.php');
    exit;
}
$user_id = (int)$_SESSION['user_id'];

// --- HANDLE ITEM REMOVAL (SELF-SUBMITTING LOGIC) ---
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['id'])) {
    $cart_item_id_to_remove = (int)$_GET['id'];

    // Security: Prepare a statement to delete the item, ensuring it belongs to the current user's cart.
    $delete_stmt = $conn->prepare("
        DELETE ci FROM cart_items ci
        JOIN cart c ON ci.cart_id = c.id
        WHERE ci.id = ? AND c.user_id = ?
    ");

    if ($delete_stmt) {
        $delete_stmt->bind_param("ii", $cart_item_id_to_remove, $user_id);
        if ($delete_stmt->execute()) {
            // Success message can be added if desired
        }
    }

    // Redirect to the same page but without the URL parameters to prevent accidental re-deletes on refresh.
    header("Location: cart.php");
    exit;
}
// --- END OF REMOVAL LOGIC ---


// --- SECURELY FETCH CART ITEMS ---
$cart_items = [];
$total = 0;

$cart_query = "
    SELECT ci.id as cart_item_id, ci.quantity, p.id as product_id, p.name, p.price, p.image
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    JOIN cart c ON ci.cart_id = c.id
    WHERE c.user_id = ?
";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_res = $stmt->get_result();

if ($cart_res) {
    while ($row = $cart_res->fetch_assoc()) {
        $cart_items[] = $row;
        $total += $row['price'] * $row['quantity'];
    }
}
?>

<?php include __DIR__ . '/../includes/customer_header.php'; ?>

<div class="container py-5">

    <h2 class="fw-bold text-dark mb-4"><i class="fa-solid fa-cart-shopping me-2"></i> My Shopping Cart</h2>

    <?php if (empty($cart_items)): ?>
        <div class="text-center py-5 card shadow-sm border-0">
            <i class="fa-solid fa-cart-arrow-down fa-4x text-muted mb-3"></i>
            <h3 class="text-muted">Your cart is empty</h3>
            <p class="text-secondary mb-4">Looks like you haven't added anything yet. Start shopping!</p>
            <a href="customer_dashboard.php" class="btn btn-primary btn-lg mx-auto" style="width: fit-content;">
                <i class="fa-solid fa-store me-2"></i> Go to Shop
            </a>
        </div>
    <?php else: ?>
        <div class="card shadow-sm border-0 p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="table-light">
                            <th scope="col">Product</th>
                            <th scope="col">Price</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Subtotal</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="/market_ecom/uploads/<?= htmlspecialchars($item['image']) ?>" class="img-thumbnail me-3" alt="<?= htmlspecialchars($item['name']) ?>" style="width: 70px; height: 70px;">
                                        <span class="fw-bold"><?= htmlspecialchars($item['name']) ?></span>
                                    </div>
                                </td>
                                <td>₹<?= number_format($item['price'], 2) ?></td>
                                <td>
                                    <input type="number" value="<?= $item['quantity'] ?>" min="1" class="form-control form-control-sm" style="width: 70px;" disabled>
                                </td>
                                <td>₹<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                <td>
                                    <!-- UPDATED LINK -->
                                    <a href="cart.php?action=remove&id=<?= $item['cart_item_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove this item from cart?')">
                                        <i class="fa-solid fa-trash"></i> Remove
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end align-items-center mt-4 pt-3 border-top">
                <h4 class="fw-bold me-3 text-dark">Total Amount:</h4>
                <h3 class="fw-bold text-success">₹<?= number_format($total, 2) ?></h3>
            </div>

            <div class="d-flex justify-content-end gap-3 mt-4">
                <a href="customer_dashboard.php" class="btn btn-outline-secondary btn-lg">
                    <i class="fa-solid fa-arrow-left me-2"></i> Continue Shopping
                </a>
                <a href="checkout.php" class="btn btn-primary btn-lg">
                    Proceed to Checkout <i class="fa-solid fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
