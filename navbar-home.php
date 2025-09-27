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

    <!-- Cart khusus untuk home page -->
    <div class="cart-navbar-wrapper">
      <button class="cart-navbar-btn" id="cartNavbarBtnHome" aria-label="Cart" onclick="toggleHomeCartDropdown()">
        <i data-feather="shopping-cart"></i>
        <span class="cart-badge" id="cartBadgeHome">0</span>
      </button>
      <div class="cart-dropdown" id="cartDropdownHome">
        <div id="cartDropdownContentHome">
          <!-- Cart content for home page only -->
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

  /* Garis bawah navbar */
  border-bottom: 2px solid var(--secondaryColor);
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

/* Cart untuk Home Page */
.cart-navbar-wrapper {
  position: relative;
}

.cart-navbar-btn {
  background: var(--mainColor);
  color: var(--secondaryColor);
  border: none;
  padding: 12px;
  border-radius: 50%;
  cursor: pointer;
  font-size: 18px;
  transition: all 0.3s ease;
  position: relative;
}

.cart-navbar-btn:hover {
  background: var(--white);
  transform: scale(1.1);
}

.cart-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: var(--accentColor);
  color: var(--white);
  border-radius: 50%;
  width: 20px;
  height: 20px;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

/* Cart dropdown - SIMPLIFIED */
.cart-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  background: var(--white);
  border-radius: 15px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
  width: 380px;
  max-height: 500px;
  overflow-y: auto;
  z-index: 1000;
  display: none;
}

.cart-dropdown.show {
  display: block;
}

/* Shopping Prompt Styles */
.home-shopping-prompt {
  padding: 30px;
  text-align: center;
  background: linear-gradient(135deg, #fff3ea, #ffeaa4);
  border-radius: 15px;
  margin: 15px;
}

.home-shopping-prompt-icon {
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

.home-shopping-prompt h3 {
  color: #5b2620;
  font-size: 1.8rem;
  margin-bottom: 15px;
  font-weight: bold;
}

.home-shopping-prompt p {
  color: #816040;
  font-size: 1.1rem;
  line-height: 1.6;
  margin-bottom: 25px;
  max-width: 280px;
  margin-left: auto;
  margin-right: auto;
}

.home-shopping-cta {
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

.home-shopping-cta:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(175, 45, 45, 0.4);
  background: linear-gradient(45deg, #5b2620, #af2d2d);
  text-decoration: none;
  color: white;
}

.home-shopping-features {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
  margin-top: 20px;
  text-align: left;
}

.home-feature-item {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #5b2620;
  font-weight: 500;
  font-size: 0.95rem;
}

.home-feature-item i {
  color: #af2d2d;
  font-size: 1rem;
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
    width: 300px;
    right: -20px;
  }

  .home-shopping-features {
    grid-template-columns: 1fr;
    gap: 10px;
  }
}
</style>


<script>
// SIMPLE AND WORKING CART FUNCTIONALITY
function updateHomeCartCount() {
  const cart = JSON.parse(localStorage.getItem('cart') || '[]');
  const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
  const cartBadge = document.getElementById('cartBadgeHome');
  if (cartBadge) {
    cartBadge.textContent = totalItems;
    cartBadge.style.display = totalItems > 0 ? 'flex' : 'none';
  }
}

function updateHomeCartDropdown() {
  const cartDropdownContent = document.getElementById('cartDropdownContentHome');
  if (!cartDropdownContent) return;

  cartDropdownContent.innerHTML = `
    <div class="home-shopping-prompt">
      <div class="home-shopping-prompt-icon">
        <i class="fas fa-shopping-bag"></i>
      </div>
      <h3>Mau Belanja Bumbu Nusantara?</h3>
      <p>Temukan berbagai bumbu instan berkualitas tinggi dengan cita rasa autentik khas Nusantara yang akan membuat masakan Anda semakin istimewa!</p>

      <div class="home-shopping-features">
        <div class="home-feature-item">
          <i class="fas fa-star"></i>
          <span>Kualitas Premium</span>
        </div>
        <div class="home-feature-item">
          <i class="fas fa-shipping-fast"></i>
          <span>Pengiriman Cepat</span>
        </div>
        <div class="home-feature-item">
          <i class="fas fa-leaf"></i>
          <span>Bahan Alami</span>
        </div>
        <div class="home-feature-item">
          <i class="fas fa-heart"></i>
          <span>Rasa Autentik</span>
        </div>
      </div>

      <a href="product.php" class="home-shopping-cta">
        <i class="fas fa-shopping-cart"></i>
        Mulai Belanja Sekarang
      </a>
    </div>
  `;
}

function toggleHomeCartDropdown() {
  console.log('toggleHomeCartDropdown called');
  const dropdown = document.getElementById('cartDropdownHome');
  if (dropdown) {
    console.log('Dropdown found, current classes:', dropdown.className);
    if (dropdown.classList.contains('show')) {
      dropdown.classList.remove('show');
      console.log('Dropdown hidden');
    } else {
      dropdown.classList.add('show');
      updateHomeCartDropdown();
      console.log('Dropdown shown');
    }
  } else {
    console.log('Dropdown not found!');
  }
}

// Scroll effect
window.addEventListener('scroll', function() {
  const navbar = document.getElementById('mainNavbar');
  if (window.scrollY > 10) {
    navbar.classList.add('scrolled');
  } else {
    navbar.classList.remove('scrolled');
  }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
  updateHomeCartCount();

  const cartBtn = document.getElementById('cartNavbarBtnHome');
  const cartDropdown = document.getElementById('cartDropdownHome');

  if (cartBtn && cartDropdown) {
    // Simple click to toggle
    cartBtn.onclick = function() {
      toggleHomeCartDropdown();
    };

    // Close when clicking outside
    document.onclick = function(e) {
      if (!cartDropdown.contains(e.target) && e.target !== cartBtn) {
        cartDropdown.classList.remove('show');
      }
    };

    // Don't close when clicking inside dropdown
    cartDropdown.onclick = function(e) {
      e.stopPropagation();
    };
  }
});

// Export functions to global scope
window.toggleHomeCartDropdown = toggleHomeCartDropdown;
window.updateHomeCartCount = updateHomeCartDropdown;

// Initialize immediately for onclick
window.toggleHomeCartDropdown = function() {
  console.log('Global toggleHomeCartDropdown called');
  const dropdown = document.getElementById('cartDropdownHome');
  if (dropdown) {
    console.log('Dropdown found, current classes:', dropdown.className);
    if (dropdown.classList.contains('show')) {
      dropdown.classList.remove('show');
      console.log('Dropdown hidden');
    } else {
      dropdown.classList.add('show');
      updateHomeCartDropdown();
      console.log('Dropdown shown');
    }
  } else {
    console.log('Dropdown not found!');
  }
};
</script>
