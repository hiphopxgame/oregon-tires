<?php declare(strict_types=1);
/**
 * Form Kit — Contact Form Template
 *
 * Self-contained, bilingual (EN/ES) contact form with inline CSS and JS.
 * Dark theme by default with emerald accent. No external dependencies.
 *
 * Usage:
 *   $formConfig = ['site_key' => 'oregon.tires', 'api_base' => '/api/form'];
 *   include FORM_KIT_TEMPLATES . '/form/contact.php';
 */

$formConfig = array_merge([
    'site_key' => '', 'api_base' => '/api/form', 'form_type' => 'contact',
    'success_message' => '', 'show_phone' => true, 'show_subject' => true,
    'honeypot_field' => '_hp_email', 'custom_fields' => null, 'lang' => 'en',
    'translations' => null, 'theme' => 'dark', 'accent_color' => '#10b981',
], $formConfig ?? []);

$siteKey       = htmlspecialchars($formConfig['site_key'], ENT_QUOTES, 'UTF-8');
$apiBase       = htmlspecialchars($formConfig['api_base'], ENT_QUOTES, 'UTF-8');
$formType      = htmlspecialchars($formConfig['form_type'], ENT_QUOTES, 'UTF-8');
$showPhone     = $formConfig['show_phone'] ? 'true' : 'false';
$showSubject   = $formConfig['show_subject'] ? 'true' : 'false';
$honeypotField = htmlspecialchars($formConfig['honeypot_field'], ENT_QUOTES, 'UTF-8');
$defaultLang   = htmlspecialchars($formConfig['lang'], ENT_QUOTES, 'UTF-8');
$theme         = htmlspecialchars($formConfig['theme'], ENT_QUOTES, 'UTF-8');
$accent        = htmlspecialchars($formConfig['accent_color'], ENT_QUOTES, 'UTF-8');
$customTranslationsJson = $formConfig['translations'] ? json_encode($formConfig['translations']) : 'null';
$successOverride = htmlspecialchars($formConfig['success_message'], ENT_QUOTES, 'UTF-8');

// Generate CSRF token if session is active (parent page must start session before output)
$csrfToken = '';
if (session_status() === PHP_SESSION_ACTIVE) {
    $csrfToken = bin2hex(random_bytes(32));
    $_SESSION['form_kit_csrf'] = $csrfToken;
    $_SESSION['form_kit_csrf_time'] = time();
}
?>
<style>
.fk-container {
    --fk-bg: <?php echo $theme === 'dark' ? '#0A0A0A' : '#f9fafb'; ?>;
    --fk-card: <?php echo $theme === 'dark' ? '#111827' : '#ffffff'; ?>;
    --fk-input-bg: <?php echo $theme === 'dark' ? '#1f2937' : '#f3f4f6'; ?>;
    --fk-border: <?php echo $theme === 'dark' ? '#374151' : '#d1d5db'; ?>;
    --fk-text: <?php echo $theme === 'dark' ? '#f3f4f6' : '#111827'; ?>;
    --fk-muted: <?php echo $theme === 'dark' ? '#9ca3af' : '#6b7280'; ?>;
    --fk-accent: <?php echo $accent; ?>;
    --fk-accent-h: <?php echo $theme === 'dark' ? '#34d399' : '#059669'; ?>;
    --fk-error: #ef4444;
    --fk-ok-bg: <?php echo $theme === 'dark' ? '#064e3b' : '#d1fae5'; ?>;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    max-width: 560px; margin: 0 auto; padding: 24px 16px; color: var(--fk-text); position: relative;
}
.fk-card { background: var(--fk-card); border: 1px solid var(--fk-border); border-radius: 16px; padding: 32px 28px; position: relative; }
@media (max-width: 480px) { .fk-card { padding: 24px 16px; } }
.fk-lang-toggle { position: absolute; top: 16px; right: 16px; background: var(--fk-input-bg); border: 1px solid var(--fk-border); color: var(--fk-muted); font-size: 12px; font-weight: 600; letter-spacing: .5px; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: border-color .2s, color .2s; z-index: 2; }
.fk-lang-toggle:hover { border-color: var(--fk-accent); color: var(--fk-accent); }
.fk-title { font-size: 24px; font-weight: 700; margin: 0 0 4px; color: var(--fk-text); }
.fk-subtitle { font-size: 14px; color: var(--fk-muted); margin: 0 0 28px; }
.fk-field { margin-bottom: 20px; }
.fk-label { display: block; font-size: 13px; font-weight: 600; color: var(--fk-text); margin-bottom: 6px; }
.fk-required { color: var(--fk-accent); margin-left: 2px; }
.fk-input, .fk-textarea { display: block; width: 100%; padding: 10px 14px; font-size: 14px; font-family: inherit; color: var(--fk-text); background: var(--fk-input-bg); border: 1px solid var(--fk-border); border-radius: 10px; outline: none; transition: border-color .2s, box-shadow .2s; box-sizing: border-box; }
.fk-input::placeholder, .fk-textarea::placeholder { color: var(--fk-muted); opacity: .7; }
.fk-input:focus, .fk-textarea:focus { border-color: var(--fk-accent); box-shadow: 0 0 0 3px rgba(16,185,129,.15); }
.fk-input.fk-err, .fk-textarea.fk-err { border-color: var(--fk-error); box-shadow: 0 0 0 3px rgba(239,68,68,.12); }
.fk-textarea { min-height: 120px; resize: vertical; }
.fk-field-error { font-size: 12px; color: var(--fk-error); margin-top: 4px; }
.fk-honeypot { position: absolute; left: -9999px; top: -9999px; opacity: 0; height: 0; width: 0; overflow: hidden; pointer-events: none; }
.fk-submit { display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 12px 24px; font-size: 15px; font-weight: 600; font-family: inherit; color: #fff; background: var(--fk-accent); border: none; border-radius: 10px; cursor: pointer; transition: background .2s, transform .1s; margin-top: 4px; }
.fk-submit:hover:not(:disabled) { background: var(--fk-accent-h); }
.fk-submit:active:not(:disabled) { transform: scale(.98); }
.fk-submit:disabled { opacity: .6; cursor: not-allowed; }
.fk-spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid rgba(255,255,255,.3); border-top-color: #fff; border-radius: 50%; animation: fk-spin .6s linear infinite; }
@keyframes fk-spin { to { transform: rotate(360deg); } }
.fk-error-banner { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3); border-radius: 10px; padding: 12px 16px; margin-top: 16px; font-size: 13px; color: var(--fk-error); }
.fk-success-view { text-align: center; padding: 40px 20px; }
.fk-success-icon { width: 64px; height: 64px; margin: 0 auto 20px; background: var(--fk-ok-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.fk-success-icon svg { width: 32px; height: 32px; stroke: var(--fk-accent); }
.fk-success-title { font-size: 22px; font-weight: 700; margin: 0 0 8px; color: var(--fk-text); }
.fk-success-msg { font-size: 14px; color: var(--fk-muted); margin: 0 0 24px; line-height: 1.6; }
.fk-send-another { display: inline-block; font-size: 14px; font-weight: 600; color: var(--fk-accent); text-decoration: none; cursor: pointer; transition: color .2s; }
.fk-send-another:hover { color: var(--fk-accent-h); }
</style>

<div id="form-kit-contact" class="fk-container">
  <div class="fk-card">
    <button type="button" class="fk-lang-toggle" id="fk-lang-toggle" aria-label="Toggle language">EN | ES</button>

    <!-- Form View -->
    <div id="fk-form-view">
      <h2 class="fk-title" data-t="form_title">Contact Us</h2>
      <p class="fk-subtitle" data-t="form_subtitle">We'd love to hear from you</p>

      <form id="fk-contact-form" novalidate>
        <div class="fk-field">
          <label class="fk-label" for="fk-name"><span data-t="name_label">Full Name</span><span class="fk-required">*</span></label>
          <input type="text" id="fk-name" name="name" class="fk-input" data-t-placeholder="name_placeholder" placeholder="Your name" required autocomplete="name" aria-describedby="fk-name-error" aria-required="true">
          <div class="fk-field-error" id="fk-name-error" role="alert"></div>
        </div>

        <div class="fk-field">
          <label class="fk-label" for="fk-email"><span data-t="email_label">Email Address</span><span class="fk-required">*</span></label>
          <input type="email" id="fk-email" name="email" class="fk-input" data-t-placeholder="email_placeholder" placeholder="you@example.com" required autocomplete="email" aria-describedby="fk-email-error" aria-required="true">
          <div class="fk-field-error" id="fk-email-error" role="alert"></div>
        </div>

        <?php if ($formConfig['show_phone']): ?>
        <div class="fk-field" id="fk-phone-field">
          <label class="fk-label" for="fk-phone"><span data-t="phone_label">Phone Number</span></label>
          <input type="tel" id="fk-phone" name="phone" class="fk-input" data-t-placeholder="phone_placeholder" placeholder="(555) 123-4567" autocomplete="tel">
        </div>
        <?php endif; ?>

        <?php if ($formConfig['show_subject']): ?>
        <div class="fk-field" id="fk-subject-field">
          <label class="fk-label" for="fk-subject"><span data-t="subject_label">Subject</span></label>
          <input type="text" id="fk-subject" name="subject" class="fk-input" data-t-placeholder="subject_placeholder" placeholder="What is this about?">
        </div>
        <?php endif; ?>

        <div class="fk-field">
          <label class="fk-label" for="fk-message"><span data-t="message_label">Message</span><span class="fk-required">*</span></label>
          <textarea id="fk-message" name="message" class="fk-textarea" data-t-placeholder="message_placeholder" placeholder="Tell us what's on your mind..." required aria-describedby="fk-message-error" aria-required="true"></textarea>
          <div class="fk-field-error" id="fk-message-error" role="alert"></div>
        </div>

        <input type="hidden" id="fk-csrf" name="_csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

        <div class="fk-honeypot" aria-hidden="true">
          <label for="fk-hp">Leave blank</label>
          <input type="text" id="fk-hp" name="<?php echo $honeypotField; ?>" tabindex="-1" autocomplete="off">
        </div>

        <button type="submit" class="fk-submit" id="fk-submit" data-t="submit_button">Send Message</button>
      </form>

      <div id="fk-error" class="fk-error-banner" style="display:none" role="alert"></div>
    </div>

    <!-- Success View -->
    <div id="fk-success-view" class="fk-success-view" style="display:none" role="alert" aria-live="polite">
      <div class="fk-success-icon">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
      </div>
      <h3 class="fk-success-title" data-t="success_title">Message Sent!</h3>
      <p class="fk-success-msg" id="fk-success-msg" data-t="success_message">Thank you for reaching out. We'll get back to you soon.</p>
      <a class="fk-send-another" id="fk-send-another" href="#" data-t="send_another">Send another message</a>
    </div>
  </div>
</div>

<script>
(function() {
    'use strict';

    var FK = {
        siteKey: '<?php echo $siteKey; ?>', apiBase: '<?php echo $apiBase; ?>',
        formType: '<?php echo $formType; ?>', honeypot: '<?php echo $honeypotField; ?>',
        defaultLang: '<?php echo $defaultLang; ?>', successOverride: '<?php echo $successOverride; ?>'
    };

    var translations = {
        en: {
            form_title: 'Contact Us', form_subtitle: "We'd love to hear from you",
            name_label: 'Full Name', name_placeholder: 'Your name',
            email_label: 'Email Address', email_placeholder: 'you@example.com',
            phone_label: 'Phone Number', phone_placeholder: '(555) 123-4567',
            subject_label: 'Subject', subject_placeholder: 'What is this about?',
            message_label: 'Message', message_placeholder: "Tell us what's on your mind...",
            submit_button: 'Send Message', submitting: 'Sending...',
            success_title: 'Message Sent!',
            success_message: "Thank you for reaching out. We'll get back to you soon.",
            error_title: 'Something went wrong', required_field: 'This field is required',
            invalid_email: 'Please enter a valid email',
            message_too_short: 'Message must be at least 10 characters',
            send_another: 'Send another message'
        },
        es: {
            form_title: 'Cont\u00e1ctenos', form_subtitle: 'Nos encantar\u00eda saber de usted',
            name_label: 'Nombre Completo', name_placeholder: 'Su nombre',
            email_label: 'Correo Electr\u00f3nico', email_placeholder: 'usted@ejemplo.com',
            phone_label: 'Tel\u00e9fono', phone_placeholder: '(555) 123-4567',
            subject_label: 'Asunto', subject_placeholder: '\u00bfDe qu\u00e9 se trata?',
            message_label: 'Mensaje', message_placeholder: 'Cu\u00e9ntenos lo que tiene en mente...',
            submit_button: 'Enviar Mensaje', submitting: 'Enviando...',
            success_title: '\u00a1Mensaje Enviado!',
            success_message: 'Gracias por comunicarse. Le responderemos pronto.',
            error_title: 'Algo sali\u00f3 mal', required_field: 'Este campo es obligatorio',
            invalid_email: 'Ingrese un correo v\u00e1lido',
            message_too_short: 'El mensaje debe tener al menos 10 caracteres',
            send_another: 'Enviar otro mensaje'
        }
    };

    // Merge custom overrides
    var overrides = <?php echo $customTranslationsJson; ?>;
    if (overrides) {
        Object.keys(overrides).forEach(function(lang) {
            if (translations[lang]) Object.assign(translations[lang], overrides[lang]);
            else translations[lang] = overrides[lang];
        });
    }
    if (FK.successOverride) translations.en.success_message = FK.successOverride;

    var currentLang = FK.defaultLang || 'en';
    var isSubmitting = false;

    // DOM refs
    var container   = document.getElementById('form-kit-contact');
    var form        = document.getElementById('fk-contact-form');
    var formView    = document.getElementById('fk-form-view');
    var successView = document.getElementById('fk-success-view');
    var errorBanner = document.getElementById('fk-error');
    var submitBtn   = document.getElementById('fk-submit');
    var langToggle  = document.getElementById('fk-lang-toggle');
    var sendAnother = document.getElementById('fk-send-another');

    function t(key) {
        return (translations[currentLang] || translations.en)[key] || translations.en[key] || key;
    }

    function applyTranslations() {
        var els = container.querySelectorAll('[data-t]');
        for (var i = 0; i < els.length; i++) els[i].textContent = t(els[i].getAttribute('data-t'));
        var phs = container.querySelectorAll('[data-t-placeholder]');
        for (var j = 0; j < phs.length; j++) phs[j].placeholder = t(phs[j].getAttribute('data-t-placeholder'));
        langToggle.textContent = currentLang === 'en' ? 'EN | ES' : 'ES | EN';
    }

    function clearErrors() {
        var errs = container.querySelectorAll('.fk-field-error');
        for (var i = 0; i < errs.length; i++) errs[i].textContent = '';
        var inputs = container.querySelectorAll('.fk-err');
        for (var j = 0; j < inputs.length; j++) {
            inputs[j].classList.remove('fk-err');
            inputs[j].removeAttribute('aria-invalid');
        }
    }

    function showFieldError(id, msg) {
        var el = document.getElementById(id);
        var errEl = document.getElementById(id + '-error');
        if (el) {
            el.classList.add('fk-err');
            el.setAttribute('aria-invalid', 'true');
        }
        if (errEl) errEl.textContent = msg;
    }

    function validateForm() {
        clearErrors();
        var valid = true;
        var name = (document.getElementById('fk-name').value || '').trim();
        var email = (document.getElementById('fk-email').value || '').trim();
        var message = (document.getElementById('fk-message').value || '').trim();

        if (!name)    { showFieldError('fk-name', t('required_field')); valid = false; }
        if (!email)   { showFieldError('fk-email', t('required_field')); valid = false; }
        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showFieldError('fk-email', t('invalid_email')); valid = false; }
        if (!message) { showFieldError('fk-message', t('required_field')); valid = false; }
        else if (message.length < 10) { showFieldError('fk-message', t('message_too_short')); valid = false; }
        return valid;
    }

    function showError(msg) {
        errorBanner.textContent = msg;
        errorBanner.style.display = 'block';
        setTimeout(function() { errorBanner.style.display = 'none'; }, 8000);
    }

    function setLoading(loading) {
        isSubmitting = loading;
        submitBtn.disabled = loading;
        while (submitBtn.firstChild) submitBtn.removeChild(submitBtn.firstChild);
        if (loading) {
            var sp = document.createElement('span'); sp.className = 'fk-spinner'; submitBtn.appendChild(sp);
            var tx = document.createElement('span'); tx.textContent = t('submitting'); submitBtn.appendChild(tx);
        } else {
            submitBtn.textContent = t('submit_button');
        }
    }

    function handleSubmit(e) {
        e.preventDefault();
        if (isSubmitting) return;
        errorBanner.style.display = 'none';
        if (!validateForm()) return;

        // Honeypot check — bots fill this, humans never see it
        var hp = document.getElementById('fk-hp');
        if (hp && hp.value) { showSuccess(); return; }

        setLoading(true);

        var csrfEl = document.getElementById('fk-csrf');
        var payload = {
            site_key: FK.siteKey, form_type: FK.formType,
            _csrf_token: csrfEl ? csrfEl.value : '',
            name: document.getElementById('fk-name').value.trim(),
            email: document.getElementById('fk-email').value.trim(),
            message: document.getElementById('fk-message').value.trim()
        };
        var phone = document.getElementById('fk-phone');
        if (phone && phone.value.trim()) payload.phone = phone.value.trim();
        var subject = document.getElementById('fk-subject');
        if (subject && subject.value.trim()) payload.subject = subject.value.trim();

        fetch(FK.apiBase.replace(/\/+$/, '') + '/submit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify(payload)
        })
        .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
        .then(function(res) {
            setLoading(false);
            if (res.ok && res.data.success) showSuccess();
            else { showError(res.data.error || t('error_title')); console.error('Form Kit submit error:', res.data); }
        })
        .catch(function(err) {
            setLoading(false);
            showError(t('error_title'));
            console.error('Form Kit network error:', err);
        });
    }

    function showSuccess() { formView.style.display = 'none'; successView.style.display = 'block'; }

    function resetForm() {
        form.reset(); clearErrors();
        errorBanner.style.display = 'none';
        setLoading(false);
        successView.style.display = 'none';
        formView.style.display = 'block';
    }

    // Events
    form.addEventListener('submit', handleSubmit);
    sendAnother.addEventListener('click', function(e) { e.preventDefault(); resetForm(); });
    langToggle.addEventListener('click', function() {
        currentLang = currentLang === 'en' ? 'es' : 'en';
        applyTranslations();
    });

    // Clear field errors on input
    var allInputs = container.querySelectorAll('.fk-input, .fk-textarea');
    for (var i = 0; i < allInputs.length; i++) {
        allInputs[i].addEventListener('input', function() {
            this.classList.remove('fk-err');
            this.removeAttribute('aria-invalid');
            var errEl = document.getElementById(this.id + '-error');
            if (errEl) errEl.textContent = '';
        });
    }

    applyTranslations();
})();
</script>
