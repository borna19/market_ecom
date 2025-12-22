<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

// Check if user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

/* =========================
   FETCH USER INFO
========================= */
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

/* =========================
   FETCH ADDRESSES
========================= */
$addr_stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id=?");
if (!$addr_stmt) {
    die("Prepare failed: " . $conn->error);
}
$addr_stmt->bind_param("i", $user_id);
$addr_stmt->execute();
$addresses = $addr_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* =========================
   FETCH ORDER SUMMARY
========================= */
$order_stmt = $conn->prepare("SELECT COUNT(*) as total_orders, MAX(created_at) as last_order_date FROM orders WHERE user_id=?");
if (!$order_stmt) {
    die("Prepare failed: " . $conn->error);
}
$order_stmt->bind_param("i", $user_id);
$order_stmt->execute();
$order_summary = $order_stmt->get_result()->fetch_assoc();

/* =========================
   PROFILE UPDATE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {

    $full_name = $_POST['full_name'] ?? '';
    $phone     = $_POST['phone'] ?? '';
    $gender    = $_POST['gender'] ?? '';

    // Profile photo upload
    $photo_name = $user['profile_photo'] ?? '';
    if (!empty($_FILES['profile_photo']['name'])) {
        $upload_dir = __DIR__ . "/uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $photo_name = time() . '_' . basename($_FILES['profile_photo']['name']);
        move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_dir . $photo_name);
    }

    $update_stmt = $conn->prepare("UPDATE users SET name=?, phone=?, gender=?, profile_photo=? WHERE id=?");
    if (!$update_stmt) {
        die("Prepare failed: " . $conn->error);
    }
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
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f4f6f8;color:#333;margin:0;}
.main-content{margin-left:240px;padding:25px;min-height:100vh;}
@media(max-width:768px){.main-content{margin-left:0;}}
.page-title{color:#1b5e20;font-weight:600;}
.card{background:#fff;border:none;border-radius:14px;box-shadow:0 4px 12px rgba(0,0,0,0.08);}
.card-header{background:#e8f5e9;color:#1b5e20;font-weight:600;}
.address-box{border:1px solid #ddd;padding:12px;border-radius:10px;margin-bottom:10px;background:#fafafa;}
.btn-success{background:#2e7d32;border:none;}
.btn-success:hover{background:#1b5e20;}
.btn-primary{background:#1976d2;border:none;}
.btn-info{background:#0288d1;border:none;color:#fff;}
.btn-danger{background:#e53935;border:none;}
</style>
</head>
<body>

<!-- SIDEBAR -->
<?php include __DIR__ . '/../includes/sidebar.php'; ?>

<div class="main-content">
<div class="container-fluid">

<h2 class="page-title mb-4">👤 User Profile</h2>

<?php if(isset($_GET['success'])): ?>
<div class="alert alert-success">Profile updated successfully!</div>
<?php endif; ?>

<!-- BASIC INFO -->
<div class="card mb-4">
<div class="card-header">Basic Information</div>
<div class="card-body">
<form method="POST" enctype="multipart/form-data">
<div class="row">
<div class="col-md-6 mb-3">
<label class="form-label">Full Name</label>
<input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
</div>
<div class="col-md-6 mb-3">
<label class="form-label">Email</label>
<input type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
</div>
</div>

<div class="row">
<div class="col-md-6 mb-3">
<label class="form-label">Phone</label>
<input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
</div>
<div class="col-md-6 mb-3">
<label class="form-label">Gender</label>
<select name="gender" class="form-select">
<option value="">Select</option>
<option value="Male" <?= (($user['gender'] ?? '')=='Male')?'selected':'' ?>>Male</option>
<option value="Female" <?= (($user['gender'] ?? '')=='Female')?'selected':'' ?>>Female</option>
<option value="Other" <?= (($user['gender'] ?? '')=='Other')?'selected':'' ?>>Other</option>
</select>
</div>
</div>

<div class="mb-3">
<label class="form-label">Profile Photo</label>
<input type="file" name="profile_photo" class="form-control">
<?php if(!empty($user['profile_photo'] ?? '')): ?>
<img src="uploads/<?= htmlspecialchars($user['profile_photo']) ?>" width="120" class="mt-2">
<?php endif; ?>
</div>

<button class="btn btn-success" name="update_profile">Save Changes</button>
</form>
</div>
</div>

<!-- ADDRESSES -->
<div class="card mb-4">
<div class="card-header">Address Details</div>
<div class="card-body">
<?php foreach($addresses as $addr): ?>
<div class="address-box">
<strong><?= htmlspecialchars($addr['house']) ?></strong>,
<?= htmlspecialchars($addr['city']) ?>,
<?= htmlspecialchars($addr['state']) ?> -
<?= htmlspecialchars($addr['pincode']) ?><br>
Landmark: <?= htmlspecialchars($addr['landmark']) ?><br>
<a href="edit_address.php?id=<?= $addr['id'] ?>" class="btn btn-sm btn-primary mt-2">Edit</a>
</div>
<?php endforeach; ?>
<a href="add_address.php" class="btn btn-primary mt-2">Add Address</a>
</div>
</div>

<!-- ORDER SUMMARY -->
<div class="card mb-4">
<div class="card-header">Order Summary</div>
<div class="card-body d-flex justify-content-between">
<span>Total Orders: <strong><?= $order_summary['total_orders'] ?? 0 ?></strong></span>
<span>Last Order: <strong><?= !empty($order_summary['last_order_date']) ? date("d M Y", strtotime($order_summary['last_order_date'])) : 'N/A'; ?></strong></span>
</div>
<a href="orders.php" class="btn btn-info m-3">View Orders</a>
</div>

<!-- ACTIONS -->
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
