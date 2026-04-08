-- =============================================
-- 003b: PDX Network OAuth Client
-- Register shared OAuth client for cross-site PDX SSO
-- =============================================

INSERT INTO oauth_clients (id, secret, name, redirect_uris, created_at)
VALUES (
  'pdx-network',
  '3288f97c822650fc6bfb3b61e60cdcc8c95c6950e3a5eae190b624b2affaef4c',
  'PDX Network',
  JSON_ARRAY(
    'https://pdx.business/sso',
    'https://pdx.business/api/member/sso-callback.php',
    'https://pdx.directory/sso',
    'https://pdx.directory/api/member/sso-callback.php',
    'https://pdx.marketing/sso',
    'https://pdx.marketing/api/member/sso-callback.php',
    'https://pdx.gives/sso',
    'https://pdx.gives/api/member/sso-callback.php',
    'https://pdx.foundation/sso',
    'https://pdx.foundation/api/member/sso-callback.php'
  ),
  NOW()
)
ON DUPLICATE KEY UPDATE
  name          = VALUES(name),
  redirect_uris = VALUES(redirect_uris);
