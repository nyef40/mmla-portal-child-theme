#!/bin/bash
# Migration script to unified React architecture

echo "====================================================="
echo "Mobile Medical LA - React Migration Script"
echo "====================================================="

# Step 1: Install dependencies
echo "\n[1/6] Installing dependencies..."
cd /Users/nikolayyefimov/Projects/mmla-portal/wordpress/wp-content/src
npm install react-router-dom --save

# Step 4: Build React app
echo "\n[4/6] Building React application..."
npm run build

# Step 5: Create WordPress template
echo "\n[5/6] Setting up WordPress integration..."


// mkdir -p ../themes/blocksy-child/react-app
// cp -r ../../themes/blocksy-child/portal/dist ../themes/blocksy-child/react-app/

// continue using '/themes/blocksy-child/portal/dist' directly
// verify path in functions-react-unified.php and update if needed

# Step 6: Include in functions.php
echo "\n[6/6] Updating WordPress functions..."
echo "\n\n// Load unified React app" >> ../themes/blocksy-child/functions.php
echo "require_once get_stylesheet_directory() . '/functions-react-unified.php';" >> ../themes/blocksy-child/functions.php

echo "\n====================================================="
echo "✓ Migration complete!"
echo "====================================================="
echo "\nNext steps:"
echo "1. Go to WordPress admin → Pages → Add New"
echo "2. Create page named 'React App' with slug 'react-app'"
echo "3. Assign template: 'React App (Unified)'"
echo "4. Flush rewrite rules: wp rewrite flush"
echo "5. Test: http://localhost:8080/our-services/"
echo "====================================================="
