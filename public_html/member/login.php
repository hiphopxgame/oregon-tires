<?php
/**
 * Oregon Tires — Login Redirect
 *
 * Redirects to /members which handles login via the dashboard page.
 */

$redirect = '/members';
if (!empty($_GET['return'])) {
    $redirect .= '?return=' . urlencode($_GET['return']);
}
header('Location: ' . $redirect, true, 302);
exit;
