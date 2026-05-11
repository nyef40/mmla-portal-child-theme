# Strategy: Register Flow, Duplicates, and Deprecations

## Goals

1. **Eliminate duplicate content** on 5 portal pages (portal-login, register, portal-profile, portal-resources, contact).
2. **Suppress** "PHP Deprecated: Constant E_STRICT is deprecated" in logs.
3. **Fix register flow** so: success message appears, verification email is sent, new row is inserted in `lqbk_portal_users`, and clicking the link sets `email_verified = 1`.

---

## 1. Duplicates

**Cause:** Cached or theme/Elementor output can render a second block alongside our template (e.g. two forms on one page).

**Approach:**

- **Single canonical block:** Every portal PHP template wraps its content in `<div id="portal-page-main">`. Only one such block exists per page when our template runs.
- **Forced template:** `template_include` (priority 999) forces the correct template for the 5 pages by slug (and URI fallback), so our template always runs.
- **Hide-other CSS/JS:** On those 5 pages, `portal-loader.php` injects CSS and JS that:
  - Hides any sibling/content blocks that do **not** contain `#portal-page-main`.
  - Ensures `#portal-page-main` is visible.
- **Body class:** Those pages get `portal-single-view` and `page-template-page-*` so the hide selectors apply.
- **Cache:** Exclude these URLs from full-page cache (e.g. WP-Optimize) so the server sends our template output, not old HTML with two blocks. No-cache headers are sent for portal-login and register.

**Pages in scope:** portal-login, **register**, portal-profile, portal-resources, contact.

---

## 2. E_STRICT Deprecation

**Cause:** In PHP 8.4+, the constant `E_STRICT` itself is deprecated; using it in `error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT)` triggers a deprecation notice.

**Fix:** Use only `E_ALL & ~E_DEPRECATED` in `functions.php` (do not reference `E_STRICT`).

---

## 3. Register Flow

**Issues:**

- **Stale nonce:** Cached HTML/JS can submit an old nonce → "Nonce verification failed" / "An error occurred."
- **Lower form full POST:** If our JS doesn’t run (e.g. jQuery not yet loaded), form does full POST to `/register/`; nonce in that POST can also be stale if page was cached.

**Approach:**

- **Single block + no-cache:** Register page uses `#portal-page-main` and is included in duplicate-hiding and no-cache. After cache clear/exclusion, only one form is shown and our script runs.
- **Fresh nonce before submit:** Our JS calls `portal_register_fresh_nonce`, then submits with the new nonce so AJAX register always uses a valid nonce.
- **Fallback for full POST:** If the form still does a full POST (e.g. JS off or error), `template_redirect` handles POST to `/register/` with `action=portal_register`, runs `portal_process_registration($_POST)`, and redirects to success or `/register/?error=1` with a transient message.
- **Cache exclusion:** Exclude `/register/` and `/portal-login/` from page cache so nonces and HTML are fresh.

**Email verification:**

- After registration, `send_verification_email_direct()` sends an email with a link: `?action=verify_portal_email&token=...&uid=...`.
- `handle_portal_email_verification()` (on `init`) validates token and expiry, then sets `email_verified = 1` and clears `validation_token` / `token_expiry`.
- All redirects after verification use **`/portal-login/`** (not `/portal/login/`).

---

## 4. Host / Cache Checklist

On the host:

- **WP-Optimize / page cache:** Exclude: `/portal-login/`, `/register/`, `/portal-profile/`, `/portal-resources/`, `/contact/` (or the exact slugs used).
- **Browser:** Hard refresh or private window when testing register/login to avoid cached HTML/JS.
- **Debug log:** After changes, `tail -f wp-content/debug.log` should no longer show "E_STRICT is deprecated" or "Nonce verification failed" for a fresh register attempt.

---

## 5. Test Flow (High Level)

1. **Duplicates:** Open each of the 5 pages; only one main content block (one form or one view) should be visible.
2. **Register (AJAX):** Submit register form → success message → redirect to portal-login; new row in `lqbk_portal_users`; email received with verification link.
3. **Register (fallback POST):** If JS disabled or form does full POST → redirect to portal-login or register with error message; no duplicate user.
4. **Verification:** Click link in email → redirect to `/portal-login/?verified=1`; `email_verified = 1` for that user in DB.
5. **Logs:** No E_STRICT deprecation; no "Nonce verification failed" for a normal register after cache exclusion.

Detailed steps are in **TEST-CASES-PORTAL.md**.
