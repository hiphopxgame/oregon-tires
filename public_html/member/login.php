<?php
/**
 * Oregon Tires — Login Redirect
 *
 * Redirects to /members which handles login via the dashboard page.
 */

$redirect = '/members';
$params = [];
if (!empty($_GET['return'])) {
    $params['return'] = $_GET['return'];
}
if (!empty($_GET['lang'])) {
    $params['lang'] = $_GET['lang'];
}
if ($params) {
    $redirect .= '?' . http_build_query($params);
}
header('Location: ' . $redirect, true, 302);
exit;
