-- Register satellite sites in engine_sites and enable core components
-- Run on: hiphopwo_rld database
-- Idempotent: uses INSERT IGNORE

-- Register all satellite sites
INSERT IGNORE INTO engine_sites (site_key, name, domain, site_type, status) VALUES
('oregontires', 'Oregon Tires Auto Care', 'oregon.tires', '1vsm', 'active'),
('1oh6events', '1oh6 Events', '1oh6.events', '1vsm', 'active'),
('fortune4kmedia', 'Fortune 4K Media', 'fortune4k.media', '1vsm', 'active'),
('mudpodcast', 'Mud Podcast', 'mudpodcast.com', '1vsm', 'active'),
('nisatax', 'Nisa Tax', 'nisa.tax', '1vsm', 'active'),
('tre5magic', 'Tre5 Magic', 'tre5magic.com', '1vsm', 'active'),
('mentalstamina', 'Mental Stamina', 'mentalstamina.world', '1vsm', 'active'),
('gremgoyles', 'Gremgoyles', 'gremgoyles.com', '1vsm', 'active'),
('iwittyparty', 'iWitty Party', 'iwitty.party', '1vsm', 'active'),
('mozaycalloway', 'Mozay Calloway', 'mozaycalloway.com', '1vsm', 'active'),
('pdxgives', 'PDX Gives', 'pdx.gives', '1vsm', 'active'),
('margaritaparty', 'Margarita Party', 'margarita.party', '1vsm', 'active'),
('obbium', 'Obbium', 'obbium.com', '1vsm', 'active'),
('colombianbakery', 'The Colombian Bakery', 'thecolombianbakery.com', '1vsm', 'active'),
('absurdlywell', 'Absurdly Well', 'absurdlywell.shop', '1vsm', 'active'),
('howlywoods', 'Howlywoods', 'howlywoods.com', '1vsm', 'active'),
('intergalactic', 'Intergalactic Charity', 'intergalactic.charity', '1vsm', 'active');

-- Enable core components for all new sites
INSERT IGNORE INTO engine_site_components (site_key, component_slug, enabled)
SELECT s.site_key, c.slug, 1
FROM engine_sites s
CROSS JOIN engine_components c
WHERE c.is_core = 1
  AND s.site_key NOT IN (SELECT DISTINCT site_key FROM engine_site_components);
