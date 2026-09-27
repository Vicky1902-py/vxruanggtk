/* Ruang GTK — helper UI ringan tanpa dependency */
(function () {
  'use strict';

  // Penanda JS aktif (gate untuk scroll-reveal agar tanpa JS konten tetap tampil)
  document.documentElement.classList.add('js');

  // Toast auto-hide
  var toast = document.querySelector('.toast');
  if (toast) {
    requestAnimationFrame(function () { toast.classList.add('show'); });
    setTimeout(function () { toast.classList.remove('show'); }, 3800);
  }

  // Scroll-reveal ala Nival: elemen .reveal muncul saat masuk viewport
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Intro veil: buang dari DOM setelah animasi selesai (safety 2.5s)
  var veil = document.querySelector('.intro-veil');
  if (veil) {
    var kill = function () { if (veil && veil.parentNode) veil.parentNode.removeChild(veil); };
    veil.addEventListener('animationend', function (e) {
      if (e.animationName === 'veilOut') kill();
    });
    setTimeout(kill, 2500);
  }

  // Count-up angka statistik (format Indonesia)
  var counters = document.querySelectorAll('.count-up');
  if (counters.length) {
    var fmt = function (n) { return Math.round(n).toLocaleString('id-ID'); };
    if (reduceMotion || !('requestAnimationFrame' in window)) {
      counters.forEach(function (el) { el.textContent = fmt(parseFloat(el.getAttribute('data-count') || '0')); });
    } else {
      var ioCount = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          var el = entry.target;
          ioCount.unobserve(el);
          var target = parseFloat(el.getAttribute('data-count') || '0');
          var dur = 950;
          var t0 = null;
          var step = function (t) {
            if (t0 === null) t0 = t;
            var p = Math.min((t - t0) / dur, 1);
            var eased = 1 - Math.pow(1 - p, 3);
            el.textContent = fmt(target * eased);
            if (p < 1) requestAnimationFrame(step);
          };
          requestAnimationFrame(step);
        });
      }, { threshold: 0.4 });
      counters.forEach(function (el) { ioCount.observe(el); });
    }
  }

  var revealEls = document.querySelectorAll('.reveal');
  if (revealEls.length) {
    if (!reduceMotion && 'IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            var el = entry.target;
            var delay = parseInt(el.getAttribute('data-reveal-delay') || '0', 10);
            el.style.transitionDelay = delay + 'ms';
            el.classList.add('in');
            io.unobserve(el);
          }
        });
      }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
      revealEls.forEach(function (el) { io.observe(el); });
    } else {
      revealEls.forEach(function (el) { el.classList.add('in'); });
    }
  }

  // ── Mobile hamburger menu ──────────────────────────────────
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

  // Tutup sidebar saat tekan Escape
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && sidebar && sidebar.classList.contains('open')) {
      closeSidebar();
    }
  });

  // ── Dialog: buka via [data-dialog="#id"], tutup via [data-close] ──
  document.addEventListener('click', function (e) {
    var opener = e.target.closest('[data-dialog]');
    if (opener) {
      var dlg = document.querySelector(opener.getAttribute('data-dialog'));
      if (dlg) {
        e.preventDefault();
        // Prefill: salin data-* opener ke elemen [name] di dalam dialog
        Array.prototype.forEach.call(opener.attributes, function (attr) {
          if (attr.name.indexOf('data-field-') === 0) {
            var name = attr.name.replace('data-field-', '');
            var input = dlg.querySelector('[name="' + name + '"]');
            if (input) input.value = attr.value;
          }
        });
        dlg.showModal();
      }
      return;
    }
    if (e.target.closest('[data-close]')) {
      var d = e.target.closest('dialog');
      if (d) d.close();
      return;
    }
    // Klik backdrop menutup dialog
    if (e.target instanceof HTMLDialogElement) {
      e.target.close();
    }
  });

  // ── Konfirmasi hapus ───────────────────────────────────────
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (form.matches('[data-confirm]') && !window.confirm(form.getAttribute('data-confirm'))) {
      e.preventDefault();
      return;
    }
    // Loading state: tombol dengan data-loading
    var btn = form.querySelector('button[data-loading]');
    if (btn) {
      var label = btn.getAttribute('data-loading') || 'Menyimpan...';
      btn.textContent = label;
      btn.classList.add('loading');
      btn.disabled = true;
    }
  });

  // ── Auto-fill nominal tagihan dari jenis yang dipilih ──────
  var billTypeSelect  = document.getElementById('billTypeSelect');
  var billAmountInput = document.getElementById('billAmountInput');
  if (billTypeSelect && billAmountInput) {
    function syncAmount() {
      var opt = billTypeSelect.options[billTypeSelect.selectedIndex];
      var amount = opt ? opt.getAttribute('data-amount') : '';
      if (amount && amount !== '0') {
        billAmountInput.value = amount;
      }
    }
    billTypeSelect.addEventListener('change', syncAmount);
    // Isi otomatis saat halaman dimuat
    syncAmount();
  }

})();
