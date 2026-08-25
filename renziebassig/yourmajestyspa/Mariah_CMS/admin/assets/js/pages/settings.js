/** Settings: the signed-in user's own account, and system reference info. */

import { api } from '../api.js';
import { session } from '../session.js';
import { dateTimeLabel, el, esc } from '../ui/dom.js';
import { applyErrors, clearErrors, field, fill, section } from '../ui/form.js';
import { notify, withBusy } from '../ui/feedback.js';
import { pageHead } from './helpers.js';

export async function settingsPage(outlet) {
  outlet.appendChild(pageHead({
    title: 'Settings',
    description: 'Your account and a reference for how this CMS connects to the website.',
  }));

  const grid = el('<div class="panel-grid"></div>');

  grid.appendChild(accountCard());
  grid.appendChild(passwordCard());
  grid.appendChild(integrationCard());

  outlet.appendChild(grid);
}

function accountCard() {
  const user = session.user;

  return el(`
    <div class="card">
      <div class="card__head"><h3>Your account</h3></div>
      <div class="card__body">
        <dl class="detail-list">
          <dt>Name</dt><dd>${esc(user.full_name)}</dd>
          <dt>Email</dt><dd>${esc(user.email)}</dd>
          <dt>Role</dt><dd>${esc(user.role.name)}</dd>
          <dt>Last sign-in</dt>
          <dd>${user.last_login_at ? esc(dateTimeLabel(user.last_login_at)) : 'This is your first session'}</dd>
          <dt>Permissions held</dt><dd>${user.permissions.length}</dd>
        </dl>
      </div>
    </div>
  `);
}

function passwordCard() {
  const card = el(`
    <div class="card">
      <div class="card__head"><h3>Change your password</h3></div>
      <div class="card__body"></div>
    </div>
  `);

  const body = card.querySelector('.card__body');
  const form = el('<form novalidate></form>');

  form.appendChild(fill(
    section(''),
    field({
      name: 'current_password', label: 'Current password', type: 'password',
      required: true, span: 12,
    }),
    field({
      name: 'new_password', label: 'New password', type: 'password',
      required: true, span: 12, hint: 'At least 10 characters.',
    }),
    field({
      name: 'confirm_password', label: 'Confirm new password', type: 'password',
      required: true, span: 12,
    }),
  ));

  // The empty section() heading leaves a stray block; drop it.
  const heading = form.querySelector('.form-section__head');
  if (heading) heading.remove();

  const submit = el('<button type="submit" class="btn mt-2">Update password</button>');
  form.appendChild(submit);

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    clearErrors(form);

    const current = form.querySelector('[name="current_password"]').value;
    const next = form.querySelector('[name="new_password"]').value;
    const confirm = form.querySelector('[name="confirm_password"]').value;

    if (next !== confirm) {
      applyErrors(form, {
        message: 'The passwords do not match.',
        fields: { confirm_password: 'This does not match the new password.' },
      });
      return;
    }

    await withBusy(submit, async () => {
      try {
        await api.post('/auth/password', {
          current_password: current,
          new_password: next,
        });
        notify.ok('Your password has been updated.');
        form.reset();
      } catch (error) {
        applyErrors(form, error);
        notify.error(error.message);
      }
    });
  });

  body.appendChild(form);
  return card;
}

function integrationCard() {
  // pages → js → assets → admin → Mariah_CMS
  const apiBase = new URL('../../../../api', new URL('.', import.meta.url)).pathname.replace(/\/$/, '');

  return el(`
    <div class="card">
      <div class="card__head"><h3>Website connection</h3></div>
      <div class="card__body">
        <p style="margin-top:0;color:var(--text-soft);font-size:.9rem">
          The public website reads its content from these endpoints. Only active and published
          records are returned, so deactivating an item removes it from the site immediately.
        </p>
        <dl class="detail-list">
          <dt>Everything the page needs</dt>
          <dd><code>${esc(apiBase)}/public/bootstrap</code></dd>
          <dt>Individual collections</dt>
          <dd><code>${esc(apiBase)}/public/services</code><br>
              <code>${esc(apiBase)}/public/specials</code><br>
              <code>${esc(apiBase)}/public/promotions</code><br>
              <code>${esc(apiBase)}/public/products</code><br>
              <code>${esc(apiBase)}/public/gift-cards</code></dd>
        </dl>
        <a class="btn btn--ghost btn--sm mt-2" href="${esc(apiBase)}/public/bootstrap"
           target="_blank" rel="noopener">Open the live public feed</a>
      </div>
    </div>
  `);
}
