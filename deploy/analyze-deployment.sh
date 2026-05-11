#!/bin/bash

echo "=== Deployment Analysis ==="

echo "1. Checking local files that should be deployed:"
find wordpress/wp-content/themes/blocksy-child -type f -name "*.php" -o -name "*.css" -o -name "*.js" | head -10

echo -e "\n2. Checking server files:"
ssh godaddy "find ~/public_html/wp-content/themes/blocksy-child -type f -name '*.php' -o -name '*.css' -o -name '*.js' | head -10"

echo -e "\n3. Comparing functions.php timestamps:"
echo "Local functions.php modified:"
stat -f "%Sm" wordpress/wp-content/themes/blocksy-child/functions.php

echo "Server functions.php modified:"
ssh godaddy "stat -c '%y' ~/public_html/wp-content/themes/blocksy-child/functions.php"

echo -e "\n4. Checking Git hook deployment section:"
ssh godaddy "grep -A 10 -B 5 'Copying theme files' ~/git/mmla-portal.git/hooks/post-receive"

echo -e "\n5. Recent deployment log entries:"
ssh godaddy "tail -n 20 ~/deployment.log"
