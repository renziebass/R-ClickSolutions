/** Settings: the signed-in user's own account, and system reference info. */

import { api } from '../api.js';
import { session } from '../session.js';
import { dateTimeLabel, el, esc } from '../ui/dom.js';
import { applyErrors, clearErrors, field, fill, formValues, section, switchField } from '../ui/form.js';
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

  // The route itself carries no permission on purpose — every signed-in user
  // needs the account and password cards. This check only decides whether to
  // render; GET and PUT /settings each enforce their own guard server-side.
  if (session.can('settings.view')) {
    try {
      grid.insertBefore(await siteSettingsCard(), grid.firstChild);
    } catch (error) {
      grid.insertBefore(el(`
        <div class="card"><div class="card__body">
          <div class="error-state">
            <h3>Site settings could not be loaded</h3>
            <p>${esc(error.message || 'Unexpected error.')}</p>
          </div>
        </div></div>
      `), grid.firstChild);
    }
  }
}

// =================================================================
// Site settings
// =================================================================
async function siteSettingsCard() {
  const data = (await api.get('/settings')).data;
  const canEdit = data.can_edit === true;

  const card = el(`
    <div class="card">
      <div class="card__head"><h3>Site settings</h3></div>
      <div class="card__body">
        <p style="margin-top:0;color:var(--text-soft);font-size:.9rem">
          Options that change how the CMS behaves. These are stored in the database,
          so they apply to everyone.
        </p>
      </div>
    </div>
  `);

  const body = card.querySelector('.card__body');
  const form = el('<form novalidate></form>');

  data.groups.forEach((group) => {
    const node = section(group.label);

    fill(node, ...group.settings.map((setting) => (
      setting.type === 'bool'
        ? switchField({
            name: setting.key, label: setting.label,
            hint: setting.help, checked: Boolean(setting.value), span: 12,
          })
        // The field's name IS the setting key, which is what makes
        // applyErrors() paint a 422 onto the right input with no changes
        // to ui/form.js.
        : field({
            name: setting.key, label: setting.label, hint: setting.help,
            type: setting.type === 'url' ? 'url' : 'text',
            value: setting.value ?? '', span: 12,
          })
    )));

    form.appendChild(node);
  });

  if (!canEdit) {
    form.querySelectorAll('input, select, textarea').forEach((input) => {
      input.disabled = true;
    });
    form.appendChild(el(
      '<p class="muted" style="font-size:.87rem">Your role can view these settings but not change them.</p>'
    ));
  } else {
    const submit = el('<button type="submit" class="btn mt-2">Save settings</button>');
    form.appendChild(submit);

    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      clearErrors(form);

      await withBusy(submit, async () => {
        try {
          const result = await api.put('/settings', formValues(form));
          notify.ok('Settings saved.');

          // Keeps this tab consistent immediately: /auth/me only runs at boot,
          // so without this the person who just pasted the template link would
          // not see the button on the import screen until a reload.
          Object.assign(session.config, result.data.values_public || {});
        } catch (error) {
          applyErrors(form, error);
          notify.error(error.message);
        }
      });
    });
  }

  body.appendChild(form);
  body.appendChild(sheetSetupBlock());

  return card;
}

/**
 * The one-time setup the CMS cannot do for you — Google has no URL that
 * creates a sheet with content in it.
 *
 * The sheet's *contents* come from the import screen's template generator, so
 * the column list lives in exactly one place (ServiceCsvSchema, served through
 * /services/form-options) rather than being copied here as well.
 */
function sheetSetupBlock() {
  return el(`
    <div class="mt-3" style="border-top:1px solid var(--line);padding-top:1.25rem">
      <h4 style="margin:0 0 .5rem;font-size:.95rem">Setting up the Google Sheets template</h4>
      <p class="muted" style="font-size:.87rem;margin-top:0">
        The CMS cannot create the sheet for you. This is a one-time setup.
      </p>
      <ol class="muted" style="font-size:.87rem;padding-left:1.2rem;line-height:1.7">
        <li>Open <b>sheets.google.com</b> and create a blank spreadsheet.</li>
        <li>Go to <a href="#/services/import">Services → Import</a>, open
            <b>Start from a template</b> and click <b>Copy blank template</b>.
            Back in your sheet, click cell <b>A1</b> and paste.</li>
        <li>Choose <b>Share → General access → Anyone with the link → Viewer</b>,
            then <b>Copy link</b>.</li>
        <li>Paste that link into <b>Google Sheets template link</b> above and save.</li>
      </ol>
      <p class="muted" style="font-size:.82rem;margin-top:.75rem">
        Anyone holding the link will be able to read that sheet. For a blank template
        that is fine; keep anything private out of it.
      </p>
    </div>
  `);
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
