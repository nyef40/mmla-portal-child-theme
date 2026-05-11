# Deploy to GoDaddy – Login fix, portal build, test on host

## Why "Security check failed" in browser (and Register hangs)

- **Stale nonce:** The login form used a nonce rendered with the page. If the page was cached (browser or server), the nonce was old and the server rejected it.
- **Fix in code:** The login page **fetches a fresh nonce via AJAX** on load (`portal_login_fresh_nonce`), so even a cached HTML page gets a valid nonce. The backend accepts `portal_login_form_nonce` (from that endpoint), `portal_login_nonce`, and `portal_nonce`. No-cache headers are sent on the fresh-nonce and login responses so they are not cached.
- **Register "Loading Portal" hang:** The server had an **old** `portal/dist/` (multiple JS files from an old build). The theme expects a **single** `portal.js` + `portal.css` bundle. Deploy the current `portal/dist/` from local.

## One-command deploy (from your Mac)

From the **theme directory** (or project root, see below):

```bash
cd /Users/nikolayyefimov/Projects/mmla-portal/wordpress/wp-content/themes/blocksy-child
chmod +x deploy-to-host.sh
./deploy-to-host.sh
```

Or from project root:

```bash
cd /Users/nikolayyefimov/Projects/mmla-portal
bash wordpress/wp-content/themes/blocksy-child/deploy-to-host.sh
```

This uploads:

- `functions.php`, `functions-portal-auth-enhanced.php`, `portal-loader.php`
- `page-portal-login-enhanced.php` (fresh nonce on load)
- `portal/dist/portal.js`, `portal/dist/portal.css` (and removes old chunk files on host: login.js, dashboard.js, etc.)
- `test-auth.sh` (theme root)

Optional: set `HOST=user@ip` if different from default.

## Run the test on the host (SSH)

After deploy:

```bash
ssh c9gyjyiudq9m@198.12.217.103
cd ~/public_html/wp-content/themes/blocksy-child
chmod +x test-auth.sh
PORTAL_USER=mmla2024 PORTAL_PASS=tiger2025 ./test-auth.sh
```

You should see: `[test:auth] PASS: Login succeeded`.

## Manual upload (if you prefer SCP)

Upload to `~/public_html/wp-content/themes/blocksy-child/` on the server:

| Local path (theme dir) | Purpose |
|------------------------|--------|
| `functions.php` | Config, portal_debug, no-cache, fresh-nonce endpoint, auth |
| `functions-portal-auth-enhanced.php` | Login/register handlers (dual nonce) |
| `portal-loader.php` | Portal React app loader, nonce + logoutUrl |
| `page-portal-login-enhanced.php` | Login template with **fresh nonce on load** |
| `portal/dist/portal.js` | Single portal bundle (overwrites old multi-file build) |
| `portal/dist/portal.css` | Portal styles |
| `test-auth.sh` | Run on host to test login (no Node) |

Ensure `portal/scripts/` exists on the server if you use `portal/scripts/test-auth.sh`; otherwise use `test-auth.sh` in the theme root.

## Theme folder layout (reference)

- **Theme root:** `functions.php`, `portal-loader.php`, `functions-portal-auth-enhanced.php`, `page-portal-login-enhanced.php`, `test-auth.sh`, `deploy-to-host.sh`
- **portal/dist/** – only `portal.js` and `portal.css` (single React app). Remove old `login.js`, `dashboard.js`, etc. on the server if present.
- **portal/scripts/** – `test-auth.sh`, `test-auth.js` (Node test; optional on host)

## After deploy

1. In the browser, open https://mobilemedicalla.com/portal-login/ (hard refresh once is enough).
2. Log in – the page fetches a fresh nonce on load, so "Security check failed" should stop.
3. Click Register – the new `portal.js` should load the register view instead of hanging on "Loading Portal".
