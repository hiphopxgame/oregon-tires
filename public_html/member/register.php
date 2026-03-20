<?php
// Redirect /member/register → /members?view=register
$params = ['view' => 'register'];
if (!empty($_GET['return'])) $params['return'] = $_GET['return'];
if (!empty($_GET['lang'])) $params['lang'] = $_GET['lang'];
header('Location: /members?' . http_build_query($params), true, 301);
exit;
