#!/bin/bash
# Portal Deployment Verification Script
# Run on GoDaddy server after deployment

echo "=========================================="
echo "Portal Deployment Verification"
echo "=========================================="
echo ""

# 1. Check theme files exist
echo "1. Checking theme files..."
THEME_DIR="$HOME/public_html/wp-content/themes/blocksy-child"

if [ -f "$THEME_DIR/functions.php" ]; then
    echo "✓ functions.php exists"
else
    echo "✗ functions.php MISSING"
    exit 1
fi

if [ -f "$THEME_DIR/functions-portal-auth-live-fix.php" ]; then
    echo "✓ functions-portal-auth-live-fix.php exists"
else
    echo "✗ functions-portal-auth-live-fix.php MISSING"
    exit 1
fi

if [ -f "$THEME_DIR/portal/dist/portal.js" ]; then
    echo "✓ portal/dist/portal.js exists"
else
    echo "✗ portal/dist/portal.js MISSING"
    exit 1
fi

echo ""

# 2. Check file permissions
echo "2. Checking file permissions..."
PERMS=$(stat -c "%a" "$THEME_DIR/functions.php" 2>/dev/null || stat -f "%A" "$THEME_DIR/functions.php")
if [ "$PERMS" = "644" ]; then
    echo "✓ functions.php permissions correct (644)"
else
    echo "⚠ functions.php permissions: $PERMS (expected 644)"
fi

echo ""

# 3. Test portal URL
echo "3. Testing portal URL..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://mobilemedicalla.com/portal/)
if [ "$HTTP_CODE" = "200" ]; then
    echo "✓ Portal loads (HTTP $HTTP_CODE)"
else
    echo "✗ Portal failed (HTTP $HTTP_CODE)"
fi

echo ""

# 4. Check debug log size
echo "4. Checking debug log..."
DEBUG_LOG="$HOME/public_html/wp-content/debug.log"
if [ -f "$DEBUG_LOG" ]; then
    SIZE=$(wc -c < "$DEBUG_LOG")
    LINES=$(wc -l < "$DEBUG_LOG")
    echo "   Debug log: $LINES lines, $SIZE bytes"
    
    if [ $SIZE -lt 1048576 ]; then  # Less than 1MB
        echo "✓ Debug log size OK"
    else
        echo "⚠ Debug log too large (>1MB)"
    fi
else
    echo "✓ No debug log (good if WP_DEBUG is false)"
fi

echo ""

# 5. Check portal auth log
echo "5. Checking portal auth log..."
AUTH_LOG="$HOME/public_html/wp-content/portal-auth.log"
if [ -f "$AUTH_LOG" ]; then
    RECENT=$(tail -n 5 "$AUTH_LOG")
    echo "   Recent portal auth events:"
    echo "$RECENT"
else
    echo "   No portal auth log yet (will be created on first login)"
fi

echo ""

# 6. Database check
echo "6. Checking database tables..."
TABLES=$(mysql c9gyjyiu_wp989 -sN -e "SHOW TABLES LIKE '%portal%';")
if [ -n "$TABLES" ]; then
    echo "✓ Portal tables exist:"
    echo "$TABLES"
else
    echo "✗ No portal tables found"
fi

echo ""
echo "=========================================="
echo "Verification Complete"
echo "=========================================="
