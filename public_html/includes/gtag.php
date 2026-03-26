<?php
/**
 * Oregon Tires — Google Analytics 4 (static tag)
 * Include in <head> of ALL pages. This outputs the standard gtag.js snippet
 * exactly as Google requires for tag detection and verification.
 *
 * Usage: <?php require_once __DIR__ . '/includes/gtag.php'; ?>
 */
$_gaId = 'G-PCK6ZYFHQ0';
?>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= $_gaId ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?= $_gaId ?>');
</script>
