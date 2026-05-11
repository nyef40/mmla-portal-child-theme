# Portal Test Cases

Use these after deploy to verify register, login, duplicates, and email verification.

---

## Prerequisites

- **Cache:** Exclude from page cache: `/portal-login/`, `/register/`, `/portal-profile/`, `/portal-resources/`, `/contact/`.
- **Browser:** Use a private/incognito window or hard refresh (Ctrl+Shift+R / Cmd+Shift+R) when testing register/login so cached HTML/JS are not used.
- **DB:** Access to `lqbk_portal_users` (e.g. phpMyAdmin) to check new rows and `email_verified`.

---

## TC1: No duplicate content (5 pages)

**Goal:** Only one main content block per page.

| Step | Action | Expected |
|------|--------|----------|
| 1.1 | Open https://mobilemedicalla.com/portal-login/ | Single login form; no second form or duplicate block. |
| 1.2 | Open https://mobilemedicalla.com/register/ | Single register form. |
| 1.3 | Open https://mobilemedicalla.com/portal-profile/ (logged in) | Single profile view. |
| 1.4 | Open https://mobilemedicalla.com/portal-resources/ | Single resources view. |
| 1.5 | Open https://mobilemedicalla.com/contact/ | Single contact form/view. |

**Pass:** Each URL shows exactly one main content area (no duplicated form or layout).

---

## TC2: Register – success path (AJAX)

**Goal:** Submit register form → success message → new row in DB → verification email sent.

| Step | Action | Expected |
|------|--------|----------|
| 2.1 | Open https://mobilemedicalla.com/register/ (private window). | Register form loads. |
| 2.2 | Fill: unique username (e.g. testuserNN), email (real inbox), password, confirm password, required fields. Click "Create Account". | "Creating Account..." then "Registration successful! Please check your email to verify your account."; redirect to /portal-login/ (or message stays and redirect after ~2s). |
| 2.3 | Check DB: `SELECT id, username, email, email_verified, created_at FROM lqbk_portal_users ORDER BY id DESC LIMIT 1;` | New row with that username/email; `email_verified = 0`. |
| 2.4 | Check inbox for verification email. | Email received with link containing `action=verify_portal_email&token=...&uid=...`. |

**Pass:** Success message, new row with `email_verified=0`, and verification email received.

---

## TC3: Register – full-page POST fallback

**Goal:** If form submits without AJAX (e.g. JS broken), server still processes and redirects.

| Step | Action | Expected |
|------|--------|----------|
| 3.1 | On /register/, disable JS or use a client that does a normal form POST. Submit the form. | Redirect to either /portal-login/ (success) or /register/?error=1 with an error message (e.g. "Security check failed" or "Username already exists"). |
| 3.2 | If redirected to /register/?error=1 | Page shows the error from the transient (e.g. in `#portal-register-messages`). |

**Pass:** No blank page; user sees either success redirect or clear error on register page.

---

## TC4: Email verification – link sets email_verified = 1

**Goal:** Clicking the link in the verification email sets `email_verified = 1` and redirects to login.

| Step | Action | Expected |
|------|--------|----------|
| 4.1 | From TC2, open the verification link from the email (same browser/session ok). | Redirect to https://mobilemedicalla.com/portal-login/?verified=1 (or similar with verified=1). |
| 4.2 | Check DB: same user row. | `email_verified = 1`; `validation_token` and `token_expiry` cleared (NULL). |
| 4.3 | Log in with that user on /portal-login/. | Login succeeds (no “verify email” block if your login logic checks email_verified). |

**Pass:** Link works, DB updated to `email_verified=1`, user can log in.

---

## TC5: No E_STRICT deprecation in log

**Goal:** Debug log does not show E_STRICT deprecation.

| Step | Action | Expected |
|------|--------|----------|
| 5.1 | Trigger a few portal actions (e.g. load register, submit register, load portal-login). | Normal log lines (e.g. REGISTRATION ATTEMPT, TEMPLATE LOADED). |
| 5.2 | Run: `tail -n 100 wp-content/debug.log` on host. | No line containing "Constant E_STRICT is deprecated" or "E_STRICT is deprecated". |

**Pass:** No E_STRICT deprecation in the last 100 lines.

---

## TC6: Upper vs lower register form (when duplicates existed)

**Goal:** If the page ever had two forms (“upper” cached, “lower” from template), both should behave the same after fix.

| Step | Action | Expected |
|------|--------|----------|
| 6.1 | Clear page cache for /register/; open /register/ in private window. | Only one register form visible (TC1). |
| 6.2 | Submit that form with new credentials. | Same as TC2 (success message, DB row, email). No "An error occurred" or "Nonce verification failed" from a fresh load. |

**Pass:** Single form; submit uses fresh nonce and succeeds.

---

## Quick checklist (after deploy)

- [ ] TC1: All 5 pages show a single block (no duplicates).
- [ ] TC2: Register → success message, new row, verification email.
- [ ] TC3: Full-POST register → redirect + message (no blank/500).
- [ ] TC4: Verification link → `email_verified=1`, redirect to login.
- [ ] TC5: No E_STRICT in debug.log.
- [ ] TC6: Single form on /register/, submit works without nonce error.

---

## Optional: One-liner DB checks (on host)

```bash
# Latest portal user
mysql -u USER -p DATABASE -e "SELECT id, username, email, email_verified, created_at FROM lqbk_portal_users ORDER BY id DESC LIMIT 1;"

# After verification
mysql -u USER -p DATABASE -e "SELECT id, username, email_verified, validation_token, token_expiry FROM lqbk_portal_users ORDER BY id DESC LIMIT 1;"
```

Replace USER and DATABASE with your DB credentials.
