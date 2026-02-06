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
   HANDLE ADD ADDRESS
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_address'])) {
    $house = $_POST['house'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $pincode = $_POST['pincode'] ?? '';
    $landmark = $_POST['landmark'] ?? '';

    $addr_insert_stmt = $conn->prepare("INSERT INTO addresses (user_id, house, city, state, pincode, landmark) VALUES (?, ?, ?, ?, ?, ?)");
    $addr_insert_stmt->bind_param("isssss", $user_id, $house, $city, $state, $pincode, $landmark);
    if ($addr_insert_stmt->execute()) {
        $_SESSION['success'] = "New address added successfully!";
    } else {
        $_SESSION['error'] = "Failed to add address.";
    }
    header("Location: profile.php");
    exit;
}

/* =========================
   FETCH USER INFO
========================= */
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

/* =========================
   FETCH ADDRESSES
========================= */
$addr_stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id=?");
$addr_stmt->bind_param("i", $user_id);
$addr_stmt->execute();
$addresses = $addr_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* =========================
   FETCH ORDER SUMMARY
========================= */
$order_stmt = $conn->prepare("SELECT COUNT(*) as total_orders, MAX(created_at) as last_order_date FROM orders WHERE user_id=?");
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

    $photo_name = $user['profile_photo'] ?? '';
    if (!empty($_FILES['profile_photo']['name'])) {
        $upload_dir = __DIR__ . "/../uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $photo_name = time() . '_' . basename($_FILES['profile_photo']['name']);
        move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_dir . $photo_name);
    }

    $update_stmt = $conn->prepare("UPDATE users SET name=?, phone=?, gender=?, profile_photo=? WHERE id=?");
    $update_stmt->bind_param("ssssi", $full_name, $phone, $gender, $photo_name, $user_id);
    $update_stmt->execute();

    $_SESSION['success'] = "Profile updated successfully!";
    header("Location: profile.php");
    exit;
}
?>

<?php include __DIR__ . '/../includes/customer_header.php'; ?>

<div class="container py-5">

    <h2 class="fw-bold text-dark mb-4"><i class="fa-solid fa-user me-2"></i> User Profile</h2>

    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- BASIC INFO -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0"><i class="fa-solid fa-id-card me-2"></i> Basic Information</h5>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">Select</option>
                        <option value="Male" <?= (($user['gender'] ?? '')=='Male')?'selected':'' ?>>Male</option>
                        <option value="Female" <?= (($user['gender'] ?? '')=='Female')?'selected':'' ?>>Female</option>
                        <option value="Other" <?= (($user['gender'] ?? '')=='Other')?'selected':'' ?>>Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Profile Photo</label>
                    <input type="file" name="profile_photo" class="form-control">
                    <?php if(!empty($user['profile_photo'] ?? '')): ?>
                        <img src="/market_ecom/uploads/<?= htmlspecialchars($user['profile_photo']) ?>" width="120" class="mt-2 rounded-circle">
                    <?php endif; ?>
                </div>
                <button class="btn btn-primary" name="update_profile">
                    <i class="fa-solid fa-save me-2"></i> Save Changes
                </button>
            </form>
        </div>
    </div>

    <!-- ADDRESSES -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fa-solid fa-address-card me-2"></i> Address Details</h5>
            <!-- MODAL TRIGGER BUTTON -->
            <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                <i class="fa-solid fa-plus"></i> Add New
            </button>
        </div>
        <div class="card-body">
            <?php if (empty($addresses)): ?>
                <p class="text-muted">No addresses added yet.</p>
            <?php else: ?>
                <?php foreach($addresses as $addr): ?>
                    <div class="alert alert-light border rounded-3 mb-3">
                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($addr['house']) ?></h6>
                        <p class="mb-1">
                            <?= htmlspecialchars($addr['city']) ?>, <?= htmlspecialchars($addr['state']) ?> - <?= htmlspecialchars($addr['pincode']) ?><br>
                            Landmark: <?= htmlspecialchars($addr['landmark']) ?><br>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ORDER SUMMARY -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0"><i class="fa-solid fa-box-open me-2"></i> Order Summary</h5>
        </div>
        <div class="card-body d-flex justify-content-between align-items-center">
            <span>Total Orders: <strong><?= $order_summary['total_orders'] ?? 0 ?></strong></span>
            <span>Last Order: <strong><?= !empty($order_summary['last_order_date']) ? date("d M Y", strtotime($order_summary['last_order_date'])) : 'N/A' ?></strong></span>
        </div>
        <div class="card-footer bg-transparent border-top">
            <a href="orders.php" class="btn btn-primary w-100">
                <i class="fa-solid fa-eye me-2"></i> View Orders
            </a>
        </div>
    </div>
</div>

<!-- ADD ADDRESS MODAL -->
<div class="modal fade" id="addAddressModal" tabindex="-1" aria-labelledby="addAddressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAddressModalLabel">Add New Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">House No / Building Name</label>
                        <input type="text" name="house" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">City / Town</label>
                        <input type="text" name="city" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">State</label>
                        <input type="text" name="state" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pincode</label>
                        <input type="text" name="pincode" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Landmark (Optional)</label>
                        <input type="text" name="landmark" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_address" class="btn btn-primary">Save Address</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
