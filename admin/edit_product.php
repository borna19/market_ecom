<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$product_id = $_GET['id'] ?? null;

// Fetch product data
$stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? '';
    $stock = $_POST['stock'] ?? '';

    $update_stmt = $conn->prepare("UPDATE products SET name=?, price=?, stock=? WHERE id=?");
    $update_stmt->bind_param("sdii", $name, $price, $stock, $product_id);
    $update_stmt->execute();

    header("Location: manage_products.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<div class="content">
    <h1>Edit Product</h1>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Product Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Price ($)</label>
            <input type="number" name="price" step="0.01" class="form-control" value="<?= htmlspecialchars($product['price']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Stock</label>
            <input type="number" name="stock" class="form-control" value="<?= htmlspecialchars($product['stock']) ?>" required>
        </div>

        <button class="btn btn-success">Update Product</button>
        <a href="manage_products.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

</body>
</html>
