<?php
/**
 * Oregon Tires — Site Footer (reusable partial)
 * Extracted from index.html for use on PHP pages.
 */
?>
<footer class="bg-brand text-white py-12 pb-24 md:pb-12">
  <div class="container mx-auto px-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
      <div>
        <h3 class="text-xl font-bold mb-4" data-t="footContact">Contact Information</h3>
        <div class="space-y-2 text-gray-200">
          <p>&#128222; <a href="tel:5033679714" class="hover:text-amber-300">(503) 367-9714</a></p>
          <p>&#9993;&#65039; <a href="mailto:oregontirespdx@gmail.com">oregontirespdx@gmail.com</a></p>
          <p>&#128205; 8536 SE 82nd Ave, Portland, OR 97266</p>
          <p>&#128336; <span data-t="footHours">Mon-Sat 7AM-7PM</span></p>
        </div>
        <div class="mt-4 space-y-2">
          <a href="https://www.facebook.com/61571913202998/" target="_blank" class="inline-block text-gray-200 hover:text-amber-300 mr-3">Facebook</a>
          <a href="https://www.instagram.com/oregontires" target="_blank" class="inline-block text-gray-200 hover:text-amber-300">Instagram</a>
        </div>
      </div>
      <div>
        <h3 class="text-xl font-bold mb-4" data-t="footServices">Services</h3>
        <ul class="space-y-1 text-gray-200">
          <li><a href="/tire-installation" class="hover:text-amber-300 transition" data-t="footTireInstall">Tire Installation</a></li>
          <li><a href="/tire-repair" class="hover:text-amber-300 transition" data-t="footTireRepair">Tire Repair</a></li>
          <li><a href="/wheel-alignment" class="hover:text-amber-300 transition" data-t="footAlignment">Wheel Alignment</a></li>
          <li><a href="/brake-service" class="hover:text-amber-300 transition" data-t="footBrakes">Brake Service</a></li>
          <li><a href="/oil-change" class="hover:text-amber-300 transition" data-t="footOilChange">Oil Change</a></li>
          <li><a href="/engine-diagnostics" class="hover:text-amber-300 transition" data-t="footDiagnostics">Engine Diagnostics</a></li>
          <li><a href="/suspension-repair" class="hover:text-amber-300 transition" data-t="footSuspension">Suspension Repair</a></li>
          <li><a href="/fleet-services" class="hover:text-amber-300 transition" data-t="footFleet">Fleet Services</a></li>
        </ul>
      </div>
      <div>
        <h3 class="text-xl font-bold mb-4" data-t="footQuickLinks">Quick Links</h3>
        <ul class="space-y-1 text-gray-200">
          <li><a href="/financing" class="hover:text-amber-300 transition" data-t="footFinancing">Financing</a></li>
          <li><a href="/guarantee" class="hover:text-amber-300 transition" data-t="footGuarantee">Our Guarantee</a></li>
          <li><a href="/why-us" class="hover:text-amber-300 transition" data-t="footWhyUs">Why Choose Us</a></li>
          <li><a href="/care-plan" class="hover:text-amber-300 transition" data-t="footCarePlan">Care Plan</a></li>
          <li><a href="/promotions" class="hover:text-amber-300 transition" data-t="footPromotions">Promotions</a></li>
          <li><a href="/blog" class="hover:text-amber-300 transition" data-t="footBlog">Blog</a></li>
          <li><a href="/faq" class="hover:text-amber-300 transition" data-t="footFaq">FAQ</a></li>
          <li><a href="/reviews" class="hover:text-amber-300 transition" data-t="footReviews">Reviews</a></li>
        </ul>
      </div>
      <div>
        <h3 class="text-xl font-bold mb-4" data-t="footServiceAreas">Service Areas</h3>
        <ul class="space-y-1 text-gray-200">
          <li><a href="/service-areas" class="hover:text-amber-300 transition" data-t="footAllAreas">All Service Areas</a></li>
          <li><a href="/tires-se-portland" class="hover:text-amber-300 transition">SE Portland</a></li>
          <li><a href="/tires-foster-powell" class="hover:text-amber-300 transition">Foster-Powell</a></li>
          <li><a href="/tires-woodstock" class="hover:text-amber-300 transition">Woodstock</a></li>
          <li><a href="/tires-lents" class="hover:text-amber-300 transition">Lents</a></li>
          <li><a href="/tires-mt-scott" class="hover:text-amber-300 transition">Mt. Scott</a></li>
          <li><a href="/tires-happy-valley" class="hover:text-amber-300 transition">Happy Valley</a></li>
          <li><a href="/tires-clackamas" class="hover:text-amber-300 transition">Clackamas</a></li>
          <li><a href="/tires-milwaukie" class="hover:text-amber-300 transition">Milwaukie</a></li>
        </ul>
      </div>
    </div>
    <div class="border-t border-green-600 mt-8 pt-8 text-center text-gray-300">
      <p data-t="footCopyright">&copy; 2026 Oregon Tires Auto Care. All rights reserved.</p>
      <p class="mt-3 text-xs text-gray-300">Powered by <a href="https://1vsM.com" target="_blank" rel="noopener noreferrer" class="text-amber-200 hover:text-amber-100 transition-colors">1vsM.com</a></p>
    </div>
  </div>
</footer>
<script src="/assets/js/scroll-reveal.js" defer></script>
<script>
(function(){
  var nav = {
    navHome:{ en:'Home', es:'Inicio' },
    navServices:{ en:'Services', es:'Servicios' },
    navAbout:{ en:'About', es:'Nosotros' },
    navReviews:{ en:'Reviews', es:'Rese\u00f1as' },
    navContact:{ en:'Contact', es:'Contacto' },
    navBlog:{ en:'Blog', es:'Blog' },
    navFinancing:{ en:'Financing', es:'Financiamiento' },
    navMore:{ en:'More', es:'M\u00e1s' },
    navWhyUs:{ en:'Why Choose Us', es:'Por Qu\u00e9 Elegirnos' },
    navGuarantee:{ en:'Our Guarantee', es:'Nuestra Garant\u00eda' },
    navCarePlan:{ en:'Care Plan', es:'Plan de Cuidado' },
    navServiceAreas:{ en:'Service Areas', es:'\u00c1reas de Servicio' },
    navPromotions:{ en:'Promotions', es:'Promociones' },
    navFaq:{ en:'FAQ', es:'Preguntas Frecuentes' },
    navSchedule:{ en:'Schedule Service', es:'Agendar Servicio' },
    topHours:{ en:'\uD83D\uDD50 Mon-Sat 7AM-7PM', es:'\uD83D\uDD50 Lun-S\u00e1b 7AM-7PM' },
    footContact:{ en:'Contact Information', es:'Informaci\u00f3n de Contacto' },
    footHours:{ en:'Mon-Sat 7AM-7PM', es:'Lun-S\u00e1b 7AM-7PM' },
    footServices:{ en:'Services', es:'Servicios' },
    footTireInstall:{ en:'Tire Installation', es:'Instalaci\u00f3n de Llantas' },
    footTireRepair:{ en:'Tire Repair', es:'Reparaci\u00f3n de Llantas' },
    footAlignment:{ en:'Wheel Alignment', es:'Alineaci\u00f3n' },
    footBrakes:{ en:'Brake Service', es:'Servicio de Frenos' },
    footOilChange:{ en:'Oil Change', es:'Cambio de Aceite' },
    footDiagnostics:{ en:'Engine Diagnostics', es:'Diagn\u00f3stico de Motor' },
    footSuspension:{ en:'Suspension Repair', es:'Reparaci\u00f3n de Suspensi\u00f3n' },
    footFleet:{ en:'Fleet Services', es:'Servicios de Flotilla' },
    footQuickLinks:{ en:'Quick Links', es:'Enlaces R\u00e1pidos' },
    footFinancing:{ en:'Financing', es:'Financiamiento' },
    footGuarantee:{ en:'Our Guarantee', es:'Nuestra Garant\u00eda' },
    footWhyUs:{ en:'Why Choose Us', es:'Por Qu\u00e9 Elegirnos' },
    footCarePlan:{ en:'Care Plan', es:'Plan de Cuidado' },
    footPromotions:{ en:'Promotions', es:'Promociones' },
    footBlog:{ en:'Blog', es:'Blog' },
    footFaq:{ en:'FAQ', es:'Preguntas Frecuentes' },
    footReviews:{ en:'Reviews', es:'Rese\u00f1as' },
    footServiceAreas:{ en:'Service Areas', es:'\u00c1reas de Servicio' },
    footAllAreas:{ en:'All Service Areas', es:'Todas las \u00c1reas' },
    footFollow:{ en:'Follow Us', es:'S\u00edguenos' },
    footCopyright:{ en:'\u00a9 2026 Oregon Tires Auto Care. All rights reserved.', es:'\u00a9 2026 Oregon Tires Auto Care. Todos los derechos reservados.' }
  };
  var lang = window.currentLang;
  if (!lang) {
    try { var p = new URLSearchParams(window.location.search); lang = p.get('lang'); } catch(e){}
    if (!lang) { try { lang = localStorage.getItem('oregontires_lang'); } catch(e){} }
    if (!lang) { lang = (navigator.language||'').startsWith('es') ? 'es' : 'en'; }
  }
  // Update lang toggle button text
  var langBtn = document.getElementById('lang-toggle');
  if (langBtn) langBtn.textContent = lang === 'es' ? '\uD83C\uDF10 EN' : '\uD83C\uDF10 ES';
  // Apply nav/footer translations
  function applyNavTranslations(l) {
    document.querySelectorAll('[data-t]').forEach(function(el){
      var k = el.getAttribute('data-t');
      if (nav[k] && nav[k][l]) el.textContent = nav[k][l];
    });
  }
  if (lang === 'es') applyNavTranslations('es');
  // Language toggle function for header button
  window.__toggleLang = function() {
    var newLang = (localStorage.getItem('oregontires_lang') || 'en') === 'es' ? 'en' : 'es';
    localStorage.setItem('oregontires_lang', newLang);
    // Reload with new lang param to trigger full page translation
    var url = new URL(window.location.href);
    url.searchParams.set('lang', newLang);
    window.location.href = url.toString();
  };
})();
</script>
