<?php
$secret = 'OT_SETUP_2026';
if (($_GET['key'] ?? '') !== $secret) { http_response_code(403); die('Forbidden'); }

$pubKey = file_get_contents('/var/cpanel/domain_keys/public/oregon.tires');
// Strip PEM headers and newlines to get raw base64
$raw = str_replace(['-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----', "\n", "\r"], '', $pubKey);
echo "DKIM_RECORD=v=DKIM1; k=rsa; p=$raw";
