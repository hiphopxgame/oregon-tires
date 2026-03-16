<?php
/**
 * Admin Panel tab — rendered inline on the unified dashboard for admin users.
 * Shows a quick overview + link to the full admin SPA.
 */

declare(strict_types=1);

$lang = $lang ?? getMemberLang();
?>
<div class="member-tab-content">
    <h3 class="member-tab-title"><?= htmlspecialchars(memberT('admin_panel', $lang)) ?></h3>
    <p style="color:var(--member-text-muted);margin-bottom:1.5rem;">
        <?= htmlspecialchars(memberT('admin_desc', $lang)) ?>
    </p>

    <a href="/admin/"
       style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.875rem 1.5rem;background:var(--member-accent);color:var(--member-accent-text);border-radius:var(--member-radius);font-weight:600;text-decoration:none;font-size:1rem;transition:opacity 0.2s;"
       onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
        ⚙️ <?= htmlspecialchars(memberT('open_admin', $lang)) ?>
    </a>

    <div style="margin-top:2rem;padding:1.25rem;background:var(--member-surface);border:1px solid var(--member-border);border-radius:var(--member-radius);">
        <p style="font-weight:600;margin-bottom:0.75rem;"><?= htmlspecialchars(memberT('admin_features', $lang)) ?></p>
        <ul style="list-style:none;padding:0;margin:0;display:grid;gap:0.5rem;">
            <?php
            $features = $lang === 'es'
                ? ['📅 Citas y calendario', '👤 Directorio de clientes', '🔧 Órdenes de reparación y kanban', '📋 Inspecciones y estimados', '👥 Empleados y horarios', '📝 Blog y contenido del sitio', '📈 Analíticas', '💬 Mensajes y conversaciones']
                : ['📅 Appointments & calendar', '👤 Customer directory', '🔧 Repair orders & kanban', '📋 Inspections & estimates', '👥 Employees & schedules', '📝 Blog & site content', '📈 Analytics', '💬 Messages & conversations'];
            foreach ($features as $f):
            ?>
            <li style="padding:0.5rem 0.75rem;background:var(--member-bg);border-radius:calc(var(--member-radius) * 0.75);"><?= $f ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
