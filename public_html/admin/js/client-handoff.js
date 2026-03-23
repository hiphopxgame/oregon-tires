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
      title: 'Web Hosting',
      description: 'Get your own hosting account. All recommended hosts include free SSL, email, backups, and one-click setup.',
      items: [
        { id: 'host-choose', text: 'Sign up for a web host', detail: 'Pick one of these (all include SSL, email, backups, cPanel):\n\n• SiteGround ($15/mo) — Best support, fastest, auto-updates. Recommended.\n• A2 Hosting ($5/mo) — Great value, reliable, developer-friendly.\n• Hostinger ($3/mo) — Budget option, works well for small business.\n\nAll three include everything you need — no extra purchases required.' },
        { id: 'host-share', text: 'Share your hosting login with your developer', detail: 'After signing up, share your cPanel login URL, username, and password with your developer so they can set up the site. They will handle the database, file upload, and configuration.' },
        { id: 'host-email', text: 'Set up your business email', detail: 'In your hosting cPanel, create email accounts like contact@oregon.tires and info@oregon.tires. Your hosting includes free email. Share the email credentials with your developer.' },
      ]
    },
    {
      id: 'domain',
      icon: '🌐',
      title: 'Domain Ownership',
      description: 'Transfer the oregon.tires domain into your name.',
      items: [
        { id: 'dom-registrar', text: 'Create a domain registrar account', detail: 'Sign up at Namecheap.com or Cloudflare.com (both are reputable and affordable). This is where your domain will live.' },
        { id: 'dom-transfer', text: 'Receive domain transfer from developer', detail: 'Your developer will unlock the domain and send you a transfer code. Enter it in your registrar to complete the transfer. You\'ll get a confirmation email — click to approve.' },
        { id: 'dom-verify', text: 'Verify the website loads', detail: 'After transfer completes (can take up to 48 hours), visit https://oregon.tires to confirm everything works. Check that the lock icon shows in the browser (SSL).' },
      ]
    },
    {
      id: 'accounts',
      icon: '🔑',
      title: 'Business Accounts to Create',
      description: 'Set up your own accounts for the services your website uses. Share credentials with your developer to connect them.',
      items: [
        { id: 'acct-google', text: 'Google Cloud account (reviews + customer login)', detail: 'Go to console.cloud.google.com and sign in with your business Gmail. This one account powers two features:\n\n1. "Login with Google" for your customers\n2. Google Reviews displayed on your website\n\nYour developer will set up the project — just create the account and share access.', category: 'Required' },
        { id: 'acct-whatsapp', text: 'WhatsApp Business (FREE customer messaging)', detail: 'Go to business.facebook.com and create a Meta Business account using your Facebook. Then go to developers.facebook.com and add the WhatsApp product.\n\nThis gives you FREE automated messages to customers — appointment reminders, estimate approvals, vehicle ready notifications.\n\nFREE: 1,000 messages/month (more than enough for most shops).', category: 'Required' },
        { id: 'acct-paypal', text: 'PayPal Business (accept payments)', detail: 'Go to paypal.com/business and create a PayPal Business account using your business email. This lets customers pay for care plans and services online.\n\nYour developer will connect it to the website.', category: 'Required' },
        { id: 'acct-gsc', text: 'Google Search Console (SEO monitoring)', detail: 'Go to search.google.com/search-console and sign in with your business Gmail. Click "Add property" and add oregon.tires. This lets you see how your website appears in Google search results.\n\nFREE — highly recommended for any business website.', category: 'Recommended' },
        { id: 'acct-stripe', text: 'Stripe (in-person + online payments)', detail: 'Go to stripe.com and create an account. Stripe lets you accept credit cards online and in-person at the shop counter with a card reader ($59 one-time).\n\nProcessing: 2.7% + 5¢ per in-person swipe, 2.9% + 30¢ online.', category: 'Optional' },
        { id: 'acct-sentry', text: 'Sentry (error monitoring)', detail: 'Go to sentry.io and create a free account. This monitors your website for errors and alerts you when something breaks.\n\nFREE: 5,000 error reports/month (more than enough).', category: 'Optional' },
      ]
    },
    {
      id: 'features',
      icon: '🚀',
      title: 'Features to Add',
      description: 'Recommended upgrades to grow your business. Discuss with your developer which to prioritize.',
      items: [
        { id: 'feat-whatsapp', text: 'WhatsApp messaging (ready to activate)', detail: 'The code is already built into your website. Once you create your WhatsApp Business account (see above), your developer just enters the credentials and it\'s live. Customers get automated messages for appointment reminders, estimates, and vehicle pickups — all FREE.', category: 'Ready' },
        { id: 'feat-google-business', text: 'Google Business Profile management', detail: 'Post shop updates, respond to reviews, and update your hours directly from your admin dashboard instead of going to Google separately.' },
        { id: 'feat-quickbooks', text: 'Accounting software sync', detail: 'Automatically send invoices and payment data to QuickBooks ($30/mo) or Wave (FREE). Saves hours of manual bookkeeping.' },
        { id: 'feat-stripe-terminal', text: 'Card reader at the counter', detail: 'Accept credit card payments at the shop counter using a Stripe card reader. Reader costs $59 one-time. Transactions sync with your online records automatically.' },
        { id: 'feat-google-calendar', text: 'Google Calendar sync', detail: 'Appointments automatically appear on your Google Calendar. Technicians can see their schedules on their phones without logging into the admin panel.' },
        { id: 'feat-parts', text: 'Parts ordering from admin', detail: 'Look up and order parts directly from the repair order screen. No switching between websites.' },
        { id: 'feat-fleet', text: 'Fleet management portal', detail: 'A dedicated portal for commercial fleet customers (delivery companies, taxis, etc.). Volume pricing, priority scheduling, and fleet-wide reports.' },
        { id: 'feat-nps', text: 'Customer satisfaction surveys', detail: 'Automatically send a short survey after each service. Track your customer satisfaction score over time and catch unhappy customers before they leave a bad review.' },
        { id: 'feat-ai-chat', text: 'AI chat assistant on website', detail: 'A bilingual chatbot that helps customers book appointments, answers common questions, and provides service info — 24/7, even when the shop is closed.' },
      ]
    },
    {
      id: 'maintenance',
      icon: '🛠️',
      title: 'Ongoing Business Tasks',
      description: 'Keep your website and online presence healthy with these regular tasks.',
      items: [
        { id: 'maint-reviews', text: 'Respond to Google Reviews weekly', detail: 'Check your Google Business reviews every week. Respond to ALL reviews — thank positive reviewers and professionally address any complaints. This directly impacts whether new customers choose you.' },
        { id: 'maint-content', text: 'Update blog and promotions monthly', detail: 'Post 2-4 blog articles per month about tire care, seasonal tips, or shop news. Update promotions for seasonal specials. Fresh content helps your Google ranking.' },
        { id: 'maint-analytics', text: 'Review your website traffic monthly', detail: 'Check your admin Analytics tab to see how many visitors you\'re getting, which services are most popular, and where customers come from. Use this to focus your marketing.' },
        { id: 'maint-backup', text: 'Verify backups are running', detail: 'Your hosting provider handles daily backups automatically. Once a month, log into cPanel and confirm backups are working. This protects your customer data.' },
        { id: 'maint-ssl', text: 'Verify SSL certificate is active', detail: 'Visit your website and check that the lock icon appears in the browser address bar. SSL is auto-renewed by your host, but verify monthly.' },
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
    h2.textContent = 'Oregon Tires — Setup & Ownership Guide';
    header.appendChild(h2);
    var desc = document.createElement('p');
    desc.className = 'text-sm text-gray-500 dark:text-gray-400 mb-4';
    desc.textContent = 'Everything you need to fully own and operate your Oregon Tires website. Check off each item as you complete it — your progress is saved automatically.';
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
