/**
 * Universal Nav — HipHop.World Network
 * Self-contained JS for nav interactions.
 */
(function() {
    'use strict';

    var nav = document.getElementById('hhw-universal-nav');
    if (!nav) return;

    var hubUrl = nav.getAttribute('data-hub') || 'https://hiphop.world';
    var isLoggedIn = nav.getAttribute('data-logged-in') === '1';

    // ── Mobile menu toggle ──
    var mobileBtn = document.getElementById('hhw-mobile-btn');
    var mobileMenu = document.getElementById('hhw-mobile-menu');
    var menuOpen = document.getElementById('hhw-menu-open');
    var menuClose = document.getElementById('hhw-menu-close');

    if (mobileBtn && mobileMenu) {
        function openMobile() {
            mobileMenu.removeAttribute('hidden');
            // Force reflow before adding class for animation
            mobileMenu.offsetHeight;
            mobileMenu.classList.add('hhw-open');
            menuOpen.setAttribute('hidden', '');
            menuClose.removeAttribute('hidden');
            mobileBtn.setAttribute('aria-expanded', 'true');
        }

        function closeMobile() {
            mobileMenu.classList.remove('hhw-open');
            menuOpen.removeAttribute('hidden');
            menuClose.setAttribute('hidden', '');
            mobileBtn.setAttribute('aria-expanded', 'false');
            // Wait for animation then hide
            setTimeout(function() {
                if (!mobileMenu.classList.contains('hhw-open')) {
                    mobileMenu.setAttribute('hidden', '');
                }
            }, 300);
        }

        function isMobileOpen() {
            return mobileMenu.classList.contains('hhw-open');
        }

        mobileBtn.addEventListener('click', function() {
            if (isMobileOpen()) { closeMobile(); } else { openMobile(); }
        });

        document.addEventListener('click', function(e) {
            if (isMobileOpen() && !mobileMenu.contains(e.target) && !mobileBtn.contains(e.target)) {
                closeMobile();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isMobileOpen()) {
                closeMobile();
                mobileBtn.focus();
            }
        });

        // Close mobile menu when a link is clicked
        var mobileLinks = mobileMenu.querySelectorAll('a');
        for (var i = 0; i < mobileLinks.length; i++) {
            mobileLinks[i].addEventListener('click', closeMobile);
        }
    }

    // ── Avatar dropdown toggle ──
    var avatarBtn = document.getElementById('hhw-avatar-btn');
    var avatarDrop = document.getElementById('hhw-avatar-dropdown');

    if (avatarBtn && avatarDrop) {
        avatarBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            var isHidden = avatarDrop.hasAttribute('hidden');
            if (isHidden) {
                avatarDrop.removeAttribute('hidden');
                avatarBtn.setAttribute('aria-expanded', 'true');
            } else {
                avatarDrop.setAttribute('hidden', '');
                avatarBtn.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('click', function(e) {
            if (!avatarDrop.hasAttribute('hidden') && !avatarDrop.contains(e.target) && !avatarBtn.contains(e.target)) {
                avatarDrop.setAttribute('hidden', '');
                avatarBtn.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !avatarDrop.hasAttribute('hidden')) {
                avatarDrop.setAttribute('hidden', '');
                avatarBtn.setAttribute('aria-expanded', 'false');
                avatarBtn.focus();
            }
        });
    }

    // ── Credits balance fetch (cross-domain) ──
    if (isLoggedIn) {
        var creditsUrl = hubUrl + '/api/credits/balance.php';
        fetch(creditsUrl, { credentials: 'include' })
            .then(function(r) { return r.ok ? r.json() : null; })
            .then(function(data) {
                if (!data || !data.success) return;
                var balance = data.balance || 0;
                var formatted = balance.toLocaleString();

                var desktopEl = document.getElementById('hhw-credits-amount');
                if (desktopEl) desktopEl.textContent = formatted;

                var mobileEl = document.getElementById('hhw-mobile-credits-amount');
                if (mobileEl) mobileEl.textContent = formatted;
            })
            .catch(function() {
                // Silently fail — credits meter stays as "--"
            });
    }

})();
