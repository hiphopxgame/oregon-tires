<?php
/**
 * Oregon Tires — Google Analytics 4 + Search Verification
 * Include in <head> of ALL pages. Outputs:
 * 1. Google site verification meta tag (if configured)
 * 2. Standard gtag.js snippet for GA4 tracking
 *
 * Usage: <?php require_once __DIR__ . '/includes/gtag.php'; ?>
 */
$_gaId = 'G-PCK6ZYFHQ0';
$_gsvCode = $_ENV['GOOGLE_SITE_VERIFICATION'] ?? '';
if ($_gsvCode): ?>
<meta name="google-site-verification" content="<?= htmlspecialchars($_gsvCode) ?>">
<?php endif; ?>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= $_gaId ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?= $_gaId ?>');
</script>
