// Enhanced Cart functionality for NusaJava Eats - COMPLETELY FIXED VERSION

// Add CSS styles for notifications and cart dropdown
function addCartStyles() {
  if (document.getElementById('cart-styles')) return;

  const style = document.createElement('style');
  style.id = 'cart-styles';
  style.textContent = `
    /* Cart notification styles - COMPLETELY FIXED VERSION */
    .cart-notification {
      position: fixed !important;
      top: 90px !important;
      right: 20px !important;
      background: #28a745 !important;
      color: white !important;
      padding: 14px 20px !important;
      border-radius: 12px !important;
      box-shadow: 0 6px 20px rgba(0,0,0,0.25) !important;
      z-index: 99999 !important;
      display: flex !important;
      align-items: center !important;
      gap: 12px !important;
      transform: translateX(120%) !important;
      transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55) !important;
      max-width: 350px !important;
      min-width: 280px !important;
      font-weight: 600 !important;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
      font-size: 14px !important;
      border-left: 5px solid rgba(255,255,255,0.4) !important;
      backdrop-filter: blur(10px) !important;
    }

    .cart-notification.show {
      transform: translateX(0) !important;
    }

    .cart-notification.error {
      background: #dc3545 !important;
      border-left-color: rgba(255,255,255,0.4) !important;
    }

    .cart-notification.info {
      background: #17a2b8 !important;
      border-left-color: rgba(255,255,255,0.4) !important;
    }

    .cart-notification.remove {
      background: #fd7e14 !important;
      border-left-color: rgba(255,255,255,0.4) !important;
    }

    .notification-icon {
      font-size: 18px !important;
      font-weight: bold !important;
      flex-shrink: 0 !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      width: 24px !important;
      height: 24px !important;
      background: rgba(255,255,255,0.2) !important;
      border-radius: 50% !important;
    }

    .notification-message {
      flex: 1 !important;
      line-height: 1.4 !important;
    }

    /* Cart dropdown styles - ENHANCED VERSION */
    .cart-dropdown {
      position: absolute !important;
      top: calc(100% + 15px) !important;
      right: 0 !important;
      background: white !important;
      border: 2px solid #feba71 !important;
      border-radius: 20px !important;
      box-shadow: 0 12px 40px rgba(0,0,0,0.2) !important;
      width: 420px !important;
      max-height: 580px !important;
      overflow: hidden !important;
      z-index: 9999 !important;
      opacity: 0 !important;
      visibility: hidden !important;
      transform: translateY(-15px) scale(0.95) !important;
      transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55) !important;
      pointer-events: none !important;
    }

    .cart-dropdown.show {
      opacity: 1 !important;
      visibility: visible !important;
      transform: translateY(0) scale(1) !important;
      pointer-events: auto !important;
    }

    .cart-dropdown::before {
      content: '' !important;
      position: absolute !important;
      top: -8px !important;
      right: 20px !important;
      width: 0 !important;
      height: 0 !important;
      border-left: 8px solid transparent !important;
      border-right: 8px solid transparent !important;
      border-bottom: 8px solid #feba71 !important;
    }

    /* Cart navbar button - ENHANCED */
    .cart-navbar-btn {
      position: relative !important;
      background: #feba71 !important;
      border: 2px solid transparent !important;
      color: #5b2620 !important;
      font-size: 20px !important;
      cursor: pointer !important;
      padding: 14px !important;
      border-radius: 50% !important;
      transition: all 0.3s ease !important;
      outline: none !important;
      user-select: none !important;
      width: 50px !important;
      height: 50px !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
    }

    .cart-navbar-btn:hover {
      background: white !important;
      border-color: #feba71 !important;
      transform: scale(1.1) !important;
      box-shadow: 0 4px 15px rgba(254, 186, 113, 0.4) !important;
    }

    .cart-navbar-btn:active {
      transform: scale(0.95) !important;
    }

    .cart-badge {
      position: absolute !important;
      top: -8px !important;
      right: -8px !important;
      background: linear-gradient(45deg, #af2d2d, #e74c3c) !important;
      color: white !important;
      border-radius: 50% !important;
      width: 24px !important;
      height: 24px !important;
      font-size: 12px !important;
      font-weight: bold !important;
      display: none !important;
      align-items: center !important;
      justify-content: center !important;
      min-width: 24px !important;
      border: 2px solid white !important;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2) !important;
    }

    /* Cart title - ENHANCED */
    .cart-title {
      background: linear-gradient(45deg, #5b2620, #816040) !important;
      color: white !important;
      padding: 20px !important;
      font-weight: bold !important;
      font-size: 18px !important;
      display: flex !important;
      align-items: center !important;
      gap: 12px !important;
      border-radius: 18px 18px 0 0 !important;
      border-bottom: 3px solid #feba71 !important;
    }

    .cart-items {
      max-height: 320px !important;
      overflow-y: auto !important;
      padding: 15px !important;
      background: #fafafa !important;
    }

    .cart-items::-webkit-scrollbar {
      width: 6px !important;
    }

    .cart-items::-webkit-scrollbar-track {
      background: #f1f1f1 !important;
      border-radius: 3px !important;
    }

    .cart-items::-webkit-scrollbar-thumb {
      background: #ccc !important;
      border-radius: 3px !important;
    }

    .cart-items::-webkit-scrollbar-thumb:hover {
      background: #af2d2d !important;
    }

    /* Cart item styles - ENHANCED */
    .cart-item {
      display: flex !important;
      align-items: center !important;
      gap: 15px !important;
      margin-bottom: 15px !important;
      padding: 15px !important;
      background: white !important;
      border-radius: 12px !important;
      position: relative !important;
      transition: all 0.3s ease !important;
      border: 2px solid transparent !important;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
    }

    .cart-item:hover {
      border-color: #feba71 !important;
      transform: translateY(-2px) !important;
      box-shadow: 0 4px 15px rgba(0,0,0,0.15) !important;
    }

    .cart-item-img {
      width: 60px !important;
      height: 60px !important;
      object-fit: cover !important;
      border-radius: 8px !important;
      border: 2px solid #f4e4d0 !important;
      flex-shrink: 0 !important;
      background: #f4e4d0 !important;
    }

    .cart-item-info {
      flex: 1 !important;
      min-width: 0 !important;
    }

    .cart-item-name {
      font-weight: 700 !important;
      font-size: 14px !important;
      margin-bottom: 6px !important;
      color: #5b2620 !important;
      line-height: 1.3 !important;
      display: -webkit-box !important;
      -webkit-line-clamp: 2 !important;
      -webkit-box-orient: vertical !important;
      overflow: hidden !important;
    }

    .cart-item-brand {
      color: #816040 !important;
      font-size: 11px !important;
      margin-bottom: 8px !important;
      font-style: italic !important;
    }

    .cart-item-price {
      color: #af2d2d !important;
      font-size: 13px !important;
      font-weight: 700 !important;
      margin-bottom: 10px !important;
    }

    .cart-quantity-controls {
      display: flex !important;
      align-items: center !important;
      gap: 8px !important;
      background: #f8f9fa !important;
      padding: 6px !important;
      border-radius: 20px !important;
      border: 1px solid #e9ecef !important;
    }

    .quantity-btn {
      background: #6c757d !important;
      color: white !important;
      border: none !important;
      width: 28px !important;
      height: 28px !important;
      border-radius: 50% !important;
      font-size: 14px !important;
      cursor: pointer !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      transition: all 0.2s ease !important;
      outline: none !important;
      font-weight: bold !important;
    }

    .quantity-btn:hover {
      background: #af2d2d !important;
      transform: scale(1.1) !important;
    }

    .quantity-btn.plus:hover {
      background: #28a745 !important;
    }

    .quantity-display {
      font-size: 13px !important;
      font-weight: bold !important;
      margin: 0 6px !important;
      min-width: 25px !important;
      text-align: center !important;
      background: white !important;
      padding: 4px 8px !important;
      border-radius: 6px !important;
      border: 1px solid #dee2e6 !important;
      color: #5b2620 !important;
    }

    .cart-item-remove {
      background: #dc3545 !important;
      color: white !important;
      border: none !important;
      width: 24px !important;
      height: 24px !important;
      border-radius: 50% !important;
      font-size: 12px !important;
      cursor: pointer !important;
      position: absolute !important;
      top: 10px !important;
      right: 10px !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      transition: all 0.2s ease !important;
      outline: none !important;
      font-weight: bold !important;
    }

    .cart-item-remove:hover {
      background: #c82333 !important;
      transform: scale(1.15) !important;
    }

    .cart-empty {
      padding: 40px 20px !important;
      text-align: center !important;
      color: #6c757d !important;
      background: white !important;
      margin: 15px !important;
      border-radius: 12px !important;
      border: 2px dashed #dee2e6 !important;
    }

    .cart-empty-icon {
      font-size: 3.5rem !important;
      color: #ddd !important;
      margin-bottom: 15px !important;
    }

    .cart-empty h4 {
      margin: 0 0 10px 0 !important;
      color: #5b2620 !important;
      font-size: 18px !important;
    }

    .cart-empty p {
      margin: 0 0 20px 0 !important;
      color: #666 !important;
      font-size: 14px !important;
    }

    .cart-empty-btn {
      background: linear-gradient(45deg, #af2d2d, #dc3545) !important;
      color: white !important;
      padding: 10px 20px !important;
      border-radius: 20px !important;
      text-decoration: none !important;
      font-weight: 600 !important;
      display: inline-flex !important;
      align-items: center !important;
      gap: 8px !important;
      transition: all 0.3s ease !important;
      font-size: 14px !important;
    }

    .cart-empty-btn:hover {
      background: linear-gradient(45deg, #8a2424, #c82333) !important;
      transform: translateY(-2px) !important;
      box-shadow: 0 4px 12px rgba(175, 45, 45, 0.4) !important;
    }

    .subtotal-row {
      display: flex !important;
      justify-content: space-between !important;
      align-items: center !important;
      font-weight: bold !important;
      font-size: 16px !important;
      margin: 0 15px 15px 15px !important;
      padding: 18px !important;
      color: #5b2620 !important;
      background: linear-gradient(45deg, #f4e4d0, #feba71) !important;
      border-radius: 12px !important;
      border: 2px solid #816040 !important;
    }

    .subtotal-label {
      display: flex !important;
      align-items: center !important;
      gap: 8px !important;
    }

    .subtotal-amount {
      color: #af2d2d !important;
      font-size: 18px !important;
    }

    .cart-dropdown-actions {
      display: flex !important;
      flex-direction: column !important;
      gap: 10px !important;
      padding: 15px !important;
      background: white !important;
      border-radius: 0 0 18px 18px !important;
    }

    .cart-btn-main {
      padding: 14px 20px !important;
      border: none !important;
      border-radius: 25px !important;
      width: 100% !important;
      font-weight: 700 !important;
      cursor: pointer !important;
      transition: all 0.3s ease !important;
      font-size: 14px !important;
      outline: none !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      gap: 10px !important;
    }

    .cart-btn-view {
      background: linear-gradient(45deg, #007bff, #0056b3) !important;
      color: white !important;
      border: 2px solid transparent !important;
    }

    .cart-btn-view:hover {
      background: linear-gradient(45deg, #0056b3, #004085) !important;
      transform: translateY(-2px) !important;
      box-shadow: 0 6px 16px rgba(0, 123, 255, 0.4) !important;
    }

    .cart-btn-checkout {
      background: linear-gradient(45deg, #28a745, #20c997) !important;
      color: white !important;
      border: 2px solid transparent !important;
    }

    .cart-btn-checkout:hover {
      background: linear-gradient(45deg, #1e7e34, #17a2b8) !important;
      transform: translateY(-2px) !important;
      box-shadow: 0 6px 16px rgba(40, 167, 69, 0.4) !important;
    }

    @media (max-width: 768px) {
      .cart-dropdown {
        width: 320px !important;
        right: -10px !important;
      }

      .cart-notification {
        right: 10px !important;
        max-width: 300px !important;
        min-width: 250px !important;
      }

      .cart-item {
        padding: 12px !important;
      }

      .cart-item-img {
        width: 50px !important;
        height: 50px !important;
      }
    }
  `;
  document.head.appendChild(style);
}

// Initialize cart styles
addCartStyles();

// Update cart count in navbar
function updateCartCount() {
  const cart = JSON.parse(localStorage.getItem('cart') || '[]');
  const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
  const cartBadge = document.getElementById('cartBadge');
  if (cartBadge) {
    cartBadge.textContent = totalItems;
    cartBadge.style.display = totalItems > 0 ? 'flex' : 'none';
  }
}

// Enhanced notification function - PREMIUM VERSION
function showNotification(message, type = 'success') {
  // Remove existing notifications
  const existingNotifications = document.querySelectorAll('.cart-notification');
  existingNotifications.forEach(notification => {
    notification.style.transform = 'translateX(120%)';
    setTimeout(() => notification.remove(), 300);
  });

  // Create notification element
  const notification = document.createElement('div');
  notification.className = `cart-notification ${type}`;

  // Icons based on type
  let icon;
  switch(type) {
    case 'success':
      icon = '✓';
      break;
    case 'error':
      icon = '⚠';
      break;
    case 'info':
      icon = 'ℹ';
      break;
    case 'remove':
      icon = '🗑';
      break;
    default:
      icon = '🛒';
  }

  // Create icon element
  const iconSpan = document.createElement('span');
  iconSpan.className = 'notification-icon';
  iconSpan.textContent = icon;

  // Create message element
  const messageSpan = document.createElement('span');
  messageSpan.className = 'notification-message';
  messageSpan.textContent = message;

  notification.appendChild(iconSpan);
  notification.appendChild(messageSpan);

  // Add to page
  document.body.appendChild(notification);

  // Animate in with enhanced effect
  requestAnimationFrame(() => {
    notification.classList.add('show');
  });

  // Auto remove after 4 seconds with smooth animation
  setTimeout(() => {
    notification.style.transform = 'translateX(120%)';
    notification.style.opacity = '0';
    setTimeout(() => {
      if (notification.parentNode) {
        notification.remove();
      }
    }, 400);
  }, 4000);
}

// Enhanced add to cart function - FIXED VERSION
function addToCart(productId, productName, productPrice, productImage, productStock, productBrand) {
  try {
    // Validate inputs
    if (!productId || !productName || !productPrice) {
      showNotification('Data produk tidak lengkap!', 'error');
      return;
    }

    // Get existing cart from localStorage
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');

    // Check if product already exists in cart
    const existingItemIndex = cart.findIndex(item => item.id === productId);

    if (existingItemIndex > -1) {
      // Update quantity if product exists
      const newQuantity = cart[existingItemIndex].quantity + 1;

      if (newQuantity <= productStock) {
        cart[existingItemIndex].quantity = newQuantity;
        showNotification(`${productName} ditambahkan! Total: ${newQuantity}x`, 'success');
      } else {
        showNotification(`Stok tidak mencukupi! Maksimal ${productStock} item`, 'error');
        return;
      }
    } else {
      // Add new product to cart
      cart.push({
        id: productId,
        name: productName,
        price: productPrice,
        image_url: productImage || 'https://via.placeholder.com/120x120/f4e4d0/5b2620?text=No+Image',
        quantity: 1,
        max_stock: productStock,
        brand: productBrand || 'Unknown'
      });
      showNotification(`${productName} berhasil ditambahkan ke keranjang!`, 'success');
    }

    // Save updated cart to localStorage
    localStorage.setItem('cart', JSON.stringify(cart));

    // Update cart count and dropdown
    updateCartCount();
    updateCartDropdown();

  } catch (error) {
    console.error('Error adding to cart:', error);
    showNotification('Terjadi kesalahan saat menambahkan ke keranjang', 'error');
  }
}

// Update cart dropdown content - ENHANCED VERSION
function updateCartDropdown() {
  const cart = JSON.parse(localStorage.getItem('cart') || '[]');
  const cartDropdownContent = document.getElementById('cartDropdownContent');

  if (!cartDropdownContent) return;

  if (cart.length === 0) {
    cartDropdownContent.innerHTML = `
      <div class="cart-title">
        🛒 Keranjang Belanja
      </div>
      <div class="cart-empty">
        <div class="cart-empty-icon">🛒</div>
        <h4>Keranjang Kosong</h4>
        <p>Belum ada produk yang ditambahkan ke keranjang Anda</p>
        <a href="product.php" class="cart-empty-btn">
           🛍️ Mulai Belanja Sekarang
        </a>
      </div>
    `;
    return;
  }

  let cartHTML = '<div class="cart-title">🛒 Keranjang Belanja (' + cart.length + ' item)</div>';
  cartHTML += '<div class="cart-items">';

  let total = 0;

  cart.forEach((item, index) => {
    const itemTotal = item.price * item.quantity;
    total += itemTotal;

    cartHTML += `
      <div class="cart-item">
        <img src="${item.image_url}" alt="${item.name}" class="cart-item-img">
        <div class="cart-item-info">
          <div class="cart-item-name">${item.name}</div>
          <div class="cart-item-brand">Brand: ${item.brand}</div>
          <div class="cart-item-price">Rp${item.price.toLocaleString('id-ID')}</div>
          <div class="cart-quantity-controls">
            <button class="quantity-btn" onclick="updateCartQuantity(${index}, -1)" title="Kurangi">−</button>
            <span class="quantity-display">${item.quantity}</span>
            <button class="quantity-btn plus" onclick="updateCartQuantity(${index}, 1)" title="Tambah">+</button>
          </div>
        </div>
        <button class="cart-item-remove" onclick="removeFromCart(${index})" title="Hapus item">×</button>
      </div>
    `;
  });

  cartHTML += '</div>';

  cartHTML += `
    <div class="subtotal-row">
      <span class="subtotal-label">
        💰 Total Belanja:
      </span>
      <span class="subtotal-amount">Rp${total.toLocaleString('id-ID')}</span>
    </div>
    <div class="cart-dropdown-actions">
      <button class="cart-btn-main cart-btn-view" onclick="viewCart()">
        🛒 Lihat Keranjang Lengkap
      </button>
      <button class="cart-btn-main cart-btn-checkout" onclick="checkout()">
        💳 Checkout Sekarang
      </button>
    </div>
  `;

  cartDropdownContent.innerHTML = cartHTML;
}

// Update quantity in cart
function updateCartQuantity(index, change) {
  try {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');

    if (index >= 0 && index < cart.length) {
      const item = cart[index];
      const newQuantity = item.quantity + change;

      if (newQuantity <= 0) {
        // Remove item if quantity becomes 0
        cart.splice(index, 1);
        showNotification(`${item.name} dihapus dari keranjang`, 'remove');
      } else if (newQuantity <= item.max_stock) {
        item.quantity = newQuantity;
        showNotification(`${item.name} diperbarui (${newQuantity}x)`, 'success');
      } else {
        showNotification(`Stok maksimal ${item.max_stock} untuk ${item.name}`, 'error');
        return;
      }

      localStorage.setItem('cart', JSON.stringify(cart));
      updateCartCount();
      updateCartDropdown();
    }
  } catch (error) {
    console.error('Error updating cart quantity:', error);
    showNotification('Terjadi kesalahan saat memperbarui keranjang', 'error');
  }
}

// Remove item from cart
function removeFromCart(index) {
  try {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');

    if (index >= 0 && index < cart.length) {
      const item = cart[index];
      cart.splice(index, 1);
      localStorage.setItem('cart', JSON.stringify(cart));
      showNotification(`${item.name} berhasil dihapus dari keranjang`, 'remove');
      updateCartCount();
      updateCartDropdown();
    }
  } catch (error) {
    console.error('Error removing from cart:', error);
    showNotification('Terjadi kesalahan saat menghapus item', 'error');
  }
}

// View cart page - redirect to cart.php
function viewCart() {
  window.location.href = 'cart.php';
}

// Checkout function - redirect to checkout.php
function checkout() {
  const cart = JSON.parse(localStorage.getItem('cart') || '[]');
  if (cart.length === 0) {
    showNotification('Keranjang masih kosong!', 'error');
    return;
  }
  showNotification('Mengarahkan ke halaman checkout...', 'info');
  setTimeout(() => {
    window.location.href = 'checkout.php';
  }, 1500);
}

// Clear cart
function clearCart() {
  localStorage.removeItem('cart');
  updateCartCount();
  updateCartDropdown();
  showNotification('Keranjang berhasil dikosongkan', 'info');
}

// Toggle cart dropdown - COMPLETELY FIXED VERSION
function toggleCartDropdown() {
  const dropdown = document.getElementById('cartDropdown');

  if (!dropdown) {
    console.error('Cart dropdown element not found!');
    return;
  }

  console.log('Toggling cart dropdown'); // Debug log

  if (dropdown.classList.contains('show')) {
    dropdown.classList.remove('show');
    console.log('Dropdown closed');
  } else {
    dropdown.classList.add('show');
    updateCartDropdown();
    console.log('Dropdown opened');
  }
}

// Initialize cart on page load
document.addEventListener('DOMContentLoaded', function() {
  console.log('Cart initialization started');

  // Update cart count first
  updateCartCount();

  // Setup cart dropdown toggle with multiple fallbacks
  const cartBtn = document.getElementById('cartNavbarBtn');

  if (cartBtn) {
    console.log('Cart button found, setting up events');

    // Remove any existing handlers
    cartBtn.onclick = null;

    // Add click event with proper handling
    cartBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      console.log('Cart button clicked!'); // Debug log
      toggleCartDropdown();
    });

    // Alternative: Also handle with onclick
    cartBtn.onclick = function(e) {
      e.preventDefault();
      e.stopPropagation();
      console.log('Cart button onclick triggered!'); // Debug log
      toggleCartDropdown();
    };
  } else {
    console.error('Cart button not found!');
  }

  // Setup dropdown click handling
  const cartDropdown = document.getElementById('cartDropdown');

  if (cartDropdown) {
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!cartDropdown.contains(e.target) && !cartBtn.contains(e.target)) {
        cartDropdown.classList.remove('show');
      }
    });

    // Prevent dropdown from closing when clicking inside
    cartDropdown.addEventListener('click', function(e) {
      e.stopPropagation();
    });
  }

  console.log('Cart initialization completed');
});

// Export functions for global use
window.addToCart = addToCart;
window.updateCartQuantity = updateCartQuantity;
window.removeFromCart = removeFromCart;
window.viewCart = viewCart;
window.checkout = checkout;
window.clearCart = clearCart;
window.toggleCartDropdown = toggleCartDropdown;
window.updateCartCount = updateCartCount;
window.updateCartDropdown = updateCartDropdown;
window.showNotification = showNotification;
