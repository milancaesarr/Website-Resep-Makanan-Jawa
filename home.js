const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    let currentSlide = 0;

    function showSlide(index) {
      document.querySelector('.slides').style.transform = `translateX(-${index * 100}%)`;
      dots.forEach(dot => dot.classList.remove('active'));
      dots[index].classList.add('active');
    }

    document.querySelector('.prev').addEventListener('click', () => {
      currentSlide = (currentSlide === 0) ? slides.length - 1 : currentSlide - 1;
      showSlide(currentSlide);
    });

    document.querySelector('.next').addEventListener('click', () => {
      currentSlide = (currentSlide + 1) % slides.length;
      showSlide(currentSlide);
    });

    dots.forEach((dot, i) => {
      dot.addEventListener('click', () => {
        currentSlide = i;
        showSlide(currentSlide);
      });
    });

    // Auto slide
    setInterval(() => {
      currentSlide = (currentSlide + 1) % slides.length;
      showSlide(currentSlide);
    }, 5000);

    showSlide(currentSlide);

  const buttons = document.querySelectorAll('.filter-button button');

  buttons.forEach(button => {
    button.addEventListener('click', function() {
      buttons.forEach(btn => btn.classList.remove('active'));
      this.classList.add('active');
    });
  });


  