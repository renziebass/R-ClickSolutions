/**
 * Admin SPA entry point.
 *
 * Loads the session, builds the permission-filtered sidebar, registers routes
 * and hands control to the hash router.
 */

import { setUnauthenticatedHandler } from './api.js';
import { session } from './session.js';
import { navigate, route, startRouter } from './router.js';
import { el, esc, icon, initials } from './ui/dom.js';
import { notify } from './ui/feedback.js';

import { dashboardPage } from './pages/dashboard.js';
import { servicesPage, serviceFormPage } from './pages/services.js';
import { categoriesPage, categoryFormPage } from './pages/categories.js';
import { promotionsPage, promotionFormPage } from './pages/promotions.js';
import { specialsPage, specialFormPage } from './pages/specials.js';
import {
  productsPage, productFormPage,
  productCategoriesPage, productCategoryFormPage,
  brandsPage, brandFormPage,
} from './pages/shop.js';
import { giftCardsPage, giftCardFormPage } from './pages/gift-cards.js';
import { mediaPage } from './pages/media.js';
import { usersPage, userFormPage } from './pages/users.js';
import { rolesPage, roleFormPage } from './pages/roles.js';
import { auditLogsPage } from './pages/audit-logs.js';
import { settingsPage } from './pages/settings.js';

// =================================================================
// Navigation definition — each item declares what it needs to be seen.
// =================================================================
const NAV = [
  {
    group: null,
    items: [
      { label: 'Dashboard', path: '/', iconName: 'i-grid', permission: 'dashboard.view' },
    ],
  },
  {
    group: 'Content',
    items: [
      { label: 'Services', path: '/services', iconName: 'i-sparkle', permission: 'services.view' },
      { label: 'Categories', path: '/categories', iconName: 'i-folder', permission: 'categories.view' },
      { label: 'Promotions', path: '/promotions', iconName: 'i-tag', permission: 'promotions.view' },
      { label: 'Specials', path: '/specials', iconName: 'i-star', permission: 'specials.view' },
      { label: 'Media', path: '/media', iconName: 'i-image', permission: 'media.view' },
    ],
  },
  {
    group: 'Shop',
    items: [
      { label: 'Products', path: '/products', iconName: 'i-bag', permission: 'products.view' },
      { label: 'Product types', path: '/product-categories', iconName: 'i-folder', permission: 'product_categories.view' },
      { label: 'Brands', path: '/brands', iconName: 'i-crown', permission: 'brands.view' },
      { label: 'Gift cards', path: '/gift-cards', iconName: 'i-gift', permission: 'gift_cards.view' },
    ],
  },
  {
    group: 'Management',
    items: [
      { label: 'Users', path: '/users', iconName: 'i-users', permission: 'users.view' },
      { label: 'Roles & permissions', path: '/roles', iconName: 'i-shield', permission: 'roles.view' },
      { label: 'Audit logs', path: '/audit-logs', iconName: 'i-history', permission: 'audit_logs.view' },
    ],
  },
  {
    group: 'System',
    items: [
      { label: 'Settings', path: '/settings', iconName: 'i-settings' },
    ],
  },
];

// Breadcrumb labels per route pattern.
const CRUMBS = {
  '/': ['Dashboard'],
  '/services': ['Content', 'Services'],
  '/services/new': ['Content', 'Services', 'Add service'],
  '/services/:id/edit': ['Content', 'Services', 'Edit service'],
  '/categories': ['Content', 'Categories'],
  '/categories/new': ['Content', 'Categories', 'Add category'],
  '/categories/:id/edit': ['Content', 'Categories', 'Edit category'],
  '/promotions': ['Content', 'Promotions'],
  '/promotions/new': ['Content', 'Promotions', 'Add promotion'],
  '/promotions/:id/edit': ['Content', 'Promotions', 'Edit promotion'],
  '/specials': ['Content', 'Specials'],
  '/specials/new': ['Content', 'Specials', 'Add special'],
  '/specials/:id/edit': ['Content', 'Specials', 'Edit special'],
  '/media': ['Content', 'Media'],
  '/products': ['Shop', 'Products'],
  '/products/new': ['Shop', 'Products', 'Add product'],
  '/products/:id/edit': ['Shop', 'Products', 'Edit product'],
  '/product-categories': ['Shop', 'Product types'],
  '/product-categories/new': ['Shop', 'Product types', 'Add type'],
  '/product-categories/:id/edit': ['Shop', 'Product types', 'Edit type'],
  '/brands': ['Shop', 'Brands'],
  '/brands/new': ['Shop', 'Brands', 'Add brand'],
  '/brands/:id/edit': ['Shop', 'Brands', 'Edit brand'],
  '/gift-cards': ['Shop', 'Gift cards'],
  '/gift-cards/new': ['Shop', 'Gift cards', 'Add offering'],
  '/gift-cards/:id/edit': ['Shop', 'Gift cards', 'Edit offering'],
  '/users': ['Management', 'Users'],
  '/users/new': ['Management', 'Users', 'Add user'],
  '/users/:id/edit': ['Management', 'Users', 'Edit user'],
  '/roles': ['Management', 'Roles & permissions'],
  '/roles/new': ['Management', 'Roles', 'Add role'],
  '/roles/:id/edit': ['Management', 'Roles', 'Role detail'],
  '/audit-logs': ['Management', 'Audit logs'],
  '/settings': ['System', 'Settings'],
};

// =================================================================
async function boot() {
  setUnauthenticatedHandler(() => {
    // Only bounce once, and remember where to come back to.
    if (!window.__redirecting) {
      window.__redirecting = true;
      sessionStorage.setItem('mariah_return_to', window.location.hash);
      window.location.href = 'login.html';
    }
  });

  try {
    await session.load();
  } catch {
    sessionStorage.setItem('mariah_return_to', window.location.hash);
    window.location.href = 'login.html';
    return;
  }

  document.getElementById('boot').remove();
  document.getElementById('app').hidden = false;

  buildSidebar();
  buildUserBlock();

  registerRoutes();

  startRouter(document.getElementById('view'), {
    onNavigate: (matched) => {
      setCrumbs(CRUMBS[matched.pattern] || []);
      highlightNav(matched.pattern);
      closeSidebar();
    },
  });

  // Restore the page the user was on before the session expired.
  const returnTo = sessionStorage.getItem('mariah_return_to');
  if (returnTo && returnTo.length > 1) {
    sessionStorage.removeItem('mariah_return_to');
    navigate(returnTo.replace(/^#/, ''));
  }
}

function registerRoutes() {
  route('/', dashboardPage, { permission: 'dashboard.view' });

  route('/services', servicesPage, { permission: 'services.view' });
  route('/services/new', serviceFormPage, { permission: 'services.create' });
  route('/services/:id/edit', serviceFormPage, { permission: 'services.edit' });

  route('/categories', categoriesPage, { permission: 'categories.view' });
  route('/categories/new', categoryFormPage, { permission: 'categories.create' });
  route('/categories/:id/edit', categoryFormPage, { permission: 'categories.edit' });

  route('/promotions', promotionsPage, { permission: 'promotions.view' });
  route('/promotions/new', promotionFormPage, { permission: 'promotions.create' });
  route('/promotions/:id/edit', promotionFormPage, { permission: 'promotions.edit' });

  route('/specials', specialsPage, { permission: 'specials.view' });
  route('/specials/new', specialFormPage, { permission: 'specials.create' });
  route('/specials/:id/edit', specialFormPage, { permission: 'specials.edit' });

  route('/products', productsPage, { permission: 'products.view' });
  route('/products/new', productFormPage, { permission: 'products.create' });
  route('/products/:id/edit', productFormPage, { permission: 'products.edit' });

  route('/product-categories', productCategoriesPage, { permission: 'product_categories.view' });
  route('/product-categories/new', productCategoryFormPage, { permission: 'product_categories.create' });
  route('/product-categories/:id/edit', productCategoryFormPage, { permission: 'product_categories.edit' });

  route('/brands', brandsPage, { permission: 'brands.view' });
  route('/brands/new', brandFormPage, { permission: 'brands.create' });
  route('/brands/:id/edit', brandFormPage, { permission: 'brands.edit' });

  route('/gift-cards', giftCardsPage, { permission: 'gift_cards.view' });
  route('/gift-cards/new', giftCardFormPage, { permission: 'gift_cards.create' });
  route('/gift-cards/:id/edit', giftCardFormPage, { permission: 'gift_cards.edit' });

  route('/media', mediaPage, { permission: 'media.view' });

  route('/users', usersPage, { permission: 'users.view' });
  route('/users/new', userFormPage, { permission: 'users.create' });
  route('/users/:id/edit', userFormPage, { permission: 'users.edit' });

  route('/roles', rolesPage, { permission: 'roles.view' });
  route('/roles/new', roleFormPage, { permission: 'roles.create' });
  route('/roles/:id/edit', roleFormPage, { permission: 'roles.view' });

  route('/audit-logs', auditLogsPage, { permission: 'audit_logs.view' });

  route('/settings', settingsPage);
}

// =================================================================
function buildSidebar() {
  const nav = document.getElementById('sidebar-nav');
  nav.replaceChildren();

  NAV.forEach((section) => {
    const visible = section.items.filter(
      (item) => !item.permission || session.can(item.permission)
    );

    if (!visible.length) return;

    if (section.group) {
      nav.appendChild(el(`<div class="sidebar__group">${esc(section.group)}</div>`));
    }

    visible.forEach((item) => {
      nav.appendChild(el(`
        <a class="sidebar__link" href="#${esc(item.path)}" data-path="${esc(item.path)}">
          ${icon(item.iconName, 17)} <span>${esc(item.label)}</span>
        </a>
      `));
    });
  });
}

function buildUserBlock() {
  const foot = document.getElementById('sidebar-foot');

  // el() returns only the first root element of the string it is given, and
  // .sidebar__foot is a flex row that lays out all three of these as siblings —
  // so they are built individually rather than as one multi-root fragment.
  const avatar = el(`<span class="sidebar__avatar">${esc(initials(session.fullName))}</span>`);

  const user = el(`
    <span class="sidebar__user">
      <b>${esc(session.fullName)}</b>
      <small>${esc(session.roleName)}</small>
    </span>
  `);

  const logout = el(`
    <button class="sidebar__logout" title="Sign out" aria-label="Sign out">
      ${icon('i-logout', 18)}
    </button>
  `);

  foot.replaceChildren(avatar, user, logout);

  logout.addEventListener('click', async () => {
    notify.ok('Signing out…');
    await session.logout();
  });
}

function highlightNav(pattern) {
  // "/services/12/edit" should light up the "Services" item.
  const base = '/' + (pattern.split('/')[1] || '');

  document.querySelectorAll('.sidebar__link').forEach((link) => {
    const path = link.dataset.path;
    const isActive = path === pattern
      || (path !== '/' && base === path)
      || (pattern === '/' && path === '/');
    link.classList.toggle('is-active', isActive);
  });
}

function setCrumbs(parts) {
  const crumbs = document.getElementById('crumbs');

  if (!parts.length) {
    crumbs.replaceChildren();
    return;
  }

  const html = ['<a href="#/">Majesty Spa</a>'];
  parts.forEach((part, index) => {
    html.push('<span>/</span>');
    html.push(index === parts.length - 1 ? `<b>${esc(part)}</b>` : esc(part));
  });

  crumbs.innerHTML = html.join(' ');
}

// --- Mobile sidebar ----------------------------------------------
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('is-open');
  document.getElementById('scrim').classList.remove('is-on');
}

document.getElementById('burger').addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('is-open');
  document.getElementById('scrim').classList.toggle('is-on');
});

document.getElementById('scrim').addEventListener('click', closeSidebar);

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') closeSidebar();
});

boot();
