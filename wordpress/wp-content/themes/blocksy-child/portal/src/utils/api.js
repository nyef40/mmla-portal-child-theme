const getAjaxUrl = () => window.wpPortalData?.ajaxUrl || '/wp-admin/admin-ajax.php';
const getNonce = () => window.wpPortalData?.nonce || '';
const getLogoutUrl = () => window.wpPortalData?.logoutUrl || '/wp-login.php?action=logout';

/**
 * @typedef {Object} PortalApiResponse
 * @property {boolean} [success]
 * @property {string|Object} [data]
 * @property {string} [message]
 */

/**
 * @param {Record<string, unknown>} data
 * @returns {FormData}
 */
const toFormData = (data) => {
  const formData = new FormData();
  Object.keys(data).forEach((key) => {
    const value = data[key];
    if (value === undefined || value === null) return;
    formData.append(key, String(value));
  });
  return formData;
};

/**
 * @template T
 * @param {Response} response
 * @returns {Promise<T>}
 */
const parseJsonSafe = async (response) => {
  const json = await response.json().catch(() => ({}));
  return /** @type {T} */ (json);
};

/**
 * Reusable API helper for WordPress admin-ajax actions.
 * @template T
 * @param {string} action
 * @param {Record<string, unknown>} data
 * @returns {Promise<T>}
 */
export const requestAction = async (action, data = {}) => {
  const nonce = getNonce();
  if (!nonce && (action === 'portal_login' || action === 'portal_register')) {
    console.error('Portal nonce missing. Ensure wpPortalData.nonce is set (portal_nonce).');
  }
  const formData = toFormData({ action, nonce, ...data });

  const response = await fetch(getAjaxUrl(), {
    method: 'POST',
    body: formData,
    credentials: 'same-origin',
  });

  const json = await parseJsonSafe(/** @type {Response} */ (response));
  if (!response.ok) {
    const err = new Error(json.data || json.message || 'Request failed');
    err.response = response;
    err.data = json;
    throw err;
  }
  if (json.success === false && json.data) {
    const err = new Error(typeof json.data === 'string' ? json.data : json.data.message || 'Action failed');
    err.data = json.data;
    throw err;
  }
  return json;
};

/**
 * Backward compatible alias.
 * @param {string} action
 * @param {Record<string, unknown>} data
 * @returns {Promise<PortalApiResponse>}
 */
export const post = async (action, data = {}) => requestAction(action, data);

export const logout = () => {
  window.location.href = getLogoutUrl();
};