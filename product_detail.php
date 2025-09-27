<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

require_once "db.php";

// Ambil ID produk
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: product.php");
    exit();
}

// Ambil data produk dari database
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("Location: product.php");
    exit();
}

$product = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($product['name']); ?> - NusaJava Eats</title>
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
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      background-color: var(--mainColor);
      padding-top: 100px; /* Increased for navbar */
    }

    .container {
      max-width: 1200px;
      margin: 20px auto;
      padding: 30px;
      background: var(--light-brown);
      border-radius: 25px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .breadcrumb {
      margin-bottom: 25px;
      color: var(--doubleColor);
      padding: 15px 0;
      font-size: 16px;
    }

    .breadcrumb a {
      color: var(--acsentColor);
      text-decoration: none;
      font-weight: 500;
    }

    .breadcrumb a:hover {
      text-decoration: underline;
    }

    .product-detail {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 50px;
      margin-bottom: 40px;
    }

    .product-image-container {
      display: flex;
      justify-content: center;
      align-items: flex-start;
    }

    .product-image {
      width: 100%;
      max-width: 400px;
      height: auto;
      border-radius: 20px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
      background: #fff0d7;
      padding: 20px;
    }

    .product-info h1 {
      font-size: 2.4em;
      margin-bottom: 15px;
      color: var(--secondrayColor);
      font-weight: bold;
      line-height: 1.2;
    }

    .product-brand {
      color: var(--doubleColor);
      margin-bottom: 15px;
      font-size: 1.2em;
      font-weight: 600;
      background: rgba(167, 119, 78, 0.1);
      padding: 8px 15px;
      border-radius: 15px;
      display: inline-block;
    }

    .product-price {
      font-size: 2.8em;
      color: var(--acsentColor);
      font-weight: bold;
      margin-bottom: 25px;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }

    .product-description {
      line-height: 1.7;
      margin-bottom: 25px;
      color: var(--tambahColor);
      font-size: 1.1em;
      background: rgba(254, 186, 113, 0.1);
      padding: 20px;
      border-radius: 15px;
      border-left: 4px solid var(--trippleColor);
    }

    .product-stock {
      margin-bottom: 25px;
    }

    .stock-info {
      padding: 15px 25px;
      border-radius: 25px;
      display: inline-block;
      font-weight: 600;
      font-size: 1.1em;
    }

    .stock-available {
      background: linear-gradient(45deg, #d4edda, #c3e6cb);
      color: #155724;
      border: 2px solid #28a745;
    }

    .stock-low {
      background: linear-gradient(45deg, #fff3cd, #ffeaa7);
      color: #856404;
      border: 2px solid #ffc107;
    }

    .stock-out {
      background: linear-gradient(45deg, #f8d7da, #f5c6cb);
      color: #721c24;
      border: 2px solid #dc3545;
    }

    .quantity-selector {
      display: flex;
      align-items: center;
      gap: 20px;
      margin-bottom: 30px;
      background: rgba(255, 255, 255, 0.8);
      padding: 20px;
      border-radius: 20px;
      border: 2px solid var(--trippleColor);
    }

    .quantity-selector label {
      font-weight: 700;
      color: var(--secondrayColor);
      font-size: 1.2em;
    }

    .quantity-btn {
      background: var(--trippleColor);
      color: white;
      border: none;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      cursor: pointer;
      font-size: 24px;
      font-weight: bold;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .quantity-btn:hover {
      background: var(--acsentColor);
      transform: scale(1.1);
    }

    .quantity-input {
      width: 100px;
      text-align: center;
      padding: 15px;
      border: 3px solid var(--trippleColor);
      border-radius: 15px;
      font-size: 18px;
      font-weight: bold;
      background: white;
    }

    .add-to-cart-btn {
      background: linear-gradient(45deg, var(--acsentColor), var(--secondrayColor));
      color: white;
      border: none;
      padding: 20px 40px;
      border-radius: 25px;
      font-size: 1.3em;
      font-weight: bold;
      cursor: pointer;
      width: 100%;
      margin-bottom: 20px;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .add-to-cart-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.3);
    }

    .add-to-cart-btn:disabled {
      background: #95a5a6;
      cursor: not-allowed;
      transform: none;
    }

    .back-btn {
      background: var(--trippleColor);
      color: white;
      padding: 15px 30px;
      text-decoration: none;
      border-radius: 25px;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      font-weight: 600;
      font-size: 1.1em;
      transition: all 0.3s ease;
      box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    }

    .back-btn:hover {
      background: var(--doubleColor);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }

    @media (max-width: 768px) {
      .product-detail {
        grid-template-columns: 1fr;
        gap: 30px;
      }
      .container {
        margin: 10px;
        padding: 20px;
      }
      .product-info h1 {
        font-size: 1.8em;
      }
      .product-price {
        font-size: 2.2em;
      }
      .quantity-selector {
        flex-direction: column;
        gap: 15px;
      }
    }
  </style>
</head>

<body>
  <?php include 'navbar.php'; ?>

  <div class="container">
    <div class="breadcrumb">
      <a href="home-login.php">Beranda</a> >
      <a href="product.php">Produk</a> >
      <?php echo htmlspecialchars($product['name']); ?>
    </div>

    <div class="product-detail">
      <div class="product-image-container">
        <?php if (!empty($product['image_url'])): ?>
          <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
        <?php else: ?>
          <div style="width:100%;max-width:400px;height:400px;background:#fff0d7;display:flex;align-items:center;justify-content:center;border-radius:20px;color:var(--doubleColor);font-weight:bold;font-size:1.1em;">No Image Available</div>
        <?php endif; ?>
      </div>

      <div class="product-info">
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>

        <?php if (!empty($product['brand'])): ?>
          <div class="product-brand">Brand: <?php echo htmlspecialchars($product['brand']); ?></div>
        <?php endif; ?>

        <div class="product-price">Rp<?php echo number_format($product['price'], 0, ',', '.'); ?></div>

        <?php if (!empty($product['description'])): ?>
          <div class="product-description">
            <h3 style="margin-bottom: 10px; color: var(--secondrayColor);">📝 Deskripsi Produk:</h3>
            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
          </div>
        <?php endif; ?>

        <div class="product-stock">
          <?php if ($product['stock'] > 10): ?>
            <div class="stock-info stock-available">
              ✅ Stok tersedia (<?php echo $product['stock']; ?> unit)
            </div>
          <?php elseif ($product['stock'] > 0): ?>
            <div class="stock-info stock-low">
              ⚠️ Stok terbatas (<?php echo $product['stock']; ?> unit)
            </div>
          <?php else: ?>
            <div class="stock-info stock-out">
              ❌ Stok habis
            </div>
          <?php endif; ?>
        </div>

        <?php if ($product['stock'] > 0): ?>
          <div class="quantity-selector">
            <label>Jumlah:</label>
            <button type="button" class="quantity-btn" onclick="changeQuantity(-1)">-</button>
            <input type="number" id="quantity" class="quantity-input" value="1" min="1" max="<?php echo $product['stock']; ?>">
            <button type="button" class="quantity-btn" onclick="changeQuantity(1)">+</button>
          </div>

          <button onclick="addToCartDetail()" class="add-to-cart-btn">
            <i data-feather="shopping-cart"></i>
            Tambah ke Keranjang
          </button>
        <?php else: ?>
          <button disabled class="add-to-cart-btn">
            <i data-feather="x-circle"></i>
            Stok Habis
          </button>
        <?php endif; ?>

        <a href="product.php" class="back-btn">
          <i data-feather="arrow-left"></i>
          Kembali ke Produk
        </a>
      </div>
    </div>
  </div>

  <?php include 'footer.php'; ?>

  <!-- FIXED: Load cart.js BEFORE the inline script -->
  <script src="cart.js"></script>
  <script>
    const product = <?php echo json_encode($product); ?>;

    function changeQuantity(change) {
      const quantityInput = document.getElementById('quantity');
      let currentQuantity = parseInt(quantityInput.value);
      let newQuantity = currentQuantity + change;

      if (newQuantity < 1) newQuantity = 1;
      if (newQuantity > product.stock) newQuantity = product.stock;

      quantityInput.value = newQuantity;
    }

    function addToCartDetail() {
      const quantity = parseInt(document.getElementById('quantity').value);

      // Validate quantity
      if (quantity < 1 || quantity > product.stock) {
        showNotification('Jumlah tidak valid!', 'error');
        return;
      }

      // Get existing cart from localStorage
      let cart = JSON.parse(localStorage.getItem('cart') || '[]');

      // Check if product already in cart
      let existingItemIndex = cart.findIndex(item => item.id === product.id);

      if (existingItemIndex > -1) {
        // Update existing item
        const newTotalQuantity = cart[existingItemIndex].quantity + quantity;

        if (newTotalQuantity <= product.stock) {
          cart[existingItemIndex].quantity = newTotalQuantity;
          showNotification(`${product.name} ditambahkan! Total: ${newTotalQuantity}`, 'success');
        } else {
          // Set to maximum available
          cart[existingItemIndex].quantity = product.stock;
          showNotification(`Ditambahkan ${product.stock - (newTotalQuantity - quantity)} item. Stok maksimal: ${product.stock}`, 'error');
        }
      } else {
        // Add new item to cart
        cart.push({
          id: product.id,
          name: product.name,
          price: product.price,
          image_url: product.image_url || 'https://via.placeholder.com/120x120/f4e4d0/5b2620?text=No+Image',
          brand: product.brand || 'Unknown',
          quantity: quantity,
          max_stock: product.stock
        });
        showNotification(`${quantity}x ${product.name} ditambahkan ke keranjang!`, 'success');
      }

      // Save to localStorage
      localStorage.setItem('cart', JSON.stringify(cart));

      // Update cart badge and dropdown
      updateCartCount();
      updateCartDropdown();

      // Reset quantity to 1
      document.getElementById('quantity').value = 1;
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      updateCartCount();
      feather.replace();
    });

    feather.replace();
  </script>
</body>
</html>
