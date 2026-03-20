<?php
/**
 * HTMX partial: Time slot grid for booking form.
 *
 * Expected variables:
 *   $allSlots  — array of time => ['available' => bool, 'reason' => string|null]
 *   $shopStart — int (hour, e.g. 7)
 *   $shopEnd   — int (hour, e.g. 18)
 *   $lang      — 'en' | 'es'
 *   $shopClosed — bool (optional, if entire day is closed)
 */

$shopClosed = $shopClosed ?? false;

if ($shopClosed): ?>
<div class="col-span-4 text-center py-6 text-gray-500 dark:text-gray-400">
    <p class="font-semibold text-lg"><?= $lang === 'es' ? 'Cerrado este dia' : 'Closed this day' ?></p>
    <p class="text-sm mt-1"><?= $lang === 'es' ? 'Por favor seleccione otra fecha.' : 'Please select a different date.' ?></p>
</div>
<?php return; endif;

for ($h = $shopStart; $h <= $shopEnd; $h++):
    $mins = ($h === $shopEnd) ? [0] : [0, 15, 30, 45];
    $ampm = $h >= 12 ? 'PM' : 'AM';
    $hour12 = $h > 12 ? $h - 12 : ($h === 0 ? 12 : $h);
?>
<div class="hour-label"><?= $hour12 . ' ' . $ampm ?></div>
<?php foreach ($mins as $m):
    $timeVal = sprintf('%02d:%02d', $h, $m);
    $displayTime = $hour12 . ':' . sprintf('%02d', $m) . ' ' . $ampm;
    $info = $allSlots[$timeVal] ?? ['available' => true, 'reason' => null];
    $available = $info['available'] ?? true;
    $reason = $info['reason'] ?? null;

    $baseClass = 'time-slot min-h-[44px] border-2 rounded-lg py-2 px-3 text-sm font-medium transition';
    if (!$available && $reason === 'closed'):
        $classes = $baseClass . ' slot-closed bg-gray-100 dark:bg-gray-800/50 border-gray-200 dark:border-gray-600 text-gray-400 dark:text-gray-500 cursor-not-allowed';
    elseif (!$available && $reason === 'past'):
        $classes = $baseClass . ' slot-past bg-gray-100 dark:bg-gray-800/50 border-gray-200 dark:border-gray-600 text-gray-400 dark:text-gray-500 cursor-not-allowed opacity-40';
    elseif (!$available):
        $classes = $baseClass . ' slot-full border-gray-200 dark:border-gray-600 opacity-50 cursor-not-allowed line-through';
    else:
        $classes = $baseClass . ' border-gray-200 dark:border-gray-600 hover:border-green-400';
    endif;
?>
<button type="button"
        class="<?= $classes ?>"
        data-time="<?= $timeVal ?>"
        <?= !$available ? 'disabled' : '' ?>
        <?php if (!$available && $reason === 'closed'): ?>
            title="<?= $lang === 'es' ? 'Cerrado' : 'Closed' ?>"
        <?php elseif (!$available && $reason === 'past'): ?>
            title="<?= $lang === 'es' ? 'Hora pasada' : 'Time has passed' ?>"
        <?php elseif (!$available): ?>
            title="<?= $lang === 'es' ? 'Horario lleno' : 'Time slot full' ?>"
        <?php endif; ?>
><?php if (!$available && $reason === 'closed'): ?>
<?= $lang === 'es' ? 'Cerrado' : 'Closed' ?>
<?php elseif (!$available && $reason === 'past'): ?>
<?= htmlspecialchars($displayTime) ?>
<?php elseif (!$available): ?>
<?= htmlspecialchars($displayTime) ?><span class="slot-badge block text-xs font-normal"><?= $lang === 'es' ? 'Lleno' : 'Full' ?></span>
<?php else: ?>
<?= htmlspecialchars($displayTime) ?>
<?php endif; ?></button>
<?php endforeach; endfor; ?>
