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
    <title>Shop</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body{
            background:#111;
            color:#fff;
            margin:0;
        }

        /* MAIN CONTENT (AFTER SIDEBAR) */
        .main-content{
            margin-left:240px; /* sidebar width */
            padding:20px;
        }

        @media(max-width:768px){
            .main-content{
                margin-left:0;
            }
        }

        .card{
            background:#1e1e1e;
            border:none;
            border-radius:12px;
            height:100%;
        }

        .card img{
            height:180px;
            object-fit:cover;
            border-radius:12px 12px 0 0;
        }

        .price{
            color:#ffcc00;
            font-weight:bold;
            font-size:18px;
        }

        .unit{
            font-size:14px;
            color:#ccc;
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<!-- PAGE CONTENT STARTS AFTER SIDEBAR -->
<div class="main-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Available Products</h2>
        <a href="/market_ecom/pages/dashboard.php" class="btn btn-outline-light btn-sm">
            ← Back to Dashboard
        </a>
    </div>

    <div class="row g-4">
        <?php if(mysqli_num_rows($result) > 0){ ?>
            <?php while($row = mysqli_fetch_assoc($result)){ ?>
                <div class="col-md-3 col-sm-6">
                    <div class="card h-100">

                        <img src="../uploads/<?php echo htmlspecialchars($row['image'] ?? 'no-image.png'); ?>">

                        <div class="card-body d-flex flex-column">
                            <h5><?php echo htmlspecialchars($row['name']); ?></h5>

                            <?php if(!empty($row['description'])){ ?>
                                <p class="small">
                                    <?php echo htmlspecialchars($row['description']); ?>
                                </p>
                            <?php } ?>

                            <p class="price mb-2">
                                ₹<?php echo $row['price']; ?>
                                <span class="unit">/ <?php echo $row['unit']; ?></span>
                            </p>

                            <div class="mt-auto">
                                <a href="cart_action.php?id=<?php echo $row['id']; ?>"
                                   class="btn btn-success btn-sm w-100 mb-2">
                                    Add to Cart
                                </a>

                                <a href="checkout.php?buy=<?php echo $row['id']; ?>"
                                   class="btn btn-danger btn-sm w-100">
                                    Order Now
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
<!-- PAGE CONTENT END -->

</body>
</html>
