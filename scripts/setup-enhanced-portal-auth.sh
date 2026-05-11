#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

echo "🔧 SETTING UP ENHANCED PORTAL AUTHENTICATION"
echo ""

# Define paths
THEME_DIR="/var/www/html/wp-content/themes/blocksy-child"
FUNCTIONS_PHP="$THEME_DIR/functions.php"
AUTH_FUNCTIONS_FILE="functions-portal-auth-enhanced.php"
NAV_FUNCTIONS_FILE="functions-portal-navigation-fix.php"
PORTAL_FUNCTIONS_FILE="functions-portal.php" # New: Main portal functions file
REGISTER_PAGE_FILE="page-register-enhanced.php"
LOGIN_PAGE_FILE="page-portal-login-enhanced.php"
PORTAL_FINAL_PAGE_FILE="page-portal-final.php" # New: Final portal page template

# Get WordPress container name
WP_CONTAINER=$(docker compose ps -q wordpress)
if [ -z "$WP_CONTAINER" ]; then
    echo "Error: WordPress container not found"
    exit 1
fi

# 1. Add enhanced authentication functions
echo "1️⃣ Adding enhanced authentication functions..."
docker cp wordpress/wp-content/themes/blocksy-child/$AUTH_FUNCTIONS_FILE mmla-portal-wordpress-1:$THEME_DIR/

# 2. Add enhanced registration page
echo "2️⃣ Adding enhanced registration page..."
docker cp wordpress/wp-content/themes/blocksy-child/$REGISTER_PAGE_FILE mmla-portal-wordpress-1:$THEME_DIR/

# 3. Add enhanced login page
echo "3️⃣ Adding enhanced login page..."
docker cp wordpress/wp-content/themes/blocksy-child/$LOGIN_PAGE_FILE mmla-portal-wordpress-1:$THEME_DIR/

# 4. Add navigation fix functions
echo "4️⃣ Adding navigation fix functions..."
docker cp wordpress/wp-content/themes/blocksy-child/$NAV_FUNCTIONS_FILE mmla-portal-wordpress-1:$THEME_DIR/

# 5. Add main portal functions file
echo "5️⃣ Adding main portal functions file..."
docker cp wordpress/wp-content/themes/blocksy-child/$PORTAL_FUNCTIONS_FILE mmla-portal-wordpress-1:$THEME_DIR/

# 6. Add final portal page template
echo "6️⃣ Adding final portal page template..."
docker cp wordpress/wp-content/themes/blocksy-child/$PORTAL_FINAL_PAGE_FILE mmla-portal-wordpress-1:$THEME_DIR/

# 7. Update functions.php to include all necessary files and remove debug statements
echo "7️⃣ Updating functions.php..."
docker exec -it mmla-portal-wordpress-1 bash -c "
cd $THEME_DIR

# Backup current functions.php
cp functions.php functions.php.backup-enhanced-$(date +%Y%m%d-%H%M%S)

# Remove all var_dump and die statements
sed -i '/var_dump(/d' functions.php
sed -i '/die(/d' functions.php

# Ensure functions.php includes the new auth file
if ! grep -q \"require_once get_stylesheet_directory() . \"/$AUTH_FUNCTIONS_FILE\";\" functions.php; then
    echo \"Adding require_once for $AUTH_FUNCTIONS_FILE to functions.php...\"
    sed -i '/^?>/i require_once get_stylesheet_directory() . \"/functions-portal-auth-enhanced.php\";' functions.php
else
    echo \"Include for $AUTH_FUNCTIONS_FILE already exists in functions.php. Skipping.\"
fi

# Ensure functions.php includes the navigation fix file
if ! grep -q \"require_once get_stylesheet_directory() . \"/$NAV_FUNCTIONS_FILE\";\" functions.php; then
    echo \"Adding require_once for $NAV_FUNCTIONS_FILE to functions.php...\"
    sed -i '/functions-portal-auth-enhanced.php\";/a require_once get_stylesheet_directory() . \"/functions-portal-navigation-fix.php\";' functions.php
else
    echo \"Include for $NAV_FUNCTIONS_FILE already exists in functions.php. Skipping.\"
fi

# Ensure functions.php includes the main portal functions file
if ! grep -q \"require_once get_stylesheet_directory() . \"/$PORTAL_FUNCTIONS_FILE\";\" functions.php; then
    echo \"Adding require_once for $PORTAL_FUNCTIONS_FILE to functions.php...\"
    sed -i '/functions-portal-navigation-fix.php\";/a require_once get_stylesheet_directory() . \"/functions-portal.php\";' functions.php
else
    echo \"Include for $PORTAL_FUNCTIONS_FILE already exists in functions.php. Skipping.\"
fi

# Remove old TEMPLATE DEBUG HOOKS if they exist
sed -i '/^ \/\/ TEMPLATE DEBUG HOOKS - Remove after debugging/,/^ \/\/ Include Enhanced Portal Authentication Functions/d' functions.php
sed -i '/^add_filter(\"page_template\", \"force_portal_template\", 99);/d' functions.php
sed -i '/^add_filter(\"template_include\", \"force_portal_template\", 99);/d' functions.php
sed -i '/^add_action(\"wp\", \"debug_template_selection\");/d' functions.php
sed -i '/^add_filter(\"template_include\", \"debug_template_include\");/d' functions.php
sed -i '/^add_filter(\"page_template\", \"debug_page_template\");/d' functions.php

echo 'Functions.php updated with all necessary includes and debug statements removed.'
"

# 8. Create portal tables (dbDelta will only create if they don't exist or update if schema changes)
echo "8️⃣ Creating/Updating portal database tables..."
docker exec -it "$WP_CONTAINER" wp eval "
global \$wpdb;

\$portal_users_table = \$wpdb->prefix . 'portal_users';
\$charset_collate = \$wpdb->get_charset_collate();

\$sql = \"CREATE TABLE IF NOT EXISTS \$portal_users_table (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    wp_user_id bigint(20) NOT NULL,
    username varchar(60) NOT NULL,
    email varchar(100) NOT NULL,
    first_name varchar(50) DEFAULT '',
    last_name varchar(50) DEFAULT '',
    phone varchar(20) DEFAULT '',
    role varchar(50) DEFAULT 'portal_user',
    specialty varchar(100) DEFAULT '',
    license_number varchar(50) DEFAULT '',
    practice varchar(100) DEFAULT '',
    address text DEFAULT '',
    city varchar(50) DEFAULT '',
    state varchar(20) DEFAULT '',
    zip varchar(10) DEFAULT '',
    email_verified tinyint(1) DEFAULT 0,
    validation_token varchar(255) DEFAULT '',
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY wp_user_id (wp_user_id),
    UNIQUE KEY username (username),
    UNIQUE KEY email (email),
    INDEX idx_email_verified (email_verified),
    INDEX idx_validation_token (validation_token)
) \$charset_collate;\";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta(\$sql);

// Create portal_access_logs table
\$access_logs_table = \$wpdb->prefix . 'portal_access_logs';

\$sql2 = \"CREATE TABLE IF NOT EXISTS \$access_logs_table (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    resource_id bigint(20) NOT NULL DEFAULT 0,
    action varchar(50) NOT NULL,
    ip_address varchar(45) DEFAULT '',
    user_agent text DEFAULT '',
    details text DEFAULT '',
    accessed_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_accessed_at (accessed_at)
) \$charset_collate;\";

dbDelta(\$sql2);

// Create portal_resources table (if it doesn't exist or needs updates)
\$resources_table = \$wpdb->prefix . 'portal_resources';
\$sql3 = \"CREATE TABLE IF NOT EXISTS \$resources_table (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    title varchar(255) NOT NULL,
    description text DEFAULT NULL,
    file_path varchar(255) DEFAULT NULL,
    access_level varchar(50) DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) \$charset_collate;\";
dbDelta(\$sql3);

echo '✅ Portal database tables created/updated' . PHP_EOL;
" --allow-root

# 9. Update existing pages to use enhanced versions and correct templates
echo "9️⃣ Updating page templates..."

# Update 'Register' page to use 'page-register-enhanced.php'
echo "Updating 'Register' page template..."
REGISTER_PAGE_ID=$(docker exec -it "$WP_CONTAINER" wp post list --post_type=page --name=register --field=ID --format=ids --allow-root)
if [ -n "$REGISTER_PAGE_ID" ]; then
    docker exec -it "$WP_CONTAINER" wp post update "$REGISTER_PAGE_ID" --page_template="$REGISTER_PAGE_FILE" --allow-root
    echo "'Register' page template updated."
else
    echo "Warning: 'Register' page not found. Please ensure it exists in WordPress."
fi

# Update 'Portal Login' page to use 'page-portal-login-enhanced.php'
echo "Updating 'Portal Login' page template..."
LOGIN_PAGE_ID=$(docker exec -it "$WP_CONTAINER" wp post list --post_type=page --name=portal-login --field=ID --format=ids --allow-root)
if [ -n "$LOGIN_PAGE_ID" ]; then
    docker exec -it "$WP_CONTAINER" wp post update "$LOGIN_PAGE_ID" --page_template="$LOGIN_PAGE_FILE" --allow-root
    echo "'Portal Login' page template updated."
else
    echo "Warning: 'Portal Login' page not found. Please ensure it exists in WordPress."
fi

# Update 'Portal' page to use 'page-portal-final.php'
echo "Updating 'Portal' page template..."
PORTAL_PAGE_ID=$(docker exec -it "$WP_CONTAINER" wp post list --post_type=page --name=portal --field=ID --format=ids --allow-root)
if [ -n "$PORTAL_PAGE_ID" ]; then
    docker exec -it "$WP_CONTAINER" wp post update "$PORTAL_PAGE_ID" --page_template="$PORTAL_FINAL_PAGE_FILE" --allow-root
    echo "'Portal' page template updated."
else
    echo "Warning: 'Portal' page not found. Please ensure it exists in WordPress."
fi

echo "Page templates update attempts complete."

# 10. Clear caches and restart
echo "🔟 Clearing caches and restarting..."
docker exec -it "$WP_CONTAINER" wp cache flush --allow-root
docker exec -it "$WP_CONTAINER" wp rewrite flush --allow-root
docker compose restart wordpress

echo ""
echo "✅ ENHANCED PORTAL AUTHENTICATION & NAVIGATION SETUP COMPLETE!"
echo ""
echo "🧪 TEST THE ENHANCED PORTAL:"
echo ""
echo "📋 Registration Process:"
echo "1. Go to: http://localhost:8080/register/"
echo "2. Fill out the enhanced registration form with all details (including address, city, state, zip, and professional info)."
echo "3. Check email for verification link"
echo "4. Click verification link"
echo "5. Sign in at: http://localhost:8080/portal-login/"
echo ""
echo "🎯 New Features:"
echo "✅ Professional role selection (RN, PT, OT, etc.)"
echo "✅ Enhanced form with sections"
echo "✅ Email verification (like referral system)"
echo "✅ Portal-specific user table"
echo "✅ Access logging"
echo "✅ Protected portal pages"
echo "✅ Better error messages"
echo "✅ Professional styling"
echo "✅ Correct menu bar behavior (main hidden, portal visible)"
echo ""
echo "🗄️ Database Tables Created/Updated:"
echo "• lqbk_portal_users (user profiles)"
echo "• lqbk_portal_access_logs (access tracking)"
echo "• lqbk_portal_resources (resource management)"
echo ""
echo "📧 Email Verification:"
echo "Uses same system as referral validation"
echo "Check email after registration!"


