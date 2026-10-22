/* BK Enfab public site behaviour (no dependencies besides Bootstrap). */
(function () {
  'use strict';

  // Sticky header shadow
  var header = document.querySelector('.site-header');
  if (header) {
    var onScroll = function () { header.classList.toggle('scrolled', window.scrollY > 10); };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  // Analytics loads only after consent (GA4 / GTM IDs set in admin)
  var A = window.BKE_ANALYTICS;
  var CONSENT_KEY = 'bke_consent';
  function getConsent() { try { return localStorage.getItem(CONSENT_KEY); } catch (e) { return null; } }
  function setConsent(v) { try { localStorage.setItem(CONSENT_KEY, v); } catch (e) {} }
  function loadScript(src) { var s = document.createElement('script'); s.async = true; s.src = src; document.head.appendChild(s); }
  function startAnalytics() {
    if (!A || window.__bkeAnalytics) return;
    window.__bkeAnalytics = true;
    if (typeof gtag === 'function') gtag('consent', 'update', { analytics_storage: 'granted' });
    if (A.gtm) {
      window.dataLayer.push({ 'gtm.start': Date.now(), event: 'gtm.js' });
      loadScript('https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent(A.gtm));
    }
    if (A.ga4) {
      loadScript('https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(A.ga4));
      gtag('js', new Date());
      gtag('config', A.ga4, { anonymize_ip: true });
    }
  }
  if (A) {
    var banner = document.getElementById('cookieBanner');
    var consent = getConsent();
    if (!A.banner || consent === 'granted') startAnalytics();
    else if (banner && !consent) banner.hidden = false;
    if (banner) banner.addEventListener('click', function (e) {
      var v = e.target.getAttribute('data-consent');
      if (!v) return;
      setConsent(v);
      banner.hidden = true;
      if (v === 'granted') startAnalytics();
    });
  }

  // Click tracking for phone / email / WhatsApp / CTAs (no personal data sent)
  document.addEventListener('click', function (e) {
    var el = e.target.closest('[data-track]');
    if (!el) return;
    var name = el.getAttribute('data-track');
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({ event: 'bke_click', click_type: name, page_path: location.pathname });
    if (typeof gtag === 'function') gtag('event', name + '_click', { page_path: location.pathname });
  });

  // Prevent double submit on lead forms
  document.querySelectorAll('[data-lead-form]').forEach(function (form) {
    form.addEventListener('submit', function () {
      var btn = form.querySelector('[type=submit]');
      if (btn) { setTimeout(function () { btn.disabled = true; btn.textContent = 'Sending…'; }, 0); }
    });
  });

  // Minimal accessible lightbox for galleries
  document.addEventListener('click', function (e) {
    var a = e.target.closest('[data-lightbox]');
    if (!a) return;
    e.preventDefault();
    var box = document.createElement('div');
    box.className = 'lightbox';
    box.setAttribute('role', 'dialog');
    box.setAttribute('aria-modal', 'true');
    box.setAttribute('aria-label', 'Image viewer');
    var img = document.createElement('img');
    img.src = a.getAttribute('href');
    img.alt = (a.querySelector('img') || {}).alt || '';
    var close = document.createElement('button');
    close.type = 'button';
    close.setAttribute('aria-label', 'Close');
    close.innerHTML = '&times;';
    box.appendChild(close);
    box.appendChild(img);
    var cap = a.getAttribute('data-caption');
    if (cap) { var p = document.createElement('p'); p.textContent = cap; box.appendChild(p); }
    function shut() { box.remove(); document.removeEventListener('keydown', onKey); a.focus(); }
    function onKey(ev) { if (ev.key === 'Escape') shut(); }
    box.addEventListener('click', function (ev) { if (ev.target !== img) shut(); });
    document.addEventListener('keydown', onKey);
    document.body.appendChild(box);
    close.focus();
  });

  // Subtle reveal of sections
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); } });
    }, { rootMargin: '0px 0px -60px 0px' });
    document.querySelectorAll('.section .card, .section .testimonial, .industry-chip, .roof-tile').forEach(function (el) { el.classList.add('reveal'); io.observe(el); });
    // Never leave content hidden when printing or if the observer does not fire.
    var showAll = function () { document.querySelectorAll('.reveal').forEach(function (el) { el.classList.add('in'); }); };
    window.addEventListener('beforeprint', showAll);
    setTimeout(function () { if (document.hidden) showAll(); }, 1500);
  }
})();
