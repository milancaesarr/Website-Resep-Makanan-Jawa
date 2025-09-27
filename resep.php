<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}
require_once "db.php";

// Update login tracking
$user_id = $_SESSION['user_id'];
$update_login_sql = "UPDATE users SET last_login = NOW(), login_count = login_count + 1 WHERE id = ?";
$stmt = mysqli_prepare($conn, $update_login_sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

// Filter dan search logic untuk resep
$region_filter = isset($_GET['region']) ? $_GET['region'] : '';
$search = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';

// Build SQL query dengan filter dan search
$sql = "SELECT * FROM recipes WHERE 1=1";
$params = array();

// Apply region filter
if (!empty($region_filter)) {
    $sql .= " AND region = ?";
    $params[] = $region_filter;
}

// Apply search filter
if (!empty($search)) {
    $sql .= " AND (LOWER(name) LIKE ? OR LOWER(description) LIKE ? OR LOWER(region) LIKE ?)";
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

// Order by ID ASC - resep pertama yang ditambahkan muncul di atas
$sql .= " ORDER BY id ASC";

// Prepare and execute statement
$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get unique regions for filter
$region_sql = "SELECT DISTINCT region FROM recipes WHERE region IS NOT NULL AND region != '' ORDER BY region";
$region_result = mysqli_query($conn, $region_sql);
$regions = array();
while ($region_row = mysqli_fetch_assoc($region_result)) {
    $regions[] = $region_row['region'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resep Nusantara - NusaJava Eats</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
    <script src="https://unpkg.com/feather-icons"></script>

    <style>
        :root {
            --mainColor: #feba71;
            --secondaryColor: #5b2620;
            --accentColor: #af2d2d;
            --doubleColor: #816040;
            --tripleColor: #a7774e;
            --tambahColor: #222;
            --white: #ffffff;
            --light-brown: #f4e4d0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--mainColor);
            padding-top: 80px;
        }

        main {
            margin-top: 0px;
            padding: 80px 20px;
        }

        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
        }

        .Header-text {
            font-size: 1.9rem;
            color: #222;
            margin-bottom: 20px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .search-bar {
            display: flex;
            justify-content: center;
            align-items: center;
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 2px 8px 0 rgba(31,31,31,0.06);
            max-width: 400px;
            margin: 0 auto 18px auto;
            padding: 4px 16px;
        }

        .search-bar input {
            flex: 1;
            border: none;
            font-size: 1rem;
            padding: 12px 6px;
            background: none;
            outline: none;
        }

        .search-bar button {
            background: none;
            border: none;
            outline: none;
            cursor: pointer;
            padding: 0 8px;
            color: #666;
        }

        .filter-button {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 8px;
        }

        .filter-button button, .filter-button a {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ff983e;
            color: #432a0b;
            font-size: 0.95rem;
            padding: 6px 16px;
            margin: 0 3px 5px 3px;
            border-radius: 16px;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
            text-align: center;
            white-space: nowrap;
            border: none;
            text-decoration: none;
            min-width: 120px;
        }

        .filter-button button:hover, .filter-button a:hover {
            background: #ffa95c;
            color: #2e1c06;
        }

        .filter-button button.active, .filter-button a.active {
            background-color: #a7774e;
            color: white;
            border-color: #2e1c06;
        }

        .container-resep {
            max-width: 1240px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px 32px;
            justify-content: center;
            align-items: flex-start;
        }

        .card {
            background-color: #a7774e;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(60, 60, 60, 0.11);
            width: 365px;
            min-width: 280px;
            flex-shrink: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .card-img {
            width: 100%;
            height: 185px;
            object-fit: cover;
            background: #eee;
            display: block;
        }

        .card-content {
            padding: 17px 18px 22px 18px;
            display: flex;
            flex-direction: column;
            gap: 9px;
            background: #a7774e;
            height: 100%;
            width: 100%;
        }

        .location {
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.2px;
            color: #fffaf3;
            opacity: 0.95;
            margin-bottom: 0.5px;
            margin-top: 2px;
            text-align: center;
        }

        .food-name {
            font-size: 1.25rem;
            font-weight: bold;
            color: #252525;
            text-align: center;
            margin: 0;
            margin-bottom: 6px;
            line-height: 1.13;
        }

        .card p {
            color: #fff;
            font-weight: 500;
            text-align: center;
            line-height: 1.4;
        }

        .details-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin: 5px 35px 0px;
        }

        .detail {
            display: flex;
            align-items: center;
            font-size: 1.04rem;
            color: #ffd5c2;
            gap: 7px;
        }

        .detail .fa-clock {
            color: #ff837d;
            font-size: 1.07em;
            margin-right: 3px;
        }

        .detail .fa-star {
            color: #ffc246;
            font-size: 1.09em;
            margin-right: 3px;
        }

        .lihat-btn {
            margin: 13px auto 0 auto;
            padding: 7px 26px;
            outline: none;
            background: #e9a06e;
            color: #432a0b;
            font-weight: 600;
            font-size: 1rem;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: background 0.16s;
            text-decoration: none;
            box-shadow: 0 2px 8px #0002;
            display: inline-block;
        }

        .lihat-btn:hover {
            background: #f9bc93;
        }

        .no-recipes {
            grid-column: 1/-1;
            text-align: center;
            padding: 60px 20px;
            color: var(--doubleColor);
        }

        .no-recipes h3 {
            color: var(--secondaryColor);
            font-size: 2em;
            margin-bottom: 15px;
        }

        .no-recipes p {
            font-size: 1.1em;
            margin-bottom: 20px;
        }

        .filter-info {
            margin-bottom: 30px;
            text-align: center;
        }

        .filter-info span {
            color: var(--doubleColor);
        }

        .filter-info a {
            color: var(--accentColor);
            margin-left: 10px;
            text-decoration: underline;
        }

        @media (max-width: 1100px) {
            .container-resep {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 780px) {
            .container-resep {
                grid-template-columns: 1fr;
                gap: 20px;
                padding: 0 7px;
            }

            .Header-text {
                font-size: 1.8em;
            }

            .search-bar {
                flex-direction: column;
                gap: 10px;
            }

            .search-bar input,
            .search-bar button {
                border-radius: 25px;
            }

            .filter-button {
                gap: 10px;
            }
        }
    </style>
</head>

<body>
    <?php include 'navbar-home.php'; ?>

    <main>
        <div class="hero-content">
            <h2 class="Header-text">Cari Resep Nusantara? Ini Dia Kumpulan Terbaik dari Tanah Jawa!</h2>

            <form method="GET" class="search-bar">
                <input type="hidden" name="region" value="<?php echo htmlspecialchars($region_filter); ?>">
                <input type="text" name="search" id="searchInput" placeholder="Cari resep..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <button type="submit" id="searchBtn" aria-label="search">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
            </form>

            <div class="filter-button">
                <a href="resep.php<?php echo !empty($search) ? '?search=' . urlencode($search) : ''; ?>"
                   class="<?php echo empty($region_filter) ? 'active' : ''; ?>">All</a>
                <a href="resep.php?region=Jawa Timur<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"
                   class="<?php echo $region_filter === 'Jawa Timur' ? 'active' : ''; ?>">Jawa Timur</a>
                <a href="resep.php?region=Jawa Tengah<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"
                   class="<?php echo $region_filter === 'Jawa Tengah' ? 'active' : ''; ?>">Jawa Tengah</a>
                <a href="resep.php?region=Jawa Barat<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"
                   class="<?php echo $region_filter === 'Jawa Barat' ? 'active' : ''; ?>">Jawa Barat</a>
                <a href="resep.php?region=Yogyakarta<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"
                   class="<?php echo $region_filter === 'Yogyakarta' ? 'active' : ''; ?>">Yogyakarta</a>
                <a href="resep.php?region=Jakarta<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"
                   class="<?php echo $region_filter === 'Jakarta' ? 'active' : ''; ?>">Jakarta</a>
                <a href="resep.php?region=Banten<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"
                   class="<?php echo $region_filter === 'Banten' ? 'active' : ''; ?>">Banten</a>
            </div>

            <?php if (!empty($search) || !empty($region_filter)): ?>
                <div class="filter-info">
                    <span>
                        Menampilkan <?php echo mysqli_num_rows($result); ?> resep
                        <?php if (!empty($search)): ?>
                            untuk "<?php echo htmlspecialchars($search); ?>"
                        <?php endif; ?>
                        <?php if (!empty($region_filter)): ?>
                            dari "<?php echo htmlspecialchars($region_filter); ?>"
                        <?php endif; ?>
                    </span>
                    <a href="resep.php">Hapus Filter</a>
                </div>
            <?php endif; ?>

            <div class="container-resep">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($recipe = mysqli_fetch_assoc($result)): ?>
                        <div class="card">
                            <?php if (!empty($recipe['image_url'])): ?>
                                <img class="card-img" src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="<?php echo htmlspecialchars($recipe['name']); ?>" />
                            <?php else: ?>
                                <div style="width:100%;height:185px;background:#eee;display:flex;align-items:center;justify-content:center;color:#666;">No Image</div>
                            <?php endif; ?>

                            <div class="card-content">
                                <div class="location"><?php echo htmlspecialchars($recipe['region']); ?></div>
                                <div class="food-name"><?php echo htmlspecialchars($recipe['name']); ?></div>
                                <p><?php echo htmlspecialchars($recipe['description']); ?></p>
                                <div class="details-row">
                                    <span class="detail"><i class="fa-solid fa-clock"></i> <?php echo htmlspecialchars($recipe['cook_time']); ?></span>
                                    <span class="detail"><i class="fa-solid fa-star"></i> <?php echo $recipe['rating']; ?></span>
                                </div>
                                <a href="resep-detail.php?id=<?php echo $recipe['id']; ?>" class="lihat-btn">Lihat</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-recipes">
                        <h3>Tidak ada resep ditemukan</h3>
                        <p>Coba cari dengan kata kunci lain atau hapus filter.</p>
                        <a href="resep.php" class="lihat-btn" style="display: inline-block; width: auto; padding: 12px 25px; margin-top: 15px;">
                            Lihat Semua Resep
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script>
        feather.replace();

        const navbar = document.getElementById("mainNavbar");
        window.addEventListener("scroll", () => {
            if (window.scrollY > 20) {
                navbar.classList.add("scrolled");
            } else {
                navbar.classList.remove("scrolled");
            }
        });
    </script>
</body>
</html>
