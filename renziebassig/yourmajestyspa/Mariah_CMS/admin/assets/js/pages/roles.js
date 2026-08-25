/** Roles and permissions. Editing is restricted to Super Admins server-side. */

import { api } from '../api.js';
import { navigate } from '../router.js';
import { session } from '../session.js';
import { el, esc } from '../ui/dom.js';
import { DataTable } from '../ui/table.js';
import { field, fill, section, textarea } from '../ui/form.js';
import { confirmDialog, notify, withBusy } from '../ui/feedback.js';
import { bindFormSubmit, formShell, pageHead } from './helpers.js';

export async function rolesPage(outlet) {
  outlet.appendChild(pageHead({
    title: 'Roles & permissions',
    description: 'Each role is a bundle of permissions. Every API request re-checks these '
      + 'on the server, so hiding a button is never the only protection.',
    actions: session.isSuperAdmin
      ? [{ label: 'Add Role', iconName: 'i-plus', onClick: () => navigate('/roles/new') }]
      : [],
  }));

  const table = new DataTable({
    searchPlaceholder: 'Search roles…',
    allowTrash: false,
    defaultSort: { sort: 'id', direction: 'ASC' },
    fetch: (query) => api.list('/roles', query),

    columns: [
      {
        key: 'name', label: 'Role', sortable: true,
        render: (row) => `
          <span class="cell-title">${esc(row.name)}${
            row.is_system ? ' <span class="pill pill--plain">Built-in</span>' : ''
          }</span>
          <span class="cell-sub">${esc(row.description || '')}</span>
        `,
      },
      {
        key: 'permissions_count', label: 'Permissions', className: 'cell-num',
        render: (row) => `${row.permissions_count}`,
      },
      {
        key: 'users_count', label: 'Users', className: 'cell-num',
        render: (row) => `${row.users_count}`,
      },
    ],

    rowActions: (row) => {
      const actions = [];

      actions.push({
        label: 'View permissions', iconName: 'i-eye',
        onClick: (r) => navigate(`/roles/${r.id}/edit`),
      });

      if (session.isSuperAdmin && !row.is_system && row.users_count === 0) {
        actions.push({
          label: 'Delete', iconName: 'i-trash', danger: true,
          onClick: async (r, button) => {
            const ok = await confirmDialog({
              title: `Delete the "${r.name}" role?`,
              message: 'This cannot be undone. Roles assigned to users cannot be deleted.',
              confirmLabel: 'Delete role',
            });
            if (!ok) return;

            await withBusy(button, async () => {
              try {
                await api.del(`/roles/${r.id}`);
                notify.ok(`Role "${r.name}" deleted.`);
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

    emptyTitle: 'No roles',
    emptyMessage: 'Roles are created during setup. Run the seed script if this list is empty.',
  });

  table.mount(outlet);
}

export async function roleFormPage(outlet, args) {
  const id = args.id ? Number(args.id) : null;
  const isEdit = Boolean(id);
  const readOnly = !session.isSuperAdmin;

  let record = null;
  if (isEdit) {
    try {
      record = (await api.get(`/roles/${id}`)).data;
    } catch (error) {
      notify.error(error.message);
      navigate('/roles');
      return;
    }
  }

  const catalogue = (await api.get('/permissions')).data;
  const granted = new Set(record?.permissions || []);
  const isSuperAdminRole = record?.slug === 'super-admin';

  outlet.appendChild(pageHead({
    title: isEdit ? `${record.name} role` : 'Add a role',
    description: readOnly
      ? 'You can view this role. Only a Super Admin can change roles or permissions.'
      : (isSuperAdminRole
        ? 'The Super Admin role always holds every permission and cannot be narrowed.'
        : 'Tick the permissions this role should hold.'),
  }));

  const { form, body } = formShell({
    backTo: '/roles',
    saveLabel: isEdit ? 'Save role' : 'Create role',
  });

  body.appendChild(fill(
    section('Role details'),
    field({
      name: 'name', label: 'Role name', required: !isEdit, span: 6,
      value: record?.name,
    }),
    textarea({
      name: 'description', label: 'Description', rows: 2, span: 12,
      value: record?.description,
    }),
  ));

  // --- Permission checkboxes -------------------------------------
  const permissionSection = section(
    'Permissions',
    `${granted.size} of ${Object.values(catalogue).flat().length} permissions currently granted.`
  );
  const grid = permissionSection.querySelector('.grid');
  const holder = el('<div class="col-12"></div>');

  Object.entries(catalogue).forEach(([groupName, permissions]) => {
    const group = el(`
      <div class="perm-group">
        <h4>${esc(groupName)}</h4>
        <div class="perm-group__items"></div>
      </div>
    `);

    const items = group.querySelector('.perm-group__items');

    permissions.forEach((permission) => {
      const disabled = readOnly || isSuperAdminRole;
      items.appendChild(el(`
        <label class="check">
          <input type="checkbox" data-permission="${esc(permission.slug)}"
            ${granted.has(permission.slug) || isSuperAdminRole ? 'checked' : ''}
            ${disabled ? 'disabled' : ''}>
          <span>${esc(permission.name)}<small><code>${esc(permission.slug)}</code></small></span>
        </label>
      `));
    });

    holder.appendChild(group);
  });

  grid.appendChild(holder);
  body.appendChild(permissionSection);

  if (readOnly || isSuperAdminRole) {
    form.querySelectorAll('input, textarea, select').forEach((input) => { input.disabled = true; });
    form.querySelector('[type="submit"]').style.display = 'none';
  }

  bindFormSubmit({
    form,
    base: '/roles',
    id,
    redirectTo: '/roles',
    successMessage: isEdit ? 'Role updated.' : 'Role created.',
    transform: (payload, formEl) => {
      payload.permissions = [...formEl.querySelectorAll('[data-permission]:checked')]
        .map((input) => input.dataset.permission);
      return payload;
    },
  });

  outlet.appendChild(form);
}
