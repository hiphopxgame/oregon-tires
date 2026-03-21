/**
 * Oregon Tires — Market Intel (Portland Auto Directory)
 * Map + directory view of 976 auto-related businesses in the Portland metro area.
 * Uses Leaflet.js for the interactive map (loaded lazily).
 */
(function() {
  'use strict';

  var DATA_URL = '/admin/js/market-intel-data.json';
  var allShops = [];
  var filteredShops = [];
  var map = null;
  var markers = [];
  var markerCluster = null;
  var selectedShop = null;
  var currentView = 'map'; // 'map' or 'directory'
  var filterCategory = 'all';
  var filterCity = 'all';
  var filterChain = 'all';
  var searchQuery = '';
  var sortBy = 'rating';
  var directoryPage = 1;
  var perPage = 25;
  var leafletLoaded = false;

  function t(key, fb) {
    return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fb;
  }

  // ─── Load Leaflet CSS + JS lazily ─────────────────────────────────────────
  function loadLeaflet() {
    return new Promise(function(resolve) {
      if (leafletLoaded) { resolve(); return; }

      var css = document.createElement('link');
      css.rel = 'stylesheet';
      css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
      document.head.appendChild(css);

      var script = document.createElement('script');
      script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
      script.onload = function() { leafletLoaded = true; resolve(); };
      script.onerror = function() { resolve(); }; // graceful fail
      document.head.appendChild(script);
    });
  }

  // ─── Category colors + labels ─────────────────────────────────────────────
  var CAT_CONFIG = {
    auto_repair:  { color: '#3B82F6', icon: '🔧', label: 'Auto Repair', labelEs: 'Reparaci\u00f3n' },
    parts_store:  { color: '#F59E0B', icon: '🏪', label: 'Parts Store', labelEs: 'Refacciones' },
    dealership:   { color: '#8B5CF6', icon: '🚗', label: 'Dealership', labelEs: 'Agencia' },
    specialty:    { color: '#10B981', icon: '⭐', label: 'Specialty', labelEs: 'Especialidad' },
  };

  var SUBCAT_LABELS = {
    tires: 'Tires', general_mechanic: 'General Mechanic', brakes: 'Brakes',
    parts: 'Parts', dealership_service: 'Dealership Service',
    body_shop: 'Body Shop', detailing: 'Detailing', tint: 'Window Tint',
    audio: 'Car Audio', upholstery: 'Upholstery', towing: 'Towing',
    oil_change: 'Oil Change', transmission: 'Transmission', muffler: 'Muffler/Exhaust',
  };

  // ─── Load data ────────────────────────────────────────────────────────────
  async function loadMarketIntel() {
    var container = document.getElementById('market-intel-container');
    if (!container) return;

    if (allShops.length) {
      render();
      return;
    }

    container.textContent = '';
    var loading = document.createElement('div');
    loading.className = 'text-center py-16';
    loading.innerHTML = '<div class="inline-block w-8 h-8 border-4 border-brand border-t-transparent rounded-full animate-spin mb-4"></div><p class="text-gray-500 dark:text-gray-400">' + t('miLoading', 'Loading market data...') + '</p>';
    container.appendChild(loading);

    try {
      var res = await fetch(DATA_URL);
      allShops = await res.json();
      applyFilters();
      render();
    } catch (err) {
      console.error('loadMarketIntel:', err);
      container.textContent = '';
      var errP = document.createElement('p');
      errP.className = 'text-red-500 text-center py-8';
      errP.textContent = t('miLoadError', 'Failed to load market data.');
      container.appendChild(errP);
    }
  }

  // ─── Filtering ────────────────────────────────────────────────────────────
  function applyFilters() {
    var q = searchQuery.toLowerCase();
    filteredShops = allShops.filter(function(s) {
      if (filterCategory !== 'all' && s.category !== filterCategory) return false;
      if (filterCity !== 'all' && s.city !== filterCity) return false;
      if (filterChain === 'chain' && !s.chain) return false;
      if (filterChain === 'independent' && s.chain) return false;
      if (q) {
        var haystack = (s.name + ' ' + s.address + ' ' + s.city + ' ' + (s.chain_name || '') + ' ' + (s.subcategory || '') + ' ' + (s.services || []).join(' ')).toLowerCase();
        if (haystack.indexOf(q) === -1) return false;
      }
      return true;
    });

    // Sort
    if (sortBy === 'rating') {
      filteredShops.sort(function(a, b) { return (b.google_rating || 0) - (a.google_rating || 0); });
    } else if (sortBy === 'reviews') {
      filteredShops.sort(function(a, b) { return (b.google_review_count || 0) - (a.google_review_count || 0); });
    } else if (sortBy === 'name') {
      filteredShops.sort(function(a, b) { return a.name.localeCompare(b.name); });
    } else if (sortBy === 'distance') {
      // Distance from Oregon Tires (45.4626, -122.5801)
      var OT_LAT = 45.4626, OT_LNG = -122.5801;
      filteredShops.sort(function(a, b) {
        var da = Math.hypot((a.lat || 0) - OT_LAT, (a.lng || 0) - OT_LNG);
        var db = Math.hypot((b.lat || 0) - OT_LAT, (b.lng || 0) - OT_LNG);
        return da - db;
      });
    }

    directoryPage = 1;
  }

  // ─── Main Render ──────────────────────────────────────────────────────────
  function render() {
    var container = document.getElementById('market-intel-container');
    if (!container) return;
    container.textContent = '';

    // Stats bar
    container.appendChild(renderStats());

    // Filter bar
    container.appendChild(renderFilterBar());

    // View toggle + content
    var viewWrap = document.createElement('div');
    viewWrap.id = 'mi-view-content';

    if (currentView === 'map') {
      viewWrap.appendChild(renderMapView());
    } else {
      viewWrap.appendChild(renderDirectoryView());
    }

    container.appendChild(viewWrap);
  }

  // ─── Stats Bar ────────────────────────────────────────────────────────────
  function renderStats() {
    var stats = document.createElement('div');
    stats.className = 'grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4';

    var cats = {};
    var totalReviews = 0;
    var ratedCount = 0;
    var ratingSum = 0;
    allShops.forEach(function(s) {
      cats[s.category] = (cats[s.category] || 0) + 1;
      totalReviews += (s.google_review_count || 0);
      if (s.google_rating) { ratingSum += s.google_rating; ratedCount++; }
    });
    var avgRating = ratedCount ? (ratingSum / ratedCount).toFixed(1) : '—';

    stats.className = 'grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-4';

    [
      [String(allShops.length), t('miTotalShops', 'Total Businesses'), 'text-brand dark:text-green-400'],
      [totalReviews.toLocaleString(), t('miTotalReviews', 'Total Reviews'), 'text-amber-600 dark:text-amber-400'],
      [avgRating + '⭐', t('miAvgRating', 'Avg Rating'), 'text-amber-600 dark:text-amber-400'],
      [String(cats.auto_repair || 0), t('miRepairShops', 'Repair Shops'), 'text-blue-600 dark:text-blue-400'],
      [String(cats.dealership || 0), t('miDealerships', 'Dealerships'), 'text-purple-600 dark:text-purple-400'],
      [String(cats.specialty || 0), t('miSpecialty', 'Specialty'), 'text-emerald-600 dark:text-emerald-400'],
    ].forEach(function(item) {
      var card = document.createElement('div');
      card.className = 'bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700 p-3 text-center';
      var val = document.createElement('div');
      val.className = 'text-xl font-bold ' + item[2];
      val.textContent = item[0];
      card.appendChild(val);
      var lbl = document.createElement('div');
      lbl.className = 'text-xs text-gray-500 dark:text-gray-400';
      lbl.textContent = item[1];
      card.appendChild(lbl);
      stats.appendChild(card);
    });

    return stats;
  }

  // ─── Filter Bar ───────────────────────────────────────────────────────────
  function renderFilterBar() {
    var bar = document.createElement('div');
    bar.className = 'flex flex-wrap items-center gap-2 mb-4';

    // View toggle
    var viewToggle = document.createElement('div');
    viewToggle.className = 'flex rounded-lg border dark:border-gray-600 overflow-hidden';
    ['map', 'directory'].forEach(function(v) {
      var btn = document.createElement('button');
      btn.className = 'px-3 py-2 text-sm font-medium transition ' + (currentView === v ? 'bg-brand text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700');
      btn.textContent = v === 'map' ? '🗺️ ' + t('miMap', 'Map') : '📋 ' + t('miDirectory', 'Directory');
      btn.addEventListener('click', function() { currentView = v; render(); });
      viewToggle.appendChild(btn);
    });
    bar.appendChild(viewToggle);

    // Search
    var search = document.createElement('input');
    search.type = 'text';
    search.placeholder = t('miSearch', 'Search businesses...');
    search.value = searchQuery;
    search.className = 'border rounded-lg px-3 py-2 text-sm flex-1 min-w-[150px] dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200';
    var debounce;
    search.addEventListener('input', function() {
      clearTimeout(debounce);
      var self = this;
      debounce = setTimeout(function() { searchQuery = self.value.trim(); applyFilters(); render(); }, 300);
    });
    bar.appendChild(search);

    // Category filter
    var catSel = document.createElement('select');
    catSel.className = 'border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200';
    [['all', t('miAllCategories', 'All Categories')], ['auto_repair', '🔧 ' + t('miRepairShops', 'Repair')], ['parts_store', '🏪 ' + t('miParts', 'Parts')], ['dealership', '🚗 ' + t('miDealerships', 'Dealership')], ['specialty', '⭐ ' + t('miSpecialty', 'Specialty')]].forEach(function(o) {
      var opt = document.createElement('option');
      opt.value = o[0]; opt.textContent = o[1];
      if (o[0] === filterCategory) opt.selected = true;
      catSel.appendChild(opt);
    });
    catSel.addEventListener('change', function() { filterCategory = this.value; applyFilters(); render(); });
    bar.appendChild(catSel);

    // City filter
    var cities = {};
    allShops.forEach(function(s) { cities[s.city] = (cities[s.city] || 0) + 1; });
    var sortedCities = Object.entries(cities).sort(function(a, b) { return b[1] - a[1]; });
    var citySel = document.createElement('select');
    citySel.className = 'border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200';
    var allOpt = document.createElement('option');
    allOpt.value = 'all'; allOpt.textContent = t('miAllCities', 'All Cities');
    citySel.appendChild(allOpt);
    sortedCities.forEach(function(c) {
      var opt = document.createElement('option');
      opt.value = c[0]; opt.textContent = c[0] + ' (' + c[1] + ')';
      if (c[0] === filterCity) opt.selected = true;
      citySel.appendChild(opt);
    });
    citySel.addEventListener('change', function() { filterCity = this.value; applyFilters(); render(); });
    bar.appendChild(citySel);

    // Chain filter
    var chainSel = document.createElement('select');
    chainSel.className = 'border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200';
    [['all', t('miAllTypes', 'All Types')], ['independent', t('miIndependent', 'Independent')], ['chain', t('miChains', 'Chains')]].forEach(function(o) {
      var opt = document.createElement('option');
      opt.value = o[0]; opt.textContent = o[1];
      if (o[0] === filterChain) opt.selected = true;
      chainSel.appendChild(opt);
    });
    chainSel.addEventListener('change', function() { filterChain = this.value; applyFilters(); render(); });
    bar.appendChild(chainSel);

    // Sort (directory view)
    if (currentView === 'directory') {
      var sortSel = document.createElement('select');
      sortSel.className = 'border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200';
      [['rating', '⭐ ' + t('miSortRating', 'Rating')], ['reviews', '💬 ' + t('miSortReviews', 'Reviews')], ['name', 'A-Z'], ['distance', '📍 ' + t('miSortDistance', 'Distance')]].forEach(function(o) {
        var opt = document.createElement('option');
        opt.value = o[0]; opt.textContent = o[1];
        if (o[0] === sortBy) opt.selected = true;
        sortSel.appendChild(opt);
      });
      sortSel.addEventListener('change', function() { sortBy = this.value; applyFilters(); render(); });
      bar.appendChild(sortSel);
    }

    // Results count
    var count = document.createElement('span');
    count.className = 'text-sm text-gray-500 dark:text-gray-400 ml-auto';
    count.textContent = filteredShops.length + ' ' + t('miResults', 'results');
    bar.appendChild(count);

    return bar;
  }

  // ─── Map View ─────────────────────────────────────────────────────────────
  function renderMapView() {
    var wrap = document.createElement('div');
    wrap.className = 'flex gap-4 flex-col lg:flex-row';

    // Map container
    var mapCol = document.createElement('div');
    mapCol.className = 'flex-1 min-h-[500px]';
    var mapDiv = document.createElement('div');
    mapDiv.id = 'mi-map';
    mapDiv.className = 'w-full h-[500px] lg:h-[600px] rounded-xl border dark:border-gray-700 overflow-hidden bg-gray-100 dark:bg-gray-900';
    mapCol.appendChild(mapDiv);
    wrap.appendChild(mapCol);

    // Side panel (selected shop detail or legend)
    var sidePanel = document.createElement('div');
    sidePanel.id = 'mi-side-panel';
    sidePanel.className = 'w-full lg:w-80 shrink-0';

    if (selectedShop) {
      sidePanel.appendChild(renderShopDetail(selectedShop));
    } else {
      sidePanel.appendChild(renderMapLegend());
    }
    wrap.appendChild(sidePanel);

    // Init map after DOM insert
    setTimeout(function() { initMap(); }, 50);

    return wrap;
  }

  function initMap() {
    var mapEl = document.getElementById('mi-map');
    if (!mapEl) return;

    loadLeaflet().then(function() {
      if (typeof L === 'undefined') {
        mapEl.innerHTML = '<p class="text-center text-gray-400 py-16">Map library failed to load. Use Directory view instead.</p>';
        return;
      }

      // Destroy previous map
      if (map) { map.remove(); map = null; }

      map = L.map('mi-map').setView([45.5152, -122.6784], 11);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap',
        maxZoom: 18,
      }).addTo(map);

      // Oregon Tires marker (special)
      var otIcon = L.divIcon({
        html: '<div style="background:#007030;color:#fff;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.3);">🏠</div>',
        iconSize: [32, 32], iconAnchor: [16, 16], className: '',
      });
      L.marker([45.4626, -122.5801], { icon: otIcon })
        .addTo(map)
        .bindPopup('<strong>Oregon Tires Auto Care</strong><br>8536 SE 82nd Ave');

      // Add shop markers
      addMarkers();
    });
  }

  function addMarkers() {
    if (!map) return;

    // Clear existing
    markers.forEach(function(m) { map.removeLayer(m); });
    markers = [];

    filteredShops.forEach(function(shop) {
      if (!shop.lat || !shop.lng) return;

      var cat = CAT_CONFIG[shop.category] || CAT_CONFIG.auto_repair;
      var icon = L.divIcon({
        html: '<div style="background:' + cat.color + ';width:10px;height:10px;border-radius:50%;border:2px solid #fff;box-shadow:0 1px 3px rgba(0,0,0,0.3);"></div>',
        iconSize: [10, 10], iconAnchor: [5, 5], className: '',
      });

      var marker = L.marker([shop.lat, shop.lng], { icon: icon });
      marker.bindPopup(
        '<div style="min-width:200px">' +
        '<strong>' + escHtml(shop.name) + '</strong>' +
        (shop.google_rating ? '<br>⭐ ' + shop.google_rating + ' (' + (shop.google_review_count || 0) + ')' : '') +
        '<br><span style="color:#6b7280;font-size:12px">' + escHtml(shop.address) + ', ' + escHtml(shop.city) + '</span>' +
        (shop.phone ? '<br>📞 ' + escHtml(shop.phone) : '') +
        '</div>'
      );
      marker.on('click', function() {
        selectedShop = shop;
        var panel = document.getElementById('mi-side-panel');
        if (panel) { panel.textContent = ''; panel.appendChild(renderShopDetail(shop)); }
      });
      marker.addTo(map);
      markers.push(marker);
    });
  }

  function escHtml(s) {
    var d = document.createElement('div');
    d.textContent = s || '';
    return d.innerHTML;
  }

  // ─── Map Legend ───────────────────────────────────────────────────────────
  function renderMapLegend() {
    var legend = document.createElement('div');
    legend.className = 'bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 p-4 space-y-3';

    var title = document.createElement('h4');
    title.className = 'font-bold dark:text-gray-200 mb-2';
    title.textContent = t('miLegend', 'Legend');
    legend.appendChild(title);

    Object.entries(CAT_CONFIG).forEach(function(entry) {
      var row = document.createElement('div');
      row.className = 'flex items-center gap-2';
      var dot = document.createElement('span');
      dot.style.cssText = 'width:12px;height:12px;border-radius:50%;background:' + entry[1].color + ';display:inline-block;';
      row.appendChild(dot);
      var lbl = document.createElement('span');
      lbl.className = 'text-sm text-gray-700 dark:text-gray-300';
      lbl.textContent = entry[1].label;
      row.appendChild(lbl);
      legend.appendChild(row);
    });

    // Oregon Tires callout
    var otRow = document.createElement('div');
    otRow.className = 'flex items-center gap-2 pt-2 border-t dark:border-gray-700';
    otRow.innerHTML = '<span style="width:12px;height:12px;border-radius:50%;background:#007030;display:inline-block;"></span><span class="text-sm font-medium text-brand dark:text-green-400">Oregon Tires (You)</span>';
    legend.appendChild(otRow);

    // Quick stats
    var statsDiv = document.createElement('div');
    statsDiv.className = 'pt-3 border-t dark:border-gray-700 space-y-1';
    var chainCount = filteredShops.filter(function(s) { return s.chain; }).length;
    var indCount = filteredShops.length - chainCount;
    var avgRating = filteredShops.length ? (filteredShops.reduce(function(s, b) { return s + (b.google_rating || 0); }, 0) / filteredShops.length).toFixed(1) : '—';
    [
      [t('miChains', 'Chains'), String(chainCount)],
      [t('miIndependent', 'Independent'), String(indCount)],
      [t('miAvgRating', 'Avg Rating'), avgRating + '⭐'],
    ].forEach(function(item) {
      var r = document.createElement('div');
      r.className = 'flex justify-between text-sm';
      r.innerHTML = '<span class="text-gray-500 dark:text-gray-400">' + item[0] + '</span><span class="font-medium dark:text-gray-200">' + item[1] + '</span>';
      statsDiv.appendChild(r);
    });
    legend.appendChild(statsDiv);

    // Tip
    var tip = document.createElement('p');
    tip.className = 'text-xs text-gray-400 dark:text-gray-500 pt-2';
    tip.textContent = t('miClickMarker', 'Click a marker to see details');
    legend.appendChild(tip);

    return legend;
  }

  // ─── Shop Detail Panel ────────────────────────────────────────────────────
  function renderShopDetail(shop) {
    var card = document.createElement('div');
    card.className = 'bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 p-4 space-y-3';

    // Close button
    var closeRow = document.createElement('div');
    closeRow.className = 'flex justify-between items-start';
    var nameH = document.createElement('h4');
    nameH.className = 'font-bold text-gray-900 dark:text-white text-sm leading-tight';
    nameH.textContent = shop.name;
    closeRow.appendChild(nameH);
    var closeBtn = document.createElement('button');
    closeBtn.className = 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-lg leading-none';
    closeBtn.textContent = '\u00d7';
    closeBtn.addEventListener('click', function() { selectedShop = null; var p = document.getElementById('mi-side-panel'); if (p) { p.textContent = ''; p.appendChild(renderMapLegend()); } });
    closeRow.appendChild(closeBtn);
    card.appendChild(closeRow);

    // Category badge
    var cat = CAT_CONFIG[shop.category] || CAT_CONFIG.auto_repair;
    var badge = document.createElement('div');
    badge.className = 'flex items-center gap-2 flex-wrap';
    badge.innerHTML = '<span class="text-xs px-2 py-0.5 rounded-full font-medium" style="background:' + cat.color + '20;color:' + cat.color + '">' + cat.icon + ' ' + (SUBCAT_LABELS[shop.subcategory] || shop.subcategory) + '</span>'
      + (shop.chain ? '<span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">' + escHtml(shop.chain_name) + '</span>' : '<span class="text-xs px-2 py-0.5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">Independent</span>');
    card.appendChild(badge);

    // Rating
    if (shop.google_rating) {
      var ratingRow = document.createElement('div');
      ratingRow.className = 'flex items-center gap-2';
      var stars = document.createElement('span');
      stars.className = 'text-amber-500 font-bold';
      stars.textContent = '⭐ ' + shop.google_rating;
      ratingRow.appendChild(stars);
      var reviews = document.createElement('span');
      reviews.className = 'text-xs text-gray-500 dark:text-gray-400';
      reviews.textContent = '(' + (shop.google_review_count || 0) + ' ' + t('miReviews', 'reviews') + ')';
      ratingRow.appendChild(reviews);
      card.appendChild(ratingRow);
    }

    // Address
    var addrP = document.createElement('p');
    addrP.className = 'text-sm text-gray-600 dark:text-gray-300';
    addrP.textContent = '📍 ' + shop.address + ', ' + shop.city + ', ' + shop.state + ' ' + shop.zip;
    card.appendChild(addrP);

    // Phone + Website
    if (shop.phone) {
      var phoneA = document.createElement('a');
      phoneA.href = 'tel:' + shop.phone.replace(/\D/g, '');
      phoneA.className = 'text-sm text-blue-600 dark:text-blue-400 hover:underline block';
      phoneA.textContent = '📞 ' + shop.phone;
      card.appendChild(phoneA);
    }
    if (shop.website) {
      var webA = document.createElement('a');
      webA.href = shop.website;
      webA.target = '_blank';
      webA.rel = 'noopener';
      webA.className = 'text-sm text-blue-600 dark:text-blue-400 hover:underline block truncate';
      webA.textContent = '🌐 ' + shop.website.replace(/^https?:\/\/(www\.)?/, '').replace(/\/$/, '');
      card.appendChild(webA);
    }

    // Hours
    if (shop.hours) {
      var hoursDiv = document.createElement('div');
      hoursDiv.className = 'text-xs text-gray-500 dark:text-gray-400 space-y-0.5 pt-2 border-t dark:border-gray-700';
      ['mon_fri', 'sat', 'sun'].forEach(function(k) {
        if (shop.hours[k]) {
          var label = k === 'mon_fri' ? 'Mon-Fri' : k === 'sat' ? 'Sat' : 'Sun';
          var r = document.createElement('div');
          r.innerHTML = '<span class="font-medium">' + label + ':</span> ' + escHtml(shop.hours[k]);
          hoursDiv.appendChild(r);
        }
      });
      card.appendChild(hoursDiv);
    }

    // Services
    if (shop.services && shop.services.length) {
      var svcDiv = document.createElement('div');
      svcDiv.className = 'pt-2 border-t dark:border-gray-700';
      var svcLabel = document.createElement('p');
      svcLabel.className = 'text-xs font-medium text-gray-500 dark:text-gray-400 mb-1';
      svcLabel.textContent = t('miServices', 'Services');
      svcDiv.appendChild(svcLabel);
      var chips = document.createElement('div');
      chips.className = 'flex flex-wrap gap-1';
      shop.services.forEach(function(svc) {
        var chip = document.createElement('span');
        chip.className = 'text-xs px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300';
        chip.textContent = svc;
        chips.appendChild(chip);
      });
      svcDiv.appendChild(chips);
      card.appendChild(svcDiv);
    }

    // Google Maps link
    var gmLink = document.createElement('a');
    gmLink.href = 'https://www.google.com/maps/place/?q=place_id:' + (shop.google_place_id || '');
    gmLink.target = '_blank';
    gmLink.rel = 'noopener';
    gmLink.className = 'block text-center text-xs py-2 bg-gray-50 dark:bg-gray-700 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition mt-2';
    gmLink.textContent = '📍 ' + t('miViewOnGoogle', 'View on Google Maps');
    card.appendChild(gmLink);

    return card;
  }

  // ─── Directory View ───────────────────────────────────────────────────────
  function renderDirectoryView() {
    var wrap = document.createElement('div');

    var totalPages = Math.ceil(filteredShops.length / perPage) || 1;
    var start = (directoryPage - 1) * perPage;
    var pageShops = filteredShops.slice(start, start + perPage);

    // Table
    var tableWrap = document.createElement('div');
    tableWrap.className = 'overflow-x-auto bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700';

    var table = document.createElement('table');
    table.className = 'w-full text-sm';

    // Header
    var thead = document.createElement('thead');
    thead.className = 'bg-gray-50 dark:bg-gray-900/50';
    var hr = document.createElement('tr');
    ['', t('miBusiness', 'Business'), t('miCategory', 'Category'), t('miLocation', 'Location'), t('miRating', 'Rating'), t('miPhone', 'Phone'), t('miType', 'Type')].forEach(function(h) {
      var th = document.createElement('th');
      th.className = 'text-left px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase';
      th.textContent = h;
      hr.appendChild(th);
    });
    thead.appendChild(hr);
    table.appendChild(thead);

    // Body
    var tbody = document.createElement('tbody');
    tbody.className = 'divide-y divide-gray-100 dark:divide-gray-700';

    if (!pageShops.length) {
      var emptyTr = document.createElement('tr');
      var emptyTd = document.createElement('td');
      emptyTd.colSpan = 7;
      emptyTd.className = 'text-center py-8 text-gray-400';
      emptyTd.textContent = t('miNoResults', 'No businesses match your filters.');
      emptyTr.appendChild(emptyTd);
      tbody.appendChild(emptyTr);
    }

    pageShops.forEach(function(shop, idx) {
      var tr = document.createElement('tr');
      tr.className = 'hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition';
      tr.addEventListener('click', function() {
        currentView = 'map';
        selectedShop = shop;
        render();
        if (map && shop.lat && shop.lng) {
          map.setView([shop.lat, shop.lng], 15);
        }
      });

      // Rank
      var tdRank = document.createElement('td');
      tdRank.className = 'px-3 py-2 text-gray-400 text-xs';
      tdRank.textContent = String(start + idx + 1);
      tr.appendChild(tdRank);

      // Name + website
      var tdName = document.createElement('td');
      tdName.className = 'px-3 py-2';
      var nameSpan = document.createElement('div');
      nameSpan.className = 'font-medium text-gray-900 dark:text-white';
      nameSpan.textContent = shop.name;
      tdName.appendChild(nameSpan);
      if (shop.website) {
        var webSpan = document.createElement('div');
        webSpan.className = 'text-xs text-gray-400 truncate max-w-[200px]';
        webSpan.textContent = shop.website.replace(/^https?:\/\/(www\.)?/, '').replace(/\/$/, '');
        tdName.appendChild(webSpan);
      }
      tr.appendChild(tdName);

      // Category
      var tdCat = document.createElement('td');
      tdCat.className = 'px-3 py-2';
      var cat = CAT_CONFIG[shop.category] || CAT_CONFIG.auto_repair;
      tdCat.innerHTML = '<span class="text-xs px-2 py-0.5 rounded-full font-medium" style="background:' + cat.color + '15;color:' + cat.color + '">' + (SUBCAT_LABELS[shop.subcategory] || shop.subcategory) + '</span>';
      tr.appendChild(tdCat);

      // Location
      var tdLoc = document.createElement('td');
      tdLoc.className = 'px-3 py-2 text-gray-600 dark:text-gray-300 text-xs';
      tdLoc.textContent = shop.city + (shop.neighborhood && shop.neighborhood !== shop.city ? ' (' + shop.neighborhood + ')' : '');
      tr.appendChild(tdLoc);

      // Rating
      var tdRat = document.createElement('td');
      tdRat.className = 'px-3 py-2';
      if (shop.google_rating) {
        tdRat.innerHTML = '<span class="font-bold text-amber-600">' + shop.google_rating + '</span> <span class="text-xs text-gray-400">(' + (shop.google_review_count || 0) + ')</span>';
      } else {
        tdRat.textContent = '—';
      }
      tr.appendChild(tdRat);

      // Phone
      var tdPhone = document.createElement('td');
      tdPhone.className = 'px-3 py-2 text-xs text-gray-600 dark:text-gray-300';
      tdPhone.textContent = shop.phone || '—';
      tr.appendChild(tdPhone);

      // Type
      var tdType = document.createElement('td');
      tdType.className = 'px-3 py-2';
      if (shop.chain) {
        tdType.innerHTML = '<span class="text-xs px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">Chain</span>';
      } else {
        tdType.innerHTML = '<span class="text-xs px-2 py-0.5 rounded bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400">Indie</span>';
      }
      tr.appendChild(tdType);

      tbody.appendChild(tr);
    });

    table.appendChild(tbody);
    tableWrap.appendChild(table);
    wrap.appendChild(tableWrap);

    // Pagination
    if (totalPages > 1) {
      var pag = document.createElement('div');
      pag.className = 'flex items-center justify-between mt-3 text-sm';
      var prevBtn = document.createElement('button');
      prevBtn.className = 'px-3 py-1 border rounded dark:border-gray-600 dark:text-gray-300 disabled:opacity-40';
      prevBtn.textContent = '← Prev';
      prevBtn.disabled = directoryPage <= 1;
      prevBtn.addEventListener('click', function() { directoryPage--; render(); });
      var info = document.createElement('span');
      info.className = 'text-gray-500 dark:text-gray-400';
      info.textContent = t('miPage', 'Page') + ' ' + directoryPage + ' / ' + totalPages;
      var nextBtn = document.createElement('button');
      nextBtn.className = 'px-3 py-1 border rounded dark:border-gray-600 dark:text-gray-300 disabled:opacity-40';
      nextBtn.textContent = 'Next →';
      nextBtn.disabled = directoryPage >= totalPages;
      nextBtn.addEventListener('click', function() { directoryPage++; render(); });
      pag.appendChild(prevBtn);
      pag.appendChild(info);
      pag.appendChild(nextBtn);
      wrap.appendChild(pag);
    }

    return wrap;
  }

  // ─── Expose ───────────────────────────────────────────────────────────────
  window.loadMarketIntel = loadMarketIntel;
})();
