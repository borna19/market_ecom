<?php
// Beginner-friendly registration page where a user can choose their role.
session_start();
include __DIR__ . '/../includes/e_db.php';

if (isset($_POST['register'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    // Accept role values used elsewhere in the app: 'farmer', 'customer', 'admin'
    $role = strtolower(trim($_POST['role'] ?? 'customer'));

    // Simple validation
    if ($name === '' || $email === '' || $password === '' || $role === '') {
        $_SESSION['error'] = 'All fields (including role) are required!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email address!';
    } elseif (strlen($password) < 6) {
        $_SESSION['error'] = 'Password must be at least 6 characters.';
    } else {
        // Check if email exists (safe prepared statement)
        $stmt = mysqli_prepare($conn, 'SELECT id FROM users WHERE email = ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $_SESSION['error'] = 'Email already registered!';
        } else {
            // Insert new user with hashed password
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = mysqli_prepare($conn, 'INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
            mysqli_stmt_bind_param($ins, 'ssss', $name, $email, $hash, $role);
            if (mysqli_stmt_execute($ins)) {
                $_SESSION['success'] = 'Registration successful! You can now login.';
                header('Location: /market_ecom/pages/login.php');
                exit;
            } else {
                $_SESSION['error'] = 'Registration failed. Please try again.';
            }
            mysqli_stmt_close($ins);
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <style>label{display:block;margin-top:8px;}input,select{width:100%;padding:8px;margin-top:4px}</style>
</head>
<body style="max-width:520px;margin:40px auto;font-family:Arial, sans-serif;">
    <h2>Register</h2>
    <?php
    if (!empty($_SESSION['error'])) { echo '<div style="color:#a00;margin-bottom:12px;">'.htmlspecialchars($_SESSION['error']).'</div>'; unset($_SESSION['error']); }
    if (!empty($_SESSION['success'])) { echo '<div style="color:green;margin-bottom:12px;">'.htmlspecialchars($_SESSION['success']).'</div>'; unset($_SESSION['success']); }
    ?>
    <form method="post">
        <label>Full name
            <input type="text" name="name" required>
        </label>

        <label>Email
            <input type="email" name="email" required>
        </label>

        <label>Password (min 6 chars)
            <input type="password" name="password" required>
        </label>

        <label>Role
            <select name="role" required>
                <option value="customer">Customer</option>
                <option value="farmer">Vendor (Farmer)</option>
                <option value="admin">Admin</option>
            </select>
        </label>

        <button type="submit" name="register" style="margin-top:12px;padding:10px 16px;">Register</button>
    </form>
    <p style="margin-top:12px">Already have an account? <a href="login.php">Login here</a></p>
</body>
</html>
