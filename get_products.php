<?php
include 'db.php';
header('Content-Type: application/json');

$query = "SELECT * FROM products ORDER BY id ASC";
$result = mysqli_query($conn, $query);

$products = array();
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
}

echo json_encode($products);
?>
