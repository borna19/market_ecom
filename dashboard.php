<?php
session_start();
include "../includes/e_db.php";

// DEBUG: temporary marker to verify that this file is being served
ini_set('display_errors',1);
error_reporting(E_ALL);
echo "<!-- DASHBOARD_MARKER: v2 -->\n";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

/* COUNTS */
$totalUsers     = $conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'];
$totalProducts  = $conn->query("SELECT COUNT(*) c FROM products")->fetch_assoc()['c'];
$totalOrders    = $conn->query("SELECT COUNT(*) c FROM orders")->fetch_assoc()['c'];
$pendingOrders  = $conn->query("SELECT COUNT(*) c FROM orders WHERE status='pending'")->fetch_assoc()['c'];
$processingOrders = $conn->query("SELECT COUNT(*) c FROM orders WHERE status='processing'")->fetch_assoc()['c'];
$completedOrders = $conn->query("SELECT COUNT(*) c FROM orders WHERE status IN ('delivered','completed')")->fetch_assoc()['c'];
$cancelledOrders = $conn->query("SELECT COUNT(*) c FROM orders WHERE status='cancelled'")->fetch_assoc()['c'];

/* Today's summary */
$ordersToday = $conn->query("SELECT COUNT(*) c FROM orders WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['c'];
$newUsersToday = $conn->query("SELECT COUNT(*) c FROM users WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['c'];
$revenueToday = $conn->query("SELECT IFNULL(SUM(total_amount),0) c FROM orders WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['c'];

/* Recent orders (latest 5) */
$recentOrders = $conn->query("SELECT o.id, u.name, o.total_amount, o.status, o.created_at FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.id DESC LIMIT 5");

/* New users (latest 10) */
$newUsers = $conn->query("SELECT name,email,created_at FROM users ORDER BY id DESC LIMIT 10");

/* Low stock products */
$lowStock = $conn->query("SELECT product_name,stock FROM products WHERE stock<=5 ORDER BY stock ASC LIMIT 10");

/* Notifications */
$notifications = [];
$no = $conn->query("SELECT id, total_amount, status, created_at FROM orders ORDER BY id DESC LIMIT 5");
while($row = $no->fetch_assoc()) { $notifications[] = ['type'=>'order','text'=>"New order #{$row['id']} - ₹{$row['total_amount']}", 'date'=>$row['created_at']]; }
$ls = $conn->query("SELECT product_name,stock FROM products WHERE stock<=5 ORDER BY stock ASC LIMIT 5");
while($p = $ls->fetch_assoc()) { $notifications[] = ['type'=>'stock','text'=>"Low stock: {$p['product_name']} ({$p['stock']})", 'date'=>null]; }
$nu = $conn->query("SELECT name,created_at FROM users ORDER BY id DESC LIMIT 5");
while($u = $nu->fetch_assoc()) { $notifications[] = ['type'=>'user','text'=>"New user: {$u['name']}", 'date'=>$u['created_at']]; }

/* Orders per month (last 6 months) */
$ordersPerMonth = $conn->query("SELECT DATE_FORMAT(created_at, '%Y-%m') ym, COUNT(*) cnt FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY ym ORDER BY ym ASC");
$ordersLabels = $ordersData = [];
while($r = $ordersPerMonth->fetch_assoc()) { $ordersLabels[] = $r['ym']; $ordersData[] = (int)$r['cnt']; }

/* Order status pie */
$statusCounts = $conn->query("SELECT status, COUNT(*) cnt FROM orders GROUP BY status");
$statusLabels = $statusData = [];
while($s = $statusCounts->fetch_assoc()) { $statusLabels[] = $s['status']; $statusData[] = (int)$s['cnt']; }

/* Top selling products */
$topProductsQ = $conn->query("SELECT p.product_name, SUM(oi.quantity) sold FROM order_items oi JOIN products p ON oi.product_id=p.id GROUP BY oi.product_id ORDER BY sold DESC LIMIT 5");
$topProducts = [];
while($t = $topProductsQ->fetch_assoc()) { $topProducts[] = $t; }

/* Revenue this vs last month */
$revThis = $conn->query("SELECT IFNULL(SUM(total_amount),0) c FROM orders WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())")->fetch_assoc()['c'];
$revLast = $conn->query("SELECT IFNULL(SUM(total_amount),0) c FROM orders WHERE MONTH(created_at)=MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(created_at)=YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))")->fetch_assoc()['c'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>

<style>
body{
    margin:0;
    font-family:Arial;
    background:#f1f1f1;
}
.content{
    margin-left:250px;
    padding:30px;
}
.layout{display:flex;gap:25px}
.main{flex:1}
.sidebar{width:340px}
h1{margin-bottom:10px;}
h3,h4{margin-top:40px;}

.cards{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:25px;
}
.stat-card, .card{
    background:#fff;
    padding:25px;
    border-radius:10px;
    box-shadow:0 3px 10px rgba(0,0,0,.1);
}
.stat-card h3, .card h3{margin:0;}
.stat-card p, .card p{font-size:22px;margin-top:10px}

/* STATUS CARDS */
.status-cards{display:grid;grid-template-columns:repeat(4,1fr);gap:15px;margin-top:20px}
.status-card{padding:12px;border-radius:8px;color:#fff;text-align:center}
.status-pending{background:#ffc107;color:#000}
.status-processing{background:#0d6efd}
.status-completed{background:#198754}
.status-cancelled{background:#dc3545}

/* QUICK ACTIONS */
.actions a{display:inline-block;padding:10px 18px;background:#198754;color:#fff;text-decoration:none;border-radius:6px;margin-right:10px}
.actions a.alt{background:#0d6efd}
.actions a.warn{background:#ffc107;color:#000}
.actions a:hover{opacity:0.9}

/* TABLE */
table{width:100%;border-collapse:collapse;background:#fff}
table th, table td{padding:12px;border:1px solid #ddd}
table th{background:#f5f5f5}
.badge{padding:5px 10px;border-radius:5px;color:#fff;font-size:13px}
.pending{background:#ffc107;color:#000}
.processing{background:#0d6efd}
.shipped{background:#0dcaf0;color:#000}
.completed{background:#198754}
.cancelled{background:#dc3545}
.badge.low{background:#ffc107;color:#000}

/* SIDEBAR WIDGETS */
.widget{background:#fff;padding:16px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:20px}
.widget h4{margin-top:0}

.actions-link{color:#0d6efd;text-decoration:none;font-weight:600}
.actions-link:hover{text-decoration:underline}
</style>
</head>

<body>

<?php include "panel_home.php"; ?>

<div class="content">

<h1>Admin Dashboard</h1>

<!-- STAT CARDS -->
<div class="status-section">
    <div class="cards">
        <div class="stat-card"><h3>Total Users</h3><p><?= $totalUsers ?></p></div>
        <div class="stat-card"><h3>Total Products</h3><p><?= $totalProducts ?></p></div>
        <div class="stat-card"><h3>Total Orders</h3><p><?= $totalOrders ?></p></div>
    </div>

    <!-- ORDER STATUS MINI CARDS -->
    <div class="status-cards">
        <div class="status-card status-pending"><h4>Pending</h4><p><?= $pendingOrders ?></p></div>
        <div class="status-card status-processing"><h4>Processing</h4><p><?= $processingOrders ?></p></div>
        <div class="status-card status-completed"><h4>Completed</h4><p><?= $completedOrders ?></p></div>
        <div class="status-card status-cancelled"><h4>Cancelled</h4><p><?= $cancelledOrders ?></p></div>
    </div>
</div>

<!-- QUICK ACTIONS -->
<h3>Quick Actions</h3>
<div class="actions">
    <a href="add_product.php">➕ Add Product</a>
    <a href="add_category.php" class="alt">➕ Add Category</a>
    <a href="create_admin.php" class="warn">👤 Add User</a>
    <a href="manage_products.php">🛒 Manage Products</a>
    <a href="manage_orders.php">📦 View All Orders</a>
    <a href="manage_users.php">👥 Manage Users</a>
</div>

<div class="layout">
  <div class="main">

<!-- RECENT ORDERS (fetched above) -->
<h3>Recent Orders</h3>
<table>
<tr>
    <th>ID</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th><th>Action</th>
</tr>
<?php while($r = $recentOrders->fetch_assoc()): ?>
<tr>
    <td>#<?= $r['id'] ?></td>
    <td><?= htmlspecialchars($r['name']) ?></td>
    <td>₹<?= number_format($r['total_amount'],2) ?></td>
    <?php $sc = $r['status']; if($sc==='delivered') $sc='completed'; ?>
    <td><span class="badge <?= htmlspecialchars($sc) ?>"><?= htmlspecialchars(ucfirst($r['status'])) ?></span></td>
    <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
    <td><a href="order_view.php?id=<?= $r['id'] ?>" class="actions-link">View</a></td>
</tr>
<?php endwhile; ?>
</table>

<!-- NEW USERS (latest 10) -->
<h3>New Users</h3>
<table>
<tr><th>Name</th><th>Email</th><th>Joined</th></tr>
<?php while($u = $newUsers->fetch_assoc()): ?>
<tr>
    <td><?= $u['name'] ?></td>
    <td><?= $u['email'] ?></td>
    <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
</tr>
<?php endwhile; ?>
</table>

<!-- CHARTS & ANALYTICS -->
<h3>Charts & Analytics</h3>
<div class="cards">
    <div class="card"><h4>Orders per Month</h4><canvas id="ordersChart" height="140"></canvas></div>
    <div class="card"><h4>Order Status</h4><canvas id="statusChart" height="140"></canvas></div>
    <div class="card"><h4>Top Selling Products</h4><canvas id="topProductsChart" height="140"></canvas></div>
    <div class="card"><h4>Revenue (This vs Last Month)</h4><canvas id="revenueChart" height="140"></canvas></div>
</div>

<!-- LOW STOCK (fetched above) -->
<h3 style="color:red">Low Stock Products</h3>
<?php if($lowStock->num_rows>0): ?>
<table>
<tr><th>Product</th><th>Stock</th></tr>
<?php while($p=$lowStock->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($p['product_name']) ?> <?php if($p['stock']<=5): ?><span class="badge low">Low</span><?php endif; ?></td>
    <td><?= $p['stock'] ?></td>
</tr>
<?php endwhile; ?>
</table>
<?php else: ?>
<p>All products have sufficient stock ✅</p>
<?php endif; ?>

  </div> <!-- end main -->

  <div class="sidebar">
    <div class="widget">
      <h4>Today's Summary</h4>
      <p>Orders Today: <strong><?= $ordersToday ?></strong></p>
      <p>New Users Today: <strong><?= $newUsersToday ?></strong></p>
      <p>Revenue Today: <strong>₹<?= number_format($revenueToday,2) ?></strong></p>
    </div>

    <div class="widget">
      <h4>Notifications</h4>
      <?php if(!empty($notifications)): ?>
        <ul style="padding-left:18px;margin:0;">
          <?php foreach($notifications as $n): ?>
            <li style="margin-bottom:8px;">
              <?= htmlspecialchars($n['text']) ?> <?php if(!empty($n['date'])): ?><small style="color:#666"> - <?= date('d M Y', strtotime($n['date'])) ?></small><?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p>No notifications</p>
      <?php endif; ?>
    </div>

    <div class="widget">
      <h4>Admin Profile</h4>
      <p><strong><?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?></strong></p>
      <p>Role: Admin</p>
      <p>Last Login: N/A</p>
      <a href="profile.php" class="actions-link">Edit Profile</a>
    </div>
  </div> <!-- end sidebar -->

</div> <!-- end layout -->

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Data prepared in PHP
const ordersLabels = <?= json_encode($ordersLabels) ?>;
const ordersData = <?= json_encode($ordersData) ?>;
const statusLabels = <?= json_encode($statusLabels) ?>;
const statusData = <?= json_encode($statusData) ?>;
const topProducts = <?= json_encode(array_column($topProducts, 'product_name')) ?>;
const topProductsData = <?= json_encode(array_map('intval', array_column($topProducts, 'sold'))) ?>;
const revThis = <?= json_encode((float)$revThis) ?>;
const revLast = <?= json_encode((float)$revLast) ?>;

// Orders per month
const ctx1 = document.getElementById('ordersChart');
if(ctx1){ new Chart(ctx1, { type:'bar', data:{ labels:ordersLabels, datasets:[{label:'Orders', data:ordersData, backgroundColor:'#1976d2'}] }, options:{responsive:true} }); }

// Status pie
const ctx2 = document.getElementById('statusChart');
if(ctx2){ new Chart(ctx2, { type:'pie', data:{ labels:statusLabels, datasets:[{data:statusData, backgroundColor:['#ffc107','#0d6efd','#198754','#dc3545']}] }, options:{responsive:true} }); }

// Top products
const ctx3 = document.getElementById('topProductsChart');
if(ctx3){ new Chart(ctx3, { type:'bar', data:{ labels: topProducts, datasets:[{label:'Sold', data: topProductsData, backgroundColor:'#0d6efd'}] }, options:{responsive:true} }); }

// Revenue comparison
const ctx4 = document.getElementById('revenueChart');
if(ctx4){ new Chart(ctx4, { type:'bar', data:{ labels:['Last Month','This Month'], datasets:[{label:'Revenue', data:[revLast, revThis], backgroundColor:['#6c757d','#198754']}] }, options:{responsive:true} }); }
</script>

</body>
</html>
