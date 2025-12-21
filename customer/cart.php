<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

// If cart empty
$cart_empty = empty($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Cart</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body{
            background:#111;
            color:#fff;
            margin:0;
        }

        /* CONTENT AFTER SIDEBAR */
        .main-content{
            margin-left:240px; /* sidebar width */
            padding:20px;
        }

        @media(max-width:768px){
            .main-content{
                margin-left:0;
            }
        }

        .card{background:#1e1e1e;border:none}
        .table{color:#fff}
        .table th,.table td{vertical-align:middle}
        img{width:60px;height:60px;object-fit:cover;border-radius:6px}
    </style>
</head>

<body>

<!-- SIDEBAR -->
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<!-- PAGE CONTENT START -->
<div class="main-content">

    <h2 class="mb-4">My Cart</h2>

    <div class="mb-3">
        <a href="/market_ecom/pages/dashboard.php" class="btn btn-outline-light btn-sm">
            ← Back to Dashboard
        </a>
    </div>

    <?php if ($cart_empty) { ?>

        <div class="alert alert-warning">
            Your cart is empty.
        </div>
        <a href="shop.php" class="btn btn-primary">Back to Shop</a>

    <?php } else { ?>

        <div class="card p-3">
            <div class="table-responsive">
                <table class="table table-dark table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th width="120">Qty</th>
                            <th>Subtotal</th>
                            <th width="80">Remove</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php
                    $total = 0;
                    foreach ($_SESSION['cart'] as $id => $qty) {

                        $q = mysqli_query($conn, "SELECT name, price, image FROM products WHERE id=$id");
                        $p = mysqli_fetch_assoc($q);

                        $subtotal = $p['price'] * $qty;
                        $total += $subtotal;
                    ?>
                        <tr>
                            <td>
                                <img src="../uploads/<?php echo $p['image'] ?? 'no-image.png'; ?>">
                                <?php echo htmlspecialchars($p['name']); ?>
                            </td>
                            <td>₹<?php echo $p['price']; ?></td>
                            <td><?php echo $qty; ?></td>
                            <td>₹<?php echo $subtotal; ?></td>
                            <td>
                                <a href="remove_cart.php?id=<?php echo $id; ?>" class="btn btn-danger btn-sm">
                                    ✕
                                </a>
                            </td>
                        </tr>
                    <?php } ?>

                    </tbody>
                </table>
            </div>

            <h4 class="text-end mt-3">
                Total: ₹<?php echo $total; ?>
            </h4>

            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="shop.php" class="btn btn-secondary">Continue Shopping</a>
                <a href="checkout.php" class="btn btn-success">Place Order</a>
            </div>
        </div>

    <?php } ?>

</div>
<!-- PAGE CONTENT END -->

</body>
</html>
