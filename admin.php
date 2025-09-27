<?php
session_start();

// Proteksi akses: hanya untuk admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.html");
    exit();
}

require_once "db.php";

// Get statistics
$stats = [];

// Total products
$sql = "SELECT COUNT(*) as total_products FROM products";
$result = mysqli_query($conn, $sql);
$stats['total_products'] = mysqli_fetch_assoc($result)['total_products'];

// Total orders
$sql = "SELECT COUNT(*) as total_orders FROM orders";
$result = mysqli_query($conn, $sql);
$stats['total_orders'] = mysqli_fetch_assoc($result)['total_orders'];

// Total users
$sql = "SELECT COUNT(*) as total_users FROM users WHERE role = 'user'";
$result = mysqli_query($conn, $sql);
$stats['total_users'] = mysqli_fetch_assoc($result)['total_users'];

// Total revenue
$sql = "SELECT SUM(total_amount) as total_revenue FROM orders WHERE status != 'cancelled'";
$result = mysqli_query($conn, $sql);
$stats['total_revenue'] = mysqli_fetch_assoc($result)['total_revenue'] ?? 0;

// Pending orders
$sql = "SELECT COUNT(*) as pending_orders FROM orders WHERE status = 'pending'";
$result = mysqli_query($conn, $sql);
$stats['pending_orders'] = mysqli_fetch_assoc($result)['pending_orders'];

// Recent orders
$sql = "SELECT o.*, u.full_name
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
        LIMIT 5";
$recent_orders = mysqli_query($conn, $sql);

// Low stock products
$sql = "SELECT * FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 5";
$low_stock = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - NusaJava Eats</title>
    <script src="https://unpkg.com/feather-icons"></script>
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
        .content-header p {
            color: var(--doubleColor);
            font-size: 1.2em;
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
        .stat-card.primary {
            background: linear-gradient(135deg, var(--secondaryColor), var(--doubleColor));
        }
        .stat-icon {
            font-size: 3em;
            margin-bottom: 15px;
            opacity: 0.9;
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
        .dashboard-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .dashboard-section {
            background: var(--light-brown);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            color: var(--secondaryColor);
            font-size: 1.3em;
            font-weight: bold;
        }
        .order-item, .product-item {
            background: var(--white);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px;
            border-left: 4px solid var(--mainColor);
            transition: all 0.3s ease;
        }
        .order-item:hover, .product-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .order-number {
            font-weight: bold;
            color: var(--secondaryColor);
        }
        .order-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }
        .status-pending {
            background: var(--warning);
            color: var(--white);
        }
        .status-confirmed {
            background: var(--info);
            color: var(--white);
        }
        .status-delivered {
            background: var(--success);
            color: var(--white);
        }
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .action-btn {
            background: linear-gradient(45deg, var(--tripleColor), var(--doubleColor));
            color: var(--white);
            padding: 20px;
            border-radius: 15px;
            text-decoration: none;
            text-align: center;
            font-weight: bold;
            font-size: 1.1em;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
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
            .dashboard-sections {
                grid-template-columns: 1fr;
            }
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
                <li><a href="admin.php" class="active"><i data-feather="pie-chart"></i> Dashboard</a></li>
                <li><a href="admin-product.php"><i data-feather="package"></i> Produk</a></li>
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
                <i data-feather="pie-chart"></i>
                Dashboard Admin
            </h1>
            <p>Kelola bisnis NusaJava Eats dengan mudah</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-icon"><i data-feather="package"></i></div>
                <div class="stat-number"><?php echo $stats['total_products']; ?></div>
                <div class="stat-label">Total Produk</div>
            </div>
            <div class="stat-card success">
                <div class="stat-icon"><i data-feather="shopping-bag"></i></div>
                <div class="stat-number"><?php echo $stats['total_orders']; ?></div>
                <div class="stat-label">Total Pesanan</div>
            </div>
            <div class="stat-card info">
                <div class="stat-icon"><i data-feather="users"></i></div>
                <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                <div class="stat-label">Total Pengguna</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon"><i data-feather="clock"></i></div>
                <div class="stat-number"><?php echo $stats['pending_orders']; ?></div>
                <div class="stat-label">Pesanan Pending</div>
            </div>
            <div class="stat-card success" style="grid-column: span 2;">
                <div class="stat-icon"><i data-feather="dollar-sign"></i></div>
                <div class="stat-number">Rp<?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?></div>
                <div class="stat-label">Total Pendapatan</div>
            </div>
        </div>

        <!-- Dashboard Sections -->
        <div class="dashboard-sections">
            <!-- Recent Orders -->
            <div class="dashboard-section">
                <div class="section-header">
                    <i data-feather="shopping-cart"></i>
                    Pesanan Terbaru
                </div>
                <?php if (mysqli_num_rows($recent_orders) > 0): ?>
                    <?php while ($order = mysqli_fetch_assoc($recent_orders)): ?>
                        <div class="order-item">
                            <div class="order-header">
                                <span class="order-number">Order #NJE<?php echo str_pad($order['id'], 6, "0", STR_PAD_LEFT); ?></span>
                                <span class="order-status status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                            <div style="color: var(--doubleColor); margin-bottom: 8px;">
                                <strong><?php echo htmlspecialchars($order['full_name']); ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: var(--tripleColor);">
                                    <?php echo date('d M Y H:i', strtotime($order['created_at'])); ?>
                                </span>
                                <span style="font-weight: bold; color: var(--accentColor);">
                                    Rp<?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    <a href="admin-orders.php" style="display: block; text-align: center; color: var(--secondaryColor); font-weight: bold; margin-top: 15px;">
                        Lihat Semua Pesanan →
                    </a>
                <?php else: ?>
                    <p style="text-align: center; color: var(--doubleColor); padding: 20px;">Belum ada pesanan</p>
                <?php endif; ?>
            </div>

            <!-- Low Stock Products -->
            <div class="dashboard-section">
                <div class="section-header">
                    <i data-feather="alert-triangle"></i>
                    Stok Rendah
                </div>
                <?php if (mysqli_num_rows($low_stock) > 0): ?>
                    <?php while ($product = mysqli_fetch_assoc($low_stock)): ?>
                        <div class="product-item">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-weight: bold; color: var(--secondaryColor); margin-bottom: 5px;">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </div>
                                    <div style="color: var(--doubleColor); font-size: 0.9em;">
                                        <?php echo htmlspecialchars($product['brand'] ?? 'No Brand'); ?>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-weight: bold; color: <?php echo $product['stock'] < 5 ? 'var(--danger)' : 'var(--warning)'; ?>;">
                                        Stok: <?php echo $product['stock']; ?>
                                    </div>
                                    <div style="color: var(--tripleColor); font-size: 0.9em;">
                                        Rp<?php echo number_format($product['price'], 0, ',', '.'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    <a href="admin-product.php" style="display: block; text-align: center; color: var(--secondaryColor); font-weight: bold; margin-top: 15px;">
                        Kelola Produk →
                    </a>
                <?php else: ?>
                    <p style="text-align: center; color: var(--doubleColor); padding: 20px;">Semua produk memiliki stok yang cukup</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="admin-product.php" class="action-btn">
                <i data-feather="plus-circle" style="width: 30px; height: 30px;"></i>
                Kelola Produk
            </a>
            <a href="admin-orders.php" class="action-btn">
                <i data-feather="list" style="width: 30px; height: 30px;"></i>
                Kelola Pesanan
            </a>
            <a href="admin-users.php" class="action-btn">
                <i data-feather="user-plus" style="width: 30px; height: 30px;"></i>
                Kelola Pengguna
            </a>
            <a href="home-login.php" target="_blank" class="action-btn">
                <i data-feather="eye" style="width: 30px; height: 30px;"></i>
                Lihat Website
            </a>
        </div>
    </div>
</div>

<script>
    feather.replace();
</script>
</body>
</html>
