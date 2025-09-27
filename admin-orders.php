<?php
session_start();

// Proteksi akses: hanya untuk admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.html");
    exit();
}

require_once "db.php";

// Handle status update
if ($_POST && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    $payment_status = $_POST['payment_status'] ?? null;

    if ($payment_status) {
        $sql = "UPDATE orders SET status = ?, payment_status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $new_status, $payment_status, $order_id);
    } else {
        $sql = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $new_status, $order_id);
    }

    if (mysqli_stmt_execute($stmt)) {
        $success = "Status pesanan berhasil diperbarui!";
    } else {
        $error = "Gagal memperbarui status pesanan.";
    }
}

// Get orders with user info
$sql = "SELECT o.*, u.full_name, u.email
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC";
$orders_result = mysqli_query($conn, $sql);

// Get order statistics
$stats = [];
$sql = "SELECT
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_orders,
    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
    SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as paid_revenue
    FROM orders";
$stats_result = mysqli_query($conn, $sql);
$stats = mysqli_fetch_assoc($stats_result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Admin NusaJava Eats</title>
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
        .stat-card.pending {
            background: linear-gradient(135deg, var(--warning), #f7dc6f);
        }
        .stat-card.confirmed {
            background: linear-gradient(135deg, var(--success), #58d68d);
        }
        .stat-card.delivered {
            background: linear-gradient(135deg, var(--secondaryColor), var(--doubleColor));
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
        .orders-table {
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
        .order-id {
            font-weight: bold;
            color: var(--secondaryColor);
        }
        .order-status {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
            display: inline-block;
        }
        .status-pending {
            background: var(--warning);
            color: var(--white);
        }
        .status-confirmed {
            background: var(--info);
            color: var(--white);
        }
        .status-shipped {
            background: var(--tripleColor);
            color: var(--white);
        }
        .status-delivered {
            background: var(--success);
            color: var(--white);
        }
        .status-cancelled {
            background: var(--danger);
            color: var(--white);
        }
        .payment-status {
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: bold;
            display: inline-block;
        }
        .payment-menunggu {
            background: #ffeaa7;
            color: #2d3436;
        }
        .payment-lunas {
            background: #00b894;
            color: var(--white);
        }
        .payment-gagal {
            background: var(--danger);
            color: var(--white);
        }
        .payment-dikembalikan {
            background: #6c5ce7;
            color: var(--white);
        }
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: bold;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .btn-primary {
            background: var(--info);
            color: var(--white);
        }
        .btn-success {
            background: var(--success);
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

        /* Modal styles - COMPLETELY FIXED FOR SCROLLING */
        .modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.6);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: var(--white);
            margin: 2% auto;
            border-radius: 20px;
            width: 95%;
            max-width: 900px;
            max-height: 90vh;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .modal-header {
            background: linear-gradient(45deg, var(--secondaryColor), var(--doubleColor));
            color: var(--white);
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
            border-radius: 20px 20px 0 0;
        }

        .modal-header h2 {
            font-size: 1.8em;
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0;
        }

        .close {
            color: var(--white);
            font-size: 32px;
            font-weight: bold;
            cursor: pointer;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .close:hover {
            background: rgba(255,255,255,0.2);
            transform: scale(1.1);
        }

        /* Scrollable content area */
        .modal-body {
            flex: 1;
            overflow-y: auto;
            padding: 30px;
            background: var(--white);
        }

        .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: var(--tripleColor);
            border-radius: 4px;
        }

        .modal-body::-webkit-scrollbar-thumb:hover {
            background: var(--accentColor);
        }

        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: var(--secondaryColor);
            font-size: 1.1em;
        }
        .form-group select {
            width: 100%;
            padding: 15px;
            border: 2px solid var(--light-brown);
            border-radius: 10px;
            font-size: 16px;
            background: var(--white);
            transition: border-color 0.3s ease;
        }
        .form-group select:focus {
            outline: none;
            border-color: var(--mainColor);
            box-shadow: 0 0 10px rgba(254, 186, 113, 0.3);
        }
        .alert {
            padding: 18px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }
        .alert-success {
            background: linear-gradient(45deg, #d4edda, #c3e6cb);
            color: #155724;
            border: 2px solid #28a745;
        }
        .alert-error {
            background: linear-gradient(45deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border: 2px solid #dc3545;
        }
        .order-details {
            background: var(--light-brown);
            padding: 25px;
            border-radius: 15px;
            margin-top: 20px;
            border: 2px solid var(--tripleColor);
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(91, 38, 32, 0.1);
        }
        .detail-row:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 1.2em;
            color: var(--accentColor);
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
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
            table {
                font-size: 0.9em;
            }
            .action-buttons {
                flex-direction: column;
            }
            .modal-content {
                width: 98%;
                margin: 1% auto;
                max-height: 95vh;
            }
            .modal-body {
                padding: 20px 15px;
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
                <li><a href="admin-orders.php"class="active"><i data-feather="shopping-bag"></i> Pesanan</a></li>
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
                <i data-feather="shopping-bag"></i>
                Kelola Pesanan
            </h1>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i data-feather="check-circle"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i data-feather="alert-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-number"><?php echo $stats['total_orders']; ?></div>
                <div class="stat-label">Total Pesanan</div>
            </div>
            <div class="stat-card pending">
                <div class="stat-number"><?php echo $stats['pending_orders']; ?></div>
                <div class="stat-label">Menunggu Konfirmasi</div>
            </div>
            <div class="stat-card confirmed">
                <div class="stat-number"><?php echo $stats['confirmed_orders']; ?></div>
                <div class="stat-label">Dikonfirmasi</div>
            </div>
            <div class="stat-card delivered">
                <div class="stat-number"><?php echo $stats['delivered_orders']; ?></div>
                <div class="stat-label">Terkirim</div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="orders-table">
            <div class="table-header">
                <i data-feather="list"></i>
                Daftar Pesanan
            </div>
            <?php if (mysqli_num_rows($orders_result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status Pesanan</th>
                        <th>Status Pembayaran</th>
                        <th>Metode Bayar</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                    <tr>
                        <td class="order-id">NJE<?php echo str_pad($order['id'], 6, "0", STR_PAD_LEFT); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($order['full_name']); ?></strong><br>
                            <small style="color: var(--doubleColor);"><?php echo htmlspecialchars($order['email']); ?></small>
                        </td>
                        <td style="font-weight: bold; color: var(--accentColor);">
                            Rp<?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                        </td>
                        <td>
                            <span class="order-status status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="payment-status payment-<?php echo $order['payment_status'] ?? 'pending'; ?>">
                                <?php echo ucfirst($order['payment_status'] ?? 'Pending'); ?>
                            </span>
                        </td>
                        <td><?php echo strtoupper($order['payment_method'] ?? 'COD'); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button onclick="viewOrder(<?php echo $order['id']; ?>)" class="btn btn-primary">
                                    <i data-feather="eye"></i> Detail
                                </button>
                                <button onclick="updateStatus(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>', '<?php echo $order['payment_status'] ?? 'pending'; ?>')" class="btn btn-success">
                                    <i data-feather="edit"></i> Update
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div style="text-align: center; padding: 50px; color: var(--doubleColor);">
                <i data-feather="inbox" style="width: 64px; height: 64px; margin-bottom: 20px;"></i>
                <h3>Belum Ada Pesanan</h3>
                <p>Pesanan dari customer akan muncul di sini</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div id="updateModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i data-feather="edit"></i> Update Status Pesanan</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <form method="POST" id="updateForm">
                <input type="hidden" name="update_status" value="1">
                <input type="hidden" name="order_id" id="updateOrderId">

                <div class="form-group">
                    <label for="updateOrderStatus">Status Pesanan:</label>
                    <select name="status" id="updateOrderStatus" required>
                        <option value="pending">Pending - Menunggu Konfirmasi</option>
                        <option value="confirmed">Confirmed - Dikonfirmasi</option>
                        <option value="shipped">Shipped - Sedang Dikirim</option>
                        <option value="delivered">Delivered - Terkirim</option>
                        <option value="cancelled">Cancelled - Dibatalkan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="updatePaymentStatus">Status Pembayaran:</label>
                    <select name="payment_status" id="updatePaymentStatus" required>
                        <option value="menunggu">Menunggu - Belum Bayar</option>
                        <option value="lunas">Lunas - Sudah Bayar</option>
                        <option value="gagal">Gagal - Pembayaran Gagal</option>
                        <option value="dikembalikan">Dikembalikan - Uang Dikembalikan</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success" style="width: 100%; padding: 15px; font-size: 1.1em;">
                    <i data-feather="check"></i> Update Status
                </button>
            </form>
        </div>
    </div>
</div>

<!-- View Order Modal - COMPLETELY FIXED FOR SCROLLING -->
<div id="viewModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i data-feather="eye"></i> Detail Pesanan</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body" id="orderDetailsContent">
            <!-- Order details will be loaded here with proper scrolling -->
        </div>
    </div>
</div>

<script>
    feather.replace();

    // Modal functionality - ENHANCED VERSION
    const updateModal = document.getElementById('updateModal');
    const viewModal = document.getElementById('viewModal');
    const closeBtns = document.getElementsByClassName('close');

    // Close button functionality
    for (let i = 0; i < closeBtns.length; i++) {
        closeBtns[i].onclick = function() {
            updateModal.style.display = 'none';
            viewModal.style.display = 'none';
            document.body.style.overflow = 'auto'; // Re-enable body scroll
        }
    }

    // Click outside modal to close
    window.onclick = function(event) {
        if (event.target == updateModal) {
            updateModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        if (event.target == viewModal) {
            viewModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    function updateStatus(orderId, currentStatus, currentPaymentStatus) {
        document.getElementById('updateOrderId').value = orderId;
        document.getElementById('updateOrderStatus').value = currentStatus;
        document.getElementById('updatePaymentStatus').value = currentPaymentStatus;
        updateModal.style.display = 'block';
        document.body.style.overflow = 'hidden'; // Disable body scroll when modal open
        feather.replace();
    }

    function viewOrder(orderId) {
        // Show loading state
        document.getElementById('orderDetailsContent').innerHTML = `
            <div style="text-align: center; padding: 50px; color: var(--doubleColor);">
                <div style="font-size: 2em; margin-bottom: 20px;">⏳</div>
                <h3>Memuat detail pesanan...</h3>
                <p>Mohon tunggu sebentar</p>
            </div>
        `;

        viewModal.style.display = 'block';
        document.body.style.overflow = 'hidden'; // Disable body scroll when modal open

        // Fetch order details via AJAX
        fetch(`get_order_details.php?id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('orderDetailsContent').innerHTML = data.html;
                    feather.replace();
                } else {
                    document.getElementById('orderDetailsContent').innerHTML = `
                        <div style="text-align: center; padding: 50px; color: var(--danger);">
                            <div style="font-size: 2em; margin-bottom: 20px;">⚠️</div>
                            <h3>Gagal Memuat Detail</h3>
                            <p>Terjadi kesalahan saat memuat detail pesanan</p>
                            <button onclick="viewOrder(${orderId})" class="btn btn-primary" style="margin-top: 15px;">
                                <i data-feather="refresh-cw"></i> Coba Lagi
                            </button>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('orderDetailsContent').innerHTML = `
                    <div style="text-align: center; padding: 50px; color: var(--danger);">
                        <div style="font-size: 2em; margin-bottom: 20px;">❌</div>
                        <h3>Koneksi Bermasalah</h3>
                        <p>Tidak dapat terhubung ke server. Periksa koneksi internet Anda.</p>
                        <button onclick="viewOrder(${orderId})" class="btn btn-primary" style="margin-top: 15px;">
                            <i data-feather="refresh-cw"></i> Coba Lagi
                        </button>
                    </div>
                `;
            });
    }

    // Keyboard navigation for modals
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            updateModal.style.display = 'none';
            viewModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });

    // Initialize feather icons on page load
    document.addEventListener('DOMContentLoaded', function() {
        feather.replace();
    });
</script>
</body>
</html>
