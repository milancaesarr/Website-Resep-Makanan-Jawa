<?php
// CLEAN VERSION - No HTML output before AJAX
session_start();

// Proteksi akses: hanya untuk admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    if (isset($_POST['ajax_recipe_action'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Akses tidak diizinkan']);
        exit();
    }
    header("Location: index.html");
    exit();
}

// AJAX Request Handler - HARUS DI ATAS SEBELUM HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_recipe_action'])) {
    // Clear any output buffering
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Set JSON header immediately
    header('Content-Type: application/json');

    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "nusajava_eats";

    $conn = mysqli_connect($servername, $username, $password, $dbname);

    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit();
    }

    $action = $_POST['action'] ?? '';
    $response = ['success' => false, 'message' => ''];

    if ($action === 'add' || $action === 'edit') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $region = trim($_POST['region'] ?? '');
        $prep_time = trim($_POST['prep_time'] ?? '');
        $cook_time = trim($_POST['cook_time'] ?? '');
        $difficulty = trim($_POST['difficulty'] ?? 'Easy');
        $rating = floatval($_POST['rating'] ?? 4.5);
        $votes = intval($_POST['votes'] ?? 100);
        $author = trim($_POST['author'] ?? '');
        $cooking_method = trim($_POST['cooking_method'] ?? '');
        $cuisine = trim($_POST['cuisine'] ?? '');
        $courses = trim($_POST['courses'] ?? '');
        $ingredients_main = trim($_POST['ingredients_main'] ?? '');
        $ingredients_seasoning = trim($_POST['ingredients_seasoning'] ?? '');
        $ingredients_complementary = trim($_POST['ingredients_complementary'] ?? '');
        $instructions = trim($_POST['instructions'] ?? '');

        if (empty($name) || empty($description) || empty($region)) {
            $response['message'] = "Nama, deskripsi, dan daerah harus diisi!";
            echo json_encode($response);
            exit();
        }

        // Handle image upload
        $image_url = '';
        $current_image = '';

        if ($action === 'edit') {
            $id = intval($_POST['id']);
            $current_sql = "SELECT image_url FROM recipes WHERE id = ?";
            $current_stmt = mysqli_prepare($conn, $current_sql);
            if ($current_stmt) {
                mysqli_stmt_bind_param($current_stmt, "i", $id);
                mysqli_stmt_execute($current_stmt);
                $current_result = mysqli_stmt_get_result($current_stmt);
                if ($current_row = mysqli_fetch_assoc($current_result)) {
                    $current_image = $current_row['image_url'] ?? '';
                    $image_url = $current_image;
                }
                mysqli_stmt_close($current_stmt);
            }
        }

        // Check if new image is uploaded
        if (isset($_FILES['recipe_image']) && $_FILES['recipe_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/recipes/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_tmp = $_FILES['recipe_image']['tmp_name'];
            $file_name = $_FILES['recipe_image']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($file_ext, $allowed_types)) {
                $new_filename = 'recipe_' . uniqid() . '.' . $file_ext;
                $upload_path = $upload_dir . $new_filename;

                if ($_FILES['recipe_image']['size'] <= 5 * 1024 * 1024) {
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        if ($action === 'edit' && !empty($current_image) && file_exists($current_image)) {
                            unlink($current_image);
                        }
                        $image_url = $upload_path;
                    } else {
                        $response['message'] = "Gagal mengupload gambar!";
                        echo json_encode($response);
                        exit();
                    }
                } else {
                    $response['message'] = "Ukuran file terlalu besar! Maksimal 5MB.";
                    echo json_encode($response);
                    exit();
                }
            } else {
                $response['message'] = "Tipe file tidak diizinkan! Gunakan JPG, PNG, GIF, atau WebP.";
                echo json_encode($response);
                exit();
            }
        }

        // Database operations
        if ($action === 'add') {
            $sql = "INSERT INTO recipes (name, description, region, prep_time, cook_time, difficulty, rating, votes, image_url, author, cooking_method, cuisine, courses, ingredients_main, ingredients_seasoning, ingredients_complementary, instructions) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssssssdissssssss",
                    $name, $description, $region, $prep_time, $cook_time,
                    $difficulty, $rating, $votes, $image_url, $author,
                    $cooking_method, $cuisine, $courses, $ingredients_main,
                    $ingredients_seasoning, $ingredients_complementary, $instructions);

                if (mysqli_stmt_execute($stmt)) {
                    $response['success'] = true;
                    $response['message'] = "Resep berhasil ditambahkan!";
                } else {
                    $response['message'] = "Gagal menambahkan resep: " . mysqli_stmt_error($stmt);
                    if (!empty($image_url) && file_exists($image_url)) {
                        unlink($image_url);
                    }
                }
                mysqli_stmt_close($stmt);
            } else {
                $response['message'] = "Gagal menyiapkan query: " . mysqli_error($conn);
            }
        } elseif ($action === 'edit') {
            $id = intval($_POST['id']);
            $sql = "UPDATE recipes SET name=?, description=?, region=?, prep_time=?, cook_time=?, difficulty=?, rating=?, votes=?, image_url=?, author=?, cooking_method=?, cuisine=?, courses=?, ingredients_main=?, ingredients_seasoning=?, ingredients_complementary=?, instructions=? WHERE id=?";
            $stmt = mysqli_prepare($conn, $sql);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssssssdissssssssi",
                    $name, $description, $region, $prep_time, $cook_time,
                    $difficulty, $rating, $votes, $image_url, $author,
                    $cooking_method, $cuisine, $courses, $ingredients_main,
                    $ingredients_seasoning, $ingredients_complementary, $instructions, $id);

                if (mysqli_stmt_execute($stmt)) {
                    $response['success'] = true;
                    $response['message'] = "Resep berhasil diperbarui!";
                } else {
                    $response['message'] = "Gagal memperbarui resep: " . mysqli_stmt_error($stmt);
                }
                mysqli_stmt_close($stmt);
            } else {
                $response['message'] = "Gagal menyiapkan query: " . mysqli_error($conn);
            }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id']);

        $sql = "SELECT image_url FROM recipes WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $recipe = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if ($recipe) {
                $delete_sql = "DELETE FROM recipes WHERE id = ?";
                $delete_stmt = mysqli_prepare($conn, $delete_sql);
                if ($delete_stmt) {
                    mysqli_stmt_bind_param($delete_stmt, "i", $id);

                    if (mysqli_stmt_execute($delete_stmt)) {
                        if (!empty($recipe['image_url']) && file_exists($recipe['image_url'])) {
                            unlink($recipe['image_url']);
                        }
                        $response['success'] = true;
                        $response['message'] = "Resep berhasil dihapus!";
                    } else {
                        $response['message'] = "Gagal menghapus resep: " . mysqli_error($conn);
                    }
                    mysqli_stmt_close($delete_stmt);
                } else {
                    $response['message'] = "Gagal menyiapkan query hapus: " . mysqli_error($conn);
                }
            } else {
                $response['message'] = "Resep tidak ditemukan!";
            }
        } else {
            $response['message'] = "Gagal menyiapkan query pencarian: " . mysqli_error($conn);
        }
    } else {
        $response['message'] = "Action tidak valid!";
    }

    mysqli_close($conn);
    echo json_encode($response);
    exit();
}

// Setelah AJAX handler, baru include database untuk HTML
require_once "db.php";

// Get all recipes
$sql = "SELECT * FROM recipes ORDER BY id ASC";
$recipes_result = mysqli_query($conn, $sql);

// Get statistics
$stats = [];
$sql = "SELECT COUNT(*) as total_recipes FROM recipes";
$result = mysqli_query($conn, $sql);
$stats['total_recipes'] = mysqli_fetch_assoc($result)['total_recipes'];

$sql = "SELECT COUNT(DISTINCT region) as total_regions FROM recipes WHERE region IS NOT NULL AND region != ''";
$result = mysqli_query($conn, $sql);
$stats['total_regions'] = mysqli_fetch_assoc($result)['total_regions'];

$sql = "SELECT AVG(rating) as avg_rating FROM recipes WHERE rating > 0";
$result = mysqli_query($conn, $sql);
$stats['avg_rating'] = round(mysqli_fetch_assoc($result)['avg_rating'], 1);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Resep - Admin NusaJava Eats</title>
    <script src="https://unpkg.com/feather-icons"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --mainColor: #feba71;
            --secondaryColor: #5b2620;
            --accentColor: #af2d2d;
            --doubleColor: #816040;
            --tripleColor: #a7774e;
            --white: #ffffff;
            --light-brown: #f4e4d0;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
            --info: #3498db;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, var(--light-brown), var(--mainColor));
            min-height: 100vh;
        }
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--secondaryColor), var(--doubleColor));
            padding: 30px 20px;
            color: var(--white);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(255,255,255,0.2);
        }
        .sidebar-header h2 {
            margin-bottom: 10px;
            font-size: 1.8em;
        }
        .sidebar-header p {
            opacity: 0.8;
            font-size: 1.1em;
        }
        .sidebar ul {
            list-style: none;
        }
        .sidebar li {
            margin-bottom: 8px;
        }
        .sidebar a {
            color: var(--white);
            text-decoration: none;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-size: 1.1em;
        }
        .sidebar a:hover {
            background: rgba(255,255,255,0.15);
            transform: translateX(5px);
        }
        .sidebar a.active {
            background: var(--mainColor);
            color: var(--secondaryColor);
            font-weight: bold;
        }
        .content {
            flex: 1;
            padding: 30px;
            background: var(--white);
            margin: 20px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }
        .content-header {
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid var(--mainColor);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .content-header h1 {
            color: var(--secondaryColor);
            font-size: 2.5em;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            color: var(--white);
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .stat-card.total {
            background: linear-gradient(135deg, var(--info), #5dade2);
        }
        .stat-card.regions {
            background: linear-gradient(135deg, var(--success), #58d68d);
        }
        .stat-card.rating {
            background: linear-gradient(135deg, var(--warning), #f7dc6f);
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .stat-label {
            font-size: 1.1em;
            opacity: 0.9;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            backdrop-filter: blur(5px);
        }
        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            position: relative;
            background-color: var(--white);
            margin: 2% auto;
            padding: 0;
            border-radius: 20px;
            width: 90%;
            max-width: 1000px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.3s ease-out;
        }
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        .modal-header {
            background: linear-gradient(135deg, var(--secondaryColor), var(--doubleColor));
            color: var(--white);
            padding: 25px 30px;
            border-radius: 20px 20px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header h3 {
            font-size: 1.8em;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .close {
            color: var(--white);
            font-size: 35px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            background: none;
            padding: 5px;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        .close:hover {
            background: rgba(255,255,255,0.2);
            transform: rotate(90deg);
        }
        .modal-body {
            padding: 30px;
            background: var(--light-brown);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--secondaryColor);
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--tripleColor);
            border-radius: 8px;
            font-size: 16px;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--accentColor);
        }
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        /* File Upload Styles */
        .file-upload-container {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-upload-input {
            display: none;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 20px;
            border: 2px dashed var(--mainColor);
            border-radius: 12px;
            background: var(--light-brown);
            color: var(--secondaryColor);
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .file-upload-label:hover {
            background: var(--mainColor);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .file-upload-label.has-file {
            border-color: var(--success);
            background: rgba(39, 174, 96, 0.1);
            color: var(--success);
        }

        .image-preview {
            margin-top: 15px;
            text-align: center;
        }
        .image-preview img {
            max-width: 200px;
            max-height: 150px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-right: 10px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .btn-primary {
            background: var(--accentColor);
            color: var(--white);
        }
        .btn-warning {
            background: var(--warning);
            color: var(--white);
        }
        .btn-danger {
            background: var(--danger);
            color: var(--white);
        }
        .btn-secondary {
            background: var(--tripleColor);
            color: var(--white);
        }
        .btn-add {
            background: linear-gradient(135deg, var(--success), #58d68d);
            color: var(--white);
            font-size: 1.1em;
            padding: 15px 30px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }
        .btn-add:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.4);
        }
        .recipes-table {
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .table-header {
            background: var(--secondaryColor);
            color: var(--white);
            padding: 20px;
            font-size: 1.3em;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--light-brown);
        }
        table th {
            background: var(--light-brown);
            color: var(--secondaryColor);
            font-weight: bold;
        }
        table tr:hover {
            background: rgba(254, 186, 113, 0.1);
        }
        .recipe-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        /* Alert Styles */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }

        .alert.success {
            background: rgba(39, 174, 96, 0.1);
            border: 2px solid var(--success);
            color: var(--success);
        }

        .alert.error {
            background: rgba(231, 76, 60, 0.1);
            border: 2px solid var(--danger);
            color: var(--danger);
        }

        .loading {
            display: none;
            align-items: center;
            gap: 10px;
        }
        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid var(--success);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                padding: 20px;
            }
            .content {
                margin: 10px;
                padding: 20px;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            .modal-content {
                width: 95%;
                margin: 5% auto;
            }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="sidebar">
        <div class="sidebar-header">
            <h2><i data-feather="shield"></i> Dashboard Admin</h2>
            <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?>!</p>
        </div>
        <ul>
            <li><a href="admin.php" ><i data-feather="pie-chart"></i> Dashboard</a></li>
            <li><a href="admin-product.php"><i data-feather="package"></i> Produk</a></li>
            <li><a href="admin-orders.php"><i data-feather="shopping-bag"></i> Pesanan</a></li>
            <li><a href="admin-users.php"><i data-feather="users"></i> Pengguna</a></li>
            <li><a href="admin-recipes-fix.php" class="active"><i data-feather="book-open"></i> Resep</a></li>
            <li><a href="admin-community.php"><i data-feather="message-circle"></i> Komunitas</a></li>
            <li><a href="admin-contacts.php"><i data-feather="mail"></i> Kontak</a></li>
            <li><a href="home-login.php" target="_blank"><i data-feather="external-link"></i> Lihat Website</a></li>
            <li><a href="logout.php" onclick="return confirm('Yakin ingin logout?')"><i data-feather="log-out"></i> Logout</a></li>
        </ul>
    </div>
    <div class="content">
        <div class="content-header">
            <h1>
                <i data-feather="book-open"></i>
                Kelola Resep
            </h1>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-number"><?php echo $stats['total_recipes']; ?></div>
                <div class="stat-label">Total Resep</div>
            </div>
            <div class="stat-card regions">
                <div class="stat-number"><?php echo $stats['total_regions']; ?></div>
                <div class="stat-label">Daerah</div>
            </div>
            <div class="stat-card rating">
                <div class="stat-number"><?php echo $stats['avg_rating'] ?: '0'; ?></div>
                <div class="stat-label">Rata-rata Rating</div>
            </div>
        </div>

        <!-- Add Recipe Button -->
        <div style="margin-bottom: 30px;">
            <button onclick="openModal('add')" class="btn btn-add">
                <i data-feather="plus"></i>
                Tambah Resep Baru
            </button>
        </div>

        <!-- Recipes Table -->
        <div class="recipes-table">
            <div class="table-header">
                <i data-feather="list"></i>
                Daftar Resep (ID 1 di Atas)
            </div>
            <?php if ($recipes_result && mysqli_num_rows($recipes_result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Gambar</th>
                        <th>Nama Resep</th>
                        <th>Daerah</th>
                        <th>Rating</th>
                        <th>Difficulty</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($recipe = mysqli_fetch_assoc($recipes_result)): ?>
                    <tr>
                        <td><?php echo $recipe['id']; ?></td>
                        <td>
                            <?php if (!empty($recipe['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="<?php echo htmlspecialchars($recipe['name']); ?>" class="recipe-image" onerror="this.style.display='none'; this.parentNode.innerHTML='<div style=\'width:60px;height:60px;background:#eee;display:flex;align-items:center;justify-content:center;border-radius:8px;font-size:10px;\'>No Image</div>';">
                            <?php else: ?>
                                <div style="width:60px;height:60px;background:#eee;display:flex;align-items:center;justify-content:center;border-radius:8px;font-size:10px;">No Image</div>
                            <?php endif; ?>
                        </td>
                        <td style="font-weight: bold; color: var(--secondaryColor);">
                            <?php echo htmlspecialchars($recipe['name']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($recipe['region']); ?></td>
                        <td>
                            <span style="color: #ffd700;">★</span>
                            <?php echo $recipe['rating']; ?>
                        </td>
                        <td>
                            <span style="color: <?php
                                echo $recipe['difficulty'] === 'Easy' ? 'var(--success)' :
                                     ($recipe['difficulty'] === 'Medium' ? 'var(--warning)' : 'var(--danger)');
                            ?>; font-weight: bold;">
                                <?php echo $recipe['difficulty']; ?>
                            </span>
                        </td>
                        <td>
                            <button onclick="editRecipe(<?php echo $recipe['id']; ?>)" class="btn btn-warning">
                                <i data-feather="edit"></i>
                            </button>
                            <button onclick="deleteRecipe(<?php echo $recipe['id']; ?>)" class="btn btn-danger">
                                <i data-feather="trash-2"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div style="text-align: center; padding: 50px; color: var(--doubleColor);">
                <i data-feather="book-open" style="width: 64px; height: 64px; margin-bottom: 20px;"></i>
                <h3>Belum Ada Resep</h3>
                <p>Tambahkan resep pertama untuk memulai</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Recipe Modal -->
<div id="recipeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">
                <i data-feather="plus"></i>
                <span id="modalTitleText">Tambah Resep Baru</span>
            </h3>
            <button class="close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="modalAlert"></div>
            <form id="recipeForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="recipeId" value="">

                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Nama Resep:</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="region">Daerah Asal:</label>
                        <select id="region" name="region" required>
                            <option value="">Pilih Daerah</option>
                            <option value="Jawa Barat">Jawa Barat</option>
                            <option value="Jawa Tengah">Jawa Tengah</option>
                            <option value="Jawa Timur">Jawa Timur</option>
                            <option value="Yogyakarta">Yogyakarta</option>
                            <option value="Jakarta">Jakarta</option>
                            <option value="Banten">Banten</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="prep_time">Waktu Persiapan:</label>
                        <input type="text" id="prep_time" name="prep_time" placeholder="30 mins">
                    </div>
                    <div class="form-group">
                        <label for="cook_time">Waktu Memasak:</label>
                        <input type="text" id="cook_time" name="cook_time" placeholder="1 hour">
                    </div>
                    <div class="form-group">
                        <label for="difficulty">Tingkat Kesulitan:</label>
                        <select id="difficulty" name="difficulty">
                            <option value="Easy">Easy</option>
                            <option value="Medium">Medium</option>
                            <option value="Advanced">Advanced</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="rating">Rating:</label>
                        <input type="number" id="rating" name="rating" min="0" max="5" step="0.1" value="4.5">
                    </div>
                    <div class="form-group">
                        <label for="votes">Jumlah Vote:</label>
                        <input type="number" id="votes" name="votes" min="0" value="100">
                    </div>
                    <div class="form-group">
                        <label for="author">Penulis:</label>
                        <input type="text" id="author" name="author" placeholder="Chef Tradisional">
                    </div>
                    <div class="form-group">
                        <label for="cooking_method">Metode Memasak:</label>
                        <input type="text" id="cooking_method" name="cooking_method" placeholder="Traditional Cooking">
                    </div>
                    <div class="form-group">
                        <label for="cuisine">Jenis Masakan:</label>
                        <input type="text" id="cuisine" name="cuisine" placeholder="Indonesian, Jawa">
                    </div>
                    <div class="form-group">
                        <label for="courses">Kategori:</label>
                        <input type="text" id="courses" name="courses" placeholder="Main Course">
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Deskripsi:</label>
                    <textarea id="description" name="description" required></textarea>
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-image"></i>
                        Gambar Resep
                    </label>
                    <div class="file-upload-container">
                        <input type="file" id="recipe_image" name="recipe_image" class="file-upload-input" accept="image/*">
                        <label for="recipe_image" class="file-upload-label" id="file-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Klik untuk upload gambar</span>
                        </label>
                    </div>
                    <div id="imagePreview" class="image-preview"></div>
                    <div id="currentImagePreview" style="margin-top: 10px;"></div>
                </div>

                <div class="form-group">
                    <label for="ingredients_main">Bahan Utama (satu bahan per baris):</label>
                    <textarea id="ingredients_main" name="ingredients_main"></textarea>
                </div>

                <div class="form-group">
                    <label for="ingredients_seasoning">Bumbu Halus (satu bahan per baris):</label>
                    <textarea id="ingredients_seasoning" name="ingredients_seasoning"></textarea>
                </div>

                <div class="form-group">
                    <label for="ingredients_complementary">Pelengkap (satu bahan per baris):</label>
                    <textarea id="ingredients_complementary" name="ingredients_complementary"></textarea>
                </div>

                <div class="form-group">
                    <label for="instructions">Cara Pembuatan (satu langkah per baris):</label>
                    <textarea id="instructions" name="instructions" style="height: 200px;" required></textarea>
                </div>

                <div style="text-align: center; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span class="normal-text">
                            <i data-feather="save"></i>
                            <span id="submitButtonText">Tambah Resep</span>
                        </span>
                        <span class="loading">
                            <div class="spinner"></div>
                            Menyimpan...
                        </span>
                    </button>
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">
                        <i data-feather="x"></i>
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    feather.replace();

    // File upload preview functionality
    function setupFileUpload(inputId, labelId, previewId) {
        const input = document.getElementById(inputId);
        const label = document.getElementById(labelId);
        const preview = document.getElementById(previewId);

        input.addEventListener('change', function() {
            const file = this.files[0];
            const labelText = label.querySelector('span');

            if (file) {
                labelText.textContent = file.name;
                label.classList.add('has-file');

                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <p style="margin-top: 10px; color: var(--secondaryColor); font-weight: bold;">
                            Preview Gambar
                        </p>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                labelText.textContent = 'Klik untuk upload gambar';
                label.classList.remove('has-file');
                preview.innerHTML = '';
            }
        });
    }

    setupFileUpload('recipe_image', 'file-label', 'imagePreview');

    function openModal(action) {
        const modal = document.getElementById('recipeModal');
        const modalTitle = document.getElementById('modalTitleText');
        const submitButton = document.getElementById('submitButtonText');
        const formAction = document.getElementById('formAction');

        if (action === 'add') {
            modalTitle.textContent = 'Tambah Resep Baru';
            submitButton.textContent = 'Tambah Resep';
            formAction.value = 'add';
            document.getElementById('recipeForm').reset();
            document.getElementById('rating').value = '4.5';
            document.getElementById('votes').value = '100';
            document.getElementById('imagePreview').innerHTML = '';
            document.getElementById('currentImagePreview').innerHTML = '';
            document.getElementById('file-label').classList.remove('has-file');
            document.getElementById('file-label').querySelector('span').textContent = 'Klik untuk upload gambar';
        }

        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        feather.replace();
    }

    function closeModal() {
        const modal = document.getElementById('recipeModal');
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
        document.getElementById('modalAlert').innerHTML = '';
    }

    function editRecipe(id) {
        fetch(`get-recipe.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const recipe = data.recipe;

                    document.getElementById('formAction').value = 'edit';
                    document.getElementById('recipeId').value = recipe.id;
                    document.getElementById('name').value = recipe.name || '';
                    document.getElementById('description').value = recipe.description || '';
                    document.getElementById('region').value = recipe.region || '';
                    document.getElementById('prep_time').value = recipe.prep_time || '';
                    document.getElementById('cook_time').value = recipe.cook_time || '';
                    document.getElementById('difficulty').value = recipe.difficulty || '';
                    document.getElementById('rating').value = recipe.rating || '';
                    document.getElementById('votes').value = recipe.votes || '';
                    document.getElementById('author').value = recipe.author || '';
                    document.getElementById('cooking_method').value = recipe.cooking_method || '';
                    document.getElementById('cuisine').value = recipe.cuisine || '';
                    document.getElementById('courses').value = recipe.courses || '';
                    document.getElementById('ingredients_main').value = recipe.ingredients_main || '';
                    document.getElementById('ingredients_seasoning').value = recipe.ingredients_seasoning || '';
                    document.getElementById('ingredients_complementary').value = recipe.ingredients_complementary || '';
                    document.getElementById('instructions').value = recipe.instructions || '';

                    const imagePreview = document.getElementById('currentImagePreview');
                    if (recipe.image_url) {
                        imagePreview.innerHTML = `
                            <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--secondaryColor);">
                                <i class="fas fa-eye"></i> Gambar Saat Ini:
                            </label>
                            <img src="${recipe.image_url}" alt="Current Image" style="max-width: 200px; max-height: 150px; border-radius: 8px; border: 2px solid #e0e0e0;" onerror="this.style.display='none'; this.parentNode.innerHTML='<p style=\\'color: #666; font-style: italic;\\'>Gambar tidak ditemukan</p>';">
                        `;
                    } else {
                        imagePreview.innerHTML = '<p style="color: #666; font-style: italic;">Tidak ada gambar</p>';
                    }

                    document.getElementById('modalTitleText').textContent = 'Edit Resep';
                    document.getElementById('submitButtonText').textContent = 'Update Resep';

                    document.getElementById('recipeModal').classList.add('show');
                    document.body.style.overflow = 'hidden';
                    feather.replace();
                } else {
                    alert('Error loading recipe data: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error loading recipe data');
                console.error('Error:', error);
            });
    }

    function deleteRecipe(id) {
        if (confirm('Yakin ingin menghapus resep ini?')) {
            const formData = new FormData();
            formData.append('ajax_recipe_action', '1');
            formData.append('action', 'delete');
            formData.append('id', id);

            fetch('admin-recipes.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                alert('Terjadi kesalahan saat menghapus resep');
                console.error('Error:', error);
            });
        }
    }

    // Handle form submission
    document.getElementById('recipeForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const submitBtn = document.getElementById('submitBtn');
        const normalText = submitBtn.querySelector('.normal-text');
        const loadingText = submitBtn.querySelector('.loading');

        submitBtn.disabled = true;
        normalText.style.display = 'none';
        loadingText.style.display = 'flex';

        const formData = new FormData(this);
        formData.append('ajax_recipe_action', '1');

        // Debug: log yang dikirim
        console.log('Sending form data...');

        fetch('admin-recipes.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            return response.text(); // Ambil sebagai text dulu untuk debug
        })
        .then(text => {
            console.log('Raw response:', text);

            // Coba parse sebagai JSON
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                throw new Error('Response bukan JSON valid: ' + text.substring(0, 200));
            }

            const alertDiv = document.getElementById('modalAlert');

            if (data.success) {
                alertDiv.innerHTML = `
                    <div class="alert success">
                        <i class="fas fa-check-circle"></i>
                        ${data.message}
                    </div>
                `;

                setTimeout(() => {
                    closeModal();
                    location.reload();
                }, 1500);
            } else {
                alertDiv.innerHTML = `
                    <div class="alert error">
                        <i class="fas fa-exclamation-circle"></i>
                        ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            document.getElementById('modalAlert').innerHTML = `
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    Terjadi kesalahan: ${error.message}
                </div>
            `;
        })
        .finally(() => {
            submitBtn.disabled = false;
            normalText.style.display = 'flex';
            loadingText.style.display = 'none';
        });
    });

    window.onclick = function(event) {
        const modal = document.getElementById('recipeModal');
        if (event.target === modal) {
            closeModal();
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
</script>
</body>
</html>
