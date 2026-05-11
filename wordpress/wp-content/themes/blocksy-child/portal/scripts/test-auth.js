#!/usr/bin/env node
/**
 * Login / logout auth test for the portal.
 * Fetches portal-login page to get a fresh nonce, then POSTs portal_login and checks response.
 *
 * Usage:
 *   BASE_URL=https://mobilemedicalla.com npm run test:auth
 *   BASE_URL=http://localhost:8080 USER=testuser PASS=testpass npm run test:auth
 */

const BASE_URL = process.env.BASE_URL || 'https://mobilemedicalla.com';
const USER = process.env.PORTAL_USER || process.env.USER || 'mmla2024';
const PASS = process.env.PASS || process.env.PORTAL_PASS || 'tiger2025';

function log(msg, data) {
  console.log('[test:auth]', msg, data !== undefined ? JSON.stringify(data) : '');
}

async function fetchLoginPage() {
  const base = BASE_URL.replace(/\/$/, '');
  const url = `${base}/portal-login/?_t=${Date.now()}`;
  log('Fetching login page for nonce (cache-bust)', url);
  const res = await fetch(url, {
    redirect: 'follow',
    headers: {
      'User-Agent': 'PortalAuthTest/1.0',
      'Cache-Control': 'no-cache',
      Pragma: 'no-cache',
    },
  });
  const html = await res.text();
  if (!res.ok) {
    throw new Error(`Login page returned ${res.status}`);
  }
  let nonce = null;
  let ajaxUrl = `${base}/wp-admin/admin-ajax.php`;

  const wpPortalMatch = html.match(/wpPortalData\s*=\s*(\{[\s\S]*?\});?\s*<\/script>/);
  if (wpPortalMatch) {
    try {
      const raw = wpPortalMatch[1].replace(/&quot;/g, '"').replace(/&#039;/g, "'");
      const data = JSON.parse(raw);
      nonce = data.nonce || data.restNonce;
      if (data.ajaxUrl) ajaxUrl = data.ajaxUrl;
    } catch (e) {
      log('wpPortalData parse failed', e.message);
    }
  }
  if (!nonce) {
    const formNonce = html.match(/name=["']nonce["']\s+value=["']([a-zA-Z0-9]+)["']/i)
      || html.match(/formData\.append\(["']nonce["'],\s*["']([a-zA-Z0-9]+)["']\)/);
    if (formNonce) nonce = formNonce[1];
  }
  if (!nonce || nonce.length < 8) {
    const freshRes = await fetch(ajaxUrl, {
      method: 'POST',
      body: new URLSearchParams({ action: 'portal_login_fresh_nonce' }),
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    });
    const freshJson = await freshRes.json().catch(() => ({}));
    if (freshJson.success && freshJson.data && freshJson.data.nonce) nonce = freshJson.data.nonce;
  }
  if (!nonce || nonce.length < 8) {
    throw new Error('No valid nonce found on page or from portal_login_fresh_nonce');
  }
  log('Nonce obtained (length ' + nonce.length + ')');
  return { nonce, ajaxUrl };
}

async function postLogin(nonce, ajaxUrl) {
  const form = new URLSearchParams();
  form.append('action', 'portal_login');
  form.append('nonce', nonce);
  form.append('username', USER);
  form.append('password', PASS);
  form.append('remember', '0');

  log('POSTing portal_login to', ajaxUrl);
  const res = await fetch(ajaxUrl, {
    method: 'POST',
    body: form,
    redirect: 'follow',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'User-Agent': 'PortalAuthTest/1.0',
      Referer: `${BASE_URL}/portal-login/`,
    },
  });
  const json = await res.json().catch(() => ({}));
  return { ok: res.ok, status: res.status, json };
}

async function run() {
  console.log('\n--- Portal login/logout test ---');
  log('BASE_URL', BASE_URL);
  log('USER', USER);

  try {
    const { nonce, ajaxUrl } = await fetchLoginPage();
    const { ok, status, json } = await postLogin(nonce, ajaxUrl);

    if (status !== 200) {
      log('FAIL: admin-ajax.php returned status', status);
      process.exitCode = 1;
      return;
    }

    if (json.success) {
      log('PASS: Login succeeded', { redirect: json.data?.redirect });
      return;
    }

    const msg = json.data || json.message || 'Unknown error';
    if (msg === 'Security check failed') {
      log('FAIL: Security check failed (nonce rejected). Ensure backend accepts portal_nonce and portal_login_nonce.');
      process.exitCode = 1;
      return;
    }
    if (msg.includes('Invalid') || msg.includes('password') || msg.includes('username')) {
      log('FAIL: Credentials rejected:', msg);
      process.exitCode = 1;
      return;
    }
    log('FAIL: Login failed', msg);
    process.exitCode = 1;
  } catch (err) {
    log('ERROR', err.message);
    process.exitCode = 1;
  }
}

run();
