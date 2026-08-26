/**
 * Bulk import of services from a CSV file.
 *
 * Three steps on one route: choose a file, read the preview, confirm. The
 * preview and the commit hit the same endpoint with the same file — only the
 * `dry_run` flag differs — so what the operator approves is exactly what runs.
 */

import { api } from '../api.js';
import { navigate } from '../router.js';
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
  // The File object is held across both requests — the commit re-uploads it
  // rather than the server keeping any state between the two calls.
  let file = null;

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

    const templateButton = el(
      `<button type="button" class="btn btn--ghost">${icon('i-upload', 15)} Download blank template</button>`
    );
    templateButton.addEventListener('click', downloadTemplate);
    card.querySelector('[data-slot="actions"]').appendChild(templateButton);

    const dropSlot = card.querySelector('[data-slot="drop"]');

    if (errorMessage) {
      dropSlot.appendChild(el(`<div class="form-error">${esc(errorMessage)}</div>`));
    }

    dropSlot.appendChild(buildCsvDropzone((chosen) => {
      file = chosen;
      submitFile(true);
    }));

    stage.replaceChildren(card);
  }

  // ---------------------------------------------------------------
  // Upload — preview (dryRun) or commit
  // ---------------------------------------------------------------
  async function submitFile(dryRun, digest, button) {
    if (!file) return;

    const form = new FormData();
    form.append('file', file);
    form.append('dry_run', dryRun ? '1' : '0');
    if (digest) form.append('confirm_digest', digest);

    const run = async () => {
      try {
        const result = await api.upload('/services/import', form);
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
      stage.replaceChildren(el(`
        <div class="card"><div class="card__body">
          <p class="muted" style="text-align:center;padding:2rem 0">Reading ${esc(file.name)}…</p>
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
      // preview with the file still held so retry is one click.
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
        <button type="button" class="btn btn--ghost">Choose a different file</button>
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

        if (ok) await submitFile(false, data.file.digest, confirmButton);
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
      notify.error('Only .csv files can be imported. In Excel choose File → Save As → CSV UTF-8.');
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
