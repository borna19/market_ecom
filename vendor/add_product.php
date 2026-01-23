<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

// Only vendor allowed
$role = strtolower($_SESSION['role'] ?? '');
if (!isset($_SESSION['user_id']) || $role !== 'vendor') {
    $_SESSION['message'] = "Please login as vendor.";
    header("Location: /market_ecom/index.php");
    exit;
}

$vendor_id = (int)$_SESSION['user_id'];

// Edit mode
$editing = false;
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $pstmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ? AND vendor_id = ? LIMIT 1");
    if ($pstmt) {
        mysqli_stmt_bind_param($pstmt, 'ii', $edit_id, $vendor_id);
        mysqli_stmt_execute($pstmt);
        $res = mysqli_stmt_get_result($pstmt);
        if ($res && mysqli_num_rows($res) === 1) {
            $editing = true;
            $edit_product = mysqli_fetch_assoc($res);
        }
        mysqli_stmt_close($pstmt);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= $editing ? 'Edit' : 'Add' ?> Product - Vendor</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f8fafc;
        }

        .layout {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: #1e293b;
            color: #fff;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            height: 100%;
        }

        .sidebar-header {
            padding: 25px 20px;
            background: #0f172a;
            text-align: center;
            border-bottom: 1px solid #334155;
            flex-shrink: 0;
        }

        .sidebar-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .sidebar-menu li {
            border-bottom: 1px solid #334155;
            flex-shrink: 0;
        }

        .sidebar-menu li:last-child {
            border-bottom: none;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 15px;
            transition: all 0.3s;
            gap: 12px;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: #3b82f6;
            color: #fff;
            padding-left: 30px;
        }

        .sidebar-menu a i {
            width: 20px;
            text-align: center;
        }

        .logout-link {
            background: #ef4444;
            color: white !important;
            justify-content: center;
        }
        .logout-link:hover {
            background: #dc2626 !important;
            padding-left: 25px !important;
        }

        /* Main */
        .main {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            height: 100%;
        }

        .topbar {
            background: #fff;
            padding: 20px 30px;
            border-radius: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            margin-bottom: 30px;
            flex-shrink: 0;
        }

        .topbar h2 { margin: 0; font-size: 22px; color: #1e293b; }
        .topbar span { color: #64748b; font-weight: 500; }

        .form-card {
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            max-width: 700px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #475569;
        }

        input[type="text"], input[type="number"], input[type="file"], textarea, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            box-sizing: border-box;
            transition: border-color 0.2s;
            font-size: 15px;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        .btn {
            background: #3b82f6;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: background 0.2s;
        }

        .btn:hover {
            background: #2563eb;
        }
    </style>
</head>
<body>

<div class="layout">

    <!-- Sidebar -->
    <?php include __DIR__ . '/../includes/vendor_sidebar.php'; ?>

    <!-- Main -->
    <div class="main">

        <div class="topbar">
            <h2><i class="fa-solid fa-pen-to-square"></i> <?= $editing ? 'Edit Product' : 'Add New Product' ?></h2>
            <span><?= date("l, d M Y") ?></span>
        </div>

        <div class="form-card">
            <form action="add_product_action.php" method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" required value="<?= $editing ? htmlspecialchars($edit_product['name']) : '' ?>">
                </div>

                <div class="form-group">
                    <label>Price (₹)</label>
                    <input type="number" step="0.01" name="price" required value="<?= $editing ? htmlspecialchars($edit_product['price']) : '' ?>">
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category" required value="<?= $editing ? htmlspecialchars($edit_product['category']) : '' ?>">
                </div>

                <div class="form-group">
                    <label>Stock Quantity</label>
                    <input type="number" name="stock" required value="<?= $editing ? htmlspecialchars($edit_product['stock']) : '1' ?>">
                </div>

                <div class="form-group">
                    <label>Product Image</label>
                    <input type="file" name="image" <?= $editing ? '' : 'required' ?>>
                    <?php if ($editing && !empty($edit_product['image'])): ?>
                        <div style="margin-top:10px;">
                            <small>Current:</small><br>
                            <img src="/market_ecom/uploads/<?= htmlspecialchars($edit_product['image']) ?>" style="height:80px;border-radius:8px;border:1px solid #e2e8f0;padding:4px;">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="5"><?= $editing ? htmlspecialchars($edit_product['description']) : '' ?></textarea>
                </div>

                <?php if ($editing): ?>
                    <input type="hidden" name="product_id" value="<?= $edit_product['id'] ?>">
                    <input type="hidden" name="action" value="update">
                <?php endif; ?>

                <button type="submit" class="btn">
                    <i class="fa-solid fa-save"></i> <?= $editing ? 'Update Product' : 'Save Product' ?>
                </button>
            </form>
        </div>

    </div>
</div>

</body>
</html>
