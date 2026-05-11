#!/usr/bin/env bash
# Deploy blocksy-child theme (and portal build) to GoDaddy host.
# Run from project root: ./wordpress/wp-content/themes/blocksy-child/deploy-to-host.sh
# Or: bash wordpress/wp-content/themes/blocksy-child/deploy-to-host.sh
#
# Set HOST if different: HOST=user@1.2.3.4 ./deploy-to-host.sh

set -e
HOST="${HOST:-c9gyjyiudq9m@198.12.217.103}"
REMOTE_DIR="/home/c9gyjyiudq9m/public_html/wp-content/themes/blocksy-child"
THEME_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
THEME_NAME="$(basename "$THEME_DIR")"

echo "Deploying $THEME_NAME to $HOST"
echo "Local theme: $THEME_DIR"
echo "Remote: $HOST:$REMOTE_DIR"
echo ""

# 1. PHP and loader
scp "$THEME_DIR/functions.php" "$THEME_DIR/functions-portal-auth-enhanced.php" "$THEME_DIR/portal-loader.php" "$HOST:$REMOTE_DIR/"
echo "Uploaded functions.php, functions-portal-auth-enhanced.php, portal-loader.php"
ssh "$HOST" "mkdir -p $REMOTE_DIR/includes"
scp "$THEME_DIR/includes/PortalAuthService.php" "$THEME_DIR/includes/PortalRegistrationService.php" "$HOST:$REMOTE_DIR/includes/"
echo "Uploaded includes/PortalAuthService.php, includes/PortalRegistrationService.php"

# 2. Page templates (login, register, dashboard, referrals, profile, resources, contact)
scp "$THEME_DIR/page-portal-login-enhanced.php" "$THEME_DIR/page-register-enhanced.php" "$THEME_DIR/page-dashboard.php" "$THEME_DIR/page-portal-referrals.php" "$THEME_DIR/page-profile.php" "$THEME_DIR/page-portal-resources.php" "$THEME_DIR/page-contact.php" "$HOST:$REMOTE_DIR/"
echo "Uploaded page templates (login, register, dashboard, referrals, profile, resources, contact)"

# 3. Portal dist (single-app bundle – overwrites old multi-file build)
ssh "$HOST" "mkdir -p $REMOTE_DIR/portal/dist"
scp "$THEME_DIR/portal/dist/portal.js" "$THEME_DIR/portal/dist/portal.css" "$HOST:$REMOTE_DIR/portal/dist/"
# Remove old chunk files so only portal.js + portal.css are used (avoids Register hang)
ssh "$HOST" "cd $REMOTE_DIR/portal/dist && rm -f login.js dashboard.js register.js home.js profile.js referrals.js resources.js *.map *.LICENSE.txt ._* 2>/dev/null; true"
echo "Uploaded portal/dist/portal.js, portal.css (old chunks removed on host)"

# 4. Test script in theme root (run on host: ./test-auth.sh)
scp "$THEME_DIR/test-auth.sh" "$THEME_DIR/test-register.sh" "$HOST:$REMOTE_DIR/"
echo "Uploaded test-auth.sh (run on host: chmod +x test-auth.sh && ./test-auth.sh) and test-register.sh"

# 5. Optional: scripts folder so host has test-auth.sh in portal/scripts too
ssh "$HOST" "mkdir -p $REMOTE_DIR/portal/scripts"
scp "$THEME_DIR/portal/scripts/test-auth.sh" "$HOST:$REMOTE_DIR/portal/scripts/" 2>/dev/null || true
echo "Done. On host run: cd $REMOTE_DIR && chmod +x test-auth.sh && PORTAL_USER=mmla2024 PORTAL_PASS=tiger2025 ./test-auth.sh"
