/**
 * Promotions: list and form.
 *
 * The status shown is derived on the server from status + dates, so a
 * promotion becomes Scheduled, Active or Expired on its own.
 */

import { api } from '../api.js';
import { navigate } from '../router.js';
import { session } from '../session.js';
import { dateLabel, el, esc } from '../ui/dom.js';
import { DataTable } from '../ui/table.js';
import { bindSlugPreview, field, fill, section, select, switchField, textarea } from '../ui/form.js';
import { mediaField } from '../ui/media-picker.js';
import { notify } from '../ui/feedback.js';
import {
  bindFormSubmit, crudActions, featuredStar, formShell, pageHead, schedulePill, thumbCell,
} from './helpers.js';

const STATE_FILTER = [
  { value: 'active', label: 'Active now' },
  { value: 'scheduled', label: 'Scheduled' },
  { value: 'expired', label: 'Expired' },
  { value: 'draft', label: 'Draft' },
  { value: 'inactive', label: 'Archived' },
];

export async function promotionsPage(outlet) {
  outlet.appendChild(pageHead({
    title: 'Promotions',
    description: 'Discount offers applied to services. Set the dates and the CMS works out '
      + 'whether a promotion is scheduled, live or expired.',
    actions: session.can('promotions.create')
      ? [{ label: 'Add Promotion', iconName: 'i-plus', onClick: () => navigate('/promotions/new') }]
      : [],
  }));

  const table = new DataTable({
    searchPlaceholder: 'Search promotions…',
    defaultSort: { sort: 'display_order', direction: 'ASC' },
    fetch: (query) => api.list('/promotions', query),

    filters: [
      { name: 'state', label: 'State', options: STATE_FILTER },
      {
        name: 'featured', label: 'Featured',
        options: [{ value: '1', label: 'Featured only' }, { value: '0', label: 'Not featured' }],
      },
    ],

    columns: [
      { key: 'image', label: 'Image', width: '70px', render: (row) => thumbCell(row, 'title') },
      {
        key: 'title', label: 'Promotion', sortable: true,
        render: (row) => `
          <span class="cell-title">${esc(row.title)}</span>
          <span class="cell-sub">${esc(row.badge_label || row.slug)}</span>
        `,
      },
      {
        key: 'discount', label: 'Discount', className: 'nowrap',
        render: (row) => esc(row.discount_label || '—'),
      },
      {
        key: 'start_date', label: 'Runs', sortable: true, className: 'nowrap',
        render: (row) => `
          <span>${esc(row.start_date ? dateLabel(row.start_date) : 'Immediately')}</span>
          <span class="cell-sub">to ${esc(row.end_date ? dateLabel(row.end_date) : 'no end date')}</span>
        `,
      },
      { key: 'status', label: 'State', sortable: true, render: (row) => schedulePill(row) },
      { key: 'featured', label: 'Featured', sortable: true, render: (row) => featuredStar(row.featured) },
    ],

    rowActions: (row) => crudActions({
      row,
      base: '/promotions',
      permission: 'promotions',
      table,
      statusValues: ['published', 'archived'],
      activateLabels: ['Unpublish', 'Publish'],
      onEdit: (r) => navigate(`/promotions/${r.id}/edit`),
    }),

    emptyTitle: 'No promotions yet',
    emptyMessage: 'Create a discount offer, set its dates, and it goes live on the website by itself.',
    emptyActionLabel: session.can('promotions.create') ? 'Add Promotion' : null,
    onEmptyAction: () => navigate('/promotions/new'),
  });

  table.mount(outlet);
}

export async function promotionFormPage(outlet, args) {
  const id = args.id ? Number(args.id) : null;
  const isEdit = Boolean(id);

  let record = null;
  if (isEdit) {
    try {
      record = (await api.get(`/promotions/${id}`)).data;
    } catch (error) {
      notify.error(error.message);
      navigate('/promotions');
      return;
    }
  }

  // Services this promotion can be attached to.
  let services = [];
  try {
    services = (await api.list('/services', { per_page: 100, status: 'active' })).data;
  } catch {
    services = [];
  }

  outlet.appendChild(pageHead({
    title: isEdit ? `Edit ${record.title}` : 'Add a promotion',
    description: 'Publish the promotion, then let the start and end dates control when it shows.',
  }));

  const { form, body } = formShell({
    backTo: '/promotions',
    saveLabel: isEdit ? 'Save changes' : 'Create promotion',
  });

  body.appendChild(fill(
    section('Basic information'),
    field({
      name: 'title', label: 'Promotion title', required: true, span: 8,
      value: record?.title, placeholder: 'e.g. Midweek Massage Offer',
    }),
    field({
      name: 'slug', label: 'URL slug', span: 4, value: record?.slug,
      hint: 'Leave blank to generate from the title.',
    }),
    field({
      name: 'badge_label', label: 'Badge text', span: 4, value: record?.badge_label,
      placeholder: 'Midweek', hint: 'Small label on the promotion card.',
    }),
    field({
      name: 'booking_url', label: 'Booking link', type: 'url', span: 8,
      value: record?.booking_url,
    }),
    textarea({
      name: 'description', label: 'Description', rows: 4, span: 12,
      value: record?.description,
    }),
  ));

  // --- Discount ---------------------------------------------------
  const discountSection = fill(
    section('Discount', 'Choose how the saving is expressed. Only the matching field is required.'),
    select({
      name: 'discount_type', label: 'Discount type', required: true, span: 4,
      value: record?.discount_type || 'percentage', placeholder: '',
      options: [
        { value: 'percentage', label: 'Percentage off' },
        { value: 'fixed', label: 'Fixed amount off' },
        { value: 'special_price', label: 'Special promotional price' },
      ],
    }),
    field({
      name: 'discount_value', label: 'Discount value', type: 'number', step: '0.01', min: 0,
      span: 4, value: record?.discount_value, dataType: 'number',
      hint: 'A percentage (1–100) or a dollar amount.',
    }),
    field({
      name: 'original_price', label: 'Original price', type: 'number', step: '0.01', min: 0,
      span: 4, value: record?.original_price, dataType: 'number', prefix: '$',
    }),
    field({
      name: 'promo_price', label: 'Promotional price', type: 'number', step: '0.01', min: 0,
      span: 4, value: record?.promo_price, dataType: 'number', prefix: '$',
      hint: 'Required when using a special promotional price.',
    }),
  );

  body.appendChild(discountSection);

  // Dim the fields that do not apply to the chosen discount type.
  const typeSelect = discountSection.querySelector('[name="discount_type"]');
  const valueField = discountSection.querySelector('[data-field="discount_value"]');
  const promoField = discountSection.querySelector('[data-field="promo_price"]');

  const syncDiscountFields = () => {
    const isSpecialPrice = typeSelect.value === 'special_price';
    valueField.style.opacity = isSpecialPrice ? '0.45' : '1';
    promoField.style.opacity = isSpecialPrice ? '1' : '0.45';
  };

  typeSelect.addEventListener('change', syncDiscountFields);
  syncDiscountFields();

  // --- Services ---------------------------------------------------
  if (services.length) {
    const servicesSection = section(
      'Applies to',
      'Optional. Naming the services a promotion covers lets the website list them on the offer.'
    );
    const grid = servicesSection.querySelector('.grid');
    const holder = el('<div class="col-12 perm-group"><div class="perm-group__items"></div></div>');
    const items = holder.querySelector('.perm-group__items');

    const selectedIds = new Set((record?.service_ids || []).map(Number));

    services.forEach((service) => {
      items.appendChild(el(`
        <label class="check">
          <input type="checkbox" data-service-id="${service.id}"
            ${selectedIds.has(Number(service.id)) ? 'checked' : ''}>
          <span>${esc(service.name)}<small>${esc(service.category_name || '')}</small></span>
        </label>
      `));
    });

    grid.appendChild(holder);
    body.appendChild(servicesSection);
  }

  // --- Media ------------------------------------------------------
  body.appendChild(fill(
    section('Image'),
    mediaField({
      name: 'media_id', label: 'Promotion image', span: 12,
      value: record?.media_id, imageUrl: record?.image_url, altText: record?.image_alt,
    }),
  ));

  // --- Schedule ---------------------------------------------------
  body.appendChild(fill(
    section('Schedule & publishing',
      'A published promotion goes live on its start date and comes down after its end date, automatically.'),
    select({
      name: 'status', label: 'Status', span: 4, placeholder: '',
      value: record?.status || 'draft',
      options: [
        { value: 'draft', label: 'Draft — not on the website' },
        { value: 'published', label: 'Published — governed by the dates below' },
        { value: 'archived', label: 'Archived — permanently off the website' },
      ],
    }),
    field({
      name: 'start_date', label: 'Start date', type: 'date', span: 4,
      value: record?.start_date, hint: 'Leave blank to start immediately.',
    }),
    field({
      name: 'end_date', label: 'End date', type: 'date', span: 4,
      value: record?.end_date, hint: 'Leave blank for no expiry.',
    }),
    field({
      name: 'display_order', label: 'Display order', type: 'number', min: 0, span: 4,
      value: record?.display_order ?? 0, dataType: 'number',
    }),
    switchField({
      name: 'featured', label: 'Featured promotion',
      checked: Boolean(record?.featured), span: 6,
    }),
  ));

  bindSlugPreview(form, 'title', 'slug');

  bindFormSubmit({
    form,
    base: '/promotions',
    id,
    redirectTo: '/promotions',
    successMessage: isEdit ? 'Promotion updated.' : 'Promotion created.',
    transform: (payload, formEl) => {
      payload.media_id = payload.media_id ? Number(payload.media_id) : null;

      const checked = [...formEl.querySelectorAll('[data-service-id]:checked')]
        .map((input) => Number(input.dataset.serviceId));

      if (formEl.querySelector('[data-service-id]')) payload.service_ids = checked;

      return payload;
    },
  });

  outlet.appendChild(form);

  if (isEdit) {
    outlet.appendChild(el(`
      <div class="card mt-3"><div class="card__body">
        <dl class="detail-list" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem">
          <div><dt>Current state</dt><dd>${schedulePill(record)}</dd></div>
          <div><dt>Stored status</dt><dd>${esc(record.status)}</dd></div>
          <div><dt>Created</dt><dd>${esc(dateLabel(record.created_at))}</dd></div>
        </dl>
      </div></div>
    `));
  }
}
