<?php
/**
 * Mobile Medical LA Portal - Clean Implementation
 * Single functions.php with all portal functionality
 */

if (!defined('ABSPATH')) exit;

// Suppress PHP deprecation notices in logs (third-party plugins/themes). Do not use E_STRICT (deprecated in PHP 8.4).
error_reporting(E_ALL & ~E_DEPRECATED);

// ============================================
// CONFIGURATION (must be before any require that uses portal_debug)
// ============================================
\define('PORTAL_VERSION', '2.0.0');
\define('PORTAL_DEBUG', true);
date_default_timezone_set('America/Los_Angeles');

function portal_debug($message, $data = null, $file = 'portal-debug.log') {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message";
    
    if ($data !== null) {
        $log_entry .= " | Data: " . (is_array($data) ? json_encode($data) : $data);
    }
    
    $log_entry .= " | User: " . (is_user_logged_in() ? get_current_user_id() : '0');
    $log_entry .= " | Page: " . ($_SERVER['REQUEST_URI'] ?? 'unknown');
    
    error_log($log_entry);
}

/**
 * Encryption key for referral PHI (AES). Resolution order:
 * 1. PORTAL_ENCRYPTION_KEY — explicit override in wp-config
 * 2. MMLA_ENCRYPTION_KEY — project standard (local + production)
 * 3. AUTH_KEY — WordPress salt (legacy / fallback only)
 * 4. Hard-coded dev fallback (do not use in production)
 */
if (!function_exists('get_encryption_key')) {
    function get_encryption_key() {
        if (defined('PORTAL_ENCRYPTION_KEY') && PORTAL_ENCRYPTION_KEY !== '') {
            return PORTAL_ENCRYPTION_KEY;
        }
        if (defined('MMLA_ENCRYPTION_KEY') && MMLA_ENCRYPTION_KEY !== '') {
            return MMLA_ENCRYPTION_KEY;
        }
        if (defined('AUTH_KEY') && AUTH_KEY !== '') {
            return AUTH_KEY;
        }
        return 'portal-referral-key-16';
    }
}

/**
 * Replicate MySQL's AES_ENCRYPT key derivation: XOR the user key bytes
 * across a 16-byte buffer (AES-128 ECB, zero-padded, no IV).
 * This lets us decrypt rows that were inserted with AES_ENCRYPT(value, key).
 */
if (!function_exists('mysql_aes_key')) {
    function mysql_aes_key($key) {
        $derived = str_repeat("\0", 16);
        $len = strlen($key);
        for ($i = 0; $i < $len; $i++) {
            $derived[$i % 16] = chr(ord($derived[$i % 16]) ^ ord($key[$i]));
        }
        return $derived;
    }
}

/**
 * Encrypt a value with MySQL-compatible AES_ENCRYPT (AES-128 ECB,
 * zero-padded, XOR-folded key). Pair this with try_decrypt_field() on read.
 *
 * After the one-time normalization migration runs, every patient_name and
 * patient_email row in lqbk_referral_submissions is encrypted via this function.
 */
if (!function_exists('mysql_aes_encrypt')) {
    function mysql_aes_encrypt($plain, $key = null) {
        if ($plain === null || $plain === '') return $plain;
        if ($key === null) $key = get_encryption_key();
        return openssl_encrypt(
            (string) $plain,
            'aes-128-ecb',
            mysql_aes_key($key),
            OPENSSL_RAW_DATA
        );
    }
}

/**
 * Try-decrypt a patient_name (or any field) that *might* have been stored with
 * MySQL's AES_ENCRYPT. Returns the decrypted UTF-8 string if it decrypts cleanly,
 * otherwise returns the original raw value untouched (so plaintext rows pass through).
 *
 * Tries multiple candidate keys to handle data from different code generations.
 */
if (!function_exists('try_decrypt_field')) {
    function try_decrypt_field($value) {
        if ($value === null || $value === '' || !is_string($value)) {
            return $value;
        }
        // If the value is already printable ASCII/UTF-8 with no control bytes,
        // assume it's already plaintext and skip decryption.
        if (mb_check_encoding($value, 'UTF-8') && !preg_match('/[\x00-\x08\x0E-\x1F\x7F]/', $value)) {
            return $value;
        }
        // Try keys in a fixed order that matches db-migrations / normalize script:
        // legacy MySQL AES_ENCRYPT(..., 'mmla_2025'), then wp-config constants, then WP salts.
        $candidate_keys = array_values(array_filter(array_unique([
            'mmla_2025',
            (defined('MMLA_ENCRYPTION_KEY') && MMLA_ENCRYPTION_KEY !== '') ? MMLA_ENCRYPTION_KEY : null,
            (defined('PORTAL_ENCRYPTION_KEY') && PORTAL_ENCRYPTION_KEY !== '') ? PORTAL_ENCRYPTION_KEY : null,
            get_encryption_key(),
            (defined('AUTH_KEY') && AUTH_KEY !== '') ? AUTH_KEY : null,
            'portal-referral-key-16',
        ])));
        foreach ($candidate_keys as $key) {
            $plain = @openssl_decrypt(
                $value,
                'aes-128-ecb',
                mysql_aes_key($key),
                OPENSSL_RAW_DATA
            );
            if ($plain !== false
                && $plain !== ''
                && mb_check_encoding($plain, 'UTF-8')
                && !preg_match('/[\x00-\x08\x0E-\x1F\x7F]/', $plain)
            ) {
                return $plain;
            }
        }
        // Nothing decrypted cleanly — return a safe placeholder rather than binary garbage.
        return '[encrypted]';
    }
}

/**
 * Portal resource list stored in wp_options (key mmla_portal_resources).
 * Edit via wp-admin (e.g. Options or a small plugin) or update_option from code.
 */
if (!function_exists('mmla_portal_default_resources')) {
    function mmla_portal_default_resources() {
        return [
            [
                'id' => 1,
                'title' => 'Understanding HIPAA Compliance',
                'description' => 'Comprehensive guide to HIPAA compliance for healthcare providers',
                'type' => 'PDF',
                'url' => '/wp-content/uploads/hipaa-guide.pdf',
                'category' => 'Compliance',
                'meta' => 'PDF • 2.3 MB',
            ],
            [
                'id' => 2,
                'title' => 'Patient Care Guidelines',
                'description' => 'Best practices for patient care in home health settings',
                'type' => 'PDF',
                'url' => '/wp-content/uploads/patient-care-guide.pdf',
                'category' => 'Clinical',
                'meta' => 'PDF • 1.8 MB',
            ],
            [
                'id' => 3,
                'title' => 'Emergency Procedures',
                'description' => 'Step-by-step emergency response procedures',
                'type' => 'PDF',
                'url' => '/wp-content/uploads/emergency-procedures.pdf',
                'category' => 'Safety',
                'meta' => 'PDF • 1.2 MB',
            ],
            [
                'id' => 4,
                'title' => 'Referral Form Template',
                'description' => 'Standard referral form template for patient transfers',
                'type' => 'DOC',
                'url' => '/wp-content/uploads/referral-form-template.doc',
                'category' => 'Forms',
                'meta' => 'DOC • 0.5 MB',
            ],
            [
                'id' => 5,
                'title' => 'Insurance Verification Checklist',
                'description' => 'Complete checklist for verifying patient insurance coverage',
                'type' => 'PDF',
                'url' => '/wp-content/uploads/insurance-verification-checklist.pdf',
                'category' => 'Administrative',
                'meta' => 'PDF • 0.8 MB',
            ],
        ];
    }
}

if (!function_exists('mmla_portal_is_legacy_ivig_resources_seed')) {
    /**
     * Detect the earlier 3-item default (IVIG / referral PDF / intake) so we can migrate to the 5-item catalog.
     */
    function mmla_portal_is_legacy_ivig_resources_seed($raw) {
        if (!is_array($raw) || count($raw) !== 3) {
            return false;
        }
        $titles = [];
        foreach ($raw as $row) {
            if (!is_array($row)) {
                return false;
            }
            $titles[] = isset($row['title']) ? (string) $row['title'] : '';
        }
        return $titles[0] === 'IVIG Administration Guide'
            && $titles[1] === 'Referral Form Template'
            && $titles[2] === 'Patient Intake Checklist';
    }
}

if (!function_exists('mmla_portal_migrate_legacy_resources_seed_if_needed')) {
    function mmla_portal_migrate_legacy_resources_seed_if_needed() {
        if ((int) get_option('mmla_portal_resources_catalog_rev', 0) >= 2) {
            return;
        }
        $raw = get_option('mmla_portal_resources');
        if (mmla_portal_is_legacy_ivig_resources_seed($raw)) {
            update_option('mmla_portal_resources', mmla_portal_default_resources(), false);
        }
        update_option('mmla_portal_resources_catalog_rev', 2, false);
    }
}

if (!function_exists('mmla_portal_resource_card_theme')) {
    /**
     * @return array{border:string,btn:string,badge:string}
     */
    function mmla_portal_resource_card_theme($category) {
        switch ($category) {
            case 'Compliance':
                return [
                    'border' => '#17a2b8',
                    'btn' => 'linear-gradient(135deg, #17a2b8 0%, #20c997 100%)',
                    'badge' => '#17a2b8',
                ];
            case 'Clinical':
                return [
                    'border' => '#28a745',
                    'btn' => 'linear-gradient(135deg, #28a745 0%, #20c997 100%)',
                    'badge' => '#28a745',
                ];
            case 'Safety':
                return [
                    'border' => '#dc3545',
                    'btn' => 'linear-gradient(135deg, #dc3545 0%, #c82333 100%)',
                    'badge' => '#dc3545',
                ];
            case 'Forms':
                return [
                    'border' => '#6f42c1',
                    'btn' => 'linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%)',
                    'badge' => '#6f42c1',
                ];
            case 'Administrative':
                return [
                    'border' => '#fd7e14',
                    'btn' => 'linear-gradient(135deg, #fd7e14 0%, #e83e8c 100%)',
                    'badge' => '#fd7e14',
                ];
            default:
                return [
                    'border' => '#0A3D62',
                    'btn' => 'linear-gradient(135deg, #0A3D62 0%, #2980b9 100%)',
                    'badge' => '#0A3D62',
                ];
        }
    }
}

if (!function_exists('mmla_portal_ensure_resources_option')) {
    function mmla_portal_ensure_resources_option() {
        if (!get_option('mmla_portal_resources')) {
            update_option('mmla_portal_resources', mmla_portal_default_resources(), false);
        }
    }
}

if (!function_exists('mmla_portal_get_resources_normalized')) {
    /**
     * @return array<int, array{id:int,title:string,description:string,type:string,url:string,category:string,meta:string}>
     */
    function mmla_portal_get_resources_normalized() {
        mmla_portal_ensure_resources_option();
        mmla_portal_migrate_legacy_resources_seed_if_needed();
        $raw = get_option('mmla_portal_resources', []);
        if (!is_array($raw)) {
            return [];
        }
        $out = [];
        foreach ($raw as $index => $row) {
            if (!is_array($row)) {
                continue;
            }
            $title = isset($row['title']) ? sanitize_text_field((string) $row['title']) : '';
            if ($title === '') {
                continue;
            }
            $url = isset($row['url']) ? esc_url_raw((string) $row['url']) : '';
            if ($url === '') {
                continue;
            }
            $type = isset($row['type']) ? sanitize_text_field((string) $row['type']) : 'PDF';
            $description = isset($row['description']) ? sanitize_textarea_field((string) $row['description']) : '';
            $category = isset($row['category']) ? sanitize_text_field((string) $row['category']) : 'General';
            $meta = isset($row['meta']) ? sanitize_text_field((string) $row['meta']) : '';
            $id = isset($row['id']) ? absint($row['id']) : ($index + 1);
            $out[] = [
                'id' => $id,
                'title' => $title,
                'description' => $description,
                'type' => $type,
                'url' => $url,
                'category' => $category,
                'meta' => $meta,
            ];
        }
        return $out;
    }
}

if (!function_exists('mmla_portal_resources_count')) {
    function mmla_portal_resources_count() {
        return count(mmla_portal_get_resources_normalized());
    }
}

add_action('after_switch_theme', function () {
    if (function_exists('mmla_portal_ensure_resources_option')) {
        mmla_portal_ensure_resources_option();
    }
});

/**
 * Canonical From address for all portal/theme wp_mail() usage.
 * Filter: mmla_portal_mail_from_email. Optional wp-config: MMLA_PORTAL_FROM_EMAIL.
 * Must match a mailbox Google Workspace / WP Mail SMTP can send as (bounce / envelope Sender).
 */
if (!function_exists('mmla_portal_outbound_from_email')) {
    function mmla_portal_outbound_from_email() {
        $default = 'nick.yefimov@mobilemedicalla.com';
        if (defined('MMLA_PORTAL_FROM_EMAIL') && MMLA_PORTAL_FROM_EMAIL !== '') {
            $default = (string) MMLA_PORTAL_FROM_EMAIL;
        }
        $email = apply_filters('mmla_portal_mail_from_email', $default);
        $email = sanitize_email($email);
        return ($email !== '' && is_email($email)) ? $email : 'nick.yefimov@mobilemedicalla.com';
    }
}

if (!function_exists('mmla_portal_outbound_from_name')) {
    function mmla_portal_outbound_from_name() {
        $name = apply_filters(
            'mmla_portal_mail_from_name',
            wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES)
        );
        $name = is_string($name) ? trim($name) : '';
        return $name !== '' ? $name : 'Mobile Medical LA Portal';
    }
}

/**
 * GoDaddy/cPanel often sets envelope MAIL FROM to account@server.host (undeliverable).
 * WP Mail SMTP uses From for SMTP auth, but bounces still go to Sender/Return-Path — force it.
 */
add_action('phpmailer_init', function ($phpmailer) {
    if (!is_object($phpmailer)) {
        return;
    }
    $from = mmla_portal_outbound_from_email();
    if (is_email($from)) {
        $phpmailer->Sender = $from;
    }
}, 5, 1);

// Disable comments and pingbacks sitewide (spam reduction; complements Settings → Discussion).
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);
add_filter('comments_array', '__return_empty_array', 10, 2);
remove_action('wp_head', 'feed_links_extra', 3);

// Load portal services and auth (after portal_debug exists)
if (is_readable(get_stylesheet_directory() . '/includes/PortalAuthService.php')) {
    require_once get_stylesheet_directory() . '/includes/PortalAuthService.php';
}
if (is_readable(get_stylesheet_directory() . '/includes/PortalRegistrationService.php')) {
    require_once get_stylesheet_directory() . '/includes/PortalRegistrationService.php';
}
require_once get_stylesheet_directory() . '/portal-loader.php';
if (is_readable(get_stylesheet_directory() . '/functions-portal-auth-enhanced.php')) {
    require_once get_stylesheet_directory() . '/functions-portal-auth-enhanced.php';
}

// ============================================
// 0a. REDIRECTS: /portal/ -> /dashboard/; old /portal/xxx/ -> correct slugs
// ============================================
add_action('template_redirect', function() {
    // Single dashboard entry: /portal/ shows same as dashboard (avoids duplicate upper/lower)
    if (is_page('portal')) {
        wp_redirect(home_url('/dashboard/'), 302);
        exit;
    }
    $uri = isset($_SERVER['REQUEST_URI']) ? strtok($_SERVER['REQUEST_URI'], '?') : '';
    $redirects = [
        '/portal/profile/' => '/portal-profile/',
        '/portal/resources/' => '/portal-resources/',
        '/portal/referrals/' => '/portal-referrals/',
        '/portal/contact/' => '/contact/',
    ];
    /*
     * Do NOT redirect /resources2/ → /resources/ on production by default: many live sites
     * already use SEO plugins or server rules that send /resources/ → /resources2/ (old
     * canonical). Theme + host redirects in opposite directions = ERR_TOO_MANY_REDIRECTS.
     * Local only: mirror /resources2/ to the page-resources template at /resources/.
     * To force on production after removing duplicate host/SEO rules:
     *   add_filter('mmla_redirect_resources2_to_resources', '__return_true');
     */
    $host = wp_parse_url(home_url('/'), PHP_URL_HOST);
    $local_resources2_redirect = $host && (
        str_contains($host, 'localhost')
        || str_contains($host, '127.0.0.1')
        || str_ends_with($host, '.local')
    );
    if (apply_filters('mmla_redirect_resources2_to_resources', $local_resources2_redirect)) {
        $redirects['/resources2/'] = '/resources/';
    }
    foreach ($redirects as $from => $to) {
        if ($uri === $from || $uri === rtrim($from, '/')) {
            wp_redirect(home_url($to), 301);
            exit;
        }
    }
}, 0);

// ============================================
// 0. NO-CACHE FOR PORTAL PAGES (fresh nonce + ensure our template runs, not cached HTML)
// ============================================
// Only login and register: no-cache (fresh nonce). Other portal pages: allow cache to avoid session/cookie issues.
add_action('template_redirect', function() {
    if (is_page('portal-login') || is_page('register')) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
}, 1);
add_action('wp_head', function() {
    if (is_page('portal-login') || is_page('register')) {
        echo '<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">';
        echo '<meta http-equiv="Pragma" content="no-cache">';
        echo '<meta http-equiv="Expires" content="0">';
    }
}, 1);

// ============================================
// 1. PORTAL PAGE DETECTION & BODY CLASS (hide duplicate on profile/resources/contact)
// ============================================
// Force our page templates for portal-login, portal-profile, portal-resources, contact so #portal-page-main is always output (overrides Elementor/Default template)
add_filter('template_include', function($template) {
    $portal_templates = [
        'portal-login'     => 'page-portal-login-enhanced.php',
        'portal-profile'   => 'page-profile.php',
        'portal-resources' => 'page-portal-resources.php',
        'contact'          => 'page-contact.php',
        'register'         => 'page-register-enhanced.php',
    ];
    $slug = null;
    if (is_page(array_keys($portal_templates))) {
        $slug = get_post_field('post_name', get_queried_object_id());
    }
    // Fallback: detect by request URI (in case main query was altered by cache/plugin)
    if (empty($slug) && !empty($_SERVER['REQUEST_URI'])) {
        $uri = strtok($_SERVER['REQUEST_URI'], '?');
        $uri = trim($uri, '/');
        foreach (array_keys($portal_templates) as $s) {
            if ($uri === $s || $uri === $s . '/' || strpos($uri, $s . '/') === 0) {
                $slug = $s;
                break;
            }
        }
    }
    if (!empty($slug) && !empty($portal_templates[$slug])) {
        $our = get_stylesheet_directory() . '/' . $portal_templates[$slug];
        if (is_readable($our)) {
            return $our;
        }
    }
    return $template;
}, 999);

add_filter('body_class', function ($classes) {
    if (is_page(['portal-login', 'register', 'portal-profile', 'portal-resources', 'contact', 'dashboard', 'portal-referrals', 'portal'])) {
        $classes[] = 'portal-single-view';
    }
    if (is_page(['dashboard', 'portal-referrals'])) {
        $classes[] = 'portal-react-mount';
    }
    return $classes;
}, 999);

/**
 * Portal pages use minimal templates; wpautop often injects empty <p> tags that leave a white gap at the bottom.
 */
add_action('template_redirect', function () {
    if (!function_exists('is_portal_page') || !is_portal_page()) {
        return;
    }
    remove_filter('the_content', 'wpautop');
    remove_filter('the_excerpt', 'wpautop');
}, 4);

function is_portal_page() {
    global $post;
    if (is_admin() || !$post) return false;
    
    // Direct slugs
    $portal_slugs = [
        'portal', 'dashboard', 'portal-profile', 'portal-resources',
        'portal-referrals', 'portal-login', 'register', 'contact',
        'profile', 'referrals', 'login'  // child slugs under portal (not public /resources/)
    ];
    
    // Check if it's a child of portal
    if ($post->post_parent) {
        $parent = get_post($post->post_parent);
        if ($parent && $parent->post_name === 'portal') {
            return true;
        }
    }
    
    return in_array($post->post_name, $portal_slugs);
}

/**
 * YouTube video IDs for the public /resources/ page (page-resources.php).
 *
 * Precedence:
 * 1. Option `mmla_public_resources_youtube_ids` (array of 11-char IDs), if non-empty.
 * 2. Constant `MMLA_PUBLIC_RESOURCES_YOUTUBE_IDS` (PHP 7+ array), if defined — see config/wp-config.php.
 * 3. Filter `mmla_public_resources_youtube_ids` default list (aligned with production /resources2/).
 *
 * Hide the grid: `add_filter('mmla_public_resources_youtube_ids', '__return_empty_array');`
 * Force theme grid under Elementor: `add_filter('mmla_public_resources_append_video_grid', '__return_true');`
 * Page title / intro: edit the WordPress page with slug `resources` (not the portal URL /portal-resources/).
 */
if (!function_exists('mmla_get_public_resources_youtube_ids')) {
    function mmla_get_public_resources_youtube_ids() {
        $opt = get_option('mmla_public_resources_youtube_ids');
        if (is_array($opt) && count(array_filter($opt)) > 0) {
            return array_slice(array_values(array_filter(array_map('sanitize_text_field', $opt))), 0, 9);
        }
        if (defined('MMLA_PUBLIC_RESOURCES_YOUTUBE_IDS') && is_array(MMLA_PUBLIC_RESOURCES_YOUTUBE_IDS)) {
            return array_slice(MMLA_PUBLIC_RESOURCES_YOUTUBE_IDS, 0, 9);
        }
        // Production https://mobilemedicalla.com/resources2/ (Elementor), May 2025.
        return apply_filters(
            'mmla_public_resources_youtube_ids',
            [
                '5BhXLu3zMoQ',
                'fz5QuBGZIZU',
                'WJVVYk1sg0I',
                'e0beW0LfFGo',
                'fmrt-6E-ufY',
                'iZ_r5ztWa4k',
                'KL24UudgYtU',
                'dhv4haxM_1Q',
                'bhO7re7lOQI',
            ]
        );
    }
}

/**
 * Whether page-resources.php should append the theme YouTube grid below the_content().
 * Skipped by default when the page is built with Elementor (videos already in the layout).
 *
 * @param int $post_id
 * @return bool
 */
if (!function_exists('mmla_should_append_public_resources_video_grid')) {
    function mmla_should_append_public_resources_video_grid($post_id) {
        $post_id = (int) $post_id;
        if ($post_id < 1) {
            return false;
        }
        $decision = apply_filters('mmla_public_resources_append_video_grid', null, $post_id);
        if ($decision === false) {
            return false;
        }
        if ($decision === true) {
            return true;
        }
        if (get_post_meta($post_id, '_elementor_edit_mode', true) === 'builder') {
            return false;
        }
        return true;
    }
}

/**
 * Elementor core newer than Elementor Pro: Pro’s `pro-preloaded-elements-handlers.js` calls
 * `elementorCommon.helpers.softDeprecated`, which current Elementor core no longer defines →
 * uncaught TypeError and broken Pro frontend widgets. Shim before that script runs (all public
 * pages that enqueue it). Long-term fix: upgrade Elementor Pro to match core (e.g. 3.31.x with 3.31.x).
 */
add_action('wp_enqueue_scripts', function () {
    if (is_admin()) {
        return;
    }
    if (!wp_script_is('pro-preloaded-elements-handlers', 'enqueued')) {
        return;
    }
    $shim = <<<'JS'
(function(){try{var c=window.elementorCommon;if(!c)return;var h=c.helpers||(c.helpers={});if(typeof h.softDeprecated!=="function"){h.softDeprecated=function(){}}}catch(e){}})();
JS;
    wp_add_inline_script('pro-preloaded-elements-handlers', $shim, 'before');
}, 99999);

// ============================================
// 2. ENQUEUE PORTAL ASSETS
// ============================================
add_action('wp_enqueue_scripts', function() {
    if (!is_portal_page()) return;
    
    global $post;
    
    // Dequeue theme/Elementor scripts on portal pages
    wp_dequeue_script('elementor-frontend');
    wp_dequeue_script('elementor-pro-frontend');
    wp_dequeue_style('elementor-frontend');
    
    $dist_path = get_stylesheet_directory() . '/portal/dist';
    $dist_url = get_stylesheet_directory_uri() . '/portal/dist';
    
    // CSS
    if (file_exists("$dist_path/portal.css")) {
        wp_enqueue_style('portal-css', "$dist_url/portal.css", [], filemtime("$dist_path/portal.css"));
    }
    
    // JS
    if (file_exists("$dist_path/portal.js")) {
        wp_enqueue_script('portal-js', "$dist_url/portal.js", ['wp-element'], filemtime("$dist_path/portal.js"), true);
        
        $current_user = wp_get_current_user();
        
        wp_localize_script('portal-js', 'wpPortalData', [
            'ajaxUrl'       => admin_url('admin-ajax.php'),
            'restUrl'       => rest_url('portal/v1/'),
            'siteUrl'       => home_url('/'),
            'nonce'         => wp_create_nonce('portal_nonce'),
            'restNonce'     => wp_create_nonce('wp_rest'),
            'currentPage'   => $post->post_name,
            'isLoggedIn'    => is_user_logged_in(),
            'logoutUrl'     => wp_logout_url(home_url('/portal-login/')),
            'user'          => is_user_logged_in() ? [
                'id'          => $current_user->ID,
                'email'       => $current_user->user_email,
                'displayName' => $current_user->display_name,
                'firstName'   => get_user_meta($current_user->ID, 'first_name', true),
                'lastName'    => get_user_meta($current_user->ID, 'last_name', true)
            ] : null
        ]);
    }
}, 9999);

// ============================================
// 3. PORTAL TEMPLATE OUTPUT
// ============================================
add_action('template_redirect', function() {
    if (!is_portal_page()) return;
    
    // Add styles to head
    add_action('wp_head', function() {
        ?>
        <style>
            /* Hide theme header/footer */
            header, .site-header, #masthead, footer, .site-footer, 
            .ct-header, .ct-footer, #colophon { display: none !important; }
            
            body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, sans-serif; }

            /* Full-viewport grey (avoids white band below React shell when body was default white) */
            html { background-color: #f1f5f9; }
            body.portal-single-view {
                background: #f1f5f9 !important;
                min-height: 100vh;
                min-height: 100dvh;
            }
            #portal-root {
                background: #f1f5f9;
            }

            /* Dashboard / referrals: strip theme bottom padding from empty loop content */
            body.portal-react-mount .site-content,
            body.portal-react-mount .site-main,
            body.portal-react-mount main,
            body.portal-react-mount #content,
            body.portal-react-mount .content-area,
            body.portal-react-mount .ct-container,
            body.portal-react-mount article,
            body.portal-react-mount .entry-content,
            body.portal-react-mount .entry-content > *:last-child {
                margin-bottom: 0 !important;
                padding-bottom: 0 !important;
            }
            
            /* Portal Header */
            .portal-header {
                background: linear-gradient(135deg, #0A3D62 0%, #1a5a8a 50%, #2980b9 100%);
                padding: 0;
                position: sticky;
                top: 0;
                z-index: 9999;
                box-shadow: 0 4px 20px rgba(10, 61, 98, 0.4);
            }
            .portal-header-inner {
                max-width: 1400px;
                margin: 0 auto;
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 12px 24px;
            }
            .portal-logo {
                color: white;
                font-size: 18px;
                font-weight: 700;
                text-decoration: none;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .portal-logo img {
                height: 45px;
                width: auto;
            }
            .portal-nav {
                display: flex;
                gap: 8px;
                align-items: center;
            }
            .portal-nav a {
                color: rgba(255,255,255,0.85);
                text-decoration: none;
                padding: 10px 18px;
                border-radius: 25px;
                font-weight: 500;
                font-size: 14px;
                transition: all 0.3s ease;
            }
            .portal-nav a:hover, .portal-nav a.active {
                background: rgba(255,255,255,0.2);
                color: white;
            }
            .portal-nav .logout-btn {
                background: linear-gradient(135deg, #e74c3c, #c0392b);
                color: white !important;
                margin-left: 10px;
            }
            .portal-nav .logout-btn:hover {
                background: linear-gradient(135deg, #c0392b, #a93226);
                transform: translateY(-1px);
            }
            .back-to-site {
                background: rgba(255,255,255,0.15);
                border: 1px solid rgba(255,255,255,0.3);
                backdrop-filter: blur(10px);
            }
            
            body.portal-react-mount #portal-root {
                min-height: calc(100dvh - 70px);
                min-height: calc(100vh - 70px);
                margin-bottom: 0;
                padding-bottom: 0;
                box-sizing: border-box;
            }
            
            /* Loading state */
            .portal-init-loading {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 400px;
                background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            }
            .portal-init-loading h2 {
                color: #0A3D62;
                margin-bottom: 10px;
            }
            .portal-init-loading p {
                color: #64748b;
            }
            .spinner {
                width: 50px;
                height: 50px;
                border: 4px solid #e2e8f0;
                border-top-color: #0A3D62;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin: 20px 0;
            }
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
        </style>
        <?php
    }, 999);
    
    // Add header HTML – all links use home_url() so Register/Login go to correct pages
    add_action('wp_body_open', function() {
        global $post;
        $current = $post ? $post->post_name : '';
        $is_logged_in = is_user_logged_in();
        $logout_url = wp_logout_url(home_url('/portal-login/'));
        $logo = home_url('/wp-content/uploads/2024/03/2018_04_01_mmla_logo-removebg-preview.png');
        ?>
        <div class="portal-header">
            <div class="portal-header-inner">
                <a href="<?php echo esc_url(home_url('/portal/')); ?>" class="portal-logo">
                    <img src="<?php echo esc_url($logo); ?>" alt="MMLA" onerror="this.style.display='none'">
                    <span>Provider Portal</span>
                </a>
                <nav class="portal-nav">
                    <?php if ($is_logged_in): ?>
                        <a href="<?php echo esc_url(home_url('/dashboard/')); ?>" class="<?php echo $current === 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
                        <a href="<?php echo esc_url(home_url('/portal-profile/')); ?>" class="<?php echo $current === 'portal-profile' ? 'active' : ''; ?>">Profile</a>
                        <a href="<?php echo esc_url(home_url('/portal-resources/')); ?>" class="<?php echo $current === 'portal-resources' ? 'active' : ''; ?>">Resources</a>
                        <a href="<?php echo esc_url(home_url('/portal-referrals/')); ?>" class="<?php echo $current === 'portal-referrals' ? 'active' : ''; ?>">Referrals</a>
                        <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="<?php echo $current === 'contact' ? 'active' : ''; ?>">Contact</a>
                        <a href="<?php echo esc_url($logout_url); ?>" class="logout-btn">Logout</a>
                    <?php else: ?>
                        <a href="<?php echo esc_url(home_url('/portal-login/')); ?>" class="<?php echo $current === 'portal-login' ? 'active' : ''; ?>">Login</a>
                        <a href="<?php echo esc_url(home_url('/register/')); ?>" class="<?php echo $current === 'register' ? 'active' : ''; ?>">Register</a>
                    <?php endif; ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="back-to-site">Main Site</a>
                </nav>
            </div>
        </div>

        <?php
        // Only inject the React mount point on pages that actually run the React app.
        // PHP-only pages (portal-profile, portal-resources, contact, portal-login, register)
        // render their own content inside <div id="portal-page-main"> via their page templates.
        if (is_page(['dashboard', 'portal-referrals'])): ?>
        <div id="portal-root">
            <div class="portal-init-loading">
                <div class="spinner"></div>
                <h2>Loading Portal</h2>
                <p>Preparing your experience...</p>
            </div>
        </div>
        <?php endif; ?>
        <?php
    }, 1);
});

// ============================================
// 4. PROTECT PORTAL PAGES
// ============================================
add_action('template_redirect', function() {
    $protected = ['dashboard', 'portal-profile', 'portal-resources', 'portal-referrals'];
    global $post;

    if ($post && in_array($post->post_name, $protected) && !is_user_logged_in()) {
        wp_redirect(home_url('/portal-login/'));
        exit;
    }
}, 5);

// ============================================
// 4b. STOP DUPLICATE FORMS AT THE SOURCE
// ============================================
// On the three PHP-only portal pages (portal-profile, portal-resources, contact)
// the page template renders the form itself inside <div id="portal-page-main">.
// However Elementor Pro's Theme Builder and Blocksy's content hooks may also
// render the page (from _elementor_data postmeta or from post_content), causing
// a duplicate form to appear ABOVE the correct one. We suppress that here.
add_action('template_redirect', function() {
    if (!is_page(['portal-profile', 'portal-resources', 'contact'])) return;

    // 1. Blank post_content so Gutenberg/Blocksy auto-rendering produces nothing.
    global $wp_query;
    if (isset($wp_query->post)) {
        $wp_query->post->post_content = '';
        $wp_query->post->post_content_filtered = '';
    }
    add_filter('the_content', '__return_empty_string', PHP_INT_MAX);

    // 2. Disable Elementor frontend rendering for this request.
    if (class_exists('\Elementor\Plugin')) {
        // Suppress Elementor's "single" Theme Builder template (Elementor Pro).
        add_filter('elementor/theme/get_location_templates/template_id', '__return_zero', PHP_INT_MAX);

        // Make Elementor think this page is NOT built with Elementor by hiding
        // the _elementor_edit_mode flag during this request only.
        add_filter('get_post_metadata', function($value, $object_id, $meta_key, $single) {
            if ($meta_key === '_elementor_edit_mode' && $object_id === get_queried_object_id()) {
                return $single ? '' : [''];
            }
            return $value;
        }, 10, 4);
    }
}, 6);

// ============================================
// 5. LOGIN HANDLER (AJAX) – only if auth-enhanced not loaded
// ============================================
if (!function_exists('handle_portal_login')) {
    add_action('wp_ajax_nopriv_portal_login', 'handle_portal_login');
    add_action('wp_ajax_portal_login', 'handle_portal_login');
    function handle_portal_login() {
        $nonce = $_POST['nonce'] ?? '';
        if (
            !wp_verify_nonce($nonce, 'portal_login_form_nonce')
            && !wp_verify_nonce($nonce, 'portal_login_nonce')
            && !wp_verify_nonce($nonce, 'portal_nonce')
        ) {
            wp_send_json_error('Security check failed');
            return;
        }
        $username = sanitize_user($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = !empty($_POST['remember']);
        if (empty($username) || empty($password)) {
            wp_send_json_error('Please enter username and password');
            return;
        }
        $user = wp_signon([
            'user_login' => $username,
            'user_password' => $password,
            'remember' => $remember
        ], is_ssl());
        if (is_wp_error($user)) {
            wp_send_json_error('Invalid username or password');
            return;
        }
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'portal_users',
            ['last_login' => current_time('mysql')],
            ['wp_user_id' => $user->ID]
        );
        wp_send_json_success([
            'message' => 'Login successful',
            'redirect' => home_url('/dashboard/'),
            'user' => ['id' => $user->ID, 'displayName' => $user->display_name, 'email' => $user->user_email]
        ]);
    }
}

// Fresh nonce for login form (avoids stale nonce when page is cached)
add_action('wp_ajax_nopriv_portal_login_fresh_nonce', 'portal_login_fresh_nonce');
add_action('wp_ajax_portal_login_fresh_nonce', 'portal_login_fresh_nonce');
function portal_login_fresh_nonce() {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    wp_send_json_success(['nonce' => wp_create_nonce('portal_login_form_nonce')]);
}

// Fresh nonce for register form (avoids stale nonce when page is cached)
add_action('wp_ajax_nopriv_portal_register_fresh_nonce', 'portal_register_fresh_nonce');
add_action('wp_ajax_portal_register_fresh_nonce', 'portal_register_fresh_nonce');
function portal_register_fresh_nonce() {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    wp_send_json_success(['nonce' => wp_create_nonce('portal_register_nonce')]);
}

// ============================================
// 6. REGISTER – shared logic and handlers
// ============================================

/**
 * Process registration from POST data. Used by AJAX and by full-page POST fallback.
 * @param array $post e.g. $_POST
 * @return array ['ok' => true] or ['ok' => false, 'message' => string]
 */
function portal_process_registration($post) {
    if (!class_exists('PortalRegistrationService')) {
        return ['ok' => false, 'message' => 'Registration service unavailable'];
    }
    global $wpdb;
    $service = new PortalRegistrationService($wpdb);
    return $service->register((array) $post);
}

// Fallback: full-page POST to /register/ (when JS fails or form doesn’t use AJAX)
add_action('template_redirect', function() {
    if (!is_page('register') || $_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['action']) || $_POST['action'] !== 'portal_register') {
        return;
    }
    $result = portal_process_registration($_POST);
    if ($result['ok']) {
        wp_redirect(home_url('/portal-login/?registered=1'), 302);
        exit;
    }
    set_transient('portal_register_error', $result['message'], 60);
    wp_redirect(home_url('/register/?error=1'), 302);
    exit;
}, 5);

// Single registration handler for both local and live: insert portal_users, send verification email, link sets email_verified=1.
if (!function_exists('handle_portal_register')) {
    add_action('wp_ajax_nopriv_portal_register', 'handle_portal_register', 1);
    add_action('wp_ajax_portal_register', 'handle_portal_register', 1);
    function handle_portal_register() {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        $result = portal_process_registration($_POST);
        if ($result['ok']) {
            wp_send_json_success([
                'message'  => 'Registration successful! Please check your email to verify your account.',
                'redirect' => home_url('/portal-login/?registered=1')
            ]);
        }
        wp_send_json_error($result['message']);
    }
}

/**
 * Send verification email directly (not via hook)
 */
function send_verification_email_direct($user_id, $email, $first_name, $token) {
    // Build verification URL with the raw token
    $verify_url = add_query_arg([
        'action' => 'verify_portal_email',
        'token'  => $token,
        'uid'    => $user_id
    ], home_url('/'));
    
    $subject = 'Verify Your Email - Mobile Medical LA Provider Portal';
    
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f4f7fa;">
        <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f7fa; padding: 40px 20px;">
            <tr>
                <td align="center">
                    <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                        <tr>
                            <td style="background: linear-gradient(135deg, #0A3D62 0%, #2980b9 100%); padding: 30px 40px; text-align: center;">
                                <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700;">Mobile Medical LA</h1>
                                <p style="color: rgba(255,255,255,0.85); margin: 8px 0 0 0; font-size: 14px;">Provider Portal</p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 40px;">
                                <h2 style="color: #1e293b; margin: 0 0 20px 0; font-size: 22px;">Welcome, ' . esc_html($first_name ?: 'Provider') . '!</h2>
                                <p style="color: #475569; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                    Thank you for registering with the Mobile Medical LA Provider Portal. To complete your registration and access all portal features, please verify your email address.
                                </p>
                                <p style="color: #475569; font-size: 16px; line-height: 1.6; margin: 0 0 30px 0;">
                                    Click the button below to verify your email:
                                </p>
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td align="center">
                                            <a href="' . esc_url($verify_url) . '" style="display: inline-block; background: linear-gradient(135deg, #0A3D62 0%, #2980b9 100%); color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 8px; font-size: 16px; font-weight: 600;">
                                                Verify Email Address
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                                <p style="color: #64748b; font-size: 14px; line-height: 1.6; margin: 30px 0 0 0;">
                                    This verification link will expire in <strong>24 hours</strong>.
                                </p>
                                <p style="color: #64748b; font-size: 14px; line-height: 1.6; margin: 20px 0 0 0;">
                                    If the button does not work, copy and paste this link into your browser:
                                </p>
                                <p style="color: #0A3D62; font-size: 12px; word-break: break-all; margin: 10px 0 0 0;">
                                    ' . esc_url($verify_url) . '
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="background-color: #f8fafc; padding: 24px 40px; border-top: 1px solid #e2e8f0;">
                                <p style="color: #94a3b8; font-size: 12px; margin: 0; text-align: center;">
                                    If you did not create an account, please ignore this email.
                                </p>
                                <p style="color: #94a3b8; font-size: 12px; margin: 10px 0 0 0; text-align: center;">
                                    &copy; ' . date('Y') . ' Mobile Medical LA. All rights reserved.
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';
    
    $from_addr = mmla_portal_outbound_from_email();
    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: Mobile Medical LA Portal <' . $from_addr . '>',
        'Reply-To: ' . $from_addr,
    ];
    
    $sent = wp_mail($email, $subject, $message, $headers);
    
    if ($sent) {
        error_log("[Portal] Verification email sent to $email for user $user_id");
    } else {
        error_log("[Portal] Failed to send verification email to $email");
    }
    
    return $sent;
}

// ============================================
// EMAIL VERIFICATION SYSTEM
// ============================================

/**
 * Send verification email after portal registration
 * Hooks into user_register to send email automatically
 */
// add_action('user_register', 'send_portal_verification_email', 20, 1);

function send_portal_verification_email($user_id) {
    global $wpdb;
    
    $user = get_userdata($user_id);
    if (!$user) {
        error_log("[Portal] send_portal_verification_email: User not found for ID $user_id");
        return false;
    }
    
    // Generate secure verification token
    $token = wp_generate_password(32, false, false);
    $token_hash = hash('sha256', $token);
    $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    // Store token in portal_users table
    $table = $wpdb->prefix . 'portal_users';
    
    // First check if validation_token column exists, if not add it
    $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $table LIKE 'validation_token'");
    if (empty($column_exists)) {
        $wpdb->query("ALTER TABLE $table ADD COLUMN validation_token VARCHAR(64) NULL");
        $wpdb->query("ALTER TABLE $table ADD COLUMN token_expiry DATETIME NULL");
    }
    
    // Update the portal user with token
    $updated = $wpdb->update(
        $table,
        [
            'validation_token' => $token_hash,
            'token_expiry' => $expiry
        ],
        ['wp_user_id' => $user_id]
    );

    // Add debug logging
    error_log("[Portal] Token update result: " . ($updated !== false ? 'success' : 'failed') . " for user $user_id");
    error_log("[Portal] Stored token hash: $token_hash");
    
    if ($updated === false) {
        error_log("[Portal] Failed to store validation token for user $user_id");
        return false;
    }
    
    // Build verification URL (use raw token, we'll hash it on verification)
    $verify_url = add_query_arg([
        'action' => 'verify_portal_email',
        'token' => $token,
        'uid' => $user_id
    ], home_url('/'));
    
    // Get user details for email
    $portal_user = $wpdb->get_row($wpdb->prepare(
        "SELECT first_name FROM $table WHERE wp_user_id = %d",
        $user_id
    ));
    $first_name = $portal_user->first_name ?: $user->display_name ?: 'Provider';
    
    // Build HTML email
    $subject = 'Verify Your Email - Mobile Medical LA Provider Portal';
    
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f4f7fa;">
        <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f7fa; padding: 40px 20px;">
            <tr>
                <td align="center">
                    <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                        <!-- Header -->
                        <tr>
                            <td style="background: linear-gradient(135deg, #0A3D62 0%, #2980b9 100%); padding: 30px 40px; text-align: center;">
                                <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700;">Mobile Medical LA</h1>
                                <p style="color: rgba(255,255,255,0.85); margin: 8px 0 0 0; font-size: 14px;">Provider Portal</p>
                            </td>
                        </tr>
                        
                        <!-- Body -->
                        <tr>
                            <td style="padding: 40px;">
                                <h2 style="color: #1e293b; margin: 0 0 20px 0; font-size: 22px;">Welcome, ' . esc_html($first_name) . '!</h2>
                                
                                <p style="color: #475569; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                    Thank you for registering with the Mobile Medical LA Provider Portal. To complete your registration and access all portal features, please verify your email address.
                                </p>
                                
                                <p style="color: #475569; font-size: 16px; line-height: 1.6; margin: 0 0 30px 0;">
                                    Click the button below to verify your email:
                                </p>
                                
                                <!-- CTA Button -->
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td align="center">
                                            <a href="' . esc_url($verify_url) . '" style="display: inline-block; background: linear-gradient(135deg, #0A3D62 0%, #2980b9 100%); color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 8px; font-size: 16px; font-weight: 600;">
                                                Verify Email Address
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                                
                                <p style="color: #64748b; font-size: 14px; line-height: 1.6; margin: 30px 0 0 0;">
                                    This verification link will expire in <strong>24 hours</strong>.
                                </p>
                                
                                <p style="color: #64748b; font-size: 14px; line-height: 1.6; margin: 20px 0 0 0;">
                                    If the button does not work, copy and paste this link into your browser:
                                </p>
                                <p style="color: #0A3D62; font-size: 12px; word-break: break-all; margin: 10px 0 0 0;">
                                    ' . esc_url($verify_url) . '
                                </p>
                            </td>
                        </tr>
                        
                        <!-- Footer -->
                        <tr>
                            <td style="background-color: #f8fafc; padding: 24px 40px; border-top: 1px solid #e2e8f0;">
                                <p style="color: #94a3b8; font-size: 12px; margin: 0; text-align: center;">
                                    If you did not create an account, please ignore this email.
                                </p>
                                <p style="color: #94a3b8; font-size: 12px; margin: 10px 0 0 0; text-align: center;">
                                    &copy; ' . date('Y') . ' Mobile Medical LA. All rights reserved.
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';
    
    // Set HTML headers
    $from_addr = mmla_portal_outbound_from_email();
    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: Mobile Medical LA Portal <' . $from_addr . '>',
        'Reply-To: ' . $from_addr,
    ];
    
    // Send email
    $sent = wp_mail($user->user_email, $subject, $message, $headers);
    
    if ($sent) {
        error_log("[Portal] Verification email sent to {$user->user_email} for user $user_id");
    } else {
        error_log("[Portal] Failed to send verification email to {$user->user_email}");
    }
    
    return $sent;
}

/**
 * Handle email verification URL
 * Intercepts requests with action=verify_portal_email
 */
add_action('init', 'handle_portal_email_verification');

function handle_portal_email_verification() {
    if (!isset($_GET['action']) || $_GET['action'] !== 'verify_portal_email') {
        return;
    }
    
    $token = sanitize_text_field($_GET['token'] ?? '');
    $user_id = absint($_GET['uid'] ?? 0);
    
    if (empty($token) || empty($user_id)) {
        wp_redirect(home_url('/portal-login/?error=invalid_link'));
        exit;
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'portal_users';
    
    // Hash the token to compare with stored hash
    $token_hash = hash('sha256', $token);
    
    // Find portal user with matching token
    $portal_user = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE wp_user_id = %d AND validation_token = %s",
        $user_id,
        $token_hash
    ));
    
    if (!$portal_user) {
        error_log("[Portal] Invalid verification token for user $user_id");
        wp_redirect(home_url('/portal-login/?error=invalid_token'));
        exit;
    }
    
    // Check if token expired
    if (!empty($portal_user->token_expiry) && strtotime($portal_user->token_expiry) < time()) {
        error_log("[Portal] Verification token expired for user $user_id");
        wp_redirect(home_url('/portal-login/?error=token_expired'));
        exit;
    }

    // Check if already verified
    if ($portal_user->email_verified == 1) {
        wp_redirect(home_url('/portal-login/?message=already_verified'));
        exit;
    }
    
    // Mark email as verified and clear token
    $updated = $wpdb->update(
        $table,
        [
            'email_verified' => 1,
            'validation_token' => null,
            'token_expiry' => null,
            'updated_at' => current_time('mysql')
        ],
        ['wp_user_id' => $user_id]
    );
    
    if ($updated !== false) {
        error_log("[Portal] Email verified successfully for user $user_id");
        wp_redirect(home_url('/portal-login/?verified=1'));
    } else {
        error_log("[Portal] Failed to update email_verified for user $user_id");
        wp_redirect(home_url('/portal-login/?error=verification_failed'));
    }
    exit;
}

// ============================================
// REFERRAL — provider email validation (confirm receipt)
// ============================================

if (!function_exists('mmla_referral_ensure_validation_token_column')) {
    function mmla_referral_ensure_validation_token_column() {
        global $wpdb;
        $table = $wpdb->prefix . 'referral_submissions';
        $exists = $wpdb->get_results("SHOW COLUMNS FROM `{$table}` LIKE 'validation_token'");
        if (empty($exists)) {
            $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `validation_token` VARCHAR(64) NULL DEFAULT NULL");
        }
    }
}

if (!function_exists('mmla_referral_validation_url')) {
    function mmla_referral_validation_url($submission_id, $raw_token) {
        return add_query_arg(
            [
                'action' => 'validate_referral',
                'sid'    => (int) $submission_id,
                'token'  => $raw_token,
            ],
            home_url('/')
        );
    }
}

if (!function_exists('mmla_send_referral_provider_validation_email')) {
    /**
     * Notify the receiving provider with a one-time link to mark the referral as validated.
     *
     * @param int    $submission_id
     * @param string $provider_email
     * @param string $provider_name
     * @param string $patient_name_plain Display name (plaintext)
     * @param string $reason
     * @param string $raw_token           Secret sent in URL; only SHA-256 hash is stored in DB.
     * @return bool Whether wp_mail reported success
     */
    function mmla_send_referral_provider_validation_email(
        $submission_id,
        $provider_email,
        $provider_name,
        $patient_name_plain,
        $reason,
        $raw_token
    ) {
        $provider_email = sanitize_email($provider_email);
        if ($provider_email === '' || !is_email($provider_email)) {
            error_log('[Portal] Referral validation email skipped: invalid provider_email');
            return false;
        }

        $link = esc_url(mmla_referral_validation_url((int) $submission_id, $raw_token));
        $site = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
        $subject = sprintf('[%s] Confirm referral — %s', $site, $patient_name_plain !== '' ? $patient_name_plain : 'New referral');

        $patient_esc = esc_html($patient_name_plain !== '' ? $patient_name_plain : 'Patient');
        $provider_esc = esc_html($provider_name);
        $reason_esc = esc_html($reason);

        $message = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="margin:0;padding:0;font-family:Arial,sans-serif;background:#f4f7fa;">'
            . '<table width="100%" cellpadding="0" cellspacing="0" style="padding:32px 16px;"><tr><td align="center">'
            . '<table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);">'
            . '<tr><td style="background:linear-gradient(135deg,#0A3D62 0%,#2980b9 100%);padding:28px 32px;text-align:center;">'
            . '<h1 style="color:#fff;margin:0;font-size:22px;">' . esc_html($site) . '</h1>'
            . '<p style="color:rgba(255,255,255,0.9);margin:8px 0 0;font-size:14px;">Referral confirmation</p></td></tr>'
            . '<tr><td style="padding:32px;">'
            . '<p style="color:#334155;font-size:16px;line-height:1.6;margin:0 0 16px;">Hello ' . $provider_esc . ',</p>'
            . '<p style="color:#334155;font-size:16px;line-height:1.6;margin:0 0 16px;">'
            . 'A referral was submitted through the provider portal for patient <strong>' . $patient_esc . '</strong>'
            . ($reason_esc !== '' ? ' (reason: <strong>' . $reason_esc . '</strong>).' : '.')
            . '</p>'
            . '<p style="color:#334155;font-size:16px;line-height:1.6;margin:0 0 24px;">'
            . 'Please click the button below to confirm you received this referral. This marks the referral as <strong>validated</strong> in the portal.</p>'
            . '<table width="100%" cellpadding="0" cellspacing="0"><tr><td align="center">'
            . '<a href="' . $link . '" style="display:inline-block;background:linear-gradient(135deg,#0A3D62 0%,#2980b9 100%);color:#fff;text-decoration:none;padding:14px 32px;border-radius:8px;font-size:16px;font-weight:600;">Confirm referral</a>'
            . '</td></tr></table>'
            . '<p style="color:#64748b;font-size:13px;line-height:1.5;margin:24px 0 0;">If the button does not work, copy and paste this link into your browser:<br><a href="' . $link . '" style="color:#2980b9;word-break:break-all;">' . $link . '</a></p>'
            . '</td></tr></table></td></tr></table></body></html>';

        $from_email = mmla_portal_outbound_from_email();
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: Mobile Medical LA Portal <' . $from_email . '>',
            'Reply-To: ' . $from_email,
        ];

        $sent = wp_mail($provider_email, $subject, $message, $headers);
        if (!$sent) {
            error_log('[Portal] Failed to send referral validation email to ' . $provider_email . ' for submission ' . (int) $submission_id);
        } else {
            error_log('[Portal] Referral validation email sent to ' . $provider_email . ' submission ' . (int) $submission_id);
        }
        return (bool) $sent;
    }
}

add_action('init', 'mmla_handle_referral_validation_request', 5);

function mmla_handle_referral_validation_request() {
    if (!isset($_GET['action']) || $_GET['action'] !== 'validate_referral') {
        return;
    }

    $sid = absint($_GET['sid'] ?? 0);
    $token = isset($_GET['token']) ? sanitize_text_field(wp_unslash((string) $_GET['token'])) : '';

    if ($sid < 1 || $token === '') {
        wp_safe_redirect(home_url('/?referral_error=invalid_link'));
        exit;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'referral_submissions';
    $hash = hash('sha256', $token);

    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT submission_id, is_validated, validation_token FROM `{$table}` WHERE submission_id = %d LIMIT 1",
            $sid
        )
    );

    if (!$row || empty($row->validation_token)) {
        error_log('[Portal] validate_referral: missing row or token for sid=' . $sid);
        wp_safe_redirect(home_url('/?referral_error=not_found'));
        exit;
    }

    if (!hash_equals((string) $row->validation_token, $hash)
        && !(strlen((string) $row->validation_token) === 36 && hash_equals((string) $row->validation_token, $token))
    ) {
        error_log('[Portal] validate_referral: token mismatch sid=' . $sid);
        wp_safe_redirect(home_url('/?referral_error=invalid_token'));
        exit;
    }

    if ((int) $row->is_validated === 1) {
        wp_safe_redirect(home_url('/?referral_msg=already_validated'));
        exit;
    }

    $sql = $wpdb->prepare(
        "UPDATE `{$table}` SET `is_validated` = 1, `validation_token` = NULL WHERE `submission_id` = %d AND `is_validated` = 0 AND (`validation_token` = %s OR `validation_token` = %s)",
        $sid,
        $hash,
        $token
    );
    $updated = $wpdb->query($sql);

    if ($updated === false) {
        error_log('[Portal] validate_referral: DB update failed sid=' . $sid);
        wp_safe_redirect(home_url('/?referral_error=update_failed'));
        exit;
    }

    if ((int) $updated === 0) {
        wp_safe_redirect(home_url('/?referral_msg=already_validated'));
        exit;
    }

    wp_safe_redirect(home_url('/?referral_validated=1'));
    exit;
}

/**
 * Resend verification email (AJAX handler)
 */
add_action('wp_ajax_resend_verification_email', 'handle_resend_verification_email');
add_action('wp_ajax_nopriv_resend_verification_email', 'handle_resend_verification_email');

function handle_resend_verification_email() {
    $email = sanitize_email($_POST['email'] ?? '');
    
    if (empty($email)) {
        wp_send_json_error('Please provide your email address');
        return;
    }
    
    // Find user by email
    $user = get_user_by('email', $email);
    if (!$user) {
        // Don't reveal if email exists or not for security
        wp_send_json_success(['message' => 'If an account exists with this email, a verification link will be sent.']);
        return;
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'portal_users';
    
    $portal_user = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE wp_user_id = %d",
        $user->ID
    ));
    
    if (!$portal_user) {
        wp_send_json_success(['message' => 'If an account exists with this email, a verification link will be sent.']);
        return;
    }
    
    // Check if already verified
    if ($portal_user->email_verified == 1) {
        wp_send_json_error('This email is already verified. Please login.');
        return;
    }
    
    // Send new verification email
    $sent = send_portal_verification_email($user->ID);
    
    if ($sent) {
        wp_send_json_success(['message' => 'Verification email sent! Please check your inbox.']);
    } else {
        wp_send_json_error('Failed to send verification email. Please try again later.');
    }
}

/**
 * Optional: Check email verification on login
 * Uncomment if you want to require verification before login
 */
/*
add_filter('authenticate', 'check_email_verified_on_login', 30, 3);

function check_email_verified_on_login($user, $username, $password) {
    if (is_wp_error($user)) {
        return $user;
    }
    
    if (!$user || !($user instanceof WP_User)) {
        return $user;
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'portal_users';
    
    $portal_user = $wpdb->get_row($wpdb->prepare(
        "SELECT email_verified FROM $table WHERE wp_user_id = %d",
        $user->ID
    ));
    
    if ($portal_user && $portal_user->email_verified != 1) {
        return new WP_Error(
            'email_not_verified',
            'Please verify your email before logging in. <a href="/portal/login/?resend=' . urlencode($user->user_email) . '">Resend verification email</a>'
        );
    }
    
    return $user;
}
*/


// ============================================
// 7. GET USER PROFILE (AJAX)
// ============================================
add_action('wp_ajax_get_user_profile', function() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Not authorized');
        return;
    }
    
    $user_id = get_current_user_id();
    $user = get_userdata($user_id);
    
    global $wpdb;
    $portal_user = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}portal_users WHERE wp_user_id = %d",
        $user_id
    ), ARRAY_A);
    
    wp_send_json_success([
        'personal' => [
            'first_name' => $portal_user['first_name'] ?? get_user_meta($user_id, 'first_name', true),
            'last_name'  => $portal_user['last_name'] ?? get_user_meta($user_id, 'last_name', true),
            'email'      => $user->user_email,
            'phone'      => $portal_user['phone'] ?? ''
        ],
        'professional' => [
            'practice'       => $portal_user['practice'] ?? '',
            'specialty'      => $portal_user['specialty'] ?? '',
            'license_number' => $portal_user['license_number'] ?? '',
            'address'        => $portal_user['address'] ?? '',
            'city'           => $portal_user['city'] ?? '',
            'state'          => $portal_user['state'] ?? '',
            'zip'            => $portal_user['zip'] ?? ''
        ]
    ]);
});

// ============================================
// 8. UPDATE USER PROFILE (AJAX)
// ============================================
add_action('wp_ajax_update_user_profile', function() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Not authorized');
        return;
    }
    
    $user_id = get_current_user_id();
    
    $data = [
        'first_name'     => sanitize_text_field($_POST['first_name'] ?? ''),
        'last_name'      => sanitize_text_field($_POST['last_name'] ?? ''),
        'phone'          => sanitize_text_field($_POST['phone'] ?? ''),
        'practice'       => sanitize_text_field($_POST['practice'] ?? ''),
        'specialty'      => sanitize_text_field($_POST['specialty'] ?? ''),
        'license_number' => sanitize_text_field($_POST['license_number'] ?? ''),
        'address'        => sanitize_text_field($_POST['address'] ?? ''),
        'city'           => sanitize_text_field($_POST['city'] ?? ''),
        'state'          => sanitize_text_field($_POST['state'] ?? ''),
        'zip'            => sanitize_text_field($_POST['zip'] ?? ''),
        'updated_at'     => current_time('mysql')
    ];

    error_log('[Portal] ' . print_r($data, true));
    
    global $wpdb;
    $wpdb->update($wpdb->prefix . 'portal_users', $data, ['wp_user_id' => $user_id]);
    
    // Also update WP user meta
    update_user_meta($user_id, 'first_name', $data['first_name']);
    update_user_meta($user_id, 'last_name', $data['last_name']);
    
    wp_send_json_success(['message' => 'Profile updated successfully']);
});

// ============================================
// 9. GET RESOURCES (AJAX) — fallback when functions-portal-auth-enhanced.php is absent
// ============================================
if (!has_action('wp_ajax_get_resources', 'get_resources_callback')) {
    add_action('wp_ajax_get_resources', function () {
        if (!is_user_logged_in()) {
            wp_send_json_error('Not authorized');
            return;
        }
        wp_send_json_success(mmla_portal_get_resources_normalized());
    });
}

// ============================================
// 10. GET REFERRALS (AJAX) — fallback when functions-portal-auth-enhanced.php is absent
// ============================================
if (!has_action('wp_ajax_get_referrals', 'get_referrals_callback')) {
    add_action('wp_ajax_get_referrals', function () {
        if (!is_user_logged_in()) {
            wp_send_json_error('Not authorized');
            return;
        }

        global $wpdb;
        $user_id = get_current_user_id();

        $portal_user = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}portal_users WHERE wp_user_id = %d",
            $user_id
        ));

        $referrals = $wpdb->get_results($wpdb->prepare(
            "SELECT submission_id, provider_name, provider_practice, provider_email, 
                reason, notes, created_at, is_validated
         FROM {$wpdb->prefix}referral_submissions 
         WHERE user_id = %d OR user_id IS NULL
         ORDER BY created_at DESC 
         LIMIT 50",
            $portal_user->id ?? 0
        ), ARRAY_A);

        foreach ($referrals as &$ref) {
            $ref['patient_name'] = 'Patient #' . $ref['submission_id'];
        }

        wp_send_json_success($referrals);
    });
}

// ============================================
// 11. SUBMIT REFERRAL (AJAX) — fallback when functions-portal-auth-enhanced.php is absent
// ============================================
if (!has_action('wp_ajax_submit_referral', 'submit_referral_callback')) {
    add_action('wp_ajax_submit_referral', function () {
        if (!is_user_logged_in()) {
            wp_send_json_error('Not authorized');
            return;
        }

        $allowed_reasons = [
            'General Consultation',
            'ENT consultation',
            'OMFS consultation',
            'Neurology referral',
            'Cardiology referral',
            'Other',
        ];
        $reason_raw = sanitize_text_field($_POST['reason'] ?? '');
        $reason = in_array($reason_raw, $allowed_reasons, true) ? $reason_raw : '';
        if ($reason === '') {
            wp_send_json_error('Invalid reason selected');
            return;
        }

        global $wpdb;
        $wp_user_id = get_current_user_id();
        $portal_user_id = null;
        if ($wp_user_id) {
            $portal_user = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}portal_users WHERE wp_user_id = %d LIMIT 1",
                    $wp_user_id
                )
            );
            $portal_user_id = $portal_user ? (int) $portal_user : null;
        }

        if (function_exists('mmla_referral_ensure_validation_token_column')) {
            mmla_referral_ensure_validation_token_column();
        }

        $raw_validation_token = wp_generate_password(48, false, false);
        $validation_token_hash = hash('sha256', $raw_validation_token);

        $result = $wpdb->insert($wpdb->prefix . 'referral_submissions', [
            'provider_name'     => sanitize_text_field($_POST['provider_name'] ?? ''),
            'provider_practice' => sanitize_text_field($_POST['provider_practice'] ?? ''),
            'provider_email'    => sanitize_email($_POST['provider_email'] ?? ''),
            'provider_phone'    => sanitize_text_field($_POST['provider_phone'] ?? ''),
            'patient_name'      => sanitize_text_field($_POST['patient_name'] ?? ''),
            'patient_email'     => sanitize_email($_POST['patient_email'] ?? ''),
            'reason'            => $reason,
            'notes'             => sanitize_textarea_field($_POST['notes'] ?? ''),
            'user_id'           => $portal_user_id,
            'created_at'        => current_time('mysql'),
            'validation_token'  => $validation_token_hash,
            'is_validated'      => 0,
        ]);

        if ($result) {
            $submission_id = (int) $wpdb->insert_id;
            $provider_email = sanitize_email($_POST['provider_email'] ?? '');
            if (function_exists('mmla_send_referral_provider_validation_email')) {
                mmla_send_referral_provider_validation_email(
                    $submission_id,
                    $provider_email,
                    sanitize_text_field($_POST['provider_name'] ?? ''),
                    sanitize_text_field($_POST['patient_name'] ?? ''),
                    $reason,
                    $raw_validation_token
                );
            }
            wp_send_json_success(['message' => 'Referral submitted successfully']);
        } else {
            wp_send_json_error('Failed to submit referral');
        }
    });
}

// ============================================
// 12. CONTACT FORM (AJAX)
// ============================================
add_action('wp_ajax_submit_contact', 'handle_contact_form');
add_action('wp_ajax_nopriv_submit_contact', 'handle_contact_form');

function handle_contact_form() {
    $name    = sanitize_text_field($_POST['name'] ?? '');
    $email   = sanitize_email($_POST['email'] ?? '');
    $subject = sanitize_text_field($_POST['subject'] ?? '');
    $message = sanitize_textarea_field($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($message)) {
        wp_send_json_error('Please fill in all required fields');
        return;
    }
    
    $to = get_option('admin_email');
    $email_subject = "[Portal] $subject";
    $body = "From: $name <$email>\n\n$message";
    
    if (wp_mail($to, $email_subject, $body, ["Reply-To: $email"])) {
        wp_send_json_success(['message' => 'Message sent successfully']);
    } else {
        wp_send_json_error('Failed to send message');
    }
}

// ============================================
// 13. DASHBOARD STATS (AJAX)
// ============================================
add_action('wp_ajax_get_dashboard_stats', function() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Not authorized');
        return;
    }
    
    global $wpdb;
    $user_id = get_current_user_id();
    
    $portal_user = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}portal_users WHERE wp_user_id = %d",
        $user_id
    ), ARRAY_A);
    
    $referral_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}referral_submissions WHERE user_id = %d",
        $portal_user['id'] ?? 0
    )) ?: 0;
    
    $resource_count = function_exists('mmla_portal_resources_count')
        ? mmla_portal_resources_count()
        : 0;
    
    wp_send_json_success([
        'user' => [
            'firstName'   => $portal_user['first_name'] ?? '',
            'lastName'    => $portal_user['last_name'] ?? '',
            'practice'    => $portal_user['practice'] ?? '',
            'lastLogin'   => $portal_user['last_login'] ?? null,
            'memberSince' => $portal_user['created_at'] ?? null
        ],
        'stats' => [
            'referrals' => (int) $referral_count,
            'resources' => (int) $resource_count
        ]
    ]);
});

// ============================================
// 14. LOGOUT REDIRECT
// ============================================
add_filter('logout_redirect', function() {
    return home_url('/portal-login/');
}, 10, 3);

// ============================================
// 15. LOGIN REDIRECT
// ============================================
add_filter('login_redirect', function($redirect, $request, $user) {
    if (is_a($user, 'WP_User')) {
        return home_url('/dashboard/');
    }
    return $redirect;
}, 10, 3);

// ============================================
// 16. HIDE ADMIN BAR FOR NON-ADMINS
// ============================================
add_action('after_setup_theme', function() {
    if (!current_user_can('manage_options')) {
        show_admin_bar(false);
    }
});

// ============================================
// 17. PARENT THEME STYLES (non-portal pages)
// ============================================
add_action('wp_enqueue_scripts', function() {
    if (is_portal_page()) return;
    
    wp_enqueue_style('blocksy-parent', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('blocksy-child', get_stylesheet_directory_uri() . '/style.css', ['blocksy-parent']);
});