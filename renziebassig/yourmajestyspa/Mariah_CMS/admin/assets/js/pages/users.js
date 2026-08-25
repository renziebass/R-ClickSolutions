/**
 * User management.
 *
 * The Super Admin guards below mirror the server's rules — but the server is
 * what enforces them. A non-Super-Admin who bypasses this UI still gets a 403.
 */

import { api } from '../api.js';
import { navigate } from '../router.js';
import { session } from '../session.js';
import { dateTimeLabel, el, esc, initials, relativeTime } from '../ui/dom.js';
import { DataTable } from '../ui/table.js';
import { field, fill, section, select } from '../ui/form.js';
import { confirmDialog, notify, withBusy } from '../ui/feedback.js';
import { bindFormSubmit, formShell, pageHead } from './helpers.js';

const STATUS_PILLS = {
  active: 'pill pill--ok',
  inactive: 'pill',
  suspended: 'pill pill--danger',
};

export async function usersPage(outlet) {
  outlet.appendChild(pageHead({
    title: 'Users',
    description: 'Staff accounts with access to this dashboard. Roles decide what each person can do.',
    actions: session.can('users.create')
      ? [{ label: 'Add User', iconName: 'i-plus', onClick: () => navigate('/users/new') }]
      : [],
  }));

  let roles = [];
  try {
    roles = (await api.get('/users/assignable-roles')).data;
  } catch { /* filter simply offers no role options */ }

  const table = new DataTable({
    searchPlaceholder: 'Search by name or email…',
    defaultSort: { sort: 'created_at', direction: 'DESC' },
    fetch: (query) => api.list('/users', query),

    filters: [
      {
        name: 'role_id', label: 'Role',
        options: roles.map((role) => ({ value: role.id, label: role.name })),
      },
      {
        name: 'status', label: 'Status',
        options: [
          { value: 'active', label: 'Active' },
          { value: 'inactive', label: 'Inactive' },
          { value: 'suspended', label: 'Suspended' },
        ],
      },
    ],

    columns: [
      {
        key: 'name', label: 'User', sortable: true,
        render: (row) => `
          <div style="display:flex;align-items:center;gap:.7rem">
            <span style="width:34px;height:34px;border-radius:50%;background:var(--sand-2);
                         color:var(--emerald);display:grid;place-items:center;
                         font-family:var(--f-display);flex:none">${esc(initials(row.full_name))}</span>
            <span style="min-width:0">
              <span class="cell-title">${esc(row.full_name)}${
                row.id === session.user.id ? ' <em class="muted">(you)</em>' : ''
              }</span>
              <span class="cell-sub">${esc(row.email)}</span>
            </span>
          </div>
        `,
      },
      {
        key: 'role', label: 'Role', sortable: true,
        render: (row) => `<span class="pill pill--plain">${esc(row.role.name)}</span>`,
      },
      {
        key: 'status', label: 'Status', sortable: true,
        render: (row) => `<span class="${STATUS_PILLS[row.status] || 'pill'}">${esc(row.status)}</span>`,
      },
      {
        key: 'last_login', label: 'Last sign-in', sortable: true, className: 'nowrap',
        render: (row) => row.last_login_at
          ? `<span class="muted">${esc(relativeTime(row.last_login_at))}</span>`
          : '<span class="muted">Never</span>',
      },
      {
        key: 'created_at', label: 'Added', sortable: true, className: 'nowrap',
        render: (row) => `<span class="muted">${esc(relativeTime(row.created_at))}</span>`,
      },
    ],

    rowActions: (row) => {
      const actions = [];
      const isSuperAdmin = row.role.slug === 'super-admin';
      const blocked = isSuperAdmin && !session.isSuperAdmin;

      if (row.deleted_at) {
        if (session.can('users.delete') && !blocked) {
          actions.push({
            label: 'Restore', iconName: 'i-undo',
            onClick: async (r, button) => {
              await withBusy(button, async () => {
                try {
                  await api.post(`/users/${r.id}/restore`);
                  notify.ok(`${r.full_name} was restored.`);
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

      if (session.can('users.edit') && !blocked) {
        actions.push({
          label: 'Edit', iconName: 'i-edit',
          onClick: (r) => navigate(`/users/${r.id}/edit`),
        });
      }

      if (session.can('users.delete') && !blocked && row.id !== session.user.id) {
        actions.push({
          label: 'Delete', iconName: 'i-trash', danger: true,
          onClick: async (r, button) => {
            const ok = await confirmDialog({
              title: `Delete ${r.full_name}?`,
              message: 'They lose access immediately. The account is kept in deleted items '
                + 'and can be restored.',
              confirmLabel: 'Delete user',
            });
            if (!ok) return;

            await withBusy(button, async () => {
              try {
                const result = await api.del(`/users/${r.id}`);
                notify.ok(result.data.message);
                table.refresh();
              } catch (error) {
                notify.error(error.message);
              }
            });
          },
        });
      }

      return actions;
    },

    emptyTitle: 'No users found',
    emptyMessage: 'Add a staff account so your team can manage the website.',
    emptyActionLabel: session.can('users.create') ? 'Add User' : null,
    onEmptyAction: () => navigate('/users/new'),
  });

  table.mount(outlet);
}

export async function userFormPage(outlet, args) {
  const id = args.id ? Number(args.id) : null;
  const isEdit = Boolean(id);

  let record = null;
  if (isEdit) {
    try {
      record = (await api.get(`/users/${id}`)).data;
    } catch (error) {
      notify.error(error.message);
      navigate('/users');
      return;
    }
  }

  const roles = (await api.get('/users/assignable-roles')).data;

  outlet.appendChild(pageHead({
    title: isEdit ? `Edit ${record.full_name}` : 'Add a user',
    description: isEdit
      ? 'Leave the password blank to keep the current one.'
      : 'The new user signs in with the email and password you set here.',
  }));

  const { form, body } = formShell({
    backTo: '/users',
    saveLabel: isEdit ? 'Save changes' : 'Create user',
  });

  body.appendChild(fill(
    section('Account'),
    field({
      name: 'first_name', label: 'First name', required: !isEdit, span: 6,
      value: record?.first_name,
    }),
    field({
      name: 'last_name', label: 'Last name', required: !isEdit, span: 6,
      value: record?.last_name,
    }),
    field({
      name: 'email', label: 'Email address', type: 'email', required: !isEdit, span: 12,
      value: record?.email,
    }),
    field({
      name: 'password', label: isEdit ? 'New password' : 'Password',
      type: 'password', required: !isEdit, span: 6,
      hint: isEdit
        ? 'Leave blank to keep the current password. Minimum 10 characters.'
        : 'Minimum 10 characters.',
    }),
  ));

  body.appendChild(fill(
    section('Access', 'A role decides which sections of this dashboard the person can reach.'),
    select({
      name: 'role_id', label: 'Role', required: !isEdit, span: 6,
      value: record?.role_id, placeholder: 'Choose a role',
      options: roles.map((role) => ({ value: role.id, label: role.name })),
    }),
    select({
      name: 'status', label: 'Status', span: 6, placeholder: '',
      value: record?.status || 'active',
      options: [
        { value: 'active', label: 'Active — can sign in' },
        { value: 'inactive', label: 'Inactive — cannot sign in' },
        { value: 'suspended', label: 'Suspended — cannot sign in' },
      ],
    }),
  ));

  bindFormSubmit({
    form,
    base: '/users',
    id,
    redirectTo: '/users',
    successMessage: isEdit ? 'User updated.' : 'User created.',
    transform: (payload) => {
      // Sending an empty password on edit would fail min-length validation.
      if (!payload.password) delete payload.password;
      if (payload.role_id) payload.role_id = Number(payload.role_id);
      return payload;
    },
  });

  outlet.appendChild(form);

  if (isEdit) {
    outlet.appendChild(el(`
      <div class="card mt-3"><div class="card__body">
        <dl class="detail-list" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:1rem">
          <div><dt>Last sign-in</dt><dd>${
            record.last_login_at ? esc(dateTimeLabel(record.last_login_at)) : 'Never'
          }</dd></div>
          <div><dt>Last sign-in IP</dt><dd>${esc(record.last_login_ip || '—')}</dd></div>
          <div><dt>Account created</dt><dd>${esc(dateTimeLabel(record.created_at))}</dd></div>
        </dl>
      </div></div>
    `));
  }
}
