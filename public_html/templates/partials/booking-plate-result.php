<?php
/**
 * HTMX partial: License plate lookup result.
 *
 * Expected variables:
 *   $plateSuccess — bool (plate was found)
 *   $plateVin     — string|null (resolved VIN)
 *   $plateError   — string|null (error message if not found)
 *   $vinSuccess   — bool (VIN decode succeeded, only if plateSuccess)
 *   $vinData      — array|null
 *   $vinError     — string|null
 *   $lang         — 'en' | 'es'
 */

if ($plateSuccess && !empty($plateVin)): ?>
<p class="text-xs mt-1 text-green-600 dark:text-green-400 font-medium">
    <?= $lang === 'es' ? 'Placa encontrada!' : 'Plate found!' ?>
</p>

<!-- OOB swap: populate VIN field with resolved VIN -->
<input type="text" name="vehicle_vin" id="vehicle-vin" maxlength="17"
       value="<?= htmlspecialchars($plateVin, ENT_QUOTES, 'UTF-8') ?>"
       placeholder="<?= $lang === 'es' ? 'ej. 1HGCM82633A004352' : 'e.g. 1HGCM82633A004352' ?>"
       hx-get="/api/vin-decode.php"
       hx-trigger="input changed delay:300ms[this.value.length === 17]"
       hx-target="#vin-status"
       hx-swap="innerHTML"
       hx-swap-oob="outerHTML:#vehicle-vin"
       class="flex-1 p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-600 dark:text-gray-100 dark:border-gray-500 uppercase tracking-wider font-mono text-sm">

<?php
// Reuse the existing VIN result partial for vehicle specs + year/make/model OOB swaps
require __DIR__ . '/booking-vin-result.php';
?>

<?php else: ?>
<p class="text-xs mt-1 text-red-600 dark:text-red-400">
    <?= htmlspecialchars($plateError ?? ($lang === 'es' ? 'No se encontró el vehículo.' : 'Vehicle not found.'), ENT_QUOTES, 'UTF-8') ?>
</p>
<?php endif; ?>
