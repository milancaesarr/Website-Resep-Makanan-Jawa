<?php
// Debug file untuk melihat response admin-recipes.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "=== DEBUG MODE ===\n";
    echo "POST Data:\n";
    print_r($_POST);
    echo "\nFILES Data:\n";
    print_r($_FILES);
    echo "\n=== END DEBUG ===\n";
}

// Test AJAX request ke admin-recipes.php
if (isset($_GET['test'])) {
    echo "<h2>Testing AJAX Request</h2>";

    // Simulasi AJAX request
    $postData = [
        'ajax_recipe_action' => '1',
        'action' => 'add',
        'name' => 'Test Resep',
        'description' => 'Test Deskripsi',
        'region' => 'Jawa Barat',
        'prep_time' => '30 mins',
        'cook_time' => '1 hour',
        'difficulty' => 'Easy',
        'rating' => '4.5',
        'votes' => '100',
        'author' => 'Test Author',
        'cooking_method' => 'Traditional',
        'cuisine' => 'Indonesian',
        'courses' => 'Main Course',
        'ingredients_main' => 'Test Ingredients',
        'ingredients_seasoning' => 'Test Seasoning',
        'ingredients_complementary' => 'Test Complementary',
        'instructions' => 'Test Instructions'
    ];

    // Start output buffering to capture any unwanted output
    ob_start();

    // Include admin-recipes.php dengan POST data
    $_POST = $postData;
    $_SERVER['REQUEST_METHOD'] = 'POST';

    try {
        include 'admin-recipes.php';
        $output = ob_get_contents();
    } catch (Exception $e) {
        $output = "Error: " . $e->getMessage();
    }

    ob_end_clean();

    echo "<h3>Raw Output:</h3>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";

    echo "<h3>Output Analysis:</h3>";
    if (strpos($output, '{') === 0) {
        echo "✅ Valid JSON response<br>";
        $json = json_decode($output, true);
        if ($json) {
            echo "JSON Data: ";
            print_r($json);
        }
    } else {
        echo "❌ Invalid JSON - contains extra output<br>";
        echo "First 200 characters: " . htmlspecialchars(substr($output, 0, 200));
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Debug Recipes Upload</title>
</head>
<body>
    <h1>Debug Recipes Upload</h1>

    <p><a href="?test=1">Test AJAX Request</a></p>

    <h2>Manual AJAX Test</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="ajax_recipe_action" value="1">
        <input type="hidden" name="action" value="add">

        <table>
            <tr><td>Name:</td><td><input type="text" name="name" value="Test Recipe" required></td></tr>
            <tr><td>Description:</td><td><textarea name="description" required>Test Description</textarea></td></tr>
            <tr><td>Region:</td><td><select name="region" required>
                <option value="Jawa Barat">Jawa Barat</option>
            </select></td></tr>
            <tr><td>Prep Time:</td><td><input type="text" name="prep_time" value="30 mins"></td></tr>
            <tr><td>Cook Time:</td><td><input type="text" name="cook_time" value="1 hour"></td></tr>
            <tr><td>Difficulty:</td><td><select name="difficulty">
                <option value="Easy">Easy</option>
            </select></td></tr>
            <tr><td>Rating:</td><td><input type="number" name="rating" value="4.5" step="0.1"></td></tr>
            <tr><td>Votes:</td><td><input type="number" name="votes" value="100"></td></tr>
            <tr><td>Author:</td><td><input type="text" name="author" value="Test Author"></td></tr>
            <tr><td>Cooking Method:</td><td><input type="text" name="cooking_method" value="Traditional"></td></tr>
            <tr><td>Cuisine:</td><td><input type="text" name="cuisine" value="Indonesian"></td></tr>
            <tr><td>Courses:</td><td><input type="text" name="courses" value="Main Course"></td></tr>
            <tr><td>Ingredients Main:</td><td><textarea name="ingredients_main">Test Main</textarea></td></tr>
            <tr><td>Ingredients Seasoning:</td><td><textarea name="ingredients_seasoning">Test Seasoning</textarea></td></tr>
            <tr><td>Ingredients Complementary:</td><td><textarea name="ingredients_complementary">Test Complementary</textarea></td></tr>
            <tr><td>Instructions:</td><td><textarea name="instructions" required>Test Instructions</textarea></td></tr>
            <tr><td>Image:</td><td><input type="file" name="recipe_image" accept="image/*"></td></tr>
        </table>

        <br>
        <button type="submit">Test Submit</button>
    </form>
</body>
</html>
