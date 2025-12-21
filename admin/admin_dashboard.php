<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>

    <style>
        body {
            margin: 0;
            font-family: Arial;
            background: #f1f1f1;
        }

        /* CONTENT AREA */
        .content {
            margin-left: 250px; /* same width as sidebar */
            padding: 30px;
        }

        .content h1 {
            font-size: 30px;
            margin-bottom: 15px;
            color: #222;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: .3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }

        .card h3 {
            margin: 0;
            color: #333;
            font-size: 22px;
        }

        .card p {
            color: #666;
            margin-top: 10px;
        }
    </style>

</head>
<body>

<?php include 'panel_home.php'; ?>

<div class="content">

    <h1>Admin Dashboard</h1>

    <div class="cards">
        <div class="card">
            <h3>Total Users</h3>
            <p>124</p>
        </div>

        <div class="card">
            <h3>Total Vendors</h3>
            <p>42</p>
        </div>

        <div class="card">
            <h3>Total Orders</h3>
            <p>987</p>
        </div>

        <div class="card">
            <h3>Total Products</h3>
            <p>350</p>
        </div>

        <div class="card">
            <h3>Pending Orders</h3>
            <p>12</p>
        </div>

        <div class="card">
            <h3>Revenue</h3>
            <p>$12,540</p>
        </div>
    </div>

</div>

</body>
</html>
