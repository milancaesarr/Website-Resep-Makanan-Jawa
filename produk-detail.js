// Data produk digunakan ulang (dummy, sama dengan produk grid)
const produkList = [
  {
    id: 'p1',
    nama: 'Royco Kaldu Jamur',
    gambar: 'Assets/img/produk/Royco Rasa Jamur.png',
    harga: 8000,
    bullets: [
      'Royco Kaldu Jamur adalah kaldu jamur dengan rasa gurih sedap.',
      'Cocok untuk berbagai macam masakan rumahan.',
      'Tanpa MSG tambahan dan mudah digunakan.',
      'Tersedia dalam kemasan sachet dan botol.'
    ],
    desc: 'Royco Kaldu Jamur merupakan solusi praktis penambah cita rasa masakan sehari-hari keluarga Indonesia. Diperkaya dengan rasa jamur alami yang lezat, Royco hadir untuk membuat masakan Anda semakin istimewa.'
  },
  {
    id: 'p2',
    nama: 'Saori Lada Hitam',
    gambar: 'Assets/img/produk/Saori Lada Hitam.png',
    harga: 5000,
    bullets: [
      'Saori Lada Hitam memberikan rasa lada hitam yang pedas nikmat.',
      'Cocok untuk bumbu tumisan, steak, atau daging sapi.',
      'Praktis, tinggal tuang tanpa perlu tambahan bumbu.',
    ],
    desc: 'Hadirkan kelezatan ala restoran di rumah dengan Saori Lada Hitam. Mudah digunakan dan membuat masakan favorit keluarga semakin lezat dan menggugah selera.'
  },
  {
    id: 'p3',
    nama: 'Bango Kecap Manis Hitam Gurih',
    gambar: 'Assets/img/produk/Kecap Bango.png',
    harga: 8000,
    bullets: [
      'Bango Kecap Manis Hitam Gurih adalah kecap manis yang dibuat dari kedelai hitam Mallika berkualitas.',
      'Kecap manis dengan perpaduan cita rasa manis dan gurih dengan warna hitam yang medok.',
      'Cocok digunakan untuk masakan dengan cita rasa manis gurih dan tampilan yang lebih hitam.',
      'Bango Kecap Manis Hitam Gurih membuat masakan jadi lebih medok hitamnya dan gurih rasanya.',
      'Tersedia dalam kemasan 40 ml dan 550 ml.'
    ],
    desc: 'Memperkenalkan Bango Kecap Manis Hitam Gurih, kecap manis dengan perpaduan cita rasa manis dan gurih dengan warna hitam yang pekat. Tambahkan Bango Kecap Manis Hitam Gurih ke dalam masakan favorit keluarga. Hidangan legendaris seperti semur, satay, tumis daging, hingga racikan sambal kesukaan jadi lebih mantap berkat sentuhan kecap manis yang kaya rasa ini. Ciptakan kelezatan rasa legendaris untuk keluarga di rumah bersama Bango Kecap Manis Hitam Gurih.'
  },
  {
    id: 'p4',
    nama: 'Royco Kaldu Ayam',
    gambar: 'Assets/img/produk/Royco Kaldu Ayam.png',
    harga: 1000,
    bullets: [
      'Kaldu ayam bubuk serbaguna.',
      'Menambah cita rasa gurih pada masakan.',
      'Mengandung ekstrak daging ayam pilihan.',
      'Cocok untuk berbagai jenis masakan.',
      'Kemasan praktis dan ekonomis.'
    ],
    desc: 'Royco Kaldu Ayam adalah bumbu kaldu serbaguna yang memberikan rasa gurih alami dari ayam. Sangat cocok digunakan untuk sup, tumisan, maupun aneka hidangan sehari-hari.'
  },
  {
    id: 'p5',
    nama: 'Sasa Ayam Spesial',
    gambar: 'Assets/img/produk/Sasa Ayam Spesial.png',
    harga: 6000,
    bullets: [
      'Bumbu praktis untuk ayam goreng spesial.',
      'Diformulasikan dengan rempah-rempah pilihan.',
      'Cocok untuk ayam goreng krispi dan gurih.',
      'Memberikan cita rasa khas nusantara.',
      'Mudah digunakan tanpa tambahan bumbu lain.'
    ],
    desc: 'Sasa Ayam Spesial adalah bumbu instan siap pakai untuk mengolah ayam goreng yang lezat dan kaya rasa. Dengan racikan rempah-rempah khas Indonesia, bumbu ini cocok untuk kamu yang ingin hasil masakan ayam yang gurih dan renyah tanpa repot.'
  },
  {
    id: 'p6',
    nama: 'Bamboe Rawon',
    gambar: 'Assets/img/produk/Bamboe Rawon.png',
    harga: 7000,
    bullets: [
      'Bumbu siap pakai untuk memasak rawon khas Jawa Timur.',
      'Terbuat dari bahan-bahan alami pilihan seperti keluak, bawang, dan rempah-rempah.',
      'Mudah digunakan tanpa perlu menambahkan banyak bumbu lain.',
      'Cocok untuk 4-6 porsi masakan.',
      'Dikemas higienis dan praktis untuk keperluan memasak di rumah.'
    ],
    desc: 'Bamboe Rawon adalah bumbu instan yang diracik khusus untuk menghadirkan rasa autentik rawon khas Jawa Timur. Mengandung keluak dan rempah pilihan yang memberikan cita rasa hitam khas rawon. Cocok untuk Anda yang ingin menyajikan hidangan tradisional Indonesia secara praktis dan cepat di rumah.'
  },
  {
    id: 'p7',
    nama: 'Bamboe Soto Betawi',
    gambar: 'Assets/img/produk/Bamboe Soto Betawi.png',
    harga: 7000,
    bullets: [
      'Bumbu instan praktis untuk membuat Soto Betawi khas Jakarta.',
      'Mengandung rempah-rempah alami seperti serai, lengkuas, dan santan bubuk.',
      'Memberikan cita rasa gurih dan creamy khas soto Betawi otentik.',
      'Cocok untuk disajikan dengan daging sapi, tomat, kentang, dan emping.',
      'Ideal untuk 4-6 porsi hidangan.'
    ],
    desc: 'Bamboe Soto Betawi adalah bumbu masak siap pakai yang diracik khusus untuk menghadirkan kelezatan khas soto Betawi. Perpaduan santan dan rempah-rempah pilihan memberikan cita rasa gurih dan kaya. Sangat cocok untuk Anda yang ingin menikmati hidangan soto Betawi yang otentik dan lezat di rumah tanpa repot.'
  },
  {
    id: 'p8',
    nama: 'Bamboe Empal',
    gambar: 'Assets/img/produk/Bamboe Empal.png',
    harga: 7000,
    bullets: [
      'Bumbu praktis untuk masakan Empal khas Indonesia.',
      'Terbuat dari rempah-rempah pilihan dan tanpa pengawet.',
      'Cocok untuk memasak daging empuk dan gurih.',
      'Mudah digunakan, hanya perlu ditumis dan dicampur dengan daging.',
      'Ideal untuk hidangan sehari-hari maupun acara khusus.'
    ],
    desc: 'Bamboe Empal adalah bumbu instan siap pakai untuk memasak empal daging khas Indonesia. Dengan campuran rempah pilihan, bumbu ini membantu menghadirkan rasa gurih dan kaya rempah pada empal buatan Anda. Sangat praktis untuk Anda yang ingin menyajikan masakan rumahan khas Jawa tanpa repot.'
  },
  {
    id: 'p9',
    nama: 'Bamboe Gule',
    gambar: 'Assets/img/produk/Bamboe Gule.png',
    harga: 7000,
    bullets: [
      'Bumbu instan khas masakan gule Indonesia.',
      'Dibuat dari rempah-rempah asli.',
      'Cocok untuk gule kambing, ayam, atau sapi.',
      'Tanpa MSG dan pengawet buatan.',
      'Mudah digunakan untuk masakan sehari-hari.'
    ],
    desc: 'Bumbu Bamboe Gule membantu Anda menyajikan masakan gule khas nusantara yang gurih dan kaya rempah hanya dalam beberapa langkah sederhana.'
  },
  {
    id: 'p10',
    nama: 'Bamboe Nasi Goreng Jawa',
    gambar: 'Assets/img/produk/Bamboe Nasi Goreng Jawa.png',
    harga: 7000,
    bullets: [
      'Bumbu siap pakai untuk nasi goreng Jawa.',
      'Cita rasa manis pedas khas Jawa.',
      'Praktis untuk hidangan cepat saji.',
      'Tanpa pengawet dan pewarna buatan.',
      'Cocok untuk 2-3 porsi.'
    ],
    desc: 'Nikmati nasi goreng khas Jawa dengan cita rasa autentik menggunakan Bamboe Nasi Goreng Jawa. Praktis dan cepat disajikan untuk keluarga Anda.'
  },
  {
    id: 'p11',
    nama: 'Bamboe Sayur Asem',
    gambar: 'Assets/img/produk/Bamboe Sayur Asem.png',
    harga: 7000,
    bullets: [
      'Bumbu siap pakai untuk sayur asem.',
      'Rasa asam segar khas Indonesia.',
      'Cocok untuk sayur asem Jawa maupun Betawi.',
      'Terbuat dari rempah dan bahan alami.',
      'Tanpa pengawet dan pewarna.'
    ],
    desc: 'Bamboe Sayur Asem memudahkan Anda memasak sayur asem dengan rasa segar dan nikmat. Tambahkan sayuran dan air, sajikan dalam hitungan menit.'
  },
  {
    id: 'p12',
    nama: 'Bamboe Tongseng',
    gambar: 'Assets/img/produk/Bamboe Tongseng.png',
    harga: 7000,
    bullets: [
      'Bumbu instan untuk tongseng kambing atau sapi.',
      'Cita rasa manis, gurih, dan berempah.',
      'Mudah dan praktis untuk dimasak di rumah.',
      'Tanpa MSG dan bahan pengawet.',
      'Cocok untuk 3-4 porsi.'
    ],
    desc: 'Dengan Bamboe Tongseng, nikmati kelezatan tongseng khas Solo yang kaya rasa dan aroma hanya dalam beberapa langkah mudah.'
  },
  {
    id: 'p13',
    nama: 'Bango Rawon',
    gambar: 'Assets/img/produk/Bango Rawon.png',
    harga: 9000,
    bullets: [
      'Bumbu instan untuk rawon khas Jawa Timur.',
      'Dibuat dari kluwek pilihan.',
      'Cocok untuk sajian daging berkuah hitam.',
      'Cita rasa khas rawon yang autentik.',
      'Kemasan praktis dan mudah digunakan.'
    ],
    desc: 'Nikmati kelezatan rawon khas Jawa Timur dengan Bango Bumbu Rawon. Kaya akan kluwek dan rempah-rempah yang menghadirkan kuah hitam pekat yang gurih dan harum.'
  },
  {
    id: 'p14',
    nama: 'Bango Soto Betawi',
    gambar: 'Assets/img/produk/Bango Soto Betawi.png',
    harga: 9000,
    bullets: [
      'Bumbu instan untuk soto Betawi.',
      'Rasa gurih dan creamy dari santan.',
      'Cocok untuk hidangan soto daging khas Jakarta.',
      'Dibuat dari bahan alami dan rempah asli.',
      'Kemasan praktis dan hemat waktu.'
    ],
    desc: 'Bango Bumbu Soto Betawi memberikan cita rasa autentik soto Betawi yang gurih, creamy, dan kaya rempah. Cocok disajikan untuk makan siang atau malam.'
  },
  {
    id: 'p15',
    nama: 'Indofood Rawon',
    gambar: 'Assets/img/produk/Indofood Rawon.png',
    harga: 10000,
    bullets: [
      'Bumbu masakan rawon khas Jawa Timur.',
      'Terbuat dari kluwek dan rempah pilihan.',
      'Memberi warna hitam khas rawon.',
      'Mudah digunakan untuk memasak.',
      'Cocok untuk 4-5 porsi.'
    ],
    desc: 'Indofood Bumbu Rawon memudahkan Anda menyajikan rawon dengan rasa otentik. Daging empuk dengan kuah hitam gurih kini dapat dibuat lebih praktis.'
  },
  {
    id: 'p16',
    nama: 'Indofood Rendang',
    gambar: 'Assets/img/produk/Indofood Rendang.png',
    harga: 10000,
    bullets: [
      'Bumbu rendang khas Minang.',
      'Rasa gurih dan pedas khas rempah rendang.',
      'Cocok untuk daging sapi, ayam, atau jengkol.',
      'Tanpa pengawet dan pewarna buatan.',
      'Hidangan jadi lezat tanpa repot.'
    ],
    desc: 'Nikmati rendang khas Padang yang kaya rempah dengan Indofood Bumbu Rendang. Praktis untuk sajian spesial keluarga tanpa harus membuat dari nol.'
  },
  {
    id: 'p17',
    nama: 'Indofood Sambel Balado',
    gambar: 'Assets/img/produk/Indofood Sambel Balado.png',
    harga: 10000,
    bullets: [
      'Sambal instan rasa balado pedas.',
      'Cocok untuk lauk goreng, telur, atau terong.',
      'Rasa pedas manis khas Padang.',
      'Siap saji dan praktis digunakan.',
      'Tanpa pengawet buatan.'
    ],
    desc: 'Indofood Sambel Balado memberikan sensasi pedas manis khas Minang yang pas untuk menemani berbagai jenis lauk. Cukup panaskan dan sajikan.'
  },
  {
    id: 'p18',
    nama: 'Sasa Santan Kelapa',
    gambar: 'Assets/img/produk/Sasa Santan Kelapa.png',
    harga: 6000,
    bullets: [
      'Santan instan dari kelapa asli.',
      'Tekstur kental dan rasa gurih alami.',
      'Tanpa pengawet dan pewarna.',
      'Cocok untuk masakan gurih atau manis.',
      'Kemasan praktis siap pakai.'
    ],
    desc: 'Sasa Santan Kelapa adalah santan instan dari kelapa segar, ideal untuk berbagai masakan seperti rendang, gulai, kue, dan es santan.'
  },
  {
    id: 'p19',
    nama: 'Sasa Sambal Terasi Sunda',
    gambar: 'Assets/img/produk/Sasa Sambal Terasi Sunda.png',
    harga: 15000,
    bullets: [
      'Sambal khas Sunda dengan terasi.',
      'Pedas gurih dan beraroma kuat.',
      'Cocok untuk lalapan, nasi, dan gorengan.',
      'Terbuat dari cabai segar dan terasi pilihan.',
      'Siap saji dalam kemasan praktis.'
    ],
    desc: 'Sasa Sambal Terasi Sunda adalah sambal siap saji dengan cita rasa khas Sunda. Kombinasi cabai dan terasi menghadirkan sensasi pedas dan gurih yang menggugah selera.'
  },
  {
    id: 'p20',
    nama: 'Sasa Nasi Goreng Jawa',
    gambar: 'Assets/img/produk/Sasa Nasi Goreng Jawa.png',
    harga: 6000,
    bullets: [
      'Bumbu siap pakai nasi goreng Jawa.',
      'Rasa manis dan gurih khas Jawa Tengah.',
      'Praktis dan cepat disajikan.',
      'Tanpa bahan pengawet.',
      'Cocok untuk 2-3 porsi.'
    ],
    desc: 'Masak nasi goreng khas Jawa jadi lebih mudah dan cepat dengan Sasa Nasi Goreng Jawa. Cita rasa tradisionalnya cocok untuk seluruh keluarga.'
  },
  // Tulis produk sejenis lain pakai data simple/desc pendek
];

const formatRupiah = (n) => `Rp ${n.toLocaleString('id-ID')}`;

// Ambil ID produk dari URL query
function getProductId() {
  const url = new URL(window.location.href);
  return url.searchParams.get('id') || '';
}
const prodId = getProductId();
const produk = produkList.find(p => p.id === prodId);

const prodContainer = document.getElementById('produkDetailContainer');
if (!produk) {
  prodContainer.innerHTML = '<div style="margin:2em;font-size:1.2em;">Produk tidak ditemukan.</div>';
} else {
  prodContainer.innerHTML = `
    <img class="produk-detail-img" src="${produk.gambar}" alt="${produk.nama}">
    <div class="produk-detail-info">
      <div class="produk-detail-title">${produk.nama}</div>
      <ul class="produk-detail-bullets">
        ${produk.bullets.map(b=>`<li>${b}</li>`).join('')}
      </ul>
      <p>${produk.desc}</p>
      <div class="produk-detail-actions">
        <button class="produk-detail-back"><i data-feather="arrow-left"></i> Kembali</button>
        <div class="produk-detail-qty">
          <button class="produk-detail-qty-btn" title="Kurang" id="qtyMinus"><i data-feather="minus"></i></button>
          <span class="produk-detail-qty-val" id="qtyVal">1</span>
          <button class="produk-detail-qty-btn" title="Tambah" id="qtyPlus"><i data-feather="plus"></i></button>
        </div>
        <button class="produk-detail-cart-btn" id="addToCartBtn"><i data-feather="shopping-cart"></i> Tambah ke Keranjang</button>
      </div>
      <div style="margin-top:10px;font-size:1.13em;color:#af2d2d;font-weight:600;">${formatRupiah(produk.harga)} / Pcs</div>
    </div>
  `;
  feather.replace({ 'stroke-width': 2 });

  // Logic tombol qty dan cart
  let qty = 1;
  const qtyVal = document.getElementById('qtyVal');
  document.getElementById('qtyMinus').onclick = function() {
    if (qty > 1) { qty--; qtyVal.textContent = qty; }
  };
  document.getElementById('qtyPlus').onclick = function() {
    qty++; qtyVal.textContent = qty;
  };

  document.getElementById('addToCartBtn').onclick = function() {
    // Cart logic sama seperti di grid produk
    addToCart(prodId, qty);
   ;
  };
  document.querySelector('.produk-detail-back').onclick = function() {
    window.location.href = 'produk.html';
  };
}

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
