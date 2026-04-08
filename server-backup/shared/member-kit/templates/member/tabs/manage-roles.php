<?php
/**
 * Manage Roles Tab -- Site admin role management interface
 *
 * Allows site admins to view and manage role assignments on their site.
 * Super admins can assign admin roles; site admins can only assign manager/support.
 *
 * Calls the site-roles API endpoint for all operations.
 */

declare(strict_types=1);

$isSuperAdmin = MemberAuth::isSuperAdmin();
$currentSiteKey = MemberAuth::getConfig('site_key') ?: '';
$csrfToken = MemberAuth::getCsrfToken();
?>
<div class="member-page">
    <div class="member-card member-card--wide">
        <div class="member-header">
            <h1>Manage Roles</h1>
            <p>Assign and manage roles for <?= htmlspecialchars(MemberAuth::getConfig('site_name') ?: 'this site') ?></p>
        </div>

        <!-- Assign Role Form -->
        <div style="margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--member-border);">
            <h2 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Assign Role</h2>
            <form id="assign-role-form" class="member-form" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end;">
                <div class="member-field" style="flex: 1; min-width: 200px;">
                    <label class="member-label" for="role-email">User Email</label>
                    <input class="member-input" type="email" id="role-email" placeholder="user@example.com" required>
                </div>
                <div class="member-field" style="min-width: 140px;">
                    <label class="member-label" for="role-select">Role</label>
                    <select class="member-input" id="role-select" required>
                        <?php if ($isSuperAdmin): ?>
                        <option value="admin">Admin</option>
                        <?php endif; ?>
                        <option value="manager">Manager</option>
                        <option value="support">Support</option>
                    </select>
                </div>
                <button type="submit" class="member-button" style="height: fit-content;">Assign</button>
            </form>
            <div id="assign-feedback" style="margin-top: 0.5rem; font-size: 0.875rem; display: none;"></div>
        </div>

        <!-- Current Roles -->
        <h2 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Current Role Assignments</h2>
        <div id="roles-list" style="color: var(--member-text-muted); font-size: 0.875rem;">
            Loading...
        </div>
    </div>
</div>

<script>
(function() {
    var siteKey = <?= json_encode($currentSiteKey) ?>;
    var csrf = <?= json_encode($csrfToken) ?>;
    var isSuperAdmin = <?= $isSuperAdmin ? 'true' : 'false' ?>;

    function el(tag, attrs, children) {
        var node = document.createElement(tag);
        if (attrs) Object.keys(attrs).forEach(function(k) {
            if (k === 'textContent') node.textContent = attrs[k];
            else if (k === 'onclick') node.addEventListener('click', attrs[k]);
            else node.setAttribute(k, attrs[k]);
        });
        if (children) children.forEach(function(c) { if (c) node.appendChild(c); });
        return node;
    }

    function loadRoles() {
        fetch('/api/member/site-roles.php?site_key=' + encodeURIComponent(siteKey), {
            credentials: 'include'
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var container = document.getElementById('roles-list');
            container.textContent = '';

            if (!data.success || !data.roles || data.roles.length === 0) {
                container.textContent = 'No roles assigned yet.';
                return;
            }

            var table = el('table', { style: 'width:100%;border-collapse:collapse;' });
            var thead = el('thead');
            var headerRow = el('tr', { style: 'text-align:left;border-bottom:1px solid var(--member-border);' });
            ['User', 'Role', 'Granted By', 'Date', ''].forEach(function(h) {
                headerRow.appendChild(el('th', { style: 'padding:0.5rem;', textContent: h }));
            });
            thead.appendChild(headerRow);
            table.appendChild(thead);

            var tbody = el('tbody');
            data.roles.forEach(function(r) {
                var row = el('tr', { style: 'border-bottom:1px solid var(--member-border);' });

                row.appendChild(el('td', { style: 'padding:0.5rem;', textContent: r.display_name || r.email }));

                var badge = el('span', {
                    style: 'background:var(--member-accent);color:var(--member-accent-text);padding:0.125rem 0.5rem;border-radius:0.25rem;font-size:0.75rem;font-weight:600;',
                    textContent: r.role
                });
                var roleTd = el('td', { style: 'padding:0.5rem;' });
                roleTd.appendChild(badge);
                row.appendChild(roleTd);

                row.appendChild(el('td', { style: 'padding:0.5rem;color:var(--member-text-muted);', textContent: r.granted_by_name || r.granted_by_email || 'System' }));
                row.appendChild(el('td', { style: 'padding:0.5rem;color:var(--member-text-muted);', textContent: r.granted_at || '' }));

                var actionTd = el('td', { style: 'padding:0.5rem;' });
                var canRemove = isSuperAdmin || r.role !== 'admin';
                if (canRemove) {
                    var userId = r.user_id;
                    actionTd.appendChild(el('button', {
                        style: 'background:none;border:none;color:var(--member-error);cursor:pointer;font-size:0.75rem;',
                        textContent: 'Remove',
                        onclick: (function(uid) { return function() { removeRole(uid); }; })(userId)
                    }));
                }
                row.appendChild(actionTd);
                tbody.appendChild(row);
            });
            table.appendChild(tbody);
            container.appendChild(table);
        })
        .catch(function() {
            document.getElementById('roles-list').textContent = 'Failed to load roles.';
        });
    }

    function removeRole(userId) {
        if (!confirm('Remove this role?')) return;
        fetch('/api/member/site-roles.php', {
            method: 'DELETE',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId, csrf_token: csrf })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) loadRoles();
            else alert(data.error || 'Failed to remove role');
        });
    }

    document.getElementById('assign-role-form').addEventListener('submit', function(e) {
        e.preventDefault();
        var email = document.getElementById('role-email').value;
        var role = document.getElementById('role-select').value;
        var feedback = document.getElementById('assign-feedback');

        fetch('/api/member/site-roles.php', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email, role: role, csrf_token: csrf })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            feedback.style.display = 'block';
            if (data.success) {
                feedback.style.color = 'var(--member-success)';
                feedback.textContent = data.message || 'Role assigned.';
                document.getElementById('role-email').value = '';
                loadRoles();
            } else {
                feedback.style.color = 'var(--member-error)';
                feedback.textContent = data.error || 'Failed to assign role.';
            }
        })
        .catch(function() {
            feedback.style.display = 'block';
            feedback.style.color = 'var(--member-error)';
            feedback.textContent = 'Connection error.';
        });
    });

    loadRoles();
})();
</script>
