#!/bin/bash

echo "=== QUICK FIX FOR PORTAL ==="

echo "1. Clean dist..."
rm -rf ../themes/blocksy-child/portal/dist/*

echo "2. Build with fixed filenames..."
NODE_ENV=production npx webpack --config webpack.config.final.js

echo "3. Check files..."
ls -la ../themes/blocksy-child/portal/dist/

echo "4. Test direct access..."
echo "   CSS: http://localhost:8080/wp-content/themes/blocksy-child/portal/dist/portal.css"
echo "   JS:  http://localhost:8080/wp-content/themes/blocksy-child/portal/dist/portal.js"

echo "5. Clear caches..."
docker exec mmla-portal-wordpress-1 wp cache flush --allow-root

echo "6. Test page..."
echo "   http://localhost:8080/portal/"
echo ""
echo "CHECK:"
echo "1. Files should load with 200 OK"
echo "2. Console should show 'Portal Simple: Script loaded'"
echo "3. No 404 errors for portal.css/portal.js"