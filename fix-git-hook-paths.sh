#!/bin/bash
echo "=== Fixing Git Hook Paths ==="

# Backup the current hook
ssh godaddy "cp ~/git/mmla-portal.git/hooks/post-receive ~/git/mmla-portal.git/hooks/post-receive.backup-$(date +%Y%m%d-%H%M%S)"

# Fix the rsync path
ssh godaddy "sed -i 's|/tmp/deployment/wp-content/themes/blocksy-child/|\$TEMP_DIR/wordpress/wp-content/themes/blocksy-child/|g' ~/git/mmla-portal.git/hooks/post-receive"

echo "Git hook paths fixed!"

# Show the change
echo "=== Updated rsync command ==="
ssh godaddy "grep -A 3 -B 1 'rsync.*TEMP_DIR' ~/git/mmla-portal.git/hooks/post-receive"
