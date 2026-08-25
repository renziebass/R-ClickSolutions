/**
 * Reusable data table: toolbar filters, sortable headers, pagination,
 * loading/empty/error states, and row actions.
 *
 * Every list page in the CMS is built from this, so search, sorting and
 * pagination behave identically everywhere.
 */

import { debounce, el, esc, icon } from './dom.js';
import { emptyState, errorState, skeletonRows } from './feedback.js';

export class DataTable {
  /**
   * @param {object} config
   *   columns       [{ key, label, sortable, render(row), className, width }]
   *   fetch         (query) => Promise<{ data, meta }>
   *   filters       [{ name, label, options:[{value,label}], type }]
   *   searchPlaceholder
   *   rowActions    (row) => [{ label, iconName, onClick, danger, hidden }]
   *   emptyTitle / emptyMessage / emptyActionLabel / onEmptyAction
   *   defaultSort   { sort, direction }
   *   allowTrash    show the "Deleted items" filter
   *   onRowClick    (row) => void
   */
  constructor(config) {
    this.config = config;
    this.state = {
      page: 1,
      per_page: 20,
      search: '',
      sort: config.defaultSort?.sort || '',
      direction: config.defaultSort?.direction || 'ASC',
      filters: {},
      deleted: '',
    };

    this.root = el('<div class="card"></div>');
    this.toolbar = null;
    this.content = el('<div></div>');
    this.footer = el('<div></div>');

    this.buildToolbar();
    this.root.append(this.content, this.footer);
  }

  mount(container) {
    container.appendChild(this.root);
    this.load();
    return this;
  }

  // -------------------------------------------------------------
  buildToolbar() {
    const { filters = [], searchPlaceholder = 'Search…', allowTrash = true } = this.config;

    this.toolbar = el(`
      <div class="toolbar">
        <div class="toolbar__search">
          ${icon('i-search', 16)}
          <input type="search" placeholder="${esc(searchPlaceholder)}" aria-label="Search">
        </div>
      </div>
    `);

    const searchInput = this.toolbar.querySelector('input');
    searchInput.addEventListener('input', debounce(() => {
      this.state.search = searchInput.value.trim();
      this.state.page = 1;
      this.load();
    }, 320));

    filters.forEach((filter) => {
      const options = filter.options
        .map((option) => `<option value="${esc(option.value)}">${esc(option.label)}</option>`)
        .join('');

      const select = el(`
        <select aria-label="${esc(filter.label)}" style="width:auto;min-width:150px">
          <option value="">${esc(filter.label)}: All</option>
          ${options}
        </select>
      `);

      select.addEventListener('change', () => {
        this.state.filters[filter.name] = select.value;
        this.state.page = 1;
        this.load();
      });

      // Options that arrive from the API after construction.
      if (filter.load) {
        filter.load().then((loaded) => {
          loaded.forEach((option) => {
            select.appendChild(
              el(`<option value="${esc(option.value)}">${esc(option.label)}</option>`)
            );
          });
        }).catch(() => { /* leave the static options in place */ });
      }

      this.toolbar.appendChild(select);
      filter.el = select;
    });

    if (allowTrash) {
      const trash = el(`
        <select aria-label="Deleted items" style="width:auto;min-width:140px">
          <option value="">Active items</option>
          <option value="only">Deleted items</option>
          <option value="with">All items</option>
        </select>
      `);

      trash.addEventListener('change', () => {
        this.state.deleted = trash.value;
        this.state.page = 1;
        this.load();
      });

      this.toolbar.appendChild(trash);
    }

    this.root.appendChild(this.toolbar);
  }

  query() {
    const query = {
      page: this.state.page,
      per_page: this.state.per_page,
      search: this.state.search,
      sort: this.state.sort,
      direction: this.state.direction,
      deleted: this.state.deleted,
    };

    Object.entries(this.state.filters).forEach(([key, value]) => {
      if (value !== '' && value !== undefined) query[key] = value;
    });

    return query;
  }

  async load() {
    this.content.replaceChildren(skeletonRows(6));
    this.footer.replaceChildren();

    try {
      const { data, meta } = await this.config.fetch(this.query());
      this.rows = data;
      this.render(data, meta);
    } catch (error) {
      this.content.replaceChildren(errorState(error.message, () => this.load()));
    }
  }

  /** Re-fetches the current page — call after a create/update/delete. */
  refresh() {
    return this.load();
  }

  render(rows, meta) {
    if (!rows.length) {
      const isFiltered = this.state.search || this.state.deleted
        || Object.values(this.state.filters).some(Boolean);

      this.content.replaceChildren(emptyState({
        title: isFiltered ? 'No matches' : (this.config.emptyTitle || 'Nothing here yet'),
        message: isFiltered
          ? 'No records match your current search and filters. Try clearing them.'
          : (this.config.emptyMessage || 'Create your first record to get started.'),
        actionLabel: isFiltered ? 'Clear filters' : this.config.emptyActionLabel,
        onAction: isFiltered ? () => this.clearFilters() : this.config.onEmptyAction,
      }));

      this.renderPager(meta);
      return;
    }

    const { columns, rowActions } = this.config;

    const head = columns.map((column) => {
      const isSorted = this.state.sort === column.key;
      const arrow = isSorted ? (this.state.direction === 'ASC' ? '▲' : '▼') : '▲';

      return `<th
          class="${column.sortable ? 'sortable' : ''} ${isSorted ? 'is-sorted' : ''} ${column.className || ''}"
          ${column.sortable ? `data-sort="${esc(column.key)}"` : ''}
          ${column.width ? `style="width:${esc(column.width)}"` : ''}
        >${esc(column.label)}${column.sortable ? `<span class="sort-arrow">${arrow}</span>` : ''}</th>`;
    }).join('');

    const table = el(`
      <div class="table-wrap">
        <table class="data">
          <thead><tr>${head}${rowActions ? '<th class="text-right">Actions</th>' : ''}</tr></thead>
          <tbody></tbody>
        </table>
      </div>
    `);

    const tbody = table.querySelector('tbody');

    rows.forEach((row) => {
      const tr = el(`<tr class="${row.deleted_at ? 'is-deleted' : ''}"></tr>`);

      columns.forEach((column) => {
        const td = el(`<td data-label="${esc(column.label)}" class="${column.className || ''}"></td>`);
        const value = column.render ? column.render(row) : esc(row[column.key] ?? '—');

        if (value instanceof Node) {
          td.appendChild(value);
        } else {
          td.innerHTML = value;   // column renderers escape their own values
        }

        tr.appendChild(td);
      });

      if (rowActions) {
        const actions = rowActions(row).filter((action) => !action.hidden);
        const cell = el('<td data-label="Actions"><div class="row-actions"></div></td>');
        const holder = cell.querySelector('.row-actions');

        actions.forEach((action) => {
          const button = el(`
            <button class="${action.danger ? 'is-danger' : ''}" title="${esc(action.label)}"
                    aria-label="${esc(action.label)}">${icon(action.iconName || 'i-dots', 16)}</button>
          `);
          button.addEventListener('click', (event) => {
            event.stopPropagation();
            action.onClick(row, button);
          });
          holder.appendChild(button);
        });

        tr.appendChild(cell);
      }

      if (this.config.onRowClick) {
        tr.style.cursor = 'pointer';
        tr.addEventListener('click', () => this.config.onRowClick(row));
      }

      tbody.appendChild(tr);
    });

    table.querySelectorAll('th.sortable').forEach((th) => {
      th.addEventListener('click', () => {
        const key = th.dataset.sort;
        if (this.state.sort === key) {
          this.state.direction = this.state.direction === 'ASC' ? 'DESC' : 'ASC';
        } else {
          this.state.sort = key;
          this.state.direction = 'ASC';
        }
        this.state.page = 1;
        this.load();
      });
    });

    this.content.replaceChildren(table);
    this.renderPager(meta);
  }

  renderPager(meta) {
    if (!meta || meta.total === 0) {
      this.footer.replaceChildren();
      return;
    }

    const pager = el(`
      <div class="pager">
        <div class="pager__info">
          Showing ${meta.from}–${meta.to} of ${meta.total}
        </div>
        <div class="pager__nav"></div>
      </div>
    `);

    const nav = pager.querySelector('.pager__nav');
    const { page, last_page: lastPage } = meta;

    const addButton = (label, targetPage, { disabled = false, current = false } = {}) => {
      const button = el(
        `<button class="${current ? 'is-current' : ''}" ${disabled ? 'disabled' : ''}>${esc(label)}</button>`
      );
      if (!disabled && !current) {
        button.addEventListener('click', () => {
          this.state.page = targetPage;
          this.load();
          this.root.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
      }
      nav.appendChild(button);
    };

    addButton('‹', page - 1, { disabled: page <= 1 });

    // A compact window around the current page keeps long lists readable.
    const window_ = 2;
    const pages = new Set([1, lastPage]);
    for (let i = page - window_; i <= page + window_; i++) {
      if (i >= 1 && i <= lastPage) pages.add(i);
    }

    let previous = 0;
    [...pages].sort((a, b) => a - b).forEach((p) => {
      if (p - previous > 1) nav.appendChild(el('<span style="padding:0 .25rem;color:var(--text-faint)">…</span>'));
      addButton(String(p), p, { current: p === page });
      previous = p;
    });

    addButton('›', page + 1, { disabled: page >= lastPage });

    this.footer.replaceChildren(pager);
  }

  clearFilters() {
    this.state.search = '';
    this.state.deleted = '';
    this.state.filters = {};
    this.state.page = 1;

    this.toolbar.querySelectorAll('input, select').forEach((input) => { input.value = ''; });
    this.load();
  }
}
