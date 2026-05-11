<?php
/*
Plugin Name: MMLA Portal
Description: Custom portal for Mobile Medical LA
Version: 1.0
Author: Nikolay Yefimov
*/

// Load .env manually
// $dotenv = parse_ini_file(__DIR__ . '/../../.env');

// Include auth functions
require_once __DIR__ . '/includes/auth.php';

function mmla_portal_handle_logout() {
    if (isset($_GET['mmla_logout']) && $_GET['mmla_logout'] == 1) {

        // In mmla-portal.php, before wp_logout()
        error_log("mmla-portal: wp_logout() called, REQUEST_URI: " . $_SERVER['REQUEST_URI'] . ", is_user_logged_in: " . (is_user_logged_in() ? 'true' : 'false'));
        // wp_logout(); // Clear WP session
        wp_redirect('/portal/'); // Redirect to portal home
        exit;
    }
}
add_action('init', 'mmla_portal_handle_logout');

function mmla_portal_preserve_session() {
    $path = $_SERVER['REQUEST_URI'];
    if (is_user_logged_in() && strpos($path, '/portal') === 0) {
        $user_id = get_current_user_id();
        wp_set_auth_cookie($user_id, true);
        error_log("Session refreshed for user: $user_id on $path");
    } else {
        error_log("No session refresh: not logged in or not on portal - $path");
    }
}
add_action('wp', 'mmla_portal_preserve_session');

function mmla_portal_init() {
    add_rewrite_rule('^portal/([^/]*)/?', 'index.php?mmla_portal_page=$matches[1]', 'top');
    add_rewrite_rule('^portal/?$', 'index.php?mmla_portal_page=home', 'top');
}
add_action('init', 'mmla_portal_init');

function mmla_portal_query_vars($vars) {
    $vars[] = 'mmla_portal_page';
    return $vars;
}
add_filter('query_vars', 'mmla_portal_query_vars');

// Template filter
function mmla_portal_template($template) {
    $path = $_SERVER['REQUEST_URI'];
    $query_var = get_query_var('mmla_portal_page');
    error_log('Request URI: ' . $path . ' | Query Var: ' . ($query_var ?: 'not set'));
    if (strpos($path, '/portal') === 0 || $query_var) {
        $page = $query_var ?: ($path === '/portal/' || $path === '/portal' ? 'home' : 'unknown');
        error_log('Loading template for: ' . $page);
        $template_path = plugin_dir_path(__FILE__) . "pages/{$page}.php";
        if (file_exists($template_path)) {
            // Force assets if home
            if ($page === 'home') {
                wp_enqueue_script('mmla-portal-js', plugins_url('assets/portal.js', __FILE__), [], '1.0', true);
                wp_enqueue_style('mmla-portal-css', plugins_url('assets/portal.css', __FILE__), [], '1.0');
                $data = [
                    'isLoggedIn' => is_user_logged_in(),
                ];
                if (is_user_logged_in()) {
                    $user = wp_get_current_user();
                    $data['currentUser'] = [
                        'username' => $user->user_login,
                        'email' => $user->user_email,
                    ];
                }
                wp_add_inline_script('mmla-portal-js', 'window.MmlaPortal = ' . json_encode($data) . ';', 'before');
                add_action('wp_head', function() {
                    echo '<style>#header-menu-1, .header-menu-1, nav.header-menu-1 { display: none !important; }</style>';
                    echo '<script>document.addEventListener("DOMContentLoaded", () => { document.querySelector("#header-menu-1").style.display = "none"; });</script>';
                }, 10);
            }
            return $template_path;
        } else {
            error_log('Template not found: ' . $template_path);
        }
    }
    return $template;
}
add_filter('template_include', 'mmla_portal_template');

function mmla_portal_enqueue_assets() {
    $query_var = get_query_var('mmla_portal_page');
    $path = $_SERVER['REQUEST_URI'];
    error_log("Checking enqueue for URI: $path | Query Var: " . ($query_var ?: 'not set'));
    if ($query_var || strpos($path, '/portal') === 0) {
        error_log("Enqueuing assets for URI: $path");
        wp_enqueue_script('mmla-portal-js', plugins_url('assets/portal.js', __FILE__), [], '1.0', true);
        wp_enqueue_style('mmla-portal-css', plugins_url('assets/portal.css', __FILE__), [], '1.0');
        $data = [
            'isLoggedIn' => is_user_logged_in(),
        ];
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $data['currentUser'] = [
                'username' => $user->user_login,
                'email' => $user->user_email,
            ];
        }
        wp_add_inline_script('mmla-portal-js', 'window.MmlaPortal = ' . json_encode($data) . ';', 'before');
        error_log("Set window.MmlaPortal: " . json_encode($data));
    } else {
        error_log("Assets not enqueued: $path not a portal page");
    }
}
add_action('wp_enqueue_scripts', 'mmla_portal_enqueue_assets', 10);

function mmla_portal_hide_main_nav() {
    $query_var = get_query_var('mmla_portal_page');
    $path = $_SERVER['REQUEST_URI'];
    error_log('Checking hide nav for URI: ' . $path . ' | Query Var: ' . ($query_var ?: 'not set'));
    if ($query_var || strpos($path, '/portal') === 0) {
        error_log('Hiding main nav for URI: ' . $path);
        echo '<style>#header-menu-1, .header-menu-1, nav.header-menu-1 { display: none !important; }</style>';
        echo '<script>document.addEventListener("DOMContentLoaded", () => { document.querySelector("#header-menu-1").style.display = "none"; });</script>';
    }
}
add_action('wp_head', 'mmla_portal_hide_main_nav', 10);

function mmla_portal_add_back_button() {
    $path = $_SERVER['REQUEST_URI'];
    $query_var = get_query_var('mmla_portal_page');
    error_log("Checking back button for URI: $path | Query Var: " . ($query_var ?: 'not set'));
    if ($query_var || strpos($path, '/portal') === 0) {
        echo '<style>
            .back-to-main {
                position: fixed;
                top: 50px;
                right: 10px;
                background: #0073aa;
                color: white;
                padding: 8px 16px;
                border-radius: 4px;
                text-decoration: none;
                font-family: Arial, sans-serif;
                font-size: 14px;
                z-index: 1000;
            }
            .back-to-main:hover {
                background: #005d82;
            }
        </style>';
        echo '<a href="/" class="back-to-main">Back to Main Site</a>';
        error_log("Back button added for URI: $path");
    }
}
add_action('wp_footer', 'mmla_portal_add_back_button', 100);

function mmla_portal_hide_admin_bar() {
    if (!current_user_can('manage_options')) { // Only admins see it
        add_filter('show_admin_bar', '__return_false');
    }
}
add_action('after_setup_theme', 'mmla_portal_hide_admin_bar');

// REST API Endpoints
function mmla_portal_register_rest_routes() {
    // Register user
    register_rest_route('mmla/v1', '/register', [
        'methods' => 'POST',
        'callback' => 'mmla_portal_handle_register',
        'permission_callback' => '__return_true', // Public for now
    ]);

    // Login user
    register_rest_route('mmla/v1', '/login', [
        'methods' => 'POST',
        'callback' => 'mmla_portal_handle_login',
        'permission_callback' => '__return_true',
    ]);

    // Verify email
    register_rest_route('mmla/v1', '/verify', [
        'methods' => 'GET',
        'callback' => 'mmla_portal_handle_verify',
        'permission_callback' => '__return_true',
    ]);
}
add_action('rest_api_init', 'mmla_portal_register_rest_routes');

function mmla_portal_handle_register($request) {
    $params = $request->get_params();
    $data = [
        'username' => sanitize_user($params['username']),
        'email' => sanitize_email($params['email']),
        'password' => $params['password'],
        'specialty' => sanitize_text_field($params['specialty']),
        'license_number' => sanitize_text_field($params['license_number']),
    ];

    if (username_exists($data['username'])) {
        return new WP_Error('username_exists', 'Username already taken', ['status' => 400]);
    }
    if (email_exists($data['email'])) {
        return new WP_Error('email_exists', 'Email already registered', ['status' => 400]);
    }

    $user_id = mmla_portal_register_user($data);
    if (is_wp_error($user_id)) {
        return new WP_Error('registration_failed', $user_id->get_error_message(), ['status' => 400]);
    }

    $token = bin2hex(random_bytes(16));
    update_user_meta($user_id, 'verification_token', $token);

    $verify_url = site_url("/wp-json/mmla/v1/verify?token=$token&user_id=$user_id");
    $subject = 'Verify Your Mobile Medical LA Account';
    $message = "Click here to verify your email: $verify_url";
    wp_mail($data['email'], $subject, $message);

    return ['message' => 'Registration successful. Check your email to verify.', 'user_id' => $user_id];
}

function mmla_portal_handle_login($request) {
    $params = $request->get_params();
    $username = sanitize_user($params['username']);
    $password = $params['password'];

    $logged_in = mmla_portal_login($username, $password);
    if (!$logged_in) {
        return new WP_Error('login_failed', 'Invalid credentials', ['status' => 401]);
    }

    global $wpdb;
    $user = get_user_by('login', $username);
    $verified = $wpdb->get_var($wpdb->prepare(
        "SELECT verified FROM {$wpdb->prefix}portal_users WHERE wp_user_id = %d",
        $user->ID
    ));

    if (!$verified) {
        return new WP_Error('unverified', 'Please verify your email first', ['status' => 403]);
    }

    return ['message' => 'Login successful', 'user_id' => $user->ID];
}

function mmla_portal_handle_verify($request) {
    $token = sanitize_text_field($request['token']);
    $user_id = absint($request['user_id']);

    $stored_token = get_user_meta($user_id, 'verification_token', true);
    if ($token === $stored_token) {
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'portal_users',
            ['verified' => true],
            ['wp_user_id' => $user_id]
        );
        delete_user_meta($user_id, 'verification_token');
        wp_redirect('/portal/login?verified=1');
        exit;
    }
    return new WP_Error('invalid_token', 'Invalid or expired token', ['status' => 400]);
}
