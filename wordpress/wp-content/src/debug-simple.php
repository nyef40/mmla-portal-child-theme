<?php
require_once('/var/www/html/wp-load.php');

echo "<h1>Portal Debug - Simple</h1>";

// Check current page
global $post;
if ($post) {
    echo "<h2>Current Page</h2>";
    echo "ID: {$post->ID}<br>";
    echo "Slug: {$post->post_name}<br>";
    echo "Title: {$post->post_title}<br>";
}

// Check files
$css_path = get_stylesheet_directory() . '/portal/dist/portal.css';
$js_path = get_stylesheet_directory() . '/portal/dist/portal.js';

echo "<h2>File Check</h2>";
echo "CSS Path: {$css_path}<br>";
echo "CSS Exists: " . (file_exists($css_path) ? 'YES' : 'NO') . "<br>";
echo "CSS URL: " . get_stylesheet_directory_uri() . '/portal/dist/portal.css<br><br>';

echo "JS Path: {$js_path}<br>";
echo "JS Exists: " . (file_exists($js_path) ? 'YES' : 'NO') . "<br>";
echo "JS URL: " . get_stylesheet_directory_uri() . '/portal/dist/portal.js<br>';

// Test if enqueue would run
$portal_pages = ['portal', 'dashboard', 'portal-profile', 'portal-resources', 'portal-referrals', 'portal-login', 'register'];
$current_slug = $post ? $post->post_name : '';
echo "<h2>Enqueue Check</h2>";
echo "Current slug: {$current_slug}<br>";
echo "Is portal page: " . (in_array($current_slug, $portal_pages) ? 'YES' : 'NO') . "<br>";

// Direct test
echo "<h2>Direct Test Links</h2>";
echo '<a href="' . get_stylesheet_directory_uri() . '/portal/dist/portal.css" target="_blank">Test CSS</a><br>';
echo '<a href="' . get_stylesheet_directory_uri() . '/portal/dist/portal.js" target="_blank">Test JS</a>';