<?php
/**
 * HTMX partial: VIN decode result.
 *
 * Expected variables:
 *   $vinSuccess — bool
 *   $vinData    — array|null (keys: year, make, model, engine, transmission, drive_type, body_class, fuel_type, trim_level, doors)
 *   $vinError   — string|null
 *   $lang       — 'en' | 'es'
 */

if ($vinSuccess && $vinData): ?>
<p class="text-xs mt-1 text-green-600 dark:text-green-400 font-medium">
    <?= $lang === 'es' ? 'Vehiculo identificado!' : 'Vehicle identified!' ?>
</p>

<!-- Vehicle specs card -->
<?php
$specLabel = [
    'en' => ['engine' => 'Engine', 'transmission' => 'Transmission', 'drive_type' => 'Drive', 'body_class' => 'Body', 'fuel_type' => 'Fuel', 'trim_level' => 'Trim', 'doors' => 'Doors'],
    'es' => ['engine' => 'Motor', 'transmission' => 'Transmision', 'drive_type' => 'Traccion', 'body_class' => 'Carroceria', 'fuel_type' => 'Combustible', 'trim_level' => 'Version', 'doors' => 'Puertas'],
];
$labels = $specLabel[$lang] ?? $specLabel['en'];
$specs = array_filter([
    'engine'       => $vinData['engine'] ?? '',
    'transmission' => $vinData['transmission'] ?? '',
    'drive_type'   => $vinData['drive_type'] ?? '',
    'body_class'   => $vinData['body_class'] ?? '',
    'fuel_type'    => $vinData['fuel_type'] ?? '',
    'trim_level'   => $vinData['trim_level'] ?? '',
    'doors'        => $vinData['doors'] ?? '',
]);
if (!empty($specs)): ?>
<div class="mt-2 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg">
    <p class="text-sm font-semibold text-brand dark:text-green-400 mb-1">
        <?= htmlspecialchars(($vinData['year'] ?? '') . ' ' . ($vinData['make'] ?? '') . ' ' . ($vinData['model'] ?? '')) ?>
    </p>
    <div class="grid grid-cols-2 gap-x-4 gap-y-0.5 text-xs text-gray-600 dark:text-gray-300">
        <?php foreach ($specs as $key => $val): ?>
        <div><span class="font-medium text-gray-800 dark:text-gray-200"><?= $labels[$key] ?>:</span> <?= htmlspecialchars($val) ?></div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($vinData['year'])): ?>
<select name="vehicleYear" id="vehicle-year" hx-swap-oob="outerHTML:#vehicle-year"
        class="w-full p-3 text-base border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-600 dark:text-gray-100 dark:border-gray-500">
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
       class="w-full p-3 text-base border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-600 dark:text-gray-100 dark:border-gray-500">
<?php endif; ?>

<?php if (!empty($vinData['model'])): ?>
<input type="text" name="vehicleModel" id="vehicle-model" hx-swap-oob="outerHTML:#vehicle-model"
       value="<?= htmlspecialchars($vinData['model'], ENT_QUOTES, 'UTF-8') ?>"
       placeholder="<?= $lang === 'es' ? 'ej. Camry' : 'e.g. Camry' ?>"
       class="w-full p-3 text-base border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-600 dark:text-gray-100 dark:border-gray-500">
<?php endif; ?>

<?php else: ?>
<p class="text-xs mt-1 text-red-600 dark:text-red-400">
    <?= htmlspecialchars($vinError ?? ($lang === 'es' ? 'No se pudo decodificar el VIN.' : 'Could not decode VIN.'), ENT_QUOTES, 'UTF-8') ?>
</p>
<?php endif; ?>
