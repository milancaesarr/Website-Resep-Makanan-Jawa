<?php
session_start();

// Proteksi akses: hanya untuk admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.html");
    exit();
}

require_once "db.php";

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Get product data first to delete image file
    $sql = "SELECT image_url FROM products WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);

    if ($product) {
        // Delete the product from database
        $delete_sql = "DELETE FROM products WHERE id = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_sql);
        mysqli_stmt_bind_param($delete_stmt, "i", $id);

        if (mysqli_stmt_execute($delete_stmt)) {
            // Delete image file if exists
            if (!empty($product['image_url']) && file_exists($product['image_url'])) {
                unlink($product['image_url']);
            }

            // Set success message
            $_SESSION['success_message'] = "Produk berhasil dihapus!";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus produk: " . mysqli_error($conn);
        }
        mysqli_stmt_close($delete_stmt);
    } else {
        $_SESSION['error_message'] = "Produk tidak ditemukan!";
    }
    mysqli_stmt_close($stmt);
} else {
    $_SESSION['error_message'] = "ID produk tidak valid!";
}

// Redirect back to admin product page
header("Location: admin-product.php");
exit();
?>
