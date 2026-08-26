/**
 * Service add-ons: the enhancements a guest can attach to a treatment.
 *
 * An add-on belongs to one category and carries its own price, because the
 * price belongs to the menu it appears on — Aromatherapy is +$25 on the
 * massage menu and +$20 on the facial menu. Two records, one per category.
 */

import { api } from '../api.js';
import { navigate } from '../router.js';
import { session } from '../session.js';
import { esc, relativeTime } from '../ui/dom.js';
import { DataTable } from '../ui/table.js';
import { field, fill, section, select, textarea } from '../ui/form.js';
import { notify } from '../ui/feedback.js';
import {
  bindFormSubmit, crudActions, formShell, loadOptions, pageHead, statusPill,
} from './helpers.js';

export async function addonsPage(outlet) {
  outlet.appendChild(pageHead({
    title: 'Add-ons',
    description: 'Enhancements guests can add to a treatment. Each one belongs to a '
      + 'category and is offered alongside every service in it.',
    actions: session.can('services.create')
      ? [{ label: 'Add Add-on', iconName: 'i-plus', onClick: () => navigate('/addons/new') }]
      : [],
  }));

  const categories = await loadOptions('/categories/options');

  const table = new DataTable({
    searchPlaceholder: 'Search add-ons…',
    defaultSort: { sort: 'display_order', direction: 'ASC' },
    fetch: (query) => api.list('/addons', query),

    filters: [
      {
        name: 'category_id',
        label: 'Category',
        options: categories.map((c) => ({ value: c.value, label: c.label })),
      },
      {
        name: 'status',
        label: 'Status',
        options: [
          { value: 'active', label: 'Active' },
          { value: 'inactive', label: 'Inactive' },
        ],
      },
    ],

    columns: [
      {
        key: 'name', label: 'Add-on', sortable: true,
        render: (row) => `
          <span class="cell-title">${esc(row.name)}</span>
          <span class="cell-sub">${esc(row.description || '')}</span>
        `,
      },
      {
        key: 'category', label: 'Category', sortable: true,
        render: (row) => esc(row.category_name || '—'),
      },
      {
        key: 'price', label: 'Price', sortable: true, className: 'cell-num',
        render: (row) => esc(row.price_label),
      },
      {
        key: 'duration', label: 'Extra time', className: 'cell-num',
        render: (row) => (row.duration_minutes ? `+${row.duration_minutes} min` : '—'),
      },
      { key: 'status', label: 'Status', sortable: true, render: (row) => statusPill(row.status) },
      {
        key: 'display_order', label: 'Order', sortable: true, className: 'cell-num',
        render: (row) => `${row.display_order}`,
      },
      {
        key: 'updated_at', label: 'Updated', sortable: true, className: 'nowrap',
        render: (row) => `<span class="muted">${esc(relativeTime(row.updated_at))}</span>`,
      },
    ],

    rowActions: (row) => crudActions({
      row,
      base: '/addons',
      permission: 'services',
      table,
      canDuplicate: false,
      onEdit: (r) => navigate(`/addons/${r.id}/edit`),
    }),

    emptyTitle: 'No add-ons yet',
    emptyMessage: 'Add-ons are the extras on a treatment menu — CBD oil, a scalp '
      + 'treatment, LED light therapy.',
    emptyActionLabel: session.can('services.create') ? 'Add Add-on' : null,
    onEmptyAction: () => navigate('/addons/new'),
  });

  table.mount(outlet);
}

export async function addonFormPage(outlet, args) {
  const id = args.id ? Number(args.id) : null;
  const isEdit = Boolean(id);

  let record = null;
  if (isEdit) {
    try {
      record = (await api.get(`/addons/${id}`)).data;
    } catch (error) {
      notify.error(error.message);
      navigate('/addons');
      return;
    }
  }

  const categories = (await api.get('/categories/options')).data.map((row) => ({
    value: row.id,
    label: (row.depth ? '— ' : '') + row.name
      + (row.status === 'inactive' ? ' (inactive)' : ''),
  }));

  outlet.appendChild(pageHead({
    title: isEdit ? `Edit ${record.name}` : 'Add an add-on',
    description: 'Offered with every treatment in the category you choose.',
  }));

  const { form, body } = formShell({
    backTo: '/addons',
    saveLabel: isEdit ? 'Save changes' : 'Create add-on',
  });

  body.appendChild(fill(
    section('Add-on details'),
    field({
      name: 'name', label: 'Add-on name', required: true, span: 8,
      value: record?.name, placeholder: 'e.g. Scalp Treatment',
    }),
    select({
      name: 'category_id', label: 'Category', required: true, span: 4,
      value: record?.category_id, options: categories,
      placeholder: 'Choose a category',
      hint: 'The same add-on can exist under two categories at different prices.',
    }),
    textarea({
      name: 'description', label: 'Description', rows: 2, span: 12,
      value: record?.description,
      placeholder: 'Helps with spot treatment for pain, relaxation, aromatherapy',
      hint: 'Optional. Shown under the add-on name.',
    }),
    field({
      name: 'price', label: 'Additional price', type: 'number', step: '0.01', min: 0,
      required: true, span: 6, value: record?.price, dataType: 'number', prefix: '+$',
    }),
    field({
      name: 'duration_minutes', label: 'Extra time (minutes)', type: 'number', min: 0, max: 1440,
      span: 6, value: record?.duration_minutes, dataType: 'number',
      hint: 'Optional. How much longer the treatment runs.',
    }),
  ));

  body.appendChild(fill(
    section('Publishing'),
    select({
      name: 'status', label: 'Status', span: 6, placeholder: '',
      value: record?.status || 'active',
      options: [
        { value: 'active', label: 'Active — offered on the website' },
        { value: 'inactive', label: 'Inactive — hidden from the website' },
      ],
    }),
    field({
      name: 'display_order', label: 'Display order', type: 'number', min: 0, span: 6,
      value: record?.display_order ?? 0, dataType: 'number',
      hint: 'Lower appears first in the add-on list.',
    }),
  ));

  bindFormSubmit({
    form,
    base: '/addons',
    id,
    redirectTo: '/addons',
    successMessage: isEdit ? 'Add-on updated.' : 'Add-on created.',
    transform: (payload) => {
      if (payload.category_id) payload.category_id = Number(payload.category_id);
      return payload;
    },
  });

  outlet.appendChild(form);
}
