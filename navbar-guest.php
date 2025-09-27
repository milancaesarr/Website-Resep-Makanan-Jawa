<nav class="navbar">
  <div class="navbar-box">
    <img class="logo" src="Assets/Logo/3.png" alt="Logo">
    <a href="index.php" class="navbar-logo" data-i18n="brand_name">NusaJava Eats</a>
    <div class="nav-item">
      <a href="index.php" data-i18n="nav_home">Home</a>
      <a href="#" onclick="showLoginModal(); return false;" data-i18n="nav_resep">Resep</a>
      <a href="#" onclick="showLoginModal(); return false;" data-i18n="nav_produk">Produk</a>
      <a href="#" onclick="showLoginModal(); return false;" data-i18n="nav_komunitas">Komunitas</a>
      <a href="#" onclick="showLoginModal(); return false;" data-i18n="nav_contact">Contact</a>
    </div>
    <div class="navbar-extra">
      <!-- Language Selector -->
      <div class="language-selector">
        <button id="languageToggle" class="language-btn" onclick="toggleLanguage()" aria-label="Change Language">
          <i data-feather="globe"></i>
          <span id="currentLang">ID</span>
        </button>
      </div>

      <!-- Login Button -->
      <button class="login-btn" onclick="showLoginModal()" data-i18n="nav_login">
        <i data-feather="log-in"></i>
        Masuk
      </button>

      <!-- Mobile Menu Button -->
      <a href="#" id="hamburger-menu">
        <i data-feather="menu"></i>
      </a>
    </div>
  </div>
</nav>

<style>
/* Navbar Styles for Guest */
.navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  background: rgba(254, 186, 113, 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 2px solid rgba(91, 38, 32, 0.1);
  z-index: 1000;
  padding: 0;
  transition: all 0.3s ease;
}

.navbar-box {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 20px;
}

.logo {
  height: 45px;
  width: auto;
  margin-right: 12px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.navbar-logo {
  font-size: 1.8rem;
  font-weight: bold;
  color: #5b2620;
  text-decoration: none;
  display: flex;
  align-items: center;
  transition: all 0.3s ease;
}

.navbar-logo:hover {
  color: #af2d2d;
  transform: scale(1.05);
}

.nav-item {
  display: flex;
  gap: 30px;
  align-items: center;
}

.nav-item a {
  color: #5b2620;
  text-decoration: none;
  font-weight: 600;
  font-size: 1rem;
  padding: 8px 16px;
  border-radius: 20px;
  transition: all 0.3s ease;
  position: relative;
}

.nav-item a:hover {
  background: rgba(175, 45, 45, 0.1);
  color: #af2d2d;
  transform: translateY(-2px);
}

.navbar-extra {
  display: flex;
  align-items: center;
  gap: 15px;
}

/* Language Selector */
.language-selector {
  position: relative;
}

.language-btn {
  background: rgba(255, 255, 255, 0.2);
  border: 2px solid rgba(91, 38, 32, 0.2);
  color: #5b2620;
  padding: 8px 12px;
  border-radius: 20px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.3s ease;
}

.language-btn:hover {
  background: rgba(255, 255, 255, 0.4);
  border-color: #af2d2d;
  transform: translateY(-2px);
}

/* Login Button */
.login-btn {
  background: linear-gradient(45deg, #af2d2d, #5b2620);
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 25px;
  cursor: pointer;
  font-weight: 600;
  font-size: 1rem;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
  box-shadow: 0 3px 10px rgba(175, 45, 45, 0.3);
}

.login-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(175, 45, 45, 0.4);
  background: linear-gradient(45deg, #d43447, #af2d2d);
}

/* Mobile Menu Button */
#hamburger-menu {
  display: none;
  color: #5b2620;
  font-size: 1.5rem;
  cursor: pointer;
}

/* Responsive Design */
@media (max-width: 768px) {
  .nav-item {
    position: fixed;
    top: 100%;
    left: 0;
    width: 100%;
    background: rgba(254, 186, 113, 0.98);
    backdrop-filter: blur(15px);
    flex-direction: column;
    gap: 0;
    padding: 20px 0;
    transform: translateY(-100%);
    transition: all 0.3s ease;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
  }

  .nav-item.active {
    transform: translateY(0);
  }

  .nav-item a {
    width: 90%;
    margin: 0 auto;
    padding: 15px 20px;
    text-align: center;
    border-bottom: 1px solid rgba(91, 38, 32, 0.1);
  }

  .nav-item a:last-child {
    border-bottom: none;
  }

  #hamburger-menu {
    display: block;
  }

  .navbar-extra {
    gap: 10px;
  }

  .login-btn {
    padding: 8px 15px;
    font-size: 0.9rem;
  }

  .language-btn {
    padding: 6px 10px;
    font-size: 0.8rem;
  }
}

@media (max-width: 480px) {
  .navbar-box {
    padding: 10px 15px;
  }

  .navbar-logo {
    font-size: 1.5rem;
  }

  .logo {
    height: 35px;
  }

  .login-btn span {
    display: none;
  }

  .language-btn span {
    display: none;
  }
}
</style>

<script>
// Mobile menu toggle for guest navbar
document.addEventListener('DOMContentLoaded', function() {
  const hamburgerMenu = document.querySelector('#hamburger-menu');
  const navItem = document.querySelector('.nav-item');

  if (hamburgerMenu && navItem) {
    hamburgerMenu.addEventListener('click', function(e) {
      e.preventDefault();
      navItem.classList.toggle('active');
    });

    // Close menu when clicking on a nav link
    const navLinks = document.querySelectorAll('.nav-item a');
    navLinks.forEach(link => {
      link.addEventListener('click', function() {
        navItem.classList.remove('active');
      });
    });

    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
      if (!hamburgerMenu.contains(e.target) && !navItem.contains(e.target)) {
        navItem.classList.remove('active');
      }
    });
  }

  // Language toggle function (placeholder)
  window.toggleLanguage = function() {
    const currentLang = document.getElementById('currentLang');
    if (currentLang) {
      currentLang.textContent = currentLang.textContent === 'ID' ? 'EN' : 'ID';
    }
  };

  // Initialize feather icons
  if (typeof feather !== 'undefined') {
    feather.replace();
  }
});

// Make showLoginModal available globally
window.showLoginModal = window.showLoginModal || function() {
  console.log('Login modal function not yet loaded');
};
</script>
