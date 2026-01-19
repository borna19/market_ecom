<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <style>
        body { font-family: Arial; background:#f5f5f5; }
        .box { width:350px; margin:80px auto; padding:20px; background:#fff; border-radius:5px; }
        input, button { width:100%; padding:10px; margin:8px 0; }
        button { background:#28a745; color:#fff; border:none; cursor:pointer; }
        a { text-decoration:none; }
    </style>
</head>
<body>

<div class="box">
    <h2>Register</h2>

    <?php
    if (isset($_SESSION['error'])) {
        echo "<p style='color:red'>".$_SESSION['error']."</p>";
        unset($_SESSION['error']);
    }
    ?>

<form method="POST" action="/MARKET_ECOM/includes/auth/register_action.php">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>

        <!-- default role -->
        <input type="hidden" name="role" value="customer">

        <button type="submit" name="register">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login</a></p>
</div>

</body>
</html>
