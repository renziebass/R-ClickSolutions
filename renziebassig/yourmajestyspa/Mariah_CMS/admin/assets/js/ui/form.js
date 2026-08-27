/**
 * Form field builders and server-error binding.
 *
 * Validation messages come from the server's `error.fields` map, keyed by
 * column name, so the client and server never disagree about what is valid.
 */

import { el, esc, formValues } from './dom.js';

export function field({
  name, label, type = 'text', value = '', required = false, hint = '',
  placeholder = '', step, min, max, span = 6, dataType, prefix,
}) {
  const attributes = [
    `name="${esc(name)}"`,
    `id="f-${esc(name)}"`,
    `type="${esc(type)}"`,
    `value="${esc(value ?? '')}"`,
    placeholder ? `placeholder="${esc(placeholder)}"` : '',
    step !== undefined ? `step="${esc(step)}"` : '',
    min !== undefined ? `min="${esc(min)}"` : '',
    max !== undefined ? `max="${esc(max)}"` : '',
    dataType ? `data-type="${esc(dataType)}"` : '',
  ].filter(Boolean).join(' ');

  const input = prefix
    ? `<div class="input-prefix"><span>${esc(prefix)}</span><input ${attributes}></div>`
    : `<input ${attributes}>`;

  return el(`
    <div class="field col-${span}" data-field="${esc(name)}">
      <label for="f-${esc(name)}">${esc(label)}${required ? '<span class="field__req">*</span>' : ''}</label>
      ${input}
      ${hint ? `<small class="field__hint">${esc(hint)}</small>` : ''}
    </div>
  `);
}

export function textarea({ name, label, value = '', rows = 4, required = false, hint = '', span = 12, placeholder = '' }) {
  return el(`
    <div class="field col-${span}" data-field="${esc(name)}">
      <label for="f-${esc(name)}">${esc(label)}${required ? '<span class="field__req">*</span>' : ''}</label>
      <textarea name="${esc(name)}" id="f-${esc(name)}" rows="${rows}"
        placeholder="${esc(placeholder)}">${esc(value ?? '')}</textarea>
      ${hint ? `<small class="field__hint">${esc(hint)}</small>` : ''}
    </div>
  `);
}

// ---------------------------------------------------------------
// Rich text
// ---------------------------------------------------------------

/**
 * The brand palette the editor offers. These exact values are what
 * HtmlSanitizer maps onto classes; anything else it strips, so the swatches
 * here and the PHP constants have to stay in step.
 */
const RTE_TEXT_COLOURS = [
  { hex: '#0F3D3E', className: 'rte-c-emerald', label: 'Emerald' },
  { hex: '#A8862A', className: 'rte-c-gold', label: 'Gold' },
  { hex: '#6A6A66', className: 'rte-c-soft', label: 'Muted' },
];

const RTE_HIGHLIGHTS = [
  { hex: '#E7CE7E', className: 'rte-h-gold', label: 'Gold' },
  { hex: '#CFE6DD', className: 'rte-h-mint', label: 'Mint' },
];

const RTE_URL_OK = /^(https?:|mailto:|tel:|\/|#)/i;

/**
 * A formatting toolbar over a contenteditable surface, for long-form copy.
 *
 * Uses document.execCommand. It is deprecated and nothing has replaced it —
 * the alternatives are a bundled library, and this project has no build step,
 * no npm and no dependencies by design. Every browser still implements it.
 *
 * The editable div carries NO `name`, because formValues() (ui/dom.js)
 * serialises by [name] and would read `.value` off a div and get undefined.
 * Pages collect it with readRichText() in their `transform` hook, the same way
 * repeater() is collected.
 *
 * Nothing here is a security control. The markup this produces is rewritten
 * from an allowlist by HtmlSanitizer on the server; the client cannot be
 * trusted to sanitise itself.
 */
export function richText({
  name, label, value = '', required = false, hint = '', span = 12,
  minHeight = '14rem', placeholder = '',
}) {
  const node = el(`
    <div class="field col-${span}" data-field="${esc(name)}">
      <label for="f-${esc(name)}">${esc(label)}${required ? '<span class="field__req">*</span>' : ''}</label>
      <div class="rte-wrap">
        <div class="rte-toolbar" role="toolbar" aria-label="${esc(label)} formatting"></div>
        <div class="rte" id="f-${esc(name)}" data-rte="${esc(name)}"
             contenteditable="true" role="textbox" aria-multiline="true"
             data-placeholder="${esc(placeholder)}"
             style="min-height:${esc(minHeight)}"></div>
      </div>
      ${hint ? `<small class="field__hint">${esc(hint)}</small>` : ''}
    </div>
  `);

  const surface = node.querySelector('.rte');
  const toolbar = node.querySelector('.rte-toolbar');

  // Stored HTML is already sanitised — it could not have reached the database
  // otherwise — and this is the admin, not the public page.
  surface.innerHTML = value || '';

  // Without this, execCommand emits <font color> instead of a style, and the
  // sanitiser has no colour to map onto a class.
  try { document.execCommand('styleWithCSS', false, true); } catch (e) { /* older engines */ }

  const run = (command, argument = null) => {
    surface.focus();
    try {
      document.execCommand(command, false, argument);
    } catch (e) { /* unsupported command — the button simply does nothing */ }
  };

  const button = (title, html, onClick) => {
    const btn = el(
      `<button type="button" class="rte-btn" title="${esc(title)}" aria-label="${esc(title)}">${html}</button>`
    );
    // mousedown, not click: the surface loses its selection the moment the
    // button takes focus, and execCommand works on the selection.
    btn.addEventListener('mousedown', (event) => {
      event.preventDefault();
      onClick();
    });
    return btn;
  };

  const divider = () => el('<span class="rte-sep" aria-hidden="true"></span>');

  toolbar.appendChild(button('Bold', '<b>B</b>', () => run('bold')));
  toolbar.appendChild(button('Italic', '<i>I</i>', () => run('italic')));
  toolbar.appendChild(button('Underline', '<u>U</u>', () => run('underline')));
  toolbar.appendChild(divider());

  toolbar.appendChild(swatches('Text colour', 'A', RTE_TEXT_COLOURS, (hex) => run('foreColor', hex)));
  toolbar.appendChild(swatches('Highlight', '<span class="rte-hl">A</span>', RTE_HIGHLIGHTS, (hex) => {
    // hiliteColor is the standard name; backColor is what old WebKit answers to.
    surface.focus();
    try {
      if (!document.execCommand('hiliteColor', false, hex)) {
        document.execCommand('backColor', false, hex);
      }
    } catch (e) { /* neither supported */ }
  }));
  toolbar.appendChild(divider());

  toolbar.appendChild(button('Link', '🔗', () => {
    const url = (window.prompt('Link address', 'https://') || '').trim();
    if (!url) return;
    // Checked here as well as on the server, so a mistyped link says so now
    // rather than vanishing silently on save.
    if (!RTE_URL_OK.test(url)) {
      notifyInvalidUrl(node);
      return;
    }
    run('createLink', url);
  }));
  toolbar.appendChild(button('Remove link', '⛓', () => run('unlink')));
  toolbar.appendChild(divider());

  toolbar.appendChild(button('Bulleted list', '•≡', () => run('insertUnorderedList')));
  toolbar.appendChild(button('Numbered list', '1≡', () => run('insertOrderedList')));
  toolbar.appendChild(divider());

  toolbar.appendChild(button('Left to right', '⇥', () => setDirection(surface, 'ltr')));
  toolbar.appendChild(button('Right to left', '⇤', () => setDirection(surface, 'rtl')));

  return node;
}

/** A button that opens a small row of fixed colour swatches. */
function swatches(title, faceHtml, palette, apply) {
  const wrap = el(`
    <span class="rte-swatch">
      <button type="button" class="rte-btn" title="${esc(title)}" aria-label="${esc(title)}"
        aria-expanded="false">${faceHtml}</button>
      <span class="rte-swatch__menu" hidden></span>
    </span>
  `);

  const trigger = wrap.querySelector('button');
  const menu = wrap.querySelector('.rte-swatch__menu');

  palette.forEach((colour) => {
    const dot = el(
      `<button type="button" class="rte-dot" title="${esc(colour.label)}"
        aria-label="${esc(colour.label)}" style="background:${esc(colour.hex)}"></button>`
    );
    dot.addEventListener('mousedown', (event) => {
      event.preventDefault();
      apply(colour.hex);
      menu.hidden = true;
      trigger.setAttribute('aria-expanded', 'false');
    });
    menu.appendChild(dot);
  });

  const clear = el('<button type="button" class="rte-dot rte-dot--none" title="None">✕</button>');
  clear.addEventListener('mousedown', (event) => {
    event.preventDefault();
    // An off-palette colour is stripped by the sanitiser, so "none" is just a
    // colour it will never keep.
    apply('#000001');
    menu.hidden = true;
  });
  menu.appendChild(clear);

  trigger.addEventListener('mousedown', (event) => {
    event.preventDefault();
    menu.hidden = !menu.hidden;
    trigger.setAttribute('aria-expanded', menu.hidden ? 'false' : 'true');
  });

  return wrap;
}

/**
 * Sets the direction of the block the cursor is in.
 *
 * execCommand has no direction command that works across engines, so this
 * walks up to the nearest block and sets `dir` — which is the one attribute
 * the sanitiser keeps on a block element.
 */
function setDirection(surface, direction) {
  const selection = window.getSelection();
  if (!selection || selection.rangeCount === 0) {
    surface.setAttribute('dir', direction);
    return;
  }

  let node = selection.getRangeAt(0).startContainer;
  if (node.nodeType === Node.TEXT_NODE) node = node.parentNode;

  const blocks = ['P', 'H2', 'H3', 'LI', 'UL', 'OL'];

  while (node && node !== surface && !blocks.includes(node.nodeName)) {
    node = node.parentNode;
  }

  // Nothing typed yet, or bare text: the surface itself is the only block.
  (node && node !== surface ? node : surface).setAttribute('dir', direction);
}

function notifyInvalidUrl(fieldNode) {
  const existing = fieldNode.querySelector('.field__error');
  if (existing) existing.remove();
  fieldNode.classList.add('has-error');
  fieldNode.appendChild(el(
    '<small class="field__error">Links must start with https://, mailto: or tel:.</small>'
  ));
}

/**
 * Reads one editor's HTML out of a form. Returns null when the editor is not
 * on the page, so the caller can leave the key off the payload entirely.
 */
export function readRichText(form, name) {
  const surface = form.querySelector(`[data-rte="${CSS.escape(name)}"]`);
  if (!surface) return null;

  const html = surface.innerHTML.trim();

  // What an empty contenteditable leaves behind. Empty should mean empty, or
  // every "cleared" field stores a paragraph of nothing.
  if (html === '' || html === '<br>' || html === '<p><br></p>' || html === '<div><br></div>') {
    return '';
  }

  return html;
}

export function select({
  name, label, value = '', options = [], required = false,
  hint = '', span = 6, placeholder = 'Select…',
}) {
  const optionHtml = options.map((option) => {
    const optionValue = option.value ?? option.id ?? '';
    const optionLabel = option.label ?? option.name ?? option.title ?? '';
    const selected = String(optionValue) === String(value ?? '') ? ' selected' : '';
    return `<option value="${esc(optionValue)}"${selected}>${esc(optionLabel)}</option>`;
  }).join('');

  return el(`
    <div class="field col-${span}" data-field="${esc(name)}">
      <label for="f-${esc(name)}">${esc(label)}${required ? '<span class="field__req">*</span>' : ''}</label>
      <select name="${esc(name)}" id="f-${esc(name)}">
        ${placeholder ? `<option value="">${esc(placeholder)}</option>` : ''}
        ${optionHtml}
      </select>
      ${hint ? `<small class="field__hint">${esc(hint)}</small>` : ''}
    </div>
  `);
}

export function switchField({ name, label, hint = '', checked = false, span = 6 }) {
  return el(`
    <div class="field col-${span}" data-field="${esc(name)}">
      <label class="switch" style="margin-top:1.55rem">
        <input type="checkbox" name="${esc(name)}"${checked ? ' checked' : ''}>
        <span class="switch__track"></span>
        <span class="switch__text">${esc(label)}${hint ? `<small>${esc(hint)}</small>` : ''}</span>
      </label>
    </div>
  `);
}

export function section(title, description = '') {
  return el(`
    <div class="form-section">
      <div class="form-section__head">
        <h3>${esc(title)}</h3>
        ${description ? `<p>${esc(description)}</p>` : ''}
      </div>
      <div class="grid"></div>
    </div>
  `);
}

/** Appends fields into a section built by `section()`. */
export function fill(sectionNode, ...fields) {
  const grid = sectionNode.querySelector('.grid');
  fields.filter(Boolean).forEach((node) => grid.appendChild(node));
  return sectionNode;
}

// ---------------------------------------------------------------
// Repeating rows
// ---------------------------------------------------------------

/**
 * A variable-length set of rows — price tiers, and anything later that needs
 * "add another".
 *
 * The inputs deliberately carry NO `name` attribute. formValues() serialises a
 * form by [name] into a flat last-wins object, so ten rows sharing a name
 * would collapse to one value. Instead each cell is tagged
 * data-repeat="<name>" / data-key="<column>", and readRepeater() collects them
 * in the page's `transform` hook — the same technique promotions.js and
 * roles.js already use to keep their checkbox lists away from formValues().
 *
 * Errors are keyed "<name>.<index>.<column>" by the server, and each cell
 * carries a matching data-field, so applyErrors() paints them with no changes
 * of its own.
 *
 * @param columns {key, label, type, span, placeholder, step}[]
 * @param rows    existing values, one object per row
 */
export function repeater({
  name, label, hint = '', columns, rows = [], addLabel = 'Add another', span = 12,
}) {
  const node = el(`
    <div class="field col-${span}" data-repeat-group="${esc(name)}">
      <label>${esc(label)}</label>
      ${hint ? `<small class="field__hint">${esc(hint)}</small>` : ''}
      <div class="repeat" data-repeat-body="${esc(name)}"></div>
      <button type="button" class="btn btn--ghost btn--sm repeat__add">${esc(addLabel)}</button>
    </div>
  `);

  const body = node.querySelector('[data-repeat-body]');

  // One grid template for the header and every row, so the columns line up.
  // Set on the container; both inherit it.
  body.style.setProperty(
    '--repeat-cols',
    columns.map((column) => `${column.span || 1}fr`).join(' ')
  );

  const buildRow = (values = {}) => {
    const row = el('<div class="repeat__row"></div>');

    columns.forEach((column) => {
      const value = values[column.key] ?? '';
      row.appendChild(el(`
        <div class="repeat__cell">
          <input type="${esc(column.type || 'text')}"
            data-repeat="${esc(name)}" data-key="${esc(column.key)}"
            value="${esc(value)}"
            placeholder="${esc(column.placeholder || column.label)}"
            aria-label="${esc(column.label)}"
            ${column.step ? `step="${esc(column.step)}"` : ''}>
        </div>
      `));
    });

    const remove = el('<button type="button" class="repeat__remove" aria-label="Remove this row">&times;</button>');
    remove.addEventListener('click', () => {
      row.remove();
      reindex();
    });
    row.appendChild(remove);

    return row;
  };

  // data-field has to track position, because that is what the server keys its
  // errors by. Recomputed whenever a row is added or removed.
  //
  // Selects .repeat__row rather than body.children: the header is a child too,
  // and counting it would shift every index by one against the server's.
  const reindex = () => {
    [...body.querySelectorAll('.repeat__row')].forEach((row, index) => {
      row.querySelectorAll('[data-key]').forEach((input) => {
        input.closest('.repeat__cell')
          .setAttribute('data-field', `${name}.${index}.${input.dataset.key}`);
      });
    });
  };

  const header = el('<div class="repeat__head"></div>');
  columns.forEach((column) => {
    header.appendChild(el(`<span>${esc(column.label)}</span>`));
  });
  header.appendChild(el('<span></span>'));
  body.appendChild(header);

  rows.forEach((row) => body.appendChild(buildRow(row)));

  if (rows.length === 0) body.appendChild(buildRow());

  node.querySelector('.repeat__add').addEventListener('click', () => {
    body.appendChild(buildRow());
    reindex();
  });

  reindex();

  return node;
}

/**
 * Collects a repeater's rows out of a form, in document order. Call from the
 * page's `transform` hook; returns null when the repeater is not on the page,
 * so the caller can leave the key off the payload entirely and the server
 * treats the children as untouched.
 */
export function readRepeater(form, name) {
  const inputs = [...form.querySelectorAll(`[data-repeat="${CSS.escape(name)}"]`)];
  if (inputs.length === 0) return null;

  const rows = [];

  [...form.querySelectorAll(`[data-repeat-body="${CSS.escape(name)}"] .repeat__row`)]
    .forEach((rowNode) => {
      const row = {};
      let filled = false;

      rowNode.querySelectorAll('[data-key]').forEach((input) => {
        const value = input.value.trim();
        row[input.dataset.key] = value === '' ? null : value;
        if (value !== '') filled = true;
      });

      // An untouched blank row is not an empty tier, it is no tier.
      if (!filled) return;

      // Because blank rows are dropped, a row's position in the payload is not
      // its position in the DOM. The server keys its errors by payload index,
      // so re-stamp data-field to match what is actually being sent —
      // otherwise a message lands on the wrong row.
      rowNode.querySelectorAll('[data-key]').forEach((input) => {
        input.closest('.repeat__cell')
          .setAttribute('data-field', `${name}.${rows.length}.${input.dataset.key}`);
      });

      rows.push(row);
    });

  return rows;
}

// ---------------------------------------------------------------
// Error handling
// ---------------------------------------------------------------

export function clearErrors(form) {
  // Not `.field.has-error`: a repeater cell is a .repeat__cell, and leaving its
  // class behind would keep the last failure highlighted after a good save.
  form.querySelectorAll('.has-error').forEach((node) => node.classList.remove('has-error'));
  form.querySelectorAll('.field__error').forEach((node) => node.remove());

  const banner = form.querySelector('.form-error');
  if (banner) banner.remove();
}

/**
 * Paints server validation errors onto their fields and shows a banner for
 * anything that has no matching input (cross-field rules, conflicts).
 */
export function applyErrors(form, apiError) {
  clearErrors(form);

  const fields = apiError.fields || {};
  const unmatched = [];
  let firstErrorNode = null;

  Object.entries(fields).forEach(([name, message]) => {
    if (message === undefined || message === null || message === '') return;

    const wrapper = form.querySelector(`[data-field="${CSS.escape(name)}"]`);

    if (!wrapper) {
      unmatched.push(message);
      return;
    }

    wrapper.classList.add('has-error');
    wrapper.appendChild(el(`<small class="field__error">${esc(message)}</small>`));

    if (!firstErrorNode) firstErrorNode = wrapper;
  });

  const bannerMessages = unmatched.length ? unmatched : [];
  const realFieldCount = Object.values(fields)
    .filter((m) => m !== undefined && m !== null && m !== '').length;

  if (!realFieldCount || bannerMessages.length) {
    const text = bannerMessages.length ? bannerMessages.join(' ') : apiError.message;
    form.prepend(el(`<div class="form-error">${esc(text)}</div>`));
  }

  const scrollTarget = form.querySelector('.form-error') || firstErrorNode;
  if (scrollTarget) scrollTarget.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

/** Wires live slug preview from a title field. */
export function bindSlugPreview(form, sourceName, slugName) {
  const source = form.querySelector(`[name="${sourceName}"]`);
  const slug = form.querySelector(`[name="${slugName}"]`);
  if (!source || !slug) return;

  let touched = slug.value.trim() !== '';

  slug.addEventListener('input', () => { touched = true; });

  source.addEventListener('input', () => {
    if (touched) return;
    slug.placeholder = 'Auto: ' + source.value
      .toLowerCase()
      .replace(/['’]/g, '')
      .replace(/&/g, ' and ')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  });
}

export { formValues };
