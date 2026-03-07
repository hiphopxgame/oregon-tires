<?php
/**
 * Oregon Tires — SEO Head Partial
 * Include in <head> of all pages for verification tags and defaults.
 * Requires: includes/seo-config.php loaded first.
 */
$_seoConfig = getBusinessConfig();

// Search platform verification
if (!empty($_seoConfig['verification']['google'])) {
    echo '<meta name="google-site-verification" content="' . htmlspecialchars($_seoConfig['verification']['google']) . '">' . "\n";
}
if (!empty($_seoConfig['verification']['bing'])) {
    echo '<meta name="msvalidate.01" content="' . htmlspecialchars($_seoConfig['verification']['bing']) . '">' . "\n";
}
