/**
 * API client.
 *
 * Wraps every call in the server's response envelope, attaches the CSRF token
 * to mutations, and turns failures into a typed ApiError the UI can branch on
 * (field errors go onto form inputs, everything else becomes a toast).
 */

// This file lives at Mariah_CMS/admin/assets/js/api.js, so the API root is
// three levels up: js → assets → admin → Mariah_CMS.
const BASE = new URL('../../../api', new URL('.', import.meta.url)).pathname.replace(/\/$/, '');

export class ApiError extends Error {
  constructor(status, code, message, fields) {
    super(message);
    this.status = status;
    this.code = code;
    this.fields = fields || {};
  }

  get isValidation() {
    return this.status === 422 || this.code === 'VALIDATION_FAILED';
  }

  get isAuth() {
    return this.status === 401;
  }

  get isForbidden() {
    return this.status === 403;
  }

  /** A stale CSRF token means the session rotated — the page must reload. */
  get isCsrf() {
    return this.status === 419 || this.code === 'CSRF_TOKEN_MISMATCH';
  }
}

let csrfToken = null;

export function setCsrfToken(token) {
  csrfToken = token || null;
}

export function getCsrfToken() {
  return csrfToken;
}

/** Called when a request comes back 401, so the app can bounce to login. */
let onUnauthenticated = null;
export function setUnauthenticatedHandler(fn) {
  onUnauthenticated = fn;
}

async function request(method, path, { body, query, isForm } = {}) {
  let url = BASE + path;

  if (query) {
    const params = new URLSearchParams();
    Object.entries(query).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.set(key, value);
      }
    });
    const qs = params.toString();
    if (qs) url += '?' + qs;
  }

  const options = {
    method,
    credentials: 'same-origin',
    headers: { Accept: 'application/json' },
  };

  if (csrfToken && !['GET', 'HEAD'].includes(method)) {
    options.headers['X-CSRF-Token'] = csrfToken;
  }

  if (body !== undefined) {
    if (isForm) {
      options.body = body; // FormData sets its own multipart boundary
    } else {
      options.headers['Content-Type'] = 'application/json';
      options.body = JSON.stringify(body);
    }
  }

  let response;
  try {
    response = await fetch(url, options);
  } catch {
    throw new ApiError(0, 'NETWORK', 'Could not reach the server. Check your connection and try again.');
  }

  let payload = null;
  const text = await response.text();

  if (text) {
    try {
      payload = JSON.parse(text);
    } catch {
      // A non-JSON body means PHP emitted a fatal error or the host returned
      // an HTML error page. Surface something actionable, not the raw HTML.
      throw new ApiError(
        response.status,
        'BAD_RESPONSE',
        'The server returned an unexpected response. Check the server error log.'
      );
    }
  }

  if (!response.ok || (payload && payload.success === false)) {
    const error = (payload && payload.error) || {};
    const apiError = new ApiError(
      response.status,
      error.code || 'ERROR',
      error.message || 'The request failed.',
      error.fields || {}
    );

    if (apiError.isAuth && onUnauthenticated) onUnauthenticated();

    throw apiError;
  }

  return payload;
}

export const api = {
  get: (path, query) => request('GET', path, { query }),
  post: (path, body) => request('POST', path, { body }),
  put: (path, body) => request('PUT', path, { body }),
  patch: (path, body) => request('PATCH', path, { body }),
  del: (path) => request('DELETE', path),

  upload(path, formData) {
    return request('POST', path, { body: formData, isForm: true });
  },

  /** Convenience for list endpoints: returns { data, meta }. */
  async list(path, query) {
    const result = await request('GET', path, { query });
    return { data: result.data || [], meta: result.meta || null };
  },
};
