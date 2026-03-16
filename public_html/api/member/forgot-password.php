<?php
/**
 * Oregon Tires — Forgot Password
 * POST /api/member/forgot-password.php
 *
 * Delegates to password-reset.php (single source of truth).
 */

declare(strict_types=1);
require __DIR__ . '/password-reset.php';
