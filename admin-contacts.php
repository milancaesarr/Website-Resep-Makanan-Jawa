<?php
session_start();

// Proteksi akses: hanya untuk admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.html");
    exit();
}

require_once "db.php";

$message = '';
$message_type = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete_contact':
                $contact_id = (int)$_POST['contact_id'];
                $sql = "DELETE FROM contacts WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $contact_id);
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Pesan berhasil dihapus!";
                    $message_type = 'success';
                } else {
                    $message = "Gagal menghapus pesan.";
                    $message_type = 'error';
                }
                break;

            case 'update_status':
                $contact_id = (int)$_POST['contact_id'];
                $status = mysqli_real_escape_string($conn, $_POST['status']);
                $sql = "UPDATE contacts SET status = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "si", $status, $contact_id);
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Status berhasil diupdate!";
                    $message_type = 'success';
                } else {
                    $message = "Gagal mengupdate status.";
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';

// Get contacts with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

$sql = "SELECT * FROM contacts";

$where_conditions = array();
if ($filter !== 'all') {
    $where_conditions[] = "status = '" . mysqli_real_escape_string($conn, $filter) . "'";
}
if ($category_filter !== 'all') {
    $where_conditions[] = "category = '" . mysqli_real_escape_string($conn, $category_filter) . "'";
}

if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}

$sql .= " ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$contacts_result = mysqli_query($conn, $sql);

// Get total contacts for pagination
$count_sql = str_replace("SELECT *", "SELECT COUNT(*) as total", $sql);
$count_sql = str_replace("LIMIT $per_page OFFSET $offset", "", $count_sql);
$count_result = mysqli_query($conn, $count_sql);
$total_contacts = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_contacts / $per_page);

// Get statistics
$stats_sql = "SELECT
    COUNT(*) as total_contacts,
    COUNT(CASE WHEN status = 'new' THEN 1 END) as new_contacts,
    COUNT(CASE WHEN status = 'read' THEN 1 END) as read_contacts,
    COUNT(CASE WHEN status = 'replied' THEN 1 END) as replied_contacts,
    COUNT(CASE WHEN status = 'closed' THEN 1 END) as closed_contacts,
    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as contacts_this_week
    FROM contacts";
$stats_result = mysqli_query($conn, $stats_sql);
$stats = mysqli_fetch_assoc($stats_result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Kontak - NusaJava Eats</title>
    <script src="https://unpkg.com/feather-icons"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />

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
            overflow-y: auto;
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

        .filters {
            background: var(--light-brown);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            font-weight: 600;
            color: var(--secondaryColor);
        }

        .filter-group select {
            padding: 8px 12px;
            border: 2px solid var(--tripleColor);
            border-radius: 8px;
            background: var(--white);
        }

        .contacts-table {
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .table-header {
            background: var(--secondaryColor);
            color: var(--white);
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr 1fr 2fr;
            gap: 15px;
            font-weight: bold;
        }

        .contact-row {
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr 1fr 2fr;
            gap: 15px;
            border-bottom: 1px solid var(--light-brown);
            align-items: center;
        }

        .contact-row:hover {
            background: var(--light-brown);
        }

        .contact-name {
            font-weight: 600;
            color: var(--secondaryColor);
        }

        .contact-meta {
            font-size: 0.9em;
            color: var(--doubleColor);
            margin-top: 5px;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
            text-align: center;
        }

        .status-new {
            background: var(--info);
            color: white;
        }

        .status-read {
            background: var(--warning);
            color: white;
        }

        .status-replied {
            background: var(--success);
            color: white;
        }

        .status-closed {
            background: var(--danger);
            color: white;
        }

        .category-badge {
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: 600;
            background: var(--tripleColor);
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8em;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-primary {
            background: var(--info);
            color: white;
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-warning {
            background: var(--warning);
            color: white;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }

        .pagination a {
            padding: 8px 16px;
            background: var(--white);
            color: var(--secondaryColor);
            text-decoration: none;
            border-radius: 8px;
            border: 2px solid var(--tripleColor);
            transition: all 0.3s ease;
        }

        .pagination a:hover,
        .pagination a.active {
            background: var(--accentColor);
            color: var(--white);
            border-color: var(--accentColor);
        }

        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }

        .message.success {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success);
            border: 2px solid var(--success);
        }

        .message.error {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger);
            border: 2px solid var(--danger);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: var(--white);
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-brown);
        }

        .modal-header h2 {
            color: var(--secondaryColor);
            margin: 0;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: var(--danger);
        }

        .modal-body {
            line-height: 1.6;
        }

        .modal-body h3 {
            color: var(--secondaryColor);
            margin-bottom: 10px;
        }

        .modal-body p {
            margin-bottom: 15px;
            color: var(--doubleColor);
        }

        .contact-details {
            background: var(--light-brown);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .contact-details .detail-item {
            display: flex;
            margin-bottom: 10px;
        }

        .contact-details .detail-label {
            font-weight: 600;
            color: var(--secondaryColor);
            min-width: 120px;
        }

        .contact-message {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            border: 2px solid var(--light-brown);
            white-space: pre-wrap;
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

            .table-header,
            .contact-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .filters {
                flex-direction: column;
                align-items: stretch;
            }

            .stats-grid {
                grid-template-columns: 1fr;
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
                <li><a href="admin-recipes.php"><i data-feather="book-open"></i> Resep</a></li>
                <li><a href="admin-community.php"><i data-feather="message-circle"></i> Komunitas</a></li>
                <li><a href="admin-contacts.php" class="active"><i data-feather="mail"></i> Kontak</a></li>
                <li><a href="home-login.php" target="_blank"><i data-feather="external-link"></i> Lihat Website</a></li>
                <li><a href="logout.php" onclick="return confirm('Yakin ingin logout?')"><i data-feather="log-out"></i> Logout</a></li>
            </ul>

    </div>

        <div class="content">
            <div class="content-header">
                <h1>
                    <i class="fas fa-envelope"></i>
                    Kelola Kontak
                </h1>
                <p>Kelola pesan kontak dari pengguna</p>
            </div>

            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_contacts']; ?></div>
                    <div class="stat-label">Total Pesan</div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-number"><?php echo $stats['new_contacts']; ?></div>
                    <div class="stat-label">Pesan Baru</div>
                </div>
                <div class="stat-card success">
                    <div class="stat-number"><?php echo $stats['replied_contacts']; ?></div>
                    <div class="stat-label">Sudah Dibalas</div>
                </div>
                <div class="stat-card danger">
                    <div class="stat-number"><?php echo $stats['closed_contacts']; ?></div>
                    <div class="stat-label">Ditutup</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['contacts_this_week']; ?></div>
                    <div class="stat-label">Pesan Minggu Ini</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters">
                <div class="filter-group">
                    <label>Status:</label>
                    <select onchange="updateFilter('filter', this.value)">
                        <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>Semua</option>
                        <option value="new" <?php echo $filter === 'new' ? 'selected' : ''; ?>>Baru</option>
                        <option value="read" <?php echo $filter === 'read' ? 'selected' : ''; ?>>Dibaca</option>
                        <option value="replied" <?php echo $filter === 'replied' ? 'selected' : ''; ?>>Dibalas</option>
                        <option value="closed" <?php echo $filter === 'closed' ? 'selected' : ''; ?>>Ditutup</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Kategori:</label>
                    <select onchange="updateFilter('category', this.value)">
                        <option value="all" <?php echo $category_filter === 'all' ? 'selected' : ''; ?>>Semua</option>
                        <option value="general" <?php echo $category_filter === 'general' ? 'selected' : ''; ?>>Umum</option>
                        <option value="recipe" <?php echo $category_filter === 'recipe' ? 'selected' : ''; ?>>Resep</option>
                        <option value="product" <?php echo $category_filter === 'product' ? 'selected' : ''; ?>>Produk</option>
                        <option value="order" <?php echo $category_filter === 'order' ? 'selected' : ''; ?>>Pesanan</option>
                        <option value="partnership" <?php echo $category_filter === 'partnership' ? 'selected' : ''; ?>>Kerjasama</option>
                        <option value="complaint" <?php echo $category_filter === 'complaint' ? 'selected' : ''; ?>>Keluhan</option>
                        <option value="suggestion" <?php echo $category_filter === 'suggestion' ? 'selected' : ''; ?>>Saran</option>
                    </select>
                </div>
            </div>

            <!-- Contacts Table -->
            <div class="contacts-table">
                <div class="table-header">
                    <div>Pengirim</div>
                    <div>Subjek</div>
                    <div>Kategori</div>
                    <div>Status</div>
                    <div>Tanggal</div>
                    <div>Aksi</div>
                </div>

                <?php if (mysqli_num_rows($contacts_result) > 0): ?>
                    <?php while ($contact = mysqli_fetch_assoc($contacts_result)): ?>
                        <div class="contact-row">
                            <div>
                                <div class="contact-name"><?php echo htmlspecialchars($contact['name']); ?></div>
                                <div class="contact-meta"><?php echo htmlspecialchars($contact['email']); ?></div>
                            </div>
                            <div><?php echo htmlspecialchars($contact['subject']); ?></div>
                            <div>
                                <span class="category-badge">
                                    <?php
                                    $categories = [
                                        'general' => 'Umum',
                                        'recipe' => 'Resep',
                                        'product' => 'Produk',
                                        'order' => 'Pesanan',
                                        'partnership' => 'Kerjasama',
                                        'complaint' => 'Keluhan',
                                        'suggestion' => 'Saran'
                                    ];
                                    echo $categories[$contact['category']] ?? $contact['category'];
                                    ?>
                                </span>
                            </div>
                            <div>
                                <span class="status-badge status-<?php echo $contact['status']; ?>">
                                    <?php
                                    $statuses = [
                                        'new' => 'Baru',
                                        'read' => 'Dibaca',
                                        'replied' => 'Dibalas',
                                        'closed' => 'Ditutup'
                                    ];
                                    echo $statuses[$contact['status']] ?? $contact['status'];
                                    ?>
                                </span>
                            </div>
                            <div><?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?></div>
                            <div class="action-buttons">
                                <button class="btn btn-primary" onclick="viewContact(<?php echo $contact['id']; ?>, '<?php echo addslashes($contact['name']); ?>', '<?php echo addslashes($contact['email']); ?>', '<?php echo addslashes($contact['subject']); ?>', '<?php echo addslashes($contact['message']); ?>', '<?php echo $contact['category']; ?>', '<?php echo $contact['created_at']; ?>')">
                                    <i class="fas fa-eye"></i> Lihat
                                </button>

                                <?php if ($contact['status'] === 'new'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                                        <input type="hidden" name="status" value="read">
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-check"></i> Tandai Dibaca
                                        </button>
                                    </form>
                                <?php elseif ($contact['status'] === 'read'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                                        <input type="hidden" name="status" value="replied">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-reply"></i> Tandai Dibalas
                                        </button>
                                    </form>
                                <?php elseif ($contact['status'] === 'replied'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                                        <input type="hidden" name="status" value="closed">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-times"></i> Tutup
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete_contact">
                                    <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Hapus pesan ini secara permanen?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="padding: 40px; text-align: center; color: var(--doubleColor);">
                        <i class="fas fa-inbox" style="font-size: 3em; margin-bottom: 20px;"></i>
                        <h3>Tidak ada pesan ditemukan</h3>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>&category=<?php echo $category_filter; ?>"
                           class="<?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal for viewing contact details -->
    <div id="contactModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-envelope"></i> Detail Pesan Kontak</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="contact-details">
                    <div class="detail-item">
                        <span class="detail-label">Nama:</span>
                        <span id="modal-name"></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email:</span>
                        <span id="modal-email"></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Subjek:</span>
                        <span id="modal-subject"></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Kategori:</span>
                        <span id="modal-category"></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Tanggal:</span>
                        <span id="modal-date"></span>
                    </div>
                </div>
                <h3>Pesan:</h3>
                <div class="contact-message" id="modal-message"></div>
            </div>
        </div>
    </div>

    <script>
        feather.replace();

        function updateFilter(type, value) {
            const url = new URL(window.location);
            url.searchParams.set(type, value);
            url.searchParams.set('page', '1'); // Reset to first page
            window.location.href = url.toString();
        }

        function viewContact(id, name, email, subject, message, category, date) {
            document.getElementById('modal-name').textContent = name;
            document.getElementById('modal-email').textContent = email;
            document.getElementById('modal-subject').textContent = subject;
            document.getElementById('modal-message').textContent = message;

            const categories = {
                'general': 'Umum',
                'recipe': 'Resep',
                'product': 'Produk',
                'order': 'Pesanan',
                'partnership': 'Kerjasama',
                'complaint': 'Keluhan',
                'suggestion': 'Saran'
            };
            document.getElementById('modal-category').textContent = categories[category] || category;

            const formattedDate = new Date(date).toLocaleString('id-ID');
            document.getElementById('modal-date').textContent = formattedDate;

            document.getElementById('contactModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('contactModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('contactModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Auto hide success message
        const message = document.querySelector('.message.success');
        if (message) {
            setTimeout(() => {
                message.style.opacity = '0';
                message.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    message.remove();
                }, 300);
            }, 5000);
        }
    </script>
</body>
</html>
