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
        en: 'Create these accounts under your business name. You never need to share your Google password — each service below has a safe way to grant access or share only the specific details your developer needs.',
        es: 'Cree estas cuentas a nombre de su negocio. Nunca necesita compartir su contraseña de Google — cada servicio tiene una forma segura de otorgar acceso o compartir solo los datos específicos que su desarrollador necesita.'
      },
      items: [
        {
          id: 'acct-google',
          text: { en: 'Google account (Reviews + Analytics + Login)', es: 'Cuenta de Google (Resenas + Analiticas + Login)' },
          detail: {
            en: 'Your business Gmail is the foundation for three website features. You never share your Google password — just grant access and share API keys as described below.\n\nDetailed step-by-step setup for Google Cloud Console (OAuth, API keys, service accounts) is in the "Google Cloud Console Setup" section below.\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n📊 GOOGLE ANALYTICS (visitor tracking)\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n1. Open Google Analytics (link below)\n2. Sign in with your business Gmail\n3. Click Admin (gear icon, bottom left)\n4. Under Property, click "Property Access Management"\n5. Click the blue "+" button > "Add users"\n6. Enter your developer\'s email and select "Editor" role\n7. Click "Add"\n\nWhat to share with your developer:\n- Your Analytics Measurement ID (looks like G-XXXXXXXXXX)\n  Find it in: Admin > Data Streams > click your stream > copy Measurement ID\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n⭐ GOOGLE REVIEWS (display on website)\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nNo password sharing needed! Your developer only needs:\n- Your Google Place ID: ChIJLSxZDQyflVQRWXEi9LpJGxs\n  (This is already configured — no action needed from you)\n- A Places API key (created in the "Google Cloud Console Setup" section below)\n\nTo verify your business listing is claimed:\n1. Open Google Business Profile (link below)\n2. Sign in with your business Gmail\n3. Confirm "Oregon Tires Auto Care" appears as your business\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n🔐 SIGN IN WITH GOOGLE (customer login)\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nSee the "Google Cloud Console Setup" section below for step-by-step OAuth credential creation. You\'ll create a Client ID and Client Secret (API keys, not your password) and share them with your developer.',
            es: 'Su Gmail del negocio es la base para tres funciones del sitio web. Nunca comparta su contrasena de Google — solo otorgue acceso y comparta claves API como se describe abajo.\n\nLa configuracion detallada paso a paso de Google Cloud Console (OAuth, claves API, cuentas de servicio) esta en la seccion "Configuracion de Google Cloud Console" mas abajo.\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n📊 GOOGLE ANALYTICS (seguimiento de visitantes)\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n1. Abra Google Analytics (enlace abajo)\n2. Inicie sesion con su Gmail del negocio\n3. Haga clic en Administrar (icono de engranaje, abajo a la izquierda)\n4. En Propiedad, clic en "Gestion de acceso a la propiedad"\n5. Clic en el boton azul "+" > "Anadir usuarios"\n6. Ingrese el correo de su desarrollador y seleccione rol "Editor"\n7. Haga clic en "Anadir"\n\nQue compartir con su desarrollador:\n- Su ID de Medicion de Analytics (se ve como G-XXXXXXXXXX)\n  Encuentrelo en: Administrar > Flujos de datos > clic en su flujo > copiar ID de medicion\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n⭐ RESENAS DE GOOGLE (mostrar en sitio web)\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nNo necesita compartir contrasena! Su desarrollador solo necesita:\n- Su Google Place ID: ChIJLSxZDQyflVQRWXEi9LpJGxs\n  (Esto ya esta configurado — no requiere accion de su parte)\n- Una clave API de Places (creada en la seccion "Configuracion de Google Cloud Console" abajo)\n\nPara verificar que su negocio este reclamado:\n1. Abra Google Business Profile (enlace abajo)\n2. Inicie sesion con su Gmail del negocio\n3. Confirme que "Oregon Tires Auto Care" aparece como su negocio\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n🔐 INICIAR SESION CON GOOGLE (acceso de clientes)\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nVea la seccion "Configuracion de Google Cloud Console" abajo para la creacion paso a paso de credenciales OAuth. Creara un Client ID y Client Secret (claves API, no su contrasena) y los compartira con su desarrollador.'
          },
          links: [
            { label: { en: 'Google Analytics (grant access)', es: 'Google Analytics (otorgar acceso)' }, url: 'https://analytics.google.com/analytics/web/#/a/p/admin/account-access-management' },
            { label: { en: 'Google Business Profile (verify)', es: 'Google Business Profile (verificar)' }, url: 'https://business.google.com/' },
            { label: { en: 'Google Cloud Console (OAuth keys)', es: 'Google Cloud Console (claves OAuth)' }, url: 'https://console.cloud.google.com/apis/credentials' }
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
          text: { en: 'Google Search Console (SEO monitoring)', es: 'Google Search Console (monitoreo SEO)' },
          detail: {
            en: 'Sign in with your business Gmail and add oregon.tires as a property. Then grant your developer access — no password sharing needed.\n\nSteps:\n1. Open Google Search Console (link below)\n2. Sign in with your business Gmail\n3. Add "oregon.tires" as a property (choose "Domain" type)\n4. Click Settings (gear icon) → Users and permissions\n5. Click "Add user" → enter your developer\'s email → select "Full" permission\n6. Click "Add"\n\nYour developer can now see search performance and improve your Google ranking without needing your password.\n\nFREE — takes 2 minutes to set up.',
            es: 'Inicie sesión con su Gmail del negocio y agregue oregon.tires como propiedad. Luego otorgue acceso a su desarrollador — no necesita compartir contraseña.\n\nPasos:\n1. Abra Google Search Console (enlace abajo)\n2. Inicie sesión con su Gmail del negocio\n3. Agregue "oregon.tires" como propiedad (elija tipo "Dominio")\n4. Clic en Configuración (ícono de engranaje) → Usuarios y permisos\n5. Clic en "Añadir usuario" → ingrese el correo de su desarrollador → seleccione permiso "Completo"\n6. Clic en "Añadir"\n\nSu desarrollador podrá ver el rendimiento en búsquedas y mejorar su posicionamiento en Google sin necesitar su contraseña.\n\nGRATIS — toma 2 minutos configurar.'
          },
          links: [
            { label: { en: 'Google Search Console', es: 'Google Search Console' }, url: 'https://search.google.com/search-console/' }
          ],
          category: { en: 'Recommended', es: 'Recomendado' }
        },
      ]
    },
    {
      id: 'google-cloud',
      icon: '☁️',
      title: { en: 'Google Cloud Console Setup', es: 'Configuracion de Google Cloud Console' },
      description: {
        en: 'Your website uses Google services for reviews, calendar sync, and customer sign-in. All of these are managed from one place: the Google Cloud Console. Complete these steps to connect your own Google account.',
        es: 'Su sitio web usa servicios de Google para resenas, sincronizacion de calendario e inicio de sesion de clientes. Todo se administra desde un lugar: Google Cloud Console. Complete estos pasos para conectar su propia cuenta de Google.'
      },
      items: [
        {
          id: 'gc-project',
          text: { en: 'Create a Google Cloud project', es: 'Crear un proyecto en Google Cloud' },
          detail: {
            en: 'This is the container for all your API keys and credentials. It costs nothing to create.\n\n1. Go to Google Cloud Console (link below)\n2. Sign in with your business Gmail\n3. Click the project dropdown (top-left, next to "Google Cloud")\n4. Click "New Project"\n5. Name it "Oregon Tires" and click "Create"\n6. Make sure "Oregon Tires" is selected as the active project\n\nThis project will hold all the API keys and credentials for your website.',
            es: 'Este es el contenedor para todas sus claves API y credenciales. No cuesta nada crearlo.\n\n1. Vaya a Google Cloud Console (enlace abajo)\n2. Inicie sesion con su Gmail del negocio\n3. Haga clic en el menu de proyectos (arriba a la izquierda, junto a "Google Cloud")\n4. Clic en "Nuevo Proyecto"\n5. Nombrelo "Oregon Tires" y haga clic en "Crear"\n6. Asegurese de que "Oregon Tires" este seleccionado como proyecto activo\n\nEste proyecto contendra todas las claves API y credenciales de su sitio web.'
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
            en: 'Enable each API your website needs. Click each link below — each opens directly to that API\'s page in your Google Cloud project. On each page, click the blue "Enable" button.\n\nAPIs to enable (click each link below):\n\n1. Places API (New) — displays your Google Reviews on the website\n2. Places API (legacy) — also needed for reviews (older format)\n3. Google Calendar API — syncs new appointments to your Google Calendar automatically\n4. My Business Business Information API — lets the website manage your Google Business Profile (hours, posts, insights)\n\nAll of these are FREE within normal usage limits. A typical tire shop will never come close to exceeding the free tier.\n\nAfter clicking "Enable" on each one, you can verify they are all active by visiting: APIs & Services > Enabled APIs (last link below).',
            es: 'Habilite cada API que su sitio web necesita. Haga clic en cada enlace abajo — cada uno abre directamente la pagina de esa API en su proyecto de Google Cloud. En cada pagina, haga clic en el boton azul "Enable".\n\nAPIs a habilitar (haga clic en cada enlace abajo):\n\n1. Places API (New) — muestra sus resenas de Google en el sitio web\n2. Places API (legacy) — tambien necesaria para resenas (formato anterior)\n3. Google Calendar API — sincroniza citas nuevas a su Google Calendar automaticamente\n4. My Business Business Information API — permite al sitio web gestionar su Google Business Profile (horarios, publicaciones, estadisticas)\n\nTodas son GRATUITAS dentro de los limites normales de uso. Un taller tipico nunca se acercara a exceder el nivel gratuito.\n\nDespues de hacer clic en "Enable" en cada una, puede verificar que estan activas visitando: APIs & Services > Enabled APIs (ultimo enlace abajo).'
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
            en: 'This key lets your website automatically pull your latest Google Reviews and display them on the Reviews page. Reviews refresh daily via an automated task.\n\n1. Click the "Credentials Page" link below\n2. Click "+ Create Credentials" (blue button at top) > "API key"\n3. A key appears immediately in a popup — copy it, then click "Edit API key"\n   (or close the popup and click the pencil icon next to the key)\n\n━━━ IMPORTANT: Restrict the key ━━━\n4. Under "Set an application restriction", select "None" (leave as-is for server-side use)\n5. Under "API restrictions", select "Restrict key"\n6. In the dropdown, check ONLY these two:\n   - Places API\n   - Places API (New)\n7. Click "Save"\n\nWhy restrict? If anyone ever sees this key, they can only use it for Google Reviews — nothing else. This is a security best practice.\n\n━━━ WHAT TO SHARE WITH YOUR DEVELOPER ━━━\n- The API key (starts with "AIza..." — about 40 characters long)\n\nOnce your developer adds this key, your Google Reviews will appear on the website and update automatically every morning.',
            es: 'Esta clave permite a su sitio web obtener automaticamente sus resenas mas recientes de Google y mostrarlas en la pagina de Resenas. Las resenas se actualizan diariamente con una tarea automatizada.\n\n1. Haga clic en el enlace "Pagina de Credenciales" abajo\n2. Clic en "+ Create Credentials" (boton azul arriba) > "API key"\n3. Una clave aparece inmediatamente en un popup — copiela, luego clic en "Edit API key"\n   (o cierre el popup y haga clic en el icono de lapiz junto a la clave)\n\n━━━ IMPORTANTE: Restringir la clave ━━━\n4. En "Set an application restriction", seleccione "None" (dejelo asi para uso del servidor)\n5. En "API restrictions", seleccione "Restrict key"\n6. En el dropdown, marque SOLO estas dos:\n   - Places API\n   - Places API (New)\n7. Clic en "Save"\n\nPor que restringir? Si alguien ve esta clave, solo puede usarla para resenas de Google — nada mas. Esto es una buena practica de seguridad.\n\n━━━ QUE COMPARTIR CON SU DESARROLLADOR ━━━\n- La clave API (empieza con "AIza..." — aproximadamente 40 caracteres)\n\nUna vez que su desarrollador agregue esta clave, sus resenas de Google apareceran en el sitio web y se actualizaran automaticamente cada manana.'
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
            en: 'A service account is like a robot employee for your website. It lets the site perform tasks automatically (syncing appointments to your calendar, managing your Google listing) without anyone needing to be logged in.\n\n━━━ PART A: Create the service account ━━━\n1. Click the "Service Accounts" link below\n2. Click "+ Create Service Account" (blue button at top)\n3. Fill in:\n   - Service account name: Oregon Tires Website\n   - Service account ID: (auto-fills, leave as-is)\n   - Description: Automated calendar and business profile sync\n4. Click "Create and Continue"\n5. Under "Grant this service account access to project":\n   - Click the Role dropdown > type "Editor" > select "Basic > Editor"\n6. Click "Continue" > "Done"\n\n━━━ PART B: Download the key file ━━━\n7. In the Service Accounts list, click the email address you just created\n   (it looks like: oregon-tires-website@your-project.iam.gserviceaccount.com)\n8. Click the "Keys" tab at the top\n9. Click "Add Key" > "Create new key"\n10. Select "JSON" > click "Create"\n11. A .json file downloads to your computer automatically\n\n━━━ WHAT TO SHARE WITH YOUR DEVELOPER ━━━\n- The downloaded JSON file\n- The service account email address (you will need this in the next two steps)\n\nSend the JSON file securely (email attachment or shared drive — not posted publicly). This file is like a password — it grants access to your Calendar and Business Profile. Your developer will install it on the server.\n\nIMPORTANT: Write down the service account email address! You will need to paste it in the next two steps (Calendar sharing and Business Profile access).',
            es: 'Una cuenta de servicio es como un empleado robot para su sitio web. Permite al sitio realizar tareas automaticamente (sincronizar citas a su calendario, gestionar su listado de Google) sin que nadie necesite estar conectado.\n\n━━━ PARTE A: Crear la cuenta de servicio ━━━\n1. Haga clic en el enlace "Cuentas de Servicio" abajo\n2. Clic en "+ Create Service Account" (boton azul arriba)\n3. Complete:\n   - Service account name: Oregon Tires Website\n   - Service account ID: (se llena automaticamente, dejelo asi)\n   - Description: Automated calendar and business profile sync\n4. Clic en "Create and Continue"\n5. En "Grant this service account access to project":\n   - Clic en el dropdown de Role > escriba "Editor" > seleccione "Basic > Editor"\n6. Clic en "Continue" > "Done"\n\n━━━ PARTE B: Descargar el archivo de clave ━━━\n7. En la lista de Service Accounts, haga clic en el email que acaba de crear\n   (se ve como: oregon-tires-website@your-project.iam.gserviceaccount.com)\n8. Haga clic en la pestana "Keys" arriba\n9. Clic en "Add Key" > "Create new key"\n10. Seleccione "JSON" > clic en "Create"\n11. Un archivo .json se descarga a su computadora automaticamente\n\n━━━ QUE COMPARTIR CON SU DESARROLLADOR ━━━\n- El archivo JSON descargado\n- La direccion de email de la cuenta de servicio (la necesitara en los proximos dos pasos)\n\nEnvie el archivo JSON de forma segura (adjunto de email o drive compartido — no lo publique). Este archivo es como una contrasena — otorga acceso a su Calendario y Perfil de Negocio. Su desarrollador lo instalara en el servidor.\n\nIMPORTANTE: Anote la direccion de email de la cuenta de servicio! La necesitara en los proximos dos pasos (compartir Calendario y acceso a Perfil de Negocio).'
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
            en: 'Connect your shop\'s Google Calendar so appointments automatically appear there.\n\n1. Open Google Calendar (link below) — sign in with your business Gmail\n2. Create a new calendar (or use an existing one):\n   - Click the "+" next to "Other calendars" on the left\n   - Click "Create new calendar"\n   - Name it "Oregon Tires Appointments"\n   - Click "Create calendar"\n3. Share it with the service account:\n   - Hover over the calendar name, click the three dots > "Settings and sharing"\n   - Scroll to "Share with specific people or groups"\n   - Click "Add people and groups"\n   - Paste the service account email (looks like: oregon-tires-website@your-project.iam.gserviceaccount.com)\n   - Set permission to "Make changes to events"\n   - Click "Send"\n4. Get the Calendar ID:\n   - In the same settings page, scroll to "Integrate calendar"\n   - Copy the "Calendar ID" (looks like: xxxx@group.calendar.google.com)\n\nWhat to share with your developer:\n- The Calendar ID\n- The service account email (so they can verify it was added)\n\nOnce configured, every new appointment booked on your website will automatically appear on this Google Calendar.',
            es: 'Conecte su Google Calendar del taller para que las citas aparezcan automaticamente.\n\n1. Abra Google Calendar (enlace abajo) — inicie sesion con su Gmail del negocio\n2. Cree un calendario nuevo (o use uno existente):\n   - Clic en "+" junto a "Otros calendarios" a la izquierda\n   - Clic en "Crear calendario nuevo"\n   - Nombrelo "Oregon Tires Appointments"\n   - Clic en "Crear calendario"\n3. Compartalo con la cuenta de servicio:\n   - Pase el mouse sobre el nombre del calendario, clic en los tres puntos > "Configuracion y uso compartido"\n   - Baje a "Compartir con personas o grupos especificos"\n   - Clic en "Agregar personas y grupos"\n   - Pegue el email de la cuenta de servicio (se ve como: oregon-tires-website@your-project.iam.gserviceaccount.com)\n   - Permiso: "Hacer cambios en los eventos"\n   - Clic en "Enviar"\n4. Obtenga el Calendar ID:\n   - En la misma pagina, baje a "Integrar calendario"\n   - Copie el "Calendar ID" (se ve como: xxxx@group.calendar.google.com)\n\nQue compartir con su desarrollador:\n- El Calendar ID\n- El email de la cuenta de servicio (para verificar que fue agregada)\n\nUna vez configurado, cada cita reservada en su sitio web aparecera automaticamente en este Google Calendar.'
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
