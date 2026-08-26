/**
 * Builds a spreadsheet of services for the operator to work in.
 *
 * Google has no URL that creates a filled sheet — /copy needs one to already
 * exist and /create makes a blank one — and actually creating one means the
 * Sheets API, a Google Cloud project and a client library this project has no
 * way to install. So the fast route is the clipboard: pasting TSV into cell A1
 * fills the whole grid in one keystroke.
 *
 * Two sources (the current menu, or a blank template with examples) and two
 * deliveries (clipboard TSV, or a CSV download). All four run through one row
 * builder, so what is pasted and what is downloaded cannot disagree.
 *
 * Everything here is client-side, which is why it needs no endpoint and no
 * non-JSON response path — Response::emit() is JSON-only by design.
 */

import { api } from '../api.js';
import { el, esc, icon } from '../ui/dom.js';
import { emptyState, notify, withBusy } from '../ui/feedback.js';

/** Paginator::MAX_PER_PAGE — a larger value is clamped, not honoured. */
const PER_PAGE = 100;

/** A runaway stop, not a product limit. */
const MAX_PAGES = 20;

/** ServiceCsvSchema::MAX_ROWS — one import file cannot hold more. */
const IMPORT_MAX_ROWS = 500;

// =================================================================
// The panel
// =================================================================

/**
 * @param {{columns: Array, categories: Array, icons: Array}} options
 *        exactly the GET /services/form-options payload
 */
export function templatePanel(options) {
  const { columns, categories, icons } = options;

  const panel = el(`
    <div style="border-top:1px solid var(--line);padding-top:1.25rem">
      <div data-slot="notes"></div>

      <div class="mb-2">
        <b style="font-size:.92rem">Your current services</b>
        <p class="muted" style="font-size:.85rem;margin:.2rem 0 .6rem">
          Everything on the menu now, ready to edit and import back. Blank cells leave a
          field alone, so you can delete rows you are not changing.
        </p>
        <div data-slot="live-actions"></div>
      </div>

      <div class="mb-2" style="border-top:1px solid var(--line);padding-top:1rem">
        <b style="font-size:.92rem">Blank template with examples</b>
        <p class="muted" style="font-size:.85rem;margin:.2rem 0 .6rem">
          Just the column headings and two example rows to copy the shape of.
          Delete the EXAMPLE rows before importing.
        </p>
        <div data-slot="blank-actions"></div>
      </div>

      <ol class="muted" style="font-size:.87rem;padding-left:1.2rem;line-height:1.7;margin:1rem 0 0">
        <li>Open <b>sheets.google.com</b> and create a blank spreadsheet.</li>
        <li>Click cell <b>A1</b> and paste. The grid fills across all the columns.</li>
        <li>Edit it, then <b>File → Download → Comma-separated values</b> and upload
            that file below.</li>
      </ol>

      <p class="muted" style="font-size:.83rem;margin:.75rem 0 0">
        Pasting cannot carry a line break or a tab inside a description — those become
        single spaces. If your descriptions have line breaks, use <b>Download CSV</b>
        instead and in Google Sheets choose <b>File → Import → Upload → Replace
        spreadsheet</b>, which keeps everything exactly.
      </p>

      <pre data-slot="manual" hidden style="overflow:auto;max-height:180px;
           background:var(--surface-2);border:1px solid var(--line);border-radius:var(--r);
           padding:.75rem;font-size:.75rem;margin:.75rem 0 0"></pre>

      <div data-slot="reference" class="mt-3"></div>
    </div>
  `);

  const notes        = panel.querySelector('[data-slot="notes"]');
  const liveActions  = panel.querySelector('[data-slot="live-actions"]');
  const blankActions = panel.querySelector('[data-slot="blank-actions"]');
  const manual       = panel.querySelector('[data-slot="manual"]');

  panel.querySelector('[data-slot="reference"]').appendChild(
    valuesReference(categories, icons)
  );

  /** At most one warning at a time, so the latest is never buried. */
  const setNote = (message) => {
    notes.replaceChildren();
    if (message) notes.appendChild(el(`<div class="form-error">${esc(message)}</div>`));
  };

  const showManual = (text) => {
    manual.textContent = text;
    manual.hidden = false;
  };

  // Started as soon as the panel opens, not when a button is pressed:
  // navigator.clipboard needs a live user gesture in Safari, and awaiting a
  // five-request paging loop inside the handler would expire it.
  const servicesPromise = fetchAllServices().catch((error) => ({ error }));

  // --- current services -------------------------------------------
  const copyLive = el(
    `<button type="button" class="btn btn--sm">${icon('i-copy', 14)} Copy current services</button>`
  );
  const downloadLive = el(
    `<button type="button" class="btn btn--sm btn--ghost" style="margin-left:.4rem">${
      icon('i-upload', 14)} Download CSV</button>`
  );

  const withLiveRows = async (button, handler) => {
    await withBusy(button, async () => {
      const result = await servicesPromise;

      if (result.error) {
        setNote('Your services could not be loaded: ' + (result.error.message || 'unknown error'));
        return;
      }

      const { header, rows, missingCategory } = buildRows(columns, result.services);

      if (rows.length === 0) {
        setNote('There are no services to export yet. Use the blank template instead.');
        return;
      }

      await handler({ header, rows, missingCategory, total: result.total });
    });
  };

  copyLive.addEventListener('click', () => withLiveRows(copyLive, async ({ header, rows, missingCategory, total }) => {
    const { text, hits } = toTsv(header, rows);

    if (!await copyToClipboard(text, showManual)) return;

    setNote(flattenNote(hits) || overCapNote(total) || missingCategoryNote(missingCategory));

    // A clean copy says so — silence after a previous warning is ambiguous.
    if (hits.length === 0) {
      notify.ok(`Copied ${rows.length} services. Open a blank Google Sheet, click A1 and paste.`);
    }
  }));

  downloadLive.addEventListener('click', () => withLiveRows(downloadLive, ({ header, rows, missingCategory, total }) => {
    downloadText(
      toCsv(header, rows),
      'majesty-services-' + new Date().toISOString().slice(0, 10) + '.csv',
      'text/csv;charset=utf-8'
    );
    setNote(overCapNote(total) || missingCategoryNote(missingCategory));
    notify.ok(`Downloaded ${rows.length} services.`);
  }));

  liveActions.append(copyLive, downloadLive);

  // --- blank template ---------------------------------------------
  const blankRows = () => ({
    header: columns.map((column) => column.key),
    rows: exampleRows(columns, categories, icons),
  });

  const copyBlank = el(
    `<button type="button" class="btn btn--sm">${icon('i-copy', 14)} Copy blank template</button>`
  );
  const downloadBlank = el(
    `<button type="button" class="btn btn--sm btn--ghost" style="margin-left:.4rem">${
      icon('i-upload', 14)} Download CSV</button>`
  );

  copyBlank.addEventListener('click', async () => {
    const { header, rows } = blankRows();
    const { text } = toTsv(header, rows);

    if (await copyToClipboard(text, showManual)) {
      setNote(noCategoriesNote(categories));
      notify.ok('Copied. Open a blank Google Sheet, click A1 and paste.');
    }
  });

  downloadBlank.addEventListener('click', () => {
    const { header, rows } = blankRows();
    downloadText(toCsv(header, rows), 'majesty-services-template.csv', 'text/csv;charset=utf-8');
    setNote(noCategoriesNote(categories));
    notify.ok('Template downloaded.');
  });

  blankActions.append(copyBlank, downloadBlank);

  // A warning the operator should see before pressing anything.
  setNote(noCategoriesNote(categories));

  return panel;
}

// =================================================================
// Warnings
// =================================================================

function missingCategoryNote(names) {
  if (!names.length) return '';

  const shown = names.slice(0, 5).join(', ');
  const more  = names.length > 5 ? ` and ${names.length - 5} more` : '';

  return `${names.length} service${names.length === 1 ? ' has' : 's have'} no category `
    + `(${shown}${more}) — their category was deleted. Those rows will be rejected on import. `
    + 'Restore the category under Content → Categories, or type a category name into that cell.';
}

function overCapNote(total) {
  if (total <= IMPORT_MAX_ROWS) return '';

  return `You have ${total} services, and one import file can hold ${IMPORT_MAX_ROWS} rows. `
    + 'Split it into two sheets, or delete the rows you are not changing — a partial sheet '
    + 'is safe, because rows that are not there are simply left alone.';
}

function noCategoriesNote(categories) {
  if (categories.length) return '';

  return 'No categories exist yet — create one under Content → Categories first, '
    + 'or the import will reject every row.';
}

/** The consequence, not just the fact: the danger is the re-import, not the paste. */
function flattenNote(hits) {
  if (!hits.length) return '';

  const columns = [...new Set(hits.map((hit) => hit.column))].join(', ');

  return `Copied, but ${hits.length} cell${hits.length === 1 ? ' was' : 's were'} flattened — `
    + `line breaks and tabs in ${columns} became spaces. Importing this sheet would write the `
    + 'flattened text back over those descriptions. To keep them, use Download CSV and then '
    + 'File → Import → Upload → Replace spreadsheet in Google Sheets.';
}

// =================================================================
// Fetching
// =================================================================

async function fetchAllServices() {
  const services = [];
  let page = 1;
  let total = 0;
  let lastPage = 1;

  do {
    // sort=id so two exports a minute apart diff cleanly, rather than
    // reshuffling when someone reorders the menu.
    const { data, meta } = await api.list('/services', {
      page, per_page: PER_PAGE, sort: 'id', direction: 'asc',
    });

    services.push(...data);

    total    = meta ? meta.total : services.length;
    lastPage = meta ? meta.last_page : 1;
    page += 1;
  } while (page <= lastPage && page <= MAX_PAGES && services.length < total);

  return { services, total, truncated: services.length < total };
}

// =================================================================
// Row building
// =================================================================

function buildRows(columns, services) {
  const header = columns.map((column) => column.key);
  const missingCategory = [];

  const rows = services.map((service) => {
    if (!service.category_name) missingCategory.push(service.name);
    return header.map((key) => cellValue(key, service));
  });

  return { header, rows, missingCategory };
}

/**
 * One cell, always a string.
 *
 * The String() is load-bearing rather than decorative: price arrives as a JSON
 * number, and an escaper calling .replace() on it would throw.
 */
function cellValue(key, service) {
  // The only renamed key. headerAliases() maps category_name back, but the
  // reference table and the importer's error messages both say "category".
  if (key === 'category') return String(service.category_name ?? '');

  // A JSON boolean; the importer wants yes/no.
  if (key === 'featured') return service.featured ? 'yes' : 'no';

  if (['price', 'promo_price', 'duration_minutes', 'most_loved_rank', 'display_order'].includes(key)) {
    return numText(service[key]);
  }

  return String(service[key] ?? '');
}

/**
 * Plain digits, never money(). "$1,250.00" would parse on the way back in, but
 * it lands in Sheets as locale-formatted text and re-exports unpredictably.
 */
function numText(value) {
  if (value === null || value === undefined || value === '') return '';
  const number = Number(value);
  return Number.isFinite(number) ? String(number) : '';
}

/**
 * Examples built from the live vocabularies rather than invented ones — a
 * hardcoded "Massages" category may not exist here, which would make the
 * template fail its own first import.
 */
function exampleRows(columns, categories, icons) {
  const preferred = categories.find((category) => category.status === 'active') || categories[0];
  const categoryName = preferred ? preferred.name : '';

  const iconKeys = icons.map((choice) => choice.key);
  const pickIcon = (wanted) => (iconKeys.includes(wanted) ? wanted : (iconKeys[0] || ''));

  const seed = [
    {
      name: 'EXAMPLE — Hot Stone Massage', price: '165', duration_minutes: '80',
      icon_key: pickIcon('i-stone'),
      short_description: 'Warm basalt stones melt deep tension.',
    },
    {
      name: 'EXAMPLE — Signature Facial', price: '140', duration_minutes: '60',
      icon_key: pickIcon('i-drop'),
      short_description: 'A tailored deep-cleansing facial.',
    },
  ];

  return seed.map((row) => columns.map(({ key }) => {
    if (key === 'category') return categoryName;
    // Imported untouched by accident, these cannot reach the public website.
    if (key === 'status') return 'inactive';
    if (key === 'featured') return 'no';
    return String(row[key] ?? '');
  }));
}

// =================================================================
// Renderers
// =================================================================

function toCsv(header, rows) {
  // \r is escaped too: a lone carriage return inside a description would
  // otherwise ship unquoted and split the record.
  const escapeCell = (value) =>
    (/["\r\n,]/.test(value) ? `"${value.replace(/"/g, '""')}"` : value);

  // The BOM makes Excel read it as UTF-8, and the importer's normaliseHeader()
  // strips it again, so it round-trips.
  return '﻿'
    + [header, ...rows].map((row) => row.map(escapeCell).join(',')).join('\r\n')
    + '\r\n';
}

/**
 * Google Sheets' paste has no quoting convention at all — a tab starts a new
 * column and a newline starts a new row, with no escape sequence. Cells must
 * therefore be flattened, and `hits` is how the caller tells the operator that
 * it happened.
 */
function toTsv(header, rows) {
  const hits = [];

  const cleaned = rows.map((row) => row.map((value, index) => {
    const flat = value.replace(/[\t\r\n]+/g, ' ').replace(/ {2,}/g, ' ').trim();
    if (flat !== value) hits.push({ column: header[index] });
    return flat;
  }));

  return {
    text: [header, ...cleaned].map((row) => row.join('\t')).join('\n'),
    hits,
  };
}

// =================================================================
// Delivery
// =================================================================

function downloadText(text, filename, mime) {
  const url = URL.createObjectURL(new Blob([text], { type: mime }));
  const link = el(`<a href="${esc(url)}" download="${esc(filename)}" style="display:none"></a>`);

  document.body.appendChild(link);
  link.click();
  link.remove();

  URL.revokeObjectURL(url);
}

/** @return {Promise<boolean>} whether the clipboard actually took it */
async function copyToClipboard(text, onManualFallback) {
  try {
    await navigator.clipboard.writeText(text);
    return true;
  } catch {
    // Needs a secure context, so plain http:// fails here. The <pre> below is
    // the manual route rather than a dead end.
    notify.warn('Could not copy automatically — select the text below and copy it.');
    onManualFallback(text);
    return false;
  }
}

// =================================================================
// The valid-values reference
//
// Google data-validation dropdowns need the API, so the guide is on screen
// instead: what an operator may legally type into the two columns that have a
// fixed vocabulary.
// =================================================================

function valuesReference(categories, icons) {
  const wrap = el('<div></div>');

  wrap.appendChild(el(`
    <b style="font-size:.92rem">What you can type</b>
    <p class="muted" style="font-size:.85rem;margin:.2rem 0 .6rem">
      The <code>category</code> and <code>icon_key</code> columns only accept these values.
    </p>
  `));

  if (!categories.length) {
    wrap.appendChild(emptyState({
      title: 'No categories yet',
      message: 'Create one under Content → Categories — every imported row needs one.',
      iconName: 'i-folder',
    }));
  } else {
    const table = el(`
      <div class="table-wrap">
        <table class="data">
          <thead><tr><th>Category</th><th>Or type this</th><th>Status</th></tr></thead>
          <tbody></tbody>
        </table>
      </div>
    `);

    const tbody = table.querySelector('tbody');

    categories.forEach((category) => {
      const isActive = category.status === 'active';

      tbody.appendChild(el(`
        <tr>
          <td data-label="Category"><span class="cell-title">${esc(category.name)}</span></td>
          <td data-label="Or type this"><code>${esc(category.slug)}</code></td>
          <td data-label="Status"><span class="pill ${isActive ? 'pill--ok' : 'pill--plain'}">${
            isActive ? 'Active' : 'Inactive'}</span></td>
        </tr>
      `));
    });

    wrap.appendChild(table);

    // Without this the Inactive pill reads as "unusable", which is wrong —
    // assertCategoryExists() checks deleted_at only, not status.
    wrap.appendChild(el(`
      <p class="muted" style="font-size:.83rem;margin:.6rem 0 0">
        An inactive category still works for importing — the service just will not show on
        the website until that category is active again.
      </p>
    `));
  }

  if (icons.length) {
    wrap.appendChild(el(`
      <p class="muted" style="font-size:.85rem;margin:1rem 0 .4rem">
        Icons for the <code>icon_key</code> column:
      </p>
    `));

    const list = el('<ul class="list-plain"></ul>');

    icons.forEach((choice) => {
      list.appendChild(el(`
        <li><code>${esc(choice.key)}</code><span class="pill pill--plain">${esc(choice.label)}</span></li>
      `));
    });

    wrap.appendChild(list);
  }

  return wrap;
}
