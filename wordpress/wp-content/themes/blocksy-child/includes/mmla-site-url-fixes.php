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
        // Longer strings first (avoid turning localhost:8080 into localhost:8080:8080).
        $replacements = [
            'https://localhost:8080' => $home,
            'http://localhost:8080'  => $home,
            'https://127.0.0.1:8080' => $home,
            'http://127.0.0.1:8080'  => $home,
            'https://localhost'      => $home,
            'http://localhost'       => $home,
            'https://127.0.0.1'      => $home,
            'http://127.0.0.1'       => $home,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $url_or_html);
    }
}

if (!function_exists('mmla_is_local_dev_site')) {
    function mmla_is_local_dev_site() {
        $host = wp_parse_url(home_url('/'), PHP_URL_HOST);
        if (!$host) {
            return false;
        }
        return str_contains($host, 'localhost') || str_contains($host, '127.0.0.1') || str_ends_with($host, '.local');
    }
}

if (!function_exists('mmla_should_rewrite_dev_urls')) {
    function mmla_should_rewrite_dev_urls() {
        if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
            return false;
        }
        // Production: rewrite localhost URLs exported from Docker. Local: PHP rewrite is usually a no-op; JS must not break :8080 links.
        $default = !mmla_is_local_dev_site();
        return (bool) apply_filters('mmla_rewrite_dev_urls', $default);
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
    // PHP rewrite on production; JS only there too (broken on local when it mangled :8080 URLs).
    if (mmla_is_local_dev_site() || !mmla_should_rewrite_dev_urls()) {
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
