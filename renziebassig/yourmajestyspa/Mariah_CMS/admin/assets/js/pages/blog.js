/** Blog: the articles and topics behind the website's "Journal" section. */

import { api } from '../api.js';
import { navigate } from '../router.js';
import { session } from '../session.js';
import { dateTimeLabel, esc } from '../ui/dom.js';
import { DataTable } from '../ui/table.js';
import {
  bindSlugPreview, field, fill, readRichText, richText, section, select, switchField, textarea,
} from '../ui/form.js';
import { mediaField } from '../ui/media-picker.js';
import { notify } from '../ui/feedback.js';
import {
  bindFormSubmit, crudActions, featuredStar, formShell, loadOptions,
  pageHead, schedulePill, statusPill, thumbCell,
} from './helpers.js';

// =================================================================
// Posts
// =================================================================

export async function blogPostsPage(outlet) {
  outlet.appendChild(pageHead({
    title: 'Blog posts',
    description: 'Articles for the Journal section of the website. A published post with a future '
      + 'date stays hidden until that date arrives.',
    actions: session.can('blog_posts.create')
      ? [{ label: 'Add Post', iconName: 'i-plus', onClick: () => navigate('/blog-posts/new') }]
      : [],
  }));

  // Topics are a filter here, so a missing list must not break the table.
  let topicOptions = [];
  try {
    topicOptions = await loadOptions('/blog-categories/options');
  } catch {
    topicOptions = [];
  }

  const table = new DataTable({
    searchPlaceholder: 'Search posts…',
    defaultSort: { sort: 'published_at', direction: 'DESC' },
    fetch: (query) => api.list('/blog-posts', query),

    filters: [
      {
        name: 'state', label: 'State',
        options: [
          { value: 'active', label: 'Live now' },
          { value: 'scheduled', label: 'Scheduled' },
          { value: 'draft', label: 'Draft' },
          { value: 'inactive', label: 'Archived' },
        ],
      },
      // Dropped entirely rather than shown empty when no topics exist yet.
      topicOptions.length ? {
        name: 'category_id', label: 'Topic',
        options: topicOptions.map((option) => ({
          value: option.value, label: option.label,
        })),
      } : null,
      {
        name: 'featured', label: 'Featured',
        options: [{ value: '1', label: 'Featured only' }, { value: '0', label: 'Not featured' }],
      },
    ].filter(Boolean),

    columns: [
      { key: 'image', label: 'Cover', width: '70px', render: (row) => thumbCell(row, 'title') },
      {
        key: 'title', label: 'Post', sortable: true,
        render: (row) => `
          <span class="cell-title">${esc(row.title)}</span>
          <span class="cell-sub">${esc(row.category_name || 'No topic')}</span>
        `,
      },
      {
        key: 'published_at', label: 'Publishes', sortable: true, className: 'nowrap',
        render: (row) => `
          <span>${esc(row.published_at ? dateTimeLabel(row.published_at) : 'Not dated')}</span>
          <span class="cell-sub">${esc(row.read_label || '—')}</span>
        `,
      },
      { key: 'status', label: 'State', sortable: true, render: (row) => schedulePill(row) },
      { key: 'featured', label: 'Featured', sortable: true, render: (row) => featuredStar(row.featured) },
    ],

    rowActions: (row) => crudActions({
      row,
      base: '/blog-posts',
      permission: 'blog_posts',
      table,
      statusValues: ['published', 'draft'],
      activateLabels: ['Unpublish', 'Publish'],
      onEdit: (r) => navigate(`/blog-posts/${r.id}/edit`),
    }),

    emptyTitle: 'No posts yet',
    emptyMessage: 'Write about treatments, seasonal rituals or skincare advice. '
      + 'Published posts appear in the Journal section of the website.',
    emptyActionLabel: session.can('blog_posts.create') ? 'Add Post' : null,
    onEmptyAction: () => navigate('/blog-posts/new'),
  });

  table.mount(outlet);
}

export async function blogPostFormPage(outlet, args) {
  const id = args.id ? Number(args.id) : null;
  const isEdit = Boolean(id);

  let record = null;
  if (isEdit) {
    try {
      record = (await api.get(`/blog-posts/${id}`)).data;
    } catch (error) {
      notify.error(error.message);
      navigate('/blog-posts');
      return;
    }
  }

  let topicOptions = [];
  try {
    topicOptions = await loadOptions('/blog-categories/options');
  } catch {
    topicOptions = [];
  }

  outlet.appendChild(pageHead({
    title: isEdit ? `Edit ${record.title}` : 'Write a post',
    description: 'Publish it, and the publish date decides when it appears on the website.',
  }));

  const { form, body } = formShell({
    backTo: '/blog-posts',
    saveLabel: isEdit ? 'Save changes' : 'Create post',
  });

  body.appendChild(fill(
    section('Article'),
    field({
      name: 'title', label: 'Post title', required: true, span: 8,
      value: record?.title, placeholder: 'e.g. How to Get the Most Out of Your First Massage',
    }),
    field({
      name: 'slug', label: 'URL slug', span: 4, value: record?.slug,
      hint: 'Leave blank to generate from the title.',
    }),
    select({
      name: 'category_id', label: 'Topic', span: 4, placeholder: 'No topic',
      value: record?.category_id ?? '',
      options: topicOptions,
      hint: 'Topics become the filter chips above the Journal.',
    }),
    field({
      name: 'author_name', label: 'Author', span: 4, value: record?.author_name,
      placeholder: 'Majesty Day Spa', hint: 'The byline shown on the post.',
    }),
    field({
      name: 'tags', label: 'Tags', span: 4, value: record?.tags,
      placeholder: 'facials, skincare', hint: 'Separate with commas.',
    }),
    textarea({
      name: 'excerpt', label: 'Excerpt', rows: 3, span: 12, value: record?.excerpt,
      placeholder: 'One or two sentences shown on the card.',
      hint: 'Leave blank and the opening of the post is used.',
    }),
    richText({
      name: 'content', label: 'Post content', span: 12, required: true,
      value: record?.content, minHeight: '24rem',
      hint: 'Format it however reads best. Reading time is worked out for you, '
        + 'and the excerpt is taken from the opening as plain text.',
    }),
  ));

  body.appendChild(fill(
    section('Cover image'),
    mediaField({
      name: 'media_id', label: 'Cover image', span: 12, folder: 'blog',
      value: record?.media_id, imageUrl: record?.image_url, altText: record?.image_alt,
    }),
  ));

  body.appendChild(fill(
    section('Search engine listing', 'How the post reads in Google results. Both are optional.'),
    field({
      name: 'meta_title', label: 'SEO title', span: 12, value: record?.meta_title,
      hint: 'Around 60 characters. Falls back to the post title.',
    }),
    textarea({
      name: 'meta_description', label: 'SEO description', rows: 2, span: 12,
      value: record?.meta_description,
      hint: 'Around 155 characters. Falls back to the excerpt.',
    }),
  ));

  body.appendChild(fill(
    section('Publishing'),
    select({
      name: 'status', label: 'Status', span: 4, placeholder: '',
      value: record?.status || 'draft',
      options: [
        { value: 'draft', label: 'Draft — not on the website' },
        { value: 'published', label: 'Published — governed by the date below' },
        { value: 'archived', label: 'Archived — permanently off the website' },
      ],
    }),
    field({
      name: 'published_at', label: 'Publish date & time', type: 'datetime-local', span: 4,
      value: toLocalInput(record?.published_at),
      hint: 'Leave blank to publish the moment you save.',
    }),
    field({
      name: 'read_minutes', label: 'Reading time (minutes)', type: 'number', min: 0, span: 4,
      value: record?.read_minutes ?? '', dataType: 'number',
      hint: 'Leave blank to calculate from the post.',
    }),
    field({
      name: 'display_order', label: 'Display order', type: 'number', min: 0, span: 4,
      value: record?.display_order ?? 0, dataType: 'number',
      hint: 'Tie-breaker for posts sharing a date.',
    }),
    switchField({
      name: 'featured', label: 'Featured post',
      checked: Boolean(record?.featured), span: 6,
    }),
  ));

  bindSlugPreview(form, 'title', 'slug');

  bindFormSubmit({
    form,
    base: '/blog-posts',
    id,
    redirectTo: '/blog-posts',
    successMessage: isEdit ? 'Post updated.' : 'Post created.',
    transform: (payload, formEl) => {
      // The editor is a contenteditable div with no name, so formValues()
      // never saw it.
      const richBody = readRichText(formEl, 'content');
      if (richBody !== null) payload.content = richBody;

      payload.media_id = payload.media_id ? Number(payload.media_id) : null;
      payload.category_id = payload.category_id ? Number(payload.category_id) : null;
      return payload;
    },
  });

  outlet.appendChild(form);
}

/**
 * "2026-08-25 14:30:00" -> "2026-08-25T14:30", the only shape a
 * datetime-local input accepts.
 */
function toLocalInput(value) {
  if (!value) return '';
  return String(value).replace(' ', 'T').slice(0, 16);
}

// =================================================================
// Topics
// =================================================================

export async function blogCategoriesPage(outlet) {
  outlet.appendChild(pageHead({
    title: 'Blog topics',
    description: 'Groups posts into the filter chips shown above the Journal. '
      + 'A topic with no live posts is hidden on the website.',
    actions: session.can('blog_categories.create')
      ? [{ label: 'Add Topic', iconName: 'i-plus', onClick: () => navigate('/blog-categories/new') }]
      : [],
  }));

  const table = new DataTable({
    searchPlaceholder: 'Search topics…',
    defaultSort: { sort: 'display_order', direction: 'ASC' },
    fetch: (query) => api.list('/blog-categories', query),

    filters: [
      {
        name: 'status', label: 'Status',
        options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }],
      },
    ],

    columns: [
      {
        key: 'name', label: 'Topic', sortable: true,
        render: (row) => `
          <span class="cell-title">${esc(row.name)}</span>
          <span class="cell-sub">${esc(row.slug)}</span>
        `,
      },
      {
        key: 'posts_count', label: 'Posts', className: 'cell-num',
        render: (row) => esc(row.posts_count),
      },
      { key: 'status', label: 'Status', sortable: true, render: (row) => statusPill(row.status) },
      {
        key: 'display_order', label: 'Order', sortable: true, className: 'cell-num',
        render: (row) => esc(row.display_order),
      },
    ],

    rowActions: (row) => crudActions({
      row,
      base: '/blog-categories',
      permission: 'blog_categories',
      table,
      canDuplicate: false,
      onEdit: (r) => navigate(`/blog-categories/${r.id}/edit`),
    }),

    emptyTitle: 'No topics yet',
    emptyMessage: 'Topics such as "Massage & Bodywork" or "Skin & Facials" let visitors filter the Journal.',
    emptyActionLabel: session.can('blog_categories.create') ? 'Add Topic' : null,
    onEmptyAction: () => navigate('/blog-categories/new'),
  });

  table.mount(outlet);
}

export async function blogCategoryFormPage(outlet, args) {
  const id = args.id ? Number(args.id) : null;
  const isEdit = Boolean(id);

  let record = null;
  if (isEdit) {
    try {
      record = (await api.get(`/blog-categories/${id}`)).data;
    } catch (error) {
      notify.error(error.message);
      navigate('/blog-categories');
      return;
    }
  }

  outlet.appendChild(pageHead({
    title: isEdit ? `Edit ${record.name}` : 'Add a blog topic',
    description: 'Posts are grouped under a topic so visitors can filter the Journal.',
  }));

  const { form, body } = formShell({
    backTo: '/blog-categories',
    saveLabel: isEdit ? 'Save changes' : 'Create topic',
  });

  body.appendChild(fill(
    section('Topic'),
    field({
      name: 'name', label: 'Topic name', required: true, span: 8,
      value: record?.name, placeholder: 'e.g. Skin & Facials',
    }),
    field({
      name: 'slug', label: 'URL slug', span: 4, value: record?.slug,
      hint: 'Leave blank to generate from the name.',
    }),
    textarea({
      name: 'description', label: 'Description', rows: 3, span: 12,
      value: record?.description,
      hint: 'Internal note — it is not printed on the website.',
    }),
    select({
      name: 'status', label: 'Status', span: 4, placeholder: '',
      value: record?.status || 'active',
      options: [
        { value: 'active', label: 'Active' },
        { value: 'inactive', label: 'Inactive — hidden on the website' },
      ],
    }),
    field({
      name: 'display_order', label: 'Display order', type: 'number', min: 0, span: 4,
      value: record?.display_order ?? 0, dataType: 'number',
    }),
  ));

  bindSlugPreview(form, 'name', 'slug');

  bindFormSubmit({
    form,
    base: '/blog-categories',
    id,
    redirectTo: '/blog-categories',
    successMessage: isEdit ? 'Topic updated.' : 'Topic created.',
  });

  outlet.appendChild(form);
}
