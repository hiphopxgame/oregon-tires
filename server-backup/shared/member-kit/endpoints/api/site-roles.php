<?php
/**
 * Site-Roles API -- Manage site-scoped role assignments
 *
 * GET    -- List site role assignments for current site
 * POST   -- Assign/change a role
 * DELETE -- Remove a role (returns user to "standard")
 *
 * Permission matrix:
 *   Super Admin: assign admin/manager/support on any site, remove anyone
 *   Site Admin:  assign manager/support on own site, remove manager/support
 *   Manager/Support/Standard: no assignment permissions
 *
 * Usage from site's API:
 *   require_once __DIR__ . '/../../config.php';
 *   require_once __DIR__ . '/../../includes/auth.php';
 *   initMemberAuth();
 *   MemberAuth::requireAuth();
 *   require MEMBER_KIT_PATH . '/endpoints/api/site-roles.php';
 */

declare(strict_types=1);

header('Content-Type: application/json');

// Ensure MemberAuth is initialized and user is logged in
if (!class_exists('MemberAuth') || !MemberAuth::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

$pdo = MemberAuth::getPdo();
$method = $_SERVER['REQUEST_METHOD'];
$siteKey = MemberAuth::getConfig('site_key') ?: '';

if (!$siteKey) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Site key not configured']);
    exit;
}

// CSRF check for write operations
if (in_array($method, ['POST', 'DELETE'])) {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $csrfToken = $input['csrf_token'] ?? $_POST['csrf_token'] ?? '';
    if (!MemberAuth::verifyCsrf($csrfToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }
}

$validRoles = ['admin', 'manager', 'support'];

switch ($method) {

    // ── GET: List role assignments ──────────────────────────────────
    case 'GET':
        // Require at least admin role to view assignments
        if (!MemberAuth::isSiteAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Insufficient permissions']);
            exit;
        }

        // For super admin: optionally filter by site_key param
        $querySite = $siteKey;
        if (MemberAuth::isSuperAdmin() && !empty($_GET['site_key'])) {
            $querySite = $_GET['site_key'];
        }

        try {
            $stmt = $pdo->prepare("
                SELECT usr.id, usr.user_id, usr.site_key, usr.role, usr.granted_at,
                       u.email, u.display_name, u.username,
                       g.email AS granted_by_email, g.display_name AS granted_by_name
                FROM user_site_roles usr
                JOIN users u ON u.id = usr.user_id
                LEFT JOIN users g ON g.id = usr.granted_by
                WHERE usr.site_key = ?
                ORDER BY usr.granted_at DESC
            ");
            $stmt->execute([$querySite]);
            $roles = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'roles' => $roles]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to load roles']);
        }
        break;

    // ── POST: Assign or change a role ───────────────────────────────
    case 'POST':
        $input = $input ?? json_decode(file_get_contents('php://input'), true) ?? [];
        $targetRole = $input['role'] ?? '';
        $targetUserId = (int) ($input['user_id'] ?? 0);
        $targetEmail = trim($input['email'] ?? '');
        $targetSite = $input['site_key'] ?? $siteKey;

        // Validate role
        if (!in_array($targetRole, $validRoles, true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid role. Must be: admin, manager, or support']);
            exit;
        }

        // Resolve user by email if user_id not provided
        if ($targetUserId <= 0 && $targetEmail !== '') {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$targetEmail]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'User not found']);
                exit;
            }
            $targetUserId = (int) $row['id'];
        }

        if ($targetUserId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Provide user_id or email']);
            exit;
        }

        // Permission checks
        $isSuperAdmin = MemberAuth::isSuperAdmin();

        if (!$isSuperAdmin && !MemberAuth::isSiteAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Only admins can assign roles']);
            exit;
        }

        // Site admins can only assign on their own site
        if (!$isSuperAdmin && $targetSite !== $siteKey) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'You can only assign roles on your own site']);
            exit;
        }

        // Site admins cannot assign admin role (only super admin can)
        if (!$isSuperAdmin && $targetRole === 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Only the super admin can assign admin roles']);
            exit;
        }

        // Get the current user's ID for audit trail
        $sessionKey = MemberAuth::getConfig('session_key') ?: 'member_id';
        $grantedBy = (int) ($_SESSION[$sessionKey] ?? 0);

        try {
            $stmt = $pdo->prepare("
                INSERT INTO user_site_roles (user_id, site_key, role, granted_by, granted_at)
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE role = VALUES(role), granted_by = VALUES(granted_by), granted_at = NOW()
            ");
            $stmt->execute([$targetUserId, $targetSite, $targetRole, $grantedBy]);

            echo json_encode([
                'success' => true,
                'message' => "Role '{$targetRole}' assigned on '{$targetSite}'",
                'user_id' => $targetUserId,
                'site_key' => $targetSite,
                'role' => $targetRole,
            ]);
        } catch (\Throwable $e) {
            error_log('[Site Roles API] Assign error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to assign role']);
        }
        break;

    // ── DELETE: Remove a role ────────────────────────────────────────
    case 'DELETE':
        $input = $input ?? json_decode(file_get_contents('php://input'), true) ?? [];
        $targetUserId = (int) ($input['user_id'] ?? 0);
        $targetSite = $input['site_key'] ?? $siteKey;

        if ($targetUserId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Provide user_id']);
            exit;
        }

        $isSuperAdmin = MemberAuth::isSuperAdmin();

        if (!$isSuperAdmin && !MemberAuth::isSiteAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Only admins can remove roles']);
            exit;
        }

        // Site admins can only remove on their own site
        if (!$isSuperAdmin && $targetSite !== $siteKey) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'You can only manage roles on your own site']);
            exit;
        }

        // Site admins cannot remove other admins (only super admin can)
        if (!$isSuperAdmin) {
            $stmt = $pdo->prepare(
                "SELECT role FROM user_site_roles WHERE user_id = ? AND site_key = ? LIMIT 1"
            );
            $stmt->execute([$targetUserId, $targetSite]);
            $existing = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($existing && $existing['role'] === 'admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Only the super admin can remove admin roles']);
                exit;
            }
        }

        try {
            $stmt = $pdo->prepare(
                "DELETE FROM user_site_roles WHERE user_id = ? AND site_key = ?"
            );
            $stmt->execute([$targetUserId, $targetSite]);

            echo json_encode([
                'success' => true,
                'message' => 'Role removed',
                'user_id' => $targetUserId,
                'site_key' => $targetSite,
            ]);
        } catch (\Throwable $e) {
            error_log('[Site Roles API] Remove error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to remove role']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
