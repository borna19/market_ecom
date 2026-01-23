<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require "../e_db.php";

if (isset($_POST['register'])) {

    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $role     = $_POST['role'];

    if ($role === 'admin') {
        $role = 'customer'; // security
    }

    if (empty($name) || empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../../index.php");
        exit;
    }

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION['error'] = "Email already registered.";
        header("Location: ../../index.php");
        exit;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $hashed, $role);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Registration successful. Please login.";
    } else {
        $_SESSION['error'] = "Registration failed.";
    }

    header("Location: ../../index.php");
    exit;
}
