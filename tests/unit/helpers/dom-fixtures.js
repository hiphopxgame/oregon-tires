/**
 * DOM fixtures for admin module testing.
 * Returns HTML strings for the required DOM containers of each admin tab.
 */

export function kanbanFixture() {
  return `
    <div id="tab-repairorders">
      <div class="bg-brand-light">
        <div class="flex items-center gap-3"></div>
      </div>
      <div class="bg-white rounded-b-xl dark:bg-gray-800">
        <div class="overflow-x-auto">
          <table id="ro-table">
            <thead><tr><th>RO</th><th>Status</th></tr></thead>
            <tbody id="ro-table-body"></tbody>
          </table>
        </div>
        <div id="ro-pagination"></div>
      </div>
    </div>
  `;
}

export function repairOrdersFixture() {
  return `
    <div id="tab-repairorders">
      <div class="bg-brand-light">
        <h2>Repair Orders</h2>
        <div class="flex items-center gap-3">
          <select id="ro-status-filter"><option value="">All</option></select>
          <input id="ro-search" type="text" />
        </div>
      </div>
      <div class="bg-white rounded-b-xl dark:bg-gray-800">
        <div class="overflow-x-auto">
          <table>
            <thead><tr>
              <th id="ro-select-all-th"></th>
              <th>RO #</th><th>Customer</th><th>Vehicle</th><th>Status</th><th>Actions</th>
            </tr></thead>
            <tbody id="ro-table-body"></tbody>
          </table>
        </div>
        <div id="ro-pagination"></div>
        <div id="ro-count"></div>
      </div>
    </div>
    <div id="ro-detail-modal" class="hidden"></div>
    <div id="ro-create-modal" class="hidden"></div>
  `;
}

export function invoicesFixture() {
  return `
    <div id="tab-invoices">
      <div class="flex items-center gap-3">
        <select id="invoice-status-filter"><option value="">All</option></select>
        <input id="invoice-search" type="text" />
      </div>
      <table>
        <thead><tr><th>Invoice</th><th>RO</th><th>Customer</th><th>Amount</th><th>Status</th></tr></thead>
        <tbody id="invoice-table-body"></tbody>
      </table>
      <div id="invoice-pagination"></div>
      <div id="invoice-count"></div>
    </div>
  `;
}

export function blogFixture() {
  return `
    <div id="tab-blog">
      <div class="flex items-center gap-3">
        <select id="blog-status-filter"><option value="">All</option></select>
        <input id="blog-search" type="text" />
      </div>
      <table>
        <thead><tr><th></th><th>Title</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody id="blog-table-body"></tbody>
      </table>
      <div id="blog-pagination"></div>
      <div id="blog-count"></div>
      <div id="blog-select-all-th"></div>
    </div>
    <div id="blog-form" class="hidden">
      <input id="blog-title-en" type="text" />
      <input id="blog-title-es" type="text" />
      <input id="blog-slug" type="text" />
      <textarea id="blog-content-en"></textarea>
      <textarea id="blog-content-es"></textarea>
      <select id="blog-category"><option value="">Select</option></select>
      <select id="blog-status-select"><option value="draft">Draft</option><option value="published">Published</option></select>
      <input id="blog-featured-image" type="file" />
      <input id="blog-id" type="hidden" />
      <h3 id="blog-form-title">New Post</h3>
    </div>
  `;
}

export function servicesFixture() {
  return `
    <div id="tab-services">
      <table>
        <thead><tr><th>Name</th><th>Category</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody id="services-table-body"></tbody>
      </table>
      <div id="services-select-all-th"></div>
      <div id="services-count"></div>
    </div>
    <div id="service-form" class="hidden">
      <input id="service-name-en" type="text" />
      <input id="service-name-es" type="text" />
      <input id="service-slug" type="text" />
      <input id="service-id" type="hidden" />
    </div>
  `;
}

export function loyaltyFixture() {
  return `
    <div id="tab-loyalty">
      <div id="loyalty-stats"></div>
      <table>
        <thead><tr><th>Customer</th><th>Points</th><th>Actions</th></tr></thead>
        <tbody id="loyalty-table-body"></tbody>
      </table>
      <div id="loyalty-pagination"></div>
    </div>
    <div id="rewards-list"></div>
    <div id="reward-form" class="hidden">
      <input id="reward-name-en" type="text" />
      <input id="reward-name-es" type="text" />
      <input id="reward-points" type="number" />
      <input id="reward-id" type="hidden" />
    </div>
  `;
}

export function promotionsFixture() {
  return `
    <div id="tab-promotions">
      <table>
        <thead><tr><th>Title</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody id="promo-table-body"></tbody>
      </table>
    </div>
    <div id="promo-form" class="hidden">
      <input id="promo-title-en" type="text" />
      <input id="promo-title-es" type="text" />
      <input id="promo-type" type="text" />
      <input id="promo-start" type="date" />
      <input id="promo-end" type="date" />
      <input id="promo-active" type="checkbox" />
      <input id="promo-image" type="file" />
      <input id="promo-id" type="hidden" />
      <textarea id="promo-desc-en"></textarea>
      <textarea id="promo-desc-es"></textarea>
      <input id="promo-cta-text-en" type="text" />
      <input id="promo-cta-url" type="text" />
      <input id="promo-bg-color" type="text" />
      <h3 id="promo-form-title">New Promotion</h3>
    </div>
  `;
}

export function subscribersFixture() {
  return `
    <div id="tab-subscribers">
      <input id="subscriber-search" type="text" />
      <span id="subscriber-active-count">0</span>
      <span id="subscriber-inactive-count">0</span>
      <table>
        <thead><tr><th>Email</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody id="subscriber-table-body"></tbody>
      </table>
      <div id="subscriber-pagination"></div>
      <div id="subscriber-count"></div>
    </div>
  `;
}
