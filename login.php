<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body { font-family: Arial; background:#f5f5f5; }
        .box { width:350px; margin:80px auto; padding:20px; background:#fff; border-radius:5px; }
        input, button { width:100%; padding:10px; margin:8px 0; }
        button { background:#007bff; color:#fff; border:none; cursor:pointer; }
        a { text-decoration:none; }
    </style>
</head>
<body>

<div class="box">
    <h2>Login</h2>

    <?php
    if (isset($_SESSION['error'])) {
        echo "<p style='color:red'>".$_SESSION['error']."</p>";
        unset($_SESSION['error']);
    }
    ?>

<form method="POST" action="includes/auth/login_action.php">
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit" name="login">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register</a></p>
</div>

</body>
</html>
