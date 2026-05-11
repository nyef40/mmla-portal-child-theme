# MMLA Portal (React app)

React app for the Mobile Medical LA provider portal (login, dashboard, profile, resources, referrals, contact).

## Build

```bash
npm install
npm run build
```

Output: `dist/portal.js`, `dist/portal.css`. The theme enqueues these on portal pages.

## Dev

```bash
npm run dev    # watch build
npm run start  # webpack dev server (for local UI dev only; WP still needs theme)
```

## Login / logout test

The test fetches the portal-login page with **cache-busting** to get a fresh nonce, then POSTs `portal_login` to `admin-ajax.php` and checks the response.

**Node (from your Mac):**
```bash
# Live site (default credentials from env or mmla2024 / tiger2025)
npm run test:auth

# Custom URL and credentials
BASE_URL=https://mobilemedicalla.com PORTAL_USER=myuser PORTAL_PASS=mypass npm run test:auth
```

**Shell – on host via SSH (no Node):**
```bash
cd ~/public_html/wp-content/themes/blocksy-child/portal/scripts
./test-auth.sh
# Or: PORTAL_USER=mmla2024 PORTAL_PASS=tiger2025 BASE_URL=https://mobilemedicalla.com ./test-auth.sh
```

**If you see "Security check failed":** usually the **browser** has a cached login page with an old nonce. Exclude `/portal-login/` and `/register/` from any caching plugin or CDN; then hard-refresh the page (Cmd+Shift+R). The test uses cache-busting so it gets a fresh nonce and may pass even when the browser still fails until cache is fixed. See `STRATEGY-HOST.md` in the theme root.

## Nonce / backend

- **React app** receives `wpPortalData.nonce` from the theme (via `portal-loader.php` or `functions.php`). This must be `portal_nonce` for login to work.
- **PHP login template** (`page-portal-login-enhanced.php`) uses `portal_login_nonce`.
- Backend handler must accept **both** nonces; see `functions-portal-auth-enhanced.php` and `functions.php` in the theme.
