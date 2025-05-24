#!/bin/bash
# Manual Git-based deployment script (FIXED)
# Save as deploy-manual.sh

set -e  # Exit on any error

# Configuration
REMOTE_USER="godaddy"
REMOTE_PATH="/home/c9gyjyiudq9m/public_html"
THEME_PATH="wp-content/themes/blocksy-child"
BACKUP_DIR="/home/c9gyjyiudq9m/backups/$(date +%Y-%m-%d-%H%M%S)"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${YELLOW}Starting manual deployment...${NC}"

# 1. Ensure we're on the main branch and up to date
echo -e "${YELLOW}Checking Git status...${NC}"
git checkout main
git pull origin main

# 2. Create a deployment tag
DEPLOY_TAG="deploy-$(date +%Y%m%d-%H%M%S)"
git tag -a $DEPLOY_TAG -m "Deployment on $(date)"
git push origin $DEPLOY_TAG

# 3. Create backup on server
echo -e "${YELLOW}Creating backup on server...${NC}"
ssh $REMOTE_USER "mkdir -p $BACKUP_DIR && cp -r $REMOTE_PATH/$THEME_PATH $BACKUP_DIR/"

# 4. Create a clean deployment directory locally
echo -e "${YELLOW}Preparing deployment files...${NC}"
rm -rf deploy-temp
mkdir deploy-temp

# 5. Export files from Git (this ensures only tracked files are deployed)
git archive --format=tar main | tar -x -C deploy-temp

# 6. Check if the theme directory exists in the correct location
THEME_SOURCE_PATH="deploy-temp/wordpress/$THEME_PATH"
if [ ! -d "$THEME_SOURCE_PATH" ]; then
    echo -e "${RED}Error: Theme directory not found at $THEME_SOURCE_PATH${NC}"
    echo -e "${RED}Available directories:${NC}"
    find deploy-temp -name "blocksy-child" -type d
    exit 1
fi

# 7. Remove local-only code from functions.php
echo -e "${YELLOW}Removing local-only code...${NC}"
if [ -f "$THEME_SOURCE_PATH/functions.php" ]; then
    # Create a deployment version without local-only code
    sed '/\/\/ Only apply port fixes on local environment/,/^}/d' $THEME_SOURCE_PATH/functions.php > $THEME_SOURCE_PATH/functions.php.tmp
    mv $THEME_SOURCE_PATH/functions.php.tmp $THEME_SOURCE_PATH/functions.php
    
    # Remove debug functions
    sed '/debug_script_loading/,/^}/d' $THEME_SOURCE_PATH/functions.php > $THEME_SOURCE_PATH/functions.php.tmp
    mv $THEME_SOURCE_PATH/functions.php.tmp $THEME_SOURCE_PATH/functions.php
    
    # Remove local environment detection and related code
    sed '/is_local_environment/,/^}/d' $THEME_SOURCE_PATH/functions.php > $THEME_SOURCE_PATH/functions.php.tmp
    mv $THEME_SOURCE_PATH/functions.php.tmp $THEME_SOURCE_PATH/functions.php
fi

# 8. Upload theme files
echo -e "${YELLOW}Uploading theme files...${NC}"
rsync -avz --delete $THEME_SOURCE_PATH/ $REMOTE_USER:$REMOTE_PATH/$THEME_PATH/

# 9. Set proper permissions
echo -e "${YELLOW}Setting permissions...${NC}"
ssh $REMOTE_USER "find $REMOTE_PATH/$THEME_PATH -type f -name '*.php' -exec chmod 644 {} \;"
ssh $REMOTE_USER "find $REMOTE_PATH/$THEME_PATH -type f -name '*.css' -exec chmod 644 {} \;"
ssh $REMOTE_USER "find $REMOTE_PATH/$THEME_PATH -type f -name '*.js' -exec chmod 644 {} \;"
ssh $REMOTE_USER "find $REMOTE_PATH/$THEME_PATH -type d -exec chmod 755 {} \;"

# 10. Clear WordPress caches
echo -e "${YELLOW}Clearing caches...${NC}"
ssh $REMOTE_USER "cd $REMOTE_PATH && wp cache flush && wp transient delete --all"

# 11. Test the deployment
echo -e "${YELLOW}Testing deployment...${NC}"
ssh $REMOTE_USER "cd $REMOTE_PATH && wp core verify-checksums"

# 12. Clean up
rm -rf deploy-temp

echo -e "${GREEN}Deployment completed successfully!${NC}"
echo -e "${GREEN}Deployment tag: $DEPLOY_TAG${NC}"
echo -e "${GREEN}Backup location: $BACKUP_DIR${NC}"