#!/bin/bash

echo "🧹 CLEANING UP PORTAL FILES"

echo "1️⃣ Removing old backup files..."
docker exec mmla-portal-wordpress-1 find /var/www/html/wp-content/themes/blocksy-child -name "*.backup" -delete
docker exec mmla-portal-wordpress-1 find /var/www/html/wp-content/themes/blocksy-child -name "functions.php.backup*" -delete

echo "2️⃣ Removing debug files..."
docker exec mmla-portal-wordpress-1 rm -f /var/www/html/wp-content/themes/blocksy-child/debug-*.php
docker exec mmla-portal-wordpress-1 rm -f /var/www/html/wp-content/themes/blocksy-child/fix-*.php
docker exec mmla-portal-wordpress-1 rm -f /var/www/html/wp-content/themes/blocksy-child/force-*.php
docker exec mmla-portal-wordpress-1 rm -f /var/www/html/wp-content/themes/blocksy-child/remove-*.php
docker exec mmla-portal-wordpress-1 rm -f /var/www/html/wp-content/themes/blocksy-child/verify-*.php
docker exec mmla-portal-wordpress-1 rm -f /var/www/html/wp-content/themes/blocksy-child/nuclear-*.php
docker exec mmla-portal-wordpress-1 rm -f /var/www/html/wp-content/themes/blocksy-child/deep-*.php
docker exec mmla-portal-wordpress-1 rm -f /var/www/html/wp-content/themes/blocksy-child/emergency-*.php

echo "3️⃣ Removing old portal function files..."
docker exec mmla-portal-wordpress-1 rm -f /var/www/html/wp-content/themes/blocksy-child/functions-portal-*.php
docker exec mmla-portal-wordpress-1 rm -f /var/www/html/wp-content/themes/blocksy-child/functions-clean*.php
docker exec mmla-portal-wordpress-1 rm -f /var/www/html/wp-content/themes/blocksy-child/functions-safe*.php

echo "4️⃣ Removing old page templates..."
docker exec mmla-portal-wordpress-1 rm -f /var/www/html/wp-content/themes/blocksy-child/page-portal-*.php
docker exec mmla-portal-wordpress-1 rm -f /var/www/html/wp-content/themes/blocksy-child/page-login-*.php
docker exec mmla-portal-wordpress-1 rm -f /var/www/html/wp-content/themes/blocksy-child/page-register-*.php

echo "5️⃣ Keeping only essential files..."
echo "✅ Keeping: functions.php"
echo "✅ Keeping: functions-portal-auth-enhanced.php"
echo "✅ Keeping: page-portal-final.php"
echo "✅ Keeping: page-portal-login-enhanced.php"
echo "✅ Keeping: page-dashboard.php"
echo "✅ Keeping: page-contact.php"
echo "✅ Keeping: page-portal-resources.php"
echo "✅ Keeping: page-profile.php"
echo "✅ Keeping: page-portal-referrals.php"

echo "6️⃣ Clearing WordPress caches..."
docker exec -it mmla-portal-wordpress-1 wp cache flush --allow-root

echo "7️⃣ Optimizing database..."
docker exec -it mmla-portal-wordpress-1 wp db optimize --allow-root

echo "✅ CLEANUP COMPLETE!"
echo ""
echo "📁 REMAINING PORTAL FILES:"
docker exec mmla-portal-wordpress-1 ls -la /var/www/html/wp-content/themes/blocksy-child/ | grep -E "(functions|page-)"
