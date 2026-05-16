<?php
/**
 * Rewrite localhost / dev URLs in front-end output (Elementor, menus, content).
 * Production DBs often still contain http://localhost:8080/ from local exports.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('mmla_fix_dev_urls_in_string')) {
    /**
     * @param string $url_or_html Absolute URL or HTML blob.
     */
    function mmla_fix_dev_urls_in_string($url_or_html) {
        if (!is_string($url_or_html) || $url_or_html === '') {
            return $url_or_html;
        }

        $home = untrailingslashit(home_url());
        $replacements = [
            'http://localhost:8080' => $home,
            'https://localhost:8080' => $home,
            'http://localhost' => $home,
            'https://localhost' => $home,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $url_or_html);
    }
}

if (!function_exists('mmla_should_rewrite_dev_urls')) {
    function mmla_should_rewrite_dev_urls() {
        if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
            return false;
        }
        return (bool) apply_filters('mmla_rewrite_dev_urls', true);
    }
}

add_filter('the_content', function ($content) {
    if (!mmla_should_rewrite_dev_urls()) {
        return $content;
    }
    return mmla_fix_dev_urls_in_string($content);
}, 20);

add_filter('widget_text', function ($text) {
    if (!mmla_should_rewrite_dev_urls()) {
        return $text;
    }
    return mmla_fix_dev_urls_in_string($text);
}, 20);

add_filter('nav_menu_link_attributes', function ($atts) {
    if (!mmla_should_rewrite_dev_urls() || empty($atts['href'])) {
        return $atts;
    }
    $atts['href'] = mmla_fix_dev_urls_in_string($atts['href']);
    return $atts;
}, 20);

add_filter('wp_nav_menu_items', function ($items) {
    if (!mmla_should_rewrite_dev_urls()) {
        return $items;
    }
    return mmla_fix_dev_urls_in_string($items);
}, 20);

add_filter('elementor/frontend/the_content', function ($content) {
    if (!mmla_should_rewrite_dev_urls()) {
        return $content;
    }
    return mmla_fix_dev_urls_in_string($content);
}, 20);

add_action('wp_enqueue_scripts', function () {
    if (!mmla_should_rewrite_dev_urls()) {
        return;
    }
    $path = get_stylesheet_directory() . '/js/fix-links.js';
    if (!is_readable($path)) {
        return;
    }
    wp_enqueue_script(
        'mmla-fix-links',
        get_stylesheet_directory_uri() . '/js/fix-links.js',
        [],
        (string) filemtime($path),
        true
    );
    wp_localize_script('mmla-fix-links', 'mmlaFixLinks', [
        'homeUrl' => untrailingslashit(home_url('/')),
    ]);
}, 20);
