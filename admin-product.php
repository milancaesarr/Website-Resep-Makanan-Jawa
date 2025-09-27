<?php
session_start();

// Proteksi akses: hanya untuk admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.html");
    exit();
}

require_once "db.php";

// Handle AJAX request for adding product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_add_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $brand = trim($_POST['brand']);

    $response = ['success' => false, 'message' => ''];

    if (empty($name) || empty($description) || $price <= 0 || $stock < 0) {
        $response['message'] = "Semua field harus diisi dengan benar!";
    } else {
        // Handle image upload
        $image_url = '';
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/products/';

            // Create directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_tmp = $_FILES['product_image']['tmp_name'];
            $file_name = $_FILES['product_image']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            // Validate file type
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($file_ext, $allowed_types)) {
                // Generate unique filename
                $new_filename = uniqid('product_') . '.' . $file_ext;
                $upload_path = $upload_dir . $new_filename;

                // Check file size (max 5MB)
                if ($_FILES['product_image']['size'] <= 5 * 1024 * 1024) {
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        $image_url = $upload_path;
                    } else {
                        $response['message'] = "Gagal mengupload gambar!";
                        header('Content-Type: application/json');
                        echo json_encode($response);
                        exit();
                    }
                } else {
                    $response['message'] = "Ukuran file terlalu besar! Maksimal 5MB.";
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit();
                }
            } else {
                $response['message'] = "Tipe file tidak diizinkan! Gunakan JPG, PNG, GIF, atau WebP.";
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            }
        }

        // Fixed SQL binding - use correct parameter types
        $sql = "INSERT INTO products (name, description, price, stock, brand, image_url) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            // Fixed parameter binding: s=string, d=double, i=integer
            mysqli_stmt_bind_param($stmt, "ssdiss", $name, $description, $price, $stock, $brand, $image_url);

            if (mysqli_stmt_execute($stmt)) {
                $response['success'] = true;
                $response['message'] = "Produk berhasil ditambahkan!";
            } else {
                $response['message'] = "Gagal menambahkan produk: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $response['message'] = "Gagal menyiapkan query: " . mysqli_error($conn);
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Ambil semua produk dari database, dimulai dari ID terkecil
$sql = "SELECT * FROM products ORDER BY id ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Produk - Nusa Java Eats</title>
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
        }
        .content-header {
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid var(--mainColor);
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
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: linear-gradient(135deg, var(--info), #5dade2);
            color: var(--white);
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .stat-card.success {
            background: linear-gradient(135deg, var(--success), #58d68d);
        }
        .stat-card.warning {
            background: linear-gradient(135deg, var(--warning), #f7dc6f);
        }
        .stat-card.danger {
            background: linear-gradient(135deg, var(--danger), #ec7063);
        }
        .stat-number {
            font-size: 3em;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .stat-label {
            font-size: 1.2em;
            opacity: 0.9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        table th {
            background-color: var(--secondaryColor);
            color: white;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        table tr:hover {
            background-color: rgba(254, 186, 113, 0.1);
        }
        .btn-action {
            margin-right: 5px;
            padding: 8px 12px;
            text-decoration: none;
            color: white;
            border-radius: 8px;
            display: inline-block;
            font-size: 12px;
            transition: all 0.3s;
            font-weight: bold;
        }
        .btn-edit {
            background-color: var(--info);
            border: none;
            cursor: pointer;
        }
        .btn-edit:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
        .btn-delete {
            background-color: var(--danger);
        }
        .btn-delete:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
        }
        .btn-add {
            background: linear-gradient(45deg, var(--success), #58d68d);
            padding: 15px 25px;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
            transition: all 0.3s;
            font-weight: bold;
            font-size: 1.1em;
            cursor: pointer;
            border: none;
        }
        .btn-add:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .no-products {
            text-align: center;
            padding: 60px 40px;
            color: var(--doubleColor);
            background: var(--light-brown);
            border-radius: 20px;
            margin-top: 20px;
        }
        .no-products h3 {
            color: var(--secondaryColor);
            margin-bottom: 15px;
            font-size: 1.8em;
        }
        .no-products i {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            color: var(--tripleColor);
        }

        /* MODAL STYLES */
        .modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-content {
            background: linear-gradient(135deg, var(--white), var(--light-brown));
            margin: auto;
            padding: 0;
            border-radius: 20px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideIn 0.3s ease;
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(45deg, var(--secondaryColor), var(--doubleColor));
            color: var(--white);
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.8em;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .close {
            color: var(--white);
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close:hover {
            background: rgba(255,255,255,0.2);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--secondaryColor);
            font-size: 1.1em;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1em;
            transition: all 0.3s ease;
            font-family: inherit;
            background: var(--white);
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--mainColor);
            box-shadow: 0 0 0 3px rgba(254, 186, 113, 0.2);
            transform: translateY(-2px);
        }

        .form-group textarea {
            min-height: 120px;
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

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
        }

        .btn-modal {
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 120px;
            justify-content: center;
        }

        .btn-modal.primary {
            background: linear-gradient(45deg, var(--success), #58d68d);
            color: var(--white);
        }

        .btn-modal.secondary {
            background: #95a5a6;
            color: var(--white);
        }

        .btn-modal:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .btn-modal:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
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
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            table {
                font-size: 0.9em;
            }
            .modal-content {
                width: 95%;
                margin: 10px;
            }
            .modal-body {
                padding: 20px;
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
            <li><a href="admin.php"><i data-feather="pie-chart"></i> Dashboard</a></li>
            <li><a href="admin-product.php" class="active"><i data-feather="package"></i> Produk</a></li>
            <li><a href="admin-orders.php"><i data-feather="shopping-bag"></i> Pesanan</a></li>
            <li><a href="admin-users.php"><i data-feather="users"></i> Pengguna</a></li>
            <li><a href="admin-recipes.php"><i data-feather="book-open"></i> Resep</a></li>
            <li><a href="admin-community.php"><i data-feather="message-circle"></i> Komunitas</a></li>
            <li><a href="admin-contacts.php"><i data-feather="mail"></i> Kontak</a></li>
            <li><a href="home-login.php" target="_blank"><i data-feather="external-link"></i> Lihat Website</a></li>
            <li><a href="logout.php" onclick="return confirm('Yakin ingin logout?')"><i data-feather="log-out"></i> Logout</a></li>
        </ul>
    </div>
    <div class="content">
        <div class="content-header">
            <h1>
                <i data-feather="package"></i>
                Manajemen Produk
            </h1>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $result ? mysqli_num_rows($result) : 0; ?></div>
                <div class="stat-label">Total Produk</div>
            </div>
            <div class="stat-card success">
                <div class="stat-number">
                    <?php
                    $stock_sql = "SELECT SUM(stock) as total_stock FROM products";
                    $stock_result = mysqli_query($conn, $stock_sql);
                    $stock_data = mysqli_fetch_assoc($stock_result);
                    echo $stock_data['total_stock'] ?? 0;
                    ?>
                </div>
                <div class="stat-label">Total Stok</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-number">
                    <?php
                    $low_stock_sql = "SELECT COUNT(*) as low_stock FROM products WHERE stock < 10";
                    $low_stock_result = mysqli_query($conn, $low_stock_sql);
                    $low_stock_data = mysqli_fetch_assoc($low_stock_result);
                    echo $low_stock_data['low_stock'] ?? 0;
                    ?>
                </div>
                <div class="stat-label">Stok Rendah</div>
            </div>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <button onclick="openAddProductModal()" class="btn-add">
            <i data-feather="plus-circle"></i>
            Tambah Produk Baru
        </button>

        <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Gambar</th>
                    <th>Nama Produk</th>
                    <th>Deskripsi</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Brand</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Reset result pointer dan buat counter untuk nomor urut
                mysqli_data_seek($result, 0);
                $counter = 1;
                while ($row = mysqli_fetch_assoc($result)):
                ?>
                <tr>
                    <td style="font-weight: bold; color: var(--secondaryColor);"><?php echo $counter; ?></td>
                    <td>
                        <?php if (!empty($row['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="product-image">
                        <?php else: ?>
                            <div style="width:60px;height:60px;background:#eee;display:flex;align-items:center;justify-content:center;border-radius:8px;font-size:10px;">No Image</div>
                        <?php endif; ?>
                    </td>
                    <td style="font-weight: bold; color: var(--secondaryColor);"><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars(substr($row['description'] ?? '', 0, 50)) . (strlen($row['description'] ?? '') > 50 ? '...' : ''); ?></td>
                    <td style="font-weight: bold; color: var(--accentColor);">Rp<?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                    <td>
                        <span style="color: <?php echo $row['stock'] < 10 ? 'var(--danger)' : 'var(--success)'; ?>; font-weight: bold;">
                            <?php echo $row['stock']; ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($row['brand'] ?? '-'); ?></td>
                    <td>
                        <span style="color: <?php echo $row['stock'] > 0 ? 'var(--success)' : 'var(--danger)'; ?>; font-weight: bold;">
                            <?php echo $row['stock'] > 0 ? 'Tersedia' : 'Habis'; ?>
                        </span>
                    </td>
                    <td>
                        <button onclick="openEditProductModal(<?php echo $row['id']; ?>)" class="btn-action btn-edit">
                            <i data-feather="edit"></i> Edit
                        </button>
                        <a href="delete_product.php?id=<?php echo $row['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Yakin hapus produk ini?')">
                            <i data-feather="trash-2"></i> Hapus
                        </a>
                    </td>
                </tr>
                <?php
                $counter++;
                endwhile;
                ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="no-products">
            <i data-feather="package"></i>
            <h3>Belum ada produk</h3>
            <p>Mulai dengan menambahkan produk pertama Anda!</p>
            <br>
            <button onclick="openAddProductModal()" class="btn-add">
                <i data-feather="plus-circle"></i>
                Tambah Produk Pertama
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Product Modal -->
<div id="addProductModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>
                <i class="fas fa-plus-circle"></i>
                Tambah Produk Baru
            </h2>
            <span class="close" onclick="closeAddProductModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="modalAlert"></div>
            <form id="addProductForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="product_name">
                        <i class="fas fa-tag"></i>
                        Nama Produk
                    </label>
                    <input type="text" id="product_name" name="name" required placeholder="Masukkan nama produk">
                </div>

                <div class="form-group">
                    <label for="product_description">
                        <i class="fas fa-align-left"></i>
                        Deskripsi
                    </label>
                    <textarea id="product_description" name="description" required placeholder="Masukkan deskripsi produk"></textarea>
                </div>

                <div class="form-group">
                    <label for="product_price">
                        <i class="fas fa-dollar-sign"></i>
                        Harga (Rp)
                    </label>
                    <input type="number" id="product_price" name="price" min="0" step="1000" required placeholder="0">
                </div>

                <div class="form-group">
                    <label for="product_stock">
                        <i class="fas fa-boxes"></i>
                        Stok
                    </label>
                    <input type="number" id="product_stock" name="stock" min="0" required placeholder="0">
                </div>

                <div class="form-group">
                    <label for="product_brand">
                        <i class="fas fa-copyright"></i>
                        Brand
                    </label>
                    <input type="text" id="product_brand" name="brand" placeholder="Masukkan brand produk">
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-image"></i>
                        Gambar Produk
                    </label>
                    <div class="file-upload-container">
                        <input type="file" id="product_image" name="product_image" class="file-upload-input" accept="image/*">
                        <label for="product_image" class="file-upload-label" id="file-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Klik untuk upload gambar</span>
                        </label>
                    </div>
                    <div id="imagePreview" class="image-preview"></div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-modal secondary" onclick="closeAddProductModal()">
                        <i class="fas fa-times"></i>
                        Batal
                    </button>
                    <button type="submit" class="btn-modal primary" id="submitBtn">
                        <span class="normal-text">
                            <i class="fas fa-plus"></i>
                            Tambah Produk
                        </span>
                        <span class="loading">
                            <div class="spinner"></div>
                            Menambahkan...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div id="editProductModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>
                <i class="fas fa-edit"></i>
                Edit Produk
            </h2>
            <span class="close" onclick="closeEditProductModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="editModalAlert"></div>
            <form id="editProductForm" enctype="multipart/form-data">
                <input type="hidden" id="edit_product_id" name="id">

                <div class="form-group">
                    <label for="edit_product_name">
                        <i class="fas fa-tag"></i>
                        Nama Produk
                    </label>
                    <input type="text" id="edit_product_name" name="name" required placeholder="Masukkan nama produk">
                </div>

                <div class="form-group">
                    <label for="edit_product_description">
                        <i class="fas fa-align-left"></i>
                        Deskripsi
                    </label>
                    <textarea id="edit_product_description" name="description" required placeholder="Masukkan deskripsi produk"></textarea>
                </div>

                <div class="form-group">
                    <label for="edit_product_price">
                        <i class="fas fa-dollar-sign"></i>
                        Harga (Rp)
                    </label>
                    <input type="number" id="edit_product_price" name="price" min="0" step="1000" required placeholder="0">
                </div>

                <div class="form-group">
                    <label for="edit_product_stock">
                        <i class="fas fa-boxes"></i>
                        Stok
                    </label>
                    <input type="number" id="edit_product_stock" name="stock" min="0" required placeholder="0">
                </div>

                <div class="form-group">
                    <label for="edit_product_brand">
                        <i class="fas fa-copyright"></i>
                        Brand
                    </label>
                    <input type="text" id="edit_product_brand" name="brand" placeholder="Masukkan brand produk">
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-image"></i>
                        Gambar Produk
                    </label>
                    <div class="file-upload-container">
                        <input type="file" id="edit_product_image" name="product_image" class="file-upload-input" accept="image/*">
                        <label for="edit_product_image" class="file-upload-label" id="edit-file-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Klik untuk upload gambar baru</span>
                        </label>
                    </div>
                    <div id="editImagePreview" class="image-preview"></div>
                    <div id="currentEditImagePreview" style="margin-top: 10px;"></div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-modal secondary" onclick="closeEditProductModal()">
                        <i class="fas fa-times"></i>
                        Batal
                    </button>
                    <button type="submit" class="btn-modal primary" id="editSubmitBtn">
                        <span class="normal-text">
                            <i class="fas fa-save"></i>
                            Update Produk
                        </span>
                        <span class="loading">
                            <div class="spinner"></div>
                            Mengupdate...
                        </span>
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
                // Update label
                labelText.textContent = file.name;
                label.classList.add('has-file');

                // Show preview
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
                // Reset label
                labelText.textContent = inputId.includes('edit') ? 'Klik untuk upload gambar baru' : 'Klik untuk upload gambar';
                label.classList.remove('has-file');
                preview.innerHTML = '';
            }
        });
    }

    // Setup file uploads
    setupFileUpload('product_image', 'file-label', 'imagePreview');
    setupFileUpload('edit_product_image', 'edit-file-label', 'editImagePreview');

    // Modal functions
    function openAddProductModal() {
        document.getElementById('addProductModal').classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeAddProductModal() {
        document.getElementById('addProductModal').classList.remove('show');
        document.body.style.overflow = 'auto';
        document.getElementById('addProductForm').reset();
        document.getElementById('modalAlert').innerHTML = '';
        document.getElementById('imagePreview').innerHTML = '';
        document.getElementById('file-label').classList.remove('has-file');
        document.getElementById('file-label').querySelector('span').textContent = 'Klik untuk upload gambar';
    }

    function openEditProductModal(productId) {
        console.log('Opening edit modal for product ID:', productId);

        // Show modal
        document.getElementById('editProductModal').classList.add('show');
        document.body.style.overflow = 'hidden';

        // Clear any previous alerts
        document.getElementById('editModalAlert').innerHTML = '';

        // Fetch product data
        fetch(`edit_product.php?ajax_get_product=1&id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const product = data.data;

                // Fill form with product data
                document.getElementById('edit_product_id').value = product.id;
                document.getElementById('edit_product_name').value = product.name || '';
                document.getElementById('edit_product_description').value = product.description || '';
                document.getElementById('edit_product_price').value = product.price || '';
                document.getElementById('edit_product_stock').value = product.stock || '';
                document.getElementById('edit_product_brand').value = product.brand || '';

                // Show current image preview if exists
                const imagePreview = document.getElementById('currentEditImagePreview');
                if (product.image_url) {
                    imagePreview.innerHTML = `
                        <label style="display: block; margin-bottom: 5px; font-weight: bold; color: var(--secondaryColor);">
                            <i class="fas fa-eye"></i> Gambar Saat Ini:
                        </label>
                        <img src="${product.image_url}" alt="Current Image" style="max-width: 200px; max-height: 150px; border-radius: 8px; border: 2px solid #e0e0e0;">
                    `;
                } else {
                    imagePreview.innerHTML = '<p style="color: #666; font-style: italic;">Tidak ada gambar</p>';
                }
            } else {
                document.getElementById('editModalAlert').innerHTML = `
                    <div class="alert error">
                        <i class="fas fa-exclamation-circle"></i>
                        ${data.message || 'Gagal mengambil data produk'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error fetching product data:', error);
            document.getElementById('editModalAlert').innerHTML = `
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    Terjadi kesalahan saat mengambil data produk
                </div>
            `;
        });
    }

    function closeEditProductModal() {
        document.getElementById('editProductModal').classList.remove('show');
        document.body.style.overflow = 'auto';
        document.getElementById('editProductForm').reset();
        document.getElementById('editModalAlert').innerHTML = '';
        document.getElementById('currentEditImagePreview').innerHTML = '';
        document.getElementById('editImagePreview').innerHTML = '';
        document.getElementById('edit-file-label').classList.remove('has-file');
        document.getElementById('edit-file-label').querySelector('span').textContent = 'Klik untuk upload gambar baru';
    }

    // Handle form submissions
    document.getElementById('addProductForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const submitBtn = document.getElementById('submitBtn');
        const normalText = submitBtn.querySelector('.normal-text');
        const loadingText = submitBtn.querySelector('.loading');

        // Show loading state
        submitBtn.disabled = true;
        normalText.style.display = 'none';
        loadingText.style.display = 'flex';

        const formData = new FormData(this);
        formData.append('ajax_add_product', '1');

        fetch('admin-product.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const alertDiv = document.getElementById('modalAlert');

            if (data.success) {
                alertDiv.innerHTML = `
                    <div class="alert success">
                        <i class="fas fa-check-circle"></i>
                        ${data.message}
                    </div>
                `;

                // Reset form and close modal after 1.5 seconds
                setTimeout(() => {
                    closeAddProductModal();
                    location.reload(); // Refresh to show new product
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
            document.getElementById('modalAlert').innerHTML = `
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    Terjadi kesalahan. Silakan coba lagi.
                </div>
            `;
        })
        .finally(() => {
            // Reset button state
            submitBtn.disabled = false;
            normalText.style.display = 'flex';
            loadingText.style.display = 'none';
        });
    });

    document.getElementById('editProductForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const submitBtn = document.getElementById('editSubmitBtn');
        const normalText = submitBtn.querySelector('.normal-text');
        const loadingText = submitBtn.querySelector('.loading');

        // Show loading state
        submitBtn.disabled = true;
        normalText.style.display = 'none';
        loadingText.style.display = 'flex';

        const formData = new FormData(this);
        formData.append('ajax_edit_product', '1');

        fetch('edit_product.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const alertDiv = document.getElementById('editModalAlert');

            if (data.success) {
                alertDiv.innerHTML = `
                    <div class="alert success">
                        <i class="fas fa-check-circle"></i>
                        ${data.message}
                    </div>
                `;

                // Reset form and close modal after 1.5 seconds
                setTimeout(() => {
                    closeEditProductModal();
                    location.reload(); // Refresh to show updated product
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
            console.error('Error updating product:', error);
            document.getElementById('editModalAlert').innerHTML = `
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    Terjadi kesalahan. Silakan coba lagi.
                </div>
            `;
        })
        .finally(() => {
            // Reset button state
            submitBtn.disabled = false;
            normalText.style.display = 'flex';
            loadingText.style.display = 'none';
        });
    });

    // Close modal when clicking outside
    window.onclick = function(event) {
        const addModal = document.getElementById('addProductModal');
        const editModal = document.getElementById('editProductModal');

        if (event.target === addModal) {
            closeAddProductModal();
        }
        if (event.target === editModal) {
            closeEditProductModal();
        }
    }

    // ESC key to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddProductModal();
            closeEditProductModal();
        }
    });
</script>
</body>
</html>
