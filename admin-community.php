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
            case 'delete_post':
                $post_id = (int)$_POST['post_id'];
                $sql = "DELETE FROM community_posts WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $post_id);
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Post berhasil dihapus!";
                    $message_type = 'success';
                } else {
                    $message = "Gagal menghapus post.";
                    $message_type = 'error';
                }
                break;

            case 'delete_comment':
                $comment_id = (int)$_POST['comment_id'];
                $sql = "DELETE FROM community_comments WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $comment_id);
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Komentar berhasil dihapus!";
                    $message_type = 'success';
                } else {
                    $message = "Gagal menghapus komentar.";
                    $message_type = 'error';
                }
                break;

            case 'update_post_status':
                $post_id = (int)$_POST['post_id'];
                $status = mysqli_real_escape_string($conn, $_POST['status']);
                $sql = "UPDATE community_posts SET status = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "si", $status, $post_id);
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Status post berhasil diupdate!";
                    $message_type = 'success';
                } else {
                    $message = "Gagal mengupdate status post.";
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';

// Get community posts with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$sql = "SELECT cp.*, u.full_name as author_name, u.email as author_email,
        (SELECT COUNT(*) FROM community_comments cc WHERE cc.post_id = cp.id) as comment_count
        FROM community_posts cp
        JOIN users u ON cp.author_id = u.id";

$where_conditions = array();
if ($filter !== 'all') {
    $where_conditions[] = "cp.status = '" . mysqli_real_escape_string($conn, $filter) . "'";
}
if ($category_filter !== 'all') {
    $where_conditions[] = "cp.category = '" . mysqli_real_escape_string($conn, $category_filter) . "'";
}

if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}

$sql .= " ORDER BY cp.created_at DESC LIMIT $per_page OFFSET $offset";
$posts_result = mysqli_query($conn, $sql);

// Get total posts for pagination
$count_sql = str_replace("SELECT cp.*, u.full_name as author_name, u.email as author_email,
        (SELECT COUNT(*) FROM community_comments cc WHERE cc.post_id = cp.id) as comment_count", "SELECT COUNT(*) as total", $sql);
$count_sql = str_replace("LIMIT $per_page OFFSET $offset", "", $count_sql);
$count_result = mysqli_query($conn, $count_sql);
$total_posts = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_posts / $per_page);

// Get statistics
$stats_sql = "SELECT
    COUNT(*) as total_posts,
    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_posts,
    COUNT(CASE WHEN status = 'hidden' THEN 1 END) as hidden_posts,
    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as posts_this_week
    FROM community_posts";
$stats_result = mysqli_query($conn, $stats_sql);
$stats = mysqli_fetch_assoc($stats_result);

$comments_stats_sql = "SELECT COUNT(*) as total_comments FROM community_comments";
$comments_stats_result = mysqli_query($conn, $comments_stats_sql);
$comments_stats = mysqli_fetch_assoc($comments_stats_result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Komunitas - NusaJava Eats</title>
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

        .posts-table {
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
            grid-template-columns: 3fr 1fr 1fr 1fr 1fr 2fr;
            gap: 15px;
            font-weight: bold;
        }

        .post-row {
            padding: 20px;
            display: grid;
            grid-template-columns: 3fr 1fr 1fr 1fr 1fr 2fr;
            gap: 15px;
            border-bottom: 1px solid var(--light-brown);
            align-items: center;
        }

        .post-row:hover {
            background: var(--light-brown);
        }

        .post-title {
            font-weight: 600;
            color: var(--secondaryColor);
        }

        .post-meta {
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

        .status-active {
            background: var(--success);
            color: white;
        }

        .status-hidden {
            background: var(--warning);
            color: white;
        }

        .status-deleted {
            background: var(--danger);
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
            .post-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            .filters {
                flex-direction: column;
                align-items: stretch;
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
                <li><a href="admin.php"><i data-feather="pie-chart"></i> Dashboard</a></li>
                <li><a href="admin-product.php"><i data-feather="package"></i> Produk</a></li>
                <li><a href="admin-orders.php"><i data-feather="shopping-bag"></i> Pesanan</a></li>
                <li><a href="admin-users.php"><i data-feather="users"></i> Pengguna</a></li>
                <li><a href="admin-recipes.php"><i data-feather="book-open"></i> Resep</a></li>
                <li><a href="admin-community.php" class="active"><i data-feather="message-circle"></i> Komunitas</a></li>
                <li><a href="admin-contacts.php"><i data-feather="mail"></i> Kontak</a></li>
                <li><a href="home-login.php" target="_blank"><i data-feather="external-link"></i> Lihat Website</a></li>
                <li><a href="logout.php" onclick="return confirm('Yakin ingin logout?')"><i data-feather="log-out"></i> Logout</a></li>
            </ul>

    </div>

    <div class="content">
        <div class="content-header">
            <h1>
                <i data-feather="users"></i>
                Kelola Komunitas
            </h1>
            <p>Kelola post dan komentar komunitas</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <i data-feather="<?php echo $message_type === 'success' ? 'check-circle' : 'alert-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-icon"><i data-feather="message-square"></i></div>
                <div class="stat-number"><?php echo $stats['total_posts']; ?></div>
                <div class="stat-label">Total Post</div>
            </div>
            <div class="stat-card success">
                <div class="stat-icon"><i data-feather="eye"></i></div>
                <div class="stat-number"><?php echo $stats['active_posts']; ?></div>
                <div class="stat-label">Post Aktif</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon"><i data-feather="eye-off"></i></div>
                <div class="stat-number"><?php echo $stats['hidden_posts']; ?></div>
                <div class="stat-label">Post Tersembunyi</div>
            </div>
            <div class="stat-card danger">
                <div class="stat-icon"><i data-feather="message-circle"></i></div>
                <div class="stat-number"><?php echo $comments_stats['total_comments']; ?></div>
                <div class="stat-label">Total Komentar</div>
            </div>
            <div class="stat-card info">
                <div class="stat-icon"><i data-feather="calendar"></i></div>
                <div class="stat-number"><?php echo $stats['posts_this_week']; ?></div>
                <div class="stat-label">Post Minggu Ini</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters">
            <div class="filter-group">
                <label>Status:</label>
                <select onchange="updateFilter('filter', this.value)">
                    <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>Semua</option>
                    <option value="active" <?php echo $filter === 'active' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="hidden" <?php echo $filter === 'hidden' ? 'selected' : ''; ?>>Tersembunyi</option>
                    <option value="deleted" <?php echo $filter === 'deleted' ? 'selected' : ''; ?>>Dihapus</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Kategori:</label>
                <select onchange="updateFilter('category', this.value)">
                    <option value="all" <?php echo $category_filter === 'all' ? 'selected' : ''; ?>>Semua</option>
                    <option value="recipe" <?php echo $category_filter === 'recipe' ? 'selected' : ''; ?>>Resep</option>
                    <option value="tips" <?php echo $category_filter === 'tips' ? 'selected' : ''; ?>>Tips</option>
                    <option value="discussion" <?php echo $category_filter === 'discussion' ? 'selected' : ''; ?>>Diskusi</option>
                    <option value="review" <?php echo $category_filter === 'review' ? 'selected' : ''; ?>>Review</option>
                </select>
            </div>
        </div>

        <!-- Posts Table -->
        <div class="posts-table">
            <div class="table-header">
                <div>Post</div>
                <div>Kategori</div>
                <div>Status</div>
                <div>Komentar</div>
                <div>Tanggal</div>
                <div>Aksi</div>
            </div>

            <?php if (mysqli_num_rows($posts_result) > 0): ?>
                <?php while ($post = mysqli_fetch_assoc($posts_result)): ?>
                    <div class="post-row">
                        <div>
                            <div class="post-title"><?php echo htmlspecialchars($post['title']); ?></div>
                            <div class="post-meta">
                                Oleh: <?php echo htmlspecialchars($post['author_name']); ?>
                                (<?php echo htmlspecialchars($post['author_email']); ?>)
                            </div>
                        </div>
                        <div><?php echo ucfirst($post['category']); ?></div>
                        <div>
                            <span class="status-badge status-<?php echo $post['status']; ?>">
                                <?php echo ucfirst($post['status']); ?>
                            </span>
                        </div>
                        <div><?php echo $post['comment_count']; ?></div>
                        <div><?php echo date('d/m/Y', strtotime($post['created_at'])); ?></div>
                        <div class="action-buttons">
                            <?php if ($post['status'] === 'active'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="update_post_status">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <input type="hidden" name="status" value="hidden">
                                    <button type="submit" class="btn btn-warning" onclick="return confirm('Sembunyikan post ini?')">
                                        <i data-feather="eye-off"></i> Sembunyikan
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="update_post_status">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <input type="hidden" name="status" value="active">
                                    <button type="submit" class="btn btn-success">
                                        <i data-feather="eye"></i> Tampilkan
                                    </button>
                                </form>
                            <?php endif; ?>

                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="delete_post">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Hapus post ini secara permanen?')">
                                    <i data-feather="trash-2"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="padding: 40px; text-align: center; color: var(--doubleColor);">
                    <i data-feather="inbox" style="width: 3em; height: 3em; margin-bottom: 20px;"></i>
                    <h3>Tidak ada post ditemukan</h3>
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

<script>
    feather.replace();

    function updateFilter(type, value) {
        const url = new URL(window.location);
        url.searchParams.set(type, value);
        url.searchParams.set('page', '1'); // Reset to first page
        window.location.href = url.toString();
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
