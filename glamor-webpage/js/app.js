// Hamburger menu toggle
const menuToggle = document.querySelector('.menu-toggle');
const navLinks = document.querySelector('nav ul');

menuToggle.addEventListener('click', () => {
  navLinks.classList.toggle('show');
});

// Carousel auto-switch (optional)
const carouselImages = document.querySelectorAll('.carousel img');
let currentImageIndex = 0;

function showNextImage() {
  carouselImages[currentImageIndex].classList.remove('active');
  currentImageIndex = (currentImageIndex + 1) % carouselImages.length;
  carouselImages[currentImageIndex].classList.add('active');
}

// Change carousel image every 5 seconds
setInterval(showNextImage, 5000);
