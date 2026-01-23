<?php
session_start();
include __DIR__ . '/../includes/e_db.php';

// Only admin allowed
$role = strtolower($_SESSION['role'] ?? '');
if (!isset($_SESSION['user_id']) || $role !== 'admin') {
    $_SESSION['message'] = "Admin access only.";
    header("Location: /market_ecom/index.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    // Prevent deleting self
    if ($delete_id !== (int)$_SESSION['user_id']) {
        mysqli_query($conn, "DELETE FROM users WHERE id = $delete_id");
        $_SESSION['success'] = "User deleted successfully.";
    } else {
        $_SESSION['error'] = "You cannot delete yourself.";
    }
    header("Location: users.php");
    exit;
}

// Fetch Users
$users = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users - Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f8fafc;
        }

        .layout {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: #1e293b;
            color: #fff;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            height: 100%;
        }

        .sidebar-header {
            padding: 25px 20px;
            background: #0f172a;
            text-align: center;
            border-bottom: 1px solid #334155;
            flex-shrink: 0;
        }

        .sidebar-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .sidebar-menu li {
            border-bottom: 1px solid #334155;
            flex-shrink: 0;
        }

        .sidebar-menu li:last-child {
            border-bottom: none;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 15px;
            transition: all 0.3s;
            gap: 12px;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: #3b82f6;
            color: #fff;
            padding-left: 30px;
        }

        .sidebar-menu a i {
            width: 20px;
            text-align: center;
        }

        .logout-link {
            background: #ef4444;
            color: white !important;
            justify-content: center;
        }
        .logout-link:hover {
            background: #dc2626 !important;
            padding-left: 25px !important;
        }

        /* Main */
        .main {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            height: 100%;
        }

        .topbar {
            background: #fff;
            padding: 20px 30px;
            border-radius: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            margin-bottom: 30px;
            flex-shrink: 0;
        }

        .topbar h2 { margin: 0; font-size: 22px; color: #1e293b; }
        .topbar span { color: #64748b; font-weight: 500; }

        .content-card {
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .alert-danger { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            color: #334155;
        }

        th {
            background: #f8fafc;
            font-weight: 600;
            color: #475569;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-admin { background: #e0e7ff; color: #4338ca; }
        .badge-vendor { background: #fef3c7; color: #b45309; }
        .badge-customer { background: #dcfce7; color: #15803d; }

        .btn-danger {
            background: #ef4444;
            color: #fff;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-danger:hover { background: #dc2626; }

    </style>
</head>
<body>

<div class="layout">

    <!-- Sidebar -->
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <!-- Main -->
    <div class="main">

        <div class="topbar">
            <h2><i class="fa-solid fa-users"></i> Manage Users</h2>
            <span><?= date("l, d M Y") ?></span>
        </div>

        <div class="content-card">

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($u = mysqli_fetch_assoc($users)): ?>
                    <tr>
                        <td>#<?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <span class="badge badge-<?= strtolower($u['role']) ?>">
                                <?= ucfirst($u['role']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                <a href="users.php?delete=<?= $u['id'] ?>" class="btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </a>
                            <?php else: ?>
                                <span style="color:#94a3b8; font-size:12px;">(You)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

</body>
</html>
