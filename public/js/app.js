/* ═══════════════════════════════════════════════════════════════════
   RUANG GTK — Concept "ConSentinel" Kinetic Controller
   Caustic sweep, interactive brand typography, cursor spotlights,
   3D tilt, count-up, mobile drawer & live presensi updates.
   ═══════════════════════════════════════════════════════════════════ */

(function () {
  'use strict';

  document.documentElement.classList.add('js');

  // 1. Toast Notification Auto-hide
  var toast = document.querySelector('.toast');
  if (toast) {
    requestAnimationFrame(function () { toast.classList.add('show'); });
    setTimeout(function () { toast.classList.remove('show'); }, 4000);
  }

  // 2. Reduce Motion Check
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // 3. Intro Veil Dismissal
  var veil = document.querySelector('.intro-veil');
  if (veil) {
    var removeVeil = function () {
      if (veil && veil.parentNode) {
        veil.parentNode.removeChild(veil);
      }
    };
    veil.addEventListener('animationend', function (e) {
      if (e.animationName === 'veilOut') removeVeil();
    });
    setTimeout(removeVeil, 2000);
  }

  // 4. Interactive Cursor Spotlight on Glass Elements
  if (!reduceMotion && window.innerWidth > 768) {
    var spotlightTargets = document.querySelectorAll('.glass, .glass-soft, .stat, .landing-feature, .ann-item');
    spotlightTargets.forEach(function (el) {
      el.addEventListener('mousemove', function (e) {
        var rect = el.getBoundingClientRect();
        var x = e.clientX - rect.left;
        var y = e.clientY - rect.top;
        el.style.setProperty('--mouse-x', x + 'px');
        el.style.setProperty('--mouse-y', y + 'px');
      });
    });

    // 3D Card Tilt on Hover
    var tiltCards = document.querySelectorAll('.stat, .landing-feature');
    tiltCards.forEach(function (card) {
      card.addEventListener('mousemove', function (e) {
        var rect = card.getBoundingClientRect();
        var x = e.clientX - rect.left - rect.width / 2;
        var y = e.clientY - rect.top - rect.height / 2;
        var rotateX = (y / (rect.height / 2)) * -4;
        var rotateY = (x / (rect.width / 2)) * 4;
        card.style.transform = 'perspective(900px) rotateX(' + rotateX.toFixed(2) + 'deg) rotateY(' + rotateY.toFixed(2) + 'deg) translateY(-3px)';
      });
      card.addEventListener('mouseleave', function () {
        card.style.transform = '';
      });
    });
  }

  // 5. Count-up Animation for Metrics
  var counters = document.querySelectorAll('.count-up');
  if (counters.length) {
    var formatId = function (n) { return Math.round(n).toLocaleString('id-ID'); };
    if (reduceMotion || !('IntersectionObserver' in window)) {
      counters.forEach(function (el) {
        el.textContent = formatId(parseFloat(el.getAttribute('data-count') || '0'));
      });
    } else {
      var ioCount = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          var el = entry.target;
          ioCount.unobserve(el);
          var targetVal = parseFloat(el.getAttribute('data-count') || '0');
          var dur = 1200;
          var startTime = null;
          var step = function (ts) {
            if (!startTime) startTime = ts;
            var progress = Math.min((ts - startTime) / dur, 1);
            var ease = 1 - Math.pow(1 - progress, 3);
            el.textContent = formatId(targetVal * ease);
            if (progress < 1) requestAnimationFrame(step);
          };
          requestAnimationFrame(step);
        });
      }, { threshold: 0.3 });
      counters.forEach(function (el) { ioCount.observe(el); });
    }
  }

  // 6. Scroll Reveal Observer
  var revealElements = document.querySelectorAll('.reveal');
  if (revealElements.length) {
    if (!reduceMotion && 'IntersectionObserver' in window) {
      var ioReveal = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            var target = entry.target;
            var delay = parseInt(target.getAttribute('data-reveal-delay') || '0', 10);
            target.style.transitionDelay = delay + 'ms';
            target.classList.add('in');
            ioReveal.unobserve(target);
          }
        });
      }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
      revealElements.forEach(function (el) { ioReveal.observe(el); });
    } else {
      revealElements.forEach(function (el) { el.classList.add('in'); });
    }
  }

  // 7. Mobile Drawer Navigation
  var hamburgerBtn = document.getElementById('hamburgerBtn');
  var sideClose    = document.getElementById('sideClose');
  var sidebar      = document.getElementById('sidebar');
  var overlay      = document.getElementById('sideOverlay');

  function openSidebar() {
    if (!sidebar) return;
    sidebar.classList.add('open');
    if (overlay) { overlay.classList.add('active'); overlay.removeAttribute('aria-hidden'); }
    if (hamburgerBtn) hamburgerBtn.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
  }

  function closeSidebar() {
    if (!sidebar) return;
    sidebar.classList.remove('open');
    if (overlay) { overlay.classList.remove('active'); overlay.setAttribute('aria-hidden', 'true'); }
    if (hamburgerBtn) hamburgerBtn.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  }

  if (hamburgerBtn) hamburgerBtn.addEventListener('click', openSidebar);
  if (sideClose)    sideClose.addEventListener('click', closeSidebar);
  if (overlay)      overlay.addEventListener('click', closeSidebar);

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && sidebar && sidebar.classList.contains('open')) {
      closeSidebar();
    }
  });

  // 7b. aaPanel Auxiliary Mini-Sidebar Toggle & LocalStorage Memory
  var appShell = document.getElementById('appShell');
  var auxToggleBtn = document.getElementById('auxToggleBtn');

  if (appShell && localStorage.getItem('ruanggtk_sidebar_collapsed') === '1') {
    appShell.classList.add('is-collapsed');
  }

  if (auxToggleBtn && appShell) {
    auxToggleBtn.addEventListener('click', function (e) {
      e.preventDefault();
      var isCollapsed = appShell.classList.toggle('is-collapsed');
      localStorage.setItem('ruanggtk_sidebar_collapsed', isCollapsed ? '1' : '0');
    });
  }

  // 8. Modal Dialog Controllers
  document.addEventListener('click', function (e) {
    var opener = e.target.closest('[data-dialog]');
    if (opener) {
      var dlgSelector = opener.getAttribute('data-dialog');
      var dlg = document.querySelector(dlgSelector);
      if (dlg) {
        e.preventDefault();
        Array.prototype.forEach.call(opener.attributes, function (attr) {
          if (attr.name.indexOf('data-field-') === 0) {
            var fieldName = attr.name.replace('data-field-', '');
            var input = dlg.querySelector('[name="' + fieldName + '"]');
            if (input) input.value = attr.value;
          }
        });
        dlg.showModal();
      }
      return;
    }

    if (e.target.closest('[data-close]')) {
      var currentDlg = e.target.closest('dialog');
      if (currentDlg) currentDlg.close();
      return;
    }

    if (e.target instanceof HTMLDialogElement) {
      e.target.close();
    }
  });

  // 9. Form Confirmation & Loading Button Protection
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (form.matches('[data-confirm]') && !window.confirm(form.getAttribute('data-confirm'))) {
      e.preventDefault();
      return;
    }

    var submitBtn = form.querySelector('button[type="submit"], button.btn-ink, button.btn-god');
    if (submitBtn && !submitBtn.disabled) {
      var loadingText = submitBtn.getAttribute('data-loading') || 'Memproses...';
      submitBtn.setAttribute('data-original-text', submitBtn.innerHTML);
      submitBtn.innerHTML = loadingText;
      submitBtn.classList.add('loading');
      setTimeout(function () {
        submitBtn.disabled = true;
      }, 20);
    }
  });

  // 10. Auto-fill Bill Nominal from Selected Type
  var billTypeSelect  = document.getElementById('billTypeSelect');
  var billAmountInput = document.getElementById('billAmountInput');
  if (billTypeSelect && billAmountInput) {
    var syncBillAmount = function () {
      var opt = billTypeSelect.options[billTypeSelect.selectedIndex];
      var amount = opt ? opt.getAttribute('data-amount') : '';
      if (amount && amount !== '0') {
        billAmountInput.value = amount;
      }
    };
    billTypeSelect.addEventListener('change', syncBillAmount);
    syncBillAmount();
  }

  // 11. Quick Demo Credential Autofill
  var demoButtons = document.querySelectorAll('[data-demo-subdomain]');
  demoButtons.forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      var sub = btn.getAttribute('data-demo-subdomain');
      var user = btn.getAttribute('data-demo-user');
      var pass = btn.getAttribute('data-demo-pass');
      var subInput = document.getElementById('subdomain');
      var userInput = document.getElementById('username');
      var passInput = document.getElementById('password');
      if (subInput && sub) subInput.value = sub;
      if (userInput && user) userInput.value = user;
      if (passInput && pass) passInput.value = pass;
    });
  });

})();
