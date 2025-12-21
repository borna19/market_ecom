<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "fmecom";  // ✔ Correct database variable

$conn = mysqli_connect($host, $user, $pass, $dbname); // ✔ Using $dbname

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8"); // ✔ Correct charset
?>
