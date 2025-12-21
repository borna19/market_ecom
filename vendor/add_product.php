<?php
session_start();
include __DIR__ . '/../includes/e_db.php';
include __DIR__ . '/../includes/vendor_helpers.php';

// REQUIRE LOGIN
if (!isset($_SESSION['user_id'])) {
    header("Location: /market_ecom/pages/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$vendor_id = getVendorIdForUser($conn, $user_id);
if (!$vendor_id) {
    $_SESSION['error'] = 'Vendor mapping not found. Please register as a vendor or contact admin.';
    header('Location: /market_ecom/');
    exit;
}

// INCLUDE SIDEBAR
include __DIR__ . '/../includes/sidebar.php';

// FETCH PRODUCTS
$products = mysqli_query($conn, "SELECT * FROM products WHERE vendor_id = '$vendor_id' ORDER BY id DESC");

// Edit mode: load product to edit if requested
$editing = false;
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $pstmt = mysqli_prepare($conn, "SELECT id, name, price, category, stock, image, description FROM products WHERE id = ? AND vendor_id = ? LIMIT 1");
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

<!-- PAGE STYLE -->
<style>
.content-area {
    margin-left: 260px;
    padding: 30px;
    background: #f5f7fa;
    min-height: 100vh;
}

.card-box {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
}

.product-card {
    background: #fff;
    border-radius: 12px;
    padding: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    text-align: center;
}

.product-card img {
    width: 100%;
    height: 180px;
    border-radius: 10px;
    object-fit: cover;
}

/* Button */
.btn {
    background: #1976d2;
    color: white;
    padding: 10px 18px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
.btn:hover { background: #0d47a1; }

/* MODAL */
.modal-bg {
    position: fixed;
    top:0; left:0;
    width:100%; height:100%;
    background: rgba(0,0,0,0.5);
    display:none;
    align-items:center;
    justify-content:center;
}

.modal-box {
    background:white;
    width:420px;
    padding:25px;
    border-radius:12px;
}

input, textarea {
    width:100%;
    padding:10px;
    margin-bottom:12px;
    border-radius:6px;
    border:1px solid #ccc;
}
</style>

<div class="content-area">
    <div class="card-box">
        <h2>My Products</h2>

        <button class="btn" onclick="openModal()">➕ Add Product</button>

        <div class="products-grid" style="margin-top:20px;">
            <?php while($p = mysqli_fetch_assoc($products)) { ?>
                <div class="product-card">
                    <img src="/market_ecom/uploads/<?php echo $p['image']; ?>" alt="">
                    <h4><?php echo $p['name']; ?></h4>
                    <p><b>₹<?php echo $p['price']; ?></b></p>
                    <p>Stock: <?php echo $p['stock']; ?></p>
                    <p>Category: <?php echo $p['category']; ?></p>
                    <p style="margin-top:8px;">
                        <a href="/market_ecom/vendor/add_product.php?edit=<?php echo $p['id']; ?>" class="btn" style="padding:6px 12px;font-size:12px;">Edit</a>
                        <a href="/market_ecom/vendor/manage_products.php?delete=<?php echo $p['id']; ?>" class="btn" style="background:#f44336;padding:6px 12px;font-size:12px;">Delete</a>
                    </p>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<!-- MODAL -->
<div class="modal-bg" id="modal">
    <div class="modal-box">
        <h3>Add Product</h3>

        <form action="add_product_action.php" method="POST" enctype="multipart/form-data">

            <label>Name</label>
            <input type="text" name="name" required value="<?php echo $editing ? htmlspecialchars($edit_product['name']) : ''; ?>">

            <label>Price</label>
            <input type="number" step="0.01" name="price" required value="<?php echo $editing ? htmlspecialchars($edit_product['price']) : ''; ?>">

            <label>Category</label>
            <input type="text" name="category" value="<?php echo $editing ? htmlspecialchars($edit_product['category']) : ''; ?>">

            <label>Stock</label>
            <input type="number" name="stock" value="<?php echo $editing ? htmlspecialchars($edit_product['stock']) : '0'; ?>">

            <label>Image</label>
            <input type="file" name="image" <?php echo $editing ? '' : 'required'; ?> >
            <?php if ($editing && !empty($edit_product['image'])): ?>
                <div style="margin-bottom:10px;"><small>Current image:</small><br><img src="/market_ecom/uploads/<?= htmlspecialchars($edit_product['image']) ?>" style="max-width:120px;margin-top:6px;border-radius:6px"></div>
            <?php endif; ?>

            <label>Description</label>
            <textarea name="description"><?php echo $editing ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>

            <?php if ($editing): ?>
                <input type="hidden" name="product_id" value="<?php echo $edit_product['id']; ?>">
                <input type="hidden" name="action" value="update">
            <?php endif; ?>

            <button class="btn" type="submit"><?php echo $editing ? 'Update Product' : 'Add Product'; ?></button>
            <button class="btn" type="button" onclick="closeModal()" style="background:gray;">Cancel</button>
        </form>
    </div>
</div>

<script>
function openModal(){ document.getElementById("modal").style.display="flex"; }
function closeModal(){ document.getElementById("modal").style.display="none"; }
</script>

<?php if ($editing): ?>
<script>openModal();</script>
<?php endif; ?>
