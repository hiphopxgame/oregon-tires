/**
 * Oregon Tires — Roadside Assistance Estimator
 * Client-side pricing calculator with bilingual support.
 * All content is from hardcoded config — no user input rendered as HTML.
 */
(function() {
  'use strict';

  var defaults = {
    base: 75,
    zoneSurcharge: 15,
    services: {
      'flat-tire':   { en: 'Flat Tire Change', es: 'Cambio de Llanta Ponchada', price: 0 },
      'jump-start':  { en: 'Jump Start', es: 'Arranque con Cables', price: 0 },
      'lockout':     { en: 'Lockout Service', es: 'Servicio de Cerrajería', price: 25 },
      'tow':         { en: 'Tow Coordination', es: 'Coordinación de Grúa', price: 50 },
    },
    zones: [
      { id: 'zone1', en: 'SE Portland (0-5 mi)', es: 'SE Portland (0-8 km)', multiplier: 0 },
      { id: 'zone2', en: 'Portland Metro (5-10 mi)', es: 'Portland Metro (8-16 km)', multiplier: 1 },
      { id: 'zone3', en: 'Outer Metro (10-15 mi)', es: 'Metro Exterior (16-24 km)', multiplier: 2 },
      { id: 'zone4', en: 'Extended (15+ mi)', es: 'Extendido (24+ km)', multiplier: 3 },
    ],
  };

  function init(containerId, options) {
    var container = document.getElementById(containerId);
    if (!container) return;

    var lang = window.currentLang || 'en';
    var config = Object.assign({}, defaults, options || {});

    // Read overrides from data attributes
    var base = parseFloat(container.dataset.priceBase) || config.base;
    var zoneSurcharge = parseFloat(container.dataset.priceZone) || config.zoneSurcharge;

    // Build UI using safe DOM methods
    buildUI(container, config, lang);

    // Event listeners
    var form = container.querySelector('.estimator-form');
    var result = container.querySelector('.estimator-result');

    form.addEventListener('change', function() {
      var service = form.querySelector('input[name="est_service"]:checked');
      var zone = form.querySelector('input[name="est_zone"]:checked');

      if (!service || !zone) {
        while (result.firstChild) result.removeChild(result.firstChild);
        return;
      }

      var svcKey = service.value;
      var zoneMultiplier = parseInt(zone.dataset.multiplier);
      var svcPrice = config.services[svcKey] ? config.services[svcKey].price : 0;
      var total = base + svcPrice + (zoneSurcharge * zoneMultiplier);

      var disclaimer = lang === 'es'
        ? 'Esto es un estimado. El precio final se confirma por teléfono.'
        : 'This is an estimate. Final pricing confirmed by phone.';

      var totalLabel = lang === 'es' ? 'Estimado' : 'Estimate';

      // Build result using DOM methods
      while (result.firstChild) result.removeChild(result.firstChild);

      var wrapper = document.createElement('div');
      wrapper.className = 'mt-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-center';

      var priceEl = document.createElement('p');
      priceEl.className = 'text-3xl font-bold text-green-700 dark:text-green-400';
      priceEl.textContent = '$' + total.toFixed(2);
      wrapper.appendChild(priceEl);

      var labelEl = document.createElement('p');
      labelEl.className = 'text-sm text-green-600 dark:text-green-500 mt-1';
      labelEl.textContent = totalLabel;
      wrapper.appendChild(labelEl);

      var disclaimerEl = document.createElement('p');
      disclaimerEl.className = 'text-xs text-gray-500 dark:text-gray-400 mt-2 italic';
      disclaimerEl.textContent = disclaimer;
      wrapper.appendChild(disclaimerEl);

      result.appendChild(wrapper);
    });
  }

  function buildUI(container, config, lang) {
    var form = document.createElement('div');
    form.className = 'estimator-form space-y-6';

    // Service type section
    var svcSection = document.createElement('div');
    var svcLabel = document.createElement('label');
    svcLabel.className = 'block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3';
    svcLabel.textContent = lang === 'es' ? 'Tipo de Servicio' : 'Service Type';
    svcSection.appendChild(svcLabel);

    var svcGrid = document.createElement('div');
    svcGrid.className = 'grid grid-cols-2 gap-3';

    for (var key in config.services) {
      if (!config.services.hasOwnProperty(key)) continue;
      var svc = config.services[key];
      var svcItem = document.createElement('label');
      svcItem.className = 'flex items-center gap-2 p-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:border-green-500 transition-colors has-[:checked]:border-green-600 has-[:checked]:bg-green-50 dark:has-[:checked]:bg-green-900/20';

      var radio = document.createElement('input');
      radio.type = 'radio';
      radio.name = 'est_service';
      radio.value = key;
      radio.className = 'text-green-600 focus:ring-green-500';
      svcItem.appendChild(radio);

      var text = document.createElement('span');
      text.className = 'text-sm';
      var labelText = lang === 'es' ? svc.es : svc.en;
      text.textContent = svc.price > 0 ? labelText + ' (+$' + svc.price + ')' : labelText;
      svcItem.appendChild(text);

      svcGrid.appendChild(svcItem);
    }
    svcSection.appendChild(svcGrid);
    form.appendChild(svcSection);

    // Zone section
    var zoneSection = document.createElement('div');
    var zoneLabel = document.createElement('label');
    zoneLabel.className = 'block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3';
    zoneLabel.textContent = lang === 'es' ? 'Zona de Servicio' : 'Service Zone';
    zoneSection.appendChild(zoneLabel);

    var zoneList = document.createElement('div');
    zoneList.className = 'space-y-2';

    for (var z = 0; z < config.zones.length; z++) {
      var zone = config.zones[z];
      var zoneItem = document.createElement('label');
      zoneItem.className = 'flex items-center gap-3 p-3 border-2 border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:border-green-500 transition-colors has-[:checked]:border-green-600 has-[:checked]:bg-green-50 dark:has-[:checked]:bg-green-900/20';

      var zRadio = document.createElement('input');
      zRadio.type = 'radio';
      zRadio.name = 'est_zone';
      zRadio.value = zone.id;
      zRadio.dataset.multiplier = zone.multiplier;
      zRadio.className = 'text-green-600 focus:ring-green-500';
      zoneItem.appendChild(zRadio);

      var zText = document.createElement('span');
      zText.className = 'text-sm';
      var zLabel = lang === 'es' ? zone.es : zone.en;
      var surcharge = zone.multiplier > 0
        ? ' (+$' + (config.zoneSurcharge * zone.multiplier) + ')'
        : (lang === 'es' ? ' (sin cargo extra)' : ' (no extra charge)');

      var zMain = document.createTextNode(zLabel);
      zText.appendChild(zMain);
      var zExtra = document.createElement('span');
      zExtra.className = 'text-gray-500 dark:text-gray-400';
      zExtra.textContent = surcharge;
      zText.appendChild(zExtra);

      zoneItem.appendChild(zText);
      zoneList.appendChild(zoneItem);
    }
    zoneSection.appendChild(zoneList);
    form.appendChild(zoneSection);

    // Base price note
    var baseNote = document.createElement('p');
    baseNote.className = 'text-xs text-gray-500 dark:text-gray-400';
    baseNote.textContent = lang === 'es'
      ? 'Tarifa base de servicio: $' + config.base
      : 'Base service fee: $' + config.base;
    form.appendChild(baseNote);

    container.appendChild(form);

    // Result container
    var result = document.createElement('div');
    result.className = 'estimator-result';
    container.appendChild(result);
  }

  window.RoadsideEstimator = { init: init };
})();
