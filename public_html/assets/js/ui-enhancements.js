// Oregon Tires — UI Enhancements (Mobile CTA + Scroll Spy)
// Extracted from index.html inline scripts

// === Hide Mobile CTA on Contact Section ===
(function() {
  var cta = document.getElementById('mobile-cta');
  var contact = document.getElementById('contact');
  if (!cta || !contact) return;
  var obs = new IntersectionObserver(function(entries) {
    entries.forEach(function(e) {
      cta.style.transform = e.isIntersecting ? 'translateY(100%)' : 'translateY(0)';
      cta.style.transition = 'transform 0.3s ease';
    });
  }, { threshold: 0.1 });
  obs.observe(contact);
})();

// === Scroll-Spy: Highlight Active Nav Link ===
(function() {
  var sections = document.querySelectorAll('section[id]');
  var navLinks = document.querySelectorAll('header nav a[href^="#"], #mobile-menu a[href^="#"]');
  if (!sections.length || !navLinks.length) return;

  var activeClass = 'underline underline-offset-4 decoration-2';

  var observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) {
        var id = entry.target.id;
        navLinks.forEach(function(link) {
          var href = link.getAttribute('href');
          if (href === '#' + id) {
            activeClass.split(' ').forEach(function(c) { link.classList.add(c); });
            link.setAttribute('aria-current', 'true');
          } else {
            activeClass.split(' ').forEach(function(c) { link.classList.remove(c); });
            link.removeAttribute('aria-current');
          }
        });
      }
    });
  }, { rootMargin: '-20% 0px -75% 0px', threshold: 0 });

  sections.forEach(function(s) { observer.observe(s); });
})();
