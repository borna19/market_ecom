<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $house   = trim($_POST['house'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $state   = trim($_POST['state'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    $landmark = trim($_POST['landmark'] ?? '');

    // ✅ Validation
    if ($house === '' || $city === '' || $state === '' || $pincode === '') {
        $error = "All required fields are mandatory.";
    } elseif (!preg_match('/^[0-9]{6}$/', $pincode)) {
        $error = "Pincode must be exactly 6 digits.";
    } else {

        $stmt = $conn->prepare(
            "INSERT INTO addresses (user_id, house, city, state, pincode, landmark)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "isssss",
            $user_id,
            $house,
            $city,
            $state,
            $pincode,
            $landmark
        );

        if ($stmt->execute()) {
            header("Location: profile.php?address=added");
            exit;
        } else {
            $error = "Failed to add address. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Address</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="container my-5">

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Add New Address</h4>
        </div>

        <div class="card-body">

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">House / Street *</label>
                    <input type="text" name="house" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">City *</label>
                    <input type="text" name="city" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">State *</label>
                    <input type="text" name="state" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Pincode *</label>
                    <input type="text" name="pincode" class="form-control" maxlength="6" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Landmark (optional)</label>
                    <input type="text" name="landmark" class="form-control">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">Save Address</button>
                    <a href="profile.php" class="btn btn-secondary">Back</a>
                </div>

            </form>

        </div>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
