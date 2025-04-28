const menuToggle = document.querySelector('.menu-toggle');
const navLinks = document.querySelector('.nav-links');

menuToggle.addEventListener('click', () => {
  navLinks.classList.toggle('show');
});

// Carousel
let slides = document.querySelectorAll('.carousel-inner img');
let dots = document.querySelectorAll('.dot');
let index = 0;

function nextSlide() {
  slides[index].classList.remove('active');
  dots[index].classList.remove('active');
  index = (index + 1) % slides.length;
  slides[index].classList.add('active');
  dots[index].classList.add('active');
}

setInterval(nextSlide, 3000);
