import { describe, it, expect, beforeAll } from 'vitest';
import { readFileSync } from 'fs';
import { resolve } from 'path';

const ROOT = resolve(import.meta.dirname, '../../public_html');
const html = readFileSync(resolve(ROOT, 'admin/index.html'), 'utf-8');

let doc;

beforeAll(async () => {
  const { JSDOM } = await import('jsdom');
  const dom = new JSDOM(html);
  doc = dom.window.document;
});

// ============================================================
// 1. DRAG-AND-DROP UPLOAD ZONES
// ============================================================
describe('drag-and-drop — gallery upload zone', () => {
  it('gallery drop zone element exists with id "gallery-drop-zone"', () => {
    const zone = doc.getElementById('gallery-drop-zone');
    expect(zone, '#gallery-drop-zone not found').not.toBeNull();
  });

  it('gallery drop zone has visual instruction text', () => {
    const zone = doc.getElementById('gallery-drop-zone');
    expect(zone).not.toBeNull();
    expect(zone.textContent.toLowerCase()).toMatch(/drag.*drop|drop.*image/i);
  });

  it('gallery file input still exists for click-to-browse fallback', () => {
    const input = doc.getElementById('gallery-file');
    expect(input, '#gallery-file input not found').not.toBeNull();
    expect(input.getAttribute('accept')).toBe('image/*');
  });
});

describe('drag-and-drop — service image upload zones', () => {
  it('service upload areas have drop zone containers with class "svc-drop-zone"', () => {
    // The renderServiceImages function generates these dynamically,
    // but the JS code must contain the class reference
    expect(html).toMatch(/svc-drop-zone/);
  });
});

describe('drag-and-drop — CSS styles', () => {
  it('drag-over CSS class is defined with dashed green border', () => {
    // Verify the CSS rule for .drag-over exists in the style block
    expect(html).toMatch(/\.drag-over\s*\{[^}]*border[^}]*dashed[^}]*green/i);
  });

  it('drop zone base styles are defined', () => {
    expect(html).toMatch(/\.drop-zone\s*\{/);
  });
});

describe('drag-and-drop — event handler setup', () => {
  it('initDragAndDrop function is defined', () => {
    expect(html).toMatch(/function\s+initDragAndDrop\s*\(/);
  });

  it('handles dragover event (adds drag-over class)', () => {
    expect(html).toMatch(/dragover/);
    expect(html).toMatch(/drag-over/);
  });

  it('handles dragleave event (removes drag-over class)', () => {
    expect(html).toMatch(/dragleave/);
  });

  it('handles drop event', () => {
    expect(html).toMatch(/\.addEventListener\s*\(\s*['"]drop['"]/);
  });

  it('prevents default browser behavior on drag events', () => {
    expect(html).toMatch(/preventDefault/);
  });
});

// ============================================================
// 2. CLIENT-SIDE IMAGE COMPRESSION
// ============================================================
describe('image compression — compressImage function', () => {
  it('compressImage function is defined', () => {
    expect(html).toMatch(/function\s+compressImage\s*\(/);
  });

  it('compressImage accepts file, maxWidth, and quality parameters', () => {
    expect(html).toMatch(/function\s+compressImage\s*\(\s*file\s*,\s*maxWidth\s*,\s*quality\s*\)/);
  });

  it('uses Canvas API for image resizing', () => {
    expect(html).toMatch(/createElement\s*\(\s*['"]canvas['"]\s*\)/);
  });

  it('defaults to maxWidth of 1920', () => {
    expect(html).toMatch(/1920/);
  });

  it('defaults to JPEG quality of 0.85', () => {
    expect(html).toMatch(/0\.85/);
  });

  it('skips compression for files under 200KB', () => {
    // 200KB = 200 * 1024 = 204800 bytes
    expect(html).toMatch(/200\s*\*\s*1024|204800/);
  });

  it('returns a Promise', () => {
    expect(html).toMatch(/new\s+Promise/);
  });
});

describe('image compression — size display', () => {
  it('formatFileSize helper function is defined', () => {
    expect(html).toMatch(/function\s+formatFileSize\s*\(/);
  });

  it('compression info element exists in gallery form', () => {
    const info = doc.getElementById('gallery-compression-info');
    expect(info, '#gallery-compression-info not found').not.toBeNull();
  });
});

// ============================================================
// 3. CROP PREVIEW WITH ASPECT RATIO OVERLAYS
// ============================================================
describe('crop preview — aspect ratio overlays', () => {
  it('renderServiceImages references crop-preview-overlay', () => {
    expect(html).toMatch(/crop-preview-overlay/);
  });

  it('defines aspect ratio for service cards (3:1)', () => {
    // The code should reference the 3:1 aspect ratio for service cards
    expect(html).toMatch(/aspect-\[3\/1\]|3\s*\/\s*1|3:1/);
  });

  it('defines aspect ratio for hero (16:9)', () => {
    // The code should reference the 16:9 aspect ratio for hero
    expect(html).toMatch(/aspect-\[16\/9\]|16\s*\/\s*9|16:9/);
  });

  it('crop preview toggle button or auto-display logic exists', () => {
    expect(html).toMatch(/toggleCropPreview|crop-preview|showCropPreview/i);
  });
});

describe('crop preview — CSS overlay mask', () => {
  it('crop overlay mask styles are defined', () => {
    expect(html).toMatch(/\.crop-overlay|crop-preview-overlay/);
  });
});

// ============================================================
// 4. BULK GALLERY UPLOAD
// ============================================================
describe('bulk gallery upload — multiple file selection', () => {
  it('gallery file input has "multiple" attribute', () => {
    const input = doc.getElementById('gallery-file');
    expect(input).not.toBeNull();
    expect(input.hasAttribute('multiple')).toBe(true);
  });
});

describe('bulk gallery upload — progress tracking', () => {
  it('bulk upload progress container exists', () => {
    const container = doc.getElementById('gallery-upload-progress');
    expect(container, '#gallery-upload-progress not found').not.toBeNull();
  });

  it('uploadGalleryImages (plural) function is defined for bulk upload', () => {
    expect(html).toMatch(/function\s+uploadGalleryImages\s*\(/);
  });

  it('shows per-file upload progress', () => {
    // The code should create individual progress elements for each file
    expect(html).toMatch(/upload-progress-|file-progress/i);
  });
});

describe('bulk gallery upload — integration with existing patterns', () => {
  it('uses Supabase storage gallery-images bucket', () => {
    expect(html).toMatch(/from\s*\(\s*['"]gallery-images['"]\s*\)/);
  });

  it('inserts into oretir_gallery_images table', () => {
    expect(html).toMatch(/oretir_gallery_images/);
  });

  it('calls loadGalleryImages after bulk upload completes', () => {
    expect(html).toMatch(/loadGalleryImages\s*\(\s*\)/);
  });
});

// ============================================================
// 5. INTEGRATION — all features coexist
// ============================================================
describe('integration — gallery form structure', () => {
  it('gallery form still has title input', () => {
    const input = doc.getElementById('gallery-title');
    expect(input).not.toBeNull();
  });

  it('gallery form still has description textarea', () => {
    const textarea = doc.getElementById('gallery-desc');
    expect(textarea).not.toBeNull();
  });

  it('gallery form still has language select', () => {
    const select = doc.getElementById('gallery-lang');
    expect(select).not.toBeNull();
  });

  it('gallery upload button still exists', () => {
    const btn = doc.getElementById('gallery-upload-btn');
    expect(btn).not.toBeNull();
  });
});

describe('integration — service images grid', () => {
  it('service-images-grid container exists', () => {
    const grid = doc.getElementById('service-images-grid');
    expect(grid).not.toBeNull();
  });
});
