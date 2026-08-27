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
import { confirmDialog, emptyState, errorState, notify, withBusy } from '../ui/feedback.js';
import { clearOptionCache, pageHead } from './helpers.js';
import { templatePanel } from './services-template.js';

const MAX_BYTES = 2 * 1024 * 1024;

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

  // Kept in the closure so the panels survive a re-render of the chooser —
  // someone who opened one, downloaded a CSV, uploaded and hit an error should
  // not have to open it again.
  let templateOpen = false;
  let rulesOpen = false;

  outlet.appendChild(pageHead({
    title: 'Import services from CSV',
    description: 'Add new services and update existing ones from a spreadsheet. '
      + 'You will see exactly what changes before anything is saved.',
  }));

  const stage = el('<div></div>');
  outlet.appendChild(stage);

  stage.replaceChildren(el(`
    <div class="card"><div class="card__body">
      <p class="muted" style="text-align:center;padding:2rem 0">Loading…</p>
    </div></div>
  `));

  // The column contract comes from ServiceCsvSchema via the API, so the admin
  // never keeps its own copy of the list. Fetched once, up front, which keeps
  // renderChooser() synchronous for its four call sites.
  let options;

  /**
   * Loads the column contract and shows step one.
   *
   * Called again after the import rules are saved, because the contract the
   * screen displays — the reference table, and which columns the template
   * carries — is derived from it.
   */
  async function boot() {
    try {
      options = (await api.get('/services/form-options')).data;
    } catch (error) {
      const card = el('<div class="card"><div class="card__body"></div></div>');
      card.querySelector('.card__body').appendChild(errorState(
        error.message || 'The import columns could not be loaded.',
        () => serviceImportPage(outlet)
      ));
      // Rendering a dropzone with no stated columns would let someone upload a
      // file against a contract nobody showed them.
      outlet.replaceChildren(card);
      return false;
    }

    renderChooser();
    return true;
  }

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
              <thead><tr>
                <th>Column</th><th>Required</th><th>Default when blank</th><th>Notes</th>
              </tr></thead>
              <tbody></tbody>
            </table>
          </div>
          <div class="mt-3" data-slot="actions"></div>
          <div class="mt-3" data-slot="template" hidden></div>
          <div class="mt-3" data-slot="rules" hidden></div>
          <div class="mt-3" data-slot="drop"></div>
        </div>
      </div>
    `);

    const tbody = card.querySelector('tbody');

    options.columns.forEach((column) => {
      tbody.appendChild(el(`
        <tr>
          <td data-label="Column"><span class="cell-title">${esc(column.key)}</span></td>
          <td data-label="Required">${column.required
            ? '<span class="pill pill--warn">Required</span>'
            : '<span class="pill pill--plain">Optional</span>'}</td>
          <td data-label="Default when blank">${column.default
            ? `<code>${esc(column.default)}</code>`
            : '<span class="muted">—</span>'}</td>
          <td data-label="Notes"><span class="cell-sub">${esc(column.help)}</span></td>
        </tr>
      `));
    });

    const actions = card.querySelector('[data-slot="actions"]');
    const templateSlot = card.querySelector('[data-slot="template"]');

    const toggle = el(
      `<button type="button" class="btn btn--ghost">${icon('i-copy', 15)} Start from a template</button>`
    );
    toggle.setAttribute('aria-expanded', String(templateOpen));

    const openTemplatePanel = () => {
      if (!templateSlot.firstChild) templateSlot.appendChild(templatePanel(options));
      templateSlot.hidden = false;
    };

    toggle.addEventListener('click', () => {
      templateOpen = !templateOpen;
      toggle.setAttribute('aria-expanded', String(templateOpen));

      if (templateOpen) {
        openTemplatePanel();
      } else {
        templateSlot.hidden = true;
      }
    });

    actions.appendChild(toggle);

    if (templateOpen) openTemplatePanel();

    // --- import rules ---------------------------------------------
    const rulesSlot = card.querySelector('[data-slot="rules"]');

    const rulesToggle = el(
      `<button type="button" class="btn btn--ghost" style="margin-left:.4rem">${
        icon('i-settings', 15)} Import rules</button>`
    );
    rulesToggle.setAttribute('aria-expanded', String(rulesOpen));

    const openRulesPanel = () => {
      if (!rulesSlot.firstChild) {
        rulesSlot.appendChild(rulesPanel(options, () => {
          // The contract changed, so everything derived from it is stale —
          // the reference table, the template columns and the panel itself.
          boot();
        }));
      }
      rulesSlot.hidden = false;
    };

    rulesToggle.addEventListener('click', () => {
      rulesOpen = !rulesOpen;
      rulesToggle.setAttribute('aria-expanded', String(rulesOpen));

      if (rulesOpen) {
        openRulesPanel();
      } else {
        rulesSlot.hidden = true;
      }
    });

    actions.appendChild(rulesToggle);

    if (rulesOpen) openRulesPanel();

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

  await boot();
}

// -----------------------------------------------------------------
// Pieces
// -----------------------------------------------------------------

/**
 * Per-column import rules: which columns must be filled, and what a blank cell
 * falls back to on a NEW service.
 *
 * Lives here rather than on the Settings page because this is the only screen
 * that knows the column list — Settings has no access to it, deliberately.
 * Everyone who can import sees the rules; only settings.edit can change them,
 * and the server enforces that independently on PUT /settings.
 *
 * Stored as one JSON site setting, so this reuses the existing settings
 * endpoint, permission and audit trail rather than adding a route.
 *
 * @param options  the /services/form-options payload
 * @param onSaved  called after a successful save, to reload the contract
 */
function rulesPanel(options, onSaved) {
  const canEdit = options.can_edit_rules === true;

  // Identity columns decide which service a row matches, before any default
  // could be applied — so they can never have one.
  const NO_DEFAULT = ['name', 'slug'];

  const panel = el(`
    <div style="border-top:1px solid var(--line);padding-top:1.25rem">
      <b style="font-size:.92rem">Import rules</b>
      <p class="muted" style="font-size:.85rem;margin:.2rem 0 .8rem">
        Tick the columns a file must fill in. A default is used when a cell is blank
        <b>on a new service only</b> — updating an existing one still leaves a blank
        cell alone, so you can import a file carrying just the columns you changed.
        Give a column a default and it drops out of the blank template.
      </p>
      <div class="table-wrap">
        <table class="data">
          <thead><tr><th>Column</th><th>Must be filled</th><th>Default when blank</th></tr></thead>
          <tbody></tbody>
        </table>
      </div>
      <div class="mt-2" data-slot="rules-actions"></div>
    </div>
  `);

  const tbody = panel.querySelector('tbody');

  // Columns whose values are a fixed list. Typing into these is how an invalid
  // default gets saved in the first place, so they are chosen, not typed.
  const CHOICES = {
    icon_key: (options.icons || []).map((i) => ({ value: i.key, label: i.label })),
    status: [
      { value: 'active', label: 'active' },
      { value: 'inactive', label: 'inactive' },
    ],
    featured: [
      { value: 'yes', label: 'yes' },
      { value: 'no', label: 'no' },
    ],
  };

  (options.columns || []).forEach((column) => {
    const locked = NO_DEFAULT.includes(column.key);
    const choices = CHOICES[column.key];
    const disabled = canEdit && !locked ? '' : 'disabled';
    const current = column.default || '';

    // The empty option is the "no default" case, and it has to come first so
    // that is what a fresh column shows.
    const control = choices
      ? `<select data-default="${esc(column.key)}" ${disabled}>
           <option value="">${column.key === 'icon_key' ? 'No icon' : 'None'}</option>
           ${choices.map((choice) => `
             <option value="${esc(choice.value)}"${
               String(choice.value) === String(current) ? ' selected' : ''
             }>${esc(choice.label)}</option>`).join('')}
         </select>`
      : `<input type="text" data-default="${esc(column.key)}"
           value="${esc(current)}"
           placeholder="${locked ? 'Not available' : 'None'}" ${disabled}>`;

    const row = el(`
      <tr>
        <td data-label="Column"><span class="cell-title">${esc(column.key)}</span></td>
        <td data-label="Must be filled">
          <label class="check" style="padding:0">
            <input type="checkbox" data-rule="${esc(column.key)}"
              ${column.required ? 'checked' : ''} ${canEdit ? '' : 'disabled'}>
            <span></span>
          </label>
        </td>
        <td data-label="Default when blank">${control}</td>
      </tr>
    `);

    if (locked) {
      row.querySelector('[data-default]').title =
        'This column identifies which service a row belongs to, so it cannot have a default.';
    }

    tbody.appendChild(row);
  });

  const actions = panel.querySelector('[data-slot="rules-actions"]');

  if (!canEdit) {
    actions.appendChild(el(
      '<p class="muted" style="font-size:.85rem;margin:0">'
      + 'These rules apply to every import. Changing them needs permission to edit settings.</p>'
    ));
    return panel;
  }

  const save = el('<button type="button" class="btn btn--sm">Save rules</button>');
  const error = el('<p class="form-error" style="margin:.6rem 0 0" hidden></p>');

  save.addEventListener('click', () => withBusy(save, async () => {
    // Sparse: only columns that differ from the built-in contract are stored,
    // so the base contract stays the source of truth and the setting stays
    // small and readable.
    const rules = {};

    (options.columns || []).forEach((column) => {
      const required = panel.querySelector(`[data-rule="${CSS.escape(column.key)}"]`).checked;
      const value = panel.querySelector(`[data-default="${CSS.escape(column.key)}"]`).value.trim();
      const entry = {};

      if (required !== Boolean(column.required)) entry.required = required;
      if (value !== '') entry.default = value;

      if (Object.keys(entry).length) {
        // `required` has to travel whenever a default does, or reloading the
        // page would show the built-in value beside a custom default.
        entry.required = required;
        rules[column.key] = entry;
      }
    });

    error.hidden = true;

    try {
      await api.put('/settings', { services_import_rules: JSON.stringify(rules) });
      notify.ok('Import rules saved.');
      onSaved();
    } catch (e) {
      error.textContent = e.fields?.services_import_rules || e.message || 'The rules could not be saved.';
      error.hidden = false;
    }
  }));

  actions.append(save, error);

  return panel;
}

/**
 * The "make a copy" link for a stored Sheets URL, or null.
 *
 * Mirrors GoogleSheetUrl::copyUrl() in PHP. A small, self-revealing
 * duplication: a link that does not parse produces no button at all, and a
 * wrong one produces an immediate Google 404.
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
