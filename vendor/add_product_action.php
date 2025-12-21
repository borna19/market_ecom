<?php
session_start();
include __DIR__ . '/../includes/e_db.php';
include __DIR__ . '/../includes/vendor_helpers.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /market_ecom/pages/login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
// Try to map the logged-in user to a vendor id (vendors.id). If mapping fails, return an error.
$vendor_id = getVendorIdForUser($conn, $user_id);
if (!$vendor_id) {
    die("Database Error: No vendor record found for the current user, and automatic creation failed. Please create a vendor entry or contact the administrator.");
}

// Get form data safely
// Basic validation & casting
$name = trim($_POST['name'] ?? '');
$price = isset($_POST['price']) ? (float)$_POST['price'] : 0.0;
$category = trim($_POST['category'] ?? 'Uncategorized');
$stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
$description = trim($_POST['description'] ?? '');

// Prepare upload filename info (moving only happens in create or when an updated image is uploaded)
$orig_name = isset($_FILES['image']['name']) ? basename($_FILES['image']['name']) : '';
$img_tmp = $_FILES['image']['tmp_name'] ?? null;
$safe_name = $orig_name ? preg_replace('/[^A-Za-z0-9._-]/', '_', pathinfo($orig_name, PATHINFO_FILENAME)) : '';
$ext = $orig_name ? pathinfo($orig_name, PATHINFO_EXTENSION) : '';
$img = $orig_name ? (time() . '_' . $safe_name . ($ext ? '.' . $ext : '')) : '';
$target_path = __DIR__ . '/../uploads/' . $img;

// Insert into database (use safe prepared statement)
// Use a prepared statement and correct type string
// Detect if update or create
$is_update = isset($_POST['action']) && $_POST['action'] === 'update' && isset($_POST['product_id']);
$product_id = $is_update ? (int)$_POST['product_id'] : 0;

if ($is_update) {
    // Verify product exists and belongs to vendor
    $vstmt = mysqli_prepare($conn, "SELECT image FROM products WHERE id = ? AND vendor_id = ? LIMIT 1");
    if (!$vstmt) die('DB error: ' . mysqli_error($conn));
    mysqli_stmt_bind_param($vstmt, 'ii', $product_id, $vendor_id);
    mysqli_stmt_execute($vstmt);
    $vres = mysqli_stmt_get_result($vstmt);
    if (!$vres || mysqli_num_rows($vres) !== 1) {
        die('Product not found or access denied.');
    }
    $existing = mysqli_fetch_assoc($vres);
    mysqli_stmt_close($vstmt);

    // If a new image is uploaded, move it; otherwise keep existing value
    $new_image_uploaded = ($img_tmp && is_uploaded_file($img_tmp));
    if ($new_image_uploaded) {
        if (!move_uploaded_file($img_tmp, $target_path)) {
            die("Failed to upload image. Check folder permissions.");
        }
        $image_to_use = $img;
    } else {
        $image_to_use = $existing['image'];
    }

    // Build update SQL depending on whether image was updated
    if ($new_image_uploaded) {
        $sql = "UPDATE products SET name=?, price=?, category=?, stock=?, image=?, description=? WHERE id=? AND vendor_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'sdsissii', $name, $price, $category, $stock, $image_to_use, $description, $product_id, $vendor_id);
        }
    } else {
        $sql = "UPDATE products SET name=?, price=?, category=?, stock=?, description=? WHERE id=? AND vendor_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'sdsisii', $name, $price, $category, $stock, $description, $product_id, $vendor_id);
        }
    }
    
    if ($stmt && mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        // Remove old image file after a successful update if a new image was uploaded
        if ($new_image_uploaded && !empty($existing['image'])) {
            $oldfile = __DIR__ . '/../uploads/' . $existing['image'];
            if (file_exists($oldfile)) unlink($oldfile);
        }
        header("Location: /market_ecom/vendor/manage_products.php?updated=1");
        exit;
    } else {
        $err = $stmt ? mysqli_stmt_error($stmt) : mysqli_error($conn);
        die("Database Error: " . $err);
    }

} else {
    // Create new product
    if (!$img_tmp || !move_uploaded_file($img_tmp, $target_path)) {
        die("Failed to upload image. Check folder permissions.");
    }
    $sql = "INSERT INTO products (vendor_id, name, price, category, stock, image, description) VALUES (?, ?, ?, ?, ?, ?, ? )";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'isdsiss', $vendor_id, $name, $price, $category, $stock, $img, $description);
    }
}

if ($stmt && mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: /market_ecom/vendor/manage_products.php?added=1");
    exit;
} else {
    $err = $stmt ? mysqli_stmt_error($stmt) : mysqli_error($conn);
    die("Database Error: " . $err);
}
?>
