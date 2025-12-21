<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

// Only vendors/farmers allowed
$role = strtolower($_SESSION['role'] ?? '');
if (!isset($_SESSION['user_id']) || ($role !== 'vendor' && $role !== 'farmer')) {
    $_SESSION['message'] = 'Access denied: vendors only.';
    header('Location: /market_ecom/');
    exit;
}

include __DIR__ . '/../includes/header.php';
?>

<div class="content-wrapper" style="padding:20px;">
    <h2>Vendor Dashboard</h2>
    <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong></p>
    <p>This is a simple vendor dashboard placeholder. Add vendor features here.</p>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
