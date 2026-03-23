/**
 * Oregon Tires — Client Handoff & Independence Guide
 * Interactive checklist for transferring the platform to client-owned infrastructure.
 * Renders inside the admin Docs tab.
 */
(function() {
  'use strict';

  var STORAGE_KEY = 'ot_handoff_checklist';

  function t(key, fb) {
    return (typeof adminT !== 'undefined' && adminT[currentLang] && adminT[currentLang][key]) || fb;
  }

  function getChecked() {
    try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}'); } catch(e) { return {}; }
  }
  function setChecked(id, val) {
    var data = getChecked();
    data[id] = val;
    localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
  }

  // ─── Data ─────────────────────────────────────────────────────────────────
  var sections = [
    {
      id: 'hosting',
      icon: '🖥️',
      title: 'Web Hosting Setup',
      description: 'Choose and configure a standalone web host for Oregon Tires.',
      items: [
        { id: 'host-choose', text: 'Choose a web host with SSH + cPanel support', detail: 'Recommended: A2 Hosting ($5/mo, SSH + cPanel), SiteGround ($15/mo, excellent support), or Hostinger ($3/mo, budget option). Requirements: PHP 8.1+, MySQL 8.0+, SSH access, Composer, free SSL.' },
        { id: 'host-account', text: 'Create hosting account and cPanel login', detail: 'Sign up, verify email, access cPanel dashboard. Note the server IP address for DNS.' },
        { id: 'host-database', text: 'Create MySQL database + user', detail: 'In cPanel → MySQL Databases: create database (e.g., oregontires_db), create user, grant all privileges. Save credentials for .env file.' },
        { id: 'host-ssh', text: 'Enable SSH access and test connection', detail: 'In cPanel → SSH Access: generate key pair or enable password auth. Test: ssh username@server-ip' },
        { id: 'host-php', text: 'Verify PHP 8.1+ and required extensions', detail: 'In cPanel → PHP Selector: set to PHP 8.1 or 8.2. Required extensions: pdo_mysql, mbstring, openssl, curl, json, fileinfo.' },
        { id: 'host-upload', text: 'Upload Oregon Tires files to server', detail: 'Use SCP or cPanel File Manager. Upload public_html contents to web root. Upload .env file ABOVE web root for security.' },
        { id: 'host-composer', text: 'Run composer install on server', detail: 'SSH into server: cd /path/to/site && composer install --no-dev' },
        { id: 'host-migrations', text: 'Run all SQL migrations', detail: 'Import all files from sql/ directory in order. Or use: cat sql/migrate-*.sql | mysql -u user -p database' },
        { id: 'host-ssl', text: 'Enable SSL certificate (HTTPS)', detail: 'In cPanel → SSL/TLS or Let\'s Encrypt: install free SSL for oregon.tires. Verify https:// works.' },
        { id: 'host-cron', text: 'Configure cron jobs (7 jobs)', detail: '0 18 * * * php cli/send-reminders.php\n0 10 * * * php cli/send-review-requests.php\n0 6 * * * php cli/fetch-google-reviews.php\n*/5 * * * * php cli/send-push-notifications.php\n0 9 * * 1 php cli/send-service-reminders.php\n0 7 * * 1 php cli/sync-google-business.php\n*/2 * * * * php cli/fetch-inbound-emails.php' },
      ]
    },
    {
      id: 'domain',
      icon: '🌐',
      title: 'Domain Transfer',
      description: 'Transfer oregon.tires domain to client ownership.',
      items: [
        { id: 'dom-unlock', text: 'Unlock domain at current registrar', detail: 'Current registrar: check Domain_Registry_2026.xlsx. Remove transfer lock, obtain EPP/auth code.' },
        { id: 'dom-transfer', text: 'Initiate transfer to client\'s registrar', detail: 'Client creates account at Namecheap, GoDaddy, or Cloudflare Registrar. Start transfer with EPP code. Approve transfer emails.' },
        { id: 'dom-dns', text: 'Update DNS to point to new server', detail: 'A record → new server IP. If using Cloudflare: add site, update nameservers at registrar. Wait for propagation (up to 48 hours).' },
        { id: 'dom-verify', text: 'Verify site loads on new server', detail: 'Test https://oregon.tires in browser. Check SSL, all pages, API endpoints.' },
      ]
    },
    {
      id: 'credentials',
      icon: '🔑',
      title: 'API Credentials (Client-Owned)',
      description: 'Replace all developer credentials with client\'s own accounts.',
      items: [
        { id: 'cred-smtp', text: 'SMTP Email (sending)', detail: 'Get from hosting provider or set up Google Workspace ($6/mo). Env vars: SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASSWORD, SMTP_FROM, SMTP_FROM_NAME, CONTACT_EMAIL. Test: php cli/test-smtp-debug.php', category: 'Required' },
        { id: 'cred-imap', text: 'IMAP Email (receiving)', detail: 'Same host as SMTP usually. Env vars: IMAP_HOST, IMAP_PORT, IMAP_USER, IMAP_PASSWORD, IMAP_ENCRYPTION. Enables customer email threading in conversations.', category: 'Required' },
        { id: 'cred-google-oauth', text: 'Google OAuth (Login with Google)', detail: 'Go to console.cloud.google.com → Create project → APIs & Services → Credentials → Create OAuth 2.0 Client ID (Web application). Add redirect URI: https://oregon.tires/api/auth/google-callback.php. Env vars: GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, GOOGLE_REDIRECT_URI', category: 'Required' },
        { id: 'cred-google-places', text: 'Google Places API (reviews + map)', detail: 'Same Google Cloud project → Enable "Places API" and "Places API (New)". Create API key, restrict to oregon.tires domain. Env var: GOOGLE_PLACES_API_KEY. Cost: free tier covers normal usage.', category: 'Required' },
        { id: 'cred-whatsapp', text: 'WhatsApp Business API (FREE messaging)', detail: 'Go to business.facebook.com → create Meta Business account. Go to developers.facebook.com → create app → add WhatsApp product. Register business phone number. Get WHATSAPP_PHONE_ID and WHATSAPP_ACCESS_TOKEN. Submit message templates for approval. FREE: 1,000 service conversations/month.', category: 'Recommended' },
        { id: 'cred-twilio', text: 'Twilio SMS (optional fallback)', detail: 'Go to twilio.com → sign up → get Account SID, Auth Token, buy phone number (~$1/mo + $0.01/SMS). Env vars: TWILIO_SID, TWILIO_TOKEN, TWILIO_FROM. Optional: WhatsApp is the free primary channel.', category: 'Optional' },
        { id: 'cred-paypal', text: 'PayPal Business (payments)', detail: 'Go to developer.paypal.com → My Apps & Credentials → Create App. Get Client ID and Secret. Env vars: PAYPAL_CLIENT_ID, PAYPAL_SECRET, PAYPAL_MODE=live', category: 'Required' },
        { id: 'cred-stripe', text: 'Stripe (alternative payments)', detail: 'Go to dashboard.stripe.com → Developers → API keys. Get Secret key. Set up webhook endpoint: https://oregon.tires/api/commerce/webhook.php. Env vars: STRIPE_SECRET_KEY, STRIPE_WEBHOOK_SECRET', category: 'Optional' },
        { id: 'cred-sentry', text: 'Sentry (error tracking)', detail: 'Go to sentry.io → create project (PHP + JavaScript). Get DSN for each. Env vars: SENTRY_DSN, SENTRY_DSN_JS. FREE tier: 5,000 errors/month.', category: 'Optional' },
        { id: 'cred-gsc', text: 'Google Search Console', detail: 'Go to search.google.com/search-console → Add property → Verify with HTML meta tag. Copy verification code to GOOGLE_SITE_VERIFICATION env var.', category: 'Recommended' },
        { id: 'cred-bing', text: 'Bing Webmaster Tools', detail: 'Go to bing.com/webmasters → Add site → Verify. Copy code to BING_SITE_VERIFICATION env var.', category: 'Recommended' },
        { id: 'cred-indexnow', text: 'IndexNow Key', detail: 'Generate: php -r "echo bin2hex(random_bytes(16));" — save as INDEXNOW_KEY and create file at /public_html/{key}.txt containing the key.', category: 'Optional' },
        { id: 'cred-vapid', text: 'VAPID Keys (push notifications)', detail: 'Run on server: php cli/generate-vapid-keys.php. Keys auto-saved to database. Set VAPID_SUBJECT=mailto:contact@oregon.tires in .env.', category: 'Recommended' },
      ]
    },
    {
      id: 'independence',
      icon: '🔗',
      title: 'Network Independence',
      description: 'Decouple from the 1vsM developer network (shared kits).',
      items: [
        { id: 'ind-member-kit', text: 'Replace member-kit with standalone auth', detail: 'Build local members table + auth class. Member-kit handles login/register/OAuth/password-reset. Estimated effort: 2-3 days. Currently 24 API endpoints wrap member-kit.' },
        { id: 'ind-form-kit', text: 'Replace form-kit with local contact handler', detail: 'Oregon Tires already has oretir_contact_messages table. Replace 4 API wrappers in api/form/ with direct DB queries. Estimated effort: 4 hours.' },
        { id: 'ind-commerce-kit', text: 'Replace commerce-kit with direct PayPal/Stripe', detail: 'Replace 6 API wrappers in api/commerce/ with direct PayPal REST API calls. Estimated effort: 1-2 days.' },
        { id: 'ind-engine-kit', text: 'Remove engine-kit (optional error tracking)', detail: 'Delete engine-kit references in bootstrap.php. Errors fall back to error_log automatically. Estimated effort: 30 minutes.' },
        { id: 'ind-cors', text: 'Remove HipHop.World CORS and cross-DB references', detail: 'Remove CORS headers for hiphop.world in bootstrap.php. Remove HW_DB_NAME cross-database queries in member-kit-init.php. Remove SYNC_API_KEY.' },
        { id: 'ind-deploy', text: 'Update deploy.sh for new server', detail: 'Change SSH_HOST and REMOTE_ROOT in deploy.sh to point to client\'s server. Or configure via DEPLOY_SSH_HOST and DEPLOY_REMOTE_PATH env vars.' },
      ]
    },
    {
      id: 'features',
      icon: '🚀',
      title: 'New Features to Integrate',
      description: 'Recommended additions to enhance the platform.',
      items: [
        { id: 'feat-whatsapp', text: 'WhatsApp Business API (FREE messaging)', detail: 'Already integrated in code. Just add credentials. Replaces paid Twilio SMS. Fallback chain: WhatsApp → SMS → Email.' },
        { id: 'feat-google-business', text: 'Google Business Profile API', detail: 'Auto-post updates, respond to reviews from admin panel. Requires Google Business Profile API access.' },
        { id: 'feat-quickbooks', text: 'QuickBooks / Wave (accounting sync)', detail: 'Sync invoices and payments to accounting software. Wave is FREE. QuickBooks $30/mo.' },
        { id: 'feat-stripe-terminal', text: 'Stripe Terminal (in-person payments)', detail: 'Accept card payments at the counter. Reader: $59 one-time. Processing: 2.7% + 5¢.' },
        { id: 'feat-google-calendar', text: 'Google Calendar API (appointment sync)', detail: 'Two-way sync appointments with Google Calendar. Techs see schedule on their phones.' },
        { id: 'feat-parts', text: 'Parts Ordering API (Nexpart / WHI)', detail: 'Look up and order parts directly from the RO detail screen. Nexpart or WHI Solutions integration.' },
        { id: 'feat-fleet', text: 'Fleet Management Portal', detail: 'Dedicated portal for commercial fleet clients. Volume pricing, priority scheduling, fleet-wide reports.' },
        { id: 'feat-nps', text: 'Customer Satisfaction Surveys (NPS)', detail: 'Auto-send post-service survey. Track Net Promoter Score over time. Identify at-risk customers.' },
        { id: 'feat-ai-chat', text: 'AI Chat Assistant (customer-facing)', detail: 'Bilingual chatbot on website for appointment booking, FAQs, and service info. Use Claude API or GPT.' },
      ]
    },
    {
      id: 'maintenance',
      icon: '🛠️',
      title: 'Monthly Maintenance',
      description: 'Ongoing tasks to keep the platform healthy.',
      items: [
        { id: 'maint-ssl', text: 'SSL certificate auto-renewal', detail: 'If using Let\'s Encrypt, renewals are automatic. Verify monthly that https:// works.' },
        { id: 'maint-backup', text: 'Database backups (daily)', detail: 'Set up automated daily MySQL backups via cPanel → Backup Wizard. Keep 30 days of backups.' },
        { id: 'maint-errors', text: 'Review error logs weekly', detail: 'Check cPanel → Error Log or Sentry dashboard. Fix recurring errors promptly.' },
        { id: 'maint-composer', text: 'Update PHP dependencies monthly', detail: 'SSH in, run: composer update --no-dev. Test site after updates.' },
        { id: 'maint-analytics', text: 'Review Google Analytics monthly', detail: 'Check traffic, top pages, conversion rates. Adjust marketing accordingly.' },
        { id: 'maint-reviews', text: 'Respond to Google Reviews', detail: 'Check reviews weekly. Respond professionally to all reviews (positive and negative).' },
        { id: 'maint-content', text: 'Update blog and promotions', detail: 'Post 2-4 blog articles per month. Update seasonal promotions. Keep FAQ current.' },
        { id: 'maint-sw', text: 'Bump service worker cache version', detail: 'After any file changes deployed, increment CACHE_VERSION in sw.js to invalidate old caches.' },
      ]
    },
  ];

  // ─── Render ───────────────────────────────────────────────────────────────
  function renderHandoff(container) {
    container.textContent = '';
    var checked = getChecked();

    // Progress overview
    var totalItems = 0, checkedItems = 0;
    sections.forEach(function(s) { s.items.forEach(function(item) { totalItems++; if (checked[item.id]) checkedItems++; }); });
    var pct = totalItems ? Math.round((checkedItems / totalItems) * 100) : 0;

    var header = document.createElement('div');
    header.className = 'mb-6';
    var h2 = document.createElement('h2');
    h2.className = 'text-2xl font-bold text-gray-900 dark:text-white mb-2';
    h2.textContent = 'Oregon Tires — Independence & Handoff Guide';
    header.appendChild(h2);
    var desc = document.createElement('p');
    desc.className = 'text-sm text-gray-500 dark:text-gray-400 mb-4';
    desc.textContent = 'Complete checklist for transferring the platform to client-owned infrastructure. Progress is saved locally in your browser.';
    header.appendChild(desc);

    // Progress bar
    var progWrap = document.createElement('div');
    progWrap.className = 'flex items-center gap-3';
    var progBar = document.createElement('div');
    progBar.className = 'flex-1 h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden';
    var progFill = document.createElement('div');
    progFill.className = 'h-full rounded-full transition-all ' + (pct === 100 ? 'bg-green-500' : 'bg-brand');
    progFill.style.width = pct + '%';
    progBar.appendChild(progFill);
    progWrap.appendChild(progBar);
    var progLabel = document.createElement('span');
    progLabel.className = 'text-sm font-bold ' + (pct === 100 ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-300');
    progLabel.textContent = checkedItems + '/' + totalItems + ' (' + pct + '%)';
    progWrap.appendChild(progLabel);
    header.appendChild(progWrap);
    container.appendChild(header);

    // Sections
    sections.forEach(function(section) {
      var sectionChecked = section.items.filter(function(i) { return checked[i.id]; }).length;
      var sectionTotal = section.items.length;
      var allDone = sectionChecked === sectionTotal;

      var details = document.createElement('details');
      details.className = 'border dark:border-gray-700 rounded-xl overflow-hidden mb-3' + (allDone ? ' border-green-300 dark:border-green-700' : '');
      if (!allDone && sectionChecked === 0) details.open = (section.id === 'hosting'); // auto-open first incomplete section

      var summary = document.createElement('summary');
      summary.className = 'px-5 py-3 cursor-pointer select-none flex items-center justify-between ' + (allDone ? 'bg-green-50 dark:bg-green-900/20' : 'bg-gray-50 dark:bg-gray-800/50') + ' hover:bg-gray-100 dark:hover:bg-gray-700/50 transition';
      var left = document.createElement('div');
      left.className = 'flex items-center gap-3';
      var icon = document.createElement('span');
      icon.className = 'text-xl';
      icon.textContent = allDone ? '✅' : section.icon;
      left.appendChild(icon);
      var titleSpan = document.createElement('span');
      titleSpan.className = 'font-semibold text-gray-900 dark:text-white';
      titleSpan.textContent = section.title;
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
        descP.textContent = section.description;
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
          renderHandoff(container);
        });
        row.appendChild(cb);

        var content = document.createElement('div');
        content.className = 'flex-1 min-w-0';

        var labelRow = document.createElement('div');
        labelRow.className = 'flex items-center gap-2 flex-wrap';
        var label = document.createElement('span');
        label.className = 'text-sm font-medium ' + (checked[item.id] ? 'text-green-700 dark:text-green-400 line-through' : 'text-gray-900 dark:text-white');
        label.textContent = item.text;
        labelRow.appendChild(label);
        if (item.category) {
          var catBadge = document.createElement('span');
          var catColors = { Required: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400', Recommended: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400', Optional: 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' };
          catBadge.className = 'text-[10px] px-1.5 py-0.5 rounded font-medium ' + (catColors[item.category] || catColors.Optional);
          catBadge.textContent = item.category;
          labelRow.appendChild(catBadge);
        }
        content.appendChild(labelRow);

        if (item.detail) {
          var detailP = document.createElement('p');
          detailP.className = 'text-xs text-gray-500 dark:text-gray-400 mt-1 whitespace-pre-line';
          detailP.textContent = item.detail;
          content.appendChild(detailP);
        }

        row.appendChild(content);
        body.appendChild(row);
      });

      details.appendChild(body);
      container.appendChild(details);
    });

    // Reset button
    var resetWrap = document.createElement('div');
    resetWrap.className = 'mt-6 text-center';
    var resetBtn = document.createElement('button');
    resetBtn.className = 'text-xs text-gray-400 dark:text-gray-500 hover:text-red-500 transition';
    resetBtn.textContent = 'Reset all checkboxes';
    resetBtn.addEventListener('click', function() {
      if (confirm('Reset all checklist progress?')) {
        localStorage.removeItem(STORAGE_KEY);
        renderHandoff(container);
      }
    });
    resetWrap.appendChild(resetBtn);
    container.appendChild(resetWrap);
  }

  // ─── Expose ───────────────────────────────────────────────────────────────
  window.renderClientHandoff = renderHandoff;
})();
