<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>

<nav class="navbar" id="mainNavbar">
  <div class="logo">
    <img src="Assets/Img/NusaJava Eats.png" alt="Logo" />
    <span class="logo-text" data-i18n="header_brand">NusaJava Eats</span>
  </div>

  <ul class="nav-links">
    <li><a href="home-login.php" data-i18n="nav_home" <?php echo ($current_page == 'home-login') ? 'class="active"' : ''; ?>>Beranda</a></li>
    <li><a href="home-login.php" data-i18n="nav_about" <?php echo ($current_page == 'about') ? 'class="active"' : ''; ?>>Tentang Kami</a></li>
    <li><a href="resep.php" data-i18n="nav_recipe" <?php echo ($current_page == 'resep' || $current_page == 'resep-detail') ? 'class="active"' : ''; ?>>Resep</a></li>
    <li><a href="product.php" data-i18n="nav_product" <?php echo ($current_page == 'product' || $current_page == 'product-detail') ? 'class="active"' : ''; ?>>Produk</a></li>
    <li><a href="contact.php" data-i18n="nav_contact" <?php echo ($current_page == 'contact') ? 'class="active"' : ''; ?>>Kontak</a></li>
    <li><a href="komunitas.php" data-i18n="nav_community" <?php echo ($current_page == 'community') ? 'class="active"' : ''; ?>>Komunitas</a></li>
  </ul>

  <div class="nav-actions">
    <?php if (isset($_SESSION['user_id'])): ?>
      <div class="user-info">
        <span>Hi! <?= htmlspecialchars($_SESSION['user_name']) ?></span>
        <form action="logout.php" method="POST">
          <button type="submit" class="login-button"><i data-feather="log-out"></i> Logout</button>
        </form>
      </div>
    <?php else: ?>
      <button class="login-button" id="btnLogin"><i data-feather="user"></i> <span>Masuk</span></button>
    <?php endif; ?>

    <div class="cart-navbar-wrapper">
      <button type="button" class="cart-navbar-btn" id="cartNavbarBtn" aria-label="Cart" title="Keranjang Belanja">
        <i data-feather="shopping-cart"></i>
        <span class="cart-badge" id="cartBadge">0</span>
      </button>
      <div class="cart-dropdown" id="cartDropdown">
        <div id="cartDropdownContent">
          <!-- Cart content will be loaded by JavaScript -->
        </div>
      </div>
    </div>
  </div>
</nav>

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

.navbar {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  background: #5b2620;
  backdrop-filter: none;
  transition: background-color 0.4s ease, backdrop-filter 0.4s ease;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 50px;
  border-bottom-left-radius: 5px;
  border-bottom-right-radius: 5px;
  box-shadow: none;
}

.navbar.scrolled {
  background: rgba(91, 38, 32, 0.4);
  backdrop-filter: blur(8px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
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

.nav-links {
  display: flex;
  list-style: none;
  gap: 30px;
  margin: 0;
  padding: 0;
}

.nav-links a {
  color: var(--white);
  text-decoration: none;
  font-weight: 500;
  transition: all 0.3s ease;
  padding: 8px 16px;
  border-radius: 20px;
}

.nav-links a:hover,
.nav-links a.active {
  background-color: var(--mainColor);
  color: var(--secondaryColor);
  transform: translateY(-2px);
  text-decoration: none !important;
}

.nav-actions {
  display: flex;
  align-items: center;
  gap: 15px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 10px;
  color: var(--white);
  font-weight: 500;
}

.login-button {
  background: var(--accentColor);
  color: var(--white);
  border: none;
  padding: 10px 20px;
  border-radius: 25px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.login-button:hover {
  background: var(--mainColor);
  color: var(--secondaryColor);
  transform: translateY(-2px);
}

/* Cart Navbar - ENHANCED VERSION */
.cart-navbar-wrapper {
  position: relative;
  z-index: 1002;
}

.cart-navbar-btn {
  background: var(--mainColor) !important;
  color: var(--secondaryColor) !important;
  border: 2px solid transparent !important;
  padding: 12px !important;
  border-radius: 50% !important;
  cursor: pointer !important;
  font-size: 20px !important;
  transition: all 0.3s ease !important;
  position: relative !important;
  width: 50px !important;
  height: 50px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  outline: none !important;
}

.cart-navbar-btn:hover {
  background: var(--white) !important;
  border-color: var(--mainColor) !important;
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
  background: linear-gradient(45deg, var(--accentColor), #e74c3c) !important;
  color: white !important;
  border-radius: 50% !important;
  width: 24px !important;
  height: 24px !important;
  font-size: 12px !important;
  font-weight: bold !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  min-width: 24px !important;
  border: 2px solid white !important;
  box-shadow: 0 2px 8px rgba(0,0,0,0.2) !important;
  opacity: 1 !important;
  transition: all 0.3s ease !important;
}

.cart-badge.hidden {
  opacity: 0 !important;
  transform: scale(0) !important;
}

.cart-dropdown {
  position: absolute !important;
  top: calc(100% + 15px) !important;
  right: 0 !important;
  background: white !important;
  border: 2px solid var(--mainColor) !important;
  border-radius: 20px !important;
  box-shadow: 0 12px 40px rgba(0,0,0,0.2) !important;
  width: 420px !important;
  max-height: 580px !important;
  overflow: hidden !important;
  z-index: 10001 !important;
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
  border-bottom: 8px solid var(--mainColor) !important;
}

/* Responsive */
@media (max-width: 768px) {
  .nav-links {
    display: none;
  }

  .navbar {
    padding: 10px 15px;
  }

  .logo-text {
    font-size: 1.2em;
  }

  .cart-dropdown {
    width: 320px !important;
    right: -20px !important;
  }
}
</style>

<script>
// Scroll effect untuk navbar
window.addEventListener('scroll', function() {
  const navbar = document.getElementById('mainNavbar');
  if (window.scrollY > 10) {
    navbar.classList.add('scrolled');
  } else {
    navbar.classList.remove('scrolled');
  }
});

// Initialize cart functionality when navbar loads
document.addEventListener('DOMContentLoaded', function() {
  console.log('🚀 Navbar loaded, initializing cart...');

  // Wait for cart.js to load
  function initializeCart() {
    if (typeof updateCartCount === 'function') {
      updateCartCount();
      console.log('✅ Cart count updated');
    } else {
      console.warn('⚠️ updateCartCount function not available, retrying...');
      setTimeout(initializeCart, 100);
      return;
    }

    // Setup cart button click handler
    const cartBtn = document.getElementById('cartNavbarBtn');
    const cartDropdown = document.getElementById('cartDropdown');

    if (cartBtn && cartDropdown) {
      console.log('✅ Cart elements found, setting up events...');

      // Remove any existing handlers
      cartBtn.onclick = null;

      // Add click handler
      cartBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('🛒 Cart button clicked!');

        if (typeof toggleCartDropdown === 'function') {
          toggleCartDropdown();
        } else {
          console.error('❌ toggleCartDropdown function not available');
        }
      });

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

      console.log('✅ Cart button events setup completed');
    } else {
      console.error('❌ Cart button or dropdown not found!');
    }
  }

  // Start initialization
  initializeCart();
});

// Ensure feather icons are replaced
feather.replace();
</script>
