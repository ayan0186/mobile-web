// Hamburger menu toggle
const menuToggle = document.querySelector('.menu-toggle');
const navLinks = document.querySelector('nav ul');

menuToggle.addEventListener('click', () => {
  navLinks.classList.toggle('show');
  menuToggle.classList.toggle('open');
});

// Carousel Auto Slide and Dots
const carouselImages = document.querySelectorAll('.carousel-inner img');
const dots = document.querySelectorAll('.dot');
let current = 0;

function showSlide(index) {
  carouselImages.forEach(img => img.classList.remove('active'));
  dots.forEach(dot => dot.classList.remove('active'));

  carouselImages[index].classList.add('active');
  dots[index].classList.add('active');
}

function nextSlide() {
  current = (current + 1) % carouselImages.length;
  showSlide(current);
}

// Auto move every 5 seconds
setInterval(nextSlide, 5000);

// Click on dots to move
dots.forEach((dot, index) => {
  dot.addEventListener('click', () => {
    current = index;
    showSlide(index);
  });
});
