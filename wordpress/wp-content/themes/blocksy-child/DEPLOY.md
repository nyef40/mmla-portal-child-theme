# Deploy Theme to GoDaddy (Fix Login + Register + Contact)

## What was fixed

1. **Login "Security check failed"** – Backend now accepts both `portal_login_nonce` and `portal_nonce`. Deploy `functions-portal-auth-enhanced.php`.
2. **Register link** – Nav "Register" now points to `/register/` (was `/portal/register/`). All nav links use `home_url()` so they resolve correctly.
3. **Contact** – Nav "Contact" now points to `/contact/` (no logout). Logout remains a separate "Logout" button.
4. **Single auth source** – `functions.php` loads `functions-portal-auth-enhanced.php` if present; login/register handlers are defined in one place (no duplicate definitions).

## Files to upload (replace on server)

Upload these from your local theme to  
`~/public_html/wp-content/themes/blocksy-child/` on GoDaddy:

| File | Purpose |
|------|--------|
| **functions-portal-auth-enhanced.php** | **Required** – dual nonce for login; fixes "Security check failed" |
| **functions.php** | Loads auth-enhanced, fixes nav URLs (Register → /register/, Login → /portal-login/, Contact → /contact/) |
| **portal-loader.php** | Correct nonce and logoutUrl for React app |
| **portal/dist/portal.js** | Rebuilt bundle (login/register redirects) |
| **portal/dist/portal.css** | Rebuilt styles |

## One-line deploy (from project root on your Mac)

```bash
cd /Users/nikolayyefimov/Projects/mmla-portal

# Required: fix login on live
scp wordpress/wp-content/themes/blocksy-child/functions-portal-auth-enhanced.php \
  c9gyjyiudq9m@198.12.217.103:/home/c9gyjyiudq9m/public_html/wp-content/themes/blocksy-child/

# Then upload the rest (nav + portal build)
scp wordpress/wp-content/themes/blocksy-child/functions.php \
  wordpress/wp-content/themes/blocksy-child/portal-loader.php \
  c9gyjyiudq9m@198.12.217.103:/home/c9gyjyiudq9m/public_html/wp-content/themes/blocksy-child/

scp wordpress/wp-content/themes/blocksy-child/portal/dist/portal.js \
  wordpress/wp-content/themes/blocksy-child/portal/dist/portal.css \
  c9gyjyiudq9m@198.12.217.103:/home/c9gyjyiudq9m/public_html/wp-content/themes/blocksy-child/portal/dist/
```

## Live registration = local (same code path)

Registration is handled **only** in `functions.php` (`portal_process_registration` + `handle_portal_register`). It:

- Inserts into `lqbk_portal_users` with `email_verified=0`, `validation_token`, `token_expiry`
- Sends verification email; link sets `email_verified=1` and redirects to `/portal-login/?verified=1`

**Required on live:** Exclude `/register/` and `/portal-login/` from **page cache** (e.g. WP-Optimize). Otherwise cached HTML can send a stale nonce and registration will fail with "Security check failed". After excluding, hard-refresh the register page and test.

## After deploy

1. Hard-refresh https://mobilemedicalla.com/portal-login/ (Cmd+Shift+R).
2. Click **Register** – should go to https://mobilemedicalla.com/register/ (not dashboard).
3. Submit register form – success message, new row in `lqbk_portal_users`, verification email; click link → `email_verified=1`, redirect to login.
4. Click **Login** – should go to https://mobilemedicalla.com/portal-login/ and login should succeed (no "Security check failed").
5. When logged in, **Contact** should go to https://mobilemedicalla.com/contact/ without logging you out.

## Run auth test locally

```bash
cd wordpress/wp-content/themes/blocksy-child/portal
PORTAL_USER=mmla2024 PORTAL_PASS=tiger2025 npm run test:auth
```

After deploying the fixed auth file, this test should report **PASS: Login succeeded**.
