/** Dashboard home: stat cards, quick actions and recent activity. */

import { api } from '../api.js';
import { navigate } from '../router.js';
import { session } from '../session.js';
import { el, esc, icon, relativeTime } from '../ui/dom.js';
import { errorState, skeletonCards } from '../ui/feedback.js';
import { pageHead, statusPill } from './helpers.js';

export async function dashboardPage(outlet) {
  outlet.appendChild(pageHead({
    title: `Good ${greeting()}, ${session.user.first_name}`,
    description: 'Everything published here appears on the Majesty Day Spa website. '
      + 'Deactivate an item to take it off the site without deleting it.',
  }));

  const loading = skeletonCards(4);
  outlet.appendChild(loading);

  let stats;
  try {
    const result = await api.get('/dashboard/stats');
    stats = result.data;
  } catch (error) {
    loading.replaceWith(errorState(error.message, () => {
      outlet.replaceChildren();
      dashboardPage(outlet);
    }));
    return;
  }

  loading.remove();

  // --- Stat cards ------------------------------------------------
  const cards = el('<div class="stat-grid"></div>');

  cards.appendChild(statCard({
    label: 'Services',
    value: stats.services.total,
    dark: true,
    meta: [
      `${stats.services.active} active`,
      `${stats.services.inactive} inactive`,
      `${stats.services.featured} featured`,
    ],
  }));

  cards.appendChild(statCard({
    label: 'Promotions',
    value: stats.promotions.active,
    valueSuffix: 'live now',
    meta: [
      `${stats.promotions.scheduled} scheduled`,
      `${stats.promotions.expired} expired`,
      `${stats.promotions.draft} draft`,
    ],
  }));

  cards.appendChild(statCard({
    label: 'Specials',
    value: stats.specials.active,
    valueSuffix: 'live now',
    meta: [
      `${stats.specials.upcoming} upcoming`,
      `${stats.specials.expired} expired`,
    ],
  }));

  if (stats.users) {
    cards.appendChild(statCard({
      label: 'Admin users',
      value: stats.users.total,
      meta: [
        `${stats.users.active} active`,
        `${stats.users.inactive + stats.users.suspended} inactive`,
      ],
    }));
  } else {
    cards.appendChild(statCard({
      label: 'Shop products',
      value: stats.products.total,
      meta: [`${stats.products.active} active`, `${stats.products.inactive} inactive`],
    }));
  }

  outlet.appendChild(cards);

  // --- Quick actions ---------------------------------------------
  const quickActions = [
    ['services.create', 'Add Service', '/services/new'],
    ['promotions.create', 'Add Promotion', '/promotions/new'],
    ['specials.create', 'Add Special', '/specials/new'],
    ['categories.create', 'Add Category', '/categories/new'],
    ['products.create', 'Add Product', '/products/new'],
    ['media.upload', 'Upload Image', '/media'],
  ].filter(([permission]) => session.can(permission));

  if (quickActions.length) {
    const quickCard = el(`
      <div class="card mb-2">
        <div class="card__head"><h3>Quick actions</h3></div>
        <div class="card__body"><div class="quick"></div></div>
      </div>
    `);

    const quick = quickCard.querySelector('.quick');

    quickActions.forEach(([, label, path]) => {
      const button = el(`<button class="quick__btn">${icon('i-plus', 15)} ${esc(label)}</button>`);
      button.addEventListener('click', () => navigate(path));
      quick.appendChild(button);
    });

    outlet.appendChild(quickCard);
  }

  // --- Panels -----------------------------------------------------
  const panels = el('<div class="panel-grid"></div>');

  if (session.can('services.view')) {
    panels.appendChild(recentServicesPanel(stats.recent_services));
  }

  if (session.can('promotions.view')) {
    panels.appendChild(recentPromotionsPanel(stats.recent_promotions));
  }

  if (session.can('audit_logs.view')) {
    panels.appendChild(activityPanel(stats.recent_activity));
  }

  if (panels.children.length) outlet.appendChild(panels);
}

// -----------------------------------------------------------------

function greeting() {
  const hour = new Date().getHours();
  if (hour < 12) return 'morning';
  if (hour < 18) return 'afternoon';
  return 'evening';
}

function statCard({ label, value, meta = [], dark = false, valueSuffix }) {
  return el(`
    <div class="stat${dark ? ' stat--dark' : ''}">
      <div class="stat__label">${esc(label)}</div>
      <div class="stat__value">${esc(value)}${
        valueSuffix ? ` <span style="font-size:.9rem;font-family:var(--f-body)">${esc(valueSuffix)}</span>` : ''
      }</div>
      <div class="stat__meta">${meta.map((m) => `<em>${esc(m)}</em>`).join('')}</div>
    </div>
  `);
}

function recentServicesPanel(services) {
  const card = el(`
    <div class="card">
      <div class="card__head">
        <h3>Recently updated services</h3>
        <a class="btn btn--ghost btn--sm" href="#/services">View all</a>
      </div>
      <div class="card__body"></div>
    </div>
  `);

  const body = card.querySelector('.card__body');

  if (!services || !services.length) {
    body.innerHTML = '<p class="muted" style="margin:0">No services yet.</p>';
    return card;
  }

  const list = el('<ul class="list-plain"></ul>');

  services.forEach((service) => {
    list.appendChild(el(`
      <li>
        <div style="min-width:0;flex:1">
          <a href="#/services/${service.id}/edit" class="cell-title">${esc(service.name)}</a>
          <span class="cell-sub">${esc(service.category_name || 'Uncategorised')}
            · ${esc(service.price_display || '$' + service.price)}
            · ${esc(relativeTime(service.updated_at))}${
              service.updated_by_name ? ' by ' + esc(service.updated_by_name) : ''
            }</span>
        </div>
        ${statusPill(service.status)}
      </li>
    `));
  });

  body.appendChild(list);
  return card;
}

function recentPromotionsPanel(promotions) {
  const card = el(`
    <div class="card">
      <div class="card__head">
        <h3>Recent promotions</h3>
        <a class="btn btn--ghost btn--sm" href="#/promotions">View all</a>
      </div>
      <div class="card__body"></div>
    </div>
  `);

  const body = card.querySelector('.card__body');

  if (!promotions || !promotions.length) {
    body.innerHTML = '<p class="muted" style="margin:0">No promotions yet.</p>';
    return card;
  }

  const list = el('<ul class="list-plain"></ul>');

  promotions.forEach((promotion) => {
    const variants = { active: 'ok', scheduled: 'info', expired: 'warn', draft: '', inactive: '' };
    list.appendChild(el(`
      <li>
        <div style="min-width:0;flex:1">
          <a href="#/promotions/${promotion.id}/edit" class="cell-title">${esc(promotion.title)}</a>
          <span class="cell-sub">Created ${esc(relativeTime(promotion.created_at))}${
            promotion.created_by_name ? ' by ' + esc(promotion.created_by_name) : ''
          }</span>
        </div>
        <span class="pill pill--${variants[promotion.effective_status] ?? ''}">${
          esc(promotion.effective_status)
        }</span>
      </li>
    `));
  });

  body.appendChild(list);
  return card;
}

function activityPanel(entries) {
  const card = el(`
    <div class="card">
      <div class="card__head">
        <h3>Recent activity</h3>
        <a class="btn btn--ghost btn--sm" href="#/audit-logs">View log</a>
      </div>
      <div class="card__body"></div>
    </div>
  `);

  const body = card.querySelector('.card__body');

  if (!entries || !entries.length) {
    body.innerHTML = '<p class="muted" style="margin:0">No activity recorded yet.</p>';
    return card;
  }

  const list = el('<ul class="timeline"></ul>');

  entries.forEach((entry) => {
    list.appendChild(el(`
      <li>
        <span class="timeline__dot"></span>
        <div class="timeline__body">
          <b>${esc(entry.description)}</b>
          <small>${esc(entry.actor)} · ${esc(relativeTime(entry.created_at))}</small>
        </div>
      </li>
    `));
  });

  body.appendChild(list);
  return card;
}
