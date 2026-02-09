<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

// --- 1. Authentication Check ---
$role = strtolower($_SESSION['role'] ?? '');
if (!isset($_SESSION['user_id']) || $role !== 'vendor') {
    $_SESSION['message'] = "Please login as vendor.";
    header("Location: /market_ecom/index.php");
    exit;
}
$vendor_id = (int)$_SESSION['user_id'];

// --- 2. AJAX: Handle View Order Details (Modal Content) ---
if (isset($_GET['action']) && $_GET['action'] === 'view_details') {
    $order_id = (int)$_GET['id'];

    // Fetch Order Info
    $query = "
        SELECT o.*,
               COALESCE(u.name, 'Guest/Deleted') as customer_name,
               COALESCE(u.email, 'N/A') as customer_email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if (!$order) {
        echo "<div class='alert alert-danger'>Order not found.</div>";
        exit;
    }

    // Fetch Order Items (Only for this vendor)
    $items_query = "
        SELECT oi.*, p.name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ? AND p.vendor_id = ?
    ";
    $stmt = $conn->prepare($items_query);
    $stmt->bind_param("ii", $order_id, $vendor_id);
    $stmt->execute();
    $items_res = $stmt->get_result();
    ?>

    <!-- Modal Content HTML -->
    <div class="container-fluid p-0">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="p-3 bg-light rounded h-100">
                    <h6 class="text-uppercase text-muted small fw-bold mb-2"><i class="fa-solid fa-user me-1"></i> Customer</h6>
                    <div class="fw-bold text-dark"><?= htmlspecialchars($order['customer_name']) ?></div>
                    <div class="small text-muted"><?= htmlspecialchars($order['customer_email']) ?></div>
                    <div class="small text-muted mt-1"><i class="fa-regular fa-calendar me-1"></i> <?= date("d M Y, h:i A", strtotime($order['created_at'])) ?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 bg-light rounded h-100">
                    <h6 class="text-uppercase text-muted small fw-bold mb-2"><i class="fa-solid fa-truck me-1"></i> Shipping</h6>
                    <div class="mb-1">
                        <?php if($order['delivery_type'] == 'pickup'): ?>
                            <span class="badge bg-info text-dark"><i class="fa-solid fa-store me-1"></i> Store Pickup</span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><i class="fa-solid fa-house me-1"></i> Home Delivery</span>
                        <?php endif; ?>
                    </div>
                    <div class="small text-muted text-break">
                        <?= nl2br(htmlspecialchars($order['shipping_address'] ?? 'No address provided')) ?>
                    </div>
                </div>
            </div>
        </div>

        <h6 class="text-uppercase text-muted small fw-bold mb-2">Order Items</h6>
        <div class="table-responsive border rounded">
            <table class="table table-sm table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Product</th>
                        <th class="text-end">Price</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end pe-3">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_vendor = 0;
                    if ($items_res->num_rows > 0):
                        while($item = $items_res->fetch_assoc()):
                            $line_total = $item['unit_price'] * $item['quantity'];
                            $total_vendor += $line_total;
                    ?>
                    <tr>
                        <td class="ps-3"><?= htmlspecialchars($item['name']) ?></td>
                        <td class="text-end">₹<?= number_format($item['unit_price'], 2) ?></td>
                        <td class="text-center"><?= $item['quantity'] ?></td>
                        <td class="text-end pe-3">₹<?= number_format($line_total, 2) ?></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="4" class="text-center text-muted">No items found for your vendor account in this order.</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Total (Your Items):</td>
                        <td class="text-end fw-bold text-success pe-3">₹<?= number_format($total_vendor, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-3 text-end">
            <span class="text-muted small me-2">Current Status:</span>
            <span class="badge bg-dark"><?= ucfirst($order['status']) ?></span>
        </div>
    </div>
    <?php
    exit; // Stop script here for AJAX requests
}

// --- 3. Handle Actions (Update & Delete) ---
$msg = "";
$msg_type = "";

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];

    $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $new_status, $order_id);

    if ($update_stmt->execute()) {
        $msg = "Order #$order_id status updated to " . ucfirst($new_status);
        $msg_type = "success";
    } else {
        $msg = "Failed to update status.";
        $msg_type = "danger";
    }
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_order') {
    $order_id = (int)$_POST['order_id'];

    // Delete items
    $del_query = "DELETE FROM order_items WHERE order_id = ?";
    $del_stmt = $conn->prepare($del_query);
    $del_stmt->bind_param("i", $order_id);

    if ($del_stmt->execute()) {
        $conn->query("DELETE FROM orders WHERE id = $order_id");
        $msg = "Order deleted successfully.";
        $msg_type = "success";
    } else {
        $msg = "Failed to delete item.";
        $msg_type = "danger";
    }
}

// --- 4. Fetch Orders for Table ---
$query = "
    SELECT
        o.id as order_id,
        o.created_at,
        o.status,
        o.shipping_address,
        o.delivery_type,
        COALESCE(u.name, 'Guest/Deleted') as customer_name,
        COALESCE(u.email, 'N/A') as customer_email,
        p.name as product_name,
        p.vendor_id as product_vendor_id,
        oi.quantity,
        oi.unit_price,
        (oi.quantity * oi.unit_price) as total_line_price
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
";
$orders = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vendor Orders</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        /* Sidebar Layout */
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #f8fafc; }
        .layout { display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: 260px; background: #1e293b; color: #fff; display: flex; flex-direction: column; box-shadow: 2px 0 10px rgba(0,0,0,0.1); height: 100%; }
        .sidebar-header { padding: 25px 20px; background: #0f172a; text-align: center; border-bottom: 1px solid #334155; flex-shrink: 0; }
        .sidebar-header h3 { margin: 0; font-size: 20px; font-weight: 700; color: #fff; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .sidebar-menu { list-style: none; padding: 0; margin: 0; flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .sidebar-menu li { border-bottom: 1px solid #334155; flex-shrink: 0; }
        .sidebar-menu li:last-child { border-bottom: none; }
        .sidebar-menu a { display: flex; align-items: center; padding: 15px 25px; color: #cbd5e1; text-decoration: none; font-size: 15px; transition: all 0.3s; gap: 12px; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: #3b82f6; color: #fff; padding-left: 30px; }
        .sidebar-menu a i { width: 20px; text-align: center; }
        .logout-link { background: #ef4444; color: white !important; justify-content: center; }
        .logout-link:hover { background: #dc2626 !important; padding-left: 25px !important; }

        /* Main Content */
        .main { flex: 1; padding: 30px; overflow-y: auto; height: 100%; }
        .topbar { background: #fff; padding: 20px 30px; border-radius: 16px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 20px rgba(0,0,0,0.03); margin-bottom: 30px; flex-shrink: 0; }
        .topbar h2 { margin: 0; font-size: 22px; color: #1e293b; }
        .table-card { background: #fff; padding: 30px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); overflow-x: auto; }

        /* Table Styling */
        .table th { background-color: #f9fafb; color: #6b7280; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; padding: 1rem; border-bottom: 1px solid #e5e7eb; }
        .table td { padding: 1rem; vertical-align: middle; color: #374151; font-size: 0.875rem; border-bottom: 1px solid #f3f4f6; }

        /* Row Highlight */
        .row-mine { background-color: #f0fdf4 !important; }
        .row-mine td { border-bottom-color: #dcfce7; }

        /* Status Dropdown Styling */
        .status-dropdown .dropdown-toggle {
            border: none;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.4em 1em;
            border-radius: 0.375rem;
            text-transform: capitalize;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .status-dropdown .dropdown-toggle::after { margin-left: 0.5em; }

        .status-pending { background-color: #fff7ed; color: #c2410c; }
        .status-confirmed { background-color: #e0f2fe; color: #0369a1; }
        .status-processing { background-color: #eff6ff; color: #1d4ed8; }
        .status-shipped { background-color: #f0fdf4; color: #15803d; }
        .status-delivered { background-color: #dcfce7; color: #166534; }
        .status-cancelled { background-color: #fef2f2; color: #b91c1c; }

        .dropdown-item { font-size: 0.85rem; padding: 0.5rem 1rem; cursor: pointer; }
        .dropdown-item:hover { background-color: #f3f4f6; }

        /* Buttons */
        .btn-icon { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; border: none; transition: 0.2s; }
        .btn-view { background-color: #e0f2fe; color: #0284c7; }
        .btn-delete { background-color: #fee2e2; color: #dc2626; }
    </style>
</head>
<body>

<div class="layout">
    <!-- Sidebar -->
    <?php include __DIR__ . '/../includes/vendor_sidebar.php'; ?>

    <div class="main">
        <div class="topbar">
            <h2><i class="fa-solid fa-box"></i> Customer Orders</h2>
            <span><?= date("l, F j, Y") ?></span>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-card">
            <div class="table-responsive">
                <table class="table w-100 mb-0">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Vendor ID</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th style="min-width: 140px;">Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders && mysqli_num_rows($orders) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($orders)): ?>
                                <?php
                                    $is_mine = ($row['product_vendor_id'] == $vendor_id);
                                    $row_class = $is_mine ? 'row-mine' : '';
                                    $status = strtolower($row['status'] ?? 'pending');

                                    // Determine status color class
                                    $status_class = 'status-pending'; // default
                                    if ($status == 'confirmed') $status_class = 'status-confirmed';
                                    if ($status == 'processing') $status_class = 'status-processing';
                                    if ($status == 'shipped') $status_class = 'status-shipped';
                                    if ($status == 'delivered') $status_class = 'status-delivered';
                                    if ($status == 'cancelled') $status_class = 'status-cancelled';
                                ?>
                                <tr class="<?= $row_class ?>">
                                    <td><span class="fw-bold">#<?= $row['order_id'] ?></span></td>
                                    <td>
                                        <?= date('M d, Y', strtotime($row['created_at'])) ?><br>
                                        <small class="text-muted"><?= date('h:i A', strtotime($row['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <span class="fw-medium"><?= htmlspecialchars($row['customer_name']) ?></span><br>
                                        <small class="text-muted"><?= htmlspecialchars($row['customer_email']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                                    <td>
                                        <?= $row['product_vendor_id'] ?>
                                        <?php if($is_mine): ?>
                                            <span class="badge bg-success ms-1">Yours</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $row['quantity'] ?></td>
                                    <td class="fw-bold text-success">₹<?= number_format($row['total_line_price'], 2) ?></td>
                                    <td>
                                        <!-- Modern Status Dropdown -->
                                        <div class="dropdown status-dropdown">
                                            <button class="btn dropdown-toggle <?= $status_class ?>" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <?= ucfirst($status) ?>
                                            </button>
                                            <ul class="dropdown-menu shadow border-0">
                                                <li><a class="dropdown-item" onclick="updateStatus(<?= $row['order_id'] ?>, 'pending')"><i class="fa-solid fa-clock text-warning me-2"></i> Pending</a></li>
                                                <li><a class="dropdown-item" onclick="updateStatus(<?= $row['order_id'] ?>, 'confirmed')"><i class="fa-solid fa-check text-info me-2"></i> Confirmed</a></li>
                                                <li><a class="dropdown-item" onclick="updateStatus(<?= $row['order_id'] ?>, 'processing')"><i class="fa-solid fa-spinner text-primary me-2"></i> Processing</a></li>
                                                <li><a class="dropdown-item" onclick="updateStatus(<?= $row['order_id'] ?>, 'shipped')"><i class="fa-solid fa-truck text-success me-2"></i> Shipped</a></li>
                                                <li><a class="dropdown-item" onclick="updateStatus(<?= $row['order_id'] ?>, 'delivered')"><i class="fa-solid fa-box-open text-success me-2"></i> Delivered</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item" onclick="updateStatus(<?= $row['order_id'] ?>, 'cancelled')"><i class="fa-solid fa-xmark text-danger me-2"></i> Cancelled</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <!-- View Button (Triggers Modal) -->
                                        <button type="button" class="btn-icon btn-view me-1" onclick="viewOrder(<?= $row['order_id'] ?>)" title="View Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>

                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this order?');">
                                            <input type="hidden" name="action" value="delete_order">
                                            <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                                            <button type="submit" class="btn-icon btn-delete" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="9" class="text-center py-5 text-muted">No orders found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- View Order Modal -->
<div class="modal fade" id="viewOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-receipt me-2 text-primary"></i> Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewOrderContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Status Update Function
    function updateStatus(orderId, newStatus) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'update_status';
        form.appendChild(actionInput);

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'order_id';
        idInput.value = orderId;
        form.appendChild(idInput);

        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = newStatus;
        form.appendChild(statusInput);

        document.body.appendChild(form);
        form.submit();
    }

    // View Order Function (AJAX to same file)
    function viewOrder(orderId) {
        const modal = new bootstrap.Modal(document.getElementById('viewOrderModal'));
        const contentDiv = document.getElementById('viewOrderContent');

        // Show modal with loading spinner
        modal.show();
        contentDiv.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>';

        // Fetch details from this same file
        fetch('orders.php?action=view_details&id=' + orderId)
            .then(response => response.text())
            .then(html => {
                contentDiv.innerHTML = html;
            })
            .catch(error => {
                contentDiv.innerHTML = '<div class="alert alert-danger">Failed to load order details.</div>';
            });
    }
</script>
</body>
</html>
