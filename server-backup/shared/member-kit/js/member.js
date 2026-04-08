/* ═══════════════════════════════════════════════════════════════════════════
   Member Kit — JavaScript
   Vanilla JS module for login, register, profile, settings, activity.
   NO frameworks. NO innerHTML. createElement/textContent/appendChild only.
   ═══════════════════════════════════════════════════════════════════════════ */

(function () {
    'use strict';

    // ── Configurable login URL ─────────────────────────────────────────────
    // Sites can set window.MEMBER_LOGIN_URL before loading this script.
    // Falls back to '/member/login' for backward compatibility.
    var LOGIN_URL = window.MEMBER_LOGIN_URL || '/member/login';

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * Read CSRF token from <meta name="csrf-token" content="...">
     */
    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') || '' : '';
    }

    /**
     * Safe same-origin check for redirect URLs.
     * Only allows relative paths or same-origin absolute URLs.
     */
    function isSameOrigin(url) {
        if (!url) return false;
        // Relative paths are always safe
        if (url.charAt(0) === '/' && url.charAt(1) !== '/') return true;
        try {
            var parsed = new URL(url, window.location.origin);
            return parsed.origin === window.location.origin;
        } catch (e) {
            return false;
        }
    }

    /**
     * Get a URL search parameter value.
     */
    function getParam(name) {
        var params = new URLSearchParams(window.location.search);
        return params.get(name);
    }

    /**
     * Create an SVG icon element for toasts.
     */
    function createToastIcon(type) {
        var svgNS = 'http://www.w3.org/2000/svg';
        var svg = document.createElementNS(svgNS, 'svg');
        svg.setAttribute('class', 'member-toast-icon');
        svg.setAttribute('viewBox', '0 0 20 20');
        svg.setAttribute('fill', 'currentColor');
        svg.setAttribute('aria-hidden', 'true');

        var path = document.createElementNS(svgNS, 'path');

        switch (type) {
            case 'success':
                path.setAttribute('fill-rule', 'evenodd');
                path.setAttribute('d', 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z');
                path.setAttribute('clip-rule', 'evenodd');
                break;
            case 'error':
                path.setAttribute('fill-rule', 'evenodd');
                path.setAttribute('d', 'M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z');
                path.setAttribute('clip-rule', 'evenodd');
                break;
            case 'warning':
                path.setAttribute('fill-rule', 'evenodd');
                path.setAttribute('d', 'M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z');
                path.setAttribute('clip-rule', 'evenodd');
                break;
            default: // info
                path.setAttribute('fill-rule', 'evenodd');
                path.setAttribute('d', 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z');
                path.setAttribute('clip-rule', 'evenodd');
                break;
        }

        svg.appendChild(path);
        return svg;
    }

    // ── Toast Container (lazy-created) ────────────────────────────────────

    var toastContainer = null;

    function getToastContainer() {
        if (toastContainer && document.body.contains(toastContainer)) {
            return toastContainer;
        }
        toastContainer = document.createElement('div');
        toastContainer.className = 'member-toast-container';
        toastContainer.setAttribute('role', 'alert');
        toastContainer.setAttribute('aria-live', 'polite');
        document.body.appendChild(toastContainer);
        return toastContainer;
    }

    // ── Toast ─────────────────────────────────────────────────────────────

    /**
     * Show a toast notification.
     * @param {string} message - The message to display.
     * @param {string} type    - 'success' | 'error' | 'info' | 'warning'
     * @param {number} [duration=4000] - Auto-dismiss in milliseconds (0 = manual).
     */
    function showToast(message, type, duration) {
        if (typeof type === 'undefined') type = 'info';
        if (typeof duration === 'undefined') duration = 4000;

        var container = getToastContainer();

        var toast = document.createElement('div');
        toast.className = 'member-toast member-toast--' + type;

        // Icon
        var icon = createToastIcon(type);
        toast.appendChild(icon);

        // Message
        var msgEl = document.createElement('span');
        msgEl.className = 'member-toast-message';
        msgEl.textContent = message;
        toast.appendChild(msgEl);

        // Close button
        var closeBtn = document.createElement('button');
        closeBtn.className = 'member-toast-close';
        closeBtn.setAttribute('type', 'button');
        closeBtn.setAttribute('aria-label', 'Close notification');
        closeBtn.textContent = '\u00D7'; // multiplication sign (x)
        toast.appendChild(closeBtn);

        container.appendChild(toast);

        // Dismiss function
        var dismissed = false;
        function dismiss() {
            if (dismissed) return;
            dismissed = true;
            toast.classList.add('member-toast--exit');
            setTimeout(function () {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 250);
        }

        closeBtn.addEventListener('click', dismiss);

        if (duration > 0) {
            setTimeout(dismiss, duration);
        }

        return toast;
    }

    // ── Redirect ──────────────────────────────────────────────────────────

    /**
     * Safe redirect: only allows same-origin URLs.
     * @param {string} url - URL to redirect to.
     */
    function safeRedirect(url) {
        if (isSameOrigin(url)) {
            window.location.href = url;
        } else {
            window.location.href = '/member/profile';
        }
    }

    // ── Validation ────────────────────────────────────────────────────────

    var validators = {
        /**
         * Validate an email input value.
         * @param {string} value
         * @returns {string|null} Error message or null if valid.
         */
        email: function (value) {
            if (!value || !value.trim()) return 'Email is required';
            // Basic email pattern — intentionally permissive
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!re.test(value.trim())) return 'Please enter a valid email address';
            return null;
        },

        /**
         * Validate a password.
         * @param {string} value
         * @returns {string|null}
         */
        password: function (value) {
            if (!value) return 'Password is required';
            if (value.length < 8) return 'Password must be at least 8 characters';
            return null;
        },

        /**
         * Validate password confirmation matches.
         * @param {string} value
         * @param {HTMLFormElement} form
         * @returns {string|null}
         */
        passwordConfirm: function (value, form) {
            var pwInput = form.querySelector('input[name="password"]');
            if (!pwInput) return null;
            if (value !== pwInput.value) return 'Passwords do not match';
            return null;
        },

        /**
         * Validate required field.
         * @param {string} value
         * @returns {string|null}
         */
        required: function (value) {
            if (!value || !value.trim()) return 'This field is required';
            return null;
        }
    };

    /**
     * Show a context-aware error banner inside a form with action links.
     * @param {HTMLFormElement} form
     * @param {string} message - Error message text.
     * @param {Array<{text:string, href:string}>} actions - Clickable action links.
     */
    function showContextError(form, message, actions) {
        // Remove any existing context error
        var existing = form.parentElement.querySelector('.member-context-error');
        if (existing) existing.parentNode.removeChild(existing);

        var container = document.createElement('div');
        container.className = 'member-alert member-alert--error member-context-error';
        container.setAttribute('role', 'alert');
        container.setAttribute('aria-live', 'assertive');

        var msgSpan = document.createElement('span');
        msgSpan.textContent = message;
        container.appendChild(msgSpan);

        if (actions && actions.length) {
            var linksWrap = document.createElement('div');
            linksWrap.className = 'member-context-error__actions';
            for (var i = 0; i < actions.length; i++) {
                if (i > 0) {
                    var sep = document.createElement('span');
                    sep.textContent = ' or ';
                    sep.className = 'member-context-error__sep';
                    linksWrap.appendChild(sep);
                }
                var link = document.createElement('a');
                link.href = actions[i].href;
                link.className = 'member-link';
                link.textContent = actions[i].text;
                linksWrap.appendChild(link);
            }
            container.appendChild(linksWrap);
        }

        // Insert before the form
        form.parentElement.insertBefore(container, form);

        // Auto-dismiss after 10 seconds
        setTimeout(function () {
            if (container.parentNode) container.parentNode.removeChild(container);
        }, 10000);
    }

    /**
     * Show an inline error message below a field.
     * @param {HTMLElement} field - The .member-field wrapper.
     * @param {string} message
     */
    function showFieldError(field, message) {
        clearFieldError(field);
        var input = field.querySelector('.member-input, .member-textarea');
        if (input) {
            input.classList.add('member-input--error');
        }
        var errEl = document.createElement('p');
        errEl.className = 'member-error-text';
        errEl.textContent = message;
        field.appendChild(errEl);
    }

    /**
     * Clear inline error from a field.
     * @param {HTMLElement} field - The .member-field wrapper.
     */
    function clearFieldError(field) {
        var input = field.querySelector('.member-input, .member-textarea');
        if (input) {
            input.classList.remove('member-input--error');
        }
        var existing = field.querySelector('.member-error-text');
        if (existing && existing.parentNode) {
            existing.parentNode.removeChild(existing);
        }
        field.classList.remove('error');
    }

    /**
     * Animate a field with error shake and show error message.
     * @param {HTMLElement} field - The .member-field wrapper.
     * @param {string} [message] - Optional error message to display.
     */
    function animateError(field, message) {
        if (!field) return;

        // Trigger shake animation
        field.classList.remove('error');
        // Force reflow to restart animation
        void field.offsetWidth;
        field.classList.add('error');

        // Show error message if provided
        if (message) {
            showFieldError(field, message);
        }

        // Focus the input
        var input = field.querySelector('.member-input, .member-textarea');
        if (input) {
            input.focus();
        }

        // Remove error class after animation completes
        setTimeout(function () {
            field.classList.remove('error');
        }, 500);
    }

    /**
     * Validate an entire form. Returns true if valid.
     * @param {HTMLFormElement} form
     * @returns {boolean}
     */
    function validateForm(form) {
        var valid = true;
        var fields = form.querySelectorAll('.member-field');

        for (var i = 0; i < fields.length; i++) {
            var field = fields[i];
            var input = field.querySelector('.member-input, .member-textarea');
            if (!input) continue;

            clearFieldError(field);

            var name = input.getAttribute('name') || '';
            var type = input.getAttribute('type') || '';
            var value = input.value;
            var isRequired = input.hasAttribute('required');
            var error = null;

            // Required check
            if (isRequired) {
                error = validators.required(value);
            }

            // Type-specific validation
            if (!error && type === 'email') {
                error = validators.email(value);
            }

            if (!error && type === 'password' && name === 'password') {
                error = validators.password(value);
            }

            if (!error && name === 'password_confirm') {
                error = validators.passwordConfirm(value, form);
            }

            if (error) {
                showFieldError(field, error);
                valid = false;
            }
        }

        return valid;
    }

    // ── Form Submission ───────────────────────────────────────────────────

    /**
     * Set the loading state on a submit button.
     * @param {HTMLButtonElement} btn
     * @param {boolean} loading
     */
    function setButtonLoading(btn, loading) {
        if (!btn) return;

        if (loading) {
            btn.disabled = true;
            btn._originalText = btn.textContent;
            btn.style.opacity = '0.7';
            btn.style.pointerEvents = 'none';
            // Clear contents safely
            while (btn.firstChild) {
                btn.removeChild(btn.firstChild);
            }
            var spinner = document.createElement('span');
            spinner.className = 'member-spinner';
            btn.appendChild(spinner);
            // Context-aware loading text
            var form = btn.closest('.member-form');
            var action = form ? (form.getAttribute('data-action') || '') : '';
            var loadingText = 'Please wait...';
            if (action.indexOf('login') !== -1) loadingText = 'Signing in...';
            else if (action.indexOf('register') !== -1) loadingText = 'Creating account...';
            else if (action.indexOf('forgot') !== -1 || action.indexOf('reset') !== -1) loadingText = 'Sending...';
            var label = document.createElement('span');
            label.textContent = loadingText;
            btn.appendChild(label);
        } else {
            btn.disabled = false;
            btn.style.opacity = '';
            btn.style.pointerEvents = '';
            while (btn.firstChild) {
                btn.removeChild(btn.firstChild);
            }
            btn.textContent = btn._originalText || 'Submit';
        }
    }

    /**
     * Handle form submission via fetch.
     * Intercepts all .member-form submissions.
     *
     * Expects data-action attribute on form for the URL, or falls back
     * to the form's action attribute.
     *
     * Expects data-method for HTTP method (default: POST).
     *
     * Response handling:
     * - 401 -> redirect to login
     * - success + data.redirect -> safeRedirect
     * - success -> show toast
     * - error -> show toast and/or field errors
     */
    function handleFormSubmit(e) {
        e.preventDefault();

        var form = e.target;
        if (!form.classList.contains('member-form')) return;

        // Validate first
        if (!validateForm(form)) return;

        var url = form.getAttribute('data-action') || form.getAttribute('action');
        var method = (form.getAttribute('data-method') || form.getAttribute('method') || 'POST').toUpperCase();

        if (!url) {
            showToast('Form configuration error: no action URL.', 'error');
            return;
        }

        // Gather form data as JSON
        var data = {};
        var inputs = form.querySelectorAll('input, textarea, select');
        for (var i = 0; i < inputs.length; i++) {
            var input = inputs[i];
            var name = input.getAttribute('name');
            if (!name) continue;
            if (input.type === 'checkbox') {
                data[name] = input.checked;
            } else if (input.type === 'file') {
                // Files are handled by avatar upload, skip in JSON
                continue;
            } else {
                data[name] = input.value;
            }
        }

        // Include CSRF token
        data.csrf_token = getCsrfToken();

        var submitBtn = form.querySelector('.member-btn[type="submit"], .member-btn:not([type])');
        setButtonLoading(submitBtn, true);

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': getCsrfToken()
            },
            credentials: 'include',
            body: JSON.stringify(data)
        })
        .then(function (response) {
            // Handle 401 — redirect to login
            if (response.status === 401) {
                var returnUrl = encodeURIComponent(window.location.pathname + window.location.search);
                window.location.href = LOGIN_URL + '?return=' + returnUrl;
                return null;
            }

            return response.json().then(function (json) {
                return { status: response.status, data: json };
            });
        })
        .then(function (result) {
            if (!result) return; // 401 redirect happened

            setButtonLoading(submitBtn, false);

            if (result.data.success) {
                // Show success message
                var successMsg = result.data.message || 'Done!';
                showToast(successMsg, 'success');

                // Redirect if specified
                if (result.data.redirect) {
                    setTimeout(function () {
                        if (result.data.server_validated) {
                            // Server pre-validated this URL (SSO allowlist or same-origin check).
                            // May be cross-domain (e.g. hub SSO hop) — assign directly.
                            window.location.href = result.data.redirect;
                        } else {
                            safeRedirect(result.data.redirect);
                        }
                    }, 500);
                } else {
                    // Check for returnUrl parameter (login/register flow)
                    var formAction = url.toLowerCase();
                    if (formAction.indexOf('register') !== -1 && result.data.email_verification === 'pending') {
                        // Registration with email verification — redirect to login with pending message
                        var loginUrl = LOGIN_URL + '?registered=1';
                        if (result.data.masked_email) {
                            loginUrl += '&email=' + encodeURIComponent(result.data.masked_email);
                        }
                        setTimeout(function () {
                            safeRedirect(loginUrl);
                        }, 500);
                    } else if (formAction.indexOf('login') !== -1 || formAction.indexOf('register') !== -1) {
                        var returnUrl = getParam('return') || getParam('returnUrl');
                        setTimeout(function () {
                            safeRedirect(returnUrl || '/member/profile');
                        }, 500);
                    }
                }

                // Emit custom event for pages to react (includes success animation callback)
                form.dispatchEvent(new CustomEvent('member:success', {
                    bubbles: true,
                    detail: result.data
                }));

                // Trigger onSuccess callback with animation function if defined
                if (typeof window.MemberKit !== 'undefined' && window.MemberKit.onSuccess) {
                    window.MemberKit.onSuccess(result.data, function() {
                        showToast(successMsg, 'success');
                    });
                }
            } else {
                // Show error
                var errorMsg = result.data.error || result.data.message || 'Something went wrong.';
                var formAction = (url || '').toLowerCase();

                // Context-aware inline error for login failures
                if (formAction.indexOf('login') !== -1 && result.status === 401) {
                    showContextError(form, errorMsg, [
                        { text: 'Create an account', href: '/member/register' },
                        { text: 'Reset password', href: '/member/forgot-password' }
                    ]);
                } else if (result.data.unverified) {
                    showContextError(form, errorMsg, [
                        { text: 'Resend verification email', href: '/member/resend-verification' }
                    ]);
                } else {
                    showToast(errorMsg, 'error');
                }

                // Field-specific errors
                if (result.data.errors && typeof result.data.errors === 'object') {
                    var fieldErrors = result.data.errors;
                    for (var fieldName in fieldErrors) {
                        if (!fieldErrors.hasOwnProperty(fieldName)) continue;
                        var fieldEl = form.querySelector('.member-field [name="' + fieldName + '"]');
                        if (fieldEl) {
                            var wrapper = fieldEl.closest('.member-field');
                            if (wrapper) {
                                showFieldError(wrapper, fieldErrors[fieldName]);
                            }
                        }
                    }
                }

                // Emit custom event
                form.dispatchEvent(new CustomEvent('member:error', {
                    bubbles: true,
                    detail: result.data
                }));
            }
        })
        .catch(function (err) {
            setButtonLoading(submitBtn, false);
            showToast('Network error. Please check your connection and try again.', 'error');
            if (typeof console !== 'undefined') {
                console.error('MemberKit form error:', err);
            }
        });
    }

    // ── Password Toggle ───────────────────────────────────────────────────

    /**
     * Initialize password visibility toggles.
     * Finds all .member-password-wrap elements and attaches toggle behavior.
     */
    function initPasswordToggles() {
        var wraps = document.querySelectorAll('.member-password-wrap');

        for (var i = 0; i < wraps.length; i++) {
            (function (wrap) {
                // Skip if already initialized
                if (wrap.querySelector('.member-password-toggle')) return;

                var input = wrap.querySelector('.member-input');
                if (!input || input.type !== 'password') return;

                var toggle = document.createElement('button');
                toggle.className = 'member-password-toggle';
                toggle.setAttribute('type', 'button');
                toggle.setAttribute('aria-label', 'Toggle password visibility');
                toggle.textContent = 'Show';

                toggle.addEventListener('click', function () {
                    if (input.type === 'password') {
                        input.type = 'text';
                        toggle.textContent = 'Hide';
                    } else {
                        input.type = 'password';
                        toggle.textContent = 'Show';
                    }
                });

                wrap.appendChild(toggle);
            })(wraps[i]);
        }
    }

    // ── Tab Switching ─────────────────────────────────────────────────────

    /**
     * Initialize tab navigation.
     * Clicking a .member-tab shows the corresponding .member-tab-content
     * matched by data-tab attribute.
     */
    function initTabs() {
        var tabGroups = document.querySelectorAll('.member-tabs');

        for (var g = 0; g < tabGroups.length; g++) {
            (function (tabGroup) {
                var tabs = tabGroup.querySelectorAll('.member-tab');

                for (var t = 0; t < tabs.length; t++) {
                    tabs[t].addEventListener('click', function () {
                        var targetId = this.getAttribute('data-tab');
                        if (!targetId) return;

                        // Find the parent container that holds both tabs and content
                        var container = tabGroup.parentElement;
                        if (!container) return;

                        // Deactivate all tabs in this group
                        var allTabs = tabGroup.querySelectorAll('.member-tab');
                        for (var i = 0; i < allTabs.length; i++) {
                            allTabs[i].classList.remove('active');
                        }

                        // Activate clicked tab
                        this.classList.add('active');

                        // Hide all tab content in this container
                        var contents = container.querySelectorAll('.member-tab-content');
                        for (var j = 0; j < contents.length; j++) {
                            contents[j].classList.remove('active');
                        }

                        // Show target content
                        var target = container.querySelector('#' + targetId);
                        if (target) {
                            target.classList.add('active');
                        }
                    });
                }
            })(tabGroups[g]);
        }
    }

    // ── Avatar Upload ─────────────────────────────────────────────────────

    /**
     * Initialize avatar upload areas.
     * Supports click-to-browse and drag-and-drop.
     * Previews the image before uploading.
     */
    function initAvatarUpload() {
        var uploadAreas = document.querySelectorAll('.member-avatar-upload');

        for (var i = 0; i < uploadAreas.length; i++) {
            (function (area) {
                // Skip if already initialized
                if (area._memberInit) return;
                area._memberInit = true;

                var fileInput = area.querySelector('input[type="file"]');

                // Create hidden file input if it does not exist
                if (!fileInput) {
                    fileInput = document.createElement('input');
                    fileInput.type = 'file';
                    fileInput.accept = 'image/jpeg,image/png,image/webp';
                    fileInput.setAttribute('name', 'avatar');
                    fileInput.setAttribute('aria-label', 'Choose avatar image');
                    area.appendChild(fileInput);
                }

                // Click to browse
                area.addEventListener('click', function (e) {
                    if (e.target === fileInput) return;
                    fileInput.click();
                });

                // Keyboard support
                area.setAttribute('tabindex', '0');
                area.setAttribute('role', 'button');
                area.setAttribute('aria-label', 'Upload avatar image');
                area.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        fileInput.click();
                    }
                });

                // Drag and drop
                area.addEventListener('dragover', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    area.classList.add('member-avatar-upload--dragover');
                });

                area.addEventListener('dragleave', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    area.classList.remove('member-avatar-upload--dragover');
                });

                area.addEventListener('drop', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    area.classList.remove('member-avatar-upload--dragover');

                    var files = e.dataTransfer.files;
                    if (files.length > 0) {
                        handleAvatarFile(area, files[0]);
                    }
                });

                // File input change
                fileInput.addEventListener('change', function () {
                    if (fileInput.files && fileInput.files.length > 0) {
                        handleAvatarFile(area, fileInput.files[0]);
                    }
                });
            })(uploadAreas[i]);
        }
    }

    /**
     * Handle an avatar file: preview and upload.
     * @param {HTMLElement} area - The .member-avatar-upload element.
     * @param {File} file
     */
    function handleAvatarFile(area, file) {
        // Validate type
        var allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (allowed.indexOf(file.type) === -1) {
            showToast('Please select a JPG, PNG, or WebP image.', 'error');
            return;
        }

        // Validate size (2 MB)
        if (file.size > 2 * 1024 * 1024) {
            showToast('Image must be under 2 MB.', 'error');
            return;
        }

        // Preview
        var reader = new FileReader();
        reader.onload = function (e) {
            // Remove existing preview
            var existing = area.querySelector('.member-avatar-preview');
            if (existing && existing.parentNode) {
                existing.parentNode.removeChild(existing);
            }

            var img = document.createElement('img');
            img.className = 'member-avatar-preview';
            img.alt = 'Avatar preview';
            img.src = e.target.result;

            // Insert at beginning of area
            if (area.firstChild) {
                area.insertBefore(img, area.firstChild);
            } else {
                area.appendChild(img);
            }
        };
        reader.readAsDataURL(file);

        // Upload
        uploadAvatar(file);
    }

    /**
     * Upload the avatar file via FormData to the API.
     * @param {File} file
     */
    function uploadAvatar(file) {
        var formData = new FormData();
        formData.append('avatar', file);
        formData.append('csrf_token', getCsrfToken());

        showToast('Uploading avatar...', 'info', 2000);

        fetch('/api/member/profile.php', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': getCsrfToken()
            },
            credentials: 'include',
            body: formData
        })
        .then(function (response) {
            if (response.status === 401) {
                var returnUrl = encodeURIComponent(window.location.pathname);
                window.location.href = LOGIN_URL + '?return=' + returnUrl;
                return null;
            }
            return response.json();
        })
        .then(function (data) {
            if (!data) return;

            if (data.success) {
                showToast('Avatar updated!', 'success');

                // Update avatar images on the page
                if (data.avatar_url) {
                    var avatars = document.querySelectorAll('.member-avatar img');
                    for (var i = 0; i < avatars.length; i++) {
                        avatars[i].src = data.avatar_url;
                    }
                }
            } else {
                showToast(data.error || 'Failed to upload avatar.', 'error');
            }
        })
        .catch(function (err) {
            showToast('Upload failed. Please try again.', 'error');
            if (typeof console !== 'undefined') {
                console.error('MemberKit avatar upload error:', err);
            }
        });
    }

    // ── SSO Button ────────────────────────────────────────────────────────

    /**
     * Initialize SSO button click handler.
     * Redirects to /api/member/sso.php with optional return URL.
     */
    function initSSOButton() {
        var ssoButtons = document.querySelectorAll('.member-sso-btn');

        for (var i = 0; i < ssoButtons.length; i++) {
            ssoButtons[i].addEventListener('click', function (e) {
                e.preventDefault();
                var returnUrl = getParam('return') || getParam('returnUrl') || '/member/profile';
                var ssoUrl = '/api/member/sso.php?return=' + encodeURIComponent(returnUrl);
                window.location.href = ssoUrl;
            });
        }
    }

    // ── Wallet Buttons ─────────────────────────────────────────────────────

    /**
     * Initialize wallet connection button handlers.
     * Dispatches 'memberkit:wallet-connect' event and redirects to wallet API.
     * Sites can listen to the event for custom handling.
     */
    function initWalletButtons() {
        var walletButtons = document.querySelectorAll('.member-wallet-btn');

        for (var i = 0; i < walletButtons.length; i++) {
            walletButtons[i].addEventListener('click', function (e) {
                e.preventDefault();
                var wallet = this.getAttribute('data-wallet');
                if (!wallet) return;

                // Dispatch custom event for site-level integrations to handle
                document.dispatchEvent(new CustomEvent('memberkit:wallet-connect', {
                    detail: { wallet: wallet },
                    bubbles: true
                }));

                // Default behavior: redirect to wallet API endpoint
                var returnUrl = getParam('return') || getParam('returnUrl') || '/member/profile';
                var walletUrl = '/api/member/wallet.php?provider=' + encodeURIComponent(wallet) + '&return=' + encodeURIComponent(returnUrl);
                window.location.href = walletUrl;
            });
        }
    }

    // ── Clear Validation on Input ─────────────────────────────────────────

    /**
     * Clear field errors when the user starts typing.
     */
    function initLiveValidation() {
        document.addEventListener('input', function (e) {
            var input = e.target;
            if (!input.classList.contains('member-input') &&
                !input.classList.contains('member-textarea')) {
                return;
            }
            var field = input.closest('.member-field');
            if (field) {
                clearFieldError(field);
            }
        });
    }

    // ── Password Strength Meter ──────────────────────────────────────────

    /**
     * Initialize password strength indicator on register forms.
     * Adds a strength meter bar below the #reg-password field.
     */
    function initPasswordStrength() {
        var regPassword = document.getElementById('reg-password');
        if (!regPassword) return;

        var field = regPassword.closest('.member-field');
        if (!field || field.querySelector('.member-strength-bar')) return;

        var container = document.createElement('div');
        container.className = 'member-strength-container';

        var bar = document.createElement('div');
        bar.className = 'member-strength-bar';
        var fill = document.createElement('div');
        fill.className = 'member-strength-fill';
        bar.appendChild(fill);
        container.appendChild(bar);

        var label = document.createElement('span');
        label.className = 'member-strength-label';
        container.appendChild(label);

        field.appendChild(container);

        regPassword.addEventListener('input', function () {
            var val = regPassword.value;
            var score = 0;
            if (val.length >= 8) score++;
            if (val.length >= 12) score++;
            if (/[a-z]/.test(val) && /[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^a-zA-Z0-9]/.test(val)) score++;

            var levels = [
                { cls: '', text: '' },
                { cls: 'strength-weak', text: 'Weak' },
                { cls: 'strength-weak', text: 'Weak' },
                { cls: 'strength-medium', text: 'Medium' },
                { cls: 'strength-strong', text: 'Strong' },
                { cls: 'strength-strong', text: 'Very strong' }
            ];
            var level = levels[Math.min(score, 5)];

            fill.className = 'member-strength-fill ' + level.cls;
            fill.style.width = val.length === 0 ? '0%' : (score * 20) + '%';
            label.textContent = val.length === 0 ? '' : level.text;
            label.className = 'member-strength-label ' + level.cls;

            // Update ARIA label for accessibility
            var strengthLevel = level.text.toLowerCase();
            var ariaLabel = strengthLevel ? 'Password strength: ' + strengthLevel : '';
            if (ariaLabel) {
                label.setAttribute('aria-label', ariaLabel);
            }
        });
    }

    // ── Phase 4.4: Dark Mode Detection ────────────────────────────────────

    /**
     * Detect system theme preference and apply to document.
     * Checks localStorage for saved preference, falls back to system preference.
     * Updates document.documentElement with 'data-theme' attribute.
     * @returns {string} The applied theme: 'light' or 'dark'
     */
    function detectSystemTheme() {
        // Check for saved preference first
        var savedTheme = localStorage.getItem('member-kit-theme');
        if (savedTheme && (savedTheme === 'light' || savedTheme === 'dark')) {
            document.documentElement.setAttribute('data-theme', savedTheme);
            return savedTheme;
        }

        // Fall back to system preference
        var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        var theme = prefersDark ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', theme);
        return theme;
    }

    /**
     * Toggle between light and dark theme.
     * Saves preference to localStorage and applies to document.
     * Dispatches custom event 'memberkit:themechange' for listeners.
     */
    function toggleTheme() {
        var currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
        var newTheme = currentTheme === 'light' ? 'dark' : 'light';

        localStorage.setItem('member-kit-theme', newTheme);
        document.documentElement.setAttribute('data-theme', newTheme);

        // Dispatch custom event
        document.dispatchEvent(new CustomEvent('memberkit:themechange', {
            detail: { theme: newTheme },
            bubbles: true
        }));

        return newTheme;
    }

    /**
     * Get current theme.
     * @returns {string} The current theme: 'light' or 'dark'
     */
    function getCurrentTheme() {
        return document.documentElement.getAttribute('data-theme') || 'light';
    }

    // ── Phase 1: Animations & Accessibility ──────────────────────────────

    /**
     * Initialize stagger animation delays for grouped form elements.
     * Sets CSS custom property --stagger-delay for each .member-group.
     */
    function initGroupStaggering() {
        var groups = document.querySelectorAll('.member-group');
        groups.forEach(function (group, index) {
            group.style.setProperty('--stagger-delay', (index * 100) + 'ms');
        });
    }

    /**
     * Initialize wallet button hover animations.
     * Scales buttons on mouseenter/mouseleave.
     */
    function initWalletButtonAnimations() {
        var walletBtns = document.querySelectorAll('.member-wallet-btn');
        walletBtns.forEach(function (btn) {
            btn.addEventListener('mouseenter', function () {
                this.style.transform = 'scale(1.02)';
            });
            btn.addEventListener('mouseleave', function () {
                this.style.transform = 'scale(1)';
            });
        });
    }

    /**
     * Announce available authentication methods via ARIA live region.
     * Informs assistive technologies of available auth options.
     */
    function announceAuthMethods() {
        var hasSocial = document.querySelector('[data-group="social"]');
        var hasWallets = document.querySelector('[data-group="wallets"]');
        var statusRegion = document.getElementById('auth-status');

        var msg = 'Authentication methods available: Email login';
        if (hasSocial) msg += ', social connections';
        if (hasWallets) msg += ', wallet connection';

        if (statusRegion) {
            statusRegion.textContent = msg;
        }
    }

    /**
     * Initialize helper tooltip click handlers.
     * Converts title attributes to info toasts on click.
     */
    function initHelperTooltips() {
        var helpers = document.querySelectorAll('.member-helper-icon');
        helpers.forEach(function (helper) {
            helper.addEventListener('click', function (e) {
                e.preventDefault();
                var tooltip = this.getAttribute('title') || 'Information';
                showToast(tooltip, 'info', 5000);
            });
        });
    }

    // ── Phase 2: Session & Device Management ──────────────────────────────

    /**
     * Generate a device fingerprint based on browser/device properties.
     * Hash includes UA, language, CPU cores, device memory, timezone, screen size.
     * @returns {string} Hex hash of the fingerprint.
     */
    function generateDeviceFingerprint() {
        var fingerprint = [
            navigator.userAgent,
            navigator.language,
            navigator.hardwareConcurrency || 'unknown',
            navigator.deviceMemory || 'unknown',
            new Date().getTimezoneOffset(),
            window.screen.width + 'x' + window.screen.height
        ].join('|');

        var hash = 0;
        for (var i = 0; i < fingerprint.length; i++) {
            var char = fingerprint.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convert to 32-bit integer
        }
        return Math.abs(hash).toString(16);
    }

    /**
     * Get or create a persistent device ID stored in localStorage.
     * @returns {string} Device ID (format: 'dev_<random>').
     */
    function getOrCreateDeviceId() {
        var stored = localStorage.getItem('device_id');
        if (stored) return stored;
        var deviceId = 'dev_' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('device_id', deviceId);
        return deviceId;
    }

    /**
     * Initialize session timeout warning and extension handler.
     * Reads session lifetime from data-session-lifetime attribute on body.
     * Shows warning 5 minutes before expiry with countdown timer.
     */
    function initSessionTimeoutWarning() {
        var sessionLifetimeEl = document.body.getAttribute('data-session-lifetime');
        if (!sessionLifetimeEl) return;

        var sessionLifetime = parseInt(sessionLifetimeEl, 10);
        if (isNaN(sessionLifetime) || sessionLifetime <= 0) return;

        var warningMinutes = 5;
        var warningTime = (sessionLifetime - (warningMinutes * 60)) * 1000;

        setTimeout(function () {
            var warningEl = document.getElementById('session-timeout-warning');
            if (warningEl) {
                warningEl.style.display = 'block';
                startSessionCountdown(warningMinutes * 60);
            }
        }, warningTime);

        // Extend session button handler
        var extendBtn = document.getElementById('extend-session');
        if (extendBtn) {
            extendBtn.addEventListener('click', function (e) {
                e.preventDefault();
                fetch('/api/member/session-extend.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'X-CSRF-Token': getCsrfToken() }
                })
                    .then(function (r) { return r.json(); })
                    .then(function (d) {
                        if (d.success) {
                            location.reload();
                        }
                    })
                    .catch(function (err) {
                        if (typeof console !== 'undefined') {
                            console.error('Session extend error:', err);
                        }
                    });
            });
        }
    }

    /**
     * Start a countdown timer for session expiry.
     * Updates #countdown element with MM:SS format every second.
     * Redirects to login when time expires.
     * @param {number} seconds - Countdown duration in seconds.
     */
    function startSessionCountdown(seconds) {
        var countdownEl = document.getElementById('countdown');
        if (!countdownEl) return;

        var interval = setInterval(function () {
            seconds--;
            var mins = Math.floor(seconds / 60);
            var secs = seconds % 60;
            countdownEl.textContent = mins + ':' + (secs < 10 ? '0' : '') + secs;
            if (seconds <= 0) {
                clearInterval(interval);
                safeRedirect(LOGIN_URL + '?expired=1');
            }
        }, 1000);
    }

    // ── Phase 3: Analytics & Event Tracking ────────────────────────────────

    /**
     * Track an event for analytics and A/B testing.
     * Sends to Google Analytics (if gtag available) and dispatches custom event.
     * @param {string} eventName - Event name (e.g., 'login_attempt', 'register_complete').
     * @param {object} [data] - Optional event data/properties.
     */
    function trackEvent(eventName, data) {
        // Send to Google Analytics
        if (window.gtag) {
            window.gtag('event', eventName, data || {});
        }

        // Dispatch custom event for site-level tracking
        document.dispatchEvent(new CustomEvent('analytics:event', {
            detail: { event: eventName, data: data },
            bubbles: true
        }));
    }

    // ── Real-Time Email Validation ──────────────────────────────────────────

    function initEmailIndicator() {
        var emailInputs = document.querySelectorAll('.member-input[type="email"]');
        for (var i = 0; i < emailInputs.length; i++) {
            (function (input) {
                var indicator = input.parentElement.querySelector('.member-input-indicator');
                if (!indicator) return;
                if (input._emailIndicatorInit) return;
                input._emailIndicatorInit = true;

                var svgNS = 'http://www.w3.org/2000/svg';
                var checkSvg = document.createElementNS(svgNS, 'svg');
                checkSvg.setAttribute('width', '18');
                checkSvg.setAttribute('height', '18');
                checkSvg.setAttribute('viewBox', '0 0 24 24');
                checkSvg.setAttribute('fill', 'none');
                checkSvg.setAttribute('stroke', 'currentColor');
                checkSvg.setAttribute('stroke-width', '3');
                checkSvg.setAttribute('stroke-linecap', 'round');
                checkSvg.setAttribute('stroke-linejoin', 'round');
                var checkPath = document.createElementNS(svgNS, 'polyline');
                checkPath.setAttribute('points', '20 6 9 17 4 12');
                checkSvg.appendChild(checkPath);

                var xSvg = document.createElementNS(svgNS, 'svg');
                xSvg.setAttribute('width', '16');
                xSvg.setAttribute('height', '16');
                xSvg.setAttribute('viewBox', '0 0 24 24');
                xSvg.setAttribute('fill', 'none');
                xSvg.setAttribute('stroke', 'currentColor');
                xSvg.setAttribute('stroke-width', '3');
                xSvg.setAttribute('stroke-linecap', 'round');
                var xLine1 = document.createElementNS(svgNS, 'line');
                xLine1.setAttribute('x1', '18'); xLine1.setAttribute('y1', '6');
                xLine1.setAttribute('x2', '6');  xLine1.setAttribute('y2', '18');
                var xLine2 = document.createElementNS(svgNS, 'line');
                xLine2.setAttribute('x1', '6');  xLine2.setAttribute('y1', '6');
                xLine2.setAttribute('x2', '18'); xLine2.setAttribute('y2', '18');
                xSvg.appendChild(xLine1);
                xSvg.appendChild(xLine2);

                var debounceTimer = null;

                input.addEventListener('input', function () {
                    clearTimeout(debounceTimer);
                    var val = input.value.trim();

                    if (!val) {
                        indicator.classList.remove('is-visible', 'member-input-indicator--valid', 'member-input-indicator--invalid');
                        while (indicator.firstChild) indicator.removeChild(indicator.firstChild);
                        return;
                    }

                    debounceTimer = setTimeout(function () {
                        var err = validators.email(val);
                        while (indicator.firstChild) indicator.removeChild(indicator.firstChild);
                        indicator.classList.remove('member-input-indicator--valid', 'member-input-indicator--invalid');

                        if (!err) {
                            indicator.appendChild(checkSvg);
                            indicator.classList.add('is-visible', 'member-input-indicator--valid');
                        } else {
                            indicator.appendChild(xSvg);
                            indicator.classList.add('is-visible', 'member-input-indicator--invalid');
                        }
                    }, 300);
                });
            })(emailInputs[i]);
        }
    }

    // ── Initialize ────────────────────────────────────────────────────────

    /**
     * Main initialization. Call on DOMContentLoaded or manually.
     */
    function init() {
        // Phase 4.4: Dark mode detection (must run early, before DOM paint if possible)
        detectSystemTheme();

        // Form submission interception
        document.addEventListener('submit', function (e) {
            if (e.target.classList.contains('member-form')) {
                handleFormSubmit(e);
            }
        });

        // Password toggles
        initPasswordToggles();

        // Tabs
        initTabs();

        // Avatar upload
        initAvatarUpload();

        // SSO buttons
        initSSOButton();

        // Wallet buttons
        initWalletButtons();

        // Live validation clearing
        initLiveValidation();

        // Real-time email validation indicator
        initEmailIndicator();

        // Password strength meter
        initPasswordStrength();

        // Phase 1: Animations & Accessibility
        initGroupStaggering();
        initWalletButtonAnimations();
        announceAuthMethods();
        initHelperTooltips();

        // Phase 2: Session & Device Management
        initSessionTimeoutWarning();
        // Device ID created on-demand via getOrCreateDeviceId()

        // Phase 3: Analytics & Event Tracking
        // Events tracked via trackEvent() calls in form submission handler
    }

    // ── Public API ────────────────────────────────────────────────────────

    window.MemberKit = {
        /**
         * Initialize all member kit functionality.
         * Called automatically on DOMContentLoaded, but can be called
         * manually after dynamic content insertion.
         */
        init: init,

        /**
         * Show a toast notification.
         * @param {string} msg  - Message text.
         * @param {string} type - 'success' | 'error' | 'info' | 'warning'
         * @param {number} [duration=4000] - Auto-dismiss ms (0 = manual only).
         */
        toast: function (msg, type, duration) {
            return showToast(msg, type, duration);
        },

        /**
         * Safe same-origin redirect.
         * @param {string} url - URL to redirect to.
         */
        redirect: function (url) {
            safeRedirect(url);
        },

        /**
         * Validate a form programmatically.
         * @param {HTMLFormElement} form
         * @returns {boolean}
         */
        validate: function (form) {
            return validateForm(form);
        },

        /**
         * Show a field-level error.
         * @param {HTMLElement} field - The .member-field wrapper.
         * @param {string} message
         */
        showFieldError: function (field, message) {
            showFieldError(field, message);
        },

        /**
         * Clear a field-level error.
         * @param {HTMLElement} field - The .member-field wrapper.
         */
        clearFieldError: function (field) {
            clearFieldError(field);
        },

        /**
         * Animate a field with error shake.
         * @param {HTMLElement} field - The .member-field wrapper.
         * @param {string} [message] - Optional error message to display.
         */
        animateError: function (field, message) {
            animateError(field, message);
        },

        /**
         * Handle wallet connection (can be overridden by sites).
         * Sites can listen to 'memberkit:wallet-connect' event for custom behavior.
         */
        walletConnect: function (wallet) {
            var event = new CustomEvent('memberkit:wallet-connect', {
                detail: { wallet: wallet },
                bubbles: true
            });
            document.dispatchEvent(event);
        },

        /**
         * Re-initialize dynamic elements (after DOM changes).
         * Useful after AJAX-loaded content.
         */
        refresh: function () {
            initPasswordToggles();
            initTabs();
            initAvatarUpload();
            initSSOButton();
            initWalletButtons();
            initPasswordStrength();
        },

        // Phase 2: Device & Session Management
        /**
         * Get or create a persistent device ID.
         * @returns {string} Device ID (format: 'dev_<random>').
         */
        getDeviceId: function () {
            return getOrCreateDeviceId();
        },

        /**
         * Generate a device fingerprint hash.
         * @returns {string} Hex hash based on browser/device properties.
         */
        getDeviceFingerprint: function () {
            return generateDeviceFingerprint();
        },

        // Phase 3: Tracking & Analytics
        /**
         * Track an event for analytics and A/B testing.
         * @param {string} name - Event name (e.g., 'login_attempt').
         * @param {object} [data] - Optional event properties.
         */
        trackEvent: function (name, data) {
            trackEvent(name, data);
        },

        // Phase 2: Session Extension
        /**
         * Trigger session extension (clicks #extend-session button).
         * Requires #extend-session button in DOM.
         */
        extendSession: function () {
            var extendBtn = document.getElementById('extend-session');
            if (extendBtn) {
                extendBtn.click();
            }
        },

        // Phase 4.3: Success/Error Animations
        /**
         * Register a success callback with animation trigger (onSuccess with animate).
         * Useful for custom success animations on forms.
         * @param {function} callback - Function called on success with (data, animateFunction).
         */
        onSuccess: null,

        // Phase 4.4: Dark Mode Control
        /**
         * Toggle between light and dark theme.
         * @returns {string} The new theme: 'light' or 'dark'
         */
        toggleTheme: function () {
            return toggleTheme();
        },

        /**
         * Get the current theme.
         * @returns {string} The current theme: 'light' or 'dark'
         */
        getTheme: function () {
            return getCurrentTheme();
        }
    };

    // ── Auto-init on DOMContentLoaded ─────────────────────────────────────

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();

/* Phase 5 Functions */
(function() {
    'use strict';
    window.MemberKit = window.MemberKit || {};
    
    function renameDevice(deviceId, name) {
        var csrf = document.querySelector('[name="csrf_token"]')?.value || '';
        return fetch('/api/member/rename-device.php', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ csrf_token: csrf, device_id: deviceId, device_name: name })
        }).then(function(r) { return r.json(); });
    }
    
    function revokeDevice(deviceId) {
        var csrf = document.querySelector('[name="csrf_token"]')?.value || '';
        return fetch('/api/member/revoke-device.php', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ csrf_token: csrf, device_id: deviceId })
        }).then(function(r) { return r.json(); });
    }
    
    function loadLoginHistory(container, opts) {
        if (!container) return Promise.reject(new Error('No container'));
        opts = opts || {};
        var url = '/api/member/login-activity.php?limit=' + (opts.limit || 20) + '&offset=' + (opts.offset || 0) + '&filter=' + encodeURIComponent(opts.filter || 'all');
        return fetch(url, { credentials: 'include' }).then(function(r) { return r.json(); });
    }
    
    function showRateLimitCountdown(seconds, element) {
        var el = element || document.getElementById('rate-limit-message');
        if (!el) return;
        el.classList.add('is-visible');
        var remaining = seconds;
        function format(s) { var m = Math.floor(s/60); return m + ':' + (s%60 < 10 ? '0' : '') + (s%60); }
        function render() {
            var cd = el.querySelector('.member-rate-limit-countdown');
            if (cd) cd.textContent = format(remaining);
        }
        render();
        var timer = setInterval(function() {
            if (--remaining <= 0) {
                clearInterval(timer);
                el.classList.remove('is-visible');
                return;
            }
            render();
        }, 1000);
    }
    
    function showLoadingState(element, type) {
        if (!element) return;
        element.setAttribute('aria-busy', 'true');
        if (!element._skeletonChildren) {
            element._skeletonChildren = [];
            while (element.firstChild) element._skeletonChildren.push(element.removeChild(element.firstChild));
        }
        var skel = document.createElement('div');
        skel.setAttribute('aria-hidden', 'true');
        skel.setAttribute('data-member-skeleton', 'true');
        skel.className = type === 'avatar' ? 'member-skeleton member-skeleton-avatar' : 'member-skeleton member-skeleton-text';
        element.appendChild(skel);
    }
    
    function hideLoadingState(element) {
        if (!element) return;
        element.setAttribute('aria-busy', 'false');
        var skels = element.querySelectorAll('[data-member-skeleton="true"]');
        for (var i = 0; i < skels.length; i++) if (skels[i].parentNode) skels[i].parentNode.removeChild(skels[i]);
        if (element._skeletonChildren) {
            for (var j = 0; j < element._skeletonChildren.length; j++) element.appendChild(element._skeletonChildren[j]);
            element._skeletonChildren = null;
        }
    }
    
    window.MemberKit.renameDevice = renameDevice;
    window.MemberKit.revokeDevice = revokeDevice;
    window.MemberKit.loadLoginHistory = loadLoginHistory;
    window.MemberKit.showRateLimitCountdown = showRateLimitCountdown;
    window.MemberKit.showLoadingState = showLoadingState;
    window.MemberKit.hideLoadingState = hideLoadingState;
})();

/* Phase 6.4: Keyboard Shortcuts */
(function () {
    'use strict';
    window.MemberKit = window.MemberKit || {};

    function isTypingContext() {
        var el = document.activeElement;
        if (!el) return false;
        var tag = el.tagName.toLowerCase();
        if (tag === 'input' || tag === 'textarea' || tag === 'select') return true;
        return el.isContentEditable;
    }

    function ctrlEnter() {
        var form = null;
        var active = document.activeElement;
        if (active && active.closest) {
            form = active.closest('form.member-form');
        }
        if (!form) {
            form = document.querySelector('form.member-form');
        }
        if (!form) return;
        var submitEvent = new Event('submit', { bubbles: true, cancelable: true });
        form.dispatchEvent(submitEvent);
    }

    var helpOverlayVisible = false;

    function showHelpOverlay() {
        var overlay = document.getElementById('member-keyboard-help-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'member-keyboard-help-overlay';
            overlay.className = 'member-modal-overlay';
            overlay.setAttribute('role', 'dialog');
            overlay.setAttribute('aria-modal', 'true');
            overlay.setAttribute('aria-labelledby', 'keyboard-help-title');
            overlay.setAttribute('aria-describedby', 'keyboard-help-desc');
            overlay.textContent = '[Keyboard Help Modal]';
            document.body.appendChild(overlay);
        }

        if (helpOverlayVisible) {
            overlay.style.display = 'none';
            helpOverlayVisible = false;
            return;
        }

        overlay.style.display = 'flex';
        helpOverlayVisible = true;
        overlay.focus();
    }

    function handleKeyboardShortcut(event) {
        if (isTypingContext()) return;
        var key = event.key;
        var ctrl = event.ctrlKey || event.metaKey;

        if (ctrl && (key === 'Enter' || key === 'NumpadEnter')) {
            event.preventDefault();
            ctrlEnter();
            return;
        }

        if (key === '?' && !ctrl && !event.altKey) {
            event.preventDefault();
            showHelpOverlay();
        }
    }

    function initKeyboardShortcuts() {
        if (document._mkKbInit) return;
        document._mkKbInit = true;
        document.addEventListener('keydown', handleKeyboardShortcut);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initKeyboardShortcuts);
    } else {
        initKeyboardShortcuts();
    }

    window.MemberKit.showHelpOverlay = showHelpOverlay;
    window.MemberKit.handleKeyboardShortcut = handleKeyboardShortcut;

})();

// ── Account Page: Collapsible Sections ──────────────────────────────────
(function () {
    'use strict';

    var STORAGE_KEY = 'member_account_sections';

    function getSavedState() {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            return raw ? JSON.parse(raw) : {};
        } catch (e) {
            return {};
        }
    }

    function saveState(state) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
        } catch (e) { /* quota exceeded — ignore */ }
    }

    function initAccountSections() {
        var sections = document.querySelectorAll('.member-account-section');
        if (!sections.length) return;

        var saved = getSavedState();

        sections.forEach(function (section) {
            var key = section.getAttribute('data-section');
            var title = section.querySelector('.member-account-section-title');
            if (!title || !key) return;

            // Restore saved state (respect default collapsed for activity)
            if (saved[key] === 'collapsed') {
                section.classList.add('collapsed');
                title.setAttribute('aria-expanded', 'false');
            } else if (saved[key] === 'expanded') {
                section.classList.remove('collapsed');
                title.setAttribute('aria-expanded', 'true');
            }

            title.addEventListener('click', function () {
                var isCollapsed = section.classList.toggle('collapsed');
                title.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
                var state = getSavedState();
                state[key] = isCollapsed ? 'collapsed' : 'expanded';
                saveState(state);
            });

            title.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    title.click();
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAccountSections);
    } else {
        initAccountSections();
    }
})();
