/** Service categories: list and form. These become the tabs on the website. */

import { api } from '../api.js';
import { navigate } from '../router.js';
import { session } from '../session.js';
import { esc, relativeTime } from '../ui/dom.js';
import { DataTable } from '../ui/table.js';
import { bindSlugPreview, field, fill, section, select, textarea } from '../ui/form.js';
import { mediaField } from '../ui/media-picker.js';
import { notify } from '../ui/feedback.js';
import {
  bindFormSubmit, crudActions, formShell, pageHead, statusPill, thumbCell, clearOptionCache,
} from './helpers.js';

export async function categoriesPage(outlet) {
  outlet.appendChild(pageHead({
    title: 'Service categories',
    description: 'Categories become the tabs above the service menu on the website. '
      + 'A category with no active services is hidden automatically.',
    actions: session.can('categories.create')
      ? [{ label: 'Add Category', iconName: 'i-plus', onClick: () => navigate('/categories/new') }]
      : [],
  }));

  const table = new DataTable({
    searchPlaceholder: 'Search categories…',
    defaultSort: { sort: 'display_order', direction: 'ASC' },
    fetch: (query) => api.list('/categories', query),

    filters: [{
      name: 'status',
      label: 'Status',
      options: [
        { value: 'active', label: 'Active' },
        { value: 'inactive', label: 'Inactive' },
      ],
    }],

    columns: [
      { key: 'image', label: 'Image', width: '70px', render: (row) => thumbCell(row) },
      {
        key: 'name', label: 'Category', sortable: true,
        render: (row) => `
          <span class="cell-title">${esc(row.name)}</span>
          <span class="cell-sub">${esc(row.description || row.slug)}</span>
        `,
      },
      {
        key: 'services_count', label: 'Services',
        className: 'cell-num',
        render: (row) => `${row.services_count}`,
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
      base: '/categories',
      permission: 'categories',
      table,
      canDuplicate: false,
      onEdit: (r) => navigate(`/categories/${r.id}/edit`),
    }),

    emptyTitle: 'No categories yet',
    emptyMessage: 'Categories group your treatments — Massage, Facials, Body Treatments and so on.',
    emptyActionLabel: session.can('categories.create') ? 'Add Category' : null,
    onEmptyAction: () => navigate('/categories/new'),
  });

  table.mount(outlet);
}

export async function categoryFormPage(outlet, args) {
  const id = args.id ? Number(args.id) : null;
  const isEdit = Boolean(id);

  let record = null;
  if (isEdit) {
    try {
      record = (await api.get(`/categories/${id}`)).data;
    } catch (error) {
      notify.error(error.message);
      navigate('/categories');
      return;
    }
  }

  outlet.appendChild(pageHead({
    title: isEdit ? `Edit ${record.name}` : 'Add a category',
    description: 'Categories organise the treatment menu. Deleting one requires moving its services first.',
  }));

  const { form, body } = formShell({
    backTo: '/categories',
    saveLabel: isEdit ? 'Save changes' : 'Create category',
  });

  body.appendChild(fill(
    section('Category details'),
    field({
      name: 'name', label: 'Category name', required: true, span: 8,
      value: record?.name, placeholder: 'e.g. Body Treatments',
    }),
    field({
      name: 'slug', label: 'URL slug', span: 4, value: record?.slug,
      hint: 'Leave blank to generate from the name.',
    }),
    textarea({
      name: 'description', label: 'Description', rows: 3, span: 12,
      value: record?.description,
      hint: 'Optional intro copy shown above the category on the website.',
    }),
    select({
      name: 'icon_key', label: 'Icon', span: 6, value: record?.icon_key,
      placeholder: 'No icon',
      options: [
        { value: 'i-hands', label: 'Hands (massage)' },
        { value: 'i-drop', label: 'Drop (facial)' },
        { value: 'i-leaf', label: 'Leaf (body / wellness)' },
        { value: 'i-stone', label: 'Stone' },
        { value: 'i-boat', label: 'Boat (waterfront)' },
        { value: 'i-crown', label: 'Crown (luxury)' },
        { value: 'i-spark', label: 'Sparkle' },
        { value: 'i-gift', label: 'Gift (packages)' },
      ],
    }),
  ));

  body.appendChild(fill(
    section('Image'),
    mediaField({
      name: 'media_id', label: 'Category image', span: 12,
      value: record?.media_id, imageUrl: record?.image_url,
    }),
  ));

  body.appendChild(fill(
    section('Publishing'),
    select({
      name: 'status', label: 'Status', span: 6, placeholder: '',
      value: record?.status || 'active',
      options: [
        { value: 'active', label: 'Active — visible on the website' },
        { value: 'inactive', label: 'Inactive — hidden from the website' },
      ],
    }),
    field({
      name: 'display_order', label: 'Display order', type: 'number', min: 0, span: 6,
      value: record?.display_order ?? 0, dataType: 'number',
      hint: 'Controls the tab order on the website. Lower appears first.',
    }),
  ));

  bindSlugPreview(form, 'name', 'slug');

  bindFormSubmit({
    form,
    base: '/categories',
    id,
    redirectTo: '/categories',
    successMessage: isEdit ? 'Category updated.' : 'Category created.',
    transform: (payload) => {
      payload.media_id = payload.media_id ? Number(payload.media_id) : null;
      clearOptionCache();   // the category select on other forms is now stale
      return payload;
    },
  });

  outlet.appendChild(form);
}
