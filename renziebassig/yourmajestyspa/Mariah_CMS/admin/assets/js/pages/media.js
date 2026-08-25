/** Media library: browse, upload, edit metadata, delete. */

import { api } from '../api.js';
import { session } from '../session.js';
import { dateLabel, debounce, el, esc, icon } from '../ui/dom.js';
import { confirmDialog, emptyState, errorState, modal, notify, withBusy } from '../ui/feedback.js';
import { buildDropzone } from '../ui/media-picker.js';
import { pageHead } from './helpers.js';

export async function mediaPage(outlet) {
  outlet.appendChild(pageHead({
    title: 'Media library',
    description: 'Images used across services, promotions, specials and the shop. '
      + 'An image still in use cannot be deleted.',
  }));

  const card = el(`
    <div class="card">
      <div class="card__body"></div>
    </div>
  `);

  const body = card.querySelector('.card__body');
  const grid = el('<div class="media-grid mt-3"></div>');

  if (session.can('media.upload')) {
    body.appendChild(buildDropzone(() => load(searchInput.value.trim())));
  }

  const search = el(`
    <div class="toolbar__search mt-3" style="max-width:420px">
      ${icon('i-search', 16)}
      <input type="search" placeholder="Search by file name or alt text…">
    </div>
  `);

  const searchInput = search.querySelector('input');
  searchInput.addEventListener('input', debounce(() => load(searchInput.value.trim()), 300));

  body.append(search, grid);
  outlet.appendChild(card);

  async function load(searchTerm = '') {
    grid.replaceChildren(...Array.from({ length: 8 }, () =>
      el('<div class="skel" style="aspect-ratio:4/3;height:auto"></div>')));

    try {
      const { data } = await api.list('/media', { search: searchTerm, per_page: 60 });

      if (!data.length) {
        grid.replaceChildren(emptyState({
          title: searchTerm ? 'No matching images' : 'No images yet',
          message: searchTerm
            ? 'Try a different search term.'
            : 'Upload an image above and it becomes available to every content form.',
          iconName: 'i-image',
        }));
        return;
      }

      grid.replaceChildren(...data.map((media) => tile(media, () => load(searchInput.value.trim()))));
    } catch (error) {
      grid.replaceChildren(errorState(error.message, () => load(searchTerm)));
    }
  }

  load();
}

function tile(media, refresh) {
  const node = el(`
    <div class="media-tile" style="cursor:default">
      <img class="media-tile__img" src="${esc(media.file_url)}"
           alt="${esc(media.alt_text || media.original_name)}" loading="lazy">
      <div class="media-tile__meta">
        <b>${esc(media.original_name)}</b>
        <small>${esc(media.size_label)} · ${media.width || '?'}×${media.height || '?'}</small>
        <small>${media.usage_count > 0
          ? `Used by ${media.usage_count} item${media.usage_count === 1 ? '' : 's'}`
          : '<span class="muted">Not in use</span>'}</small>
        <div class="row-actions mt-1" style="justify-content:flex-start"></div>
      </div>
    </div>
  `);

  const actions = node.querySelector('.row-actions');

  const addAction = (label, iconName, handler, danger = false) => {
    const button = el(`
      <button class="${danger ? 'is-danger' : ''}" title="${esc(label)}"
        aria-label="${esc(label)}">${icon(iconName, 15)}</button>
    `);
    button.addEventListener('click', () => handler(button));
    actions.appendChild(button);
  };

  addAction('View full size', 'i-eye', () => window.open(media.file_url, '_blank', 'noopener'));

  if (session.can('media.view')) {
    addAction('Where is this used?', 'i-link', async (button) => {
      await withBusy(button, async () => {
        try {
          const { data } = await api.get(`/media/${media.id}/usage`);
          showUsage(media, data);
        } catch (error) {
          notify.error(error.message);
        }
      });
    });
  }

  if (session.can('media.edit')) {
    addAction('Edit details', 'i-edit', () => editMetadata(media, refresh));
  }

  if (session.can('media.delete')) {
    addAction('Delete', 'i-trash', async (button) => {
      const ok = await confirmDialog({
        title: `Delete "${media.original_name}"?`,
        message: media.usage_count > 0
          ? `This image is used by ${media.usage_count} item(s). Remove it from those items first — the delete will be rejected otherwise.`
          : 'The file is removed from the server permanently. This cannot be undone.',
        confirmLabel: 'Delete image',
      });
      if (!ok) return;

      await withBusy(button, async () => {
        try {
          await api.del(`/media/${media.id}`);
          notify.ok('Image deleted.');
          refresh();
        } catch (error) {
          notify.error(error.message);
        }
      });
    }, true);
  }

  return node;
}

function editMetadata(media, refresh) {
  modal({
    title: 'Image details',
    subtitle: media.original_name,
    render: (body) => {
      body.innerHTML = `
        <img src="${esc(media.file_url)}" alt=""
             style="width:100%;max-height:260px;object-fit:contain;background:var(--muted-bg);border-radius:var(--r)">
        <form id="media-form" class="grid mt-3" novalidate>
          <div class="field col-12" data-field="alt_text">
            <label for="m-alt">Alt text</label>
            <input type="text" id="m-alt" name="alt_text" value="${esc(media.alt_text || '')}">
            <small class="field__hint">Describes the image for screen readers and search engines.</small>
          </div>
          <div class="field col-12" data-field="title">
            <label for="m-title">Title</label>
            <input type="text" id="m-title" name="title" value="${esc(media.title || '')}">
          </div>
        </form>
        <dl class="detail-list mt-3">
          <dt>Uploaded</dt>
          <dd>${esc(dateLabel(media.created_at))}${
            media.uploaded_by_name ? ' by ' + esc(media.uploaded_by_name) : ''
          }</dd>
          <dt>File</dt>
          <dd>${esc(media.mime_type)} · ${esc(media.size_label)} · ${media.width || '?'}×${media.height || '?'}</dd>
        </dl>
      `;
    },
    footer: (foot, close) => {
      const cancel = el('<button class="btn btn--ghost">Cancel</button>');
      const save = el('<button class="btn">Save details</button>');

      cancel.addEventListener('click', () => close(false));

      save.addEventListener('click', async () => {
        const form = foot.parentElement.querySelector('#media-form');

        await withBusy(save, async () => {
          try {
            await api.put(`/media/${media.id}`, {
              alt_text: form.querySelector('[name="alt_text"]').value.trim() || null,
              title: form.querySelector('[name="title"]').value.trim() || null,
            });
            notify.ok('Image details saved.');
            close(true);
            refresh();
          } catch (error) {
            notify.error(error.message);
          }
        });
      });

      foot.append(cancel, save);
    },
  });
}

function showUsage(media, usage) {
  modal({
    title: 'Where this image is used',
    subtitle: media.original_name,
    render: (body) => {
      if (!usage.count) {
        body.innerHTML = '<p class="muted" style="margin:0">This image is not attached to anything. '
          + 'It can be deleted safely.</p>';
        return;
      }

      body.innerHTML = `
        <ul class="list-plain">
          ${usage.items.map((item) => `
            <li>
              <span>${esc(item.title)}</span>
              <span class="pill pill--plain">${esc(item.type)}</span>
            </li>
          `).join('')}
        </ul>
      `;
    },
    footer: (foot, close) => {
      const done = el('<button class="btn">Close</button>');
      done.addEventListener('click', () => close(true));
      foot.appendChild(done);
    },
  });
}
