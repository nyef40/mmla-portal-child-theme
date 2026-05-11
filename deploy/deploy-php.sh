#!/bin/bash
echo "Deploying PHP files..."
scp wordpress/wp-content/themes/blocksy-child/*.php godaddy:~/public_html/wp-content/themes/blocksy-child/
echo "PHP files deployed!"
