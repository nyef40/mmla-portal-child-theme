const getAjaxUrl = () => window.wpPortalData?.ajaxUrl || '/wp-admin/admin-ajax.php';
const getNonce = () => window.wpPortalData?.nonce || '';
const getLogoutUrl = () => window.wpPortalData?.logoutUrl || '/wp-login.php?action=logout';

export const post = async (action, data = {}) => {
  const nonce = getNonce();
  if (!nonce && (action === 'portal_login' || action === 'portal_register')) {
    console.error('Portal nonce missing. Ensure wpPortalData.nonce is set (portal_nonce).');
  }
  const formData = new FormData();
  formData.append('action', action);
  formData.append('nonce', nonce);

  Object.keys(data).forEach((key) => {
    formData.append(key, data[key]);
  });

  const response = await fetch(getAjaxUrl(), {
    method: 'POST',
    body: formData,
    credentials: 'same-origin',
  });

  const json = await response.json().catch(() => ({}));
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

export const logout = () => {
  window.location.href = getLogoutUrl();
};