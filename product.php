<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

require_once "db.php";

// Filter dan search logic
$brand_filter = isset($_GET['brand']) ? $_GET['brand'] : '';
$search = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';

// Build SQL query dengan filter dan search
$sql = "SELECT * FROM products WHERE 1=1";
$params = array();

// Apply brand filter
if (!empty($brand_filter)) {
    $sql .= " AND brand = ?";
    $params[] = $brand_filter;
}

// Apply search filter
if (!empty($search)) {
    $sql .= " AND (LOWER(name) LIKE ? OR LOWER(description) LIKE ? OR LOWER(brand) LIKE ?)";
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$sql .= " ORDER BY id ASC";

// Prepare and execute statement
$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get unique brands for filter
$brand_sql = "SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND brand != '' ORDER BY brand";
$brand_result = mysqli_query($conn, $brand_sql);
$brands = array();
while ($brand_row = mysqli_fetch_assoc($brand_result)) {
    $brands[] = $brand_row['brand'];
}

// Add additional brands if they don't exist in the database yet
$additional_brands = ['Royco', 'Bamboe', 'Indofood', 'Bango', 'Saori'];
foreach ($additional_brands as $brand) {
    if (!in_array($brand, $brands)) {
        $brands[] = $brand;
    }
}
sort($brands);

// Get user orders
$orders_sql = "SELECT o.*, COUNT(oi.id) as item_count
               FROM orders o
               LEFT JOIN order_items oi ON o.id = oi.order_id
               WHERE o.user_id = ?
               GROUP BY o.id
               ORDER BY o.created_at DESC";
$orders_stmt = mysqli_prepare($conn, $orders_sql);
mysqli_stmt_bind_param($orders_stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($orders_stmt);
$orders_result = mysqli_stmt_get_result($orders_stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NusaJava Eats - Produk</title>
  <script src="https://unpkg.com/feather-icons"></script>

  <style>
    :root {
      --mainColor: #feba71;
      --secondrayColor: #5b2620;
      --acsentColor: #af2d2d;
      --doubleColor: #816040;
      --trippleColor: #a7774e;
      --tambahColor: #222;
      --light-brown: #f4e4d0;
      --gradient-warm: linear-gradient(135deg, #feba71 0%, #f4a261 50%, #e76f51 100%);
      --gradient-brown: linear-gradient(135deg, #5b2620 0%, #816040 50%, #a7774e 100%);
      --shadow-soft: 0 8px 32px rgba(91, 38, 32, 0.15);
      --shadow-hover: 0 16px 48px rgba(91, 38, 32, 0.25);
      --border-radius: 20px;
      --transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      background: var(--mainColor);
      background-attachment: fixed;
      padding-top: 100px;
      min-height: 100vh;
    }

    main {
      background: transparent;
      position: relative;
    }

    main::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      pointer-events: none;
      z-index: -1;
    }

    .navbar {
      background: linear-gradient(90deg, var(--secondrayColor), var(--doubleColor));
      padding: 15px 20px;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logo img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
    }

    .logo-text {
      color: white;
      font-weight: bold;
      font-size: 1.5em;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }

    .container-produk h1 {
      font-size: 2rem;
            color: #222;
            margin-bottom: 20px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
    }

    .container-produk p{
      max-width: 700px;
            margin: 0 auto 40px auto;
            font-size: 16px;
            color: #555;
            line-height: 1.6;
    }

    .desc {
      text-align: center;
      color: var(--secondrayColor);
      max-width: 700px;
      margin: 0 auto 25px auto;
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      padding: 25px;
      border-radius: var(--border-radius);
      border: 1px solid rgba(255, 255, 255, 0.2);
      box-shadow: var(--shadow-soft);
    }

    .search-filter-section {
      background: var(--light-brown);
      backdrop-filter: blur(20px);
      padding: 40px;
      margin: 40px auto;
      border-radius: 30px;
      box-shadow: var(--shadow-soft);
      max-width: 1300px;
      border: 1px solid rgba(255, 255, 255, 0.3);
      position: relative;
      overflow: hidden;
    }

    .search-filter-section::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: conic-gradient(
        from 0deg,
        transparent 0deg,
        rgba(254, 186, 113, 0.05) 60deg,
        transparent 120deg,
        rgba(167, 119, 78, 0.05) 180deg,
        transparent 240deg,
        rgba(175, 45, 45, 0.05) 300deg,
        transparent 360deg
      );
      animation: rotate 20s linear infinite;
      pointer-events: none;
    }

    @keyframes rotate {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }

    .tab-buttons {
      display: flex;
      gap: 20px;
      margin-bottom: 20px;
      justify-content: center;
    }

    .tab-btn {
      background: #fff3ea;
      border: 2px solid #b78952;
      color: #853c00;
      border-radius: 25px;
      font-size: 1.1em;
      font-weight: 600;
      cursor: pointer;
      padding: 12px 30px;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .tab-btn:hover {
      background: #ffc67b;
      color: #95351b;
      border-color: #d8922a;
    }

    .tab-btn.active {
      background: var(--acsentColor);
      color: #fffbe8;
      border-color: var(--acsentColor);
    }

    .search-form {
      display: flex;
      gap: 15px;
      margin-bottom: 20px;
      max-width: 600px;
      margin-left: auto;
      margin-right: auto;
    }

    .search-input {
      flex: 1;
      padding: 15px 20px;
      border: 2px solid var(--trippleColor);
      border-radius: 25px;
      font-size: 16px;
      transition: all 0.3s ease;
    }

    .search-input:focus {
      outline: none;
      border-color: var(--acsentColor);
      box-shadow: 0 0 10px rgba(175, 45, 45, 0.3);
    }

    .search-btn {
      background: var(--acsentColor);
      color: white;
      border: none;
      padding: 15px 30px;
      border-radius: 25px;
      cursor: pointer;
      font-weight: 500;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .search-btn:hover {
      background: var(--secondrayColor);
      transform: translateY(-2px);
    }

    .filter-brands {
      display: flex;
      gap: 13px;
      flex-wrap: wrap;
      justify-content: center;
      align-items: center;
    }

    .filter-brand-btn {
      background: #fff3ea;
      border: 1.5px solid #b78952;
      color: #853c00;
      border-radius: 19px;
      font-size: 1em;
      font-weight: 600;
      cursor: pointer;
      padding: 8px 22px;
      transition: background 0.18s, color 0.19s, border 0.14s;
      text-decoration: none;
    }

    .filter-brand-btn:hover {
      background: #ffc67b;
      color: #95351b;
      border-color: #d8922a;
    }

    .filter-brand-btn.active {
      background: #af2d2d;
      color: #fffbe8;
      border-color: #af2d2d;
    }

    .container-produk {
      padding: 1em 0 2em 0;
      max-width: 1200px;
      margin: auto;
    }

    .produk-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 30px;
      justify-content: center;
      width: 100%;
      padding: 30px;
      max-width: 1400px;
      margin: 0 auto;
    }

    .produk-card {
      background: var(--light-brown);
      backdrop-filter: blur(20px);
      border-radius: 25px;
      display: flex;
      flex-direction: column;
      align-items: center;
      box-shadow: var(--shadow-soft);
      min-width: 0;
      padding: 25px 20px 30px 20px;
      transition: var(--transition);
      position: relative;
      border: 1px solid rgba(255, 255, 255, 0.3);
      overflow: hidden;
      transform-style: preserve-3d;
    }

    .produk-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: var(--gradient-brown);
      opacity: 0;
      transition: var(--transition);
      z-index: -1;
    }

    .produk-card:hover {
      box-shadow: var(--shadow-hover);
      transform: translateY(-15px) rotateX(5deg) rotateY(5deg);
      border-color: var(--mainColor);
    }

    .produk-card:hover::before {
      opacity: 0.05;
    }

    .produk-card-image-container {
      position: relative;
      width: 100%;
      height: 200px;
      margin-bottom: 20px;
      border-radius: 20px;
      overflow: hidden;
      background: linear-gradient(135deg, #fff0d7 0%, #ffeaa4 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
      transition: var(--transition);
    }

    .produk-card:hover .produk-card-image-container {
      transform: scale(1.05);
      box-shadow: 0 12px 35px rgba(0,0,0,0.15);
    }

    .produk-card img {
      width: 90%;
      height: 90%;
      object-fit: contain;
      border-radius: 15px;
      transition: var(--transition);
      filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
    }

    .produk-card:hover img {
      transform: scale(1.1) rotate(2deg);
      filter: drop-shadow(0 8px 15px rgba(0,0,0,0.2));
    }

    .produk-card h2 {
      font-size: clamp(1.1em, 2.5vw, 1.3em);
      color: var(--secondrayColor);
      text-align: center;
      font-weight: 700;
      letter-spacing: 0.5px;
      margin: 0 0 15px 0;
      min-height: 50px;
      line-height: 1.4;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: var(--transition);
    }

    .produk-card:hover h2 {
      color: var(--acsentColor);
      transform: translateY(-2px);
    }

    .produk-card .produk-price {
      color: var(--acsentColor);
      font-size: clamp(1.1em, 2.5vw, 1.4em);
      font-weight: 800;
      margin-bottom: 15px;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
      background: linear-gradient(45deg, var(--acsentColor), #dc3545);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      transition: var(--transition);
    }

    .produk-card:hover .produk-price {
      transform: scale(1.05);
      text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    }

    .produk-card .produk-actions {
      display: flex;
      align-items: center;
      gap: 7px;
      margin-top: 5px;
      flex-direction: column;
      width: 100%;
    }

    .add-to-cart-btn {
      background: linear-gradient(135deg, var(--acsentColor) 0%, #dc3545 100%);
      color: white;
      border: none;
      padding: 12px 20px;
      border-radius: 25px;
      cursor: pointer;
      width: 100%;
      font-weight: 700;
      transition: var(--transition);
      margin-bottom: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      font-size: 1em;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      box-shadow: 0 6px 20px rgba(175, 45, 45, 0.3);
      position: relative;
      overflow: hidden;
    }

    .add-to-cart-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.6s ease;
    }

    .add-to-cart-btn:hover::before {
      left: 100%;
    }

    .add-to-cart-btn:hover {
      background: linear-gradient(135deg, #dc3545 0%, var(--secondrayColor) 100%);
      transform: translateY(-3px) scale(1.02);
      box-shadow: 0 10px 30px rgba(175, 45, 45, 0.4);
    }

    .lihat-produk-btn {
      background: rgba(255, 255, 255, 0.9);
      border: 2px solid var(--trippleColor);
      color: var(--secondrayColor);
      font-weight: 600;
      font-size: 1em;
      border-radius: 25px;
      padding: 12px 20px;
      cursor: pointer;
      text-decoration: none;
      transition: var(--transition);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      width: 100%;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      backdrop-filter: blur(10px);
      box-shadow: 0 4px 15px rgba(167, 119, 78, 0.2);
    }

    .lihat-produk-btn:hover {
      background: var(--trippleColor);
      color: white;
      border-color: var(--doubleColor);
      transform: translateY(-2px) scale(1.02);
      box-shadow: 0 8px 25px rgba(167, 119, 78, 0.4);
    }

    .product-meta {
      margin-bottom: 20px;
      text-align: center;
      width: 100%;
      background: rgba(255, 255, 255, 0.8);
      border-radius: 15px;
      padding: 15px;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .product-meta small {
      display: block;
      margin-bottom: 8px;
      color: var(--doubleColor);
      font-size: 0.9em;
      font-weight: 600;
      transition: var(--transition);
    }

    .product-meta small:last-child {
      margin-bottom: 0;
      font-weight: 700;
      font-size: 1em;
    }

    .filter-info {
      margin-top: 15px;
      text-align: center;
    }

    .filter-info span {
      color: var(--doubleColor);
    }

    .filter-info a {
      color: var(--acsentColor);
      margin-left: 10px;
      text-decoration: underline;
    }

    .no-products {
      grid-column: 1/-1;
      text-align: center;
      padding: 60px 20px;
      color: var(--doubleColor);
    }

    .no-products h3 {
      color: var(--secondrayColor);
      font-size: 2em;
      margin-bottom: 15px;
    }

    .no-products p {
      font-size: 1.1em;
      margin-bottom: 20px;
    }

    /* Orders Section Styles */
    .orders-container {
      display: none;
    }

    .orders-container.active {
      display: block;
    }

    .order-card {
      background: white;
      border-radius: 15px;
      padding: 25px;
      margin-bottom: 20px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      border: 2px solid var(--mainColor);
    }

    .order-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      padding-bottom: 15px;
      border-bottom: 2px solid var(--mainColor);
    }

    .order-number {
      font-size: 1.3em;
      font-weight: bold;
      color: var(--acsentColor);
    }

    .order-date {
      color: var(--doubleColor);
      font-size: 0.9em;
    }

    .order-status {
      padding: 8px 15px;
      border-radius: 20px;
      font-size: 0.9em;
      font-weight: bold;
      display: inline-block;
    }

    .status-pending {
      background: #f39c12;
      color: white;
    }

    .status-confirmed {
      background: #3498db;
      color: white;
    }

    .status-shipped {
      background: var(--trippleColor);
      color: white;
    }

    .status-delivered {
      background: #27ae60;
      color: white;
    }

    .status-cancelled {
      background: #e74c3c;
      color: white;
    }

    .payment-status {
      padding: 6px 12px;
      border-radius: 15px;
      font-size: 0.8em;
      font-weight: bold;
      display: inline-block;
      margin-left: 10px;
    }

    .payment-menunggu {
      background: #ffeaa7;
      color: #2d3436;
    }

    .payment-lunas {
      background: #00b894;
      color: white;
    }

    .payment-gagal {
      background: #e74c3c;
      color: white;
    }

    .order-info {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 20px;
      margin-bottom: 15px;
    }

    .order-total {
      font-size: 1.2em;
      font-weight: bold;
      color: var(--acsentColor);
    }

    .view-detail-btn {
      background: var(--acsentColor);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 20px;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .view-detail-btn:hover {
      background: var(--secondrayColor);
      transform: translateY(-2px);
    }

    .no-orders {
      text-align: center;
      padding: 60px 20px;
      color: var(--doubleColor);
    }

    .no-orders h3 {
      color: var(--secondrayColor);
      font-size: 2em;
      margin-bottom: 15px;
    }

    .no-orders p {
      font-size: 1.1em;
      margin-bottom: 20px;
    }

    /* Modal Styles */
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
      background-color: white;
      margin: 5% auto;
      padding: 30px;
      border-radius: 20px;
      width: 90%;
      max-width: 800px;
      max-height: 80vh;
      overflow-y: auto;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }

    .modal-header {
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 2px solid var(--mainColor);
    }

    .modal-header h2 {
      color: var(--secondrayColor);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .close {
      color: var(--acsentColor);
      float: right;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }

    .close:hover {
      opacity: 0.7;
    }

    /* Enhanced Responsive Design */
    @media (max-width: 1200px) {
      .produk-grid {
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 25px;
        padding: 25px;
      }

      .search-filter-section {
        margin: 30px 20px;
        padding: 35px;
      }
    }

    @media (max-width: 900px) {
      .produk-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        padding: 20px;
      }

      .order-info {
        grid-template-columns: 1fr;
      }

      .produk-card-image-container {
        height: 180px;
      }

      .search-filter-section {
        padding: 30px 25px;
      }
    }

    @media (max-width: 768px) {
      .produk-grid {
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 18px;
        padding: 18px;
      }

      .container-produk {
        padding: 0 15px 1.5em 15px;
      }

      .tab-buttons {
        flex-direction: column;
        gap: 12px;
      }

      .search-filter-section {
        margin: 20px 15px;
        padding: 25px 20px;
        border-radius: 20px;
      }

      .produk-card {
        padding: 20px 15px 25px 15px;
      }

      .produk-card-image-container {
        height: 160px;
      }
    }

    @media (max-width: 600px) {
      .produk-grid {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px;
        padding: 15px;
      }

      .produk-card:hover {
        transform: translateY(-8px) scale(1.02);
      }
    }

    @media (max-width: 480px) {
      .produk-grid {
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px;
        padding: 12px;
      }

      .produk-card {
        padding: 15px 10px 20px 10px;
        border-radius: 20px;
      }

      .produk-card-image-container {
        height: 140px;
        margin-bottom: 15px;
      }

      .search-filter-section {
        margin: 15px 10px;
        padding: 20px 15px;
      }

      .container-produk h1 {
        font-size: 24px;
      }

      .desc {
        padding: 20px;
        font-size: 14px;
      }
    }

    @media (max-width: 360px) {
      .produk-grid {
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        padding: 10px;
      }

      .produk-card {
        padding: 12px 8px 15px 8px;
      }

      .produk-card-image-container {
        height: 120px;
      }
    }
  </style>
</head>

<body>
  <?php include 'navbar.php'; ?>

  <main>
    <section class="produk" id="produk">
      <div class="container-produk">
        <h1>Bumbu Instant Nusantara: Kepraktisan Rasa Khas Tanah Jawa</h1>
        <p class="desc">
          Dengan beragam rempah yang ada, setiap hidangan menjadi lebih istimewa,
          memancarkan kelezatan yang tidak terlupakan. Mari jelajahi kekayaan cita rasa dari Tanah Jawa,
          yang menyatukan tradisi dan kelezatan dalam setiap hidangan.
        </p>

        <!-- Search and Filter Section -->
        <div class="search-filter-section">
          <div class="tab-buttons">
            <button onclick="showProducts()" class="tab-btn active" id="products-tab">
              <i data-feather="package"></i> Produk
            </button>
            <button onclick="showOrders()" class="tab-btn" id="orders-tab">
              <i data-feather="shopping-bag"></i> Pesanan Saya
            </button>
          </div>

          <div id="products-section">
            <form method="GET" class="search-form">
              <input type="hidden" name="brand" value="<?php echo htmlspecialchars($brand_filter); ?>">
              <input type="text" name="search" placeholder="Cari produk..." class="search-input" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
              <button type="submit" class="search-btn">
                <i data-feather="search"></i> Cari
              </button>
            </form>

            <div class="filter-brands">
              <a href="product.php<?php echo !empty($search) ? '?search=' . urlencode($search) : ''; ?>"
                 class="filter-brand-btn <?php echo empty($brand_filter) ? 'active' : ''; ?>">
                 All
              </a>
              <?php foreach ($brands as $brand): ?>
                <a href="product.php?brand=<?php echo urlencode($brand); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"
                   class="filter-brand-btn <?php echo $brand_filter === $brand ? 'active' : ''; ?>">
                  <?php echo htmlspecialchars($brand); ?>
                </a>
              <?php endforeach; ?>
            </div>

<?php if (!empty($search) || !empty($brand_filter)): ?>
              <div class="filter-info">
                <span>
                  Menampilkan <?php echo mysqli_num_rows($result); ?> produk
                  <?php if (!empty($search)): ?>
                    untuk "<?php echo htmlspecialchars($search); ?>"
                  <?php endif; ?>
                  <?php if (!empty($brand_filter)): ?>
                    dari brand "<?php echo htmlspecialchars($brand_filter); ?>"
                  <?php endif; ?>
                </span>
                <a href="product.php">Hapus Filter</a>
              </div>
            <?php endif; ?>
          </div>

          <!-- Orders Section -->
          <div id="orders-section" class="orders-container">
            <?php if (mysqli_num_rows($orders_result) > 0): ?>
              <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                <div class="order-card">
                  <div class="order-header">
                    <div>
                      <div class="order-number">NJE<?php echo str_pad($order['id'], 6, "0", STR_PAD_LEFT); ?></div>
                      <div class="order-date"><?php echo date('d F Y, H:i', strtotime($order['created_at'])); ?></div>
                    </div>
                    <div>
                      <span class="order-status status-<?php echo $order['status']; ?>">
                        <?php echo ucfirst($order['status']); ?>
                      </span>
                      <span class="payment-status payment-<?php echo $order['payment_status'] ?? 'menunggu'; ?>">
                        <?php echo ucfirst($order['payment_status'] ?? 'Menunggu'); ?>
                      </span>
                    </div>
                  </div>

                  <div class="order-info">
                    <div>
                      <strong>Total Item:</strong><br>
                      <?php echo $order['item_count']; ?> produk
                    </div>
                    <div>
                      <strong>Metode Pembayaran:</strong><br>
                      <?php echo strtoupper($order['payment_method'] ?? 'COD'); ?>
                    </div>
                    <div>
                      <strong>Total Pembayaran:</strong><br>
                      <span class="order-total">Rp<?php echo number_format($order['total_amount'], 0, ',', '.'); ?></span>
                    </div>
                  </div>

                  <div style="margin-top: 15px;">
                    <button onclick="viewOrderDetail(<?php echo $order['id']; ?>)" class="view-detail-btn">
                      <i data-feather="eye"></i> Lihat Detail
                    </button>
                  </div>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <div class="no-orders">
                <h3>Belum Ada Pesanan</h3>
                <p>Anda belum melakukan pemesanan. Mulai berbelanja sekarang!</p>
                <button onclick="showProducts()" class="view-detail-btn">
                  <i data-feather="shopping-cart"></i> Mulai Belanja
                </button>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="produk-grid" id="produkGrid">
          <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($product = mysqli_fetch_assoc($result)): ?>
              <div class="produk-card">
                <div class="produk-card-image-container">
                  <?php if (!empty($product['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                  <?php else: ?>
                    <div style="width:80%;height:80%;background:rgba(129,96,64,0.1);display:flex;align-items:center;justify-content:center;border-radius:15px;color:var(--doubleColor);font-weight:600;font-size:0.9em;">No Image</div>
                  <?php endif; ?>
                </div>

                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <div class="produk-price">Rp<?php echo number_format($product['price'], 0, ',', '.'); ?></div>

                <div class="product-meta">
                  <?php if (!empty($product['brand'])): ?>
                    <small>Brand: <?php echo htmlspecialchars($product['brand']); ?></small>
                  <?php endif; ?>
                  <small style="color: <?php echo $product['stock'] < 10 ? '#ff6b6b' : '#51cf66'; ?>;">
                    Stok: <?php echo $product['stock']; ?>
                  </small>
                </div>

                <div class="produk-actions">
                  <button onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['price']; ?>, '<?php echo addslashes($product['image_url']); ?>', <?php echo $product['stock']; ?>, '<?php echo addslashes($product['brand'] ?? 'Unknown'); ?>')"
                          class="add-to-cart-btn">
                    <i data-feather="shopping-cart"></i> Tambah ke Keranjang
                  </button>

                  <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="lihat-produk-btn">
                    <i data-feather="eye"></i> Lihat Detail
                  </a>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="no-products">
              <h3>Tidak ada produk ditemukan</h3>
              <p>Coba cari dengan kata kunci lain atau hapus filter.</p>
              <a href="product.php" class="lihat-produk-btn" style="display: inline-block; width: auto; padding: 12px 25px;">
                Lihat Semua Produk
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </main>

  <!-- Order Detail Modal -->
  <div id="orderModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <span class="close">&times;</span>
        <h2><i data-feather="eye"></i> Detail Pesanan</h2>
      </div>
      <div id="orderDetailsContent">
        <!-- Order details will be loaded here -->
      </div>
    </div>
  </div>
            <?php include 'footer.php'; ?>
  <!-- FIXED: Load cart.js BEFORE the inline script -->
  <script src="cart.js"></script>
  <script>
    function showProducts() {
      document.getElementById('products-section').style.display = 'block';
      document.getElementById('orders-section').style.display = 'none';
      document.getElementById('produkGrid').style.display = 'grid';

      document.getElementById('products-tab').classList.add('active');
      document.getElementById('orders-tab').classList.remove('active');

      feather.replace();
    }

    function showOrders() {
      document.getElementById('products-section').style.display = 'none';
      document.getElementById('orders-section').style.display = 'block';
      document.getElementById('produkGrid').style.display = 'none';

      document.getElementById('orders-tab').classList.add('active');
      document.getElementById('products-tab').classList.remove('active');

      feather.replace();
    }

    function viewOrderDetail(orderId) {
      // Fetch order details via AJAX (simplified for user view)
      fetch(`get_user_order_details.php?id=${orderId}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            document.getElementById('orderDetailsContent').innerHTML = data.html;
            document.getElementById('orderModal').style.display = 'block';
            feather.replace();
          } else {
            alert('Gagal memuat detail pesanan');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Terjadi kesalahan saat memuat detail pesanan');
        });
    }

    // Modal functionality
    const modal = document.getElementById('orderModal');
    const closeBtn = document.getElementsByClassName('close')[0];

    closeBtn.onclick = function() {
      modal.style.display = 'none';
    }

    window.onclick = function(event) {
      if (event.target == modal) {
        modal.style.display = 'none';
      }
    }

    // FIXED: Use the proper addToCart function from cart.js
    function addToCart(id, name, price, image, stock, brand) {
      // Validate inputs
      if (!id || !name || !price) {
        showNotification('Data produk tidak lengkap!', 'error');
        return;
      }

      // Get existing cart from localStorage
      let cart = JSON.parse(localStorage.getItem('cart') || '[]');

      // Check if product already exists in cart
      const existingItemIndex = cart.findIndex(item => item.id === id);

      if (existingItemIndex > -1) {
        // Update quantity if product exists
        const newQuantity = cart[existingItemIndex].quantity + 1;

        if (newQuantity <= stock) {
          cart[existingItemIndex].quantity = newQuantity;
          showNotification(`${name} ditambahkan! Total: ${newQuantity}x`, 'success');
        } else {
          showNotification(`Stok tidak mencukupi! Maksimal ${stock} item`, 'error');
          return;
        }
      } else {
        // Add new product to cart
        cart.push({
          id: id,
          name: name,
          price: price,
          image_url: image || 'https://via.placeholder.com/120x120/f4e4d0/5b2620?text=No+Image',
          quantity: 1,
          max_stock: stock,
          brand: brand || 'Unknown'
        });
        showNotification(`${name} berhasil ditambahkan ke keranjang!`, 'success');
      }

      // Save updated cart to localStorage
      localStorage.setItem('cart', JSON.stringify(cart));

      // Update cart count and dropdown
      updateCartCount();
      updateCartDropdown();
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      // Update cart count and setup cart functionality
      updateCartCount();
      feather.replace();
    });
  </script>

</body>
</html>
