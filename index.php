<?php
session_start();
include __DIR__ . '/includes/e_db.php';

// ---------------- REGISTER ----------------
if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    if (!$stmt) {
        $_SESSION['message'] = 'Database error: ' . $conn->error;
        $_SESSION['tab'] = 'register';
    } else {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
        $_SESSION['message'] = "Email already registered!";
        $_SESSION['tab'] = 'register';
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $insert = $conn->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
        if (!$insert) {
            $_SESSION['message'] = 'Database error: ' . $conn->error;
            $_SESSION['tab'] = 'register';
        } else {
            $insert->bind_param("sssss", $name, $email, $phone, $hashed, $role);
            if ($insert->execute()) {
            $_SESSION['message'] = "Registration successful! You can now login.";
            $_SESSION['tab'] = 'login';
        } else {
            $_SESSION['message'] = "Registration failed. Try again.";
            $_SESSION['tab'] = 'register';
        }
            $insert->close();
        }
    }
}
}

// ---------------- LOGIN ----------------
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    if (!$stmt) {
        $_SESSION['message'] = 'Database error: ' . $conn->error;
        $_SESSION['tab'] = 'login';
        // stop processing so page can show the error
    } else {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
    }

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Normalize the role and store in session
            $role = strtolower(trim($row['role'] ?? ''));
            // Prevent session fixation by regenerating the session id on login
            session_regenerate_id(true);
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['role'] = $role;

            // Redirect all users to the single dashboard page
            header("Location: /market_ecom/pages/dashboard.php");
            exit;
        } else {
            $_SESSION['message'] = "Incorrect password!";
            $_SESSION['tab'] = 'login';
        }
    } else {
        $_SESSION['message'] = "No account found!";
        $_SESSION['tab'] = 'login';
    }
    if (isset($stmt) && $stmt) {
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
    <div class="card shadow p-4" style="width: 420px; border-radius: 15px;">
        <h3 class="text-center mb-4">Welcome</h3>

        <!-- Show Message -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info text-center">
                <?= $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" id="tabMenu">
            <li class="nav-item">
                <a class="nav-link <?= (!isset($_SESSION['tab']) || $_SESSION['tab'] == 'login') ? 'active' : '' ?>" data-bs-toggle="tab" href="#loginTab">Login</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= (isset($_SESSION['tab']) && $_SESSION['tab'] == 'register') ? 'active' : '' ?>" data-bs-toggle="tab" href="#registerTab">Register</a>
            </li>
        </ul>

        <div class="tab-content">

            <!-- LOGIN FORM -->
            <div class="tab-pane fade <?= (!isset($_SESSION['tab']) || $_SESSION['tab'] == 'login') ? 'show active' : '' ?>" id="loginTab">
                <form method="POST">
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                </form>
            </div>

            <!-- REGISTER FORM -->
            <div class="tab-pane fade <?= (isset($_SESSION['tab']) && $_SESSION['tab'] == 'register') ? 'show active' : '' ?>" id="registerTab">
                <form method="POST">
                    <div class="mb-3">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Select Role</label>
                        <select name="role" class="form-control" required>
                            <option value="customer">Customer</option>
                            <option value="vendor">Vendor</option>
                        </select>
                    </div>
                    <button type="submit" name="register" class="btn btn-success w-100">Register</button>
                </form>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Keep the tab active after submission
var triggerTabList = [].slice.call(document.querySelectorAll('#tabMenu a'))
triggerTabList.forEach(function (triggerEl) {
  var tabTrigger = new bootstrap.Tab(triggerEl)
  if(triggerEl.classList.contains('active')){
      tabTrigger.show()
  }
})
</script>

</body>
</html>
