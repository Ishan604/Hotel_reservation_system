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

// FAQ Accordion
const faqQuestions = document.querySelectorAll('.faq-question');
faqQuestions.forEach(question => {
  question.addEventListener('click', () => {
    const item = question.parentNode;
    item.classList.toggle('active');
    
    // Close other open items
    faqQuestions.forEach(otherQuestion => {
      if (otherQuestion !== question) {
        otherQuestion.parentNode.classList.remove('active');
      }
    });
  });
});

// Form submission
const contactForm = document.getElementById('contactForm');
contactForm.addEventListener('submit', (e) => {
  e.preventDefault();
  // Here you would typically send the form data to your server
  alert('Thank you for your message! We will get back to you soon.');
  contactForm.reset();
});

// Animation triggers for footer and other elements
document.addEventListener('DOMContentLoaded', () => {
  const animateElements = document.querySelectorAll('.animate__animated');
  const footerCols = document.querySelectorAll('.footer-col');
  
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const element = entry.target;
        const animationClass = element.classList[1];
        element.classList.add(animationClass);
        observer.unobserve(element);
      }
    });
  }, { threshold: 0.1 });

  animateElements.forEach(el => {
    if (!el.classList.contains('animate__fadeIn')) { // Skip hero elements already animated
      observer.observe(el);
    }
  });

  footerCols.forEach(col => {
    observer.observe(col);
  });
});