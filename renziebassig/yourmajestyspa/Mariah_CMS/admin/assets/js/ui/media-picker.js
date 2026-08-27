/**
 * Media picker field and library browser.
 *
 * Used by every content form that carries an image. Uploading goes straight to
 * POST /api/media, so a newly uploaded file is immediately available everywhere.
 *
 * Every field names the media library folder it belongs to. That folder is
 * sent with an upload, so a photo added from a service form lands in Services
 * rather than passing through Unsorted, and it seeds the library browser's
 * filter so the relevant photos are the ones on screen first.
 */

import { api } from '../api.js';
import { el, esc, icon } from './dom.js';
import { modal, notify } from './feedback.js';
import { session } from '../session.js';

const ACCEPT = 'image/jpeg,image/png,image/webp';

/**
 * The folder list is small, fixed in code on the server, and wanted by every
 * picker on the page — so it is fetched once per page load.
 */
let foldersPromise = null;

export function mediaFolders() {
  if (!foldersPromise) {
    foldersPromise = api.get('/media/folders')
      .then((result) => result.data || [])
      .catch(() => {
        // A failed fetch must not break the picker: without folders it simply
        // behaves as it did before they existed.
        foldersPromise = null;
        return [];
      });
  }
  return foldersPromise;
}

/** Discards the cached counts after an upload or a move changes them. */
export function invalidateFolders() {
  foldersPromise = null;
}

/**
 * The folder filter strip. `onPick(slug)` fires with '' for "All".
 * Counts are omitted when the caller has none to show.
 */
export function folderChips(folders, current, onPick, { showCounts = true } = {}) {
  const strip = el('<div class="folder-chips"></div>');

  const chips = [{ slug: '', label: 'All' }, ...folders].map((folder) => {
    const count = showCounts && folder.slug !== '' ? ` <span>${folder.count}</span>` : '';
    const chip = el(
      `<button type="button" class="folder-chip">${esc(folder.label)}${count}</button>`
    );

    chip.classList.toggle('is-active', folder.slug === current);
    chip.addEventListener('click', () => {
      strip.querySelectorAll('.folder-chip').forEach((c) => c.classList.remove('is-active'));
      chip.classList.add('is-active');
      onPick(folder.slug);
    });

    return chip;
  });

  strip.append(...chips);
  return strip;
}

/**
 * A form field showing the current image with Choose / Upload / Remove.
 * Exposes `.value` (media id) and a hidden input named `media_id`.
 */
export function mediaField({ name = 'media_id', label = 'Image', value = null, imageUrl = null, altText = '', span = 12, hint = '', folder = null }) {
  const node = el(`
    <div class="field col-${span}" data-field="${esc(name)}">
      <span class="field__label">${esc(label)}</span>
      <div class="media-pick">
        <div class="media-pick__preview">
          ${imageUrl
            ? `<img src="${esc(imageUrl)}" alt="${esc(altText)}">`
            : `<span>${icon('i-image', 24)}<br>No image selected</span>`}
        </div>
        <div class="media-pick__side">
          <input type="hidden" name="${esc(name)}" value="${esc(value ?? '')}">
          <button type="button" class="btn btn--ghost btn--sm" data-act="choose">
            ${icon('i-image', 15)} Choose from library
          </button>
          <button type="button" class="btn btn--ghost btn--sm" data-act="upload">
            ${icon('i-upload', 15)} Upload new image
          </button>
          <button type="button" class="btn btn--ghost btn--sm" data-act="clear"
            ${value ? '' : 'style="display:none"'}>Remove image</button>
          ${hint ? `<small class="field__hint">${esc(hint)}</small>` : ''}
        </div>
      </div>
    </div>
  `);

  const hidden = node.querySelector('input[type="hidden"]');
  const preview = node.querySelector('.media-pick__preview');
  const clearButton = node.querySelector('[data-act="clear"]');

  const apply = (media) => {
    if (!media) {
      hidden.value = '';
      preview.innerHTML = `<span>${icon('i-image', 24)}<br>No image selected</span>`;
      clearButton.style.display = 'none';
      return;
    }

    hidden.value = media.id;
    preview.innerHTML = `<img src="${esc(media.file_url)}" alt="${esc(media.alt_text || '')}">`;
    clearButton.style.display = '';
  };

  node.querySelector('[data-act="choose"]').addEventListener('click', async () => {
    const chosen = await openLibrary({ folder });
    if (chosen) apply(chosen);
  });

  node.querySelector('[data-act="upload"]').addEventListener('click', async () => {
    const uploaded = await promptUpload({ folder });
    if (uploaded) apply(uploaded);
  });

  clearButton.addEventListener('click', () => apply(null));

  return node;
}

/**
 * Opens the library in a modal; resolves with the chosen media row or null.
 *
 * `folder` filters the grid to that folder to begin with — the photos for the
 * form you are on — with "All" one click away. Uploads made from here inherit
 * it too.
 */
export function openLibrary({ folder = null } = {}) {
  return modal({
    title: 'Media library',
    subtitle: 'Choose an image, or upload a new one.',
    wide: true,
    render: (body, close) => {
      body.innerHTML = '<div class="skel skel--lg"></div>';

      const container = el('<div></div>');
      const dropzone = buildDropzone(async (media) => {
        close(media);
      }, { folder });

      const grid = el('<div class="media-grid mt-3"></div>');
      container.append(dropzone, grid);

      let selected = null;
      let activeFolder = folder || '';
      let searchTerm = '';

      const load = async (search = searchTerm) => {
        searchTerm = search;

        grid.innerHTML = Array.from({ length: 8 }, () =>
          '<div class="skel" style="aspect-ratio:4/3;height:auto"></div>').join('');

        try {
          const { data } = await api.list('/media', {
            search,
            folder: activeFolder,
            per_page: 60,
          });

          if (!data.length) {
            grid.replaceChildren(el(
              '<p class="muted" style="grid-column:1/-1;text-align:center;padding:2rem 0">' +
              (activeFolder || search
                ? 'No images here. Try another folder, or upload one above.'
                : 'No images yet. Upload one above.') +
              '</p>'
            ));
            return;
          }

          grid.replaceChildren(...data.map((media) => {
            const tile = el(`
              <button type="button" class="media-tile">
                <img class="media-tile__img" src="${esc(media.file_url)}"
                     alt="${esc(media.alt_text || media.original_name)}" loading="lazy">
                <div class="media-tile__meta">
                  <b>${esc(media.original_name)}</b>
                  <small>${esc(media.size_label)} · ${media.width || '?'}×${media.height || '?'}</small>
                </div>
              </button>
            `);

            tile.addEventListener('click', () => {
              grid.querySelectorAll('.media-tile').forEach((t) => t.classList.remove('is-selected'));
              tile.classList.add('is-selected');
              selected = media;
            });

            tile.addEventListener('dblclick', () => close(media));

            return tile;
          }));
        } catch (error) {
          grid.replaceChildren(el(`<p class="muted" style="grid-column:1/-1">${esc(error.message)}</p>`));
        }
      };

      const search = el(`
        <div class="toolbar__search mt-3" style="max-width:100%">
          ${icon('i-search', 16)}
          <input type="search" placeholder="Search images by name or alt text…">
        </div>
      `);

      let timer;
      search.querySelector('input').addEventListener('input', (event) => {
        clearTimeout(timer);
        timer = setTimeout(() => load(event.target.value.trim()), 300);
      });

      container.insertBefore(search, grid);
      body.replaceChildren(container);
      load();

      // Chips arrive after the grid so a slow folder fetch never delays the
      // images themselves.
      mediaFolders().then((folders) => {
        if (!folders.length) return;

        const chips = folderChips(folders, activeFolder, (slug) => {
          activeFolder = slug;
          load();
        });
        chips.classList.add('mt-3');

        container.insertBefore(chips, search);
      });

      body.dataset.ready = '1';
      body.__getSelected = () => selected;
    },
    footer: (foot, close) => {
      const cancel = el('<button class="btn btn--ghost">Cancel</button>');
      const use = el('<button class="btn">Use selected image</button>');

      cancel.addEventListener('click', () => close(null));
      use.addEventListener('click', () => {
        const body = foot.parentElement.querySelector('.modal__body');
        const selected = body.__getSelected ? body.__getSelected() : null;

        if (!selected) {
          notify.warn('Select an image first.');
          return;
        }
        close(selected);
      });

      foot.append(cancel, use);
    },
  });
}

/** File chooser + upload, resolving with the created media row. */
export function promptUpload({ folder = null } = {}) {
  return new Promise((resolve) => {
    const input = el(`<input type="file" accept="${ACCEPT}" style="display:none">`);
    document.body.appendChild(input);

    input.addEventListener('change', async () => {
      const file = input.files && input.files[0];
      input.remove();

      if (!file) {
        resolve(null);
        return;
      }

      const media = await uploadFile(file, { folder });
      resolve(media);
    });

    // A cancelled file dialog fires no event; resolve on the next focus.
    window.addEventListener('focus', function onFocus() {
      window.removeEventListener('focus', onFocus);
      setTimeout(() => {
        if (document.body.contains(input)) {
          input.remove();
          resolve(null);
        }
      }, 400);
    });

    input.click();
  });
}

/** Uploads one file with client-side pre-checks, then a server-side re-check. */
export async function uploadFile(file, { altText = '', folder = null, onProgress } = {}) {
  const maxBytes = session.config.uploadMaxBytes || 5242880;

  if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
    notify.error('Only JPG, PNG and WEBP images can be uploaded.');
    return null;
  }

  if (file.size > maxBytes) {
    notify.error(`That image is ${(file.size / 1048576).toFixed(1)} MB. The limit is ${(maxBytes / 1048576).toFixed(1)} MB.`);
    return null;
  }

  const formData = new FormData();
  formData.append('file', file);
  if (altText) formData.append('alt_text', altText);

  // Naming the folder here means the file is written straight into it — no
  // move, and no moment where the photo sits in Unsorted.
  if (folder) formData.append('folder', folder);

  try {
    if (onProgress) onProgress(40);
    const result = await api.upload('/media', formData);
    if (onProgress) onProgress(100);

    invalidateFolders();
    notify.ok(`Uploaded "${file.name}".`);
    return result.data;
  } catch (error) {
    notify.error(error.message);
    return null;
  }
}

/**
 * Drag-and-drop upload area. `onUploaded(media)` fires per accepted file.
 * `folder` files everything dropped here straight into that folder.
 */
export function buildDropzone(onUploaded, { folder = null } = {}) {
  const zone = el(`
    <div>
      <div class="dropzone">
        ${icon('i-upload', 22)}
        <div class="mt-1"><b>Drop an image here</b> or click to browse</div>
        <small class="muted">JPG, PNG or WEBP · up to 5 MB</small>
      </div>
      <div class="upload-bar" style="display:none"><span></span></div>
    </div>
  `);

  const dropzone = zone.querySelector('.dropzone');
  const bar = zone.querySelector('.upload-bar');
  const fill = bar.querySelector('span');

  const handle = async (files) => {
    if (!files || !files.length) return;

    bar.style.display = '';
    for (const file of files) {
      const media = await uploadFile(file, {
        folder,
        onProgress: (percent) => { fill.style.width = percent + '%'; },
      });
      if (media && onUploaded) onUploaded(media);
    }
    bar.style.display = 'none';
    fill.style.width = '0';
  };

  dropzone.addEventListener('click', () => {
    const input = el(`<input type="file" accept="${ACCEPT}" multiple style="display:none">`);
    document.body.appendChild(input);
    input.addEventListener('change', () => {
      handle(input.files);
      input.remove();
    });
    input.click();
  });

  ['dragenter', 'dragover'].forEach((type) => {
    dropzone.addEventListener(type, (event) => {
      event.preventDefault();
      dropzone.classList.add('is-over');
    });
  });

  ['dragleave', 'drop'].forEach((type) => {
    dropzone.addEventListener(type, (event) => {
      event.preventDefault();
      dropzone.classList.remove('is-over');
    });
  });

  dropzone.addEventListener('drop', (event) => {
    handle(event.dataTransfer.files);
  });

  return zone;
}
