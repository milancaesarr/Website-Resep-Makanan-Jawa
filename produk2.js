// Produk dummy (semua harga 1000)
const produkList = [
  {
    id: 'p1',
    nama: 'Royco Kaldu Jamur',
    gambar: 'Assets/img/produk/Royco Rasa Jamur.png',
    harga: 8000,
  },
  {
    id: 'p2',
    nama: 'Saori Lada Hitam',
    gambar: 'Assets/img/produk/Saori Lada Hitam.png',
    harga: 5000,
  },
  {
    id: 'p3',
    nama: 'Bango Kecap Manis',
    gambar: 'Assets/img/produk/Kecap Bango.png',
    harga: 8000,
  },
  {
    id: 'p4',
    nama: 'Royco Kaldu Ayam',
    gambar: 'Assets/img/produk/Royco Rasa Ayam.png',
    harga: 8000,
  },
  {
    id: 'p5',
    nama: 'Sasa Ayam Spesial',
    gambar: 'Assets/img/produk/Sasa Ayam Spesial.png',
    harga: 6000,
  },
  {
    id: 'p6',
    nama: 'Bamboe Rawon',
    gambar: 'Assets/img/produk/Bamboe Rawon.png',
    harga: 7000,
  },
  {
    id: 'p7',
    nama: 'Bamboe Soto Betawi',
    gambar: 'Assets/img/produk/Bamboe Soto Betawi.png',
    harga: 7000,
  },
  {
    id: 'p8',
    nama: 'Bamboe Empal',
    gambar: 'Assets/img/produk/Bamboe Empal.png',
    harga: 7000,
  },
  {
    id: 'p9',
    nama: 'Bamboe Gule',
    gambar: 'Assets/img/produk/Bamboe Gule.png',
    harga: 7000,
  },
  {
    id: 'p10',
    nama: 'Bamboe Nasi Goreng Jawa',
    gambar: 'Assets/img/produk/Bamboe Nasi Goreng Jawa.png',
    harga: 7000,
  },
  {
    id: 'p11',
    nama: 'Bamboe Sayur Asem',
    gambar: 'Assets/img/produk/Bamboe Sayur Asem.png',
    harga: 7000,
  },
  {
    id: 'p12',
    nama: 'Bamboe Tongseng',
    gambar: 'Assets/img/produk/Bamboe Tongseng.png',
    harga: 7000,
  },
  {
    id: 'p13',
    nama: 'Bango Rawon',
    gambar: 'Assets/img/produk/Bango Rawon.png',
    harga: 9000,
  },
  {
    id: 'p14',
    nama: 'Bango Soto Betawi',
    gambar: 'Assets/img/produk/Bango Soto Betawi.png',
    harga: 9000,
  },
  {
    id: 'p15',
    nama: 'Indofood Rawon',
    gambar: 'Assets/img/produk/Indofood Rawon.png',
    harga: 10000,
  },
  {
    id: 'p16',
    nama: 'Indofood Rendang',
    gambar: 'Assets/img/produk/Indofood Rendang.png',
    harga: 10000,
  },
  {
    id: 'p17',
    nama: 'Indofood Sambel Balado',
    gambar: 'Assets/img/produk/Indofood Sambel Balado.png',
    harga: 10000,
  },
  {
    id: 'p18',
    nama: 'Sasa Santan Kelapa',
    gambar: 'Assets/img/produk/Sasa Santan Kelapa.png',
    harga: 6000,
  },
  {
    id: 'p19',
    nama: 'Sasa Sambal Terasi Sunda',
    gambar: 'Assets/img/produk/Sasa Sambal Terasi Sunda.png',
    harga: 15000,
  },
  {
    id: 'p20',
    nama: 'Sasa Nasi Goreng Jawa',
    gambar: 'Assets/img/produk/Sasa Nasi Goreng Jawa.png',
    harga: 6000,
  },
];

const formatRupiah = (n) => `Rp ${n.toLocaleString('id-ID')}`;

const produkGrid = document.getElementById('produkGrid');

// --- Render Produk Grid TANPA qty-control & Add to cart, GANTI Lihat Produk ---
produkList.forEach((produk) => {
  const card = document.createElement('div');
  let merk = '';
  const namaLower = produk.nama.toLowerCase();
  if (namaLower.includes('royco')) merk = 'royco';
  else if (namaLower.includes('bamboe')) merk = 'bamboe';
  else if (namaLower.includes('indofood')) merk = 'indofood';
  else if (namaLower.includes('bango')) merk = 'bango';
  else if (namaLower.includes('saori')) merk = 'saori';
  else if (namaLower.includes('sasa')) merk = 'sasa';
  card.className = `produk-card ${merk}`;
  card.innerHTML = `
    <img src="${produk.gambar}" alt="${produk.nama}" />
    <h2>${produk.nama}</h2>
    <div class="produk-price">${formatRupiah(produk.harga)}</div>
    <div style="margin-top:12px; width:100%; display:flex; justify-content:center;">
      <a class="lihat-produk-btn" href="produk-detail.html?id=${produk.id}">Lihat Produk</a>
    </div>
  `;
  produkGrid.appendChild(card);
});

// === FIX FILTER BUTTON LOGIC ===
const filterContainer = document.querySelector('.filter-button');
if (filterContainer) {
  const btns = filterContainer.querySelectorAll('button');
  btns.forEach((btn) => {
    btn.addEventListener('click', () => {
      btns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const filter = btn.getAttribute('data-filter');
      document.querySelectorAll('.produk-card').forEach(card => {
        if (filter === 'all') card.style.display = '';
        else if (card.classList.contains(filter.slice(1))) card.style.display = '';
        else card.style.display = 'none';
      });
    });
  });
  // Set 'All' active default
  const allBtn = filterContainer.querySelector('[data-filter="all"]');
  if (allBtn) allBtn.classList.add('active');
}

// ---- Shopping Cart Logic ----
const CART_KEY = 'keranjangBelanja';
function getCart() {
  try { return JSON.parse(localStorage.getItem(CART_KEY)) || []; }
  catch { return []; }
}
function setCart(state) {
  localStorage.setItem(CART_KEY, JSON.stringify(state));
}
function addToCart(prodId, qtyAdd) {
  let cart = getCart();
  const idx = cart.findIndex((item) => item.id === prodId);
  if (idx !== -1) {
    cart[idx].qty += qtyAdd;
  } else {
    cart.push({ id: prodId, qty: qtyAdd });
  }
  setCart(cart);
  renderCart();
}
function removeFromCart(prodId) {
  let cart = getCart();
  cart = cart.filter((item) => item.id !== prodId);
  setCart(cart);
  renderCart();
}
// --- Render Cart Dropdown ---
const cartNavbarBtn = document.getElementById('cartNavbarBtn');
const cartDropdown = document.getElementById('cartDropdown');
const cartBadge = document.getElementById('cartBadge');

function renderCart() {
  const cart = getCart();
  let count = cart.reduce((a,b)=>a+b.qty,0);
  cartBadge.textContent = count;

  if (!cart.length) {
    cartDropdown.innerHTML = `<div class="cart-title">Keranjang Kosong</div>`;
    cartDropdown.classList.remove('open');
    return;
  }
  let subtotal = 0;
  let itemsHtml = cart.map((item) => {
    const prod = produkList.find((p) => p.id === item.id);
    const total = item.qty * prod.harga;
    subtotal += total;
    return `<div class="cart-item">
        <img src="${prod.gambar}" class="cart-item-img" alt="${prod.nama}" />
        <div class="cart-item-info">
          <div class="cart-item-name">${prod.nama}</div>
          <div class="cart-item-detail">${item.qty} × ${formatRupiah(prod.harga)}</div>
        </div>
        <button class="cart-item-remove" title="Hapus" data-cart-prod="${prod.id}"><i data-feather="x"></i></button>
      </div>`;
  }).join('');
  cartDropdown.innerHTML = `
    <div class="cart-title">Keranjang</div>
    <div class="cart-items">${itemsHtml}</div>
    <div class="subtotal-row">Subtotal: <span style="color:#af2d2d">${formatRupiah(subtotal)}</span></div>
    <div class="cart-dropdown-actions">
      <button class="cart-btn-main" id="cartView">View cart</button>
    </div>
  `;
  feather.replace({ 'stroke-width': 2 });
  // Event untuk tombol remove
  cartDropdown.querySelectorAll('.cart-item-remove').forEach(btn => {
    btn.onclick = () => {
      removeFromCart(btn.getAttribute('data-cart-prod'));
    };
  });
  // Dummy btn
  cartDropdown.querySelector('#cartView').onclick = () => {
    window.location.href = 'keranjang.html';
};
}
renderCart();

// --- Dropdown toggle ---
let cartDropOpen = false;
cartNavbarBtn.onclick = (e) => {
  e.stopPropagation();
  cartDropOpen = !cartDropOpen;
  if(cartDropOpen) {
    cartDropdown.classList.add('open');
  } else {
    cartDropdown.classList.remove('open');
  }
};
document.body.addEventListener('click', ()=>{
  cartDropdown.classList.remove('open');
  cartDropOpen = false;
});
cartDropdown.onclick = (e) => e.stopPropagation();

// Navbar background scroll effect
const navbar = document.getElementById("mainNavbar");
window.addEventListener("scroll", () => {
  if (window.scrollY > 20) {
    navbar.classList.add("scrolled");
  } else {
    navbar.classList.remove("scrolled");
  }
});
