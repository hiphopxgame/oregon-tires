<?php
/**
 * HTMX partial: VIN decode result.
 *
 * Expected variables:
 *   $vinSuccess — bool
 *   $vinData    — array|null (keys: year, make, model)
 *   $vinError   — string|null
 *   $lang       — 'en' | 'es'
 */

if ($vinSuccess && $vinData): ?>
<p class="text-xs mt-1 text-green-600 dark:text-green-400 font-medium">
    <?= $lang === 'es' ? 'Informacion del vehiculo completada!' : 'Vehicle info filled in!' ?>
</p>

<?php if (!empty($vinData['year'])): ?>
<select name="vehicleYear" id="vehicle-year" hx-swap-oob="outerHTML:#vehicle-year"
        class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-600 dark:text-gray-100 dark:border-gray-500">
    <option value="">--</option>
    <?php
    $currentYear = (int) date('Y') + 1;
    for ($y = $currentYear; $y >= 1990; $y--):
        $selected = ($y === (int) $vinData['year']) ? ' selected' : '';
    ?>
    <option value="<?= $y ?>"<?= $selected ?>><?= $y ?></option>
    <?php endfor; ?>
</select>
<?php endif; ?>

<?php if (!empty($vinData['make'])): ?>
<input type="text" name="vehicleMake" id="vehicle-make" hx-swap-oob="outerHTML:#vehicle-make"
       value="<?= htmlspecialchars($vinData['make'], ENT_QUOTES, 'UTF-8') ?>"
       placeholder="<?= $lang === 'es' ? 'ej. Toyota' : 'e.g. Toyota' ?>"
       class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-600 dark:text-gray-100 dark:border-gray-500">
<?php endif; ?>

<?php if (!empty($vinData['model'])): ?>
<input type="text" name="vehicleModel" id="vehicle-model" hx-swap-oob="outerHTML:#vehicle-model"
       value="<?= htmlspecialchars($vinData['model'], ENT_QUOTES, 'UTF-8') ?>"
       placeholder="<?= $lang === 'es' ? 'ej. Camry' : 'e.g. Camry' ?>"
       class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-600 dark:text-gray-100 dark:border-gray-500">
<?php endif; ?>

<?php else: ?>
<p class="text-xs mt-1 text-red-600 dark:text-red-400">
    <?= htmlspecialchars($vinError ?? ($lang === 'es' ? 'No se pudo decodificar el VIN.' : 'Could not decode VIN.'), ENT_QUOTES, 'UTF-8') ?>
</p>
<?php endif; ?>
