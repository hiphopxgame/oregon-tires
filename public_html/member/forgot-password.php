<?php
// Redirect /member/forgot-password → /members?view=forgot-password
$params = ['view' => 'forgot-password'];
if (!empty($_GET['lang'])) $params['lang'] = $_GET['lang'];
header('Location: /members?' . http_build_query($params), true, 301);
exit;
