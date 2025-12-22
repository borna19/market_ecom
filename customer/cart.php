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

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* =========================
           GLOBAL
        ========================== */
        body{
            background:#f4f6f8; /* same family as shop */
            color:#333;
            margin:0;
        }

        /* =========================
           MAIN CONTENT
        ========================== */
        .main-content{
            margin-left:240px;
            padding:25px;
            min-height:100vh;
        }

        @media(max-width:768px){
            .main-content{
                margin-left:0;
            }
        }

        /* =========================
           CARD
        ========================== */
        .card{
            background:#ffffff;
            border:none;
            border-radius:14px;
            box-shadow:0 4px 12px rgba(0,0,0,0.08);
        }

        /* =========================
           TABLE
        ========================== */
        .table{
            color:#333;
            margin-bottom:0;
        }

        .table thead{
            background:#e8f5e9;
        }

        .table thead th{
            color:#1b5e20;
            font-weight:600;
            border-bottom:2px solid #c8e6c9;
        }

        .table td, .table th{
            vertical-align:middle;
        }

        .table-bordered > :not(caption) > *{
            border-color:#e0e0e0;
        }

        /* =========================
           PRODUCT IMAGE
        ========================== */
        img{
            width:60px;
            height:60px;
            object-fit:cover;
            border-radius:8px;
            margin-right:8px;
            border:1px solid #ddd;
        }

        /* =========================
           TOTAL
        ========================== */
        .total-amount{
            color:#1b5e20;
            font-weight:700;
        }

        /* =========================
           BUTTONS
        ========================== */
        .btn-back{
            border:1px solid #2e7d32;
            color:#2e7d32;
        }

        .btn-back:hover{
            background:#2e7d32;
            color:#fff;
        }

        .btn-remove{
            background:#e53935;
            border:none;
        }

        .btn-remove:hover{
            background:#c62828;
        }

        .btn-success{
            background:#2e7d32;
            border:none;
        }

        .btn-success:hover{
            background:#1b5e20;
        }

        .btn-secondary{
            background:#9e9e9e;
            border:none;
        }

        .btn-secondary:hover{
            background:#757575;
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>🛒 My Cart</h2>
        <a href="/market_ecom/pages/dashboard.php" class="btn btn-back btn-sm">
            ← Back to Dashboard
        </a>
    </div>

    <?php if ($cart_empty) { ?>

        <div class="alert alert-warning">
            Your cart is empty.
        </div>
        <a href="shop.php" class="btn btn-success">Go to Shop</a>

    <?php } else { ?>

        <div class="card p-3">
            <div class="table-responsive">
                <table class="table table-bordered">
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
                                <a href="remove_cart.php?id=<?php echo $id; ?>" 
                                   class="btn btn-remove btn-sm">
                                    ✕
                                </a>
                            </td>
                        </tr>
                    <?php } ?>

                    </tbody>
                </table>
            </div>

            <h4 class="text-end mt-3 total-amount">
                Total: ₹<?php echo $total; ?>
            </h4>

            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="shop.php" class="btn btn-secondary">
                    Continue Shopping
                </a>
                <a href="checkout.php" class="btn btn-success">
                    Place Order
                </a>
            </div>
        </div>

    <?php } ?>

</div>
<!-- END MAIN CONTENT -->

</body>
</html>
