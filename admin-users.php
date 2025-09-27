<?php
session_start();

// Proteksi akses: hanya untuk admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.html");
    exit();
}

require_once "db.php";

// Get users statistics
$stats = [];

// Total users
$sql = "SELECT COUNT(*) as total_users FROM users WHERE role = 'user'";
$result = mysqli_query($conn, $sql);
$stats['total_users'] = mysqli_fetch_assoc($result)['total_users'];

// Total admins
$sql = "SELECT COUNT(*) as total_admins FROM users WHERE role = 'admin'";
$result = mysqli_query($conn, $sql);
$stats['total_admins'] = mysqli_fetch_assoc($result)['total_admins'];

// Users who logged in today
$sql = "SELECT COUNT(*) as today_logins FROM users WHERE DATE(last_login) = CURDATE() AND role = 'user'";
$result = mysqli_query($conn, $sql);
$stats['today_logins'] = mysqli_fetch_assoc($result)['today_logins'];

// Users who logged in this week
$sql = "SELECT COUNT(*) as week_logins FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND role = 'user'";
$result = mysqli_query($conn, $sql);
$stats['week_logins'] = mysqli_fetch_assoc($result)['week_logins'];

// Most active users
$sql = "SELECT full_name, email, login_count, last_login FROM users WHERE role = 'user' ORDER BY login_count DESC LIMIT 10";
$most_active_users = mysqli_query($conn, $sql);

// Recent registrations
$sql = "SELECT full_name, email, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 10";
$recent_users = mysqli_query($conn, $sql);

// Online users (logged in within last 30 minutes)
$sql = "SELECT COUNT(*) as online_users FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 MINUTE) AND role = 'user'";
$result = mysqli_query($conn, $sql);
$stats['online_users'] = mysqli_fetch_assoc($result)['online_users'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - Admin NusaJava Eats</title>
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
            margin-bottom: 40px;
        }
        .stat-card {
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            color: var(--white);
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .stat-card.total {
            background: linear-gradient(135deg, var(--info), #5dade2);
        }
        .stat-card.admins {
            background: linear-gradient(135deg, var(--secondaryColor), var(--doubleColor));
        }
        .stat-card.today {
            background: linear-gradient(135deg, var(--success), #58d68d);
        }
        .stat-card.week {
            background: linear-gradient(135deg, var(--warning), #f7dc6f);
        }
        .stat-card.online {
            background: linear-gradient(135deg, var(--accentColor), #ec7063);
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
        .users-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .users-section {
            background: var(--light-brown);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
            color: var(--secondaryColor);
            font-size: 1.4em;
            font-weight: bold;
            border-bottom: 2px solid var(--mainColor);
            padding-bottom: 10px;
        }
        .user-item {
            background: var(--white);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px;
            border-left: 4px solid var(--mainColor);
            transition: all 0.3s ease;
        }
        .user-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .user-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .user-name {
            font-weight: bold;
            color: var(--secondaryColor);
            font-size: 1.1em;
        }
        .user-email {
            color: var(--doubleColor);
            font-size: 0.9em;
            margin-bottom: 8px;
        }
        .user-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85em;
        }
        .login-count {
            background: var(--info);
            color: var(--white);
            padding: 4px 8px;
            border-radius: 10px;
            font-weight: bold;
        }
        .last-login {
            color: var(--tripleColor);
        }
        .online-indicator {
            width: 8px;
            height: 8px;
            background: var(--success);
            border-radius: 50%;
            display: inline-block;
            margin-left: 5px;
            animation: pulse 2s infinite;
        }
        .offline-indicator {
            width: 8px;
            height: 8px;
            background: #bdc3c7;
            border-radius: 50%;
            display: inline-block;
            margin-left: 5px;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .users-table {
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-top: 30px;
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
        .no-users {
            text-align: center;
            padding: 50px;
            color: var(--doubleColor);
        }
        .no-users i {
            width: 64px;
            height: 64px;
            margin-bottom: 20px;
            color: var(--tripleColor);
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
            .users-sections {
                grid-template-columns: 1fr;
            }
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
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
                <li><a href="admin-users.php" class="active"><i data-feather="users"></i> Pengguna</a></li>
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
                <i data-feather="users"></i>
                Kelola Pengguna
            </h1>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                <div class="stat-label">Total Pengguna</div>
            </div>
            <div class="stat-card admins">
                <div class="stat-number"><?php echo $stats['total_admins']; ?></div>
                <div class="stat-label">Total Admin</div>
            </div>
            <div class="stat-card online">
                <div class="stat-number"><?php echo $stats['online_users']; ?></div>
                <div class="stat-label">Online Sekarang</div>
            </div>
            <div class="stat-card today">
                <div class="stat-number"><?php echo $stats['today_logins']; ?></div>
                <div class="stat-label">Login Hari Ini</div>
            </div>
            <div class="stat-card week">
                <div class="stat-number"><?php echo $stats['week_logins']; ?></div>
                <div class="stat-label">Login Minggu Ini</div>
            </div>
        </div>

        <!-- Users Sections -->
        <div class="users-sections">
            <!-- Most Active Users -->
            <div class="users-section">
                <div class="section-header">
                    <i data-feather="trending-up"></i>
                    Pengguna Teraktif
                </div>
                <?php if (mysqli_num_rows($most_active_users) > 0): ?>
                    <?php while ($user = mysqli_fetch_assoc($most_active_users)): ?>
                        <div class="user-item">
                            <div class="user-header">
                                <span class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></span>
                                <?php
                                $last_login = strtotime($user['last_login']);
                                $now = time();
                                $is_online = ($now - $last_login) < 1800; // 30 minutes
                                ?>
                                <span class="<?php echo $is_online ? 'online-indicator' : 'offline-indicator'; ?>"></span>
                            </div>
                            <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                            <div class="user-stats">
                                <span class="login-count"><?php echo $user['login_count']; ?> login</span>
                                <span class="last-login">
                                    <?php
                                    if ($user['last_login']) {
                                        echo date('d/m/Y H:i', strtotime($user['last_login']));
                                    } else {
                                        echo 'Belum pernah login';
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: var(--doubleColor); padding: 20px;">Belum ada data pengguna aktif</p>
                <?php endif; ?>
            </div>

            <!-- Recent Registrations -->
            <div class="users-section">
                <div class="section-header">
                    <i data-feather="user-plus"></i>
                    Pendaftar Terbaru
                </div>
                <?php if (mysqli_num_rows($recent_users) > 0): ?>
                    <?php while ($user = mysqli_fetch_assoc($recent_users)): ?>
                        <div class="user-item">
                            <div class="user-header">
                                <span class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></span>
                                <span style="background: var(--success); color: var(--white); padding: 4px 8px; border-radius: 10px; font-size: 0.8em;">New</span>
                            </div>
                            <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                            <div class="user-stats">
                                <span style="color: var(--tripleColor);">
                                    Daftar: <?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: var(--doubleColor); padding: 20px;">Belum ada pendaftar baru</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- All Users Table -->
        <div class="users-table">
            <div class="table-header">
                <i data-feather="list"></i>
                Semua Pengguna
            </div>
            <?php
            // Get all users for table
            $all_users_sql = "SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC";
            $all_users_result = mysqli_query($conn, $all_users_sql);
            ?>
            <?php if (mysqli_num_rows($all_users_result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Tanggal Daftar</th>
                        <th>Login Terakhir</th>
                        <th>Total Login</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = mysqli_fetch_assoc($all_users_result)): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td style="font-weight: bold; color: var(--secondaryColor);">
                            <?php echo htmlspecialchars($user['full_name']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <?php
                            if ($user['last_login']) {
                                echo date('d/m/Y H:i', strtotime($user['last_login']));
                            } else {
                                echo '<span style="color: var(--warning);">Belum pernah</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <span style="background: var(--info); color: var(--white); padding: 4px 8px; border-radius: 8px; font-size: 0.9em;">
                                <?php echo $user['login_count']; ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            if ($user['last_login']) {
                                $last_login = strtotime($user['last_login']);
                                $now = time();
                                $is_online = ($now - $last_login) < 1800; // 30 minutes

                                if ($is_online) {
                                    echo '<span style="color: var(--success); font-weight: bold;">● Online</span>';
                                } else {
                                    echo '<span style="color: #bdc3c7;">● Offline</span>';
                                }
                            } else {
                                echo '<span style="color: var(--warning);">● Belum Aktif</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-users">
                <i data-feather="users"></i>
                <h3>Belum Ada Pengguna</h3>
                <p>Pengguna yang mendaftar akan muncul di sini</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    feather.replace();

    // Auto refresh every 30 seconds for online status
    setInterval(() => {
        location.reload();
    }, 30000);
</script>
</body>
</html>
