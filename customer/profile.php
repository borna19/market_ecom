<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

// Fetch user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch addresses
$addr_stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id=?");
$addr_stmt->bind_param("i", $user_id);
$addr_stmt->execute();
$addresses = $addr_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch order summary
$order_stmt = $conn->prepare("
    SELECT COUNT(*) as total_orders, MAX(created_at) as last_order_date 
    FROM orders WHERE user_id=?
");
$order_stmt->bind_param("i", $user_id);
$order_stmt->execute();
$order_summary = $order_stmt->get_result()->fetch_assoc();

// Fetch wishlist
$wish_stmt = $conn->prepare("
    SELECT w.id, p.name, p.price 
    FROM wishlist w 
    JOIN products p ON w.product_id = p.id 
    WHERE w.user_id=?
");
$wish_stmt->bind_param("i", $user_id);
$wish_stmt->execute();
$wishlist = $wish_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'] ?? '';

    if (!empty($_FILES['profile_photo']['name'])) {
        $photo_name = time().'_'.$_FILES['profile_photo']['name'];
        move_uploaded_file($_FILES['profile_photo']['tmp_name'], 'uploads/'.$photo_name);
    } else {
        $photo_name = $user['profile_photo'];
    }

    $update_stmt = $conn->prepare("
        UPDATE users 
        SET full_name=?, phone=?, gender=?, profile_photo=? 
        WHERE id=?
    ");
    $update_stmt->bind_param("ssssi", $full_name, $phone, $gender, $photo_name, $user_id);
    $update_stmt->execute();

    header("Location: profile.php?success=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

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

        .card{
            background:#1e1e1e;
            border:none;
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<!-- PAGE CONTENT -->
<div class="main-content">
    <div class="container my-4">

        <h1 class="mb-4">User Profile</h1>

        <!-- BASIC INFO -->
        <div class="card mb-4">
            <div class="card-header">Basic Information</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">

                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control"
                               value="<?= htmlspecialchars($user['full_name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email (readonly)</label>
                        <input type="email" class="form-control"
                               value="<?= htmlspecialchars($user['email']); ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control"
                               value="<?= htmlspecialchars($user['phone']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">Select</option>
                            <option value="Male" <?= ($user['gender']=='Male')?'selected':''; ?>>Male</option>
                            <option value="Female" <?= ($user['gender']=='Female')?'selected':''; ?>>Female</option>
                            <option value="Other" <?= ($user['gender']=='Other')?'selected':''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Profile Photo</label>
                        <input type="file" name="profile_photo" class="form-control">
                        <?php if(!empty($user['profile_photo'])){ ?>
                            <img src="uploads/<?= htmlspecialchars($user['profile_photo']); ?>"
                                 class="img-thumbnail mt-2" width="120">
                        <?php } ?>
                    </div>

                    <button class="btn btn-success" name="update_profile">
                        Save Changes
                    </button>
                </form>
            </div>
        </div>

        <!-- ADDRESS -->
        <div class="card mb-4">
            <div class="card-header">Address Details</div>
            <div class="card-body">
                <?php foreach ($addresses as $addr){ ?>
                    <div class="border p-2 mb-2 rounded">
                        <strong><?= htmlspecialchars($addr['house']); ?></strong>,
                        <?= htmlspecialchars($addr['city']); ?>,
                        <?= htmlspecialchars($addr['state']); ?> -
                        <?= htmlspecialchars($addr['pincode']); ?><br>
                        Landmark: <?= htmlspecialchars($addr['landmark']); ?><br>
                        <a href="edit_address.php?id=<?= $addr['id']; ?>" class="btn btn-sm btn-primary mt-1">Edit</a>
                    </div>
                <?php } ?>
                <a href="add_address.php" class="btn btn-primary">Add Address</a>
            </div>
        </div>

        <!-- ORDER SUMMARY -->
        <div class="card mb-4">
            <div class="card-header">Order Summary</div>
            <div class="card-body d-flex justify-content-between">
                <span>Total Orders: <?= $order_summary['total_orders']; ?></span>
                <span>Last Order: <?= $order_summary['last_order_date'] ?? 'N/A'; ?></span>
            </div>
            <a href="orders.php" class="btn btn-info m-3">View All Orders</a>
        </div>

        <!-- LOGOUT -->
        <div class="card">
            <div class="card-body d-flex gap-2">
                <a href="logout.php" class="btn btn-secondary">Logout</a>
                <a href="deactivate_account.php" class="btn btn-danger">Deactivate Account</a>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
