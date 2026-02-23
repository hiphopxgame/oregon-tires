-- ============================================================================
-- Oregon Tires + 1OH6 Events — 1vsM Network Integration
-- Run against: hiphopwo_rld_system
-- ============================================================================

-- 1A. Update Oregon Tires branding
UPDATE engine_sites SET
    branding = JSON_SET(
        COALESCE(branding, '{}'),
        '$.primary_color', '#16a34a',
        '$.bg_color', '#ffffff',
        '$.text_color', '#1e293b',
        '$.font_heading', 'Inter',
        '$.font_body', 'Inter'
    ),
    google_analytics_id = 'G-CHYMTNB6LH'
WHERE site_key = 'oregontires';

-- 1A. Update 1OH6 Events branding
UPDATE engine_sites SET
    branding = JSON_SET(
        COALESCE(branding, '{}'),
        '$.primary_color', '#f59e0b',
        '$.bg_color', '#0f172a',
        '$.text_color', '#f1f5f9',
        '$.font_heading', 'Montserrat',
        '$.font_body', 'Open Sans'
    )
WHERE site_key = '1oh6events';

-- 1B. Activate core components for both sites
INSERT IGNORE INTO engine_site_components (site_key, component_slug, enabled, activated_at)
SELECT s.site_key, c.slug, 1, NOW()
FROM engine_sites s
CROSS JOIN engine_components c
WHERE c.is_core = 1
  AND s.site_key IN ('oregontires', '1oh6events');

-- 1C. Configure analytics for Oregon Tires
UPDATE engine_site_components
SET config = '{"ga4_measurement_id":"G-CHYMTNB6LH"}'
WHERE site_key = 'oregontires' AND component_slug = 'analytics';

-- ============================================================================
-- Verification queries (run after migration)
-- ============================================================================
-- SELECT site_key, domain, JSON_EXTRACT(branding, '$.primary_color'), google_analytics_id
-- FROM engine_sites WHERE site_key IN ('oregontires', '1oh6events');
--
-- SELECT site_key, component_slug, enabled
-- FROM engine_site_components WHERE site_key IN ('oregontires', '1oh6events');
