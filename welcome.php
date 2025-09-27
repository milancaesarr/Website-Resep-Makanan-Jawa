<?php
session_start();

// Auto-redirect ke halaman yang sesuai berdasarkan status login
if (isset($_SESSION['user_id'])) {
    $user_role = $_SESSION['user_role'] ?? 'user';

    if (trim($user_role) === 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: home-login.php");
    }
    exit();
} else {
    // User belum login, redirect ke index.php
    header("Location: index.php");
    exit();
}
?>
