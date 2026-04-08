import { describe, it, expect, beforeEach } from 'vitest';
import { loadModule } from './helpers/load-module.js';

/**
 * blog.js exposes on window:
 *   loadBlogPosts, openBlogEditor, closeBlogEditor,
 *   blogAutoSlug, saveBlogPost, deleteBlogPost, blogSearchDebounce
 */

function blogHtml() {
  return `<!DOCTYPE html><html><head>
    <meta name="csrf-token" content="test-csrf-abc">
  </head><body>
    <select id="blog-status-filter"><option value="">All</option></select>
    <input id="blog-search" type="text" />
    <table>
      <thead><tr><th id="blog-select-all-th"></th><th>Title</th></tr></thead>
      <tbody id="blog-table-body"></tbody>
    </table>
    <div id="blog-pagination-admin"></div>
    <div id="blog-bulk-toolbar"></div>
    <!-- Editor modal -->
    <div id="blog-editor-modal" class="hidden">
      <h3 id="blog-form-title">New Post</h3>
      <input id="blog-title-en" type="text" />
      <input id="blog-title-es" type="text" />
      <input id="blog-slug" type="text" />
      <textarea id="blog-excerpt-en"></textarea>
      <textarea id="blog-excerpt-es"></textarea>
      <textarea id="blog-body-en"></textarea>
      <textarea id="blog-body-es"></textarea>
      <input id="blog-image" type="text" />
      <input id="blog-author" type="text" />
      <select id="blog-status"><option value="draft">Draft</option><option value="published">Published</option></select>
      <div id="blog-categories-list"></div>
    </div>
  </body></html>`;
}

let win, doc;

beforeEach(async () => {
  const mod = await loadModule('public_html/admin/js/blog.js', {
    html: blogHtml(),
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

describe('blog -- module loading', () => {
  it('exposes loadBlogPosts globally', () => {
    expect(typeof win.loadBlogPosts).toBe('function');
  });

  it('exposes openBlogEditor globally', () => {
    expect(typeof win.openBlogEditor).toBe('function');
  });

  it('exposes closeBlogEditor globally', () => {
    expect(typeof win.closeBlogEditor).toBe('function');
  });

  it('exposes blogAutoSlug globally', () => {
    expect(typeof win.blogAutoSlug).toBe('function');
  });

  it('exposes saveBlogPost globally', () => {
    expect(typeof win.saveBlogPost).toBe('function');
  });

  it('exposes deleteBlogPost globally', () => {
    expect(typeof win.deleteBlogPost).toBe('function');
  });

  it('exposes blogSearchDebounce globally', () => {
    expect(typeof win.blogSearchDebounce).toBe('function');
  });
});

describe('blog -- DOM fixtures', () => {
  it('has blog-table-body element', () => {
    expect(doc.getElementById('blog-table-body')).not.toBeNull();
  });

  it('has blog-editor-modal element', () => {
    expect(doc.getElementById('blog-editor-modal')).not.toBeNull();
  });

  it('has blog-status-filter element', () => {
    expect(doc.getElementById('blog-status-filter')).not.toBeNull();
  });

  it('has blog-search element', () => {
    expect(doc.getElementById('blog-search')).not.toBeNull();
  });

  it('has blog-pagination-admin element', () => {
    expect(doc.getElementById('blog-pagination-admin')).not.toBeNull();
  });

  it('has blog form fields', () => {
    expect(doc.getElementById('blog-title-en')).not.toBeNull();
    expect(doc.getElementById('blog-title-es')).not.toBeNull();
    expect(doc.getElementById('blog-slug')).not.toBeNull();
    expect(doc.getElementById('blog-body-en')).not.toBeNull();
    expect(doc.getElementById('blog-body-es')).not.toBeNull();
    expect(doc.getElementById('blog-author')).not.toBeNull();
    expect(doc.getElementById('blog-status')).not.toBeNull();
  });
});

describe('blog -- blogAutoSlug', () => {
  it('generates slug from English title', () => {
    doc.getElementById('blog-title-en').value = 'Hello World Post!';
    win.blogAutoSlug();
    expect(doc.getElementById('blog-slug').value).toBe('hello-world-post');
  });

  it('strips special characters from slug', () => {
    doc.getElementById('blog-title-en').value = 'Tire & Brake Tips: 2024!';
    win.blogAutoSlug();
    const slug = doc.getElementById('blog-slug').value;
    expect(slug).not.toMatch(/[^a-z0-9-]/);
  });

  it('generates empty slug for empty title', () => {
    doc.getElementById('blog-title-en').value = '';
    win.blogAutoSlug();
    expect(doc.getElementById('blog-slug').value).toBe('');
  });

  it('collapses multiple hyphens', () => {
    doc.getElementById('blog-title-en').value = 'Hello   World   Post';
    win.blogAutoSlug();
    expect(doc.getElementById('blog-slug').value).toBe('hello-world-post');
  });
});

describe('blog -- closeBlogEditor', () => {
  it('adds hidden class to editor modal', () => {
    const modal = doc.getElementById('blog-editor-modal');
    modal.classList.remove('hidden');
    win.closeBlogEditor();
    expect(modal.classList.contains('hidden')).toBe(true);
  });
});

describe('blog -- openBlogEditor', () => {
  it('removes hidden class from editor modal for new post', () => {
    const modal = doc.getElementById('blog-editor-modal');
    modal.classList.add('hidden');
    win.openBlogEditor();
    expect(modal.classList.contains('hidden')).toBe(false);
  });

  it('sets form title to New Blog Post for new post', () => {
    win.openBlogEditor();
    expect(doc.getElementById('blog-form-title').textContent).toBe('New Blog Post');
  });

  it('resets form fields for new post', () => {
    doc.getElementById('blog-title-en').value = 'old';
    doc.getElementById('blog-slug').value = 'old-slug';
    win.openBlogEditor();
    expect(doc.getElementById('blog-title-en').value).toBe('');
    expect(doc.getElementById('blog-slug').value).toBe('');
  });

  it('sets author default to Oregon Tires', () => {
    win.openBlogEditor();
    expect(doc.getElementById('blog-author').value).toBe('Oregon Tires');
  });
});

describe('blog -- saveBlogPost validation', () => {
  it('calls showToast when title is empty', () => {
    doc.getElementById('blog-title-en').value = '';
    doc.getElementById('blog-body-en').value = 'Some body';
    win.saveBlogPost();
    expect(win.showToast).toHaveBeenCalled();
    const lastCall = win.showToast.mock.calls[win.showToast.mock.calls.length - 1];
    expect(lastCall[1]).toBe(true); // error flag
  });

  it('calls showToast when body is empty', () => {
    doc.getElementById('blog-title-en').value = 'Valid Title';
    doc.getElementById('blog-body-en').value = '';
    win.saveBlogPost();
    expect(win.showToast).toHaveBeenCalled();
    const lastCall = win.showToast.mock.calls[win.showToast.mock.calls.length - 1];
    expect(lastCall[1]).toBe(true);
  });
});
