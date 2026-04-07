<?php
/**
 * Oregon Tires — Site Header (reusable partial)
 * Extracted from index.html for use on PHP pages (members, approve, inspection, etc.)
 */

?>
<style>
  :root {
    --member-bg: #f8fafc;
    --member-surface: #ffffff;
    --member-surface-hover: #f1f5f9;
    --member-border: #e2e8f0;
    --member-text: #1e293b;
    --member-text-muted: #64748b;
    --member-accent: #15803d;
    --member-accent-hover: #0D3618;
    --member-accent-text: #ffffff;
  }
  @media (prefers-color-scheme: dark) {
    :root {
      --member-bg: #0C1A10;
      --member-surface: #132319;
      --member-surface-hover: #1E3325;
      --member-border: #2D4A33;
      --member-text: #DCE8DD;
      --member-text-muted: #8FAF92;
      --member-accent: #15803d;
      --member-accent-hover: #007030;
    }
  }
  .member-page { padding-top: 1rem; }
</style>
<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 bg-white text-black px-4 py-2 rounded shadow">Skip to content</a>
<!-- Top Info Bar -->
<div class="bg-brand text-white text-sm py-2">
  <div class="container mx-auto px-4 flex flex-wrap justify-between items-center gap-2">
    <div class="flex flex-wrap items-center gap-4">
      <span>&#x1F4DE; <a href="tel:5033679714" class="hover:text-amber-300">(503) 367-9714</a></span>
      <span class="hidden sm:inline">&#x2709;&#xFE0F; <a href="mailto:oregontirespdx@gmail.com" class="hover:text-amber-300">oregontirespdx@gmail.com</a></span>
      <span class="hidden md:inline">&#x1F4CD; 8536 SE 82nd Ave, Portland, OR 97266</span>
      <span class="hidden lg:inline" data-t="topHours">&#x1F550; Mon-Sat 7AM-7PM</span>
    </div>
    <div class="flex items-center gap-3">
      <a href="https://www.instagram.com/oregontires" target="_blank" class="hover:text-amber-300">Instagram</a>
      <a href="https://www.facebook.com/61571913202998/" target="_blank" class="hover:text-amber-300">Facebook</a>
      <button onclick="document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light')" aria-label="Toggle dark mode" class="text-sm text-white hover:text-amber-300 transition-colors">&#x1F319;</button>
      <button onclick="window.__toggleLang ? window.__toggleLang() : location.href='?lang=' + (localStorage.getItem('oregontires_lang') === 'es' ? 'en' : 'es')" class="hover:text-amber-300 font-medium" id="lang-toggle" aria-label="Switch language">&#x1F310; ES</button>
    </div>
  </div>
</div>
<header class="bg-white shadow-md sticky top-0 z-50 dark:bg-gray-800">
  <div class="container mx-auto px-4 py-3 flex justify-between items-center">
    <a href="/">
      <picture><source srcset="/assets/logo.webp" type="image/webp"><img src="/assets/logo.png" alt="Oregon Tires Auto Care" class="h-14 w-auto" width="781" height="275" loading="eager"></picture>
    </a>
    <nav class="hidden md:flex items-center gap-5">
      <a href="/" class="text-brand dark:text-green-400 font-medium hover:opacity-70" data-t="navHome">Home</a>
      <a href="/#services" class="text-brand dark:text-green-400 font-medium hover:opacity-70" data-t="navServices">Services</a>
      <a href="/financing" class="text-brand dark:text-green-400 font-medium hover:opacity-70" data-t="navFinancing">Financing</a>
      <a href="/#reviews" class="text-brand dark:text-green-400 font-medium hover:opacity-70" data-t="navReviews">Reviews</a>
      <div class="relative group">
        <button class="text-brand dark:text-green-400 font-medium hover:opacity-70 flex items-center gap-1" data-t="navMore">More <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></button>
        <div class="absolute top-full left-0 mt-1 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg shadow-xl py-2 w-48 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
          <a href="/why-us" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700" data-t="navWhyUs">Why Choose Us</a>
          <a href="/guarantee" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700" data-t="navGuarantee">Our Guarantee</a>
          <a href="/care-plan" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700" data-t="navCarePlan">Care Plan</a>
          <a href="/service-areas" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700" data-t="navServiceAreas">Service Areas</a>
          <a href="/blog" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700" data-t="navBlog">Blog</a>
          <a href="/promotions" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700" data-t="navPromotions">Promotions</a>
          <a href="/faq" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700" data-t="navFaq">FAQ</a>
          <a href="/#contact" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700" data-t="navContact">Contact</a>
        </div>
      </div>
      <?php if (!empty($_SESSION['member_id'])): ?>
      <a href="/dashboard" class="text-brand dark:text-green-400 font-medium hover:opacity-70"><?= ($_SESSION['dashboard_role'] ?? 'member') !== 'member' ? 'Dashboard' : 'My Account' ?></a>
      <?php endif; ?>
      <?php if (!empty($_SESSION['admin_id'])): ?>
      <a href="/admin/" class="text-amber-600 dark:text-amber-400 font-medium hover:opacity-70">Admin</a>
      <?php endif; ?>
      <?php if (!empty($_SESSION['member_id'])): ?>
      <button onclick="fetch('/api/member/logout.php',{method:'POST',credentials:'include'}).then(()=>location.href='/members')" class="text-red-500 dark:text-red-400 font-medium hover:opacity-70 text-sm bg-transparent border-0 cursor-pointer">Sign Out</button>
      <?php endif; ?>
      <a href="/book-appointment" class="bg-amber-500 text-black px-5 py-2 rounded-lg font-semibold hover:bg-amber-600 transition" data-t="navSchedule">Schedule Service</a>
    </nav>
    <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden'); this.setAttribute('aria-expanded', this.getAttribute('aria-expanded') === 'true' ? 'false' : 'true')" class="md:hidden text-brand dark:text-green-400 text-2xl" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="mobile-menu">&#9776;</button>
  </div>
  <div id="mobile-menu" class="hidden md:hidden bg-white border-t px-4 pb-4 dark:bg-gray-800 dark:border-gray-700">
    <a href="/" class="block py-2 text-brand dark:text-green-400" data-t="navHome">Home</a>
    <a href="/#services" class="block py-2 text-brand dark:text-green-400" data-t="navServices">Services</a>
    <a href="/financing" class="block py-2 text-brand dark:text-green-400" data-t="navFinancing">Financing</a>
    <a href="/#reviews" class="block py-2 text-brand dark:text-green-400" data-t="navReviews">Reviews</a>
    <a href="/why-us" class="block py-2 text-brand dark:text-green-400" data-t="navWhyUs">Why Choose Us</a>
    <a href="/guarantee" class="block py-2 text-brand dark:text-green-400" data-t="navGuarantee">Our Guarantee</a>
    <a href="/care-plan" class="block py-2 text-brand dark:text-green-400" data-t="navCarePlan">Care Plan</a>
    <a href="/service-areas" class="block py-2 text-brand dark:text-green-400" data-t="navServiceAreas">Service Areas</a>
    <a href="/blog" class="block py-2 text-brand dark:text-green-400" data-t="navBlog">Blog</a>
    <a href="/promotions" class="block py-2 text-brand dark:text-green-400" data-t="navPromotions">Promotions</a>
    <a href="/faq" class="block py-2 text-brand dark:text-green-400" data-t="navFaq">FAQ</a>
    <a href="/#contact" class="block py-2 text-brand dark:text-green-400" data-t="navContact">Contact</a>
    <?php if (!empty($_SESSION['member_id'])): ?>
    <a href="/dashboard" class="block py-2 text-brand dark:text-green-400 font-medium"><?= ($_SESSION['dashboard_role'] ?? 'member') !== 'member' ? 'Dashboard' : 'My Account' ?></a>
    <?php endif; ?>
    <?php if (!empty($_SESSION['admin_id'])): ?>
    <a href="/admin/" class="block py-2 text-amber-600 dark:text-amber-400 font-medium">Admin</a>
    <?php endif; ?>
    <?php if (!empty($_SESSION['member_id'])): ?>
    <button onclick="fetch('/api/member/logout.php',{method:'POST',credentials:'include'}).then(()=>location.href='/members')" class="block py-2 text-red-500 dark:text-red-400 font-medium bg-transparent border-0 cursor-pointer text-left w-full">Sign Out</button>
    <?php endif; ?>
    <a href="/book-appointment" class="block py-2 text-brand dark:text-green-400 font-semibold" data-t="navSchedule">Schedule Service</a>
  </div>
</header>
<script>
(function() {
  var menu = document.getElementById('mobile-menu');
  if (!menu) return;
  var toggle = document.querySelector('[aria-controls="mobile-menu"]');
  if (!toggle) return;

  function focusables() {
    return menu.querySelectorAll('a[href], button:not([disabled])');
  }
  function isOpen() { return !menu.classList.contains('hidden'); }
  function closeMenu() {
    menu.classList.add('hidden');
    toggle.setAttribute('aria-expanded', 'false');
    toggle.focus();
  }
  document.addEventListener('keydown', function(e) {
    if (!isOpen()) return;
    if (e.key === 'Escape') { e.preventDefault(); closeMenu(); return; }
    if (e.key === 'Tab') {
      var f = focusables();
      if (!f.length) return;
      var first = f[0], last = f[f.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  });
  document.addEventListener('click', function(e) {
    if (!isOpen()) return;
    if (menu.contains(e.target) || toggle.contains(e.target)) return;
    closeMenu();
  });
})();
</script>
