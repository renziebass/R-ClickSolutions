/** Gift cards and memberships — one entity, discriminated by type. */

import { api } from '../api.js';
import { navigate } from '../router.js';
import { session } from '../session.js';
import { esc } from '../ui/dom.js';
import { DataTable } from '../ui/table.js';
import {
  bindSlugPreview, field, fill, readRichText, richText, section, select, switchField, textarea,
} from '../ui/form.js';
import { mediaField } from '../ui/media-picker.js';
import { notify } from '../ui/feedback.js';
import {
  bindFormSubmit, crudActions, featuredStar, formShell, pageHead, statusPill, thumbCell,
} from './helpers.js';

export async function giftCardsPage(outlet) {
  outlet.appendChild(pageHead({
    title: 'Gift cards & memberships',
    description: 'Prepaid offerings guests can buy. Memberships bill monthly or yearly; '
      + 'gift cards are a one-time purchase.',
    actions: session.can('gift_cards.create')
      ? [{ label: 'Add Offering', iconName: 'i-plus', onClick: () => navigate('/gift-cards/new') }]
      : [],
  }));

  const table = new DataTable({
    searchPlaceholder: 'Search gift cards and memberships…',
    defaultSort: { sort: 'display_order', direction: 'ASC' },
    fetch: (query) => api.list('/gift-cards', query),

    filters: [
      {
        name: 'type', label: 'Type',
        options: [
          { value: 'gift_card', label: 'Gift cards' },
          { value: 'membership', label: 'Memberships' },
        ],
      },
      {
        name: 'status', label: 'Status',
        options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }],
      },
    ],

    columns: [
      { key: 'image', label: 'Image', width: '70px', render: (row) => thumbCell(row, 'title') },
      {
        key: 'title', label: 'Offering', sortable: true,
        render: (row) => `
          <span class="cell-title">${esc(row.title)}</span>
          <span class="cell-sub">${esc(row.badge_label || row.slug)}</span>
        `,
      },
      {
        key: 'type', label: 'Type', sortable: true,
        render: (row) => `<span class="pill pill--plain">${esc(row.type_label)}</span>`,
      },
      {
        key: 'price', label: 'Price', sortable: true, className: 'cell-num nowrap',
        render: (row) => esc(row.price_label || 'Guest chooses'),
      },
      { key: 'status', label: 'Status', sortable: true, render: (row) => statusPill(row.status) },
      { key: 'featured', label: 'Featured', render: (row) => featuredStar(row.featured) },
    ],

    rowActions: (row) => crudActions({
      row, base: '/gift-cards', permission: 'gift_cards', table,
      onEdit: (r) => navigate(`/gift-cards/${r.id}/edit`),
    }),

    emptyTitle: 'No gift cards or memberships yet',
    emptyMessage: 'Add a gift card offering, or a membership such as the Crown Society.',
    emptyActionLabel: session.can('gift_cards.create') ? 'Add Offering' : null,
    onEmptyAction: () => navigate('/gift-cards/new'),
  });

  table.mount(outlet);
}

export async function giftCardFormPage(outlet, args) {
  const id = args.id ? Number(args.id) : null;
  const isEdit = Boolean(id);

  let record = null;
  if (isEdit) {
    try {
      record = (await api.get(`/gift-cards/${id}`)).data;
    } catch (error) {
      notify.error(error.message);
      navigate('/gift-cards');
      return;
    }
  }

  outlet.appendChild(pageHead({
    title: isEdit ? `Edit ${record.title}` : 'Add a gift card or membership',
  }));

  const { form, body } = formShell({
    backTo: '/gift-cards',
    saveLabel: isEdit ? 'Save changes' : 'Create offering',
  });

  const detailsSection = fill(
    section('Details'),
    select({
      name: 'type', label: 'Type', span: 4, placeholder: '',
      value: record?.type || 'gift_card',
      options: [
        { value: 'gift_card', label: 'Gift card (one-time)' },
        { value: 'membership', label: 'Membership (recurring)' },
      ],
    }),
    field({
      name: 'title', label: 'Title', required: true, span: 8, value: record?.title,
      placeholder: 'e.g. Crown Society Membership',
    }),
    field({ name: 'slug', label: 'URL slug', span: 6, value: record?.slug }),
    field({
      name: 'badge_label', label: 'Badge', span: 6, value: record?.badge_label,
      placeholder: 'Members',
    }),
    richText({
      name: 'description', label: 'Description', span: 12, minHeight: '13rem',
      value: record?.description,
    }),
  );

  body.appendChild(detailsSection);

  const pricingSection = fill(
    section('Pricing', 'A membership must bill monthly or yearly; a gift card must be one-time.'),
    field({
      name: 'price', label: 'Price', type: 'number', step: '0.01', min: 0, span: 4,
      value: record?.price, dataType: 'number', prefix: '$',
      hint: 'Leave blank if the guest chooses the amount.',
    }),
    field({
      name: 'price_display', label: 'Price display text', span: 4,
      value: record?.price_display, placeholder: 'From $109 / mo',
    }),
    select({
      name: 'price_interval', label: 'Billing interval', span: 4, placeholder: '',
      value: record?.price_interval || 'one_time',
      options: [
        { value: 'one_time', label: 'One-time' },
        { value: 'monthly', label: 'Monthly' },
        { value: 'yearly', label: 'Yearly' },
      ],
    }),
    field({
      name: 'purchase_url', label: 'Purchase link', type: 'url', span: 12,
      value: record?.purchase_url,
      placeholder: 'https://go.booker.com/location/yourmajestyspa/buy/gift-certificate',
    }),
  );

  body.appendChild(pricingSection);

  // Keep type and interval consistent so the server-side rule is never hit by
  // accident — the server still enforces it regardless.
  const typeSelect = detailsSection.querySelector('[name="type"]');
  const intervalSelect = pricingSection.querySelector('[name="price_interval"]');

  typeSelect.addEventListener('change', () => {
    if (typeSelect.value === 'gift_card') {
      intervalSelect.value = 'one_time';
    } else if (intervalSelect.value === 'one_time') {
      intervalSelect.value = 'monthly';
    }
  });

  body.appendChild(fill(
    section('Image'),
    mediaField({
      name: 'media_id', label: 'Image', span: 12,
      value: record?.media_id, imageUrl: record?.image_url, altText: record?.image_alt,
    }),
  ));

  body.appendChild(fill(
    section('Publishing'),
    select({
      name: 'status', label: 'Status', span: 4, placeholder: '',
      value: record?.status || 'active',
      options: [
        { value: 'active', label: 'Active — visible on the website' },
        { value: 'inactive', label: 'Inactive — hidden' },
      ],
    }),
    field({
      name: 'display_order', label: 'Display order', type: 'number', min: 0, span: 4,
      value: record?.display_order ?? 0, dataType: 'number',
    }),
    switchField({
      name: 'featured', label: 'Featured', checked: Boolean(record?.featured), span: 4,
    }),
  ));

  bindSlugPreview(form, 'title', 'slug');

  bindFormSubmit({
    form, base: '/gift-cards', id, redirectTo: '/gift-cards',
    successMessage: isEdit ? 'Offering updated.' : 'Offering created.',
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
