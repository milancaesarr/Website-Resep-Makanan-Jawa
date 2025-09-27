<?php
// Database setup script for NusaJava Eats
// Run this file once to ensure all required columns exist

require_once "db.php";

echo "<h2>NusaJava Eats - Database Setup</h2>";

// Check and add missing columns to users table
echo "<p>Checking users table...</p>";

// Check if last_login column exists
$check_last_login = "SHOW COLUMNS FROM users LIKE 'last_login'";
$result = mysqli_query($conn, $check_last_login);

if (mysqli_num_rows($result) == 0) {
    echo "<p>Adding last_login column...</p>";
    $add_last_login = "ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL AFTER created_at";
    if (mysqli_query($conn, $add_last_login)) {
        echo "<p style='color: green;'>✓ last_login column added successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding last_login column: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ last_login column already exists</p>";
}

// Check if login_count column exists
$check_login_count = "SHOW COLUMNS FROM users LIKE 'login_count'";
$result = mysqli_query($conn, $check_login_count);

if (mysqli_num_rows($result) == 0) {
    echo "<p>Adding login_count column...</p>";
    $add_login_count = "ALTER TABLE users ADD COLUMN login_count INT DEFAULT 0 AFTER last_login";
    if (mysqli_query($conn, $add_login_count)) {
        echo "<p style='color: green;'>✓ login_count column added successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding login_count column: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ login_count column already exists</p>";
}

// Update existing users to have default login_count
echo "<p>Setting default login_count for existing users...</p>";
$update_defaults = "UPDATE users SET login_count = COALESCE(login_count, 0) WHERE login_count IS NULL";
if (mysqli_query($conn, $update_defaults)) {
    echo "<p style='color: green;'>✓ Default login_count values set</p>";
} else {
    echo "<p style='color: red;'>✗ Error setting default values: " . mysqli_error($conn) . "</p>";
}

// Check recipes table
echo "<p>Checking recipes table...</p>";
$check_recipes = "SHOW TABLES LIKE 'recipes'";
$result = mysqli_query($conn, $check_recipes);

if (mysqli_num_rows($result) == 0) {
    echo "<p style='color: orange;'>⚠ Recipes table doesn't exist. Please run create_recipes_table.sql</p>";
} else {
    echo "<p style='color: green;'>✓ Recipes table exists</p>";
}

// Check products table
echo "<p>Checking products table...</p>";
$check_products = "SHOW TABLES LIKE 'products'";
$result = mysqli_query($conn, $check_products);

if (mysqli_num_rows($result) == 0) {
    echo "<p style='color: orange;'>⚠ Products table doesn't exist. Please ensure products table is created</p>";
} else {
    echo "<p style='color: green;'>✓ Products table exists</p>";
}

echo "<h3 style='color: green;'>Database setup completed!</h3>";
echo "<p><a href='home-login.php'>← Go back to Home</a></p>";

mysqli_close($conn);
?>
