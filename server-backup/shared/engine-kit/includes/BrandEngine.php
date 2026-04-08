<?php
/**
 * BrandEngine — Core brand computation for the 1vsM network.
 *
 * All pure (DB-free) brand logic extracted from:
 *   - hiphop.world/includes/brand-loader.php (color math, auto-brand, fonts)
 *   - hiphop.world/includes/BrandPackGenerator.php (SVG/image color extraction, token generation)
 *
 * Every method is static. No PDO dependency. Safe to use from scaffolder, CLI, or web.
 */
class BrandEngine
{
    // ─── Auto-Branding ───────────────────────────────────────────

    /**
     * Generate automatic brand identity from a site name and domain.
     * Deterministic hashing produces consistent colors per domain.
     */
    public static function generateAutoBrand(string $siteName, string $domain): array
    {
        $seed = crc32($domain);

        // Hue 0-360, avoid 35-60 (HHW gold)
        $hue = $seed % 360;
        if ($hue >= 35 && $hue <= 60) {
            $hue = ($hue + 180) % 360;
        }

        $sat = 55 + ($seed % 21);        // 55-75
        $lit = 45 + (($seed >> 8) % 11); // 45-55

        $bgHex = '#030712';

        $primary = self::ensureContrast($hue, $sat, $lit, $bgHex, 4.5);
        $accentHue = ($hue + 30) % 360;
        $accent = self::ensureContrast($accentHue, $sat + 5, $lit + 10, $bgHex, 3.0);
        $secondary = self::hslToHex($hue, $sat - 20, 10);

        // Monogram from first 2 words
        $words = preg_split('/[\s\-_.]+/', trim($siteName));
        $monogram = '';
        foreach (array_slice($words, 0, 2) as $w) {
            $monogram .= strtoupper(mb_substr($w, 0, 1));
        }
        if (strlen($monogram) < 2 && !empty($words[0])) {
            $monogram = strtoupper(mb_substr($words[0], 0, 2));
        }

        // Curated font pairs
        $fontPairs = [
            ['heading' => 'Inter',        'body' => 'Roboto',        'import' => 'Inter:wght@400;600;700|Roboto:wght@300;400;500'],
            ['heading' => 'Poppins',      'body' => 'Open Sans',     'import' => 'Poppins:wght@400;600;700|Open+Sans:wght@300;400;500'],
            ['heading' => 'Montserrat',   'body' => 'Lato',          'import' => 'Montserrat:wght@400;600;700|Lato:wght@300;400;700'],
            ['heading' => 'Raleway',      'body' => 'Source Sans 3', 'import' => 'Raleway:wght@400;600;700|Source+Sans+3:wght@300;400;500'],
            ['heading' => 'DM Sans',      'body' => 'Inter',         'import' => 'DM+Sans:wght@400;500;700|Inter:wght@300;400;500'],
            ['heading' => 'Space Grotesk', 'body' => 'Work Sans',    'import' => 'Space+Grotesk:wght@400;500;700|Work+Sans:wght@300;400;500'],
        ];
        $pair = $fontPairs[$seed % count($fontPairs)];

        return [
            'primary_color'       => $primary,
            'secondary_color'     => $secondary,
            'accent_color'        => $accent,
            'success_color'       => '#4CAF50',
            'warning_color'       => '#FF9800',
            'error_color'         => '#F44336',
            'bg_color'            => '#030712',
            'surface_color'       => '#111827',
            'text_primary'        => '#FFFFFF',
            'text_secondary'      => '#9CA3AF',
            'font_heading'        => $pair['heading'],
            'font_heading_weight' => '700',
            'font_body'           => $pair['body'],
            'font_body_weight'    => '400',
            'font_mono'           => 'JetBrains Mono',
            'google_fonts_import' => $pair['import'],
            'border_radius'       => '12px',
            'card_radius'         => '16px',
            'button_radius'       => '12px',
            'spacing_unit'        => '4px',
            'max_width'           => '1400px',
            'grid_columns'        => 12,
            'custom_css'          => '',
            '_monogram'           => $monogram,
            '_auto_generated'     => true,
            '_hue'                => $hue,
            '_saturation'         => $sat,
            '_lightness'          => $lit,
            '_palette'            => self::generateHSLPalette($hue, $sat, $lit),
        ];
    }

    // ─── HSL Color Math ──────────────────────────────────────────

    /**
     * Convert HSL to hex color string.
     */
    public static function hslToHex(int $h, int $s, int $l): string
    {
        $s /= 100;
        $l /= 100;

        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;

        if ($h < 60)       { $r = $c; $g = $x; $b = 0; }
        elseif ($h < 120)  { $r = $x; $g = $c; $b = 0; }
        elseif ($h < 180)  { $r = 0;  $g = $c; $b = $x; }
        elseif ($h < 240)  { $r = 0;  $g = $x; $b = $c; }
        elseif ($h < 300)  { $r = $x; $g = 0;  $b = $c; }
        else               { $r = $c; $g = 0;  $b = $x; }

        $r = (int) round(($r + $m) * 255);
        $g = (int) round(($g + $m) * 255);
        $b = (int) round(($b + $m) * 255);

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }

    /**
     * Generate HSL-based palette: neutral (tinted grays), brand (5 stops), semantic.
     */
    public static function generateHSLPalette(int $hue, int $saturation, int $lightness): array
    {
        $neutralStops = [
            '50'  => 95, '100' => 90, '200' => 80, '300' => 70, '400' => 50,
            '500' => 30, '600' => 20, '700' => 15, '800' => 10, '900' => 7, '950' => 5,
        ];
        $neutral = [];
        foreach ($neutralStops as $stop => $l) {
            $neutral[$stop] = "hsl({$hue}, 10%, {$l}%)";
        }

        $brandStops = [
            '300' => min($lightness + 20, 85),
            '400' => min($lightness + 10, 75),
            '500' => $lightness,
            '600' => max($lightness - 10, 15),
            '700' => max($lightness - 20, 10),
        ];
        $brand = [];
        foreach ($brandStops as $stop => $l) {
            $brand[$stop] = "hsl({$hue}, {$saturation}%, {$l}%)";
        }

        $semantic = [
            'success' => 'hsl(130, 60%, 45%)',
            'error'   => 'hsl(0, 75%, 55%)',
            'warning' => 'hsl(45, 90%, 52%)',
            'info'    => 'hsl(210, 65%, 58%)',
        ];

        return ['neutral' => $neutral, 'brand' => $brand, 'semantic' => $semantic];
    }

    /**
     * Convert hex color to RGB array.
     */
    public static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Calculate relative luminance per WCAG 2.0.
     */
    public static function relativeLuminance(string $hex): float
    {
        $rgb = self::hexToRgb($hex);
        $channels = [];
        foreach (['r', 'g', 'b'] as $ch) {
            $val = $rgb[$ch] / 255;
            $channels[$ch] = ($val <= 0.03928) ? $val / 12.92 : pow(($val + 0.055) / 1.055, 2.4);
        }
        return 0.2126 * $channels['r'] + 0.7152 * $channels['g'] + 0.0722 * $channels['b'];
    }

    /**
     * Calculate WCAG 2.0 contrast ratio between two colors.
     */
    public static function contrastRatio(string $fg, string $bg): float
    {
        $l1 = self::relativeLuminance($fg);
        $l2 = self::relativeLuminance($bg);
        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);
        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * Ensure a color meets WCAG contrast against a background.
     * Increases lightness until the ratio is met.
     */
    public static function ensureContrast(int $hue, int $saturation, int $lightness, string $bgHex, float $minRatio = 4.5): string
    {
        $hex = self::hslToHex($hue, $saturation, $lightness);
        if (self::contrastRatio($hex, $bgHex) >= $minRatio) {
            return $hex;
        }

        for ($l = $lightness + 5; $l <= 90; $l += 5) {
            $hex = self::hslToHex($hue, $saturation, $l);
            if (self::contrastRatio($hex, $bgHex) >= $minRatio) {
                return $hex;
            }
        }

        return self::hslToHex($hue, max($saturation - 20, 10), 85);
    }

    // ─── Font Helpers ────────────────────────────────────────────

    /**
     * Generate Google Fonts <link> tags from brand config.
     */
    public static function generateBrandFontLink(array $config): string
    {
        $import = trim($config['google_fonts_import'] ?? '');
        if ($import === '') return '';

        $url = 'https://fonts.googleapis.com/css2?family='
             . str_replace('|', '&family=', htmlspecialchars($import, ENT_QUOTES, 'UTF-8'))
             . '&display=swap';

        return "<link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">\n"
             . "<link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>\n"
             . "<link href=\"{$url}\" rel=\"stylesheet\">\n";
    }

    /**
     * Build Google Fonts import URL (no HTML, just the URL).
     */
    public static function buildFontImportUrl(string $googleFontsImport): string
    {
        $import = trim($googleFontsImport);
        if ($import === '') return '';

        return 'https://fonts.googleapis.com/css2?family='
             . str_replace('|', '&family=', $import)
             . '&display=swap';
    }

    // ─── CSS Sanitization ────────────────────────────────────────

    /**
     * Sanitize user-supplied custom CSS (strips XSS vectors).
     */
    public static function sanitizeCustomCSS(string $css): string
    {
        $dangerous = [
            '/javascript\s*:/i',
            '/expression\s*\(/i',
            '/@import/i',
            '/behavior\s*:/i',
            '/-moz-binding/i',
            '/<\/?\s*style/i',
            '/<\/?\s*script/i',
            '/url\s*\(\s*["\']?\s*(?!https?:\/\/)/i',
            '/on\w+\s*=/i',
        ];
        foreach ($dangerous as $pattern) {
            $css = preg_replace($pattern, '/* blocked */', $css);
        }
        return $css;
    }

    // ─── SVG Color Extraction ────────────────────────────────────

    /**
     * Extract colors from SVG content using DOMDocument.
     * Returns frequency map: hex => count.
     */
    public static function extractSvgColors(string $svgContent): array
    {
        $colors = [];

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadXML($svgContent);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $colorAttrs = ['fill', 'stroke', 'stop-color', 'color'];
        $allElements = $xpath->query('//*');

        foreach ($allElements as $el) {
            foreach ($colorAttrs as $attr) {
                $val = $el->getAttribute($attr);
                if ($val && $val !== 'none' && $val !== 'currentColor' && $val !== 'inherit') {
                    $hex = self::normalizeColor($val);
                    if ($hex) {
                        $colors[$hex] = ($colors[$hex] ?? 0) + 1;
                    }
                }
            }

            $style = $el->getAttribute('style');
            if ($style) {
                if (preg_match_all('/(fill|stroke|stop-color|color)\s*:\s*([^;]+)/i', $style, $matches)) {
                    foreach ($matches[2] as $val) {
                        $hex = self::normalizeColor(trim($val));
                        if ($hex) {
                            $colors[$hex] = ($colors[$hex] ?? 0) + 1;
                        }
                    }
                }
            }
        }

        arsort($colors);
        return $colors;
    }

    /**
     * Normalize a color value to uppercase hex.
     */
    public static function normalizeColor(string $color): ?string
    {
        $color = trim($color);

        if (preg_match('/^#([0-9a-fA-F]{3}){1,2}$/', $color)) {
            if (strlen($color) === 4) {
                $color = '#' . $color[1] . $color[1] . $color[2] . $color[2] . $color[3] . $color[3];
            }
            return strtoupper($color);
        }

        if (preg_match('/rgb\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)/', $color, $m)) {
            return sprintf('#%02X%02X%02X', (int) $m[1], (int) $m[2], (int) $m[3]);
        }

        $named = [
            'black' => '#000000', 'white' => '#FFFFFF', 'red' => '#FF0000',
            'green' => '#008000', 'blue' => '#0000FF', 'yellow' => '#FFFF00',
            'orange' => '#FFA500', 'purple' => '#800080', 'gray' => '#808080',
            'grey' => '#808080', 'gold' => '#FFD700', 'silver' => '#C0C0C0',
        ];
        $lower = strtolower($color);
        if (isset($named[$lower])) {
            return $named[$lower];
        }

        return null;
    }

    // ─── Image Color Extraction ──────────────────────────────────

    /**
     * Extract dominant colors from a raster image by pixel sampling.
     * Returns frequency map: hex => count.
     */
    public static function extractImageColors(string $imagePath): array
    {
        $img = self::loadGdImage($imagePath);
        if (!$img) return [];

        $width = imagesx($img);
        $height = imagesy($img);
        $totalPixels = $width * $height;
        $step = max(1, (int) sqrt($totalPixels / 10000));

        $colorCounts = [];
        for ($y = 0; $y < $height; $y += $step) {
            for ($x = 0; $x < $width; $x += $step) {
                $rgba = imagecolorat($img, $x, $y);
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;
                $a = ($rgba >> 24) & 0x7F;

                if ($a > 100) continue;

                $r = min(255, max(0, (int) (round($r / 8) * 8)));
                $g = min(255, max(0, (int) (round($g / 8) * 8)));
                $b = min(255, max(0, (int) (round($b / 8) * 8)));

                $hex = sprintf('#%02X%02X%02X', $r, $g, $b);
                $colorCounts[$hex] = ($colorCounts[$hex] ?? 0) + 1;
            }
        }

        imagedestroy($img);
        arsort($colorCounts);
        return $colorCounts;
    }

    /**
     * Load a raster image via GD.
     */
    public static function loadGdImage(string $path): ?\GdImage
    {
        if (!function_exists('imagecreatetruecolor')) return null;

        $type = @exif_imagetype($path);
        if ($type === false) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $type = match ($ext) {
                'png' => IMAGETYPE_PNG,
                'jpg', 'jpeg' => IMAGETYPE_JPEG,
                'webp' => IMAGETYPE_WEBP,
                'gif' => IMAGETYPE_GIF,
                default => false,
            };
        }

        $img = match ($type) {
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            IMAGETYPE_GIF => @imagecreatefromgif($path),
            default => false,
        };

        return $img ?: null;
    }

    // ─── Color Mapping ───────────────────────────────────────────

    /**
     * Map extracted colors to a brand palette (primary, secondary, accent).
     * Filters out near-black/near-white, picks by frequency.
     */
    public static function mapColorsToPalette(array $extractedColors, array $overrides = []): array
    {
        $significantColors = [];
        foreach ($extractedColors as $hex => $count) {
            $rgb = self::hexToRgb($hex);
            $brightness = ($rgb['r'] * 299 + $rgb['g'] * 587 + $rgb['b'] * 114) / 1000;
            if ($brightness > 30 && $brightness < 230) {
                $significantColors[$hex] = $count;
            }
        }

        $colorKeys = array_keys($significantColors);

        $primary = $overrides['primary_color'] ?? ($colorKeys[0] ?? '#D4AF37');
        $accent = $overrides['accent_color'] ?? ($colorKeys[1] ?? self::hslToHex((crc32($primary) % 360 + 30) % 360, 65, 55));
        $secondary = $overrides['secondary_color'] ?? '#0A0A0A';

        return [
            'primary' => $primary,
            'secondary' => $secondary,
            'accent' => $accent,
            'extracted' => array_slice($colorKeys, 0, 8),
        ];
    }

    /**
     * Build a full brand config from a color palette + site metadata.
     */
    public static function buildBrandConfig(array $palette, string $siteName, string $domain): array
    {
        $autoBrand = self::generateAutoBrand($siteName, $domain);

        $config = $autoBrand;
        $config['primary_color'] = $palette['primary'];
        $config['accent_color'] = $palette['accent'];
        $config['secondary_color'] = $palette['secondary'];

        return $config;
    }

    // ─── Token/CSS Generation ────────────────────────────────────

    /**
     * Generate tokens.css content from a brand config.
     */
    public static function generateTokensCSS(array $config): string
    {
        $siteKey = $config['site_key'] ?? $config['_site_key'] ?? 'site';
        $css = "/* Auto-generated brand tokens for {$siteKey} */\n";
        $css .= ":root {\n";
        $css .= "  --brand-primary: {$config['primary_color']};\n";
        $css .= "  --brand-secondary: {$config['secondary_color']};\n";
        $css .= "  --brand-accent: {$config['accent_color']};\n";
        $css .= "  --brand-success: {$config['success_color']};\n";
        $css .= "  --brand-warning: {$config['warning_color']};\n";
        $css .= "  --brand-error: {$config['error_color']};\n";
        $css .= "  --brand-bg: {$config['bg_color']};\n";
        $css .= "  --brand-surface: {$config['surface_color']};\n";
        $css .= "  --brand-text: {$config['text_primary']};\n";
        $css .= "  --brand-text-muted: {$config['text_secondary']};\n";
        $css .= "  --brand-font-heading: '{$config['font_heading']}', sans-serif;\n";
        $css .= "  --brand-font-body: '{$config['font_body']}', sans-serif;\n";
        $css .= "  --brand-radius: {$config['border_radius']};\n";
        $css .= "  --brand-card-radius: {$config['card_radius']};\n";
        $css .= "  --brand-btn-radius: {$config['button_radius']};\n";

        // Light-mode overrides (scaffold default)
        $css .= "  /* Light mode colors */\n";
        $css .= "  --color-primary: {$config['primary_color']};\n";
        $css .= "  --color-primary-light: hsl({$config['_hue']}, {$config['_saturation']}%, 95%);\n";
        $css .= "  --color-primary-dark: hsl({$config['_hue']}, {$config['_saturation']}%, " . max(($config['_lightness'] ?? 50) - 15, 10) . "%);\n";
        $css .= "  --color-text: #1a1a2e;\n";
        $css .= "  --color-text-muted: #64748b;\n";
        $css .= "  --color-bg: #ffffff;\n";
        $css .= "  --color-bg-alt: #f8fafc;\n";
        $css .= "  --color-border: #e2e8f0;\n";
        $css .= "  --color-success: {$config['success_color']};\n";
        $css .= "  --color-error: {$config['error_color']};\n";
        $css .= "  --nav-height: 64px;\n";
        $css .= "  --container-max: 1200px;\n";
        $css .= "  --transition: 0.2s ease;\n";
        $css .= "  /* Semantic UI tokens */\n";
        $css .= "  --color-on-primary: #fff;\n";
        $css .= "  --color-on-dark: #fff;\n";
        $css .= "  --color-shadow: rgba(0,0,0,0.08);\n";
        $css .= "  --color-success-bg: #ecfdf5;\n";
        $css .= "  --color-success-text: #065f46;\n";
        $css .= "  --color-error-bg: #fef2f2;\n";
        $css .= "  --color-error-text: #991b1b;\n";
        $css .= "  --color-footer-muted: rgba(255,255,255,0.6);\n";
        $css .= "  --color-footer-link: rgba(255,255,255,0.7);\n";
        $css .= "  --color-footer-border: rgba(255,255,255,0.1);\n";
        $css .= "}\n";

        return $css;
    }

    /**
     * Generate buttons.css content from a brand config.
     */
    public static function generateButtonsCSS(array $config): string
    {
        $primary = $config['primary_color'] ?? '#D4AF37';
        $accent = $config['accent_color'] ?? '#FFD700';
        $radius = $config['button_radius'] ?? '12px';
        $siteKey = $config['site_key'] ?? $config['_site_key'] ?? 'site';

        return <<<CSS
/* Auto-generated button styles for {$siteKey} */
.btn-brand {
  background: {$primary};
  color: #fff;
  border: none;
  border-radius: {$radius};
  padding: 0.625rem 1.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s, transform 0.1s;
}
.btn-brand:hover { opacity: 0.9; }
.btn-brand:active { transform: scale(0.98); }
.btn-brand-outline {
  background: transparent;
  color: {$primary};
  border: 2px solid {$primary};
  border-radius: {$radius};
  padding: 0.5rem 1.375rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s, color 0.2s;
}
.btn-brand-outline:hover { background: {$primary}; color: #fff; }
.btn-brand-accent {
  background: {$accent};
  color: #000;
  border: none;
  border-radius: {$radius};
  padding: 0.625rem 1.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
}
.btn-brand-accent:hover { opacity: 0.9; }
CSS;
    }

    // ─── Favicon Generation ──────────────────────────────────────

    /**
     * Generate PNG favicons from SVG content.
     * Tries Imagick first, falls back to monogram via GD.
     */
    public static function generateFavicons(string $svgContent, string $outputDir, string $primaryColor, string $monogram): array
    {
        $results = [];

        if (extension_loaded('imagick')) {
            foreach ([16, 32] as $size) {
                $filename = "favicon-{$size}.png";
                try {
                    $im = new \Imagick();
                    $im->setResolution(72, 72);
                    $im->readImageBlob($svgContent);
                    $im->setImageFormat('png');
                    $im->resizeImage($size, $size, \Imagick::FILTER_LANCZOS, 1);
                    $im->writeImage($outputDir . '/' . $filename);
                    $im->clear();
                    $im->destroy();
                    $results[] = $filename;
                } catch (\Throwable $e) {
                    self::generateMonogramFavicon($outputDir, $primaryColor, $monogram, $size);
                    $results[] = $filename;
                }
            }
        } else {
            foreach ([16, 32] as $size) {
                $filename = "favicon-{$size}.png";
                self::generateMonogramFavicon($outputDir, $primaryColor, $monogram, $size);
                $results[] = $filename;
            }
        }

        return $results;
    }

    /**
     * Generate PNG favicons from a raster GD image.
     */
    public static function generateFaviconsFromImage(\GdImage $source, string $outputDir): array
    {
        $results = [];
        $srcW = imagesx($source);
        $srcH = imagesy($source);

        foreach ([16, 32] as $size) {
            $filename = "favicon-{$size}.png";
            $dest = imagecreatetruecolor($size, $size);
            imagealphablending($dest, false);
            imagesavealpha($dest, true);
            $transparent = imagecolorallocatealpha($dest, 0, 0, 0, 127);
            imagefill($dest, 0, 0, $transparent);
            imagecopyresampled($dest, $source, 0, 0, 0, 0, $size, $size, $srcW, $srcH);
            imagepng($dest, $outputDir . '/' . $filename);
            imagedestroy($dest);
            $results[] = $filename;
        }

        return $results;
    }

    /**
     * Generate a monogram-based PNG favicon via GD.
     */
    public static function generateMonogramFavicon(string $outputDir, string $primaryColor, string $monogram, int $size = 32): void
    {
        if (!function_exists('imagecreatetruecolor')) return;

        $img = imagecreatetruecolor($size, $size);
        $rgb = self::hexToRgb($primaryColor);
        $bgColor = imagecolorallocate($img, $rgb['r'], $rgb['g'], $rgb['b']);
        $textColor = imagecolorallocate($img, 255, 255, 255);

        imagefill($img, 0, 0, $bgColor);

        $fontSize = (int) ($size * 0.4);
        $font = $fontSize > 3 ? 5 : 3;
        $textWidth = imagefontwidth($font) * strlen($monogram);
        $textHeight = imagefontheight($font);
        $x = (int) (($size - $textWidth) / 2);
        $y = (int) (($size - $textHeight) / 2);
        imagestring($img, $font, $x, $y, $monogram, $textColor);

        $filename = "favicon-{$size}.png";
        imagepng($img, $outputDir . '/' . $filename);
        imagedestroy($img);
    }

    // ─── Monogram Helper ─────────────────────────────────────────

    /**
     * Generate 2-char monogram from a site name.
     */
    public static function generateMonogram(string $siteName): string
    {
        $words = preg_split('/[\s\-_.]+/', trim($siteName));
        $monogram = '';
        foreach (array_slice($words, 0, 2) as $w) {
            $monogram .= strtoupper(mb_substr($w, 0, 1));
        }
        if (strlen($monogram) < 2 && !empty($words[0])) {
            $monogram = strtoupper(mb_substr($words[0], 0, 2));
        }
        return $monogram;
    }
}
