#!/bin/bash
# fix_portal.sh

echo "1. Cleaning dist directory..."
npm run clean

echo "2. Rebuilding assets..."
npm run build

echo "3. Creating WordPress pages..."
# Create a temporary PHP file to create pages
cat > /tmp/create_pages.php << 'EOF'
<?php
require_once('/var/www/html/wp-load.php');

$pages = array(
    'About Us' => 'about-us',
    'Our Services' => 'our-services', 
    'IVIG News' => 'ivig-news',
    'Portal' => 'portal',
    'Dashboard' => 'dashboard',
    'Portal Login' => 'portal-login',
    'Register' => 'register',
    'Portal Profile' => 'portal-profile',
    'Portal Resources' => 'portal-resources',
    'Portal Referrals' => 'portal-referrals'
);

foreach ($pages as $title => $slug) {
    $existing = get_page_by_path($slug);
    if (!$existing) {
        $page_id = wp_insert_post(array(
            'post_title' => $title,
            'post_name' => $slug,
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => ''
        ));
        echo "Created page: $title ($slug)\n";
    } else {
        echo "Page exists: $title ($slug)\n";
    }
}
EOF

docker cp /tmp/create_pages.php mmla-portal-wordpress-1:/tmp/create_pages.php
docker exec mmla-portal-wordpress-1 php /tmp/create_pages.php

echo "4. Flushing WordPress cache..."
docker exec mmla-portal-wordpress-1 wp rewrite flush --hard --allow-root
docker exec mmla-portal-wordpress-1 wp cache flush --allow-root

echo "5. Testing pages..."
echo "Test these URLs:"
echo "http://localhost:8080/portal/"
echo "http://localhost:8080/dashboard/"
echo "http://localhost:8080/about-us/"
echo "http://localhost:8080/our-services/"

echo "Done!"