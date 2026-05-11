# Portal Deployment Guide: Local to Live (GoDaddy)

## Pre-Deployment Checklist

### 1. Verify Local Works Correctly
```bash
# Test all portal pages
http://localhost:8080/portal/           # Should load
http://localhost:8080/portal-login/     # Should show login form
http://localhost:8080/register/         # Should show register form
http://localhost:8080/dashboard/        # Should redirect if not logged in

# Test auth flow
1. Click "Register" → Fill form → Submit → Should create user and redirect to /portal/
2. Click "Logout" → Should return to /portal/ logged out state
3. Click "Login" → Fill form → Submit → Should redirect to /dashboard/
```

### 2. Database Verification
```sql
-- On local, verify tables exist
mysql> use mmla_portal_db;
mysql> SHOW TABLES LIKE '%portal%';
-- Should show: lqbk_portal_users, lqbk_portal_sessions, etc.

-- On live, verify same tables exist
mysql> use c9gyjyiu_wp989;
mysql> SHOW TABLES LIKE '%lqbk_portal%';
```

## Deployment Steps

### Step 1: Upload Theme Files to Live
```bash
# On local machine
cd /Users/nikolayyefimov/Projects/mmla-portal/wordpress/wp-content/themes/blocksy-child

# Create deployment package (excludes .git, node_modules, etc.)
tar -czf blocksy-child-deploy.tar.gz \
  --exclude='.git' \
  --exclude='node_modules' \
  --exclude='.DS_Store' \
  --exclude='portal/node_modules' \
  .

# Upload to GoDaddy via SCP
scp blocksy-child-deploy.tar.gz c9gyjyiudq9m@198.12.217.103:/home/c9gyjyiudq9m/

## c9gyjyiudq9m@p3plzcpnl503868 [~/public_html/wp-content/themes/blocksy-child]$ ls -lart /## home/c9gyjyiudq9m/blocksy-child-deploy.tar.gz 
## -rw-r--r-- 1 c9gyjyiudq9m c9gyjyiudq9m 277889 Dec 17 22:43 /home/c9gyjyiudq9m/
## blocksy-child-deploy.tar.gz

# SSH into GoDaddy
ssh godaddy

# Extract to theme directory
cd ~/public_html/wp-content/themes/
rm -rf blocksy-child.bak
mv blocksy-child blocksy-child.bak  # Backup existing

mkdir blocksy-child
cd blocksy-child
tar -xzf ~/blocksy-child-deploy.tar.gz

rm ~/blocksy-child-deploy.tar.gz

# Verify files
ls -la
# Should see: functions.php, functions-portal-auth-live-fix.php, portal/dist/, etc.
## all good

### Step 2: Fix File Permissions
```bash
# On GoDaddy server
cd ~/public_html/wp-content/themes/blocksy-child

# Set correct permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 644 functions*.php
chmod 755 portal/dist/

# Verify
ls -la functions*.php
# Should be: -rw-r--r--
```

### Step 3: Clear WordPress Cache
```bash
# On GoDaddy server
cd ~/public_html

# Clear object cache if exists
rm -rf wp-content/cache/*

# Clear transients via WP-CLI (if installed)
wp transient delete --all --path=/home/c9gyjyiudq9m/public_html
## Success: 8 transients deleted from the database.

# Or manually via MySQL
mysql c9gyjyiu_wp989 -e "DELETE FROM lqbk_options WHERE option_name LIKE '%_transient_%';"
```

### Step 4: Test Live Portal
```bash
# Open browser
https://mobilemedicalla.com/portal/

# Check console for errors
# Right-click → Inspect → Console tab
# Should NOT see: "portal.js 404" or "Uncaught ReferenceError"
## no errors: "portal.js 404" or "Uncaught ReferenceError"

# Test auth flow
1. Click "Login" button
   - Expected: Stays on https://mobilemedicalla.com/portal-login/
** yes, 'https://mobilemedicalla.com/portal-login/'
## 'login successfull' --> 'redirecting' -->taking longer -->Shows WordPress admin
   - Bug if: Redirects to https://mobilemedicalla.com/wp-admin/
## bug --> Redirects to wp-admin

2. Fill login form and submit
## (mmla2024, tiger2025)
   - Expected: Shows dashboard with logged-in menu
   - Bug if: Shows WordPress admin dashboard
## Shows WordPress admin --> bug

3. Click "Register" button
   - Expected: Stays on https://mobilemedicalla.com/register/
   ## bug --> Redirects to wp-admin
   - Bug if: Redirects to wp-admin
   ## bug --> Redirects to wp-admin
```

### Step 5: Check Debug Logs
```bash
# On GoDaddy server
cd ~/public_html

# Clear existing debug log
bash -c "echo '' > wp-content/debug.log"

# Test one login attempt
# Then check logs
tail -n 50 wp-content/debug.log

# Expected output (clean):
[2025-12-17 01:30:00] Portal Auth: Login attempt | User: test@example.com
[2025-12-17 01:30:01] Portal Auth: Login successful | User ID: 64

# Bug if you see:
# - 30+ lines of deprecation warnings
# - "_load_textdomain_just_in_time" errors
# - "Elementor nonce verification failed" (these are now suppressed)
```

## Troubleshooting

### Issue 1: Login Button Goes to wp-admin
**Symptom:** Clicking "Login" redirects to https://mobilemedicalla.com/wp-admin/

**Root Cause:** Portal auth interceptor not firing early enough

**Fix:**
```bash
# SSH to GoDaddy
cd ~/public_html/wp-content/themes/blocksy-child

# Verify functions-portal-auth-live-fix.php exists
ls -la functions-portal-auth-live-fix.php

# Check if it's included in functions.php
grep 'functions-portal-auth-live-fix' functions.php

# Should see:
# require_once get_stylesheet_directory() . '/functions-portal-auth-live-fix.php';

# If missing, add it manually
nano functions.php
# Add at the very end:
# require_once get_stylesheet_directory() . '/functions-portal-auth-live-fix.php';
```

### Issue 2: Portal Shows "Logged Out" When Admin is Logged In
**Symptom:** Portal shows logout state, but wp-admin shows "Howdy, admin"

**Root Cause:** Session mismatch between WordPress and portal

**Fix:**
```bash
# Check portal auth log
tail -n 100 ~/public_html/wp-content/portal-auth.log

# Look for:
# "User state: logged_in_no_portal" → WordPress user exists but not in portal_users table

# Fix: Add admin to portal_users table
mysql c9gyjyiu_wp989 <<EOF
INSERT INTO lqbk_portal_users (wp_user_id, username, email, first_name, last_name, role, email_verified, created_at)
SELECT ID, user_login, user_email, 'Admin', 'User', 'admin', 1, NOW()
FROM lqbk_users
WHERE ID = 1
ON DUPLICATE KEY UPDATE role = 'admin';
EOF
```

### Issue 3: Debug Log Too Noisy (30+ Lines Per Request)
**Symptom:** debug.log fills with deprecation warnings

**Fix:** Already included in functions-portal-consolidation-fix.php
```php
// Suppresses:
// - _load_textdomain_just_in_time
// - Implicitly marking parameter $x as nullable
// - Constant E_STRICT is deprecated
// - Firebase\JWT\JWT::encode()
// - ElementorPro nonce verification failed
```

## Verification Checklist

### Live Portal Must Pass All Tests:

- [ ] https://mobilemedicalla.com/portal/ loads without console errors
- [ ] Clicking "Login" stays on /portal-login/ (not wp-admin)
- [ ] Clicking "Register" stays on /register/ (not wp-admin)
- [ ] Successful login redirects to /dashboard/
- [ ] Successful register redirects to /portal/
- [ ] Logout button works and returns to logged-out state
- [ ] Debug log has fewer than 5 lines per request
- [ ] Portal auth log shows clean login/register attempts

### Database Consistency:

```sql
-- Both tables should be in sync
SELECT u.ID, u.user_login, u.user_email, p.role, p.created_at
FROM lqbk_users u
LEFT JOIN lqbk_portal_users p ON u.ID = p.wp_user_id
WHERE u.ID IN (1, 63);

-- Expected:
-- ID=1 (mmla2024) → role='admin', created_at has valid date
-- ID=63 (admin1) → role='HHA', created_at has valid date
```

## Rollback Plan

If deployment fails:

```bash
# SSH to GoDaddy
cd ~/public_html/wp-content/themes/

# Restore backup
rm -rf blocksy-child
mv blocksy-child.bak blocksy-child

# Clear cache
wp transient delete --all --path=/home/c9gyjyiudq9m/public_html

# Test
curl -I https://mobilemedicalla.com/portal/
# Should return 200 OK
```

## Post-Deployment Monitoring

### Check Every 24 Hours for First Week:

```bash
# Monitor debug log size
ls -lh ~/public_html/wp-content/debug.log
# Should stay under 1MB

# Monitor portal auth log
tail -n 50 ~/public_html/wp-content/portal-auth.log
# Should show clean login/register events

# Check database growth
mysql c9gyjyiu_wp989 -e "SELECT COUNT(*) FROM lqbk_portal_users;"
# Should increase as users register
