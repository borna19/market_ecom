<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require "../e_db.php";

if (isset($_POST['login'])) {

    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $role     = $_POST['role'];

    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            // Role check
            if ($role !== $user['role']) {
                $_SESSION['error'] = "Wrong role selected.";
                header("Location: ../../index.php");
                exit;
            }

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];

            // Redirect by role
            if ($user['role'] === 'admin') {
                header("Location: ../../admin/admin_dashboard.php");
            } elseif ($user['role'] === 'vendor') {
                header("Location: ../../vendor/dashboard.php");
            } else {
                header("Location: ../../customer/customer_dashboard.php");
            }
            exit;

        } else {
            $_SESSION['error'] = "Wrong password.";
            header("Location: ../../index.php");
            exit;
        }

    } else {
        $_SESSION['error'] = "Email not found.";
        header("Location: ../../index.php");
        exit;
    }
};