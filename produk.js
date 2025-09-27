// ... existing code ... <cartDropdown.innerHTML = ...>
  cartDropdown.innerHTML = `
    <div class="cart-title">Keranjang</div>
    <div class="cart-items">${itemsHtml}</div>
    <div class="subtotal-row">Subtotal: <span style="color:#af2d2d">${formatRupiah(subtotal)}</span></div>
    <div class="cart-dropdown-actions">
      <button class="cart-btn-main" id="cartView" title="Lihat semua keranjang belanja">View cart</button>
      <button class="cart-btn-main" id="cartCheckout" title="Checkout dan bayar!">Checkout</button>
    </div>
  `;
  feather.replace({ 'stroke-width': 2 });
  // Event untuk tombol remove
  cartDropdown.querySelectorAll('.cart-item-remove').forEach(btn => {
    btn.onclick = () => {
      removeFromCart(btn.getAttribute('data-cart-prod'));
    };
  });
  // View cart tombol redirect - efek bounce
  const viewBtn = cartDropdown.querySelector('#cartView');
  if(viewBtn) {
    viewBtn.onclick = () => {
      viewBtn.classList.add('btn-bounce');
      setTimeout(() => { window.location.href = 'keranjang.html'; }, 140);
    };
    viewBtn.onmouseover = () => viewBtn.classList.add('btn-glow');
    viewBtn.onmouseleave = () => viewBtn.classList.remove('btn-glow');
  }
  // Checkout tombol redirect - efek bounce
  const checkBtn = cartDropdown.querySelector('#cartCheckout');
  if(checkBtn) {
    checkBtn.onclick = () => {
      checkBtn.classList.add('btn-bounce');
      setTimeout(() => { window.location.href = 'pembayaran.html'; }, 150);
    };
    checkBtn.onmouseover = () => checkBtn.classList.add('btn-glow');
    checkBtn.onmouseleave = () => checkBtn.classList.remove('btn-glow');
  }
// ... existing code ... <rest of renderCart>
