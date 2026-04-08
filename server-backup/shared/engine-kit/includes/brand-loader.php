<?php
/**
 * Brand Loader — compatibility shim for engine-kit.
 *
 * Provides global function wrappers that delegate to BrandEngine statics.
 * Existing code can `require_once` this file to get the same functions.
 *
 * Note: loadBrandConfig() and generateBrandCSS() are NOT included here
 * because they require PDO — they remain in hiphop.world's brand-loader.php.
 */

require_once __DIR__ . '/BrandEngine.php';

if (!function_exists('generateAutoBrand')) {
    function generateAutoBrand(string $siteName, string $domain): array {
        return BrandEngine::generateAutoBrand($siteName, $domain);
    }
}

if (!function_exists('hslToHex')) {
    function hslToHex(int $h, int $s, int $l): string {
        return BrandEngine::hslToHex($h, $s, $l);
    }
}

if (!function_exists('generateHSLPalette')) {
    function generateHSLPalette(int $hue, int $saturation, int $lightness): array {
        return BrandEngine::generateHSLPalette($hue, $saturation, $lightness);
    }
}

if (!function_exists('hexToRgb')) {
    function hexToRgb(string $hex): array {
        return BrandEngine::hexToRgb($hex);
    }
}

if (!function_exists('relativeLuminance')) {
    function relativeLuminance(string $hex): float {
        return BrandEngine::relativeLuminance($hex);
    }
}

if (!function_exists('contrastRatio')) {
    function contrastRatio(string $fg, string $bg): float {
        return BrandEngine::contrastRatio($fg, $bg);
    }
}

if (!function_exists('ensureContrast')) {
    function ensureContrast(int $hue, int $saturation, int $lightness, string $bgHex, float $minRatio = 4.5): string {
        return BrandEngine::ensureContrast($hue, $saturation, $lightness, $bgHex, $minRatio);
    }
}

if (!function_exists('generateBrandFontLink')) {
    function generateBrandFontLink(array $config): string {
        return BrandEngine::generateBrandFontLink($config);
    }
}

if (!function_exists('sanitizeCustomCSS')) {
    function sanitizeCustomCSS(string $css): string {
        return BrandEngine::sanitizeCustomCSS($css);
    }
}
