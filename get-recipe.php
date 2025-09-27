<?php
session_start();

// Proteksi akses: hanya untuk admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Akses tidak diizinkan']);
    exit();
}

require_once "db.php";

$response = ['success' => false, 'message' => ''];

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "SELECT * FROM recipes WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $recipe = mysqli_fetch_assoc($result);

        if ($recipe) {
            $response['success'] = true;
            $response['recipe'] = $recipe;
        } else {
            $response['message'] = 'Resep tidak ditemukan';
        }

        mysqli_stmt_close($stmt);
    } else {
        $response['message'] = 'Gagal menyiapkan query: ' . mysqli_error($conn);
    }
} else {
    $response['message'] = 'ID resep tidak diberikan';
}

header('Content-Type: application/json');
echo json_encode($response);
?>
