<?php
session_start();

// Proteksi akses: hanya untuk admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.html");
    exit();
}

require_once "db.php";

// Handle AJAX request for updating product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_edit_product'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $brand = trim($_POST['brand']);

    $response = ['success' => false, 'message' => ''];

    if ($id <= 0) {
        $response['message'] = "ID produk tidak valid!";
    } elseif (empty($name) || empty($description) || $price <= 0 || $stock < 0) {
        $response['message'] = "Semua field harus diisi dengan benar!";
    } else {
        // Get current product data first
        $current_sql = "SELECT image_url FROM products WHERE id = ?";
        $current_stmt = mysqli_prepare($conn, $current_sql);
        mysqli_stmt_bind_param($current_stmt, "i", $id);
        mysqli_stmt_execute($current_stmt);
        $current_result = mysqli_stmt_get_result($current_stmt);
        $current_product = mysqli_fetch_assoc($current_result);

        $image_url = $current_product['image_url'] ?? ''; // Keep existing image by default

        // Handle new image upload
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
                        // Delete old image if exists and different from new one
                        if (!empty($current_product['image_url']) &&
                            file_exists($current_product['image_url']) &&
                            $current_product['image_url'] !== $upload_path) {
                            unlink($current_product['image_url']);
                        }
                        $image_url = $upload_path;
                    } else {
                        $response['message'] = "Gagal mengupload gambar baru!";
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

        // Fixed SQL parameter binding
        $sql = "UPDATE products SET name = ?, description = ?, price = ?, stock = ?, brand = ?, image_url = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            // Fixed: s=string, d=double, i=integer - correct order and types
            mysqli_stmt_bind_param($stmt, "ssdissi", $name, $description, $price, $stock, $brand, $image_url, $id);

            if (mysqli_stmt_execute($stmt)) {
                $response['success'] = true;
                $response['message'] = "Produk berhasil diupdate!";
            } else {
                $response['message'] = "Gagal mengupdate produk: " . mysqli_stmt_error($stmt);
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

// Get product data for AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax_get_product'])) {
    $id = intval($_GET['id']);
    $response = ['success' => false, 'data' => null];

    if ($id > 0) {
        $sql = "SELECT * FROM products WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);

        if ($product) {
            $response['success'] = true;
            $response['data'] = $product;
        } else {
            $response['message'] = "Produk tidak ditemukan!";
        }
        mysqli_stmt_close($stmt);
    } else {
        $response['message'] = "ID produk tidak valid!";
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// If regular page access, redirect to admin-product.php
header("Location: admin-product.php");
exit();
?>
