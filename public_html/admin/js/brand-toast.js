/**
 * brand-toast — Shared toast notification system
 *
 * Usage:
 *   import { showToast } from '/shared/ui/components/brand-toast.js';
 *   showToast('Saved!', 'success');
 *   showToast('Failed to connect', 'error', 5000);
 *
 * Types: 'success' | 'error' | 'warning' | 'info'
 * Duration: milliseconds before auto-dismiss (default 3000, 0 = no auto-dismiss)
 */

const COLORS = {
  success: { bg: '#16a34a', border: '#22c55e', icon: '✓' },
  error:   { bg: '#dc2626', border: '#ef4444', icon: '✕' },
  warning: { bg: '#d97706', border: '#f59e0b', icon: '⚠' },
  info:    { bg: '#0891b2', border: '#06b6d4', icon: 'ℹ' },
};

let container = null;

function getContainer() {
  if (container && container.isConnected) return container;
  container = document.createElement('div');
  Object.assign(container.style, {
    position: 'fixed',
    bottom: '1.5rem',
    right: '1.5rem',
    zIndex: 'var(--z-toast, 200)',
    display: 'flex',
    flexDirection: 'column',
    gap: '0.5rem',
    maxWidth: 'min(380px, calc(100vw - 2rem))',
    pointerEvents: 'none',
  });
  container.setAttribute('aria-live', 'polite');
  container.setAttribute('aria-atomic', 'false');
  container.setAttribute('role', 'status');
  document.body.appendChild(container);
  return container;
}

/**
 * Show a toast notification.
 *
 * @param {string} message  The message to display
 * @param {'success'|'error'|'warning'|'info'} type  Visual style
 * @param {number} duration  Auto-dismiss after ms (0 = manual only, default 3000)
 */
export function showToast(message, type = 'success', duration = 3000) {
  const colors = COLORS[type] ?? COLORS.info;
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const toast = document.createElement('div');
  toast.setAttribute('role', 'alert');
  toast.setAttribute('aria-live', 'assertive');
  Object.assign(toast.style, {
    display: 'flex',
    alignItems: 'center',
    gap: '0.75rem',
    padding: '0.75rem 1rem',
    background: 'var(--surface-elevated, #1A1A24)',
    border: `1px solid ${colors.border}`,
    borderLeft: `4px solid ${colors.border}`,
    borderRadius: 'var(--radius-lg, 12px)',
    boxShadow: 'var(--shadow-card-lg, 0 8px 40px rgba(0,0,0,0.4))',
    color: 'var(--text-primary, #fff)',
    fontSize: 'var(--text-sm, 0.875rem)',
    fontFamily: 'var(--font-ui, sans-serif)',
    lineHeight: '1.4',
    maxWidth: '100%',
    pointerEvents: 'auto',
    transition: reducedMotion ? 'none' : `opacity var(--duration-normal, 400ms) var(--ease-default, ease), transform var(--duration-normal, 400ms) var(--ease-default, ease)`,
    opacity: reducedMotion ? '1' : '0',
    transform: reducedMotion ? 'none' : 'translateY(8px)',
  });

  // Icon
  const icon = document.createElement('span');
  Object.assign(icon.style, {
    flexShrink: '0',
    width: '1.25rem',
    height: '1.25rem',
    borderRadius: '50%',
    background: colors.bg,
    color: '#fff',
    fontSize: '0.7rem',
    fontWeight: '700',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
  });
  icon.textContent = colors.icon;
  icon.setAttribute('aria-hidden', 'true');

  // Message
  const text = document.createElement('span');
  text.style.flex = '1';
  text.textContent = message;

  // Close button
  const close = document.createElement('button');
  Object.assign(close.style, {
    flexShrink: '0',
    background: 'none',
    border: 'none',
    color: 'var(--text-muted, rgba(255,255,255,0.4))',
    cursor: 'pointer',
    padding: '0.125rem',
    fontSize: '1rem',
    lineHeight: '1',
    borderRadius: '4px',
  });
  close.textContent = '×';
  close.setAttribute('aria-label', 'Dismiss notification');
  close.addEventListener('click', () => dismiss());

  toast.appendChild(icon);
  toast.appendChild(text);
  toast.appendChild(close);
  getContainer().appendChild(toast);

  // Animate in
  if (!reducedMotion) {
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
      });
    });
  }

  let dismissTimer = null;

  function dismiss() {
    if (dismissTimer) clearTimeout(dismissTimer);
    if (reducedMotion) {
      toast.remove();
      return;
    }
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(-4px)';
    setTimeout(() => toast.remove(), 400);
  }

  if (duration > 0) {
    dismissTimer = setTimeout(dismiss, duration);
  }

  return { dismiss };
}
