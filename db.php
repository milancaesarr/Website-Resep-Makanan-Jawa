<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nusajava_eats2";


$conn = mysqli_connect($servername, $username, $password, $dbname);


if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Connection failed: " . mysqli_connect_error());
}


mysqli_set_charset($conn, "utf8");


function escape_string($str) {
    global $conn;
    return mysqli_real_escape_string($conn, $str);
}


function run_query($query) {
    global $conn;
    $result = mysqli_query($conn, $query);

    if (!$result) {
        error_log("Database query failed: " . mysqli_error($conn));
        return false;
    }

    return $result;
}


function table_exists($table_name) {
    global $conn;
    $query = "SHOW TABLES LIKE '$table_name'";
    $result = mysqli_query($conn, $query);
    return $result && mysqli_num_rows($result) > 0;
}


$required_tables = ['products', 'recipes', 'users'];
foreach ($required_tables as $table) {
    if (!table_exists($table)) {
        error_log("Required table '$table' does not exist in database");
    }
}
?>
