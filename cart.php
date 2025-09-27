<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Keranjang Belanja - NusaJava Eats</title>
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
      padding-top: 100px;
    }

    .container {
      max-width: 1200px;
      margin: 20px auto 20px;
      padding: 20px;
    }
    .cart-container {
      background: var(--white);
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .cart-header {
      border-bottom: 3px solid var(--mainColor);
      padding-bottom: 20px;
      margin-bottom: 30px;
    }
    .cart-header h1 {
      color: var(--secondaryColor);
      font-size: 2.2em;
      display: flex;
      align-items: center;
      gap: 15px;
    }
    .cart-item {
      display: grid;
      grid-template-columns: 120px 1fr auto auto auto;
      gap: 25px;
      align-items: center;
      padding: 25px 0;
      border-bottom: 2px solid var(--light-brown);
    }
    .cart-item:last-child {
      border-bottom: none;
    }
    .item-image {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 15px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    .item-info h3 {
      margin-bottom: 8px;
      color: var(--secondaryColor);
      font-size: 1.2em;
    }
    .item-brand {
      color: var(--doubleColor);
      font-size: 0.95em;
      margin-bottom: 5px;
    }
    .item-price {
      font-weight: bold;
      color: var(--accentColor);
      font-size: 1.1em;
    }
    .quantity-controls {
      display: flex;
      align-items: center;
      gap: 12px;
      background: var(--light-brown);
      padding: 8px;
      border-radius: 25px;
    }
    .quantity-btn {
      background: var(--tripleColor);
      color: var(--white);
      border: none;
      width: 35px;
      height: 35px;
      border-radius: 50%;
      cursor: pointer;
      font-weight: bold;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .quantity-btn:hover {
      background: var(--accentColor);
      transform: scale(1.1);
    }
    .quantity-input {
      width: 60px;
      text-align: center;
      padding: 8px;
      border: 2px solid var(--tripleColor);
      border-radius: 8px;
      font-weight: bold;
      background: var(--white);
    }
    .item-total {
      font-weight: bold;
      color: var(--secondaryColor);
      font-size: 1.2em;
    }
    .remove-btn {
      background: var(--accentColor);
      color: var(--white);
      border: none;
      padding: 12px 18px;
      border-radius: 20px;
      cursor: pointer;
      font-weight: 500;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .remove-btn:hover {
      background: var(--secondaryColor);
      transform: translateY(-2px);
    }
    .cart-summary {
      background: linear-gradient(45deg, var(--tripleColor), var(--doubleColor));
      color: var(--white);
      padding: 30px;
      border-radius: 20px;
      margin-top: 30px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    }
    .summary-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
      font-size: 1.1em;
    }
    .total-row {
      border-top: 2px solid rgba(255,255,255,0.3);
      padding-top: 15px;
      font-weight: bold;
      font-size: 1.4em;
    }
    .checkout-btn {
      background: linear-gradient(45deg, var(--mainColor), #ffd700);
      color: var(--secondaryColor);
      border: none;
      padding: 18px 35px;
      border-radius: 25px;
      font-size: 1.2em;
      font-weight: bold;
      cursor: pointer;
      width: 100%;
      margin-top: 25px;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }
    .checkout-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.3);
    }
    .empty-cart {
      text-align: center;
      padding: 80px 20px;
      color: var(--doubleColor);
    }
    .empty-cart h3 {
      color: var(--secondaryColor);
      font-size: 2em;
      margin-bottom: 15px;
    }
    .empty-cart p {
      font-size: 1.1em;
      margin-bottom: 30px;
    }
    .continue-shopping-btn {
      background: linear-gradient(45deg, var(--accentColor), var(--secondaryColor));
      color: var(--white);
      padding: 15px 30px;
      text-decoration: none;
      border-radius: 25px;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    .continue-shopping-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }
    @media (max-width: 768px) {
      .cart-item {
        grid-template-columns: 1fr;
        gap: 15px;
        text-align: center;
      }
      .item-image {
        justify-self: center;
      }
      .container {
        margin: 10px auto 20px;
        padding: 15px;
      }
      .cart-container {
        padding: 20px;
      }
    }
  </style>
</head>

<body>
  <?php include 'navbar.php'; ?>

  <div class="container">
    <div class="cart-container">
      <div class="cart-header">
        <h1>
          <i data-feather="shopping-cart"></i>
          Keranjang Belanja
        </h1>
      </div>

      <div id="cartItems">
        <!-- Cart items will be loaded here by JavaScript -->
      </div>

      <div id="cartSummary" class="cart-summary" style="display: none;">
        <div class="summary-row">
          <span>Subtotal:</span>
          <span id="subtotal">Rp0</span>
        </div>
        <div class="summary-row total-row">
          <span>Total:</span>
          <span id="total">Rp0</span>
        </div>
        <button onclick="checkout()" class="checkout-btn">
          <i data-feather="credit-card"></i>
          Checkout
        </button>
      </div>

      <div id="emptyCart" class="empty-cart" style="display: none;">
        <h3>Keranjang Anda Kosong</h3>
        <p>Belum ada produk yang ditambahkan ke keranjang</p>
        <a href="product.php" class="continue-shopping-btn">
          <i data-feather="shopping-bag"></i>
          Mulai Belanja
        </a>
      </div>
    </div>
  </div>

  <!-- FIXED: Load cart.js BEFORE the inline script -->
  <script src="cart.js"></script>
  <script>
    function loadCart() {
      const cart = JSON.parse(localStorage.getItem('cart') || '[]');
      const cartItemsContainer = document.getElementById('cartItems');
      const cartSummary = document.getElementById('cartSummary');
      const emptyCart = document.getElementById('emptyCart');

      if (cart.length === 0) {
        cartItemsContainer.innerHTML = '';
        cartSummary.style.display = 'none';
        emptyCart.style.display = 'block';
        return;
      }

      emptyCart.style.display = 'none';
      cartSummary.style.display = 'block';

      let cartHTML = '';
      let subtotal = 0;

      cart.forEach((item, index) => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;

        cartHTML += `
          <div class="cart-item">
            <img src="${item.image_url || 'https://via.placeholder.com/120x120/f4e4d0/5b2620?text=No+Image'}" alt="${item.name}" class="item-image">
            <div class="item-info">
              <h3>${item.name}</h3>
              <div class="item-brand">Brand: ${item.brand || 'Unknown'}</div>
              <div class="item-price">Rp${item.price.toLocaleString('id-ID')}</div>
            </div>
            <div class="quantity-controls">
              <button class="quantity-btn" onclick="updateQuantity(${index}, -1)">
                <i data-feather="minus"></i>
              </button>
              <input type="number" class="quantity-input" value="${item.quantity}" min="1" max="${item.max_stock || 999}" onchange="setQuantity(${index}, this.value)">
              <button class="quantity-btn" onclick="updateQuantity(${index}, 1)">
                <i data-feather="plus"></i>
              </button>
            </div>
            <div class="item-total">Rp${itemTotal.toLocaleString('id-ID')}</div>
            <button class="remove-btn" onclick="removeItem(${index})">
              <i data-feather="trash-2"></i>
              Hapus
            </button>
          </div>
        `;
      });

      cartItemsContainer.innerHTML = cartHTML;
      feather.replace();

      // Update summary
      const shipping = 0;
      const total = subtotal + shipping;

      document.getElementById('subtotal').textContent = `Rp${subtotal.toLocaleString('id-ID')}`;
      document.getElementById('total').textContent = `Rp${total.toLocaleString('id-ID')}`;
    }

    function updateQuantity(index, change) {
      let cart = JSON.parse(localStorage.getItem('cart') || '[]');

      if (cart[index]) {
        const newQuantity = cart[index].quantity + change;

        if (newQuantity < 1) {
          cart[index].quantity = 1;
        } else if (cart[index].max_stock && newQuantity > cart[index].max_stock) {
          cart[index].quantity = cart[index].max_stock;
          showNotification(`Stok maksimal ${cart[index].max_stock} untuk ${cart[index].name}`, 'error');
        } else {
          cart[index].quantity = newQuantity;
        }

        localStorage.setItem('cart', JSON.stringify(cart));
        loadCart();
        updateCartCount();
      }
    }

    function setQuantity(index, newQuantity) {
      let cart = JSON.parse(localStorage.getItem('cart') || '[]');

      if (cart[index]) {
        const quantity = parseInt(newQuantity);

        if (quantity < 1) {
          cart[index].quantity = 1;
        } else if (cart[index].max_stock && quantity > cart[index].max_stock) {
          cart[index].quantity = cart[index].max_stock;
          showNotification(`Stok maksimal ${cart[index].max_stock} untuk ${cart[index].name}`, 'error');
        } else {
          cart[index].quantity = quantity;
        }

        localStorage.setItem('cart', JSON.stringify(cart));
        loadCart();
        updateCartCount();
      }
    }

    function removeItem(index) {
      if (confirm('Yakin ingin menghapus produk ini dari keranjang?')) {
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const item = cart[index];
        cart.splice(index, 1);
        localStorage.setItem('cart', JSON.stringify(cart));
        showNotification(`${item.name} dihapus dari keranjang`, 'info');
        loadCart();
        updateCartCount();
      }
    }

    function checkout() {
      const cart = JSON.parse(localStorage.getItem('cart') || '[]');

      if (cart.length === 0) {
        showNotification('Keranjang kosong!', 'error');
        return;
      }

      // Redirect to checkout page
      showNotification('Mengarahkan ke halaman checkout...', 'success');
      setTimeout(() => {
        window.location.href = 'checkout.php';
      }, 1000);
    }

    // Load cart when page loads
    document.addEventListener('DOMContentLoaded', function() {
      loadCart();
      updateCartCount();
      feather.replace();
    });

    feather.replace();
  </script>
</body>
</html>
