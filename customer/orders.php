<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

if (!isset($_SESSION['user_id'])) {
    die("Login required");
}

$user_id = (int)$_SESSION['user_id'];

$sql = "SELECT id, total_amount, status, delivery_type, created_at 
        FROM orders 
        WHERE user_id = $user_id
        ORDER BY id DESC";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>

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
    </style>
</head>

<body>

<!-- SIDEBAR -->
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<!-- PAGE CONTENT START -->
<div class="main-content">

    <h2 class="mb-4">My Orders</h2>

    <?php if(mysqli_num_rows($result) > 0){ ?>
        <div class="card p-3">
            <table class="table table-dark table-bordered">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Delivery</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($o = mysqli_fetch_assoc($result)){ ?>
                    <tr>
                        <td>#<?php echo $o['id']; ?></td>
                        <td>₹<?php echo $o['total_amount']; ?></td>
                        <td><?php echo htmlspecialchars($o['status']); ?></td>
                        <td><?php echo htmlspecialchars($o['delivery_type']); ?></td>
                        <td><?php echo $o['created_at']; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } else { ?>
        <div class="alert alert-warning">No orders found.</div>
    <?php } ?>

    <a href="/market_ecom/pages/dashboard.php" class="btn btn-outline-light mt-3">
        ← Back to Dashboard
    </a>

</div>
<!-- PAGE CONTENT END -->

</body>
</html>
