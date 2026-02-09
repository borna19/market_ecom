<?php
include 'includes/e_db.php';
$result = $conn->query("DESCRIBE orders");
while($row = $result->fetch_assoc()) {
    print_r($row);
}
?>