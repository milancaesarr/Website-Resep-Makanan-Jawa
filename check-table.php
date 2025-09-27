<?php
require_once "db.php";

echo "<h2>Checking recipes table structure...</h2>";

// Check if table exists and show structure
$sql = "DESCRIBE recipes";
$result = mysqli_query($conn, $sql);

if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error checking table: " . mysqli_error($conn);
}

// Check for any data
$sql = "SELECT COUNT(*) as count FROM recipes";
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "<p>Total recipes in table: " . $row['count'] . "</p>";
}

mysqli_close($conn);
?>
