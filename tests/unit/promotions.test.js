import { describe, it, expect, beforeEach } from 'vitest';
import { loadModule } from './helpers/load-module.js';

/**
 * promotions.js exposes on window:
 *   loadPromotions, togglePromotionForm, savePromotion,
 *   updatePromoPreview, removePromoImage, toggleExitIntentFields,
 *   openPromoPreviewModal, closePromoPreviewModal
 */

function promotionsHtml() {
  return `<!DOCTYPE html><html><head>
    <meta name="csrf-token" content="test-csrf-abc">
  </head><body>
    <table>
      <thead><tr><th id="promo-select-all-th"></th><th>Title</th></tr></thead>
      <tbody id="promotions-table-body"></tbody>
    </table>
    <div id="promotions-bulk-toolbar"></div>
    <!-- Promotion form panel -->
    <div id="promotion-form-panel" class="hidden">
      <h3 id="promo-form-title">New Promotion</h3>
      <select id="promo-placement">
        <option value="banner">Banner</option>
        <option value="exit_intent">Exit Intent</option>
        <option value="sidebar">Sidebar</option>
        <option value="inline">Inline</option>
      </select>
      <input id="promo-title-en" type="text" />
      <input id="promo-title-es" type="text" />
      <textarea id="promo-body-en"></textarea>
      <textarea id="promo-body-es"></textarea>
      <input id="promo-cta-text-en" type="text" value="Book Now" />
      <input id="promo-cta-text-es" type="text" value="Reserve Ahora" />
      <input id="promo-cta-url" type="text" value="/book-appointment/" />
      <input id="promo-bg-color" type="text" value="#f59e0b" />
      <input id="promo-text-color" type="text" value="#000000" />
      <input id="promo-badge-en" type="text" />
      <input id="promo-badge-es" type="text" />
      <input id="promo-active" type="checkbox" />
      <input id="promo-starts" type="datetime-local" />
      <input id="promo-ends" type="datetime-local" />
      <input id="promo-sort" type="number" value="0" />
      <!-- Exit-intent fields -->
      <div id="exit-intent-fields" class="hidden">
        <input id="promo-subtitle-en" type="text" />
        <input id="promo-subtitle-es" type="text" />
        <input id="promo-placeholder-en" type="text" />
        <input id="promo-placeholder-es" type="text" />
        <input id="promo-success-en" type="text" />
        <input id="promo-success-es" type="text" />
        <input id="promo-error-en" type="text" />
        <input id="promo-error-es" type="text" />
        <input id="promo-nospam-en" type="text" />
        <input id="promo-nospam-es" type="text" />
        <input id="promo-popup-icon" type="text" />
      </div>
      <div class="banner-only-field">Banner fields</div>
      <input id="promo-image-file" type="file" />
      <div id="promo-image-preview" class="hidden"><img src="" /></div>
      <div id="promo-existing-image" class="hidden"><img src="" /></div>
      <button id="promo-save-btn">Create Promotion</button>
    </div>
    <!-- Live preview -->
    <div id="promo-live-preview"></div>
    <!-- Preview modal -->
    <div id="promo-preview-modal" class="hidden">
      <button id="promo-preview-lang-toggle">EN / ES</button>
      <div id="promo-preview-modal-body"></div>
    </div>
  </body></html>`;
}

let win, doc;

beforeEach(async () => {
  const mod = await loadModule('public_html/admin/js/promotions.js', {
    html: promotionsHtml(),
    globals: {
      BulkManager: {
        init: () => {},
        reset: () => {},
        selectAllHtml: () => '',
        toolbarHtml: () => '',
        checkboxHtml: () => '',
        bind: () => {},
      },
      confirm: () => true,
    },
  });
  win = mod.window;
  doc = mod.document;
});

describe('promotions -- module loading', () => {
  it('exposes loadPromotions globally', () => {
    expect(typeof win.loadPromotions).toBe('function');
  });

  it('exposes togglePromotionForm globally', () => {
    expect(typeof win.togglePromotionForm).toBe('function');
  });

  it('exposes savePromotion globally', () => {
    expect(typeof win.savePromotion).toBe('function');
  });

  it('exposes updatePromoPreview globally', () => {
    expect(typeof win.updatePromoPreview).toBe('function');
  });

  it('exposes removePromoImage globally', () => {
    expect(typeof win.removePromoImage).toBe('function');
  });

  it('exposes toggleExitIntentFields globally', () => {
    expect(typeof win.toggleExitIntentFields).toBe('function');
  });

  it('exposes openPromoPreviewModal globally', () => {
    expect(typeof win.openPromoPreviewModal).toBe('function');
  });

  it('exposes closePromoPreviewModal globally', () => {
    expect(typeof win.closePromoPreviewModal).toBe('function');
  });
});

describe('promotions -- DOM fixtures', () => {
  it('has promotions-table-body element', () => {
    expect(doc.getElementById('promotions-table-body')).not.toBeNull();
  });

  it('has promotion-form-panel element', () => {
    expect(doc.getElementById('promotion-form-panel')).not.toBeNull();
  });

  it('has promo-preview-modal element', () => {
    expect(doc.getElementById('promo-preview-modal')).not.toBeNull();
  });

  it('has all form fields', () => {
    expect(doc.getElementById('promo-placement')).not.toBeNull();
    expect(doc.getElementById('promo-title-en')).not.toBeNull();
    expect(doc.getElementById('promo-title-es')).not.toBeNull();
    expect(doc.getElementById('promo-body-en')).not.toBeNull();
    expect(doc.getElementById('promo-bg-color')).not.toBeNull();
    expect(doc.getElementById('promo-text-color')).not.toBeNull();
    expect(doc.getElementById('promo-active')).not.toBeNull();
    expect(doc.getElementById('promo-sort')).not.toBeNull();
  });

  it('has exit-intent-specific fields', () => {
    expect(doc.getElementById('promo-subtitle-en')).not.toBeNull();
    expect(doc.getElementById('promo-placeholder-en')).not.toBeNull();
    expect(doc.getElementById('promo-success-en')).not.toBeNull();
    expect(doc.getElementById('promo-popup-icon')).not.toBeNull();
  });
});

describe('promotions -- togglePromotionForm', () => {
  it('shows form panel when hidden', () => {
    const panel = doc.getElementById('promotion-form-panel');
    panel.classList.add('hidden');
    win.togglePromotionForm();
    expect(panel.classList.contains('hidden')).toBe(false);
  });

  it('hides form panel when visible', () => {
    const panel = doc.getElementById('promotion-form-panel');
    panel.classList.remove('hidden');
    win.togglePromotionForm();
    expect(panel.classList.contains('hidden')).toBe(true);
  });

  it('resets form title when showing', () => {
    const panel = doc.getElementById('promotion-form-panel');
    panel.classList.add('hidden');
    doc.getElementById('promo-form-title').textContent = 'Edit Promotion';
    win.togglePromotionForm();
    expect(doc.getElementById('promo-form-title').textContent).toBe('New Promotion');
  });

  it('resets form fields when showing', () => {
    const panel = doc.getElementById('promotion-form-panel');
    panel.classList.add('hidden');
    doc.getElementById('promo-title-en').value = 'Old Promo';
    doc.getElementById('promo-bg-color').value = '#ff0000';
    win.togglePromotionForm();
    expect(doc.getElementById('promo-title-en').value).toBe('');
    expect(doc.getElementById('promo-bg-color').value).toBe('#f59e0b');
  });

  it('sets placement to banner on reset', () => {
    const panel = doc.getElementById('promotion-form-panel');
    panel.classList.add('hidden');
    doc.getElementById('promo-placement').value = 'exit_intent';
    win.togglePromotionForm();
    expect(doc.getElementById('promo-placement').value).toBe('banner');
  });
});

describe('promotions -- toggleExitIntentFields', () => {
  it('shows exit-intent fields when placement is exit_intent', () => {
    doc.getElementById('promo-placement').value = 'exit_intent';
    win.toggleExitIntentFields();
    const exitFields = doc.getElementById('exit-intent-fields');
    expect(exitFields.classList.contains('hidden')).toBe(false);
  });

  it('hides exit-intent fields when placement is banner', () => {
    doc.getElementById('promo-placement').value = 'banner';
    win.toggleExitIntentFields();
    const exitFields = doc.getElementById('exit-intent-fields');
    expect(exitFields.classList.contains('hidden')).toBe(true);
  });

  it('hides banner-only fields when placement is exit_intent', () => {
    doc.getElementById('promo-placement').value = 'exit_intent';
    win.toggleExitIntentFields();
    const bannerFields = doc.querySelectorAll('.banner-only-field');
    bannerFields.forEach((el) => {
      expect(el.classList.contains('hidden')).toBe(true);
    });
  });

  it('shows banner-only fields when placement is banner', () => {
    doc.getElementById('promo-placement').value = 'banner';
    win.toggleExitIntentFields();
    const bannerFields = doc.querySelectorAll('.banner-only-field');
    bannerFields.forEach((el) => {
      expect(el.classList.contains('hidden')).toBe(false);
    });
  });
});

describe('promotions -- closePromoPreviewModal', () => {
  it('adds hidden class to preview modal', () => {
    const modal = doc.getElementById('promo-preview-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    win.closePromoPreviewModal();
    expect(modal.classList.contains('hidden')).toBe(true);
    expect(modal.classList.contains('flex')).toBe(false);
  });
});

describe('promotions -- removePromoImage', () => {
  it('hides the existing image container', () => {
    const existing = doc.getElementById('promo-existing-image');
    existing.classList.remove('hidden');
    win.removePromoImage();
    expect(existing.classList.contains('hidden')).toBe(true);
  });

  it('creates a remove-image flag input', () => {
    win.removePromoImage();
    const flag = doc.getElementById('promo-remove-image-flag');
    expect(flag).not.toBeNull();
    expect(flag.value).toBe('1');
  });
});

describe('promotions -- savePromotion validation', () => {
  it('calls showToast when title is empty', () => {
    doc.getElementById('promo-title-en').value = '';
    win.savePromotion();
    expect(win.showToast).toHaveBeenCalled();
    const lastCall = win.showToast.mock.calls[win.showToast.mock.calls.length - 1];
    expect(lastCall[1]).toBe(true);
  });
});

describe('promotions -- updatePromoPreview', () => {
  it('renders preview content into promo-live-preview', () => {
    doc.getElementById('promo-title-en').value = 'Summer Special';
    doc.getElementById('promo-placement').value = 'banner';
    doc.getElementById('promo-bg-color').value = '#ff5500';
    doc.getElementById('promo-text-color').value = '#ffffff';
    win.updatePromoPreview();
    const preview = doc.getElementById('promo-live-preview');
    expect(preview.children.length).toBeGreaterThan(0);
    expect(preview.textContent).toContain('Summer Special');
  });

  it('renders exit-intent preview with subtitle', () => {
    doc.getElementById('promo-placement').value = 'exit_intent';
    doc.getElementById('promo-title-en').value = 'Wait!';
    doc.getElementById('promo-subtitle-en').value = 'Get 10% off';
    win.updatePromoPreview();
    const preview = doc.getElementById('promo-live-preview');
    expect(preview.textContent).toContain('Wait!');
    expect(preview.textContent).toContain('Get 10% off');
  });
});
