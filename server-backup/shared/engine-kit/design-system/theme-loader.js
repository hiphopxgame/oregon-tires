/**
 * Theme Loader — Multi-Brand Design Token Architecture
 *
 * Runtime theme switching via data-theme attribute on <html>.
 * Persists to localStorage. Supports sub-modes (e.g., PDX data-pdx-mode).
 *
 * Usage:
 *   ThemeLoader.set('hiphop-world');
 *   ThemeLoader.set('pdx-business', { subMode: 'play' });
 *   ThemeLoader.get(); // → { theme: 'hiphop-world', subMode: null }
 */

const ThemeLoader = (() => {
  const STORAGE_KEY = '1vsm-theme';
  const STORAGE_SUBMODE_KEY = '1vsm-theme-submode';
  const THEME_ATTR = 'data-theme';
  const SUBMODE_ATTR = 'data-pdx-mode';

  const VALID_THEMES = ['hiphop-world', 'pdx-business', 'pdx-gives', 'black_gold_green', 'white_blue_red'];
  const VALID_SUBMODES = ['play', 'discover', 'explore', 'give', 'care'];

  /**
   * Set the active theme.
   * @param {string} theme - Theme ID ('hiphop-world' | 'pdx-business' | 'pdx-gives')
   * @param {Object} [options]
   * @param {string} [options.subMode] - PDX sub-mode ('play' | 'discover' | 'explore' | 'give' | 'care')
   * @param {boolean} [options.persist=true] - Save to localStorage
   */
  function set(theme, options = {}) {
    const { subMode = null, persist = true } = options;

    if (!VALID_THEMES.includes(theme)) {
      console.warn(`[ThemeLoader] Invalid theme: "${theme}". Valid: ${VALID_THEMES.join(', ')}`);
      return;
    }

    const root = document.documentElement;

    // Set theme
    root.setAttribute(THEME_ATTR, theme);

    // Handle sub-mode
    if (subMode && VALID_SUBMODES.includes(subMode)) {
      root.setAttribute(SUBMODE_ATTR, subMode);
    } else {
      root.removeAttribute(SUBMODE_ATTR);
    }

    // Persist
    if (persist) {
      try {
        localStorage.setItem(STORAGE_KEY, theme);
        if (subMode) {
          localStorage.setItem(STORAGE_SUBMODE_KEY, subMode);
        } else {
          localStorage.removeItem(STORAGE_SUBMODE_KEY);
        }
      } catch (e) {
        // localStorage unavailable (private browsing, etc.)
      }
    }

    // Dispatch event for listeners
    root.dispatchEvent(new CustomEvent('themechange', {
      detail: { theme, subMode },
      bubbles: true
    }));
  }

  /**
   * Get current theme state.
   * @returns {{ theme: string|null, subMode: string|null }}
   */
  function get() {
    const root = document.documentElement;
    return {
      theme: root.getAttribute(THEME_ATTR),
      subMode: root.getAttribute(SUBMODE_ATTR)
    };
  }

  /**
   * Initialize from localStorage or default.
   * Call this early (in <head> or DOMContentLoaded) to prevent FOUC.
   * @param {string} [defaultTheme] - Fallback if nothing stored
   * @param {Object} [defaultOptions]
   */
  function init(defaultTheme, defaultOptions = {}) {
    let theme = defaultTheme;
    let subMode = defaultOptions.subMode || null;

    try {
      const stored = localStorage.getItem(STORAGE_KEY);
      if (stored && VALID_THEMES.includes(stored)) {
        theme = stored;
      }
      const storedSubMode = localStorage.getItem(STORAGE_SUBMODE_KEY);
      if (storedSubMode && VALID_SUBMODES.includes(storedSubMode)) {
        subMode = storedSubMode;
      }
    } catch (e) {
      // localStorage unavailable
    }

    if (theme) {
      set(theme, { subMode, persist: false });
    }
  }

  /**
   * Set PDX sub-mode without changing the base theme.
   * @param {string} mode - 'play' | 'discover' | 'explore' | 'give' | 'care'
   */
  function setSubMode(mode) {
    if (!VALID_SUBMODES.includes(mode)) {
      console.warn(`[ThemeLoader] Invalid sub-mode: "${mode}". Valid: ${VALID_SUBMODES.join(', ')}`);
      return;
    }

    const current = get();
    set(current.theme || 'pdx-business', { subMode: mode });
  }

  /**
   * Get CSS variable value from current theme.
   * @param {string} varName - CSS variable name (e.g., '--color-primary')
   * @returns {string}
   */
  function getVar(varName) {
    return getComputedStyle(document.documentElement).getPropertyValue(varName).trim();
  }

  /**
   * Check if current theme is dark mode.
   * @returns {boolean}
   */
  function isDark() {
    const { theme } = get();
    return theme === 'hiphop-world' || theme === 'black_gold_green';
  }

  return { set, get, init, setSubMode, getVar, isDark, VALID_THEMES, VALID_SUBMODES };
})();

// Auto-export for module environments
if (typeof module !== 'undefined' && module.exports) {
  module.exports = ThemeLoader;
}
