/**
 * DOM helpers.
 *
 * `esc` is the single escaping choke point: every value that comes from the
 * database goes through it before being interpolated into an HTML string, so
 * a service name containing markup cannot execute.
 */

export function esc(value) {
  if (value === null || value === undefined) return '';
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

/** Builds an element from an HTML string. */
export function el(html) {
  const template = document.createElement('template');
  template.innerHTML = html.trim();
  return template.content.firstElementChild;
}

export function icon(name, size = 18) {
  return `<svg class="icon" width="${size}" height="${size}" aria-hidden="true"><use href="#${esc(name)}"/></svg>`;
}

export function money(value) {
  if (value === null || value === undefined || value === '') return '—';
  const number = Number(value);
  if (Number.isNaN(number)) return esc(value);
  return '$' + number.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

export function dateLabel(value) {
  if (!value) return '—';
  const date = new Date(String(value).replace(' ', 'T'));
  if (Number.isNaN(date.getTime())) return esc(value);
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

export function dateTimeLabel(value) {
  if (!value) return '—';
  const date = new Date(String(value).replace(' ', 'T'));
  if (Number.isNaN(date.getTime())) return esc(value);
  return date.toLocaleString('en-US', {
    month: 'short', day: 'numeric', year: 'numeric',
    hour: 'numeric', minute: '2-digit',
  });
}

/** "3 hours ago" for activity feeds; falls back to a date past a week. */
export function relativeTime(value) {
  if (!value) return '';
  const date = new Date(String(value).replace(' ', 'T'));
  if (Number.isNaN(date.getTime())) return '';

  const seconds = Math.floor((Date.now() - date.getTime()) / 1000);
  if (seconds < 60) return 'just now';
  if (seconds < 3600) return `${Math.floor(seconds / 60)} min ago`;
  if (seconds < 86400) {
    const hours = Math.floor(seconds / 3600);
    return `${hours} hour${hours === 1 ? '' : 's'} ago`;
  }
  if (seconds < 604800) {
    const days = Math.floor(seconds / 86400);
    return `${days} day${days === 1 ? '' : 's'} ago`;
  }
  return dateLabel(value);
}

export function initials(name) {
  return String(name || '?')
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0].toUpperCase())
    .join('');
}

/** Debounce for search inputs so each keystroke does not hit the API. */
export function debounce(fn, wait = 300) {
  let timer = null;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn(...args), wait);
  };
}

export function slugify(text) {
  return String(text || '')
    .toLowerCase()
    .replace(/['’]/g, '')
    .replace(/&/g, ' and ')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
}

/** Serialises a <form> into a plain object, honouring data-type hints. */
export function formValues(form) {
  const values = {};

  form.querySelectorAll('[name]').forEach((input) => {
    const key = input.name;

    if (input.type === 'checkbox') {
      values[key] = input.checked ? 1 : 0;
      return;
    }

    let value = input.value;

    if (typeof value === 'string') value = value.trim();

    if (value === '') {
      values[key] = input.dataset.emptyAsNull === 'false' ? '' : null;
      return;
    }

    if (input.dataset.type === 'number') {
      values[key] = Number(value);
      return;
    }

    values[key] = value;
  });

  return values;
}
