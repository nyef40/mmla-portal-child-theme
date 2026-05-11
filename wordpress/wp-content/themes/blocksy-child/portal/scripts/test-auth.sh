#!/usr/bin/env bash
# Portal login test – run on host via SSH (no Node required).
# Uses curl to fetch a fresh nonce (cache-bust) and POST portal_login.
#
# Usage (on server):
#   cd ~/public_html/wp-content/themes/blocksy-child/portal/scripts
#   ./test-auth.sh
#
# Or from theme root:
#   ./portal/scripts/test-auth.sh
#
# Optional env: BASE_URL, PORTAL_USER, PORTAL_PASS

set -e
BASE_URL="${BASE_URL:-https://mobilemedicalla.com}"
PORTAL_USER="${PORTAL_USER:-mmla2024}"
PORTAL_PASS="${PORTAL_PASS:-tiger2025}"
CACHE_BUST="_t=$(date +%s)$$"
LOGIN_URL="${BASE_URL%/}/portal-login/?${CACHE_BUST}"
AJAX_URL="${BASE_URL%/}/wp-admin/admin-ajax.php"

echo ""
echo "--- Portal login test (host) ---"
echo "[test:auth] BASE_URL $BASE_URL"
echo "[test:auth] USER     $PORTAL_USER"
echo "[test:auth] Fetching login page (cache-bust) $LOGIN_URL"

HTML=$(curl -sS -L -H "User-Agent: PortalAuthTest/1.0" -H "Cache-Control: no-cache" "$LOGIN_URL")
NONCE=$(echo "$HTML" | sed -n 's/.*name="nonce"[^>]*value="\([^"]*\)".*/\1/p')
if [ -z "$NONCE" ] || [ ${#NONCE} -lt 8 ]; then
  NONCE=$(echo "$HTML" | sed -n 's/.*id="portal-login-nonce"[^>]*value="\([^"]*\)".*/\1/p')
fi
if [ -z "$NONCE" ] || [ ${#NONCE} -lt 8 ]; then
  NONCE=$(echo "$HTML" | sed -n 's/.*formData\.append("nonce", "\([^"]*\)").*/\1/p')
fi
if [ -z "$NONCE" ] || [ ${#NONCE} -lt 8 ]; then
  NONCE=$(echo "$HTML" | grep -o 'wpPortalData\s*=\s*{[^}]*"nonce"\s*:\s*"[^"]*"' | sed 's/.*"nonce"\s*:\s*"\([^"]*\)".*/\1/')
fi
if [ -z "$NONCE" ] || [ ${#NONCE} -lt 8 ]; then
  NONCE_RESP=$(curl -sS -L -X POST "$AJAX_URL" -H "Content-Type: application/x-www-form-urlencoded" -d "action=portal_login_fresh_nonce")
  NONCE=$(echo "$NONCE_RESP" | sed -n 's/.*"nonce":"\([^"]*\)".*/\1/p')
fi
if [ -z "$NONCE" ] || [ ${#NONCE} -lt 8 ]; then
  echo "[test:auth] ERROR No valid nonce found"
  exit 1
fi
echo "[test:auth] Nonce obtained (length ${#NONCE})"
echo "[test:auth] POSTing portal_login to $AJAX_URL"

RESP=$(curl -sS -L -X POST "$AJAX_URL" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "User-Agent: PortalAuthTest/1.0" \
  -H "Referer: ${BASE_URL%/}/portal-login/" \
  -d "action=portal_login" \
  -d "nonce=$NONCE" \
  -d "username=$PORTAL_USER" \
  -d "password=$PORTAL_PASS" \
  -d "remember=0")

if echo "$RESP" | grep -q '"success":true'; then
  REDIRECT=$(echo "$RESP" | sed -n 's/.*"redirect":"\([^"]*\)".*/\1/p')
  echo "[test:auth] PASS: Login succeeded (redirect: $REDIRECT)"
  exit 0
fi
if echo "$RESP" | grep -q 'Security check failed'; then
  echo "[test:auth] FAIL: Security check failed (nonce rejected). Exclude /portal-login/ from cache and retry."
  exit 1
fi
echo "[test:auth] FAIL: $RESP"
exit 1
