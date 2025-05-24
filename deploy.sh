#!/bin/bash
# Simple deployment script

# Configuration
REMOTE_USER="godaddy"
REMOTE_THEME_PATH="~/public_html/wp-content/themes/blocksy-child"
LOCAL_THEME_PATH="wordpress/wp-content/themes/blocksy-child"

# Create backup on remote
ssh $REMOTE_USER "mkdir -p ~/backups/\$(date +%Y-%m-%d) && cp -r $REMOTE_THEME_PATH ~/backups/\$(date +%Y-%m-%d)/blocksy-child-backup"

# Prepare deployment-ready functions.php
cp $LOCAL_THEME_PATH/functions.php $LOCAL_THEME_PATH/functions.php.local
cp $LOCAL_THEME_PATH/functions.php.deploy $LOCAL_THEME_PATH/functions.php

# Sync theme files
rsync -avz --exclude="*.bak" --exclude="*.local" $LOCAL_THEME_PATH/ $REMOTE_USER:$REMOTE_THEME_PATH/

# Restore local functions.php
mv $LOCAL_THEME_PATH/functions.php.local $LOCAL_THEME_PATH/functions.php

# Set permissions
ssh $REMOTE_USER "chmod 644 $REMOTE_THEME_PATH/*.php $REMOTE_THEME_PATH/*.css && chmod -R 755 $REMOTE_THEME_PATH/js"

# Clear cache
ssh $REMOTE_USER "cd ~/public_html && wp cache flush && wp transient delete --all"

echo "Deployment completed!"
