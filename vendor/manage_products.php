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

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<style>
.content-area { margin-left: 260px; padding: 30px; }
.card-box { background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.08);} 
.table img{max-width:80px;height:auto}
</style>

<div class="content-area">
    <div class="card-box">
        <h3>Manage Products</h3>
        <p>List of your products. You can edit or delete them here.</p>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">Product deleted successfully.</div>
        <?php endif; ?>
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success">Product updated successfully.</div>
        <?php endif; ?>
        <?php if (isset($_GET['added'])): ?>
            <div class="alert alert-success">Product added successfully.</div>
        <?php endif; ?>

        <?php
        $user_id = (int) $_SESSION['user_id'];
        $vendor_id = getVendorIdForUser($conn, $user_id);
        if (!$vendor_id) {
            echo '<div class="alert alert-danger">Vendor mapping not found. Please register as a vendor or contact support.</div>';
            exit;
        }

        // Handle delete action (GET param: delete=id)
        if (isset($_GET['delete'])) {
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
        $stmt = mysqli_prepare($conn, "SELECT id, name, price, stock, image FROM products WHERE vendor_id = ? ORDER BY id DESC");
        mysqli_stmt_bind_param($stmt, 'i', $vendor_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res && mysqli_num_rows($res) > 0): ?>
            <table class="table table-striped mt-3">
                <thead><tr><th>Image</th><th>Name</th><th>Price</th><th>Qty</th><th>Actions</th></tr></thead>
                <tbody>
                <?php while($p = mysqli_fetch_assoc($res)): ?>
                    <tr>
                        <td><?php if(!empty($p['image'])): ?><img src="/market_ecom/uploads/<?= htmlspecialchars($p['image']) ?>" alt="" class="img-thumbnail"><?php endif; ?></td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td>$<?= htmlspecialchars($p['price']) ?></td>
                        <td><?= htmlspecialchars($p['stock']) ?></td>
                        <td>
                            <a href="/market_ecom/vendor/add_product.php?edit=<?= $p['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="/market_ecom/vendor/manage_products.php?delete=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No products yet. <a href="/market_ecom/vendor/add_product.php">Add one</a></p>
        <?php endif; ?>

    </div>
</div>


