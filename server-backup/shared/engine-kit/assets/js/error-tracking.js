/**
 * Error Tracking — Client-Side (1vsM Network)
 *
 * Lazy-loads Sentry Browser SDK via CDN if DSN is configured.
 * Falls back to console logging if Sentry is unavailable.
 *
 * Usage in layout template:
 *   <script>
 *     window.__ERROR_TRACKING = { dsn: '<?= e($_ENV["SENTRY_DSN_JS"] ?? "") ?>', siteKey: 'hiphop_world' };
 *   </script>
 *   <script defer src="/path/to/error-tracking.js"></script>
 */
(function() {
    'use strict';

    var config = window.__ERROR_TRACKING || {};
    var dsn = config.dsn || '';
    var siteKey = config.siteKey || 'unknown';
    var requestId = config.requestId || '';
    var sentryLoaded = false;

    // Global error capture function (available immediately)
    window.__captureError = function(error, context) {
        var message = error instanceof Error ? error.message : String(error);
        var stack = error instanceof Error ? error.stack : '';

        console.error('[' + siteKey + '] ' + message, context || '');

        if (sentryLoaded && window.Sentry) {
            if (error instanceof Error) {
                window.Sentry.captureException(error, { extra: context });
            } else {
                window.Sentry.captureMessage(message, { extra: context });
            }
        }
    };

    // Global unhandled error handler
    window.addEventListener('error', function(event) {
        window.__captureError(event.error || event.message, {
            filename: event.filename,
            lineno: event.lineno,
            colno: event.colno
        });
    });

    // Unhandled promise rejection handler
    window.addEventListener('unhandledrejection', function(event) {
        window.__captureError(event.reason || 'Unhandled promise rejection', {
            type: 'unhandledrejection'
        });
    });

    // Lazy-load Sentry if DSN is provided
    if (dsn) {
        var script = document.createElement('script');
        script.src = 'https://browser.sentry-cdn.com/8.0.0/bundle.min.js';
        script.crossOrigin = 'anonymous';
        script.defer = true;
        script.onload = function() {
            if (window.Sentry) {
                window.Sentry.init({
                    dsn: dsn,
                    environment: config.env || 'production',
                    tracesSampleRate: 0.1,
                    initialScope: {
                        tags: {
                            site_key: siteKey,
                            request_id: requestId
                        }
                    }
                });
                sentryLoaded = true;
            }
        };
        script.onerror = function() {
            // Sentry CDN failed — error tracking continues via console + DB
        };
        document.head.appendChild(script);
    }
})();
