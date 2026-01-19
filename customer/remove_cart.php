<?php
session_start();
$id = (int)$_GET['id'];
unset($_SESSION['cart'][$id]);
header("Location: cart.php");
exit;
