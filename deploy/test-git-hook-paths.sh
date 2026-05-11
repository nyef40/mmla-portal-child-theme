#!/bin/bash
echo "=== Testing Git Hook Paths ==="

# Simulate what happens in the Git hook
echo "1. Checking if deployment temp directory exists:"
ssh godaddy "ls -la /tmp/deployment/ 2>/dev/null || echo 'Directory does not exist'"

echo -e "\n2. Looking for WordPress files in temp:"
ssh godaddy "find /tmp/deployment -name '*.php' -path '*/themes/blocksy-child/*' 2>/dev/null || echo 'No PHP files found'"

echo -e "\n3. Checking current Git hook checkout location:"
ssh godaddy "grep -A 5 -B 5 'git.*checkout\|GIT_WORK_TREE' ~/git/mmla-portal.git/hooks/post-receive"

echo -e "\n4. Testing rsync command manually:"
ssh godaddy "rsync --dry-run -av /tmp/deployment/wordpress/wp-content/themes/blocksy-child/ ~/test-rsync/ 2>&1 || echo 'Path does not exist'"
