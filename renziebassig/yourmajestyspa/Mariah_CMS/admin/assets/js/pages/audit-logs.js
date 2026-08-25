/** Audit log — read-only record of administrative activity. */

import { api } from '../api.js';
import { dateTimeLabel, el, esc, relativeTime } from '../ui/dom.js';
import { DataTable } from '../ui/table.js';
import { modal } from '../ui/feedback.js';
import { pageHead } from './helpers.js';

const ACTION_PILLS = {
  created: 'pill pill--ok',
  updated: 'pill pill--info',
  deleted: 'pill pill--danger',
  restored: 'pill pill--ok',
  activated: 'pill pill--ok',
  deactivated: 'pill pill--warn',
  duplicated: 'pill pill--info',
  uploaded: 'pill pill--info',
  login: 'pill pill--ok',
  logout: 'pill',
  login_failed: 'pill pill--danger',
  login_blocked: 'pill pill--danger',
  role_changed: 'pill pill--warn',
  password_changed: 'pill pill--warn',
};

export async function auditLogsPage(outlet) {
  outlet.appendChild(pageHead({
    title: 'Audit logs',
    description: 'Every significant action taken in this dashboard, including sign-ins. '
      + 'This record is append-only and cannot be edited.',
  }));

  let filters = { actions: [], entity_types: [], users: [] };
  try {
    filters = (await api.get('/audit-logs/filters')).data;
  } catch { /* fall back to no filter options */ }

  const table = new DataTable({
    searchPlaceholder: 'Search descriptions and users…',
    allowTrash: false,
    defaultSort: { sort: 'created_at', direction: 'DESC' },
    fetch: (query) => api.list('/audit-logs', query),

    filters: [
      {
        name: 'action', label: 'Action',
        options: filters.actions.map((action) => ({
          value: action,
          label: action.replace(/_/g, ' '),
        })),
      },
      {
        name: 'entity_type', label: 'Type',
        options: filters.entity_types.map((type) => ({
          value: type,
          label: type.replace(/_/g, ' '),
        })),
      },
      {
        name: 'user_id', label: 'User',
        options: filters.users.map((user) => ({ value: user.id, label: user.name })),
      },
    ],

    columns: [
      {
        key: 'created_at', label: 'When', sortable: true, className: 'nowrap',
        render: (row) => `
          <span class="cell-title">${esc(relativeTime(row.created_at))}</span>
          <span class="cell-sub">${esc(dateTimeLabel(row.created_at))}</span>
        `,
      },
      {
        key: 'user', label: 'User', sortable: true,
        render: (row) => `
          <span class="cell-title">${esc(row.actor)}</span>
          ${row.user_email ? `<span class="cell-sub">${esc(row.user_email)}</span>` : ''}
        `,
      },
      {
        key: 'action', label: 'Action', sortable: true,
        render: (row) => `<span class="${ACTION_PILLS[row.action] || 'pill'}">${
          esc(row.action.replace(/_/g, ' '))
        }</span>`,
      },
      {
        key: 'entity_type', label: 'Type', sortable: true,
        render: (row) => `<span class="muted">${esc(row.entity_type.replace(/_/g, ' '))}${
          row.entity_id ? ` #${row.entity_id}` : ''
        }</span>`,
      },
      {
        key: 'description', label: 'Details',
        render: (row) => esc(row.description),
      },
      {
        key: 'ip_address', label: 'IP', className: 'nowrap',
        render: (row) => `<span class="muted">${esc(row.ip_address || '—')}</span>`,
      },
    ],

    rowActions: (row) => (row.metadata ? [{
      label: 'What changed',
      iconName: 'i-eye',
      onClick: () => showMetadata(row),
    }] : []),

    emptyTitle: 'No activity recorded',
    emptyMessage: 'Actions taken in this dashboard will appear here.',
  });

  table.mount(outlet);
}

function showMetadata(entry) {
  modal({
    title: 'What changed',
    subtitle: entry.description,
    render: (body) => {
      const metadata = entry.metadata || {};
      const changes = Object.entries(metadata)
        .filter(([, value]) => value && typeof value === 'object' && 'from' in value);

      if (!changes.length) {
        body.innerHTML = `<pre style="white-space:pre-wrap;font-size:.82rem;margin:0">${
          esc(JSON.stringify(metadata, null, 2))
        }</pre>`;
        return;
      }

      body.appendChild(el(`
        <div class="table-wrap">
          <table class="data">
            <thead><tr><th>Field</th><th>Before</th><th>After</th></tr></thead>
            <tbody>
              ${changes.map(([fieldName, change]) => `
                <tr>
                  <td data-label="Field"><b>${esc(fieldName.replace(/_/g, ' '))}</b></td>
                  <td data-label="Before"><span class="muted">${
                    esc(change.from === null || change.from === '' ? '—' : change.from)
                  }</span></td>
                  <td data-label="After">${
                    esc(change.to === null || change.to === '' ? '—' : change.to)
                  }</td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
      `));
    },
    footer: (foot, close) => {
      const done = el('<button class="btn">Close</button>');
      done.addEventListener('click', () => close(true));
      foot.appendChild(done);
    },
  });
}
