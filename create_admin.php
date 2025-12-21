<?php
include __DIR__ . '/includes/e_db.php';
;

// Admin info
$name = "Admin";
$email = "admin@example.com";
$password = password_hash("admin123", PASSWORD_DEFAULT);
$role = "admin";

// Check if admin already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if($stmt->num_rows === 0){
    $insert = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $insert->bind_param("ssss", $name, $email, $password, $role);
    if($insert->execute()){
        echo "Admin user created successfully!";
    } else {
        echo "Failed to create admin!";
    }
} else {
    echo "Admin already exists!";
}
