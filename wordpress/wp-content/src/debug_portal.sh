#!/bin/bash

echo "=== Portal Debug Script ==="
echo ""

echo "1. Checking WordPress pages..."
docker exec mmla-portal-wordpress-1 wp post list --post_type=page --fields=ID,post_title,post_name --format=table --allow-root

echo ""
echo "2. Checking asset files..."
ls -la ../themes/blocksy-child/portal/dist/portal*

echo ""
echo "3. Checking manifest.json..."
cat ../themes/blocksy-child/portal/dist/manifest.json
echo ""
echo "4. Testing REST API..."
curl -s "http://localhost:8080/wp-json/portal/v1/profile" \
  -H "X-WP-Nonce: $(docker exec mmla-portal-wordpress-1 wp post nonce get --allow-root)" \
  -H "Cookie: $(docker exec mmla-portal-wordpress-1 wp user session create --allow-root 1 2>/dev/null | grep -o 'wordpress_logged_in_[^;]*')" | jq .

echo ""
echo "5. Checking WordPress logs..."
docker exec mmla-portal-wordpress-1 tail -20 /var/www/html/wp-content/debug.log

echo ""
echo "6. Testing pages..."
echo "- Portal: http://localhost:8080/portal/"
echo "- Dashboard: http://localhost:8080/dashboard/"
echo "- Resources: http://localhost:8080/portal-resources/"

echo ""
echo "=== Debug Instructions ==="
echo "1. Open Chrome DevTools (F12)"
echo "2. Check Console tab for errors"
echo "3. Check Network tab for failed requests"
echo "4. Look for 'portal-root' element in Elements tab"
echo "5. Check if 'wpPortalReact' exists in Console"
echo ""
echo "For VS Code debugging:"
echo "1. Set breakpoints in src/ files (should be solid red)"
echo "2. Run 'Debug React (Chrome)' configuration"
echo "3. Refresh page (F5)"
echo "4. Breakpoints should hit"