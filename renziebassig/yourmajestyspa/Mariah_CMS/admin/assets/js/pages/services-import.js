/**
 * Bulk import of services from a CSV file.
 *
 * Three steps on one route: choose a file, read the preview, confirm. The
 * preview and the commit hit the same endpoint with the same file — only the
 * `dry_run` flag differs — so what the operator approves is exactly what runs.
 */

import { api } from '../api.js';
import { navigate } from '../router.js';
import { session } from '../session.js';
import { el, esc, icon, money } from '../ui/dom.js';
import { confirmDialog, emptyState, notify, withBusy } from '../ui/feedback.js';
import { clearOptionCache, pageHead } from './helpers.js';

const MAX_BYTES = 2 * 1024 * 1024;

/**
 * Mirrors ServiceCsvSchema::columns() in PHP, which is the source of truth.
 * Drift is self-revealing: a renamed column shows up as "ignored" on the very
 * next preview.
 */
const COLUMNS = [
  ['name', true, 'The service name, 2 to 190 characters.'],
  ['category', true, 'An existing category name or slug. The import never creates categories.'],
  ['price', true, 'A number. "$1,250.00" and "1250" are both accepted.'],
  ['slug', false, 'Matches the row to an existing service. Blank means "match on the name".'],
  ['short_description', false, 'One line under the name on the website. Max 500 characters.'],
  ['description', false, 'The full description shown when the card is opened.'],
  ['price_display', false, 'Overrides the price on the website, e.g. "from $150".'],
  ['promo_price', false, 'Must be lower than the price.'],
  ['duration_minutes', false, 'Whole minutes, up to 1440.'],
  ['duration_display', false, 'Overrides the duration, e.g. "1 hr & 40 mins".'],
  ['icon_key', false, 'i-hands, i-leaf, i-drop, i-stone, i-boat, i-crown, i-spark or i-gift.'],
  ['booking_url', false, 'The Booker.com link for this treatment.'],
  ['status', false, 'active or inactive. Defaults to active on new services.'],
  ['featured', false, 'yes or no.'],
  ['most_loved_rank', false, '1, 2 or 3. Only one service can hold each rank.'],
  ['display_order', false, 'Lower numbers appear first. Blank follows the file order.'],
];

const ACTION_PILLS = {
  create: ['pill pill--ok', 'Create'],
  update: ['pill pill--info', 'Update'],
  unchanged: ['pill', 'No change'],
  error: ['pill pill--danger', 'Error'],
};

export async function serviceImportPage(outlet) {
  // The source is held across both requests — the commit re-sends the same
  // file or the same link, rather than the server keeping state between them.
  let file = null;
  let sourceUrl = '';
  let source = 'file';

  outlet.appendChild(pageHead({
    title: 'Import services from CSV',
    description: 'Add new services and update existing ones from a spreadsheet. '
      + 'You will see exactly what changes before anything is saved.',
  }));

  const stage = el('<div></div>');
  outlet.appendChild(stage);

  // ---------------------------------------------------------------
  // Step 1 — choose a file
  // ---------------------------------------------------------------
  function renderChooser(errorMessage) {
    file = null;
    sourceUrl = '';

    const card = el(`
      <div class="card">
        <div class="card__head"><h3>The file</h3></div>
        <div class="card__body">
          <p class="muted" style="margin-top:0">
            One row per service. Column names are matched loosely, so
            <code>Service Name</code> and <code>name</code> both work. A blank cell leaves that
            field unchanged; type <code>NULL</code> to clear one.
          </p>
          <div class="table-wrap mt-2">
            <table class="data">
              <thead><tr><th>Column</th><th>Required</th><th>Notes</th></tr></thead>
              <tbody></tbody>
            </table>
          </div>
          <div class="mt-3" data-slot="actions"></div>
          <div class="mt-3" data-slot="drop"></div>
        </div>
      </div>
    `);

    const tbody = card.querySelector('tbody');

    COLUMNS.forEach(([key, required, help]) => {
      tbody.appendChild(el(`
        <tr>
          <td data-label="Column"><span class="cell-title">${esc(key)}</span></td>
          <td data-label="Required">${required
            ? '<span class="pill pill--warn">Required</span>'
            : '<span class="pill pill--plain">Optional</span>'}</td>
          <td data-label="Notes"><span class="cell-sub">${esc(help)}</span></td>
        </tr>
      `));
    });

    const actions = card.querySelector('[data-slot="actions"]');

    const templateButton = el(
      `<button type="button" class="btn btn--ghost">${icon('i-upload', 15)} Download blank template</button>`
    );
    templateButton.addEventListener('click', downloadTemplate);
    actions.appendChild(templateButton);

    // Only when an Admin has configured a template under Settings. An anchor
    // rather than a button so middle-click and "open in new tab" work.
    const copyUrl = sheetCopyUrl(session.config.services_import_sheet_url);

    if (copyUrl) {
      actions.appendChild(el(`
        <a class="btn btn--ghost" href="${esc(copyUrl)}" target="_blank" rel="noopener"
           style="margin-left:.5rem">
          ${icon('i-copy', 15)} Open the Google Sheets template
        </a>
      `));
      actions.appendChild(el(`
        <p class="muted" style="font-size:.83rem;margin:.75rem 0 0">
          Google will offer to make your own copy. Edit that copy, then either import it
          by link below, or use <b>File → Download → Comma-separated values</b> and upload it.
        </p>
      `));
    }

    const dropSlot = card.querySelector('[data-slot="drop"]');

    // When the server says a link import cannot work — turned off, or no cURL
    // — this slot stays exactly what it was before the feature existed.
    const canImportByUrl = session.config.services_import_url_available === true;

    if (canImportByUrl) {
      dropSlot.appendChild(sourcePicker());
    }

    if (errorMessage) {
      dropSlot.appendChild(el(`<div class="form-error">${esc(errorMessage)}</div>`));
    }

    const filePanel = el('<div></div>');
    filePanel.appendChild(buildCsvDropzone((chosen) => {
      file = chosen;
      source = 'file';
      submit(true);
    }));

    dropSlot.appendChild(filePanel);

    let urlPanel = null;

    if (canImportByUrl) {
      urlPanel = buildUrlPanel((url) => {
        sourceUrl = url;
        source = 'url';
        submit(true);
      });
      urlPanel.hidden = true;
      dropSlot.appendChild(urlPanel);
    }

    stage.replaceChildren(card);

    /** Two buttons, no tab machinery — each panel holds exactly one control. */
    function sourcePicker() {
      const picker = el(`
        <div class="mb-2" role="group" aria-label="Where the services come from">
          <button type="button" class="btn btn--sm" data-src="file">Upload a CSV</button>
          <button type="button" class="btn btn--sm btn--ghost" data-src="url"
                  style="margin-left:.4rem">Google Sheets link</button>
        </div>
      `);

      picker.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-src]');
        if (!button) return;

        const wanted = button.dataset.src;

        picker.querySelectorAll('button[data-src]').forEach((node) => {
          node.classList.toggle('btn--ghost', node.dataset.src !== wanted);
        });

        filePanel.hidden = wanted !== 'file';
        if (urlPanel) urlPanel.hidden = wanted !== 'url';
      });

      return picker;
    }
  }

  // ---------------------------------------------------------------
  // Submit — preview (dryRun) or commit, from whichever source is active
  // ---------------------------------------------------------------
  async function submit(dryRun, digest, button) {
    if (source === 'file' && !file) return;
    if (source === 'url' && !sourceUrl) return;

    const run = async () => {
      try {
        let result;

        if (source === 'url') {
          // Strings, because the server compares
          // (string) input('dry_run','1') !== '0'.
          result = await api.post('/services/import', {
            source_url: sourceUrl,
            dry_run: dryRun ? '1' : '0',
            confirm_digest: digest || undefined,
          });
        } else {
          const form = new FormData();
          form.append('file', file);
          form.append('dry_run', dryRun ? '1' : '0');
          if (digest) form.append('confirm_digest', digest);

          result = await api.upload('/services/import', form);
        }

        return result.data;
      } catch (error) {
        // File-level failures come back keyed "file"; per-row problems never
        // arrive this way, they ride in data.rows.
        const message = error.fields?.file || error.message;

        if (dryRun) {
          renderChooser(message);
        } else {
          notify.error(message);
        }
        return null;
      }
    };

    let data;

    if (button) {
      data = await withBusy(button, run);
    } else {
      const what = source === 'url' ? 'the Google Sheet' : file.name;
      stage.replaceChildren(el(`
        <div class="card"><div class="card__body">
          <p class="muted" style="text-align:center;padding:2rem 0">Reading ${esc(what)}…</p>
        </div></div>
      `));
      data = await run();
    }

    if (!data) return;

    if (dryRun) {
      renderPreview(data);
    } else if (data.committed) {
      renderResult(data);
    } else {
      // Refused (rows still in error) or rolled back mid-write. Stay on the
      // preview with the source still held so retry is one click.
      renderPreview(data, data.abort?.message || data.message);
    }
  }

  // ---------------------------------------------------------------
  // Step 2 — preview
  // ---------------------------------------------------------------
  function renderPreview(data, errorMessage) {
    const { summary } = data;
    const nodes = [];

    if (errorMessage) {
      nodes.push(el(`<div class="form-error">${esc(errorMessage)}</div>`));
    }

    nodes.push(statTiles([
      ['Rows in file', summary.rows, esc(data.file.name)],
      ['To create', summary.create, 'new services'],
      ['To update', summary.update, 'existing services'],
      ['No change', summary.unchanged, 'already match'],
      ['Errors', summary.error, summary.error ? 'must be fixed first' : 'none'],
    ]));

    if (data.warnings.length) {
      const warnings = el('<div class="card mb-2"><div class="card__body"></div></div>');
      const body = warnings.querySelector('.card__body');

      data.warnings.forEach((warning) => {
        body.appendChild(el(`
          <p style="margin:0 0 .5rem">
            <span class="pill pill--warn">Note</span>
            <span class="muted">${esc(warning)}</span>
          </p>
        `));
      });

      nodes.push(warnings);
    }

    if (!data.rows.length) {
      nodes.push(el('<div class="card"><div class="card__body"></div></div>'));
      nodes[nodes.length - 1].querySelector('.card__body').appendChild(emptyState({
        title: 'No rows to import',
        message: 'The file has a header row but no services under it.',
      }));
    } else {
      nodes.push(previewTable(data.rows));
    }

    const actions = el(`
      <div class="form-actions">
        <button type="button" class="btn btn--ghost">${
          data.file.source === 'google_sheet' ? 'Choose a different sheet' : 'Choose a different file'
        }</button>
        <button type="button" class="btn"></button>
      </div>
    `);

    const [backButton, confirmButton] = actions.querySelectorAll('button');
    backButton.addEventListener('click', () => renderChooser());

    const writable = summary.create + summary.update;

    if (summary.error > 0) {
      // The disabled state IS the gate — nothing can be imported until the
      // file is fixed.
      confirmButton.textContent = `Fix ${summary.error} row${summary.error === 1 ? '' : 's'} to continue`;
      confirmButton.disabled = true;
      confirmButton.title = 'Correct the highlighted rows in your spreadsheet and upload it again.';
    } else if (writable === 0) {
      confirmButton.textContent = 'Nothing to import';
      confirmButton.disabled = true;
      confirmButton.title = 'Every row already matches what is stored.';
    } else {
      confirmButton.textContent = `Import ${writable} service${writable === 1 ? '' : 's'}`;
      confirmButton.addEventListener('click', async () => {
        const ok = await confirmDialog({
          title: `Import ${writable} service${writable === 1 ? '' : 's'}?`,
          message: `${summary.create} will be created and ${summary.update} updated. `
            + 'Active services appear on the website immediately.',
          confirmLabel: 'Import',
          danger: false,
        });

        if (ok) await submit(false, data.file.digest, confirmButton);
      });
    }

    nodes.push(actions);
    stage.replaceChildren(...nodes);
  }

  function previewTable(rows) {
    const card = el(`
      <div class="card">
        <div class="card__head"><h3>What will happen</h3></div>
        <div class="card__body card__body--flush">
          <div class="table-wrap">
            <table class="data">
              <thead><tr>
                <th>Line</th><th>Service</th><th>Category</th>
                <th class="text-right">Price</th><th>Action</th><th>Details</th>
              </tr></thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    `);

    const tbody = card.querySelector('tbody');

    rows.forEach((row) => {
      const [pillClass, pillLabel] = ACTION_PILLS[row.action] || ACTION_PILLS.error;
      const isError = row.action === 'error';

      const tr = el(`<tr${isError ? ' class="is-deleted"' : ''}></tr>`);

      tr.appendChild(el(`<td data-label="Line" class="cell-num">${esc(row.line)}</td>`));
      tr.appendChild(el(`
        <td data-label="Service">
          <span class="cell-title">${esc(row.name || '—')}</span>
          <span class="cell-sub">${esc(row.slug || '')}</span>
        </td>
      `));
      tr.appendChild(el(`<td data-label="Category">${esc(row.category || '—')}</td>`));
      tr.appendChild(el(
        `<td data-label="Price" class="cell-num text-right">${esc(row.price === null ? '—' : money(row.price))}</td>`
      ));
      tr.appendChild(el(`<td data-label="Action"><span class="${pillClass}">${pillLabel}</span></td>`));
      tr.appendChild(el(`<td data-label="Details">${detailCell(row)}</td>`));

      tbody.appendChild(tr);
    });

    return card;
  }

  function detailCell(row) {
    if (row.action === 'error') {
      return Object.values(row.errors)
        .map((message) => `<span class="cell-sub">${esc(message)}</span>`)
        .join('');
    }

    if (row.action === 'update' && row.changes) {
      return Object.entries(row.changes)
        .map(([field, change]) =>
          `<span class="cell-sub">${esc(field)}: ${esc(change.from ?? '—')} → ${esc(change.to ?? '—')}</span>`)
        .join('');
    }

    if (row.action === 'unchanged') {
      return '<span class="cell-sub muted">Already matches</span>';
    }

    return '<span class="cell-sub muted">New service</span>';
  }

  // ---------------------------------------------------------------
  // Step 3 — result
  // ---------------------------------------------------------------
  function renderResult(data) {
    const { summary } = data;

    notify.ok(
      `Imported ${summary.created + summary.updated} services — `
      + `${summary.created} created, ${summary.updated} updated.`
    );

    const nodes = [
      statTiles([
        ['Imported', summary.created + summary.updated, 'from ' + data.file.name, true],
        ['Created', summary.created, 'new services'],
        ['Updated', summary.updated, 'existing services'],
        ['No change', summary.unchanged, 'left alone'],
      ]),
    ];

    const written = data.rows.filter((row) => row.action === 'create' || row.action === 'update');

    if (written.length) {
      const card = el(`
        <div class="card">
          <div class="card__head"><h3>What changed</h3></div>
          <div class="card__body card__body--flush">
            <div class="table-wrap">
              <table class="data">
                <thead><tr><th>Service</th><th>Category</th><th>Action</th><th></th></tr></thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
      `);

      const tbody = card.querySelector('tbody');

      written.forEach((row) => {
        const [pillClass, pillLabel] = ACTION_PILLS[row.action];
        tbody.appendChild(el(`
          <tr>
            <td data-label="Service"><span class="cell-title">${esc(row.name)}</span></td>
            <td data-label="Category">${esc(row.category || '—')}</td>
            <td data-label="Action"><span class="${pillClass}">${pillLabel}</span></td>
            <td data-label="" class="text-right">${row.service_id
              ? `<a class="btn btn--ghost btn--sm" href="#/services/${esc(row.service_id)}/edit">Open</a>`
              : ''}</td>
          </tr>
        `));
      });

      nodes.push(card);
    }

    const actions = el(`
      <div class="form-actions">
        <button type="button" class="btn btn--ghost">Import another file</button>
        <button type="button" class="btn">Back to services</button>
      </div>
    `);

    const [againButton, doneButton] = actions.querySelectorAll('button');
    againButton.addEventListener('click', () => renderChooser());
    doneButton.addEventListener('click', () => {
      clearOptionCache();
      navigate('/services');
    });

    nodes.push(actions);
    stage.replaceChildren(...nodes);
  }

  renderChooser();
}

// -----------------------------------------------------------------
// Pieces
// -----------------------------------------------------------------

/**
 * The "make a copy" link for a stored Sheets URL, or null.
 *
 * Mirrors GoogleSheetUrl::copyUrl() in PHP — a small duplication of the same
 * kind as the COLUMNS const above, and self-revealing in the same way: a link
 * that does not parse produces no button, and a wrong one produces an
 * immediate Google 404.
 */
function sheetCopyUrl(stored) {
  if (!stored) return null;

  // "Publish to web" links use a different id space and /copy 404s for them.
  if (/^https?:\/\/docs\.google\.com\/spreadsheets\/d\/e\//i.test(stored)) return null;

  const match = String(stored).trim().match(
    /^https?:\/\/docs\.google\.com\/spreadsheets\/d\/([A-Za-z0-9\-_]{20,120})(?:[/?#]|$)/i
  );

  return match ? `https://docs.google.com/spreadsheets/d/${match[1]}/copy` : null;
}

function statTiles(tiles) {
  const grid = el('<div class="stat-grid"></div>');

  tiles.forEach(([label, value, meta, dark]) => {
    grid.appendChild(el(`
      <div class="stat${dark ? ' stat--dark' : ''}">
        <div class="stat__label">${esc(label)}</div>
        <div class="stat__value">${esc(value)}</div>
        <div class="stat__meta">${esc(meta)}</div>
      </div>
    `));
  });

  return grid;
}

/**
 * The Google Sheets link panel.
 *
 * Deliberately not prefilled from the stored template link: that link is the
 * BLANK template, and prefilling it would lead people to import an empty sheet.
 * What belongs here is their own copy.
 */
function buildUrlPanel(onSubmit) {
  const panel = el(`
    <div>
      <div class="field" data-field="source_url">
        <label for="f-source_url">Google Sheets link</label>
        <input type="url" id="f-source_url" name="source_url"
               placeholder="https://docs.google.com/spreadsheets/d/…">
        <small class="field__hint">
          Paste your own copy of the template — not the template itself. The sheet must be
          shared as <b>Anyone with the link → Viewer</b>, and the tab is the
          <code>#gid=</code> at the end of the link.
        </small>
      </div>
      <button type="button" class="btn mt-2">Preview this sheet</button>
    </div>
  `);

  const input = panel.querySelector('input');
  const button = panel.querySelector('button');

  const go = () => {
    const value = input.value.trim();

    if (value === '') {
      notify.error('Paste the link to your Google Sheet first.');
      input.focus();
      return;
    }

    onSubmit(value);
  };

  button.addEventListener('click', go);
  input.addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
      event.preventDefault();
      go();
    }
  });

  return panel;
}

/**
 * A CSV-shaped sibling of media-picker's buildDropzone, which is hard-bound to
 * images through its ACCEPT list and its image-only uploadFile(). The styling
 * classes are shared; the behaviour is not.
 */
function buildCsvDropzone(onFile) {
  const zone = el(`
    <div class="dropzone">
      ${icon('i-upload', 22)}
      <div class="mt-1"><b>Drop a .csv file here</b> or click to browse</div>
      <small class="muted">Up to 2 MB and 500 rows · nothing is saved until you confirm</small>
    </div>
  `);

  // Rejecting locally means no waiting through the upload of a file that was
  // never going to be accepted.
  const accept = (chosen) => {
    if (!chosen) return;

    if (!/\.(csv|txt)$/i.test(chosen.name)) {
      notify.error(
        'Only .csv files can be imported. In Excel choose File → Save As → CSV UTF-8; '
        + 'in Google Sheets choose File → Download → Comma-separated values.'
      );
      return;
    }

    if (chosen.size === 0) {
      notify.error('That file is empty.');
      return;
    }

    if (chosen.size > MAX_BYTES) {
      notify.error(
        `That file is ${(chosen.size / 1048576).toFixed(1)} MB. The limit is `
        + `${(MAX_BYTES / 1048576).toFixed(1)} MB.`
      );
      return;
    }

    onFile(chosen);
  };

  zone.addEventListener('click', () => {
    const input = el('<input type="file" accept=".csv,text/csv" style="display:none">');
    document.body.appendChild(input);
    input.addEventListener('change', () => {
      accept(input.files && input.files[0]);
      input.remove();
    });
    input.click();
  });

  ['dragenter', 'dragover'].forEach((type) => {
    zone.addEventListener(type, (event) => {
      event.preventDefault();
      zone.classList.add('is-over');
    });
  });

  ['dragleave', 'drop'].forEach((type) => {
    zone.addEventListener(type, (event) => {
      event.preventDefault();
      zone.classList.remove('is-over');
    });
  });

  zone.addEventListener('drop', (event) => {
    accept(event.dataTransfer.files && event.dataTransfer.files[0]);
  });

  return zone;
}

/**
 * The template is built here rather than served, so it needs no endpoint and
 * no non-JSON response path. The leading BOM is what makes Excel open it as
 * UTF-8 — and is the same BOM the importer strips on the way back in.
 */
function downloadTemplate() {
  const headers = COLUMNS.map(([key]) => key);

  const examples = [
    ['Hot Stone Massage', 'Massages', '165', '', 'Warm basalt stones melt deep tension.',
     '', '', '', '80', '', 'i-stone', '', 'active', 'yes', '', ''],
    ['Signature Facial', 'Facials', '140', '', 'A tailored deep-cleansing facial.',
     '', '', '', '60', '', 'i-drop', '', 'active', 'no', '', ''],
  ];

  const escapeCell = (value) => (/[",\n]/.test(value) ? `"${value.replace(/"/g, '""')}"` : value);

  const csv = '﻿'
    + [headers, ...examples].map((row) => row.map(escapeCell).join(',')).join('\r\n')
    + '\r\n';

  const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
  const link = el(`<a href="${url}" download="majesty-services-template.csv" style="display:none"></a>`);

  document.body.appendChild(link);
  link.click();
  link.remove();

  URL.revokeObjectURL(url);
}
