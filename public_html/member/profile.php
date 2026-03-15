<?php
/**
 * Oregon Tires — Profile Redirect
 *
 * Catches member-kit default redirect to /member/profile
 * and sends to the members dashboard profile tab.
 */
header('Location: /members?tab=profile', true, 302);
exit;
