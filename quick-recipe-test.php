<?php
require_once "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload_dir = 'uploads/recipes/';

    // Create directory if needed
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $image_url = '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $new_filename = 'recipe_' . uniqid() . '.' . $file_ext;
        $upload_path = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $image_url = $upload_path;
            echo "<p style='color: green;'>✅ Image uploaded successfully: $upload_path</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to upload image</p>";
        }
    }

    // Insert recipe
    $name = $_POST['name'] ?? 'Test Recipe';
    $description = $_POST['description'] ?? 'Test description';
    $region = $_POST['region'] ?? 'Jawa Barat';

    $sql = "INSERT INTO recipes (name, description, region, image_url, rating, votes, difficulty) VALUES (?, ?, ?, ?, 4.5, 100, 'Easy')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $name, $description, $region, $image_url);

    if (mysqli_stmt_execute($stmt)) {
        $recipe_id = mysqli_insert_id($conn);
        echo "<p style='color: green;'>✅ Recipe added successfully with ID: $recipe_id</p>";
        echo "<p>Image URL in database: $image_url</p>";

        if (!empty($image_url) && file_exists($image_url)) {
            echo "<p>✅ Image file exists on disk</p>";
            echo "<img src='$image_url' style='max-width: 200px; border: 1px solid #ccc;'>";
        } else if (!empty($image_url)) {
            echo "<p style='color: red;'>❌ Image file does not exist on disk</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Failed to add recipe: " . mysqli_error($conn) . "</p>";
    }

    mysqli_stmt_close($stmt);

    echo "<hr><a href='admin-recipes.php'>Go to Admin Panel</a><br>";
    echo "<a href='quick-recipe-test.php'>Add Another Recipe</a>";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quick Recipe Test</title>
</head>
<body>
    <h2>Quick Recipe Test</h2>
    <form method="post" enctype="multipart/form-data">
        <p>
            <label>Recipe Name:</label><br>
            <input type="text" name="name" value="Test Recipe <?php echo time(); ?>" required>
        </p>

        <p>
            <label>Description:</label><br>
            <textarea name="description" required>A delicious test recipe created at <?php echo date('Y-m-d H:i:s'); ?></textarea>
        </p>

        <p>
            <label>Region:</label><br>
            <select name="region" required>
                <option value="Jawa Barat">Jawa Barat</option>
                <option value="Jawa Tengah">Jawa Tengah</option>
                <option value="Jawa Timur">Jawa Timur</option>
            </select>
        </p>

        <p>
            <label>Image:</label><br>
            <input type="file" name="image" accept="image/*">
        </p>

        <button type="submit">Add Test Recipe</button>
    </form>

    <h3>Current Upload Status:</h3>
    <?php
    $upload_dir = 'uploads/recipes/';
    echo "Directory exists: " . (is_dir($upload_dir) ? 'YES' : 'NO') . "<br>";
    echo "Directory writable: " . (is_writable($upload_dir) ? 'YES' : 'NO') . "<br>";
    ?>
</body>
</html>
