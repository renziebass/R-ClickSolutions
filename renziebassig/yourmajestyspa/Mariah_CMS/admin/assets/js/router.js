/**
 * Hash router.
 *
 * Routes look like "#/services", "#/services/new", "#/services/12/edit".
 * Each route declares the permission required to reach it; an unpermitted
 * route renders a "no access" panel rather than a blank screen.
 */

import { session } from './session.js';
import { el, esc } from './ui/dom.js';

const routes = [];

export function route(pattern, handler, options = {}) {
  const params = [];
  const regex = new RegExp('^' + pattern.replace(/:([a-zA-Z]+)/g, (_, name) => {
    params.push(name);
    return '([^/]+)';
  }) + '$');

  routes.push({ pattern, regex, params, handler, ...options });
}

export function navigate(path) {
  if (window.location.hash === '#' + path) {
    handleRoute();   // same hash fires no event; re-render explicitly
    return;
  }
  window.location.hash = path;
}

export function currentPath() {
  return window.location.hash.replace(/^#/, '') || '/';
}

let outlet = null;
let onNavigate = null;

export function startRouter(outletEl, callbacks = {}) {
  outlet = outletEl;
  onNavigate = callbacks.onNavigate || null;

  window.addEventListener('hashchange', handleRoute);
  handleRoute();
}

async function handleRoute() {
  const path = currentPath();

  const match = routes
    .map((r) => ({ route: r, result: r.regex.exec(path) }))
    .find((m) => m.result !== null);

  if (!match) {
    renderNotFound(path);
    return;
  }

  const { route: matched, result } = match;

  const args = {};
  matched.params.forEach((name, index) => { args[name] = result[index + 1]; });

  if (matched.permission && !session.can(matched.permission)) {
    renderForbidden();
    return;
  }

  if (matched.permissionAny && !session.canAny(...matched.permissionAny)) {
    renderForbidden();
    return;
  }

  if (onNavigate) onNavigate(matched, args);

  outlet.replaceChildren();
  window.scrollTo({ top: 0 });

  try {
    await matched.handler(outlet, args);
  } catch (error) {
    outlet.replaceChildren(el(`
      <div class="card"><div class="error-state">
        <h3>This page could not be loaded</h3>
        <p>${esc(error.message || 'Unexpected error.')}</p>
      </div></div>
    `));
  }
}

function renderNotFound(path) {
  outlet.replaceChildren(el(`
    <div class="card"><div class="empty">
      <h3>Page not found</h3>
      <p>No screen matches <code>${esc(path)}</code>.</p>
      <a class="btn" href="#/">Back to dashboard</a>
    </div></div>
  `));
}

function renderForbidden() {
  outlet.replaceChildren(el(`
    <div class="card"><div class="empty">
      <h3>No access</h3>
      <p>Your role (${esc(session.roleName)}) does not include permission to view this section.
         Ask a Super Admin if you need it.</p>
      <a class="btn btn--ghost" href="#/">Back to dashboard</a>
    </div></div>
  `));
}
