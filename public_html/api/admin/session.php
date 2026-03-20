<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

try {
    $user = requireStaff();

    requireMethod('GET');

    // Build permissions array
    $permissions = ['*']; // admin default
    $groupName = null;
    $groupNameEs = null;
    $groupId = null;
    if ($user['type'] === 'employee') {
        $permissions = $_SESSION['employee_permissions'] ?? ['my_work'];
        $groupName   = $_SESSION['employee_group_name'] ?? null;
        $groupNameEs = $_SESSION['employee_group_name_es'] ?? null;
        $groupId     = $_SESSION['employee_group_id'] ?? null;
    }

    jsonSuccess([
        'id'            => $user['id'],
        'email'         => $user['email'],
        'role'          => $user['role'],
        'name'          => $user['name'],
        'type'          => $user['type'],
        'language'      => $user['language'] ?? 'both',
        'employee_id'   => $user['employee_id'] ?? null,
        'csrf_token'    => $_SESSION['csrf_token'] ?? '',
        'permissions'   => $permissions,
        'group_id'      => $groupId,
        'group_name'    => $groupName,
        'group_name_es' => $groupNameEs,
    ]);
} catch (\Throwable $e) {
    error_log('Session check error: ' . $e->getMessage());
    jsonError('Internal server error.', 500);
}
