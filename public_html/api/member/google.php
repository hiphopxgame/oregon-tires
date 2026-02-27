<?php
/**
 * Oregon Tires — Google OAuth redirect wrapper
 * Member-kit login template links here; forwards to /api/auth/google.php
 */
header('Location: /api/auth/google.php?' . ($_SERVER['QUERY_STRING'] ?? ''));
exit;
