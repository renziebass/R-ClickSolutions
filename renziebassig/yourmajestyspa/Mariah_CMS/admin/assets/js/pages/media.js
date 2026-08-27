/**
 * Media library: browse by folder, upload, edit metadata, move, delete.
 *
 * Photos are uploaded into Unsorted and file themselves into a module's folder
 * the first time a service, category, promotion, special, blog post, brand,
 * gift certificate or product uses them. "Move to folder" is the override for
 * when that lands somewhere unhelpful, and "Reorganise files" catches up a
 * library that was uploaded before folders existed.
 */

import { api } from '../api.js';
import { session } from '../session.js';
import { dateLabel, debounce, el, esc, icon } from '../ui/dom.js';
import { confirmDialog, emptyState, errorState, modal, notify, withBusy } from '../ui/feedback.js';
import { buildDropzone, folderChips, invalidateFolders, mediaFolders } from '../ui/media-picker.js';
import { pageHead } from './helpers.js';

export async function mediaPage(outlet) {
  let activeFolder = '';

  outlet.appendChild(pageHead({
    title: 'Media library',
    description: 'Photos are uploaded into Unsorted, then filed into a folder automatically '
      + 'the first time a service, category, promotion, special, blog post, brand or gift '
      + 'certificate uses them. An image still in use cannot be deleted.',
    actions: [
      session.can('media.edit') && {
        label: 'Reorganise files',
        iconName: 'i-folder',
        variant: 'ghost',
        onClick: (button) => reorganize(button, refresh),
      },
    ],
  }));

  const card = el(`
    <div class="card">
      <div class="card__body"></div>
    </div>
  `);

  const body = card.querySelector('.card__body');
  const grid = el('<div class="media-grid mt-3"></div>');

  if (session.can('media.upload')) {
    // No folder: a library upload is a photo with no home yet, which is the
    // whole point of Unsorted.
    body.appendChild(buildDropzone(() => refresh()));
  }

  const chipHolder = el('<div class="mt-3"></div>');

  const search = el(`
    <div class="toolbar__search mt-3" style="max-width:420px">
      ${icon('i-search', 16)}
      <input type="search" placeholder="Search by file name or alt text…">
    </div>
  `);

  const searchInput = search.querySelector('input');
  searchInput.addEventListener('input', debounce(() => load(), 300));

  body.append(chipHolder, search, grid);
  outlet.appendChild(card);

  /** Reloads the grid and the folder counts, which a move or upload changes. */
  function refresh() {
    invalidateFolders();
    load();
    renderChips();
  }

  async function renderChips() {
    const folders = await mediaFolders();
    chipHolder.replaceChildren(
      ...(folders.length
        ? [folderChips(folders, activeFolder, (slug) => {
            activeFolder = slug;
            load();
          })]
        : [])
    );
  }

  async function load() {
    const searchTerm = searchInput.value.trim();

    grid.replaceChildren(...Array.from({ length: 8 }, () =>
      el('<div class="skel" style="aspect-ratio:4/3;height:auto"></div>')));

    try {
      const { data } = await api.list('/media', {
        search: searchTerm,
        folder: activeFolder,
        per_page: 60,
      });

      if (!data.length) {
        grid.replaceChildren(emptyState({
          title: searchTerm ? 'No matching images' : 'No images here',
          message: searchTerm
            ? 'Try a different search term, or another folder.'
            : activeFolder
              ? 'Nothing has been filed into this folder yet.'
              : 'Upload an image above and it becomes available to every content form.',
          iconName: 'i-image',
        }));
        return;
      }

      grid.replaceChildren(...data.map((media) => tile(media, refresh)));
    } catch (error) {
      grid.replaceChildren(errorState(error.message, () => load()));
    }
  }

  load();
  renderChips();
}

/**
 * Walks every file into the folder its record names — the catch-up for photos
 * uploaded before folders existed. Idempotent, so re-running is harmless.
 */
async function reorganize(button, refresh) {
  const ok = await confirmDialog({
    title: 'Reorganise files into folders?',
    message: 'Every photo is moved on the server into the folder shown here. '
      + 'Image addresses change, but every page reads them from the library, so nothing breaks. '
      + 'Photos already in the right folder are left alone.',
    confirmLabel: 'Reorganise',
  });
  if (!ok) return;

  await withBusy(button, async () => {
    try {
      const { data } = await api.post('/media/reorganize');
      notify.ok(data.message);
      refresh();
    } catch (error) {
      notify.error(error.message);
    }
  });
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
        <div class="mt-1">
          <span class="pill pill--plain">${esc(media.folder_label || 'Unsorted')}</span>
        </div>
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
    addAction('Move to folder', 'i-folder', () => moveToFolder(media, refresh));
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

/**
 * The manual override on first-use-wins, for the photo that filed itself into
 * a folder that turned out to be the wrong one.
 */
function moveToFolder(media, refresh) {
  modal({
    title: 'Move to folder',
    subtitle: media.original_name,
    render: async (body) => {
      body.innerHTML = '<div class="skel skel--lg"></div>';

      const folders = await mediaFolders();

      body.replaceChildren(el(`
        <form id="folder-form" class="grid" novalidate>
          <div class="field col-12" data-field="folder">
            <label for="m-folder">Folder</label>
            <select id="m-folder" name="folder">
              ${folders.map((folder) => `
                <option value="${esc(folder.slug)}"${folder.slug === media.folder ? ' selected' : ''}>
                  ${esc(folder.label)}
                </option>
              `).join('')}
            </select>
            <small class="field__hint">
              The file moves on the server. Everything already using this image
              keeps working — the new address is picked up automatically.
            </small>
          </div>
        </form>
      `));
    },
    footer: (foot, close) => {
      const cancel = el('<button class="btn btn--ghost">Cancel</button>');
      const save = el('<button class="btn">Move image</button>');

      cancel.addEventListener('click', () => close(false));

      save.addEventListener('click', async () => {
        const select = foot.parentElement.querySelector('[name="folder"]');
        if (!select) return;

        if (select.value === media.folder) {
          close(false);
          return;
        }

        await withBusy(save, async () => {
          try {
            await api.put(`/media/${media.id}`, { folder: select.value });
            notify.ok('Image moved.');
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
