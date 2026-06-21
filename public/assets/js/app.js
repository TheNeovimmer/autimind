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

  /* ===============================
     TOAST NOTIFICATION SYSTEM
     =============================== */
  window.showToast = function(type, message, duration) {
    duration = duration || 4000;
    var container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      document.body.appendChild(container);
    }

    var icons = {
      success: 'fa-check-circle',
      error: 'fa-exclamation-circle',
      warning: 'fa-exclamation-triangle',
      info: 'fa-info-circle'
    };

    var toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.innerHTML =
      '<div class="toast-icon"><i class="fas ' + (icons[type] || icons.info) + '"></i></div>' +
      '<div class="toast-msg">' + message + '</div>' +
      '<button class="toast-close" onclick="this.closest(\'.toast\').classList.add(\'hiding\');setTimeout(function(){this.closest(\'.toast\').remove()}.bind(this),300)">&times;</button>' +
      '<div class="toast-progress"></div>';

    container.appendChild(toast);

    requestAnimationFrame(function() {
      toast.classList.add('show');
    });

    setTimeout(function() {
      toast.classList.add('hiding');
      setTimeout(function() {
        if (toast.parentNode) toast.remove();
      }, 300);
    }, duration);
  };

  /* ===============================
     PASSWORD TOGGLE (dashboard)
     =============================== */
  window.togglePassword = function(el) {
    var input = el.closest('.input-wrap, .password-wrapper').querySelector('input');
    if (!input) return;
    var isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    el.classList.toggle('fa-eye', !isPassword);
    el.classList.toggle('fa-eye-slash', isPassword);
  };

  /* ===============================
     CONFIRM DIALOG
     =============================== */
  window.confirmAction = function(message, dangerLabel) {
    dangerLabel = dangerLabel || 'Delete';
    return new Promise(function(resolve) {
      var overlay = document.createElement('div');
      overlay.className = 'confirm-overlay';
      overlay.innerHTML =
        '<div class="confirm-dialog">' +
          '<h3>Confirm</h3>' +
          '<p>' + message + '</p>' +
          '<div class="confirm-actions">' +
            '<button class="confirm-cancel" data-action="cancel">Cancel</button>' +
            '<button class="confirm-danger" data-action="confirm">' + dangerLabel + '</button>' +
          '</div>' +
        '</div>';
      document.body.appendChild(overlay);

      requestAnimationFrame(function() {
        overlay.classList.add('open');
      });

      overlay.addEventListener('click', function(e) {
        var action = e.target.getAttribute('data-action');
        if (action === 'confirm') {
          overlay.classList.remove('open');
          setTimeout(function() { overlay.remove(); }, 200);
          resolve(true);
        } else if (action === 'cancel' || e.target === overlay) {
          overlay.classList.remove('open');
          setTimeout(function() { overlay.remove(); }, 200);
          resolve(false);
        }
      });
    });
  };

  /* ===============================
     SESSION TIMEOUT WARNING
     =============================== */
  (function() {
    var timeoutEl = document.getElementById('session-timeout-data');
    if (!timeoutEl) return;
    var lifetime = parseInt(timeoutEl.getAttribute('data-lifetime'), 10) || 3600;
    var warnAt = parseInt(timeoutEl.getAttribute('data-warn'), 10) || 300;
    var lastActivity = parseInt(timeoutEl.getAttribute('data-activity'), 10) || Math.floor(Date.now() / 1000);

    var checkInterval = setInterval(function() {
      var now = Math.floor(Date.now() / 1000);
      var elapsed = now - lastActivity;
      var remaining = lifetime - elapsed;

      if (remaining <= 0) {
        clearInterval(checkInterval);
        window.location.href = '/logout';
      } else if (remaining <= warnAt && remaining > 0) {
        var minutes = Math.ceil(remaining / 60);
        showToast('warning', 'Your session will expire in ' + minutes + ' minute' + (minutes > 1 ? 's' : '') + '. <a href="#" onclick="event.preventDefault();window.location.reload()" style="color:#fff;text-decoration:underline;">Extend</a>');
      }
    }, 30000);
  })();

  /* ===============================
     AUTO-INIT TOASTS FROM FLASH
     =============================== */
  var flashToast = document.getElementById('flash-toast-data');
  if (flashToast) {
    try {
      var data = JSON.parse(flashToast.textContent);
      if (data.type && data.message) {
        showToast(data.type, data.message);
      }
    } catch(e) {}
  }

  /* ===============================
     DESTRUCTIVE ACTION CONFIRMS
     =============================== */
  document.querySelectorAll('[data-confirm]').forEach(function(el) {
    el.addEventListener('click', function(e) {
      e.preventDefault();
      var msg = this.getAttribute('data-confirm') || 'Are you sure?';
      var label = this.getAttribute('data-confirm-label') || 'Delete';
      confirmAction(msg, label).then(function(confirmed) {
        if (confirmed) {
          if (el.tagName === 'A') {
            window.location.href = el.getAttribute('href');
          } else if (el.tagName === 'FORM' || el.closest('form')) {
            el.closest('form').submit();
          }
        }
      });
    });
  });

  /* ===============================
     FORM LOADING STATES
     =============================== */
  document.addEventListener('submit', function(e) {
    var form = e.target;
    var btn = form.querySelector('button[type="submit"]');
    if (btn && !btn.classList.contains('btn-loading')) {
      btn.classList.add('btn-loading');
    }
  });

})();
