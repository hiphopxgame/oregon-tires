<?php
// Redirect /member/reset-password → /members?view=reset-password
$params = ['view' => 'reset-password'];
if (!empty($_GET['token'])) $params['token'] = $_GET['token'];
if (!empty($_GET['lang'])) $params['lang'] = $_GET['lang'];
header('Location: /members?' . http_build_query($params), true, 301);
exit;
