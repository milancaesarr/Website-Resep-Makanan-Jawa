const recipes = [
  {
    image: 'Assets/Img/makanan/Rawon.png',
    title: 'Rawon',
    categories: [],
    time: '1 hr',
    Location: 'Jawa Timur',
    rating: '4.9',
  },
  {
    image: 'Assets/img/makanan/Nasi Liwet.png',
    title: 'Nasi Liwet',
    categories: [],
    time: '1 hr',
    Location: 'Jawa Tengah',
    rating: '4.4',
  },
  {
    image: 'Assets/img/makanan/Gudeg Jogja.png',
    title: 'Gudeg',
    categories: [],
    time: '50 mins',
    Location: 'Yogyakarta',
    rating: '4.8',
  },
  {
    image: 'Assets/img/makanan/Seblak.png',
    title: 'Seblak',
    categories: [],
    time: '45 mins',
    Location: 'Jawa Barat',
    rating: '4.7',
  },
  {
    image: 'Assets/img/makanan/Soto Betawi.png',
    title: 'Soto Betawi',
    categories: [],
    time: '45 mins',
    Location: 'Jakarta',
    rating: '4.7',
  },
  {
    image: 'Assets/img/makanan/Sate Bandeng.png',
    title: 'Sate Bandeng',
    categories: [],
    time: '1.5 hr',
    Location: 'Banten',
    rating: '4.5',
  },
];

const iconMap = {

};

function renderRecipes() {
  const slider = document.getElementById('slider');
  slider.innerHTML = '';
  let imagesLoaded = 0;

  recipes.forEach((recipe) => {
    const card = document.createElement('div');
    card.className = 'slide-card';
    card.innerHTML = `
      <img src="${recipe.image}" alt="${recipe.title}">
      <div class="category-icons">
        ${recipe.categories.map(cat => `<div class="icon">${iconMap[cat] || '<i class="fa-solid fa-utensils"></i>'}</div>`).join('')}
      </div>
      <div class="slide-title">${recipe.title}</div>
      <div class="slide-info">
        <span><i class="fa-solid fa-clock"></i> ${recipe.time}</span>
        <span><i class="fa-solid fa-location-dot"></i> ${recipe.Location}</span>
        <span><i class="fa-solid fa-star"></i> ${recipe.rating}</span>
      </div>
    `;

    const img = card.querySelector('img');
    img.onload = () => {
      imagesLoaded++;
      if (imagesLoaded === recipes.length) {
        updateSlider();
      }
    };

    slider.appendChild(card);
  });
}

let slider, btnPrev, btnNext;
let currentIndex = 0;
let slideToShow = 3;

function getSlideToShow() {
  return window.innerWidth <= 800 ? 1 : 3;
}

function updateSlider() {
  slideToShow = getSlideToShow();
  if (!slider || slider.children.length === 0) return;
  const slideWidth = slider.children[0].offsetWidth;
  slider.style.transform = `translateX(-${currentIndex * (slideWidth + 32)}px)`;
  btnPrev.disabled = currentIndex === 0;
  btnNext.disabled = currentIndex >= recipes.length - slideToShow;
}

document.addEventListener("DOMContentLoaded", () => {
  const sliderEl = document.getElementById('slider');
  const btnPrevEl = document.getElementById('btn-prev');
  const btnNextEl = document.getElementById('btn-next');

  slider = sliderEl;
  btnPrev = btnPrevEl;
  btnNext = btnNextEl;

  renderRecipes();

  // Delay sedikit untuk pastikan gambar sudah dimuat
  setTimeout(updateSlider, 100);

  btnPrev.onclick = function () {
    if (currentIndex > 0) {
      currentIndex--;
      updateSlider();
    }
  };

  btnNext.onclick = function () {
    if (currentIndex < recipes.length - slideToShow) {
      currentIndex++;
      updateSlider();
    }
  };

  window.addEventListener('resize', updateSlider);
});

