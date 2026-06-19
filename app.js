(() => {
  'use strict';

  /* ---- Mobile Nav Toggle ---- */
  const toggle = document.querySelector('.nav-toggle');
  const navLinks = document.querySelector('.nav-links');
  if (toggle && navLinks) {
    toggle.addEventListener('click', () => {
      navLinks.classList.toggle('open');
      toggle.classList.toggle('open');
    });
    document.addEventListener('click', (e) => {
      if (!e.target.closest('.navbar')) {
        navLinks.classList.remove('open');
        toggle.classList.remove('open');
      }
    });
  }

  /* ---- Active Nav Highlight ---- */
  const currentPath = location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-links a').forEach(link => {
    const href = link.getAttribute('href');
    if (href === currentPath || (currentPath === '' && href === 'index.html')) {
      link.classList.add('active');
    }
    if (href && href.includes('#')) {
      const [page, hash] = href.split('#');
      if ((page === currentPath || (currentPath === '' && page === 'index.html')) && location.hash === '#' + hash) {
        link.classList.add('active');
      }
    }
  });

  /* ---- Smooth Scroll ---- */
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', e => {
      const target = document.querySelector(anchor.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  /* ---- Form Validation ---- */
  function validateForm(form) {
    let valid = true;
    form.querySelectorAll('[required]').forEach(field => {
      const error = field.parentElement.querySelector('.field-error') || (() => {
        const el = document.createElement('span');
        el.className = 'field-error';
        field.parentElement.appendChild(el);
        return el;
      })();
      field.classList.remove('invalid');
      error.textContent = '';
      if (!field.value.trim()) {
        error.textContent = 'This field is required';
        field.classList.add('invalid');
        valid = false;
      } else if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
        error.textContent = 'Please enter a valid email';
        field.classList.add('invalid');
        valid = false;
      } else if (field.type === 'password' && field.dataset.match) {
        const match = document.getElementById(field.dataset.match);
        if (match && field.value !== match.value) {
          error.textContent = 'Passwords do not match';
          field.classList.add('invalid');
          valid = false;
        }
      }
    });
    if (valid) {
      const btn = form.querySelector('button[type="submit"]');
      const orig = btn.textContent;
      btn.textContent = 'Sending...';
      btn.disabled = true;
      setTimeout(() => { btn.textContent = orig; btn.disabled = false; }, 2000);
    }
    return valid;
  }

  document.querySelectorAll('form[data-validate]').forEach(form => {
    form.addEventListener('submit', e => { if (!validateForm(form)) e.preventDefault(); });
  });

  /* ---- Password Toggle ---- */
  document.querySelectorAll('.password-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = document.getElementById(btn.dataset.target);
      if (input) {
        input.type = input.type === 'password' ? 'text' : 'password';
        btn.classList.toggle('fa-eye');
        btn.classList.toggle('fa-eye-slash');
      }
    });
  });

  /* ---- FAQ Accordion ---- */
  document.querySelectorAll('.faq-item').forEach(item => {
    const question = item.querySelector('.faq-question');
    if (question) {
      question.addEventListener('click', () => {
        const isOpen = item.classList.contains('open');
        document.querySelectorAll('.faq-item.open').forEach(el => el.classList.remove('open'));
        if (!isOpen) item.classList.add('open');
      });
    }
  });

  /* ---- FAQ Category Filter ---- */
  const faqFilters = document.querySelectorAll('.faq-filter');
  const faqItems = document.querySelectorAll('.faq-item');
  if (faqFilters.length && faqItems.length) {
    faqFilters.forEach(btn => {
      btn.addEventListener('click', () => {
        faqFilters.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const cat = btn.dataset.category;
        faqItems.forEach(item => {
          item.style.display = cat === 'all' || item.dataset.category === cat ? '' : 'none';
        });
      });
    });
  }

  /* ---- Pricing Toggle ---- */
  const pricingToggle = document.getElementById('pricing-toggle');
  const prices = document.querySelectorAll('.price-amount');
  if (pricingToggle && prices.length) {
    function updatePrices(annual) {
      prices.forEach(el => {
        const annualVal = el.dataset.annual;
        el.textContent = annual ? annualVal : el.dataset.monthly;
      });
      document.querySelectorAll('.pricing-period').forEach(el => {
        el.textContent = annual ? '/year' : '/month';
      });
    }
    pricingToggle.addEventListener('change', () => updatePrices(pricingToggle.checked));
    updatePrices(pricingToggle.checked);
  }

  /* ---- Scroll Fade-in Animations ---- */
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
    document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
  }

})();
