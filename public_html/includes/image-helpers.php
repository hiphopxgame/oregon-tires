<?php
/**
 * Image Optimization Helpers — Oregon Tires
 *
 * Provides responsive image rendering with WebP + AVIF support.
 */

/**
 * Generate a <picture> element with AVIF + WebP sources and img fallback.
 *
 * @param string $src    Image path (e.g., /assets/img/photo.jpg)
 * @param string $alt    Alt text
 * @param string $class  CSS classes for the <img> tag
 * @param int|null $width  Intrinsic width (for CLS prevention)
 * @param int|null $height Intrinsic height (for CLS prevention)
 * @param bool $lazy     Enable lazy loading (default: true)
 * @return string HTML <picture> element
 */
function responsiveImage(string $src, string $alt, string $class = '', ?int $width = null, ?int $height = null, bool $lazy = true): string
{
    $e = fn($s) => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

    $avifSrc = preg_replace('/\.(jpe?g|png)$/i', '.avif', $src);
    $webpSrc = preg_replace('/\.(jpe?g|png)$/i', '.webp', $src);

    $dims = '';
    if ($width !== null) $dims .= ' width="' . (int) $width . '"';
    if ($height !== null) $dims .= ' height="' . (int) $height . '"';

    $loading = $lazy ? ' loading="lazy" decoding="async"' : ' decoding="async"';

    $html = '<picture>';
    if ($avifSrc !== $src) $html .= '<source srcset="' . $e($avifSrc) . '" type="image/avif">';
    if ($webpSrc !== $src) $html .= '<source srcset="' . $e($webpSrc) . '" type="image/webp">';
    $html .= '<img src="' . $e($src) . '" alt="' . $e($alt) . '"'
           . ($class ? ' class="' . $e($class) . '"' : '')
           . $dims . $loading . '>';
    $html .= '</picture>';

    return $html;
}
