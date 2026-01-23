<?php
session_start();
include __DIR__ . '/../includes/e_db.php';
include __DIR__ . '/../includes/vendor_helpers.php';

// Vendor access check
$role = strtolower($_SESSION['role'] ?? '');
if (!isset($_SESSION['user_id']) || ($role !== 'vendor' && $role !== 'farmer')) {
    $_SESSION['message'] = 'Access denied: vendors only.';
    header('Location: /market_ecom/');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$vendor_id = getVendorIdForUser($conn, $user_id);

// Handle delete action (GET param: delete=id)
if (isset($_GET['delete']) && $vendor_id) {
    $del_id = (int) $_GET['delete'];
    // Fetch product to get image name and verify ownership
    $pstmt = mysqli_prepare($conn, "SELECT image FROM products WHERE id = ? AND vendor_id = ? LIMIT 1");
    if ($pstmt) {
        mysqli_stmt_bind_param($pstmt, 'ii', $del_id, $vendor_id);
        mysqli_stmt_execute($pstmt);
        $pres = mysqli_stmt_get_result($pstmt);
        if ($pres && mysqli_num_rows($pres) === 1) {
            $prow = mysqli_fetch_assoc($pres);
            mysqli_stmt_close($pstmt);
            // Delete row
            $dstmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ? AND vendor_id = ?");
            if ($dstmt) {
                mysqli_stmt_bind_param($dstmt, 'ii', $del_id, $vendor_id);
                if (mysqli_stmt_execute($dstmt)) {
                    // Remove image file if exists
                    if (!empty($prow['image'])) {
                        $file = __DIR__ . '/../uploads/' . $prow['image'];
                        if (file_exists($file)) unlink($file);
                    }
                    header('Location: /market_ecom/vendor/manage_products.php?deleted=1');
                    exit;
                }
                mysqli_stmt_close($dstmt);
            }
        } else {
            mysqli_stmt_close($pstmt);
            // Not found or not owned by vendor
            $_SESSION['message'] = 'Product not found or access denied.';
        }
    }
}

// Fetch products
$products_res = null;
if ($vendor_id) {
    $stmt = mysqli_prepare($conn, "SELECT id, name, price, stock, image FROM products WHERE vendor_id = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, 'i', $vendor_id);
    mysqli_stmt_execute($stmt);
    $products_res = mysqli_stmt_get_result($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Products - Vendor</title>
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

        .content-card {
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #842029;
            border-color: #f5c2c7;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            vertical-align: middle;
            color: #334155;
        }

        th {
            background: #f8fafc;
            font-weight: 600;
            color: #475569;
        }

        .img-thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
            margin-right: 5px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s;
        }

        .btn-primary {
            background: #3b82f6;
            color: #fff;
        }
        .btn-primary:hover { background: #2563eb; }

        .btn-danger {
            background: #ef4444;
            color: #fff;
        }
        .btn-danger:hover { background: #dc2626; }

        .btn-add {
            background: #10b981;
            color: white;
            padding: 10px 20px;
            font-size: 15px;
        }
        .btn-add:hover { background: #059669; }

    </style>
</head>
<body>

<div class="layout">

    <!-- Sidebar -->
    <?php include __DIR__ . '/../includes/vendor_sidebar.php'; ?>

    <!-- Main -->
    <div class="main">

        <div class="topbar">
            <h2><i class="fa-solid fa-list-check"></i> Manage Products</h2>
            <span><?= date("l, d M Y") ?></span>
        </div>

        <div class="content-card">

            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert"><i class="fa-solid fa-check-circle"></i> Product deleted successfully.</div>
            <?php endif; ?>
            <?php if (isset($_GET['updated'])): ?>
                <div class="alert"><i class="fa-solid fa-check-circle"></i> Product updated successfully.</div>
            <?php endif; ?>
            <?php if (isset($_GET['added'])): ?>
                <div class="alert"><i class="fa-solid fa-check-circle"></i> Product added successfully.</div>
            <?php endif; ?>

            <?php if (!$vendor_id): ?>
                <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> Vendor mapping not found. Please register as a vendor or contact support.</div>
            <?php else: ?>

                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <p style="margin:0; color:#64748b;">List of your products. You can edit or delete them here.</p>
                    <a href="add_product.php" class="btn btn-add"><i class="fa-solid fa-plus"></i> Add New Product</a>
                </div>

                <?php if ($products_res && mysqli_num_rows($products_res) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($p = mysqli_fetch_assoc($products_res)): ?>
                            <tr>
                                <td>
                                    <?php if(!empty($p['image'])): ?>
                                        <img src="/market_ecom/uploads/<?= htmlspecialchars($p['image']) ?>" alt="" class="img-thumbnail">
                                    <?php else: ?>
                                        <span style="color:#94a3b8; font-size:12px;">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($p['name']) ?></td>
                                <td>₹<?= htmlspecialchars($p['price']) ?></td>
                                <td><?= htmlspecialchars($p['stock']) ?></td>
                                <td>
                                    <a href="/market_ecom/vendor/add_product.php?edit=<?= $p['id'] ?>" class="btn btn-primary"><i class="fa-solid fa-pen"></i> Edit</a>
                                    <a href="/market_ecom/vendor/manage_products.php?delete=<?= $p['id'] ?>" class="btn btn-danger" onclick="return confirm('Delete this product?')"><i class="fa-solid fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align:center; padding:40px; color:#64748b;">
                        <i class="fa-solid fa-box-open fa-3x" style="margin-bottom:15px; color:#cbd5e1;"></i>
                        <p>No products found. Start by adding one!</p>
                    </div>
                <?php endif; ?>

            <?php endif; ?>

        </div>

    </div>
</div>

</body>
</html>
