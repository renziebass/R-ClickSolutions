/** Toasts, confirmation dialogs, modals, and shared empty/error/loading states. */

import { el, esc, icon } from './dom.js';

// ---------------------------------------------------------------
// Toasts
// ---------------------------------------------------------------
function toastHost() {
  let host = document.getElementById('toasts');
  if (!host) {
    host = el('<div class="toasts" id="toasts" role="status" aria-live="polite"></div>');
    document.body.appendChild(host);
  }
  return host;
}

export function toast(message, variant = 'ok', timeout = 4200) {
  const node = el(`
    <div class="toast toast--${esc(variant)}">
      <span>${esc(message)}</span>
      <button class="toast__close" aria-label="Dismiss">&times;</button>
    </div>
  `);

  const remove = () => node.remove();
  node.querySelector('.toast__close').addEventListener('click', remove);
  toastHost().appendChild(node);

  if (timeout) setTimeout(remove, timeout);

  return node;
}

export const notify = {
  ok: (m) => toast(m, 'ok'),
  error: (m) => toast(m, 'error', 6500),
  warn: (m) => toast(m, 'warn', 5500),
};

// ---------------------------------------------------------------
// Modal
// ---------------------------------------------------------------

/**
 * Opens a modal. `render(body, close)` fills the body; the returned promise
 * resolves with whatever `close(value)` is called with.
 */
export function modal({ title, subtitle, render, footer, wide = false }) {
  return new Promise((resolve) => {
    const scrim = el(`
      <div class="modal-scrim" role="dialog" aria-modal="true">
        <div class="modal${wide ? ' modal--wide' : ''}">
          <div class="modal__head">
            <div>
              <h3>${esc(title)}</h3>
              ${subtitle ? `<p>${esc(subtitle)}</p>` : ''}
            </div>
            <button class="modal__close" aria-label="Close">${icon('i-x', 20)}</button>
          </div>
          <div class="modal__body"></div>
          <div class="modal__foot"></div>
        </div>
      </div>
    `);

    const body = scrim.querySelector('.modal__body');
    const foot = scrim.querySelector('.modal__foot');

    let settled = false;
    const close = (value) => {
      if (settled) return;
      settled = true;
      document.removeEventListener('keydown', onKey);
      document.body.style.overflow = '';
      scrim.remove();
      resolve(value);
    };

    const onKey = (event) => {
      if (event.key === 'Escape') close(undefined);
    };

    scrim.querySelector('.modal__close').addEventListener('click', () => close(undefined));
    scrim.addEventListener('mousedown', (event) => {
      if (event.target === scrim) close(undefined);
    });
    document.addEventListener('keydown', onKey);

    if (render) render(body, close);

    if (footer) {
      footer(foot, close);
    } else {
      foot.remove();
    }

    document.body.style.overflow = 'hidden';
    document.body.appendChild(scrim);

    const focusTarget = body.querySelector('input, select, textarea, button')
      || scrim.querySelector('.modal__close');
    if (focusTarget) focusTarget.focus();
  });
}

/**
 * Destructive-action confirmation. Resolves true only if the user confirms —
 * every delete in the app goes through this.
 */
export function confirmDialog({
  title = 'Are you sure?',
  message,
  confirmLabel = 'Confirm',
  cancelLabel = 'Cancel',
  danger = true,
}) {
  return modal({
    title,
    render: (body) => {
      body.innerHTML = `<p style="margin:0;color:var(--text-soft)">${esc(message)}</p>`;
    },
    footer: (foot, close) => {
      const cancel = el(`<button class="btn btn--ghost">${esc(cancelLabel)}</button>`);
      const confirm = el(
        `<button class="btn ${danger ? 'btn--danger' : ''}">${esc(confirmLabel)}</button>`
      );

      cancel.addEventListener('click', () => close(false));
      confirm.addEventListener('click', () => close(true));

      foot.append(cancel, confirm);
    },
  }).then((value) => value === true);
}

// ---------------------------------------------------------------
// States
// ---------------------------------------------------------------

export function emptyState({ title, message, actionLabel, onAction, iconName = 'i-inbox' }) {
  const node = el(`
    <div class="empty">
      <div class="empty__icon">${icon(iconName, 46)}</div>
      <h3>${esc(title)}</h3>
      <p>${esc(message)}</p>
      ${actionLabel ? `<button class="btn">${esc(actionLabel)}</button>` : ''}
    </div>
  `);

  if (actionLabel && onAction) {
    node.querySelector('button').addEventListener('click', onAction);
  }

  return node;
}

export function errorState(message, onRetry) {
  const node = el(`
    <div class="error-state">
      <h3>Something went wrong</h3>
      <p>${esc(message)}</p>
      ${onRetry ? '<button class="btn btn--ghost">Try again</button>' : ''}
    </div>
  `);

  if (onRetry) node.querySelector('button').addEventListener('click', onRetry);

  return node;
}

export function skeletonRows(count = 6) {
  return el(`<div style="padding:1.35rem">${
    Array.from({ length: count }, () => '<div class="skel skel--row"></div>').join('')
  }</div>`);
}

export function skeletonCards(count = 4) {
  return el(`<div class="stat-grid">${
    Array.from({ length: count }, () => `
      <div class="stat">
        <div class="skel" style="width:45%"></div>
        <div class="skel skel--lg mt-2" style="width:60%"></div>
      </div>
    `).join('')
  }</div>`);
}

/** Disables a button and shows progress while an async action runs. */
export async function withBusy(button, fn) {
  const original = button.innerHTML;
  button.classList.add('is-busy');
  button.disabled = true;
  button.innerHTML = 'Working…';

  try {
    return await fn();
  } finally {
    button.classList.remove('is-busy');
    button.disabled = false;
    button.innerHTML = original;
  }
}
