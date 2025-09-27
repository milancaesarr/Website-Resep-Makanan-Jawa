<?php
session_start();

// Proteksi akses: hanya untuk admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.html");
    exit();
}

require_once "db.php";

$success = false;
$error = "";

if ($_POST) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $brand = trim($_POST['brand']);
    $image_url = trim($_POST['image_url']);
    
    if (empty($name) || empty($description) || $price <= 0 || $stock < 0) {
        $error = "Semua field harus diisi dengan benar!";
    } else {
        $sql = "INSERT INTO products (name, description, price, stock, brand, image_url) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssdiss", $name, $description, $price, $stock, $brand, $image_url);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = true;
        } else {
            $error = "Gagal menambahkan produk: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah Produk - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        textarea {
            height: 100px;
            resize: vertical;
        }
        .btn {
            background-color: #27ae60;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background-color: #229954;
        }
        .btn-secondary {
            background-color: #95a5a6;
        }
        .btn-secondary:hover {
            background-color: #7f8c8d;
        }
        .alert {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Tambah Produk Baru</h1>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                Produk berhasil ditambahkan! <a href="admin_product.php">Kembali ke dashboard</a>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="name">Nama Produk:</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="description">Deskripsi:</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="price">Harga (Rp):</label>
                <input type="number" id="price" name="price" min="0" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="stock">Stok:</label>
                <input type="number" id="stock" name="stock" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="brand">Brand:</label>
                <input type="text" id="brand" name="brand">
            </div>
            
            <div class="form-group">
                <label for="image_url">URL Gambar:</label>
                <input type="url" id="image_url" name="image_url" placeholder="Assets/Img/produk/namafile.png">
            </div>
            
            <button type="submit" class="btn">Tambah Produk</button>
            <a href="admin_product.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</body>
</html>