<footer class="footer">
  <div class="footer-container">
    <div class="footer-col">
      <h3 data-i18n="footer_useful">Useful Links</h3>
      <ul>
        <li data-i18n="footer_about">About Us</li>
        <li data-i18n="footer_services">Our Services</li>
        <li data-i18n="footer_blogs">Blogs</li>
        <li data-i18n="footer_faq">FAQ</li>
      </ul>
    </div>
    <div class="footer-col">
      <h3 data-i18n="footer_web">NusaJava Eats Web</h3>
      <ul>
        <li data-i18n="footer_home">Home</li>
        <li data-i18n="footer_about_us">About Us</li>
        <li data-i18n="footer_menu">Menu</li>
        <li data-i18n="footer_contact">Contact</li>
      </ul>
    </div>
    <div class="footer-col">
      <h3 data-i18n="footer_contact_us">Contact Us</h3>
      <ul>
        <li>NusaJavaEats@gmail.com</li>
        <li>+085727196825</li>
        <li>Instagram:@NusaJava.Eats</li>
      </ul>
    </div>
    <div class="footer-col social">
      <h3 data-i18n="footer_follow">Follow Us</h3>
      <a href="#" aria-label="LinkedIn" class="social-icon">
        <i data-feather="linkedin"></i>
      </a>
      <a href="#" aria-label="YouTube" class="social-icon">
        <i data-feather="youtube"></i>
      </a>
      <a href="https://www.instagram.com/nusajavaeats/?utm_source=ig_web_button_share_sheet" aria-label="Instagram" class="social-icon">
        <i data-feather="instagram"></i>
      </a>
      <a href="#" aria-label="Github" class="social-icon">
        <i data-feather="github"></i>
      </a>
    </div>
  </div>
  <div class="footer-bottom">© 2025 NusaJava Eats.</div>
</footer>

<style>
/* Footer Style */
.footer {
  margin-top: 30px;
  background: var(--secondaryColor);
  color: var(--doubleColor);
  padding: 20px 0 0 0;
  border-top-left-radius: 35px;
  border-top-right-radius: 35px;
}

.footer-container {
  display: flex;
  max-width: 1200px;
  margin: auto;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 32px;
}

.footer-col {
  flex: 1 1 0;
  min-width: 225px;
  text-decoration: none;
}

.footer-col h3 {
  color: var(--mainColor);
  font-size: 20px;
  margin-bottom: 12px;
  font-weight: bold;
  border-bottom: 2px solid #41424a;
  width: max-content;
  padding-bottom: 5px;
}

.footer-col ul {
  list-style: disc inside;
  padding-left: 12px;
  margin: 0;
}

.footer-col ul li {
  color: #dfdfdf;
  font-size: 17px;
  line-height: 2.2;
  transition: color 0.2s;
  cursor: pointer;
}

.footer-col ul li:hover {
  color: var(--doubleColor);
}

.footer-col .social ul {
  list-style: none;
  padding: 0;
}

.social-icons a i{
  margin-top: 18px;
  display: flex;
  gap: 18px;
  text-decoration: none;
}

.social-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: var(--mainColor);
  width: 44px;
  height: 44px;
  overflow: hidden;
  transition: transform 0.2s;
  margin-right: 10px;
}

.social-icon  {
  width: 44px;
  height: 44px;
  color: inherit;
  text-decoration: none;
}

.social-icon:hover {
  transform: translateY(-4px) scale(1.08);
}

.footer-bottom {
  text-align: center;
  margin-top: 44px;
  padding: 16px 0 30px 0;
  color: #fff;
  font-weight: bold;
  font-size: 18px;
  letter-spacing: 0.3px;
}

@media (max-width: 900px) {
  .footer-container {
    flex-wrap: wrap;
    gap: 20px;
  }
  .footer-col {
    min-width: 170px;
  }
}

@media (max-width: 600px) {
  .footer-container {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  .footer-col {
    min-width: 0;
    width: 100%;
  }
  .footer-bottom {
    font-size: 15px;
    margin-top: 24px;
  }
}
</style>
