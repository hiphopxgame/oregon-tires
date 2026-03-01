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
<header class="bg-white shadow-md sticky top-0 z-50 dark:bg-gray-800">
  <div class="container mx-auto px-4 py-3 flex justify-between items-center">
    <a href="/">
      <picture><source srcset="/assets/logo.webp" type="image/webp"><img src="/assets/logo.png" alt="Oregon Tires Auto Care" class="h-14 w-auto" width="781" height="275" loading="eager"></picture>
    </a>
    <nav class="hidden md:flex items-center gap-6">
      <a href="/" class="text-brand dark:text-green-400 font-medium hover:opacity-70">Home</a>
      <a href="/#services" class="text-brand dark:text-green-400 font-medium hover:opacity-70">Services</a>
      <a href="/#about" class="text-brand dark:text-green-400 font-medium hover:opacity-70">About</a>
      <a href="/#reviews" class="text-brand dark:text-green-400 font-medium hover:opacity-70">Reviews</a>
      <a href="/#contact" class="text-brand dark:text-green-400 font-medium hover:opacity-70">Contact</a>
      <a href="/blog" class="text-brand dark:text-green-400 font-medium hover:opacity-70">Blog</a>
      <a href="/book-appointment" class="bg-amber-500 text-black px-5 py-2 rounded-lg font-semibold hover:bg-amber-600 transition">Schedule Service</a>
    </nav>
    <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden'); this.setAttribute('aria-expanded', this.getAttribute('aria-expanded') === 'true' ? 'false' : 'true')" class="md:hidden text-brand dark:text-green-400 text-2xl" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="mobile-menu">&#9776;</button>
  </div>
  <div id="mobile-menu" class="hidden md:hidden bg-white border-t px-4 pb-4 dark:bg-gray-800 dark:border-gray-700">
    <a href="/" class="block py-2 text-brand dark:text-green-400">Home</a>
    <a href="/#services" class="block py-2 text-brand dark:text-green-400">Services</a>
    <a href="/#about" class="block py-2 text-brand dark:text-green-400">About</a>
    <a href="/#reviews" class="block py-2 text-brand dark:text-green-400">Reviews</a>
    <a href="/#contact" class="block py-2 text-brand dark:text-green-400">Contact</a>
    <a href="/blog" class="block py-2 text-brand dark:text-green-400">Blog</a>
    <a href="/book-appointment" class="block py-2 text-brand dark:text-green-400 font-semibold">Schedule Service</a>
  </div>
</header>
