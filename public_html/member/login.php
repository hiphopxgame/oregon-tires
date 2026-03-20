<?php
// Redirect /member/login → /members
$params = [];
if (!empty($_GET['return'])) $params['return'] = $_GET['return'];
if (!empty($_GET['lang'])) $params['lang'] = $_GET['lang'];
header('Location: /members' . ($params ? '?' . http_build_query($params) : ''), true, 301);
exit;
