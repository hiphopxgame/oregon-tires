/**
 * Oregon Tires — Client Setup & Ownership Guide (Bilingual)
 * Interactive checklist for the client to complete platform ownership.
 * Renders inside the admin Docs tab.
 */
(function() {
  'use strict';

  var STORAGE_KEY = 'ot_handoff_checklist';
  function isEs() { return typeof currentLang !== 'undefined' && currentLang === 'es'; }

  // Items confirmed complete by developer — auto-checked on first load
  var DEV_CONFIRMED = [
    'gc-project',           // Google Cloud project "Oregon Tires" (oregon-tires, #734338521474)
    'gc-enable-apis',       // Maps, Places, Calendar, My Business APIs enabled
    'gc-oauth',             // OAuth credentials configured (Sign in with Google working)
    'gc-api-key',           // API key active (AIzaSy...5Lw) — Places API verified working
    'gc-service-account',   // Service account created + JSON installed on server + auth verified
    'gc-calendar',          // Calendar shared with service account, read+write verified
  ];

  function getChecked() {
    try {
      var data = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
      // Auto-mark developer-confirmed items
      var changed = false;
      DEV_CONFIRMED.forEach(function(id) {
        if (!data[id]) { data[id] = true; changed = true; }
      });
      if (changed) localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
      return data;
    } catch(e) { return {}; }
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
        en: 'Get your own hosting account. InMotion Hosting includes cPanel, free SSL, email, daily backups, SSH access, and unlimited cron jobs — everything your website needs.',
        es: 'Obtenga su propia cuenta de hospedaje. InMotion Hosting incluye cPanel, SSL gratuito, correo electronico, respaldos diarios, acceso SSH y tareas cron ilimitadas — todo lo que su sitio web necesita.'
      },
      items: [
        {
          id: 'host-choose',
          text: { en: 'Sign up for InMotion Hosting', es: 'Registrese en InMotion Hosting' },
          detail: {
            en: 'Sign up for InMotion Hosting shared hosting (link below). This is the recommended host for your website.\n\nWhat you get:\n- cPanel control panel (easy website management)\n- Free SSL certificate (secure https://)\n- Free email accounts (contact@oregon.tires, etc.)\n- Daily backups\n- SSH access\n- Unlimited cron jobs (your website runs 8 automated tasks)\n- US-based datacenter\n- Phone + live chat support\n\nChoose any shared hosting plan — they all include what your website needs.',
            es: 'Registrese en InMotion Hosting hospedaje compartido (enlace abajo). Este es el host recomendado para su sitio web.\n\nLo que obtiene:\n- Panel de control cPanel (gestion facil del sitio)\n- Certificado SSL gratuito (https:// seguro)\n- Cuentas de correo gratuitas (contact@oregon.tires, etc.)\n- Respaldos diarios\n- Acceso SSH\n- Tareas cron ilimitadas (su sitio ejecuta 8 tareas automatizadas)\n- Centro de datos en EE.UU.\n- Soporte por telefono + chat en vivo\n\nElija cualquier plan de hospedaje compartido — todos incluyen lo que su sitio web necesita.'
          },
          links: [
            { label: { en: 'InMotion Hosting (Shared Hosting)', es: 'InMotion Hosting (Hospedaje Compartido)' }, url: 'https://www.inmotionhosting.com/shared-hosting' }
          ]
        },
        {
          id: 'host-share',
          text: { en: 'Share cPanel login with your developer', es: 'Comparta el acceso de cPanel con su desarrollador' },
          detail: {
            en: 'After signing up, share your cPanel login (URL, username, password) with your developer. They will handle all the technical setup — database, files, cron jobs, email, and SSL configuration.\n\nYour cPanel URL is usually: yourdomain.com/cpanel or yourdomain.com:2083',
            es: 'Después de registrarse, comparta su acceso de cPanel (URL, usuario y contraseña) con su desarrollador. Ellos se encargarán de toda la configuración técnica — base de datos, archivos, tareas cron, correo y SSL.\n\nSu URL de cPanel suele ser: sudominio.com/cpanel o sudominio.com:2083'
          }
        },
        {
          id: 'host-email',
          text: { en: 'Set up business email accounts in cPanel', es: 'Configure cuentas de correo en cPanel' },
          detail: {
            en: 'Your hosting includes free email with cPanel. In your cPanel dashboard, go to Email → Email Accounts and create:\n\n• contact@oregon.tires — for customer inquiries and website notifications\n• info@oregon.tires — for general business communication\n\nSteps:\n1. Log in to cPanel\n2. Go to Email → Email Accounts\n3. Click "Create" and enter the email address and password\n4. Share the email passwords with your developer so they can connect the website\'s notification system\n\nYou can access your email at: webmail.oregon.tires or via cPanel → Email → Webmail',
            es: 'Su hospedaje incluye correo gratuito con cPanel. En su panel de cPanel, vaya a Correo → Cuentas de Correo y cree:\n\n• contact@oregon.tires — para consultas de clientes y notificaciones del sitio\n• info@oregon.tires — para comunicación general del negocio\n\nPasos:\n1. Inicie sesión en cPanel\n2. Vaya a Correo → Cuentas de Correo\n3. Haga clic en "Crear" e ingrese la dirección y contraseña\n4. Comparta las contraseñas con su desarrollador para conectar el sistema de notificaciones\n\nPuede acceder a su correo en: webmail.oregon.tires o en cPanel → Correo → Webmail'
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
          text: { en: 'Create a Porkbun account', es: 'Cree una cuenta en Porkbun' },
          detail: {
            en: 'Sign up at Porkbun.com — this is the registrar that supports the .tires domain extension. Most registrars (Namecheap, Cloudflare, GoDaddy) do not support .tires domains, so Porkbun is required.\n\nPorkbun is reputable, affordable, and includes free WHOIS privacy. This is where your domain will live — like a title deed for your web address.',
            es: 'Regístrese en Porkbun.com — este es el registrador que soporta la extensión de dominio .tires. La mayoría de los registradores (Namecheap, Cloudflare, GoDaddy) no soportan dominios .tires, así que Porkbun es necesario.\n\nPorkbun es confiable, económico e incluye privacidad WHOIS gratis. Aquí es donde vivirá su dominio — como la escritura de su dirección web.'
          },
          links: [
            { label: { en: 'Porkbun (Required for .tires)', es: 'Porkbun (Requerido para .tires)' }, url: 'https://porkbun.com/' }
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
        en: 'Create these accounts under your business name. All Google-related setup (Analytics, Reviews, Sign-in, Calendar, Business Profile) is in the "Google Cloud Console Setup" section below.',
        es: 'Cree estas cuentas a nombre de su negocio. Toda la configuracion de Google (Analytics, Resenas, Login, Calendario, Perfil de Negocio) esta en la seccion "Configuracion de Google Cloud Console" mas abajo.'
      },
      items: [
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
          id: 'acct-stripe',
          text: { en: 'Stripe (card payments online + in-person)', es: 'Stripe (pagos con tarjeta en linea + en persona)' },
          detail: {
            en: 'Stripe is the recommended payment processor for your website. It handles credit/debit card payments both online and at the shop counter.\n\n1. Click the "Sign Up" link below and create an account with your business email\n2. Complete the business verification (Stripe will ask for basic business info)\n3. Once approved, click the "API Keys" link below\n4. Copy your Secret Key (starts with "sk_live_...")\n\n━━━ WHAT TO SHARE WITH YOUR DEVELOPER ━━━\n- Secret Key (sk_live_...)\n- Publishable Key (pk_live_...) — also on the same page\n\nStripe also supports in-person payments with a card reader — your developer can connect it.\n\nProcessing fees: 2.9% + 30¢ online, 2.7% + 5¢ in-person.',
            es: 'Stripe es el procesador de pagos recomendado para su sitio web. Maneja pagos con tarjeta de credito/debito tanto en linea como en el mostrador del taller.\n\n1. Haga clic en el enlace "Registrarse" abajo y cree una cuenta con su correo del negocio\n2. Complete la verificacion del negocio (Stripe pedira informacion basica)\n3. Una vez aprobado, haga clic en el enlace "API Keys" abajo\n4. Copie su Secret Key (empieza con "sk_live_...")\n\n━━━ QUE COMPARTIR CON SU DESARROLLADOR ━━━\n- Secret Key (sk_live_...)\n- Publishable Key (pk_live_...) — tambien en la misma pagina\n\nStripe tambien soporta pagos en persona con un lector de tarjetas — su desarrollador puede conectarlo.\n\nComisiones: 2.9% + 30¢ en linea, 2.7% + 5¢ en persona.'
          },
          links: [
            { label: { en: 'Sign Up for Stripe', es: 'Registrarse en Stripe' }, url: 'https://dashboard.stripe.com/register' },
            { label: { en: 'Stripe API Keys', es: 'Stripe API Keys' }, url: 'https://dashboard.stripe.com/apikeys' }
          ],
          category: { en: 'Required', es: 'Requerido' }
        },
        {
          id: 'acct-paypal',
          text: { en: 'PayPal Business (alternative payment option)', es: 'PayPal Business (opcion de pago alternativa)' },
          detail: {
            en: 'Optional — add PayPal as an additional payment method alongside Stripe. Some customers prefer PayPal.\n\n1. Click the "Sign Up" link below and create a PayPal Business account\n2. After signing up, click the "Developer Dashboard" link\n3. Under "Apps & Credentials", find your app or create one\n4. Copy the Client ID and Secret\n\n━━━ WHAT TO SHARE WITH YOUR DEVELOPER ━━━\n- PayPal Client ID\n- PayPal Secret\n\nNote: Stripe is the primary payment processor. PayPal is an optional add-on for customers who prefer it.',
            es: 'Opcional — agregue PayPal como metodo de pago adicional junto a Stripe. Algunos clientes prefieren PayPal.\n\n1. Haga clic en el enlace "Registrarse" abajo y cree una cuenta PayPal Business\n2. Despues de registrarse, haga clic en el enlace "Developer Dashboard"\n3. En "Apps & Credentials", encuentre su app o cree una\n4. Copie el Client ID y Secret\n\n━━━ QUE COMPARTIR CON SU DESARROLLADOR ━━━\n- PayPal Client ID\n- PayPal Secret\n\nNota: Stripe es el procesador de pagos principal. PayPal es un complemento opcional para clientes que lo prefieran.'
          },
          links: [
            { label: { en: 'Sign Up for PayPal Business', es: 'Registrarse en PayPal Business' }, url: 'https://www.paypal.com/business' },
            { label: { en: 'PayPal Developer Dashboard', es: 'Panel de Desarrollador PayPal' }, url: 'https://developer.paypal.com/dashboard/applications' }
          ],
          category: { en: 'Optional', es: 'Opcional' }
        },
      ]
    },
    {
      id: 'google-cloud',
      icon: '☁️',
      title: { en: 'Google Cloud Console Setup', es: 'Configuracion de Google Cloud Console' },
      description: {
        en: 'All Google services for your website are set up here: Reviews, Analytics, Sign in with Google, Calendar sync, Business Profile, and Search Console. Sign in with your business Gmail for every step. You never share your Google password — just API keys and access grants.',
        es: 'Todos los servicios de Google para su sitio web se configuran aqui: Resenas, Analytics, Inicio de sesion con Google, sincronizacion de Calendario, Perfil de Negocio y Search Console. Inicie sesion con su Gmail del negocio en cada paso. Nunca comparta su contrasena de Google — solo claves API y permisos de acceso.'
      },
      items: [
        {
          id: 'gc-project',
          text: { en: 'Create a Google Cloud project', es: 'Crear un proyecto en Google Cloud' },
          detail: {
            en: '\u2705 COMPLETED\n\nProject Name: Oregon Tires\nProject ID: oregon-tires\nProject Number: 734338521474\n\nThis project holds all API keys and credentials for your website.',
            es: '\u2705 COMPLETADO\n\nNombre del Proyecto: Oregon Tires\nID del Proyecto: oregon-tires\nN\u00famero del Proyecto: 734338521474\n\nEste proyecto contiene todas las claves API y credenciales de su sitio web.'
          },
          links: [
            { label: { en: 'Google Cloud Console', es: 'Google Cloud Console' }, url: 'https://console.cloud.google.com/' }
          ],
          category: { en: 'Required', es: 'Requerido' }
        },
        {
          id: 'gc-enable-apis',
          text: { en: 'Enable required APIs', es: 'Habilitar APIs requeridas' },
          detail: {
            en: '\u2705 COMPLETED\n\nThe following APIs are enabled in project oregon-tires:\n\n\u2705 Maps API — enabled\n\u2705 Places API (New) — enabled & verified working (984 reviews fetched)\n\u2705 Google Calendar API — enabled (awaiting service account setup)\n\u2705 My Business API — enabled (awaiting service account setup)\n\nAll APIs are active and within free tier limits.',
            es: '\u2705 COMPLETADO\n\nLas siguientes APIs est\u00e1n habilitadas en el proyecto oregon-tires:\n\n\u2705 Maps API — habilitada\n\u2705 Places API (New) — habilitada y verificada (984 rese\u00f1as obtenidas)\n\u2705 Google Calendar API — habilitada (pendiente cuenta de servicio)\n\u2705 My Business API — habilitada (pendiente cuenta de servicio)\n\nTodas las APIs est\u00e1n activas dentro de los l\u00edmites gratuitos.'
          },
          links: [
            { label: { en: '1. Enable Places API (New)', es: '1. Habilitar Places API (New)' }, url: 'https://console.cloud.google.com/apis/library/places-backend.googleapis.com' },
            { label: { en: '2. Enable Places API', es: '2. Habilitar Places API' }, url: 'https://console.cloud.google.com/apis/library/maps-backend.googleapis.com' },
            { label: { en: '3. Enable Calendar API', es: '3. Habilitar Calendar API' }, url: 'https://console.cloud.google.com/apis/library/calendar-json.googleapis.com' },
            { label: { en: '4. Enable My Business API', es: '4. Habilitar My Business API' }, url: 'https://console.cloud.google.com/apis/library/mybusinessbusinessinformation.googleapis.com' },
            { label: { en: 'Verify: Enabled APIs list', es: 'Verificar: Lista de APIs habilitadas' }, url: 'https://console.cloud.google.com/apis/dashboard' }
          ],
          category: { en: 'Required', es: 'Requerido' }
        },
        {
          id: 'gc-oauth',
          text: { en: 'Create OAuth credentials (Sign in with Google)', es: 'Crear credenciales OAuth (Iniciar sesion con Google)' },
          detail: {
            en: 'This is what powers the "Sign in with Google" button on your website. Customers can create accounts and log in with one click using their Google account.\n\n━━━ STEP 1: Set up the consent screen ━━━\nThis is the screen customers see when they click "Sign in with Google".\n\n1. Click the "OAuth Consent Screen" link below\n2. Choose "External" user type > click "Create"\n3. Fill in these fields:\n   - App name: Oregon Tires Auto Care\n   - User support email: (select your business email)\n   - App logo: (optional — you can upload your shop logo)\n   - App domain > Application home page: https://oregon.tires\n   - Authorized domains: oregon.tires\n   - Developer contact email: your business email\n4. Click "Save and Continue"\n5. On the Scopes page, click "Add or Remove Scopes" and add:\n   - openid\n   - email\n   - profile\n   Then click "Update" > "Save and Continue"\n6. Skip the Test Users page > click "Save and Continue"\n7. On the Summary page, click "Back to Dashboard"\n8. Click "Publish App" > confirm to move from Testing to Production\n\n━━━ STEP 2: Create the OAuth Client ID ━━━\n1. Click the "Credentials Page" link below\n2. Click "+ Create Credentials" (blue button at top) > "OAuth client ID"\n3. Application type: select "Web application"\n4. Name: type "Oregon Tires Website"\n5. Under "Authorized redirect URIs", click "+ Add URI" and enter exactly:\n   https://oregon.tires/api/auth/google-callback.php\n6. Click "Create"\n7. A popup shows your Client ID and Client Secret — copy both!\n\n━━━ WHAT TO SHARE WITH YOUR DEVELOPER ━━━\n- Client ID (long string ending in .apps.googleusercontent.com)\n- Client Secret (shorter code)\n\nThese are API keys, NOT your Google password. They are safe to share with your developer.',
            es: 'Esto es lo que alimenta el boton "Iniciar sesion con Google" en su sitio web. Los clientes pueden crear cuentas e iniciar sesion con un clic usando su cuenta de Google.\n\n━━━ PASO 1: Configurar la pantalla de consentimiento ━━━\nEsta es la pantalla que los clientes ven cuando hacen clic en "Iniciar sesion con Google".\n\n1. Haga clic en el enlace "Pantalla de Consentimiento OAuth" abajo\n2. Elija tipo "External" > clic en "Create"\n3. Complete estos campos:\n   - App name: Oregon Tires Auto Care\n   - User support email: (seleccione su correo del negocio)\n   - App logo: (opcional — puede subir el logo de su taller)\n   - App domain > Application home page: https://oregon.tires\n   - Authorized domains: oregon.tires\n   - Developer contact email: su correo del negocio\n4. Clic en "Save and Continue"\n5. En la pagina de Scopes, clic en "Add or Remove Scopes" y agregue:\n   - openid\n   - email\n   - profile\n   Luego clic en "Update" > "Save and Continue"\n6. Salte la pagina de Test Users > clic en "Save and Continue"\n7. En la pagina de Summary, clic en "Back to Dashboard"\n8. Clic en "Publish App" > confirme para pasar de Testing a Production\n\n━━━ PASO 2: Crear el Client ID de OAuth ━━━\n1. Haga clic en el enlace "Pagina de Credenciales" abajo\n2. Clic en "+ Create Credentials" (boton azul arriba) > "OAuth client ID"\n3. Application type: seleccione "Web application"\n4. Name: escriba "Oregon Tires Website"\n5. En "Authorized redirect URIs", clic en "+ Add URI" e ingrese exactamente:\n   https://oregon.tires/api/auth/google-callback.php\n6. Clic en "Create"\n7. Un popup muestra su Client ID y Client Secret — copie ambos!\n\n━━━ QUE COMPARTIR CON SU DESARROLLADOR ━━━\n- Client ID (cadena larga que termina en .apps.googleusercontent.com)\n- Client Secret (codigo mas corto)\n\nEstas son claves API, NO su contrasena de Google. Es seguro compartirlas con su desarrollador.'
          },
          links: [
            { label: { en: 'Step 1: OAuth Consent Screen', es: 'Paso 1: Pantalla de Consentimiento' }, url: 'https://console.cloud.google.com/apis/credentials/consent' },
            { label: { en: 'Step 2: Credentials Page', es: 'Paso 2: Pagina de Credenciales' }, url: 'https://console.cloud.google.com/apis/credentials' }
          ],
          category: { en: 'Required', es: 'Requerido' }
        },
        {
          id: 'gc-api-key',
          text: { en: 'Create an API key (Google Reviews)', es: 'Crear una clave API (Resenas de Google)' },
          detail: {
            en: '\u2705 COMPLETED\n\nAPI Key: AIzaSyBInG7Ta9Wg1Zxa9AdvW5bHqLdQjkza5Lw\nStatus: Active and verified\nConfigured in: .env (GOOGLE_PLACES_API_KEY)\n\nPowers:\n\u2022 Google Reviews auto-fetch (daily 6 AM cron)\n\u2022 Market Intel data collection (976 Portland businesses)\n\nRecommendation: Restrict this key to Places API + Places API (New) only in Google Cloud Console > Credentials > Edit key > API restrictions.',
            es: '\u2705 COMPLETADO\n\nClave API: AIzaSyBInG7Ta9Wg1Zxa9AdvW5bHqLdQjkza5Lw\nEstado: Activa y verificada\nConfigurada en: .env (GOOGLE_PLACES_API_KEY)\n\nFunciones:\n\u2022 Obtenci\u00f3n autom\u00e1tica de rese\u00f1as de Google (cron diario 6 AM)\n\u2022 Recolecci\u00f3n de datos Market Intel (976 negocios de Portland)\n\nRecomendaci\u00f3n: Restrinja esta clave a Places API + Places API (New) en Google Cloud Console > Credentials > Editar clave > API restrictions.'
          },
          links: [
            { label: { en: 'Credentials Page', es: 'Pagina de Credenciales' }, url: 'https://console.cloud.google.com/apis/credentials' }
          ],
          category: { en: 'Required', es: 'Requerido' }
        },
        {
          id: 'gc-service-account',
          text: { en: 'Create a Service Account (Calendar + Business Profile)', es: 'Crear una Cuenta de Servicio (Calendario + Perfil de Negocio)' },
          detail: {
            en: '\u2705 COMPLETED\n\nService Account: oregon-tires@oregon-tires.iam.gserviceaccount.com\nClient ID: 113123761510421027650\nJSON Key: Installed on server (google-service-account.json)\nAuthentication: Verified \u2014 Bearer token obtained successfully\nGoogle Client Library: v2.19.0 installed\n\nThis service account powers:\n\u2022 Google Calendar API \u2014 appointment sync (needs Calendar sharing \u2014 next step)\n\u2022 Google Business Profile API \u2014 hours/posts/insights (needs GBP access \u2014 next step)',
            es: '\u2705 COMPLETADO\n\nCuenta de Servicio: oregon-tires@oregon-tires.iam.gserviceaccount.com\nClient ID: 113123761510421027650\nClave JSON: Instalada en el servidor (google-service-account.json)\nAutenticaci\u00f3n: Verificada \u2014 Token Bearer obtenido exitosamente\nLibrer\u00eda Google Client: v2.19.0 instalada\n\nEsta cuenta de servicio alimenta:\n\u2022 Google Calendar API \u2014 sincronizaci\u00f3n de citas (necesita compartir Calendario \u2014 siguiente paso)\n\u2022 Google Business Profile API \u2014 horarios/publicaciones/estad\u00edsticas (necesita acceso GBP \u2014 siguiente paso)'
          },
          links: [
            { label: { en: 'Service Accounts', es: 'Cuentas de Servicio' }, url: 'https://console.cloud.google.com/iam-admin/serviceaccounts' }
          ],
          category: { en: 'Required', es: 'Requerido' }
        },
        {
          id: 'gc-calendar',
          text: { en: 'Share Google Calendar with the service account', es: 'Compartir Google Calendar con la cuenta de servicio' },
          detail: {
            en: '\u2705 COMPLETED\n\nCalendar: Oregon Tires Appointments\nCalendar ID: 7b9caaaee321c756...@group.calendar.google.com\nTimezone: America/Los_Angeles\nShared with: oregon-tires@oregon-tires.iam.gserviceaccount.com\nPermission: Make changes to events\n\nVerified:\n\u2022 Read access: SUCCESS\n\u2022 Write access: SUCCESS (test event created and deleted)\n\u2022 Sync enabled in .env (GOOGLE_CALENDAR_SYNC_ENABLED=1)\n\nNew appointments booked on the website will automatically appear on this Google Calendar.',
            es: '\u2705 COMPLETADO\n\nCalendario: Oregon Tires Appointments\nCalendar ID: 7b9caaaee321c756...@group.calendar.google.com\nZona horaria: America/Los_Angeles\nCompartido con: oregon-tires@oregon-tires.iam.gserviceaccount.com\nPermiso: Hacer cambios en eventos\n\nVerificado:\n\u2022 Acceso de lectura: EXITOSO\n\u2022 Acceso de escritura: EXITOSO (evento de prueba creado y eliminado)\n\u2022 Sincronizaci\u00f3n habilitada en .env (GOOGLE_CALENDAR_SYNC_ENABLED=1)\n\nLas citas reservadas en el sitio web aparecer\u00e1n autom\u00e1ticamente en este Google Calendar.'
          },
          links: [
            { label: { en: 'Google Calendar', es: 'Google Calendar' }, url: 'https://calendar.google.com/' }
          ],
          category: { en: 'Recommended', es: 'Recomendado' }
        },
        {
          id: 'gc-gbp',
          text: { en: 'Grant Business Profile access to service account', es: 'Otorgar acceso al Perfil de Negocio a la cuenta de servicio' },
          detail: {
            en: 'This lets your website automatically sync business hours, post updates, and track insights from your Google Business Profile.\n\n1. Go to Google Business Profile (link below)\n2. Sign in with the Gmail that owns your business listing\n3. Click the gear icon (Settings) or go to "Business Profile settings"\n4. Under "Managers", click "Add"\n5. Enter the service account email (same one from the previous step)\n6. Select "Manager" role\n7. Click "Invite"\n\nWhat to share with your developer:\n- Confirmation that the service account was added as Manager\n- Your GBP Account ID and Location ID (your developer can help you find these)\n\nOnce connected, your website can:\n- Auto-update business hours on Google if you change them in the admin panel\n- Post promotions directly to your Google listing\n- Track how customers find you (search, maps, direct)',
            es: 'Esto permite a su sitio web sincronizar automaticamente horarios, publicar actualizaciones y rastrear estadisticas de su Google Business Profile.\n\n1. Vaya a Google Business Profile (enlace abajo)\n2. Inicie sesion con el Gmail que es dueno de su listado de negocio\n3. Haga clic en el icono de engranaje (Configuracion)\n4. En "Administradores", clic en "Agregar"\n5. Ingrese el email de la cuenta de servicio (el mismo del paso anterior)\n6. Seleccione rol "Administrador"\n7. Clic en "Invitar"\n\nQue compartir con su desarrollador:\n- Confirmacion de que la cuenta de servicio fue agregada como Administrador\n- Su Account ID y Location ID de GBP (su desarrollador puede ayudarle a encontrarlos)\n\nUna vez conectado, su sitio web puede:\n- Actualizar automaticamente los horarios en Google si los cambia en el panel de admin\n- Publicar promociones directamente en su listado de Google\n- Rastrear como los clientes lo encuentran (busqueda, mapas, directo)'
          },
          links: [
            { label: { en: 'Google Business Profile', es: 'Google Business Profile' }, url: 'https://business.google.com/' }
          ],
          category: { en: 'Recommended', es: 'Recomendado' }
        },
        {
          id: 'gc-verify-gbp',
          text: { en: 'Verify your Google Business Profile listing', es: 'Verificar su listado de Google Business Profile' },
          detail: {
            en: 'Make sure your shop is claimed on Google so reviews and business info display correctly.\n\n1. Click the link below and sign in with your business Gmail\n2. Confirm "Oregon Tires Auto Care" appears as your business\n3. If not claimed yet, click "Add your business" and follow Google\'s verification steps (they may call your shop phone or mail a postcard)\n\nYour Google Place ID is already configured: ChIJLSxZDQyflVQRWXEi9LpJGxs\nNo action needed from you on the Place ID — just verify the listing is claimed.\n\n━━━ WHAT TO SHARE WITH YOUR DEVELOPER ━━━\n- Confirmation that the listing is claimed and you can see it in your dashboard',
            es: 'Asegurese de que su taller este reclamado en Google para que las resenas e informacion del negocio se muestren correctamente.\n\n1. Haga clic en el enlace abajo e inicie sesion con su Gmail del negocio\n2. Confirme que "Oregon Tires Auto Care" aparece como su negocio\n3. Si no esta reclamado, haga clic en "Agregar su negocio" y siga los pasos de verificacion de Google (pueden llamar al telefono de su taller o enviar una postal)\n\nSu Google Place ID ya esta configurado: ChIJLSxZDQyflVQRWXEi9LpJGxs\nNo necesita hacer nada con el Place ID — solo verifique que el listado este reclamado.\n\n━━━ QUE COMPARTIR CON SU DESARROLLADOR ━━━\n- Confirmacion de que el listado esta reclamado y puede verlo en su dashboard'
          },
          links: [
            { label: { en: 'Google Business Profile', es: 'Google Business Profile' }, url: 'https://business.google.com/' }
          ],
          category: { en: 'Required', es: 'Requerido' }
        },
        {
          id: 'gc-analytics',
          text: { en: 'Set up Google Analytics (visitor tracking)', es: 'Configurar Google Analytics (seguimiento de visitantes)' },
          detail: {
            en: 'Google Analytics shows you how many people visit your website, which pages they view, and how they found you. FREE and essential for any business website.\n\n━━━ STEP 1: Create an Analytics account ━━━\n1. Click the link below and sign in with your business Gmail\n2. Click "Start measuring"\n3. Account name: Oregon Tires Auto Care\n4. Click "Next"\n5. Property name: oregon.tires\n6. Select your time zone and currency\n7. Click "Next" > choose your business category > click "Create"\n8. Accept the terms of service\n\n━━━ STEP 2: Set up the data stream ━━━\n1. Choose "Web" as the platform\n2. Website URL: https://oregon.tires\n3. Stream name: Oregon Tires Website\n4. Click "Create stream"\n5. Copy the Measurement ID (looks like G-XXXXXXXXXX) — your developer needs this\n\n━━━ STEP 3: Grant your developer access ━━━\n1. Click Admin (gear icon, bottom left)\n2. Under Property, click "Property Access Management"\n3. Click the blue "+" button > "Add users"\n4. Enter your developer\'s email > select "Editor" role > click "Add"\n\n━━━ WHAT TO SHARE WITH YOUR DEVELOPER ━━━\n- Your Measurement ID (G-XXXXXXXXXX)',
            es: 'Google Analytics muestra cuantas personas visitan su sitio web, que paginas ven y como lo encontraron. GRATIS y esencial para cualquier sitio web de negocio.\n\n━━━ PASO 1: Crear una cuenta de Analytics ━━━\n1. Haga clic en el enlace abajo e inicie sesion con su Gmail del negocio\n2. Clic en "Start measuring"\n3. Account name: Oregon Tires Auto Care\n4. Clic en "Next"\n5. Property name: oregon.tires\n6. Seleccione su zona horaria y moneda\n7. Clic en "Next" > elija la categoria de su negocio > clic en "Create"\n8. Acepte los terminos de servicio\n\n━━━ PASO 2: Configurar el flujo de datos ━━━\n1. Elija "Web" como plataforma\n2. Website URL: https://oregon.tires\n3. Stream name: Oregon Tires Website\n4. Clic en "Create stream"\n5. Copie el Measurement ID (se ve como G-XXXXXXXXXX) — su desarrollador lo necesita\n\n━━━ PASO 3: Otorgar acceso a su desarrollador ━━━\n1. Clic en Admin (icono de engranaje, abajo a la izquierda)\n2. En Property, clic en "Property Access Management"\n3. Clic en el boton azul "+" > "Add users"\n4. Ingrese el correo de su desarrollador > seleccione rol "Editor" > clic en "Add"\n\n━━━ QUE COMPARTIR CON SU DESARROLLADOR ━━━\n- Su Measurement ID (G-XXXXXXXXXX)'
          },
          links: [
            { label: { en: 'Google Analytics', es: 'Google Analytics' }, url: 'https://analytics.google.com/' }
          ],
          category: { en: 'Required', es: 'Requerido' }
        },
        {
          id: 'gc-search-console',
          text: { en: 'Set up Google Search Console (SEO monitoring)', es: 'Configurar Google Search Console (monitoreo SEO)' },
          detail: {
            en: 'Google Search Console shows you how your website appears in Google search results — which search terms bring visitors, how often your pages appear, and any issues Google finds. FREE and important for SEO.\n\n1. Click the link below and sign in with your business Gmail\n2. Click "Add property"\n3. Choose "URL prefix" on the right side\n4. Enter: https://oregon.tires\n5. Click "Continue"\n6. For verification, choose "HTML tag" method\n7. Copy the verification code (just the content value, looks like a long string)\n8. Your developer will add this code to the website\n\n━━━ GRANT YOUR DEVELOPER ACCESS ━━━\n1. After verification, click Settings (gear icon) in the left menu\n2. Click "Users and permissions"\n3. Click "Add user"\n4. Enter your developer\'s email > select "Full" permission > click "Add"\n\n━━━ WHAT TO SHARE WITH YOUR DEVELOPER ━━━\n- The HTML tag verification code\n- Confirmation that developer access was granted\n\nFREE — takes 2 minutes. Your developer can then monitor search performance and improve your Google ranking.',
            es: 'Google Search Console muestra como aparece su sitio web en los resultados de busqueda de Google — que terminos de busqueda traen visitantes, con que frecuencia aparecen sus paginas y cualquier problema que Google encuentre. GRATIS e importante para SEO.\n\n1. Haga clic en el enlace abajo e inicie sesion con su Gmail del negocio\n2. Clic en "Add property"\n3. Elija "URL prefix" en el lado derecho\n4. Ingrese: https://oregon.tires\n5. Clic en "Continue"\n6. Para verificacion, elija el metodo "HTML tag"\n7. Copie el codigo de verificacion (solo el valor de content, se ve como una cadena larga)\n8. Su desarrollador agregara este codigo al sitio web\n\n━━━ OTORGAR ACCESO A SU DESARROLLADOR ━━━\n1. Despues de la verificacion, clic en Settings (icono de engranaje) en el menu izquierdo\n2. Clic en "Users and permissions"\n3. Clic en "Add user"\n4. Ingrese el correo de su desarrollador > seleccione permiso "Full" > clic en "Add"\n\n━━━ QUE COMPARTIR CON SU DESARROLLADOR ━━━\n- El codigo de verificacion HTML tag\n- Confirmacion de que se otorgo acceso al desarrollador\n\nGRATIS — toma 2 minutos. Su desarrollador podra monitorear el rendimiento en busquedas y mejorar su posicionamiento en Google.'
          },
          links: [
            { label: { en: 'Google Search Console', es: 'Google Search Console' }, url: 'https://search.google.com/search-console/' }
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
