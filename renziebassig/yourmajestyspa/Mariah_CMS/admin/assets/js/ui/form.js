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
// Error handling
// ---------------------------------------------------------------

export function clearErrors(form) {
  form.querySelectorAll('.field.has-error').forEach((node) => node.classList.remove('has-error'));
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
