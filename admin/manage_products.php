<?php
session_start();
include __DIR__ . '/../includes/e_db.php'; // DB connection

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

/* =========================
   FETCH PRODUCTS
========================= */
$product_result = $conn->query("SELECT * FROM products ORDER BY id DESC");
$products = $product_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f8;
        }

        .content {
            margin-left: 250px; /* sidebar width */
            padding: 30px;
        }

        table {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .table th, .table td {
            vertical-align: middle !important;
        }

        .btn-edit {
            background: #1976d2;
            color: #fff;
        }
        .btn-edit:hover { background: #0d47a1; }

        .btn-delete {
            background: #e53935;
            color: #fff;
        }
        .btn-delete:hover { background: #b71c1c; }
    </style>
</head>
<body>

<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<div class="content">
    <h1 class="mb-4">Manage Products</h1>

    <a href="add_product.php" class="btn btn-success mb-3">+ Add Product</a>

    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Vendor ID</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($products)): ?>
                <?php foreach($products as $product): ?>
                <tr>
                    <td><?= htmlspecialchars($product['id']) ?></td>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= htmlspecialchars($product['vendor_id']) ?></td>
                    <td>$<?= number_format($product['price'], 2) ?></td>
                    <td><?= htmlspecialchars($product['stock']) ?></td>
                    <td><?= htmlspecialchars($product['created_at']) ?></td>
                    <td>
                        <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-edit">Edit</a>
                        <a href="delete_product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">No products found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
