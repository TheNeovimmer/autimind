(() => {
  'use strict';

  let lenis = null;

  /* ---- Lenis Smooth Scroll ---- */
  if (typeof Lenis !== 'undefined') {
    lenis = new Lenis({
      duration: 1.2,
      easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
      orientation: 'vertical',
      smoothWheel: true,
    });

    function raf(time) {
      lenis.raf(time);
      requestAnimationFrame(raf);
    }
    requestAnimationFrame(raf);

    const scrollNavbar = document.querySelector('.navbar');
    if (scrollNavbar) {
      lenis.on('scroll', (e) => {
        scrollNavbar.classList.toggle('scrolled', e.animatedScroll > 50);
      });
    }
  }

  /* ---- Mobile Nav Overlay ---- */
  const toggle = document.querySelector('.nav-toggle');
  const navLinks = document.querySelector('.nav-links');
  let overlay = null;

  function createOverlay() {
    overlay = document.createElement('div');
    overlay.className = 'nav-overlay';
    const header = document.createElement('div');
    header.className = 'nav-overlay-header';
    const logo = document.querySelector('.nav-logo');
    if (logo) header.appendChild(logo.cloneNode(true));
    overlay.appendChild(header);
    const ul = document.createElement('ul');
    ul.className = 'nav-overlay-links';
    navLinks.querySelectorAll('li').forEach(li => {
      ul.appendChild(li.cloneNode(true));
    });
    overlay.appendChild(ul);
    document.body.appendChild(overlay);
    ul.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', closeOverlay);
    });
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) closeOverlay();
    });
  }

  function closeOverlay() {
    if (overlay) overlay.classList.remove('open');
    if (toggle) toggle.classList.remove('open');
  }

  if (toggle && navLinks) {
    toggle.addEventListener('click', () => {
      if (!overlay) createOverlay();
      overlay.classList.toggle('open');
      toggle.classList.toggle('open');
    });
  }

  window.addEventListener('resize', () => {
    if (window.innerWidth > 992) closeOverlay();
  });

  /* ---- Sticky Navbar Scroll Effect ---- */
  const navbar = document.querySelector('.navbar');
  if (navbar && typeof Lenis === 'undefined') {
    let ticking = false;
    window.addEventListener('scroll', () => {
      if (!ticking) {
        requestAnimationFrame(() => {
          navbar.classList.toggle('scrolled', window.scrollY > 50);
          ticking = false;
        });
        ticking = true;
      }
    }, { passive: true });
    if (window.scrollY > 50) navbar.classList.add('scrolled');
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
      const href = anchor.getAttribute('href');
      const target = document.querySelector(href);
      if (target) {
        e.preventDefault();
        if (lenis) {
          lenis.scrollTo(target);
        } else {
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
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

  /* ---- Custom Cursor ---- */
  const cursor = document.querySelector('.custom-cursor');
  const follower = document.querySelector('.cursor-follower');

  if (cursor && follower && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
    let mx = 0, my = 0, fx = 0, fy = 0;

    document.addEventListener('mousemove', (e) => {
      mx = e.clientX;
      my = e.clientY;
      cursor.style.transform = `translate(${mx - 8}px, ${my - 8}px)`;
    });

    (function animFollower() {
      fx += (mx - fx) * 0.12;
      fy += (my - fy) * 0.12;
      follower.style.transform = `translate(${fx - 20}px, ${fy - 20}px)`;
      requestAnimationFrame(animFollower);
    })();

    document.querySelectorAll('a, button, input, textarea, select, .nav-toggle, .pricing-card').forEach(el => {
      el.addEventListener('mouseenter', () => { cursor.style.opacity = '0'; follower.style.transform = `scale(1.6)`; follower.style.borderColor = 'var(--accent, #b388ff)'; });
      el.addEventListener('mouseleave', () => { cursor.style.opacity = '1'; follower.style.transform = `scale(1)`; follower.style.borderColor = 'rgba(255,255,255,0.6)'; });
    });
  }

})();
