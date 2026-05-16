#!/usr/bin/env bash
# Push blocksy-child theme from your Mac/laptop TO GoDaddy via scp/ssh.
#
# Run on your Mac (from repo or theme folder):
#   cd /path/to/mmla-portal/wordpress/wp-content/themes/blocksy-child
#   ./deploy-to-host.sh
#
# Do NOT run this script inside GoDaddy SSH — you are already on the server.
# On the server, edit files in ~/public_html/wp-content/themes/blocksy-child directly,
# or upload via git / File Manager / scp FROM your laptop.
#
# Optional: HOST=user@host ./deploy-to-host.sh
# Force run on server (almost never needed): DEPLOY_FROM_LAPTOP=1 ./deploy-to-host.sh

set -e

HOST="${HOST:-c9gyjyiudq9m@198.12.217.103}"
REMOTE_DIR="/home/c9gyjyiudq9m/public_html/wp-content/themes/blocksy-child"
THEME_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
THEME_NAME="$(basename "$THEME_DIR")"

# Detect GoDaddy/cPanel shell: script would scp to itself and prompt for wrong keys.
if [[ "${DEPLOY_FROM_LAPTOP:-}" != "1" ]]; then
    if [[ -f "${HOME}/public_html/wp-config.php" ]] || [[ -d "${HOME}/public_html/wp-content" ]]; then
        if [[ "$(pwd)" == *"public_html"* ]] || [[ -f "${THEME_DIR}/functions.php" && "$(hostname 2>/dev/null)" == *"p3plzcpnl"* ]]; then
            echo "ERROR: deploy-to-host.sh is for your Mac → GoDaddy, not for running on GoDaddy SSH."
            echo ""
            echo "You are already on the server. Theme path:"
            echo "  ${REMOTE_DIR}"
            echo ""
            echo "To update production:"
            echo "  1. On your Mac: cd to this repo and run ./deploy-to-host.sh"
            echo "  2. Or edit files here in nano/vi after git pull"
            echo "  3. Or use cPanel File Manager to upload changed files"
            echo ""
            echo "SSH key note: id_rsa passphrase unlocks outbound SSH from your Mac."
            echo "Pasting the key into nano on the server does not deploy anything."
            exit 1
        fi
    fi
fi

echo "Deploying $THEME_NAME to $HOST"
echo "Local theme: $THEME_DIR"
echo "Remote: $HOST:$REMOTE_DIR"
echo ""

# 1. PHP, includes, JS
scp "$THEME_DIR/functions.php" "$THEME_DIR/functions-portal-auth-enhanced.php" "$THEME_DIR/portal-loader.php" "$HOST:$REMOTE_DIR/"
ssh "$HOST" "mkdir -p $REMOTE_DIR/includes $REMOTE_DIR/js"
scp "$THEME_DIR/includes/"*.php "$HOST:$REMOTE_DIR/includes/"
scp "$THEME_DIR/js/fix-links.js" "$HOST:$REMOTE_DIR/js/"
echo "Uploaded functions.php, includes/, js/fix-links.js, portal-loader.php"

# 2. Page templates
scp "$THEME_DIR/page-portal-login-enhanced.php" "$THEME_DIR/page-register-enhanced.php" "$THEME_DIR/page-dashboard.php" "$THEME_DIR/page-portal-referrals.php" "$THEME_DIR/page-profile.php" "$THEME_DIR/page-portal-resources.php" "$THEME_DIR/page-contact.php" "$HOST:$REMOTE_DIR/"
echo "Uploaded page templates"

# 3. Portal dist
ssh "$HOST" "mkdir -p $REMOTE_DIR/portal/dist"
scp "$THEME_DIR/portal/dist/portal.js" "$THEME_DIR/portal/dist/portal.css" "$HOST:$REMOTE_DIR/portal/dist/"
ssh "$HOST" "cd $REMOTE_DIR/portal/dist && rm -f login.js dashboard.js register.js home.js profile.js referrals.js resources.js *.map *.LICENSE.txt ._* 2>/dev/null; true"
echo "Uploaded portal/dist/portal.js, portal.css"

# 4. Helpers
scp "$THEME_DIR/test-auth.sh" "$THEME_DIR/test-register.sh" "$THEME_DIR/wp-godaddy.sh" "$HOST:$REMOTE_DIR/" 2>/dev/null || true
ssh "$HOST" "mkdir -p $REMOTE_DIR/portal/scripts"
scp "$THEME_DIR/portal/scripts/test-auth.sh" "$HOST:$REMOTE_DIR/portal/scripts/" 2>/dev/null || true

echo ""
echo "Done. Clear caches (WP-Optimize / GoDaddy) and hard-refresh the browser."
echo "WP-CLI on server: $REMOTE_DIR/wp-godaddy.sh option get admin_email"
