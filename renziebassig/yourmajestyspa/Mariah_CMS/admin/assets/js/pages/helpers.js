/** Shared page scaffolding used by every screen. */

import { api } from '../api.js';
import { navigate } from '../router.js';
import { session } from '../session.js';
import { el, esc, icon } from '../ui/dom.js';
import { applyErrors, clearErrors, formValues } from '../ui/form.js';
import { confirmDialog, notify, withBusy } from '../ui/feedback.js';

/** Page header with title, description and action buttons. */
export function pageHead({ title, description = '', actions = [] }) {
  const node = el(`
    <div class="page-head">
      <div>
        <h1>${esc(title)}</h1>
        ${description ? `<p>${esc(description)}</p>` : ''}
      </div>
      <div class="page-head__actions"></div>
    </div>
  `);

  const holder = node.querySelector('.page-head__actions');

  actions.filter(Boolean).forEach((action) => {
    const button = el(`
      <button class="btn ${action.variant ? 'btn--' + action.variant : ''}">
        ${action.iconName ? icon(action.iconName, 16) : ''} ${esc(action.label)}
      </button>
    `);
    button.addEventListener('click', () => action.onClick(button));
    holder.appendChild(button);
  });

  if (!actions.filter(Boolean).length) holder.remove();

  return node;
}

/** Active/inactive pill for simple content. */
export function statusPill(status) {
  const isActive = status === 'active';
  return `<span class="pill pill--${isActive ? 'ok' : ''}">${isActive ? 'Active' : 'Inactive'}</span>`;
}

/** Derived-state pill for promotions and specials. */
export function schedulePill(row) {
  const state = row.effective_status || 'draft';
  const variants = {
    active: 'ok',
    scheduled: 'info',
    expired: 'warn',
    draft: '',
    inactive: '',
  };
  const label = row.effective_status_label || state;
  return `<span class="pill pill--${variants[state] ?? ''}">${esc(label)}</span>`;
}

export function featuredStar(isFeatured) {
  return isFeatured
    ? `<span class="star" title="Featured">${icon('i-star-on', 16)}</span>`
    : `<span class="star star--off" title="Not featured">${icon('i-star', 16)}</span>`;
}

export function thumbCell(row, altKey = 'name') {
  if (row.image_url) {
    return `<img class="thumb" src="${esc(row.image_url)}" alt="${esc(row[altKey] || '')}" loading="lazy">`;
  }
  return `<span class="thumb thumb--empty">${icon('i-image', 16)}</span>`;
}

/**
 * Standard row actions: view/edit/duplicate/toggle/delete/restore, each shown
 * only when the signed-in role holds the matching permission.
 */
export function crudActions({
  row, base, permission, table,
  onEdit, onView, activateLabels = ['Deactivate', 'Activate'],
  statusValues = ['active', 'inactive'],
  canDuplicate = true,
}) {
  const actions = [];
  const isDeleted = Boolean(row.deleted_at);
  const title = row.name || row.title || `#${row.id}`;

  if (isDeleted) {
    if (session.can(`${permission}.delete`)) {
      actions.push({
        label: 'Restore',
        iconName: 'i-undo',
        onClick: async (r, button) => {
          await withBusy(button, async () => {
            try {
              await api.post(`${base}/${r.id}/restore`);
              notify.ok(`"${title}" was restored.`);
              table.refresh();
            } catch (error) {
              notify.error(error.message);
            }
          });
        },
      });
    }
    return actions;
  }

  if (onView) {
    actions.push({ label: 'View', iconName: 'i-eye', onClick: onView });
  }

  if (session.can(`${permission}.edit`) && onEdit) {
    actions.push({ label: 'Edit', iconName: 'i-edit', onClick: onEdit });
  }

  if (canDuplicate && session.can(`${permission}.create`)) {
    actions.push({
      label: 'Duplicate',
      iconName: 'i-copy',
      onClick: async (r, button) => {
        await withBusy(button, async () => {
          try {
            await api.post(`${base}/${r.id}/duplicate`);
            notify.ok(`"${title}" was duplicated as a draft.`);
            table.refresh();
          } catch (error) {
            notify.error(error.message);
          }
        });
      },
    });
  }

  const activatePermission = session.can(`${permission}.activate`)
    ? `${permission}.activate`
    : `${permission}.edit`;

  if (session.can(activatePermission)) {
    const [activeValue, inactiveValue] = statusValues;
    const isOn = row.status === activeValue;

    actions.push({
      label: isOn ? activateLabels[0] : activateLabels[1],
      iconName: isOn ? 'i-pause' : 'i-play',
      onClick: async (r, button) => {
        const next = isOn ? inactiveValue : activeValue;

        if (isOn) {
          const ok = await confirmDialog({
            title: `${activateLabels[0]} "${title}"?`,
            message: 'It will stop appearing on the Majesty Day Spa website immediately. '
              + 'It stays here and can be turned back on at any time.',
            confirmLabel: activateLabels[0],
            danger: false,
          });
          if (!ok) return;
        }

        await withBusy(button, async () => {
          try {
            await api.patch(`${base}/${r.id}/status`, { status: next });
            notify.ok(`"${title}" is now ${next}.`);
            table.refresh();
          } catch (error) {
            notify.error(error.message);
          }
        });
      },
    });
  }

  if (session.can(`${permission}.delete`)) {
    actions.push({
      label: 'Delete',
      iconName: 'i-trash',
      danger: true,
      onClick: async (r, button) => {
        const ok = await confirmDialog({
          title: `Delete "${title}"?`,
          message: 'It will be removed from the website and moved to deleted items. '
            + 'You can restore it later from the "Deleted items" filter.',
          confirmLabel: 'Delete',
        });
        if (!ok) return;

        await withBusy(button, async () => {
          try {
            const result = await api.del(`${base}/${r.id}`);
            notify.ok(result.data.message || `"${title}" was deleted.`);
            table.refresh();
          } catch (error) {
            notify.error(error.message);
          }
        });
      },
    });
  }

  return actions;
}

/**
 * Wires a form's submit to POST/PUT, applying server field errors in place.
 * `transform` adjusts the payload before it is sent.
 */
export function bindFormSubmit({
  form, base, id, redirectTo, successMessage, transform, onSuccess,
}) {
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    clearErrors(form);

    const submitButton = form.querySelector('[type="submit"]');
    let payload = formValues(form);
    if (transform) payload = transform(payload, form);

    await withBusy(submitButton, async () => {
      try {
        const result = id
          ? await api.put(`${base}/${id}`, payload)
          : await api.post(base, payload);

        notify.ok(successMessage || 'Saved.');

        if (onSuccess) onSuccess(result.data);
        else if (redirectTo) navigate(redirectTo);
      } catch (error) {
        if (error.isValidation || error.status === 409 || error.status === 403) {
          applyErrors(form, error);
          notify.error(
            error.isValidation
              ? 'Please check the highlighted fields.'
              : error.message
          );
        } else {
          applyErrors(form, error);
          notify.error(error.message);
        }
      }
    });
  });
}

/** Card wrapper for a form, with a sticky action bar. */
export function formShell({ backTo, saveLabel = 'Save' }) {
  const form = el('<form novalidate></form>');
  const card = el('<div class="card"><div class="card__body"></div></div>');
  const body = card.querySelector('.card__body');

  const actions = el(`
    <div class="form-actions">
      <button type="button" class="btn btn--ghost">Cancel</button>
      <button type="submit" class="btn">${esc(saveLabel)}</button>
    </div>
  `);

  actions.querySelector('button[type="button"]').addEventListener('click', () => navigate(backTo));

  form.append(card, actions);

  return { form, body };
}

/** Loads select options once and caches them for the session. */
const optionCache = new Map();

export async function loadOptions(path) {
  if (optionCache.has(path)) return optionCache.get(path);

  const promise = api.get(path).then((result) =>
    (result.data || []).map((row) => ({
      value: row.id,
      label: row.name || row.title,
      status: row.status,
    }))
  );

  optionCache.set(path, promise);
  return promise;
}

export function clearOptionCache() {
  optionCache.clear();
}
