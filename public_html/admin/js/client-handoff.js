/**
 * Oregon Tires — Client Setup & Ownership Guide (Bilingual)
 * Interactive checklist for the client to complete platform ownership.
 * Renders inside the admin Docs tab.
 */
(function() {
  'use strict';

  var STORAGE_KEY = 'ot_handoff_checklist';
  function isEs() { return typeof currentLang !== 'undefined' && currentLang === 'es'; }

  function getChecked() {
    try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}'); } catch(e) { return {}; }
  }
  function setChecked(id, val) {
    var data = getChecked();
    data[id] = val;
    localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
  }

  // ─── Bilingual Content ────────────────────────────────────────────────────
  var sections = [
    {
      id: 'hosting',
      icon: '🖥️',
      title: { en: 'Web Hosting', es: 'Hospedaje Web' },
      description: {
        en: 'Get your own hosting account. All recommended hosts include free SSL, email, daily backups, and enough storage for your website and data.',
        es: 'Obtenga su propia cuenta de hospedaje. Todos los hosts recomendados incluyen SSL gratuito, correo electrónico, respaldos diarios y suficiente almacenamiento.'
      },
      items: [
        {
          id: 'host-choose',
          text: { en: 'Sign up for a web host', es: 'Regístrese en un servicio de hospedaje' },
          detail: {
            en: 'Pick one — all include free SSL, email, daily backups, and plenty of storage for your website, photos, customer data, and years of backup history. Click a link below to sign up:\n\n• SiteGround ($15/mo, 20GB) — Best support, fastest. Recommended.\n• A2 Hosting ($5/mo, 100GB) — Best value for growing businesses.\n• Hostinger ($3/mo, 100GB) — Budget-friendly, includes everything.',
            es: 'Elija uno — todos incluyen SSL gratis, correo, respaldos diarios y amplio almacenamiento. Haga clic en un enlace para registrarse:\n\n• SiteGround ($15/mes, 20GB) — Mejor soporte, más rápido. Recomendado.\n• A2 Hosting ($5/mes, 100GB) — Mejor valor para negocios en crecimiento.\n• Hostinger ($3/mes, 100GB) — Económico, incluye todo.'
          },
          links: [
            { label: { en: 'SiteGround (Recommended)', es: 'SiteGround (Recomendado)' }, url: 'https://www.siteground.com/web-hosting.htm' },
            { label: { en: 'A2 Hosting (Best Value)', es: 'A2 Hosting (Mejor Valor)' }, url: 'https://www.a2hosting.com/web-hosting/' },
            { label: { en: 'Hostinger (Budget)', es: 'Hostinger (Económico)' }, url: 'https://www.hostinger.com/web-hosting' }
          ]
        },
        {
          id: 'host-share',
          text: { en: 'Share hosting login with your developer', es: 'Comparta el acceso con su desarrollador' },
          detail: {
            en: 'After signing up, share your hosting login (cPanel URL, username, password) with your developer. They will handle all the technical setup — database, files, and configuration.',
            es: 'Después de registrarse, comparta su acceso de hospedaje (URL de cPanel, usuario y contraseña) con su desarrollador. Ellos se encargarán de toda la configuración técnica.'
          }
        },
        {
          id: 'host-email',
          text: { en: 'Set up business email accounts', es: 'Configure cuentas de correo del negocio' },
          detail: {
            en: 'In your hosting control panel, create email accounts:\n• contact@oregon.tires (for customer inquiries)\n• info@oregon.tires (general business)\n\nYour hosting includes free email. Share the email passwords with your developer so they can connect the website\'s notification system.',
            es: 'En su panel de control, cree cuentas de correo:\n• contact@oregon.tires (para consultas de clientes)\n• info@oregon.tires (general del negocio)\n\nSu hospedaje incluye correo gratuito. Comparta las contraseñas con su desarrollador para conectar el sistema de notificaciones.'
          }
        },
      ]
    },
    {
      id: 'domain',
      icon: '🌐',
      title: { en: 'Domain Ownership', es: 'Propiedad del Dominio' },
      description: {
        en: 'Transfer the oregon.tires domain into your name so you fully own it.',
        es: 'Transfiera el dominio oregon.tires a su nombre para que sea completamente suyo.'
      },
      items: [
        {
          id: 'dom-registrar',
          text: { en: 'Create a domain registrar account', es: 'Cree una cuenta de registrador de dominios' },
          detail: {
            en: 'Sign up at one of these registrars (both are reputable and affordable). This is where your domain will live — like a title deed for your web address.',
            es: 'Regístrese en uno de estos registradores (ambos son confiables y económicos). Aquí es donde vivirá su dominio — como la escritura de su dirección web.'
          },
          links: [
            { label: { en: 'Namecheap', es: 'Namecheap' }, url: 'https://www.namecheap.com/' },
            { label: { en: 'Cloudflare Registrar', es: 'Cloudflare Registrar' }, url: 'https://www.cloudflare.com/products/registrar/' }
          ]
        },
        {
          id: 'dom-transfer',
          text: { en: 'Approve the domain transfer', es: 'Apruebe la transferencia del dominio' },
          detail: {
            en: 'Your developer will send you a transfer code. Enter it in your registrar account to start the transfer. You\'ll receive a confirmation email — click the approval link. The transfer takes 24-48 hours.',
            es: 'Su desarrollador le enviará un código de transferencia. Ingréselo en su cuenta del registrador. Recibirá un correo de confirmación — haga clic en el enlace de aprobación. La transferencia toma 24-48 horas.'
          }
        },
        {
          id: 'dom-verify',
          text: { en: 'Verify the website loads correctly', es: 'Verifique que el sitio web cargue correctamente' },
          detail: {
            en: 'After the transfer completes, visit https://oregon.tires and check:\n• The site loads normally\n• The lock icon appears in the browser (SSL is active)\n• Both English and Spanish versions work',
            es: 'Después de completar la transferencia, visite https://oregon.tires y verifique:\n• El sitio carga normalmente\n• Aparece el candado en el navegador (SSL activo)\n• Las versiones en inglés y español funcionan'
          }
        },
      ]
    },
    {
      id: 'accounts',
      icon: '🔑',
      title: { en: 'Business Accounts to Create', es: 'Cuentas de Negocio a Crear' },
      description: {
        en: 'Create these accounts under your business name. Share the login credentials with your developer so they can connect everything to your website.',
        es: 'Cree estas cuentas a nombre de su negocio. Comparta las credenciales con su desarrollador para que conecten todo a su sitio web.'
      },
      items: [
        {
          id: 'acct-google',
          text: { en: 'Google account (Reviews + Analytics + Login)', es: 'Cuenta de Google (Reseñas + Analíticas + Login)' },
          detail: {
            en: 'Sign in with your business Gmail at the link below. This one account powers three features on your website:\n\n1. Google Reviews — your reviews displayed on your website and admin dashboard\n2. Google Analytics — tracks visitors, page views, and where customers come from\n3. "Login with Google" — lets customers sign in easily\n\nAfter creating the project, share your Google account login with your developer so they can connect Reviews, Analytics, and customer login.',
            es: 'Inicie sesión con su Gmail del negocio en el enlace de abajo. Esta cuenta activa tres funciones en su sitio web:\n\n1. Reseñas de Google — sus reseñas mostradas en su sitio y panel de administración\n2. Google Analytics — rastrea visitantes, vistas de páginas y de dónde vienen los clientes\n3. "Iniciar sesión con Google" — permite a los clientes iniciar sesión fácilmente\n\nDespués de crear el proyecto, comparta su acceso de Google con su desarrollador para conectar Reseñas, Analíticas e inicio de sesión.'
          },
          links: [
            { label: { en: 'Open Google Cloud Console', es: 'Abrir Google Cloud Console' }, url: 'https://console.cloud.google.com/' },
            { label: { en: 'Google Analytics Dashboard', es: 'Panel de Google Analytics' }, url: 'https://analytics.google.com/' }
          ],
          category: { en: 'Required', es: 'Requerido' }
        },
        {
          id: 'acct-whatsapp',
          text: { en: 'WhatsApp Business (FREE customer messaging)', es: 'WhatsApp Business (mensajes a clientes GRATIS)' },
          detail: {
            en: 'Your website already has WhatsApp messaging built in — you just need to create the account to activate it.\n\nStep 1: Create a Meta Business account at the first link below\n\nStep 2: Create an app at the second link → click "My Apps" → "Create App" → choose "Business" → add the "WhatsApp" product\n\nStep 3: In the WhatsApp section, register your shop\'s phone number\n\nStep 4: Share these with your developer:\n• WhatsApp Phone Number ID\n• Access Token\n\nOnce connected, customers automatically receive WhatsApp messages for:\n✓ Appointment reminders\n✓ Estimate approvals\n✓ Vehicle ready for pickup\n✓ Repair status updates\n\nFREE: 1,000 messages/month (plenty for most shops).',
            es: 'Su sitio web ya tiene WhatsApp integrado — solo necesita crear la cuenta para activarlo.\n\nPaso 1: Cree una cuenta Meta Business en el primer enlace de abajo\n\nPaso 2: Cree una app en el segundo enlace → "Mis Apps" → "Crear App" → tipo "Business" → agregar "WhatsApp"\n\nPaso 3: En la sección de WhatsApp, registre el número del taller\n\nPaso 4: Comparta estos datos con su desarrollador:\n• WhatsApp Phone Number ID\n• Access Token\n\nUna vez conectado, los clientes reciben automáticamente mensajes de WhatsApp para:\n✓ Recordatorios de citas\n✓ Aprobación de presupuestos\n✓ Vehículo listo para recoger\n✓ Actualizaciones de reparación\n\nGRATIS: 1,000 mensajes/mes (suficiente para la mayoría de talleres).'
          },
          links: [
            { label: { en: 'Create Meta Business Account', es: 'Crear Cuenta Meta Business' }, url: 'https://business.facebook.com/' },
            { label: { en: 'Meta Developer Portal (Create App)', es: 'Portal de Desarrolladores Meta (Crear App)' }, url: 'https://developers.facebook.com/apps/' }
          ],
          category: { en: 'Required', es: 'Requerido' }
        },
        {
          id: 'acct-paypal',
          text: { en: 'PayPal Business (online payments)', es: 'PayPal Business (pagos en línea)' },
          detail: {
            en: 'Create a PayPal Business account using your business email. This lets customers pay for care plans and services online.\n\nAfter signing up, go to the Developer Dashboard (second link) to get your API credentials (Client ID and Secret). Share both with your developer.',
            es: 'Cree una cuenta PayPal Business con su correo del negocio. Esto permite a los clientes pagar planes y servicios en línea.\n\nDespués de registrarse, vaya al Panel de Desarrollador (segundo enlace) para obtener sus credenciales API (Client ID y Secret). Comparta ambos con su desarrollador.'
          },
          links: [
            { label: { en: 'Sign Up for PayPal Business', es: 'Registrarse en PayPal Business' }, url: 'https://www.paypal.com/business' },
            { label: { en: 'PayPal Developer Dashboard', es: 'Panel de Desarrollador PayPal' }, url: 'https://developer.paypal.com/dashboard/applications' }
          ],
          category: { en: 'Required', es: 'Requerido' }
        },
        {
          id: 'acct-stripe',
          text: { en: 'Stripe (card payments online + in-person)', es: 'Stripe (pagos con tarjeta en línea + en persona)' },
          detail: {
            en: 'Create a Stripe account to accept credit/debit card payments on your website and at the shop counter.\n\nAfter signing up, go to Developers → API Keys in your Stripe Dashboard. Share the Secret Key with your developer.\n\nStripe also supports in-person payments with your existing card reader — your developer can connect it.\n\nProcessing fees: 2.9% + 30¢ online, 2.7% + 5¢ in-person.',
            es: 'Cree una cuenta Stripe para aceptar pagos con tarjeta de crédito/débito en su sitio web y en el mostrador.\n\nDespués de registrarse, vaya a Developers → API Keys en su Dashboard. Comparta la Secret Key con su desarrollador.\n\nStripe también soporta pagos en persona con su lector de tarjetas existente — su desarrollador puede conectarlo.\n\nComisiones: 2.9% + 30¢ en línea, 2.7% + 5¢ en persona.'
          },
          links: [
            { label: { en: 'Sign Up for Stripe', es: 'Registrarse en Stripe' }, url: 'https://dashboard.stripe.com/register' },
            { label: { en: 'Stripe Dashboard (API Keys)', es: 'Dashboard de Stripe (API Keys)' }, url: 'https://dashboard.stripe.com/apikeys' }
          ],
          category: { en: 'Recommended', es: 'Recomendado' }
        },
        {
          id: 'acct-gsc',
          text: { en: 'Google Search Console (SEO)', es: 'Google Search Console (SEO)' },
          detail: {
            en: 'Sign in with your business Gmail and add oregon.tires as a property. This shows how your site appears in Google search results and helps your developer improve your ranking.\n\nFREE — takes 2 minutes to set up.',
            es: 'Inicie sesión con su Gmail del negocio y agregue oregon.tires como propiedad. Muestra cómo aparece su sitio en Google y ayuda a su desarrollador a mejorar su posicionamiento.\n\nGRATIS — toma 2 minutos configurar.'
          },
          links: [
            { label: { en: 'Open Google Search Console', es: 'Abrir Google Search Console' }, url: 'https://search.google.com/search-console/' }
          ],
          category: { en: 'Recommended', es: 'Recomendado' }
        },
      ]
    },
  ];

  // ─── Render ───────────────────────────────────────────────────────────────
  function txt(obj) {
    if (typeof obj === 'string') return obj;
    return isEs() ? (obj.es || obj.en) : obj.en;
  }

  function renderHandoff(container) {
    container.textContent = '';
    var checked = getChecked();

    var totalItems = 0, checkedItems = 0;
    sections.forEach(function(s) { s.items.forEach(function(item) { totalItems++; if (checked[item.id]) checkedItems++; }); });
    var pct = totalItems ? Math.round((checkedItems / totalItems) * 100) : 0;

    var header = document.createElement('div');
    header.className = 'mb-6';
    var h2 = document.createElement('h2');
    h2.className = 'text-2xl font-bold text-gray-900 dark:text-white mb-2';
    h2.textContent = isEs() ? 'Oregon Tires — Guía de Configuración y Propiedad' : 'Oregon Tires — Setup & Ownership Guide';
    header.appendChild(h2);
    var desc = document.createElement('p');
    desc.className = 'text-sm text-gray-500 dark:text-gray-400 mb-4';
    desc.textContent = isEs()
      ? 'Todo lo que necesita para ser dueño completo de su sitio web Oregon Tires. Marque cada elemento al completarlo — su progreso se guarda automáticamente.'
      : 'Everything you need to fully own your Oregon Tires website. Check off each item as you complete it — your progress is saved automatically.';
    header.appendChild(desc);

    var progWrap = document.createElement('div');
    progWrap.className = 'flex items-center gap-3';
    var progBar = document.createElement('div');
    progBar.className = 'flex-1 h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden';
    var progFill = document.createElement('div');
    progFill.className = 'h-full rounded-full transition-all ' + (pct === 100 ? 'bg-green-500' : 'bg-brand');
    progFill.style.width = pct + '%';
    progFill.setAttribute('data-progress-fill', '');
    progBar.appendChild(progFill);
    progWrap.appendChild(progBar);
    var progLabel = document.createElement('span');
    progLabel.className = 'text-sm font-bold ' + (pct === 100 ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-300');
    progLabel.textContent = checkedItems + '/' + totalItems + ' (' + pct + '%)';
    progLabel.setAttribute('data-progress-label', '');
    progWrap.appendChild(progLabel);
    header.appendChild(progWrap);
    container.appendChild(header);

    sections.forEach(function(section) {
      var sectionChecked = section.items.filter(function(i) { return checked[i.id]; }).length;
      var sectionTotal = section.items.length;
      var allDone = sectionChecked === sectionTotal;

      var details = document.createElement('details');
      details.className = 'border dark:border-gray-700 rounded-xl overflow-hidden mb-3' + (allDone ? ' border-green-300 dark:border-green-700' : '');
      if (!allDone && sectionChecked === 0) details.open = (section.id === 'hosting');

      var summary = document.createElement('summary');
      summary.className = 'px-5 py-3 cursor-pointer select-none flex items-center justify-between ' + (allDone ? 'bg-green-50 dark:bg-green-900/20' : 'bg-gray-50 dark:bg-gray-800/50') + ' hover:bg-gray-100 dark:hover:bg-gray-700/50 transition';
      var left = document.createElement('div');
      left.className = 'flex items-center gap-3';
      var icon = document.createElement('span');
      icon.className = 'text-xl';
      icon.textContent = allDone ? '\u2705' : section.icon;
      left.appendChild(icon);
      var titleSpan = document.createElement('span');
      titleSpan.className = 'font-semibold text-gray-900 dark:text-white';
      titleSpan.textContent = txt(section.title);
      left.appendChild(titleSpan);
      summary.appendChild(left);
      var badge = document.createElement('span');
      badge.className = 'text-xs font-medium px-2 py-1 rounded-full ' + (allDone ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400' : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300');
      badge.textContent = sectionChecked + '/' + sectionTotal;
      summary.appendChild(badge);
      details.appendChild(summary);

      var body = document.createElement('div');
      body.className = 'p-4 space-y-2';

      if (section.description) {
        var descP = document.createElement('p');
        descP.className = 'text-sm text-gray-500 dark:text-gray-400 mb-3';
        descP.textContent = txt(section.description);
        body.appendChild(descP);
      }

      section.items.forEach(function(item) {
        var row = document.createElement('div');
        row.className = 'flex items-start gap-3 p-3 rounded-lg ' + (checked[item.id] ? 'bg-green-50 dark:bg-green-900/10' : 'bg-white dark:bg-gray-800') + ' border dark:border-gray-700';

        var cb = document.createElement('input');
        cb.type = 'checkbox';
        cb.checked = !!checked[item.id];
        cb.className = 'mt-1 w-5 h-5 rounded text-green-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 shrink-0 cursor-pointer';
        cb.addEventListener('change', function() {
          setChecked(item.id, cb.checked);
          row.className = 'flex items-start gap-3 p-3 rounded-lg ' + (cb.checked ? 'bg-green-50 dark:bg-green-900/10' : 'bg-white dark:bg-gray-800') + ' border dark:border-gray-700';
          label.className = 'text-sm font-medium ' + (cb.checked ? 'text-green-700 dark:text-green-400 line-through' : 'text-gray-900 dark:text-white');
          var nc = getChecked();
          var sc = section.items.filter(function(i) { return nc[i.id]; }).length;
          badge.textContent = sc + '/' + sectionTotal;
          var ad = sc === sectionTotal;
          badge.className = 'text-xs font-medium px-2 py-1 rounded-full ' + (ad ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400' : 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300');
          icon.textContent = ad ? '\u2705' : section.icon;
          var tc = 0, cc = 0;
          sections.forEach(function(s) { s.items.forEach(function(it) { tc++; if (nc[it.id]) cc++; }); });
          var p = tc ? Math.round((cc / tc) * 100) : 0;
          var pf = container.querySelector('[data-progress-fill]');
          var pl = container.querySelector('[data-progress-label]');
          if (pf) pf.style.width = p + '%';
          if (pl) pl.textContent = cc + '/' + tc + ' (' + p + '%)';
        });
        row.appendChild(cb);

        var content = document.createElement('div');
        content.className = 'flex-1 min-w-0';

        var labelRow = document.createElement('div');
        labelRow.className = 'flex items-center gap-2 flex-wrap';
        var label = document.createElement('span');
        label.className = 'text-sm font-medium ' + (checked[item.id] ? 'text-green-700 dark:text-green-400 line-through' : 'text-gray-900 dark:text-white');
        label.textContent = txt(item.text);
        labelRow.appendChild(label);
        if (item.category) {
          var catBadge = document.createElement('span');
          var catColors = {
            Required: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            Requerido: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            Recommended: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            Recomendado: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            Optional: 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400',
            Opcional: 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400',
          };
          var catText = txt(item.category);
          catBadge.className = 'text-[10px] px-1.5 py-0.5 rounded font-medium ' + (catColors[catText] || catColors.Optional);
          catBadge.textContent = catText;
          labelRow.appendChild(catBadge);
        }
        content.appendChild(labelRow);

        if (item.detail) {
          var detailP = document.createElement('p');
          detailP.className = 'text-xs text-gray-500 dark:text-gray-400 mt-1 whitespace-pre-line';
          detailP.textContent = txt(item.detail);
          content.appendChild(detailP);
        }
        if (item.links && item.links.length) {
          var linksDiv = document.createElement('div');
          linksDiv.className = 'flex flex-wrap gap-2 mt-2';
          item.links.forEach(function(link) {
            var a = document.createElement('a');
            a.href = link.url;
            a.target = '_blank';
            a.rel = 'noopener';
            a.className = 'inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/50 border border-blue-200 dark:border-blue-800 transition';
            a.textContent = txt(link.label) + ' \u2197';
            linksDiv.appendChild(a);
          });
          content.appendChild(linksDiv);
        }

        row.appendChild(content);
        body.appendChild(row);
      });

      details.appendChild(body);
      container.appendChild(details);
    });

    var resetWrap = document.createElement('div');
    resetWrap.className = 'mt-6 text-center';
    var resetBtn = document.createElement('button');
    resetBtn.className = 'text-xs text-gray-400 dark:text-gray-500 hover:text-red-500 transition';
    resetBtn.textContent = isEs() ? 'Reiniciar todas las casillas' : 'Reset all checkboxes';
    resetBtn.addEventListener('click', function() {
      if (confirm(isEs() ? '¿Reiniciar todo el progreso?' : 'Reset all checklist progress?')) {
        localStorage.removeItem(STORAGE_KEY);
        renderHandoff(container);
      }
    });
    resetWrap.appendChild(resetBtn);
    container.appendChild(resetWrap);
  }

  window.renderClientHandoff = renderHandoff;
})();
