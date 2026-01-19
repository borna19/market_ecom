<?php
session_start();
include "../includes/db.php";

if (isset($_POST['login'])) {

    $email = $_POST['email'];
    $pass  = $_POST['password'];
    $role  = $_POST['role'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND role=?");
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {

        if (password_verify($pass, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];

            if ($role === 'admin') {
                header("Location: ../admin/dashboard.php");
            } elseif ($role === 'vendor') {
                header("Location: ../vendor/dashboard.php");
            } else {
                header("Location: ../customer/dashboard.php");
            }
            exit;
        }
    }

    header("Location: ../index.php?error=1");
}
