# Fix and Refactor on Host (GoDaddy) via SSH

## Why browser shows "Security check failed" but the test passes

- **Test** uses cache-busting (`?_t=...`) and gets a **fresh** nonce → login succeeds.
- **Browser** often loads a **cached** `/portal-login/` page with an **old nonce** → server rejects it.
- So: **live must not cache the login (and register) pages**, and theme must send no-cache headers (already in place).

## Strategy: fix on host directly via SSH

### Phase 1 – Exclude portal pages from page cache (do this first)

1. **SSH in**
   ```bash
   ssh c9gyjyiudq9m@198.12.217.103
   cd ~/public_html
   ```

2. **WP-Optimize (or your cache plugin): exclude these URLs from page cache**
   - In **WP-Optimize** → Cache (or Minify) → exclude / never cache:
     - `/portal-login/`
     - `/register/`
     - `/portal-profile/`
     - `/portal-resources/`
     - `/contact/`
   - Why: Cached HTML for these pages often lacks `#portal-page-main` and shows duplicates; excluding them ensures PHP runs and the correct template loads.
   - If your plugin uses patterns, add: `*portal-login*`, `*register*`, `*portal-profile*`, `*portal-resources*`, `*contact*`.
   - **Cloudflare / CDN:** Add page rules to bypass cache for `*mobilemedicalla.com/portal-login*`, `*register*`, etc.

3. **Confirm theme no-cache headers**
   Your theme already sends no-cache for the login page. On the server you can confirm the file has:
   ```bash
   grep -A2 "portal-login" wp-content/themes/blocksy-child/functions.php | head -10
   ```
   You should see `Cache-Control`, `Pragma`, `Expires` for `is_page('portal-login')`.

4. **Clear any existing caches after changing settings**
   - In the caching plugin: "Purge all" / "Clear cache".
   - If using a CDN, purge cache for the site or for `/portal-login/` and `/register/`.

### Phase 2 – Align live with local (full React direction)

- **Current state:** Local is moving from mixed WordPress/React to full React; live is still mixed.
- **On the host, you can:**

  **Option A – Minimal (recommended first)**  
  - Keep current setup (mixed WP + React).
  - Ensure the same theme files as local: `functions.php`, `functions-portal-auth-enhanced.php`, `portal-loader.php`, and `portal/dist/` (portal.js, portal.css).
  - Deploy by SCP from your Mac (as you did) or clone/pull from git on the server and run `npm run build` in `portal/` if you have Node on the server.

  **Option B – Refactor on host for full React**  
  - SSH in, edit under `wp-content/themes/blocksy-child/`:
    - Ensure **portal-loader.php** loads the React app for all portal routes (portal, portal-login, register, dashboard, etc.) and passes `portal_nonce` and `logoutUrl`.
    - Ensure **only one** login/register handler (auth-enhanced) and that it accepts both nonces.
    - Remove or bypass any **PHP login/register templates** that output a separate form (so the React app is the single UI for login/register).
  - Then deploy the built React bundle (or build on server with Node) so `portal/dist/portal.js` and `portal.css` are up to date.

### Phase 3 – Run the login test from the host

- Use the **curl-based script** (no Node needed). On the server:
  ```bash
  cd ~/public_html/wp-content/themes/blocksy-child/portal/scripts
  chmod +x test-auth.sh
  PORTAL_USER=mmla2024 PORTAL_PASS=tiger2025 ./test-auth.sh
  ```
- Or from theme root: `./portal/scripts/test-auth.sh`
- If you see **PASS**, the backend is fine; browser login will work once the login page is excluded from cache and users hard-refresh (Ctrl+Shift+R / Cmd+Shift+R) once.

## Checklist (run on host via SSH)

- [ ] Caching plugin or CDN: exclude portal-login, register, portal-profile, portal-resources, contact.
- [ ] Purge all caches after deploy.
- [ ] Confirm theme sends no-cache for login and register only (functions.php).
- [ ] Run `./test-auth.sh` from theme root (or `./portal/scripts/test-auth.sh`) with PORTAL_USER/PORTAL_PASS.
- [ ] In browser: hard refresh portal pages and test login, register, contact.

## Duplicates and "No #portal-page-main"

- If you still see two login/profile/resources/contact blocks and the console reports "No #portal-page-main", the browser is almost certainly receiving **cached HTML** from before the template fix.
- Fix: exclude the portal URLs above from **page cache** in WP-Optimize (or equivalent), then purge cache and hard-refresh. The theme forces the correct PHP template (template_include priority 999); cached responses bypass PHP.

## Contact page "logs out" user

- Theme sends no-cache only for portal-login and register. If contact still seemed to log you out, try: (1) excluding contact from page cache and purging, (2) checking that no plugin clears auth on that page. If it persists, inspect cookies and any form action on the contact page.

## ChunkLoadError (Blocksy theme 783, 328, 318)

- These come from the **Blocksy parent theme** (`/wp-content/themes/blocksy/static/bundle/...`), not the child theme. Usually: missing or blocked JS chunks on the server, or theme version mismatch. Fix: re-upload Blocksy theme files, or update the Blocksy theme; ensure no security/firewall rule blocks `*.js` under the theme path.

## Why "live does not match local"

- Local: you’re moving to full React; theme and auth are updated.
- Live: may have different theme files, cached pages, or different caching rules.
- Fix: deploy the same theme files (and built React app) to live, exclude login/register from cache, and use the host-run test to verify.
