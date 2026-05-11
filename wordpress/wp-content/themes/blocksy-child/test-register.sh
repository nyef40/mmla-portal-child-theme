#!/usr/bin/env bash
# Portal register flow test – run on host via SSH.
# 1) Fetches fresh nonce from portal_register_fresh_nonce
# 2) POSTs portal_register with test data
# 3) Optionally checks debug.log for E_STRICT deprecation
#
# Usage (on server, from theme root or project root):
#   cd ~/public_html/wp-content/themes/blocksy-child
#   chmod +x test-register.sh
#   ./test-register.sh
#
# Optional env: BASE_URL (default https://mobilemedicalla.com)

set -e
BASE_URL="${BASE_URL:-https://mobilemedicalla.com}"
AJAX_URL="${BASE_URL%/}/wp-admin/admin-ajax.php"
# Unique username to avoid "already exists"
U="testreg$(date +%s)"
EMAIL="testreg-${U}@example.com"

echo ""
echo "--- Portal register test (host) ---"
echo "[test:register] BASE_URL $BASE_URL"
echo "[test:register] Test user $U / $EMAIL"

# 1) Get fresh nonce
echo "[test:register] Fetching fresh nonce (portal_register_fresh_nonce)..."
NONCE_RESP=$(curl -sS -L -X POST "$AJAX_URL" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=portal_register_fresh_nonce")
NONCE=$(echo "$NONCE_RESP" | sed -n 's/.*"nonce":"\([^"]*\)".*/\1/p')
if [ -z "$NONCE" ] || [ ${#NONCE} -lt 8 ]; then
  echo "[test:register] ERROR Could not get nonce. Response: $NONCE_RESP"
  exit 1
fi
echo "[test:register] Nonce obtained (length ${#NONCE})"

# 2) POST register
echo "[test:register] POSTing portal_register..."
REG_RESP=$(curl -sS -L -X POST "$AJAX_URL" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=portal_register" \
  -d "nonce=$NONCE" \
  -d "username=$U" \
  -d "email=$EMAIL" \
  -d "password=TestPass2026" \
  -d "confirm_password=TestPass2026" \
  -d "confirmPassword=TestPass2026" \
  -d "first_name=Test" \
  -d "last_name=Register" \
  -d "practice=Test Practice")

if echo "$REG_RESP" | grep -q '"success":true'; then
  echo "[test:register] PASS: Registration succeeded (check DB and email for $EMAIL)"
else
  echo "[test:register] FAIL or partial: $REG_RESP"
  exit 1
fi

# 3) Optional: check debug.log for E_STRICT (run from site root: ./wp-content/themes/blocksy-child/test-register.sh, or pass log path as first arg)
LOG="${1:-wp-content/debug.log}"
if [ -f "$LOG" ]; then
  if tail -n 50 "$LOG" | grep -q "E_STRICT"; then
    echo "[test:register] WARN: debug.log contains E_STRICT deprecation in last 50 lines"
  else
    echo "[test:register] OK: No E_STRICT in last 50 lines of debug.log"
  fi
fi
echo ""
