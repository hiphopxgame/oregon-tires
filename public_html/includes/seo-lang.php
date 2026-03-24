<?php
/**
 * Server-side language detection for SEO meta tags.
 * Reads ?lang= from query string so crawlers see correct lang/title/description
 * without requiring JavaScript execution.
 */

function seoLang(): string {
    static $lang;
    if ($lang === null) {
        $lang = ($_GET['lang'] ?? '') === 'es' ? 'es' : 'en';
    }
    return $lang;
}

function seoMeta(string $en, string $es): string {
    return seoLang() === 'es' ? $es : $en;
}

function seoOgLocale(): string {
    return seoLang() === 'es' ? 'es_MX' : 'en_US';
}
