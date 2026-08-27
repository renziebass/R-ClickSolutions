/** Specials: the packaged offers shown on the website's Specials section. */

import { api } from '../api.js';
import { navigate } from '../router.js';
import { session } from '../session.js';
import { dateLabel, esc } from '../ui/dom.js';
import { DataTable } from '../ui/table.js';
import {
  bindSlugPreview, field, fill, readRichText, richText, section, select, switchField, textarea,
} from '../ui/form.js';
import { mediaField } from '../ui/media-picker.js';
import { notify } from '../ui/feedback.js';
import {
  bindFormSubmit, crudActions, featuredStar, formShell, pageHead, schedulePill, thumbCell,
} from './helpers.js';

export async function specialsPage(outlet) {
  outlet.appendChild(pageHead({
    title: 'Specials',
    description: 'Packaged experiences sold at a set price — the cards under "Exclusive Spa Specials" '
      + 'on the website.',
    actions: session.can('specials.create')
      ? [{ label: 'Add Special', iconName: 'i-plus', onClick: () => navigate('/specials/new') }]
      : [],
  }));

  const table = new DataTable({
    searchPlaceholder: 'Search specials…',
    defaultSort: { sort: 'display_order', direction: 'ASC' },
    fetch: (query) => api.list('/specials', query),

    filters: [
      {
        name: 'state', label: 'State',
        options: [
          { value: 'active', label: 'Active now' },
          { value: 'scheduled', label: 'Upcoming' },
          { value: 'expired', label: 'Expired' },
          { value: 'draft', label: 'Draft' },
          { value: 'inactive', label: 'Archived' },
        ],
      },
      {
        name: 'featured', label: 'Featured',
        options: [{ value: '1', label: 'Featured only' }, { value: '0', label: 'Not featured' }],
      },
    ],

    columns: [
      { key: 'image', label: 'Image', width: '70px', render: (row) => thumbCell(row, 'title') },
      {
        key: 'title', label: 'Special', sortable: true,
        render: (row) => `
          <span class="cell-title">${esc(row.title)}</span>
          <span class="cell-sub">${esc(row.badge_label || row.slug)}</span>
        `,
      },
      {
        key: 'price', label: 'Price', sortable: true, className: 'cell-num nowrap',
        render: (row) => {
          const price = esc(row.price_label || '—');
          return row.compare_at_label
            ? `${price} <s class="muted">${esc(row.compare_at_label)}</s>`
            : price;
        },
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
      base: '/specials',
      permission: 'specials',
      table,
      statusValues: ['published', 'archived'],
      activateLabels: ['Unpublish', 'Publish'],
      onEdit: (r) => navigate(`/specials/${r.id}/edit`),
    }),

    emptyTitle: 'No specials yet',
    emptyMessage: 'Specials are packaged experiences at a set price, like the Majesty Summer Reset.',
    emptyActionLabel: session.can('specials.create') ? 'Add Special' : null,
    onEmptyAction: () => navigate('/specials/new'),
  });

  table.mount(outlet);
}

export async function specialFormPage(outlet, args) {
  const id = args.id ? Number(args.id) : null;
  const isEdit = Boolean(id);

  let record = null;
  if (isEdit) {
    try {
      record = (await api.get(`/specials/${id}`)).data;
    } catch (error) {
      notify.error(error.message);
      navigate('/specials');
      return;
    }
  }

  outlet.appendChild(pageHead({
    title: isEdit ? `Edit ${record.title}` : 'Add a special',
    description: 'Publish it and the start and end dates decide when it appears on the website.',
  }));

  const { form, body } = formShell({
    backTo: '/specials',
    saveLabel: isEdit ? 'Save changes' : 'Create special',
  });

  body.appendChild(fill(
    section('Basic information'),
    field({
      name: 'title', label: 'Special title', required: true, span: 8,
      value: record?.title, placeholder: 'e.g. Majesty Summer Reset',
    }),
    field({
      name: 'slug', label: 'URL slug', span: 4, value: record?.slug,
      hint: 'Leave blank to generate from the title.',
    }),
    field({
      name: 'badge_label', label: 'Badge text', span: 4, value: record?.badge_label,
      placeholder: 'Seasonal', hint: 'Small label above the title on the card.',
    }),
    field({
      name: 'booking_url', label: 'Booking link', type: 'url', span: 8,
      value: record?.booking_url,
    }),
    richText({
      name: 'description', label: 'Description', span: 12, minHeight: '13rem',
      value: record?.description,
      hint: 'What is included in the package. A bulleted list reads well here.',
    }),
  ));

  body.appendChild(fill(
    section('Pricing', 'The original price is shown struck through beside the special price.'),
    field({
      name: 'price', label: 'Special price', type: 'number', step: '0.01', min: 0, span: 4,
      value: record?.price, dataType: 'number', prefix: '$',
    }),
    field({
      name: 'compare_at_price', label: 'Original price', type: 'number', step: '0.01', min: 0,
      span: 4, value: record?.compare_at_price, dataType: 'number', prefix: '$',
      hint: 'Must be higher than the special price.',
    }),
    field({
      name: 'price_display', label: 'Price display text', span: 4,
      value: record?.price_display, placeholder: 'From $109 / mo',
      hint: 'Use instead of a number for recurring offers.',
    }),
  ));

  body.appendChild(fill(
    section('Image'),
    mediaField({
      name: 'media_id', label: 'Special image', span: 12,
      value: record?.media_id, imageUrl: record?.image_url, altText: record?.image_alt,
    }),
  ));

  body.appendChild(fill(
    section('Schedule & publishing'),
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
      name: 'featured', label: 'Featured special',
      checked: Boolean(record?.featured), span: 6,
    }),
  ));

  bindSlugPreview(form, 'title', 'slug');

  bindFormSubmit({
    form,
    base: '/specials',
    id,
    redirectTo: '/specials',
    successMessage: isEdit ? 'Special updated.' : 'Special created.',
    transform: (payload, formEl) => {
      // The editor is a contenteditable div with no name, so formValues()
      // never saw it.
      const richBody = readRichText(formEl, 'description');
      if (richBody !== null) payload.description = richBody;

      payload.media_id = payload.media_id ? Number(payload.media_id) : null;
      return payload;
    },
  });

  outlet.appendChild(form);
}
