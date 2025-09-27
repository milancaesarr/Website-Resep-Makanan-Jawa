// Enhanced Cart functionality for NusaJava Eats - HOME PAGE VERSION
// This version shows a shopping prompt instead of full cart functionality

// Add CSS styles for notifications and cart dropdown
function addCartStyles() {
  if (document.getElementById('cart-styles-home')) return;

  const style = document.createElement('style');
  style.id = 'cart-styles-home';
  style.textContent = `
    /* Cart notification styles */
    .cart-notification {
      position: fixed;
      top: 80px;
      right: 20px;
      background: #27ae60;
      color: white;
      padding: 15px 20px;
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
      z-index: 10000;
      display: flex;
      align-items: center;
      gap: 10px;
      transform: translateX(100%);
      opacity: 0;
      transition: all 0.3s ease;
      max-width: 300px;
      font-weight: 500;
    }

    .cart-notification.show {
      transform: translateX(0);
      opacity: 1;
    }

    .cart-notification.error {
      background: #e74c3c;
    }

    .cart-notification.info {
      background: #3498db;
    }

    .cart-notification.success {
      background: #27ae60;
    }

    .cart-notification i {
      font-size: 1.2em;
    }

    /* Cart dropdown styles - Enhanced for Home Page */
    .cart-dropdown {
      position: absolute;
      top: 100%;
      right: 0;
      background: white;
      border: 1px solid #ddd;
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
      width: 380px;
      max-height: 500px;
      overflow-y: auto;
      z-index: 1000;
      opacity: 0;
      visibility: hidden;
      transform: translateY(-10px);
      transition: all 0.3s ease;
    }

    .cart-dropdown.open {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }

    .cart-dropdown::-webkit-scrollbar {
      width: 6px;
    }

    .cart-dropdown::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 3px;
    }

    .cart-dropdown::-webkit-scrollbar-thumb {
      background: #af2d2d;
      border-radius: 3px;
    }

    /* Cart navbar button */
    .cart-navbar-btn {
      position: relative;
      background: none;
      border: none;
      color: white;
      font-size: 1.2rem;
      cursor: pointer;
      padding: 8px 12px;
      border-radius: 8px;
      transition: background 0.3s ease;
    }

    .cart-navbar-btn:hover {
      background: rgba(255,255,255,0.1);
    }

    .cart-badge {
      position: absolute;
      top: -5px;
      right: -5px;
      background: #e74c3c;
      color: white;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      font-size: 12px;
      font-weight: bold;
      display: flex;
      align-items: center;
      justify-content: center;
      min-width: 20px;
    }

    /* Shopping Prompt Styles for Home Page */
    .shopping-prompt {
      padding: 30px;
      text-align: center;
      background: linear-gradient(135deg, #fff3ea, #ffeaa4);
      border-radius: 15px;
      margin: 15px;
    }

    .shopping-prompt-icon {
      background: linear-gradient(45deg, #af2d2d, #5b2620);
      color: white;
      width: 80px;
      height: 80px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.5rem;
      margin: 0 auto 20px auto;
      box-shadow: 0 5px 15px rgba(175, 45, 45, 0.3);
    }

    .shopping-prompt h3 {
      color: #5b2620;
      font-size: 1.8rem;
      margin-bottom: 15px;
      font-weight: bold;
    }

    .shopping-prompt p {
      color: #816040;
      font-size: 1.1rem;
      line-height: 1.6;
      margin-bottom: 25px;
      max-width: 280px;
      margin-left: auto;
      margin-right: auto;
    }

    .shopping-cta {
      background: linear-gradient(45deg, #af2d2d, #5b2620);
      color: white;
      padding: 15px 30px;
      border: none;
      border-radius: 25px;
      font-size: 1.1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
      box-shadow: 0 5px 15px rgba(175, 45, 45, 0.3);
    }

    .shopping-cta:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(175, 45, 45, 0.4);
      background: linear-gradient(45deg, #5b2620, #af2d2d);
    }

    .shopping-features {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
      margin-top: 20px;
      text-align: left;
    }

    .feature-item {
      display: flex;
      align-items: center;
      gap: 8px;
      color: #5b2620;
      font-weight: 500;
      font-size: 0.95rem;
    }

    .feature-item i {
      color: #af2d2d;
      font-size: 1rem;
    }

    @media (max-width: 768px) {
      .cart-dropdown {
        width: 300px;
        right: -20px;
      }

      .cart-notification {
        right: 10px;
        max-width: 280px;
      }

      .shopping-features {
        grid-template-columns: 1fr;
        gap: 10px;
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

// Enhanced notification function
function showNotification(message, type = 'info') {
  // Remove existing notifications
  const existingNotifications = document.querySelectorAll('.cart-notification');
  existingNotifications.forEach(notification => notification.remove());

  // Create notification element
  const notification = document.createElement('div');
  notification.className = `cart-notification ${type}`;

  // Add icon based on type
  const icon = document.createElement('i');
  icon.className = type === 'success' ? 'fas fa-check-circle' :
                   type === 'error' ? 'fas fa-exclamation-circle' :
                   type === 'info' ? 'fas fa-info-circle' :
                   'fas fa-shopping-cart';

  // Add message
  const messageSpan = document.createElement('span');
  messageSpan.textContent = message;

  notification.appendChild(icon);
  notification.appendChild(messageSpan);

  // Add to page
  document.body.appendChild(notification);

  // Animate in
  setTimeout(() => {
    notification.classList.add('show');
  }, 100);

  // Remove after 3 seconds
  setTimeout(() => {
    notification.classList.remove('show');
    setTimeout(() => {
      if (notification.parentNode) {
        notification.remove();
      }
    }, 300);
  }, 3000);
}

// Update cart dropdown content - HOME PAGE VERSION with Shopping Prompt
function updateCartDropdown() {
  const cart = JSON.parse(localStorage.getItem('cart') || '[]');
  const cartDropdownContent = document.getElementById('cartDropdownContent');

  if (!cartDropdownContent) return;

  // Always show shopping prompt on home page
  cartDropdownContent.innerHTML = `
    <div class="shopping-prompt">
      <div class="shopping-prompt-icon">
        <i class="fas fa-shopping-bag"></i>
      </div>
      <h3>Mau Belanja Bumbu Nusantara?</h3>
      <p>Temukan berbagai bumbu instan berkualitas tinggi dengan cita rasa autentik khas Nusantara yang akan membuat masakan Anda semakin istimewa!</p>

      <div class="shopping-features">
        <div class="feature-item">
          <i class="fas fa-star"></i>
          <span>Kualitas Premium</span>
        </div>
        <div class="feature-item">
          <i class="fas fa-shipping-fast"></i>
          <span>Pengiriman Cepat</span>
        </div>
        <div class="feature-item">
          <i class="fas fa-leaf"></i>
          <span>Bahan Alami</span>
        </div>
        <div class="feature-item">
          <i class="fas fa-heart"></i>
          <span>Rasa Autentik</span>
        </div>
      </div>

      <a href="product.php" class="shopping-cta">
        <i class="fas fa-shopping-cart"></i>
        Mulai Belanja Sekarang
      </a>
    </div>
  `;
}

// Toggle cart dropdown - HOME PAGE VERSION
function toggleCartDropdown() {
  const dropdown = document.getElementById('cartDropdown');
  if (dropdown) {
    dropdown.classList.toggle('open');
    if (dropdown.classList.contains('open')) {
      updateCartDropdown();
    }
  }
}

// Initialize cart on page load
document.addEventListener('DOMContentLoaded', function() {
  updateCartCount();

  // Setup cart dropdown toggle
  const cartBtn = document.getElementById('cartNavbarBtn');
  const cartDropdown = document.getElementById('cartDropdown');

  if (cartBtn && cartDropdown) {
    cartBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      toggleCartDropdown();
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!cartDropdown.contains(e.target) && !cartBtn.contains(e.target)) {
        cartDropdown.classList.remove('open');
      }
    });

    // Prevent dropdown from closing when clicking inside
    cartDropdown.addEventListener('click', function(e) {
      e.stopPropagation();
    });
  }
});

// Export functions for global use
window.toggleCartDropdown = toggleCartDropdown;
window.updateCartCount = updateCartCount;
window.updateCartDropdown = updateCartDropdown;
window.showNotification = showNotification;
