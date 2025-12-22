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

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* =========================
           GLOBAL
        ========================== */
        body{
            background:#f4f6f8;
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
           PAGE TITLE
        ========================== */
        .page-title{
            font-weight:600;
            color:#1b5e20;
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

        .table-bordered > :not(caption) > *{
            border-color:#e0e0e0;
        }

        .table td, .table th{
            vertical-align:middle;
        }

        /* =========================
           STATUS BADGES
        ========================== */
        .status{
            padding:4px 10px;
            border-radius:12px;
            font-size:13px;
            font-weight:600;
        }

        .status.pending{background:#fff3cd;color:#856404}
        .status.completed{background:#d4edda;color:#155724}
        .status.cancelled{background:#f8d7da;color:#721c24}

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
    </style>
</head>

<body>

<!-- SIDEBAR -->
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="page-title">📦 My Orders</h2>
        <a href="/market_ecom/pages/dashboard.php" class="btn btn-back btn-sm">
            ← Back to Dashboard
        </a>
    </div>

    <?php if(mysqli_num_rows($result) > 0){ ?>
        <div class="card p-3">
            <div class="table-responsive">
                <table class="table table-bordered">
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
                            <td>
                                <span class="status <?php echo strtolower($o['status']); ?>">
                                    <?php echo ucfirst($o['status']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($o['delivery_type']); ?></td>
                            <td><?php echo date("d M Y", strtotime($o['created_at'])); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php } else { ?>
        <div class="alert alert-warning">
            You have not placed any orders yet.
        </div>
        <a href="shop.php" class="btn btn-success">
            Go to Shop
        </a>
    <?php } ?>

</div>
<!-- END MAIN CONTENT -->

</body>
</html>
