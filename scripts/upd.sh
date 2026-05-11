#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

echo "🔧 SETTING UP ENHANCED PORTAL AUTHENTICATION"
echo ""

# Define paths
THEME_DIR="/var/www/html/wp-content/themes/blocksy-child"
FUNCTIONS_PHP="$THEME_DIR/functions.php"
AUTH_FUNCTIONS_FILE="functions-portal-auth-enhanced.php"
REGISTER_PAGE_FILE="page-register-enhanced.php"
LOGIN_PAGE_FILE="page-portal-login-enhanced.php"

# 6. Update existing pages to use enhanced versions
echo "6️⃣ Updating page templates..."

# Get WordPress container name
WP_CONTAINER=$(/usr/local/bin/docker-compose ps -q wordpress)
echo $WP_CONTAINER

# Update 'Register' page to use 'page-register-enhanced.php'
echo "Updating 'Register' page template..."
docker exec -it "$WP_CONTAINER" wp post update $(docker exec -it "$WP_CONTAINER" wp post list --post_type=page --name=register --field=ID --format=ids) --page_template="$REGISTER_PAGE_FILE" --allow-root
echo "'Register' page template updated.
