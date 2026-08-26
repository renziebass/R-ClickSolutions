/** Services: data table, and the add/edit form. */

import { api } from '../api.js';
import { navigate } from '../router.js';
import { session } from '../session.js';
import { dateLabel, el, esc, relativeTime } from '../ui/dom.js';
import { DataTable } from '../ui/table.js';
import { field, fill, section, select, switchField, textarea, bindSlugPreview } from '../ui/form.js';
import { mediaField } from '../ui/media-picker.js';
import { notify } from '../ui/feedback.js';
import {
  bindFormSubmit, crudActions, featuredStar, formShell,
  loadOptions, pageHead, statusPill, thumbCell,
} from './helpers.js';

// =================================================================
// LIST
// =================================================================
export async function servicesPage(outlet) {
  outlet.appendChild(pageHead({
    title: 'Services',
    description: 'The treatment menu shown on the website. Active services appear publicly; '
      + 'inactive ones stay here for later.',
    // Import sits first so the primary "Add Service" stays rightmost.
    // pageHead filters out the nulls.
    actions: [
      session.can('services.import')
        ? {
            label: 'Import CSV', iconName: 'i-upload', variant: 'ghost',
            onClick: () => navigate('/services/import'),
          }
        : null,
      session.can('services.create')
        ? { label: 'Add Service', iconName: 'i-plus', onClick: () => navigate('/services/new') }
        : null,
    ],
  }));

  const table = new DataTable({
    searchPlaceholder: 'Search services by name or description…',
    defaultSort: { sort: 'display_order', direction: 'ASC' },
    fetch: (query) => api.list('/services', query),

    filters: [
      {
        name: 'category_id',
        label: 'Category',
        options: [],
        load: async () => (await loadOptions('/categories/options'))
          .map((option) => ({ value: option.value, label: option.label })),
      },
      {
        name: 'status',
        label: 'Status',
        options: [
          { value: 'active', label: 'Active' },
          { value: 'inactive', label: 'Inactive' },
        ],
      },
      {
        name: 'featured',
        label: 'Featured',
        options: [
          { value: '1', label: 'Featured only' },
          { value: '0', label: 'Not featured' },
        ],
      },
    ],

    columns: [
      { key: 'image', label: 'Image', width: '70px', render: (row) => thumbCell(row) },
      {
        key: 'name',
        label: 'Service',
        sortable: true,
        render: (row) => `
          <span class="cell-title">${esc(row.name)}</span>
          <span class="cell-sub">${esc(row.short_description || row.slug)}</span>
        `,
      },
      {
        key: 'category',
        label: 'Category',
        sortable: true,
        render: (row) => esc(row.category_name || '—'),
      },
      {
        key: 'price',
        label: 'Price',
        sortable: true,
        className: 'cell-num nowrap',
        render: (row) => {
          const base = esc(row.price_label || '—');
          return row.promo_price
            ? `${base} <span class="cell-sub">promo ${esc(row.promo_price)}</span>`
            : base;
        },
      },
      {
        key: 'duration',
        label: 'Duration',
        sortable: true,
        className: 'nowrap',
        render: (row) => esc(row.duration_label || '—'),
      },
      { key: 'status', label: 'Status', sortable: true, render: (row) => statusPill(row.status) },
      {
        key: 'featured',
        label: 'Featured',
        sortable: true,
        render: (row) => {
          const star = featuredStar(row.featured);
          return row.most_loved_rank
            ? `${star} <span class="pill pill--plain">Most Loved #${row.most_loved_rank}</span>`
            : star;
        },
      },
      {
        key: 'updated_at',
        label: 'Updated',
        sortable: true,
        className: 'nowrap',
        render: (row) => `<span class="muted">${esc(relativeTime(row.updated_at))}</span>`,
      },
    ],

    rowActions: (row) => crudActions({
      row,
      base: '/services',
      permission: 'services',
      table,
      onEdit: (r) => navigate(`/services/${r.id}/edit`),
    }),

    emptyTitle: 'No services yet',
    emptyMessage: 'Add your first treatment and it will appear on the website as soon as it is active.',
    emptyActionLabel: session.can('services.create') ? 'Add Service' : null,
    onEmptyAction: () => navigate('/services/new'),
  });

  table.mount(outlet);
}

// =================================================================
// FORM
// =================================================================
export async function serviceFormPage(outlet, args) {
  const id = args.id ? Number(args.id) : null;
  const isEdit = Boolean(id);

  let record = null;
  if (isEdit) {
    try {
      record = (await api.get(`/services/${id}`)).data;
    } catch (error) {
      notify.error(error.message);
      navigate('/services');
      return;
    }
  }

  const options = (await api.get('/services/form-options')).data;
  const categories = options.categories.map((category) => ({
    value: category.id,
    label: category.name + (category.status === 'inactive' ? ' (inactive)' : ''),
  }));

  outlet.appendChild(pageHead({
    title: isEdit ? `Edit ${record.name}` : 'Add a service',
    description: isEdit
      ? 'Changes appear on the website as soon as you save, provided the service is active.'
      : 'New services are saved as active by default and appear on the website immediately.',
  }));

  const { form, body } = formShell({
    backTo: '/services',
    saveLabel: isEdit ? 'Save changes' : 'Create service',
  });

  // --- Basic information -----------------------------------------
  body.appendChild(fill(
    section('Basic information', 'What guests see first on the service card.'),
    field({
      name: 'name', label: 'Service name', required: true, span: 8,
      value: record?.name, placeholder: 'e.g. Hot Stone Massage',
    }),
    select({
      name: 'category_id', label: 'Category', required: true, span: 4,
      value: record?.category_id, options: categories,
      placeholder: 'Choose a category',
    }),
    field({
      name: 'slug', label: 'URL slug', span: 6, value: record?.slug,
      hint: 'Leave blank to generate one from the name.',
    }),
    select({
      name: 'icon_key', label: 'Card icon', span: 6, value: record?.icon_key,
      options: options.icons.map((i) => ({ value: i.key, label: i.label })),
      placeholder: 'No icon',
    }),
    textarea({
      name: 'short_description', label: 'Short description', rows: 2, span: 12,
      value: record?.short_description,
      hint: 'One line, shown under the service name in listings. Max 500 characters.',
    }),
    textarea({
      name: 'description', label: 'Full description', rows: 6, span: 12,
      value: record?.description,
      hint: 'The full copy shown when a guest expands the service card.',
    }),
  ));

  // --- Pricing ----------------------------------------------------
  body.appendChild(fill(
    section('Pricing & duration', 'The number is used for sorting and reporting; the display text is what guests read.'),
    field({
      name: 'price', label: 'Price', type: 'number', step: '0.01', min: 0,
      required: true, span: 3, value: record?.price, dataType: 'number', prefix: '$',
    }),
    field({
      name: 'price_display', label: 'Price display text', span: 3,
      value: record?.price_display, placeholder: 'from $150',
      hint: 'Optional. Overrides the price shown on the site.',
    }),
    field({
      name: 'promo_price', label: 'Promotional price', type: 'number', step: '0.01', min: 0,
      span: 3, value: record?.promo_price, dataType: 'number', prefix: '$',
      hint: 'Must be lower than the price.',
    }),
    field({
      name: 'duration_minutes', label: 'Duration (minutes)', type: 'number', min: 0, max: 1440,
      span: 3, value: record?.duration_minutes, dataType: 'number',
    }),
    field({
      name: 'duration_display', label: 'Duration display text', span: 6,
      value: record?.duration_display, placeholder: '1 hr & 40 mins',
      hint: 'Optional. Overrides the duration shown on the site.',
    }),
    field({
      name: 'booking_url', label: 'Booking link', type: 'url', span: 6,
      value: record?.booking_url,
      placeholder: 'https://go.booker.com/location/yourmajestyspa/…',
      hint: 'Where the "Book Now" button sends guests.',
    }),
  ));

  // --- Media ------------------------------------------------------
  body.appendChild(fill(
    section('Image', 'Landscape images at roughly 4:3 look best on the service cards.'),
    mediaField({
      name: 'media_id',
      label: 'Service image',
      value: record?.media_id,
      imageUrl: record?.image_url,
      altText: record?.image_alt,
      span: 12,
    }),
    field({
      name: 'image_alt', label: 'Image alt text', span: 12,
      value: record?.image_alt,
      hint: 'Describes the image for screen readers and search engines.',
    }),
  ));

  // --- Publishing -------------------------------------------------
  const mostLovedOptions = [
    { value: '1', label: '#1 — Most booked' },
    { value: '2', label: '#2' },
    { value: '3', label: '#3' },
  ];

  body.appendChild(fill(
    section('Publishing', 'Controls whether and where this service appears on the website.'),
    select({
      name: 'status', label: 'Status', span: 4,
      value: record?.status || 'active',
      placeholder: '',
      options: [
        { value: 'active', label: 'Active — visible on the website' },
        { value: 'inactive', label: 'Inactive — hidden from the website' },
      ],
    }),
    field({
      name: 'display_order', label: 'Display order', type: 'number', min: 0, span: 4,
      value: record?.display_order ?? 0, dataType: 'number',
      hint: 'Lower numbers appear first.',
    }),
    select({
      name: 'most_loved_rank', label: 'Most Loved rank', span: 4,
      value: record?.most_loved_rank,
      placeholder: 'Not ranked',
      options: mostLovedOptions,
      hint: 'Assigning a rank removes it from whichever service holds it now.',
    }),
    switchField({
      name: 'featured', label: 'Featured service',
      hint: 'Highlighted in featured placements.',
      checked: Boolean(record?.featured), span: 6,
    }),
  ));

  bindSlugPreview(form, 'name', 'slug');

  bindFormSubmit({
    form,
    base: '/services',
    id,
    redirectTo: '/services',
    successMessage: isEdit ? 'Service updated.' : 'Service created.',
    transform: (payload) => {
      // image_alt belongs to the media row, not the service; strip it so the
      // server does not reject an unknown column.
      delete payload.image_alt;

      if (payload.media_id === '' || payload.media_id === null) payload.media_id = null;
      else payload.media_id = Number(payload.media_id);

      if (payload.most_loved_rank) payload.most_loved_rank = Number(payload.most_loved_rank);
      if (payload.category_id) payload.category_id = Number(payload.category_id);

      return payload;
    },
  });

  outlet.appendChild(form);

  if (isEdit) {
    outlet.appendChild(metaCard(record));
  }
}

function metaCard(record) {
  return el(`
    <div class="card mt-3">
      <div class="card__body">
        <dl class="detail-list" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem">
          <div><dt>Created</dt><dd>${esc(dateLabel(record.created_at))}</dd></div>
          <div><dt>Last updated</dt><dd>${esc(dateLabel(record.updated_at))}</dd></div>
          <div><dt>Public URL slug</dt><dd><code>${esc(record.slug)}</code></dd></div>
          <div><dt>Gallery images</dt><dd>${(record.images || []).length}</dd></div>
        </dl>
      </div>
    </div>
  `);
}
