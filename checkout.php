<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

require_once "db.php";

$success = false;
$error = "";

if ($_POST) {
    $cart_data = json_decode($_POST['cart_data'], true);
    $customer_name = trim($_POST['customer_name']);
    $customer_email = trim($_POST['customer_email']);
    $notes = trim($_POST['notes']);
    $shipping_address = trim($_POST['shipping_address']);
    $phone = trim($_POST['phone']);
    $payment_method = $_POST['payment_method'];

    if (empty($cart_data) || empty($customer_name) || empty($customer_email) || empty($shipping_address) || empty($phone)) {
        $error = "Semua field harus diisi!";
    } else {
        $subtotal = 0;
        foreach ($cart_data as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $shipping_cost = 15000;
        $total = $subtotal + $shipping_cost;

        // Create order with payment method and customer info
        $sql = "INSERT INTO orders (user_id, customer_name, customer_email, notes, total_amount, status, payment_method, payment_status, shipping_address, phone) VALUES (?, ?, ?, ?, ?, 'pending', ?, 'menunggu', ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isssdsss", $_SESSION['user_id'], $customer_name, $customer_email, $notes, $total, $payment_method, $shipping_address, $phone);

        if (mysqli_stmt_execute($stmt)) {
            $order_id = mysqli_insert_id($conn);

            $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $item_stmt = mysqli_prepare($conn, $item_sql);

            $all_items_added = true;
            foreach ($cart_data as $item) {
                mysqli_stmt_bind_param($item_stmt, "iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
                if (!mysqli_stmt_execute($item_stmt)) {
                    $all_items_added = false;
                    break;
                }

                // Update stock
                $update_stock_sql = "UPDATE products SET stock = stock - ? WHERE id = ?";
                $update_stmt = mysqli_prepare($conn, $update_stock_sql);
                mysqli_stmt_bind_param($update_stmt, "ii", $item['quantity'], $item['id']);
                mysqli_stmt_execute($update_stmt);
            }

            if ($all_items_added) {
                $success = true;
                $order_number = "NJE" . str_pad($order_id, 6, "0", STR_PAD_LEFT);

                // Save order details for receipt
                $order_details = array(
                    'order_id' => $order_id,
                    'order_number' => $order_number,
                    'customer_name' => $customer_name,
                    'customer_email' => $customer_email,
                    'notes' => $notes,
                    'total' => $total,
                    'payment_method' => $payment_method,
                    'items' => $cart_data,
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shipping_cost
                );
            } else {
                $error = "Gagal menambahkan beberapa item pesanan.";
            }
        } else {
            $error = "Gagal membuat pesanan: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Checkout - NusaJava Eats</title>
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
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, var(--light-brown), var(--mainColor));
      min-height: 100vh;
    }
    .navbar {
      background: linear-gradient(90deg, var(--secondaryColor), var(--doubleColor));
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
      color: var(--white);
      font-weight: bold;
      font-size: 1.5em;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }
    .container {
      max-width: 900px;
      margin: 100px auto 20px;
      padding: 20px;
    }
    .checkout-container {
      background: var(--white);
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .checkout-header {
      border-bottom: 3px solid var(--mainColor);
      padding-bottom: 20px;
      margin-bottom: 30px;
    }
    .checkout-header h1 {
      color: var(--secondaryColor);
      font-size: 2.2em;
      display: flex;
      align-items: center;
      gap: 15px;
    }
    .form-group {
      margin-bottom: 25px;
    }
    label {
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      color: var(--secondaryColor);
      font-size: 1.1em;
    }
    input, textarea, select {
      width: 100%;
      padding: 15px;
      border: 2px solid var(--light-brown);
      border-radius: 12px;
      font-size: 16px;
      transition: all 0.3s ease;
      background: var(--white);
    }
    input:focus, textarea:focus, select:focus {
      outline: none;
      border-color: var(--mainColor);
      box-shadow: 0 0 0 3px rgba(254, 186, 113, 0.2);
    }
    textarea {
      height: 120px;
      resize: vertical;
    }
    .order-summary {
      background: linear-gradient(45deg, var(--light-brown), rgba(254, 186, 113, 0.3));
      padding: 30px;
      border-radius: 20px;
      margin-bottom: 30px;
      border: 2px solid var(--mainColor);
    }
    .order-summary h3 {
      color: var(--secondaryColor);
      font-size: 1.5em;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .summary-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      padding: 15px 0;
      border-bottom: 1px solid rgba(91, 38, 32, 0.1);
    }
    .summary-item:last-child {
      border-bottom: none;
    }
    .item-details {
      flex: 1;
    }
    .item-name {
      font-weight: bold;
      color: var(--secondaryColor);
      margin-bottom: 5px;
    }
    .item-info {
      color: var(--doubleColor);
      font-size: 0.9em;
    }
    .item-total {
      font-weight: bold;
      color: var(--accentColor);
      font-size: 1.1em;
    }
    .summary-total {
      font-weight: bold;
      font-size: 1.3em;
      color: var(--secondaryColor);
      border-top: 3px solid var(--mainColor);
      padding-top: 15px;
      margin-top: 15px;
    }
    .submit-btn {
      background: linear-gradient(45deg, var(--mainColor), #ffd700);
      color: var(--secondaryColor);
      border: none;
      padding: 18px 35px;
      border-radius: 25px;
      font-size: 1.3em;
      font-weight: bold;
      cursor: pointer;
      width: 100%;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }
    .submit-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.3);
    }
    .alert {
      padding: 20px;
      border-radius: 15px;
      margin-bottom: 25px;
      display: flex;
      align-items: center;
      gap: 15px;
      font-size: 1.1em;
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
    .success-actions {
      text-align: center;
      margin-top: 30px;
    }
    .btn {
      display: inline-block;
      padding: 15px 30px;
      margin: 0 15px;
      text-decoration: none;
      border-radius: 25px;
      font-weight: bold;
      font-size: 1.1em;
      transition: all 0.3s ease;
    }
    .btn-primary {
      background: linear-gradient(45deg, var(--accentColor), var(--secondaryColor));
      color: var(--white);
    }
    .btn-secondary {
      background: linear-gradient(45deg, var(--tripleColor), var(--doubleColor));
      color: var(--white);
    }
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }
    .payment-methods {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-top: 10px;
    }
    .payment-option {
      position: relative;
    }
    .payment-option input[type="radio"] {
      position: absolute;
      opacity: 0;
      width: 100%;
      height: 100%;
      cursor: pointer;
    }
    .payment-option label {
      display: block;
      padding: 20px;
      border: 2px solid var(--light-brown);
      border-radius: 15px;
      cursor: pointer;
      transition: all 0.3s ease;
      background: var(--white);
      text-align: center;
    }
    .payment-option input[type="radio"]:checked + label {
      border-color: var(--mainColor);
      background: linear-gradient(45deg, var(--light-brown), rgba(254, 186, 113, 0.2));
      transform: scale(1.05);
    }

    /* Receipt Styles */
    .receipt-container {
      background: var(--white);
      border: 2px dashed var(--mainColor);
      border-radius: 15px;
      padding: 30px;
      margin-bottom: 30px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    .receipt-header {
      text-align: center;
      border-bottom: 2px solid var(--mainColor);
      padding-bottom: 20px;
      margin-bottom: 25px;
    }
    .receipt-header h2 {
      color: var(--secondaryColor);
      margin-bottom: 10px;
    }
    .receipt-info {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 25px;
    }
    .receipt-info div {
      background: var(--light-brown);
      padding: 15px;
      border-radius: 10px;
    }
    .receipt-info strong {
      display: block;
      color: var(--secondaryColor);
      margin-bottom: 5px;
    }
    .receipt-items {
      margin-bottom: 25px;
    }
    .receipt-item {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid var(--light-brown);
    }
    .receipt-totals {
      background: var(--light-brown);
      padding: 20px;
      border-radius: 10px;
    }
    .receipt-total {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
    }
    .receipt-total.final {
      font-weight: bold;
      font-size: 1.2em;
      color: var(--accentColor);
      border-top: 2px solid var(--mainColor);
      padding-top: 10px;
    }

    @media (max-width: 768px) {
      .container {
        margin: 80px 10px 20px;
        padding: 15px;
      }
      .checkout-container {
        padding: 25px;
      }
      .payment-methods {
        grid-template-columns: 1fr;
      }
      .receipt-info {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
  <nav class="navbar">
    <div class="logo">
      <img src="Assets/Img/NusaJava Eats.png" alt="Logo" />
      <span class="logo-text">NusaJava Eats</span>
    </div>
  </nav>

  <div class="container">
    <div class="checkout-container">
      <?php if ($success): ?>
        <div class="alert alert-success">
          <i data-feather="check-circle" style="width: 30px; height: 30px;"></i>
          <div>
            <h3>✅ Pesanan Berhasil Dibuat!</h3>
            <p>Terima kasih atas pesanan Anda. Berikut adalah struk pembayaran:</p>
          </div>
        </div>

        <!-- Receipt/Struk -->
        <div class="receipt-container">
          <div class="receipt-header">
            <h2>🧾 STRUK PEMBAYARAN</h2>
            <p>NusaJava Eats - Bumbu Instant Nusantara</p>
          </div>

          <div class="receipt-info">
            <div>
              <strong>Nomor Pesanan:</strong>
              <?php echo $order_details['order_number']; ?>
            </div>
            <div>
              <strong>Tanggal:</strong>
              <?php echo date('d/m/Y H:i'); ?>
            </div>
            <div>
              <strong>Nama Customer:</strong>
              <?php echo htmlspecialchars($order_details['customer_name']); ?>
            </div>
            <div>
              <strong>Email:</strong>
              <?php echo htmlspecialchars($order_details['customer_email']); ?>
            </div>
          </div>

          <?php if (!empty($order_details['notes'])): ?>
          <div style="background: var(--light-brown); padding: 15px; border-radius: 10px; margin-bottom: 25px;">
            <strong>Catatan:</strong><br>
            <?php echo htmlspecialchars($order_details['notes']); ?>
          </div>
          <?php endif; ?>

          <div class="receipt-items">
            <h4 style="color: var(--secondaryColor); margin-bottom: 15px;">Detail Pesanan:</h4>
            <?php foreach ($order_details['items'] as $item): ?>
            <div class="receipt-item">
              <div>
                <strong><?php echo htmlspecialchars($item['name']); ?></strong><br>
                <small><?php echo $item['quantity']; ?> x Rp<?php echo number_format($item['price'], 0, ',', '.'); ?></small>
              </div>
              <div style="font-weight: bold; color: var(--accentColor);">
                Rp<?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <div class="receipt-totals">
            <div class="receipt-total">
              <span>Subtotal:</span>
              <span>Rp<?php echo number_format($order_details['subtotal'], 0, ',', '.'); ?></span>
            </div>
            <div class="receipt-total final">
              <span>TOTAL PEMBAYARAN:</span>
              <span>Rp<?php echo number_format($order_details['total'], 0, ',', '.'); ?></span>
            </div>
          </div>

          <div style="text-align: center; margin-top: 25px; color: var(--doubleColor);">
            <p><strong>Metode Pembayaran:</strong> <?php echo strtoupper($order_details['payment_method']); ?></p>
            <p><strong>Status:</strong> Menunggu Konfirmasi Admin</p>
            <hr style="margin: 15px 0; border: 1px dashed var(--mainColor);">
            <p style="font-size: 0.9em;">Terima kasih telah berbelanja di NusaJava Eats!</p>
            <p style="font-size: 0.9em;">Silakan screenshot struk ini sebagai bukti pembayaran</p>
          </div>
        </div>

        <div class="success-actions">
          <a href="home-login.php" class="btn btn-primary">
            <i data-feather="home"></i> Kembali ke Beranda
          </a>
          <a href="product.php" class="btn btn-secondary">
            <i data-feather="shopping-bag"></i> Lanjut Belanja
          </a>
        </div>

        <script>
          // Clear cart after successful order
          localStorage.removeItem('cart');
        </script>

      <?php else: ?>
        <div class="checkout-header">
          <h1>
            <i data-feather="credit-card"></i>
            Checkout
          </h1>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-error">
            <i data-feather="alert-circle" style="width: 24px; height: 24px;"></i>
            <?php echo htmlspecialchars($error); ?>
          </div>
        <?php endif; ?>

        <div id="orderSummary" class="order-summary">
          <h3>
            <i data-feather="shopping-cart"></i>
            Ringkasan Pesanan
          </h3>
          <div id="orderItems"></div>
          <div class="summary-item summary-total">
            <span>Total Pembayaran:</span>
            <span id="orderTotal">Rp0</span>
          </div>
        </div>

        <form method="POST" id="checkoutForm">
          <input type="hidden" name="cart_data" id="cartData">

          <div class="form-group">
            <label for="customer_name">
              <i data-feather="user"></i>
              Nama Lengkap:
            </label>
            <input type="text" id="customer_name" name="customer_name" required placeholder="Masukkan nama lengkap Anda">
          </div>

          <div class="form-group">
            <label for="customer_email">
              <i data-feather="mail"></i>
              Email:
            </label>
            <input type="email" id="customer_email" name="customer_email" required placeholder="example@email.com">
          </div>

          <div class="form-group">
            <label for="notes">
              <i data-feather="file-text"></i>
              Catatan (Opsional):
            </label>
            <textarea id="notes" name="notes" placeholder="Catatan tambahan untuk pesanan Anda"></textarea>
          </div>

          <div class="form-group">
            <label for="shipping_address">
              <i data-feather="map-pin"></i>
              Alamat Pengiriman:
            </label>
            <textarea id="shipping_address" name="shipping_address" required placeholder="Masukkan alamat lengkap untuk pengiriman produk"></textarea>
          </div>

          <div class="form-group">
            <label for="phone">
              <i data-feather="phone"></i>
              Nomor Telepon:
            </label>
            <input type="tel" id="phone" name="phone" required placeholder="08xxxxxxxxxx">
          </div>

          <div class="form-group">
            <label>
              <i data-feather="credit-card"></i>
              Metode Pembayaran:
            </label>
            <div class="payment-methods">
              <div class="payment-option">
                <input type="radio" id="cod" name="payment_method" value="cod" required>
                <label for="cod">
                  <i data-feather="truck" style="margin-bottom: 10px;"></i><br>
                  <strong>COD</strong><br>
                  <small>Bayar saat diterima</small>
                </label>
              </div>
              <div class="payment-option">
                <input type="radio" id="qris" name="payment_method" value="qris" required>
                <label for="qris">
                  <i data-feather="smartphone" style="margin-bottom: 10px;"></i><br>
                  <strong>QRIS</strong><br>
                  <small>Scan QR Code</small>
                </label>
              </div>
              <div class="payment-option">
                <input type="radio" id="gopay" name="payment_method" value="gopay" required>
                <label for="gopay">
                  <i data-feather="smartphone" style="margin-bottom: 10px;"></i><br>
                  <strong>GoPay</strong><br>
                  <small>Bayar dengan GoPay</small>
                </label>
              </div>
              <div class="payment-option">
                <input type="radio" id="ovo" name="payment_method" value="ovo" required>
                <label for="ovo">
                  <i data-feather="smartphone" style="margin-bottom: 10px;"></i><br>
                  <strong>OVO</strong><br>
                  <small>Bayar dengan OVO</small>
                </label>
              </div>
              <div class="payment-option">
                <input type="radio" id="dana" name="payment_method" value="dana" required>
                <label for="dana">
                  <i data-feather="smartphone" style="margin-bottom: 10px;"></i><br>
                  <strong>DANA</strong><br>
                  <small>Bayar dengan DANA</small>
                </label>
              </div>
            </div>
          </div>

          <button type="submit" class="submit-btn">
            <i data-feather="check"></i>
            Buat Pesanan
          </button>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <script>
    function loadOrderSummary() {
      const cart = JSON.parse(localStorage.getItem('cart') || '[]');

      if (cart.length === 0) {
        alert('Keranjang kosong!');
        window.location.href = 'cart.php';
        return;
      }

      const orderItemsContainer = document.getElementById('orderItems');
      let subtotal = 0;
      let itemsHTML = '';

      cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;

        itemsHTML += `
          <div class="summary-item">
            <div class="item-details">
              <div class="item-name">${item.name}</div>
              <div class="item-info">${item.quantity} x Rp${item.price.toLocaleString('id-ID')}</div>
            </div>
            <div class="item-total">Rp${itemTotal.toLocaleString('id-ID')}</div>
          </div>
        `;
      });

      const shipping = 0;
      const total = subtotal + shipping;

      itemsHTML += `
        <div class="summary-item">
          <span>Subtotal:</span>
          <span>Rp${subtotal.toLocaleString('id-ID')}</span>
        </div>
      `;

      orderItemsContainer.innerHTML = itemsHTML;
      document.getElementById('orderTotal').textContent = `Rp${total.toLocaleString('id-ID')}`;

      // Set cart data for form submission
      document.getElementById('cartData').value = JSON.stringify(cart);
    }

    // Load order summary when page loads
    document.addEventListener('DOMContentLoaded', function() {
      <?php if (!$success): ?>
      loadOrderSummary();
      <?php endif; ?>
      feather.replace();
    });
  </script>
</body>
</html>
