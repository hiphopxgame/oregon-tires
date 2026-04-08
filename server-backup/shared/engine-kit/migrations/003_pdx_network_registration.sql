-- =============================================
-- 003: PDX Network Registration
-- Expand ENUMs to support 'pdx' site_type and register all 5 PDX sites
-- =============================================

-- Expand site_type ENUM to include 'pdx'
ALTER TABLE engine_sites MODIFY COLUMN site_type ENUM('hiphop','1vsm','pdx','client') NOT NULL;

-- Expand visual_network ENUM to include 'pdx'
ALTER TABLE engine_sites MODIFY COLUMN visual_network ENUM('hiphop','pdx','independent') DEFAULT 'independent';

-- Expand footer_badge ENUM to include 'pdx'
ALTER TABLE engine_sites MODIFY COLUMN footer_badge ENUM('1vsm','hiphop','pdx','none') DEFAULT '1vsm';

-- Upsert all 5 PDX sites (site_keys match existing bootstrap configs)
INSERT INTO engine_sites (site_key, name, domain, site_type, directory_name, visual_network, footer_badge, status, branding)
VALUES
  ('pdxbusiness',   'PDX Business',   'pdx.business',   'pdx', '---pdx.business',   'pdx', 'pdx', 'active',      JSON_OBJECT('_hue',140,'_saturation',65,'_lightness',50,'primary_color','#15803D','font_heading','DM Sans','font_body','Inter')),
  ('pdxdirectory',  'PDX Directory',  'pdx.directory',  'pdx', '---pdx.directory',  'pdx', 'pdx', 'development', JSON_OBJECT('_hue',210,'_saturation',65,'_lightness',50,'primary_color','#2563EB','font_heading','DM Sans','font_body','Inter')),
  ('pdxmarketing',  'PDX Marketing',  'pdx.marketing',  'pdx', '---pdx.marketing',  'pdx', 'pdx', 'development', JSON_OBJECT('_hue',0,  '_saturation',65,'_lightness',50,'primary_color','#DC2626','font_heading','DM Sans','font_body','Inter')),
  ('pdxgives',      'PDX Gives',      'pdx.gives',      'pdx', '---pdx.gives',      'pdx', 'pdx', 'active',      JSON_OBJECT('_hue',50, '_saturation',65,'_lightness',50,'primary_color','#CA8A04','font_heading','DM Sans','font_body','Inter')),
  ('pdxfoundation', 'PDX Foundation', 'pdx.foundation', 'pdx', '---pdx.foundation', 'pdx', 'pdx', 'development', JSON_OBJECT('_hue',25, '_saturation',65,'_lightness',50,'primary_color','#EA580C','font_heading','DM Sans','font_body','Inter'))
ON DUPLICATE KEY UPDATE
  site_type      = VALUES(site_type),
  visual_network = VALUES(visual_network),
  footer_badge   = VALUES(footer_badge),
  branding       = VALUES(branding);
