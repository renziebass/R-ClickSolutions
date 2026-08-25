/**
 * Shop: products, product categories and brands.
 *
 * These three share the same shape, so the list pages are built from one
 * factory and only the forms differ.
 */

import { api } from '../api.js';
import { navigate } from '../router.js';
import { session } from '../session.js';
import { esc, relativeTime } from '../ui/dom.js';
import { DataTable } from '../ui/table.js';
import { bindSlugPreview, field, fill, section, select, switchField, textarea } from '../ui/form.js';
import { mediaField } from '../ui/media-picker.js';
import { notify } from '../ui/feedback.js';
import {
  bindFormSubmit, clearOptionCache, crudActions, featuredStar,
  formShell, loadOptions, pageHead, statusPill, thumbCell,
} from './helpers.js';

// =================================================================
// PRODUCTS
// =================================================================
export async function productsPage(outlet) {
  outlet.appendChild(pageHead({
    title: 'Shop products',
    description: 'Retail skincare shown in the website shop and added to the guest cart.',
    actions: session.can('products.create')
      ? [{ label: 'Add Product', iconName: 'i-plus', onClick: () => navigate('/products/new') }]
      : [],
  }));

  const table = new DataTable({
    searchPlaceholder: 'Search products by name or brand…',
    defaultSort: { sort: 'display_order', direction: 'ASC' },
    fetch: (query) => api.list('/products', query),

    filters: [
      {
        name: 'brand_id', label: 'Brand', options: [],
        load: async () => (await loadOptions('/brands/options'))
          .map((o) => ({ value: o.value, label: o.label })),
      },
      {
        name: 'category_id', label: 'Type', options: [],
        load: async () => (await loadOptions('/product-categories/options'))
          .map((o) => ({ value: o.value, label: o.label })),
      },
      {
        name: 'status', label: 'Status',
        options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }],
      },
    ],

    columns: [
      { key: 'image', label: 'Image', width: '70px', render: (row) => thumbCell(row) },
      {
        key: 'name', label: 'Product', sortable: true,
        render: (row) => `
          <span class="cell-title">${esc(row.name)}</span>
          <span class="cell-sub">${esc(row.brand_name || 'No brand')}${
            row.badge_label ? ` · ${esc(row.badge_label)}` : ''
          }</span>
        `,
      },
      { key: 'category', label: 'Type', sortable: true, render: (row) => esc(row.category_name || '—') },
      {
        key: 'price', label: 'Price', sortable: true, className: 'cell-num nowrap',
        render: (row) => esc(row.price_label || '—'),
      },
      { key: 'status', label: 'Status', sortable: true, render: (row) => statusPill(row.status) },
      { key: 'featured', label: 'Featured', sortable: true, render: (row) => featuredStar(row.featured) },
      {
        key: 'updated_at', label: 'Updated', sortable: true, className: 'nowrap',
        render: (row) => `<span class="muted">${esc(relativeTime(row.updated_at))}</span>`,
      },
    ],

    rowActions: (row) => crudActions({
      row, base: '/products', permission: 'products', table,
      onEdit: (r) => navigate(`/products/${r.id}/edit`),
    }),

    emptyTitle: 'No products yet',
    emptyMessage: 'Add the retail products your estheticians recommend.',
    emptyActionLabel: session.can('products.create') ? 'Add Product' : null,
    onEmptyAction: () => navigate('/products/new'),
  });

  table.mount(outlet);
}

export async function productFormPage(outlet, args) {
  const id = args.id ? Number(args.id) : null;
  const isEdit = Boolean(id);

  let record = null;
  if (isEdit) {
    try {
      record = (await api.get(`/products/${id}`)).data;
    } catch (error) {
      notify.error(error.message);
      navigate('/products');
      return;
    }
  }

  const options = (await api.get('/products/form-options')).data;

  outlet.appendChild(pageHead({
    title: isEdit ? `Edit ${record.name}` : 'Add a product',
    description: 'Products with no image fall back to the icon you choose below.',
  }));

  const { form, body } = formShell({
    backTo: '/products',
    saveLabel: isEdit ? 'Save changes' : 'Create product',
  });

  body.appendChild(fill(
    section('Product details'),
    field({
      name: 'name', label: 'Product name', required: true, span: 8,
      value: record?.name, placeholder: 'e.g. Glycolic & Retinol Pads',
    }),
    field({ name: 'slug', label: 'URL slug', span: 4, value: record?.slug }),
    select({
      name: 'brand_id', label: 'Brand', span: 4, value: record?.brand_id,
      options: options.brands.map((b) => ({ value: b.id, label: b.name })),
      placeholder: 'No brand',
    }),
    select({
      name: 'category_id', label: 'Product type', span: 4, value: record?.category_id,
      options: options.categories.map((c) => ({ value: c.id, label: c.name })),
      placeholder: 'No type',
    }),
    field({
      name: 'badge_label', label: 'Badge', span: 4, value: record?.badge_label,
      placeholder: 'Best seller', hint: 'Shown as a corner label on the card.',
    }),
    textarea({
      name: 'description', label: 'Description', rows: 4, span: 12, value: record?.description,
    }),
  ));

  body.appendChild(fill(
    section('Pricing'),
    field({
      name: 'price', label: 'Price', type: 'number', step: '0.01', min: 0, required: true,
      span: 4, value: record?.price, dataType: 'number', prefix: '$',
    }),
    field({
      name: 'compare_at_price', label: 'Compare-at price', type: 'number', step: '0.01', min: 0,
      span: 4, value: record?.compare_at_price, dataType: 'number', prefix: '$',
      hint: 'Must be higher than the price.',
    }),
  ));

  body.appendChild(fill(
    section('Image'),
    mediaField({
      name: 'media_id', label: 'Product photo', span: 8,
      value: record?.media_id, imageUrl: record?.image_url,
    }),
    select({
      name: 'icon_key', label: 'Fallback icon', span: 4, value: record?.icon_key,
      options: options.icons.map((i) => ({ value: i.key, label: i.label })),
      placeholder: 'No icon',
      hint: 'Used when no photo is set.',
    }),
  ));

  body.appendChild(fill(
    section('Publishing'),
    select({
      name: 'status', label: 'Status', span: 4, placeholder: '',
      value: record?.status || 'active',
      options: [
        { value: 'active', label: 'Active — visible in the shop' },
        { value: 'inactive', label: 'Inactive — hidden' },
      ],
    }),
    field({
      name: 'display_order', label: 'Display order', type: 'number', min: 0, span: 4,
      value: record?.display_order ?? 0, dataType: 'number',
    }),
    switchField({
      name: 'featured', label: 'Esthetician favourite',
      checked: Boolean(record?.featured), span: 4,
    }),
  ));

  bindSlugPreview(form, 'name', 'slug');

  bindFormSubmit({
    form, base: '/products', id, redirectTo: '/products',
    successMessage: isEdit ? 'Product updated.' : 'Product created.',
    transform: (payload) => {
      payload.media_id = payload.media_id ? Number(payload.media_id) : null;
      payload.brand_id = payload.brand_id ? Number(payload.brand_id) : null;
      payload.category_id = payload.category_id ? Number(payload.category_id) : null;
      return payload;
    },
  });

  outlet.appendChild(form);
}

// =================================================================
// PRODUCT CATEGORIES
// =================================================================
export async function productCategoriesPage(outlet) {
  outlet.appendChild(pageHead({
    title: 'Product types',
    description: 'The chips guests use to browse the shop — Cleansers, Serums, SPF and so on.',
    actions: session.can('product_categories.create')
      ? [{ label: 'Add Type', iconName: 'i-plus', onClick: () => navigate('/product-categories/new') }]
      : [],
  }));

  const table = new DataTable({
    searchPlaceholder: 'Search product types…',
    defaultSort: { sort: 'display_order', direction: 'ASC' },
    fetch: (query) => api.list('/product-categories', query),

    filters: [{
      name: 'status', label: 'Status',
      options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }],
    }],

    columns: [
      {
        key: 'name', label: 'Type', sortable: true,
        render: (row) => `
          <span class="cell-title">${esc(row.name)}</span>
          <span class="cell-sub">${esc(row.slug)}</span>
        `,
      },
      { key: 'products_count', label: 'Products', className: 'cell-num', render: (row) => `${row.products_count}` },
      { key: 'status', label: 'Status', sortable: true, render: (row) => statusPill(row.status) },
      {
        key: 'display_order', label: 'Order', sortable: true, className: 'cell-num',
        render: (row) => `${row.display_order}`,
      },
    ],

    rowActions: (row) => crudActions({
      row, base: '/product-categories', permission: 'product_categories', table,
      canDuplicate: false,
      onEdit: (r) => navigate(`/product-categories/${r.id}/edit`),
    }),

    emptyTitle: 'No product types yet',
    emptyMessage: 'Product types let guests filter the shop.',
    emptyActionLabel: session.can('product_categories.create') ? 'Add Type' : null,
    onEmptyAction: () => navigate('/product-categories/new'),
  });

  table.mount(outlet);
}

export async function productCategoryFormPage(outlet, args) {
  const id = args.id ? Number(args.id) : null;
  const isEdit = Boolean(id);

  let record = null;
  if (isEdit) {
    try {
      record = (await api.get(`/product-categories/${id}`)).data;
    } catch (error) {
      notify.error(error.message);
      navigate('/product-categories');
      return;
    }
  }

  outlet.appendChild(pageHead({
    title: isEdit ? `Edit ${record.name}` : 'Add a product type',
  }));

  const { form, body } = formShell({
    backTo: '/product-categories',
    saveLabel: isEdit ? 'Save changes' : 'Create type',
  });

  body.appendChild(fill(
    section('Details'),
    field({ name: 'name', label: 'Type name', required: true, span: 8, value: record?.name }),
    field({ name: 'slug', label: 'URL slug', span: 4, value: record?.slug }),
    textarea({ name: 'description', label: 'Description', rows: 2, span: 12, value: record?.description }),
    select({
      name: 'status', label: 'Status', span: 6, placeholder: '',
      value: record?.status || 'active',
      options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }],
    }),
    field({
      name: 'display_order', label: 'Display order', type: 'number', min: 0, span: 6,
      value: record?.display_order ?? 0, dataType: 'number',
    }),
  ));

  bindSlugPreview(form, 'name', 'slug');

  bindFormSubmit({
    form, base: '/product-categories', id, redirectTo: '/product-categories',
    successMessage: isEdit ? 'Product type updated.' : 'Product type created.',
    transform: (payload) => { clearOptionCache(); return payload; },
  });

  outlet.appendChild(form);
}

// =================================================================
// BRANDS
// =================================================================
export async function brandsPage(outlet) {
  outlet.appendChild(pageHead({
    title: 'Brands',
    description: 'The skincare houses carried at Majesty — shown as brand cards in the shop.',
    actions: session.can('brands.create')
      ? [{ label: 'Add Brand', iconName: 'i-plus', onClick: () => navigate('/brands/new') }]
      : [],
  }));

  const table = new DataTable({
    searchPlaceholder: 'Search brands…',
    defaultSort: { sort: 'display_order', direction: 'ASC' },
    fetch: (query) => api.list('/brands', query),

    filters: [{
      name: 'status', label: 'Status',
      options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }],
    }],

    columns: [
      { key: 'image', label: 'Logo', width: '70px', render: (row) => thumbCell(row) },
      {
        key: 'name', label: 'Brand', sortable: true,
        render: (row) => `
          <span class="cell-title">${esc(row.name)}</span>
          <span class="cell-sub">${esc(row.tagline || row.slug)}</span>
        `,
      },
      { key: 'products_count', label: 'Products', className: 'cell-num', render: (row) => `${row.products_count}` },
      { key: 'status', label: 'Status', sortable: true, render: (row) => statusPill(row.status) },
    ],

    rowActions: (row) => crudActions({
      row, base: '/brands', permission: 'brands', table,
      canDuplicate: false,
      onEdit: (r) => navigate(`/brands/${r.id}/edit`),
    }),

    emptyTitle: 'No brands yet',
    emptyMessage: 'Add the skincare brands you retail, such as Skin Script and PCA SKIN.',
    emptyActionLabel: session.can('brands.create') ? 'Add Brand' : null,
    onEmptyAction: () => navigate('/brands/new'),
  });

  table.mount(outlet);
}

export async function brandFormPage(outlet, args) {
  const id = args.id ? Number(args.id) : null;
  const isEdit = Boolean(id);

  let record = null;
  if (isEdit) {
    try {
      record = (await api.get(`/brands/${id}`)).data;
    } catch (error) {
      notify.error(error.message);
      navigate('/brands');
      return;
    }
  }

  outlet.appendChild(pageHead({ title: isEdit ? `Edit ${record.name}` : 'Add a brand' }));

  const { form, body } = formShell({
    backTo: '/brands',
    saveLabel: isEdit ? 'Save changes' : 'Create brand',
  });

  body.appendChild(fill(
    section('Brand details'),
    field({ name: 'name', label: 'Brand name', required: true, span: 8, value: record?.name }),
    field({ name: 'slug', label: 'URL slug', span: 4, value: record?.slug }),
    field({
      name: 'tagline', label: 'Tagline', span: 12, value: record?.tagline,
      placeholder: 'Botanical, results-driven',
    }),
  ));

  body.appendChild(fill(
    section('Logo'),
    mediaField({
      name: 'media_id', label: 'Brand logo', span: 12,
      value: record?.media_id, imageUrl: record?.image_url,
    }),
  ));

  body.appendChild(fill(
    section('Publishing'),
    select({
      name: 'status', label: 'Status', span: 6, placeholder: '',
      value: record?.status || 'active',
      options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }],
    }),
    field({
      name: 'display_order', label: 'Display order', type: 'number', min: 0, span: 6,
      value: record?.display_order ?? 0, dataType: 'number',
    }),
  ));

  bindSlugPreview(form, 'name', 'slug');

  bindFormSubmit({
    form, base: '/brands', id, redirectTo: '/brands',
    successMessage: isEdit ? 'Brand updated.' : 'Brand created.',
    transform: (payload) => {
      payload.media_id = payload.media_id ? Number(payload.media_id) : null;
      clearOptionCache();
      return payload;
    },
  });

  outlet.appendChild(form);
}
