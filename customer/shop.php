<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

if (!$conn) {
    die("Database connection failed");
}

$sql = "SELECT id, name, description, price, unit, image FROM products ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Market Shop</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* =========================
           GLOBAL
        ========================== */
        body{
            background:#f4f6f8; /* shop background */
            color:#333;
            margin:0;
        }

        /* =========================
           MAIN CONTENT
        ========================== */
        .main-content{
            margin-left:240px; /* sidebar width */
            padding:25px;
            min-height:100vh;
        }

        @media(max-width:768px){
            .main-content{
                margin-left:0;
            }
        }

        /* =========================
           PAGE HEADER
        ========================== */
        .page-title{
            font-weight:600;
            color:#1b5e20;
        }

        /* =========================
           PRODUCT CARD
        ========================== */
        .card{
            background:#ffffff;
            border:none;
            border-radius:14px;
            height:100%;
            box-shadow:0 4px 12px rgba(0,0,0,0.08);
            transition:all .2s ease;
        }

        .card:hover{
            transform:translateY(-4px);
            box-shadow:0 8px 20px rgba(0,0,0,0.15);
        }

        .card img{
            height:180px;
            object-fit:cover;
            border-radius:14px 14px 0 0;
            background:#eee;
        }

        .card-body h5{
            font-size:17px;
            font-weight:600;
            color:#2e7d32;
        }

        .card-body p{
            font-size:14px;
            color:#555;
            line-height:1.4;
        }

        /* =========================
           PRICE
        ========================== */
        .price{
            font-size:18px;
            font-weight:700;
            color:#1b5e20;
        }

        .unit{
            font-size:13px;
            color:#777;
        }

        /* =========================
           BUTTONS
        ========================== */
        .btn-cart{
            background:#2e7d32;
            border:none;
            color:#fff;
        }

        .btn-cart:hover{
            background:#1b5e20;
        }

        .btn-buy{
            background:#ff9800;
            border:none;
            color:#fff;
        }

        .btn-buy:hover{
            background:#fb8c00;
        }

        .btn-back{
            border:1px solid #2e7d32;
            color:#2e7d32;
        }

        .btn-back:hover{
            background:#2e7d32;
            color:#fff;
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="page-title">🛒 Market Shop</h2>
        <a href="/market_ecom/pages/dashboard.php" class="btn btn-back btn-sm">
            ← Back to Dashboard
        </a>
    </div>

    <!-- PRODUCTS -->
    <div class="row g-4">
        <?php if(mysqli_num_rows($result) > 0){ ?>
            <?php while($row = mysqli_fetch_assoc($result)){ ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card h-100">

                        <img src="../uploads/<?php echo htmlspecialchars($row['image'] ?? 'no-image.png'); ?>" alt="Product Image">

                        <div class="card-body d-flex flex-column">
                            <h5><?php echo htmlspecialchars($row['name']); ?></h5>

                            <?php if(!empty($row['description'])){ ?>
                                <p>
                                    <?php echo htmlspecialchars($row['description']); ?>
                                </p>
                            <?php } ?>

                            <div class="mb-3">
                                <span class="price">₹<?php echo $row['price']; ?></span>
                                <span class="unit">/ <?php echo $row['unit']; ?></span>
                            </div>

                            <div class="mt-auto">
                                <a href="cart_action.php?id=<?php echo $row['id']; ?>"
                                   class="btn btn-cart btn-sm w-100 mb-2">
                                    Add to Cart
                                </a>

                                <a href="checkout.php?buy=<?php echo $row['id']; ?>"
                                   class="btn btn-buy btn-sm w-100">
                                    Buy Now
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p>No products available</p>
        <?php } ?>
    </div>

</div>
<!-- END MAIN CONTENT -->

</body>
</html>
