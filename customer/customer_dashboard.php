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

// --- FETCH CART DATA FOR UI ---
$cart_products = [];
$cart_check = $conn->prepare("
    SELECT ci.product_id, ci.quantity
    FROM cart_items ci
    JOIN cart c ON ci.cart_id = c.id
    WHERE c.user_id = ?
");
$cart_check->bind_param("i", $user_id);
$cart_check->execute();
$res_cart = $cart_check->get_result();
while ($row = $res_cart->fetch_assoc()) {
    $cart_products[$row['product_id']] = $row['quantity'];
}
// ------------------------------

// Fetch categories
$cats = [];
$rc = mysqli_query($conn, "SELECT DISTINCT category FROM products ORDER BY category ASC");
if ($rc) {
    while ($r = mysqli_fetch_assoc($rc)) $cats[] = $r['category'];
}

$category = $_GET['category'] ?? '';

if ($category) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE category = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, 's', $category);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
} else {
    $res = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
}
?>

<?php include __DIR__ . '/../includes/customer_header.php'; ?>

<div class="container py-5">

    <!-- Welcome Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark">Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'Customer') ?> 👋</h2>
            <p class="text-muted">Here's what's happening with your account today.</p>
        </div>
        <div class="text-end">
            <span class="text-muted"><?= date("l, d M Y") ?></span>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="d-flex align-items-center">
                    <div class="bg-light rounded-circle p-3 me-3">
                        <i class="fa-solid fa-cart-shopping fa-2x text-primary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Cart Items</h6>
                        <h3 class="fw-bold mb-0" id="dashboard-cart-count"><?= $cart_count ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="d-flex align-items-center">
                    <div class="bg-light rounded-circle p-3 me-3">
                        <i class="fa-solid fa-box-open fa-2x text-success"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">My Orders</h6>
                        <h3 class="fw-bold mb-0">View</h3>
                    </div>
                    <a href="orders.php" class="stretched-link"></a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="d-flex align-items-center">
                    <div class="bg-light rounded-circle p-3 me-3">
                        <i class="fa-solid fa-wallet fa-2x text-warning"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Wallet Balance</h6>
                        <h3 class="fw-bold mb-0">₹0.00</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Filter -->
    <div class="mb-4">
        <h4 class="fw-bold mb-3">Browse by Category</h4>
        <div class="d-flex flex-wrap gap-2">
            <a href="customer_dashboard.php" class="btn btn-outline-secondary rounded-pill <?= $category == '' ? 'active' : '' ?>">All</a>
            <?php foreach($cats as $c): ?>
                <a href="?category=<?= urlencode($c) ?>" class="btn btn-outline-secondary rounded-pill <?= $category == $c ? 'active' : '' ?>">
                    <?= htmlspecialchars($c) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Products Grid -->
    <h4 class="fw-bold mb-4">Fresh Products</h4>
    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
        <?php if ($res && mysqli_num_rows($res) > 0): while($p = mysqli_fetch_assoc($res)):
            $pid = $p['id'];
            $qty = $cart_products[$pid] ?? 0;
            $in_cart = $qty > 0;
        ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm product-card">
                    <img src="/market_ecom/uploads/<?= htmlspecialchars($p['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']) ?>" style="height: 200px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold"><?= htmlspecialchars($p['name']) ?></h5>
                        <p class="card-text text-muted small mb-2"><?= htmlspecialchars($p['category']) ?></p>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <span class="fs-5 fw-bold text-success">₹<?= htmlspecialchars($p['price']) ?></span>

                            <!-- DYNAMIC ACTION AREA -->
                            <div class="action-container" data-id="<?= $pid ?>">
                                <?php if ($in_cart): ?>
                                    <!-- ALREADY IN CART: AJAX Controls -->
                                    <div class="d-flex align-items-center gap-2">
                                        <button class="btn btn-sm btn-outline-danger rounded-circle btn-decrease" style="width: 32px; height: 32px;">-</button>
                                        <span class="fw-bold qty-display"><?= $qty ?></span>
                                        <button class="btn btn-sm btn-outline-success rounded-circle btn-increase" style="width: 32px; height: 32px;">+</button>
                                    </div>
                                <?php else: ?>
                                    <!-- NOT IN CART: Initial Add Button -->
                                    <button class="btn btn-sm btn-primary rounded-pill px-3 btn-initial-add">
                                        <i class="fa-solid fa-cart-plus"></i> Add
                                    </button>

                                    <!-- HIDDEN FORM: Quantity Selector + Submit -->
                                    <form action="cart_action.php" method="POST" class="d-none add-form d-flex align-items-center gap-2">
                                        <input type="hidden" name="product_id" value="<?= $pid ?>">
                                        <input type="hidden" name="action" value="set">

                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle btn-local-dec" style="width: 30px; height: 30px;">-</button>
                                        <input type="number" name="quantity" value="1" min="1" class="form-control form-control-sm text-center p-0 local-qty-input" style="width: 40px; height: 30px;" readonly>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle btn-local-inc" style="width: 30px; height: 30px;">+</button>

                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                                            Add <i class="fa-solid fa-arrow-right"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; else: ?>
            <div class="col-12 text-center py-5">
                <i class="fa-solid fa-basket-shopping fa-3x text-muted mb-3"></i>
                <p class="text-muted">No products found in this category.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<style>
    .product-card {
        transition: transform 0.2s;
    }
    .product-card:hover {
        transform: translateY(-5px);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // --- 1. LOGIC FOR ITEMS ALREADY IN CART (AJAX) ---
    function updateCart(productId, action, container) {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('action', action);
        formData.append('ajax', 1);

        fetch('cart_action.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const headerCount = document.querySelector('.badge-cart');
                const dashCount = document.getElementById('dashboard-cart-count');
                if (headerCount) headerCount.textContent = data.cart_count;
                if (dashCount) dashCount.textContent = data.cart_count;

                if (data.new_qty > 0) {
                    // Just update the number if the item is still in the cart
                    container.querySelector('.qty-display').textContent = data.new_qty;
                } else {
                    // **IMPROVED:** If quantity is 0, swap UI back to the "Add" state without reloading
                    container.innerHTML = `
                        <button class="btn btn-sm btn-primary rounded-pill px-3 btn-initial-add">
                            <i class="fa-solid fa-cart-plus"></i> Add
                        </button>
                        <form action="cart_action.php" method="POST" class="d-none add-form d-flex align-items-center gap-2">
                            <input type="hidden" name="product_id" value="${productId}">
                            <input type="hidden" name="action" value="set">
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle btn-local-dec" style="width: 30px; height: 30px;">-</button>
                            <input type="number" name="quantity" value="1" min="1" class="form-control form-control-sm text-center p-0 local-qty-input" style="width: 40px; height: 30px;" readonly>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle btn-local-inc" style="width: 30px; height: 30px;">+</button>
                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                                Add <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </form>
                    `;
                }
            }
        });
    }

    document.body.addEventListener('click', function(e) {
        const target = e.target.closest('button');
        if (!target) return;
        const container = target.closest('.action-container');
        if (!container) return;
        const productId = container.dataset.id;

        // AJAX Buttons
        if (target.classList.contains('btn-increase')) {
            updateCart(productId, 'increase', container);
        } else if (target.classList.contains('btn-decrease')) {
            updateCart(productId, 'decrease', container);
        }

        // --- 2. LOGIC FOR NEW ITEMS (LOCAL UI SWAP) ---
        if (target.classList.contains('btn-initial-add')) {
            target.classList.add('d-none');
            container.querySelector('.add-form').classList.remove('d-none');
        }

        // Local Quantity Adjustment (No Server Call)
        if (target.classList.contains('btn-local-inc')) {
            const input = container.querySelector('.local-qty-input');
            input.value = parseInt(input.value) + 1;
        }
        if (target.classList.contains('btn-local-dec')) {
            const input = container.querySelector('.local-qty-input');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            } else {
                container.querySelector('.add-form').classList.add('d-none');
                container.querySelector('.btn-initial-add').classList.remove('d-none');
            }
        }
    });

});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
