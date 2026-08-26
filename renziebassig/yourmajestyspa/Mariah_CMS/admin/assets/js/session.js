/**
 * Signed-in identity and permission checks.
 *
 * These checks only decide what the interface offers. Every endpoint re-checks
 * the same permission server-side, so hiding a button is convenience, never
 * the security boundary.
 */

import { api, setCsrfToken } from './api.js';

export const session = {
  user: null,

  config: {
    uploadMaxBytes: 5 * 1024 * 1024,
  },

  async load() {
    const result = await api.get('/auth/me');
    this.user = result.data.user;
    // Merged, not replaced: uploadMaxBytes above is still a client-side
    // default that the server does not send.
    Object.assign(this.config, result.data.config || {});
    setCsrfToken(result.data.csrf_token);
    return this.user;
  },

  get permissions() {
    return this.user ? this.user.permissions : [];
  },

  can(permission) {
    return this.permissions.includes(permission);
  },

  canAny(...permissions) {
    return permissions.some((permission) => this.can(permission));
  },

  get isSuperAdmin() {
    return this.user?.role?.slug === 'super-admin';
  },

  get fullName() {
    return this.user ? this.user.full_name : '';
  },

  get roleName() {
    return this.user?.role?.name || '';
  },

  async logout() {
    try {
      await api.post('/auth/logout');
    } finally {
      window.location.href = 'login.html';
    }
  },
};
