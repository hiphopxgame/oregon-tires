import { describe, it, expect, beforeEach } from 'vitest';
import { loadModule } from './helpers/load-module.js';

/**
 * services.js exposes on window:
 *   loadServices, toggleServiceForm, saveService,
 *   autoServiceSlug, closeServiceFaqPanel, toggleServiceFaqForm, saveServiceFaq
 */

function servicesHtml() {
  return `<!DOCTYPE html><html><head>
    <meta name="csrf-token" content="test-csrf-abc">
  </head><body>
    <table>
      <thead><tr><th id="services-select-all-th"></th><th>Name</th></tr></thead>
      <tbody id="services-table-body"></tbody>
    </table>
    <div id="services-bulk-toolbar"></div>
    <!-- Service form panel -->
    <div id="service-form-panel" class="hidden">
      <h3 id="svc-form-title">New Service</h3>
      <input id="svc-name-en" type="text" />
      <input id="svc-name-es" type="text" />
      <input id="svc-slug" type="text" />
      <input id="svc-icon" type="text" />
      <textarea id="svc-description-en"></textarea>
      <textarea id="svc-description-es"></textarea>
      <textarea id="svc-body-en"></textarea>
      <textarea id="svc-body-es"></textarea>
      <input id="svc-price-en" type="text" />
      <input id="svc-price-es" type="text" />
      <input id="svc-color-hex" type="text" value="#10B981" />
      <input id="svc-image-url" type="text" />
      <input id="svc-duration" type="text" />
      <select id="svc-category"><option value="maintenance">Maintenance</option><option value="tires">Tires</option></select>
      <input id="svc-active" type="checkbox" checked />
      <input id="svc-bookable" type="checkbox" checked />
      <input id="svc-detail-page" type="checkbox" checked />
      <input id="svc-sort" type="number" value="0" />
      <button id="svc-save-btn">Create Service</button>
    </div>
    <!-- FAQ panel -->
    <div id="service-faq-panel" class="hidden">
      <h3 id="service-faq-panel-title"></h3>
      <div id="service-faq-list"></div>
      <div id="service-faq-form" class="hidden">
        <input id="sfaq-question-en" type="text" />
        <input id="sfaq-question-es" type="text" />
        <textarea id="sfaq-answer-en"></textarea>
        <textarea id="sfaq-answer-es"></textarea>
        <input id="sfaq-sort" type="number" value="0" />
      </div>
    </div>
  </body></html>`;
}

let win, doc;

beforeEach(async () => {
  const mod = await loadModule('public_html/admin/js/services.js', {
    html: servicesHtml(),
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

describe('services -- module loading', () => {
  it('exposes loadServices globally', () => {
    expect(typeof win.loadServices).toBe('function');
  });

  it('exposes toggleServiceForm globally', () => {
    expect(typeof win.toggleServiceForm).toBe('function');
  });

  it('exposes saveService globally', () => {
    expect(typeof win.saveService).toBe('function');
  });

  it('exposes autoServiceSlug globally', () => {
    expect(typeof win.autoServiceSlug).toBe('function');
  });

  it('exposes closeServiceFaqPanel globally', () => {
    expect(typeof win.closeServiceFaqPanel).toBe('function');
  });

  it('exposes toggleServiceFaqForm globally', () => {
    expect(typeof win.toggleServiceFaqForm).toBe('function');
  });

  it('exposes saveServiceFaq globally', () => {
    expect(typeof win.saveServiceFaq).toBe('function');
  });
});

describe('services -- DOM fixtures', () => {
  it('has services-table-body element', () => {
    expect(doc.getElementById('services-table-body')).not.toBeNull();
  });

  it('has service-form-panel element', () => {
    expect(doc.getElementById('service-form-panel')).not.toBeNull();
  });

  it('has service-faq-panel element', () => {
    expect(doc.getElementById('service-faq-panel')).not.toBeNull();
  });

  it('has all form input fields', () => {
    expect(doc.getElementById('svc-name-en')).not.toBeNull();
    expect(doc.getElementById('svc-name-es')).not.toBeNull();
    expect(doc.getElementById('svc-slug')).not.toBeNull();
    expect(doc.getElementById('svc-category')).not.toBeNull();
    expect(doc.getElementById('svc-active')).not.toBeNull();
    expect(doc.getElementById('svc-bookable')).not.toBeNull();
    expect(doc.getElementById('svc-sort')).not.toBeNull();
  });

  it('has FAQ form fields', () => {
    expect(doc.getElementById('sfaq-question-en')).not.toBeNull();
    expect(doc.getElementById('sfaq-question-es')).not.toBeNull();
    expect(doc.getElementById('sfaq-answer-en')).not.toBeNull();
    expect(doc.getElementById('sfaq-answer-es')).not.toBeNull();
    expect(doc.getElementById('sfaq-sort')).not.toBeNull();
  });
});

describe('services -- toggleServiceForm', () => {
  it('shows form panel when hidden', () => {
    const panel = doc.getElementById('service-form-panel');
    panel.classList.add('hidden');
    win.toggleServiceForm();
    expect(panel.classList.contains('hidden')).toBe(false);
  });

  it('hides form panel when visible', () => {
    const panel = doc.getElementById('service-form-panel');
    panel.classList.remove('hidden');
    win.toggleServiceForm();
    expect(panel.classList.contains('hidden')).toBe(true);
  });

  it('resets form title to New Service when showing', () => {
    const panel = doc.getElementById('service-form-panel');
    panel.classList.add('hidden');
    doc.getElementById('svc-form-title').textContent = 'Edit Service';
    win.toggleServiceForm();
    expect(doc.getElementById('svc-form-title').textContent).toBe('New Service');
  });

  it('resets form fields when showing', () => {
    const panel = doc.getElementById('service-form-panel');
    panel.classList.add('hidden');
    doc.getElementById('svc-name-en').value = 'Old Name';
    doc.getElementById('svc-slug').value = 'old-slug';
    win.toggleServiceForm();
    expect(doc.getElementById('svc-name-en').value).toBe('');
    expect(doc.getElementById('svc-slug').value).toBe('');
  });
});

describe('services -- closeServiceFaqPanel', () => {
  it('hides the FAQ panel', () => {
    const panel = doc.getElementById('service-faq-panel');
    panel.classList.remove('hidden');
    win.closeServiceFaqPanel();
    expect(panel.classList.contains('hidden')).toBe(true);
  });
});

describe('services -- toggleServiceFaqForm', () => {
  it('shows FAQ form when hidden', () => {
    const form = doc.getElementById('service-faq-form');
    form.classList.add('hidden');
    win.toggleServiceFaqForm();
    expect(form.classList.contains('hidden')).toBe(false);
  });

  it('hides FAQ form when visible', () => {
    const form = doc.getElementById('service-faq-form');
    form.classList.remove('hidden');
    win.toggleServiceFaqForm();
    expect(form.classList.contains('hidden')).toBe(true);
  });

  it('clears FAQ form fields when showing', () => {
    const form = doc.getElementById('service-faq-form');
    form.classList.add('hidden');
    doc.getElementById('sfaq-question-en').value = 'Old question';
    win.toggleServiceFaqForm();
    expect(doc.getElementById('sfaq-question-en').value).toBe('');
  });
});
