<?php
/**
 * Network Constants — Shared across all 1vsM network sites
 *
 * Extracted from hiphop.world/includes/constants.php so any site
 * can reference network data without importing hiphop.world's codebase.
 */

/** The hub domain — authentication, assets, main API */
if (!defined('HH_HUB')) {
    define('HH_HUB', 'https://hiphop.world');
}

/**
 * All active SSO/CORS domains as full origins (scheme + host).
 */
if (!defined('HH_DOMAINS')) {
    define('HH_DOMAINS', [
        'https://hiphop.world',
        'https://hiphop.id',
        'https://hiphop.chat',
        'https://hiphop.cash',
        'https://hiphop.cards',
        'https://hiphop.land',
        'https://hiphop.fund',
        'https://hiphop.clothing',
        'https://hiphop.shoes',
        'https://hiphop.audio',
        'https://hiphop.movie',
        'https://hiphop.tours',
        'https://hiphop.directory',
        'https://hiphop.computer',
        'https://hiphop.poker',
        'https://hiphop.bingo',
        'https://hiphop.forum',
        'https://hiphop.community',
        'https://hiphop.network',
        'https://hiphop.university',
        'https://hiphop.degree',
        'https://hiphop.charity',
        'https://hiphop.social',
        'https://hiphop.army',
        'https://hiphop.marketing',
        'https://qr.school',
        'https://public.events',
        'https://portland.events',
        'https://1vsm.com',
        'https://oregon.tires',
        'https://1oh6.events',
    ]);
}

/**
 * Network sites for universal nav header.
 * Key = short identifier for active-state detection.
 */
if (!defined('HH_NAV_SITES')) {
    define('HH_NAV_SITES', [
        'audio'     => ['label' => 'Audio',     'domain' => 'hiphop.audio',     'icon' => 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3'],
        'directory' => ['label' => 'Directory', 'domain' => 'hiphop.directory', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
        'chat'      => ['label' => 'Chat',      'domain' => 'hiphop.chat',      'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
        'community' => ['label' => 'Community', 'domain' => 'hiphop.community', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
    ]);
}

/** Network nav item (right side, before credits) */
if (!defined('HH_NAV_NETWORK')) {
    define('HH_NAV_NETWORK', [
        'label' => 'Network',
        'domain' => 'hiphop.network',
        'icon' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
    ]);
}

/**
 * Domain colors for footer/branding
 */
if (!defined('HH_DOMAIN_COLORS')) {
    define('HH_DOMAIN_COLORS', [
        'chat'       => '#60A5FA',
        'cash'       => '#FFD700',
        'cards'      => '#FBBF24',
        'community'  => '#34D399',
        'computer'   => '#A78BFA',
        'charity'    => '#FB7185',
        'audio'      => '#ff5500',
        'movie'      => '#FF0000',
        'forum'      => '#5865F2',
        'social'     => '#1DA1F2',
        'tours'      => '#F59E0B',
        'directory'  => '#10B981',
        'network'    => '#F59E0B',
        'world'      => '#FFD700',
        'id'         => '#FFD700',
        'land'       => '#34D399',
        'fund'       => '#34D399',
        'clothing'   => '#A78BFA',
        'shoes'      => '#60A5FA',
        'poker'      => '#FB7185',
        'bingo'      => '#FBBF24',
        'university' => '#60A5FA',
        'degree'     => '#10B981',
        'army'       => '#34D399',
        'marketing'  => '#FFD700',
    ]);
}

/**
 * Footer network groups (7 columns)
 */
if (!defined('HH_FOOTER_GROUPS')) {
    define('HH_FOOTER_GROUPS', [
        'The 6 C\'s' => [
            'chat'      => 'Chat',
            'cash'      => 'Cash',
            'cards'     => 'Cards',
            'community' => 'Community',
            'computer'  => 'Computer',
            'charity'   => 'Charity',
        ],
        'Create' => [
            'audio'     => 'Audio',
            'movie'     => 'Movie',
            'tours'     => 'Tours',
            'directory' => 'Directory',
        ],
        'Connect' => [
            'forum'     => 'Forum',
            'social'    => 'Social',
            'army'      => 'Army',
        ],
        'Identity' => [
            'world'     => 'World',
            'id'        => 'ID',
            'land'      => 'Land',
            'fund'      => 'Fund',
        ],
        'Grow' => [
            'marketing' => 'Marketing',
            'network'   => 'Network',
            'university'=> 'University',
            'degree'    => 'Degree',
        ],
        'Lifestyle' => [
            'clothing'  => 'Clothing',
            'shoes'     => 'Shoes',
            'poker'     => 'Poker',
            'bingo'     => 'Bingo',
        ],
    ]);
}

/** Active sites (have real content — others render as "Coming Soon") */
if (!defined('HH_ACTIVE_SITES')) {
    define('HH_ACTIVE_SITES', [
        'world', 'id', 'chat', 'cash', 'cards', 'land', 'audio', 'movie',
        'tours', 'directory', 'computer', 'army', 'charity', 'forum', 'social',
        'network', 'university', 'degree', 'community', 'marketing',
    ]);
}
