// Mobile Menu Toggle
const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
const navLinks = document.querySelector('.nav-links');

mobileMenuBtn.addEventListener('click', () => {
  mobileMenuBtn.classList.toggle('active');
  navLinks.classList.toggle('active');
});

// Navbar scroll effect
window.addEventListener('scroll', () => {
  const navbar = document.querySelector('.navbar');
  if (window.scrollY > 50) {
    navbar.classList.add('scrolled');
  } else {
    navbar.classList.remove('scrolled');
  }
});

// Animation triggers for footer
document.addEventListener('DOMContentLoaded', () => {
  const footerCols = document.querySelectorAll('.footer-col');
  
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animate__fadeInUp');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });

  footerCols.forEach(col => {
    observer.observe(col);
  });

  // Animate service sections as they come into view
  const animateElements = document.querySelectorAll('.animate__animated');
  
  const elementObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const element = entry.target;
        const animationClass = element.classList[1];
        element.classList.add(animationClass);
        elementObserver.unobserve(element);
      }
    });
  }, { threshold: 0.1 });

  animateElements.forEach(el => {
    if (!el.classList.contains('animate__fadeIn')) { // Skip hero elements already animated
      elementObserver.observe(el);
    }
  });
});