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

// Fetch categories for filter
$cats = [];
$rc = mysqli_query($conn, "SELECT DISTINCT category FROM products ORDER BY category ASC");
if ($rc) {
    while ($r = mysqli_fetch_assoc($rc)) $cats[] = $r['category'];
}

// Search & Filter Logic
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$sql = "SELECT * FROM products WHERE 1=1";
$types = "";
$params = [];

if ($search) {
    $sql .= " AND name LIKE ?";
    $types .= "s";
    $params[] = "%$search%";
}

if ($category) {
    $sql .= " AND category = ?";
    $types .= "s";
    $params[] = $category;
}

$sql .= " ORDER BY id DESC";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shop - Farmers Market</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f8fafc;
        }

        .product-card {
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            transition: transform .2s;
            text-align: center;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }

        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .product-card h4 {
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: #1e293b;
            font-weight: 600;
        }

        .product-card .category {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 10px;
        }

        .product-card .price {
            font-size: 1.25rem;
            font-weight: 700;
            color: #10b981;
            margin-bottom: 15px;
        }

        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 8px 16px;
            transition: all 0.2s;
        }

        .btn-primary { background: #3b82f6; border: none; color: white; }
        .btn-primary:hover { background: #2563eb; }

        .search-box {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            margin-bottom: 30px;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../includes/customer_header.php'; ?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark"><i class="fa-solid fa-store me-2"></i> Shop</h2>
    </div>

    <!-- Search & Filter -->
    <div class="search-box">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-4">
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach($cats as $c): ?>
                        <option value="<?= htmlspecialchars($c) ?>" <?= $category == $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>

    <!-- Products Grid -->
    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
        <?php if ($result && mysqli_num_rows($result) > 0): while($p = mysqli_fetch_assoc($result)): ?>
            <div class="col">
                <div class="product-card">
                    <img src="/market_ecom/uploads/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                    <h4><?= htmlspecialchars($p['name']) ?></h4>
                    <div class="category"><?= htmlspecialchars($p['category'] ?? 'General') ?></div>
                    <div class="price">₹<?= htmlspecialchars($p['price']) ?> <span style="font-size:0.8rem; color:#94a3b8; font-weight:400;">/ <?= htmlspecialchars($p['unit'] ?? 'unit') ?></span></div>

                    <div class="mt-auto">
                        <form method="POST" action="cart_action.php" class="d-grid">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <button name="add" class="btn btn-primary">
                                <i class="fa-solid fa-cart-plus me-2"></i> Add to Cart
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; else: ?>
            <div class="col-12 text-center py-5">
                <i class="fa-solid fa-basket-shopping fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No products found</h4>
                <p class="text-secondary">Try adjusting your search or filter.</p>
                <a href="shop.php" class="btn btn-outline-secondary mt-2">Clear Filters</a>
            </div>
        <?php endif; ?>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
