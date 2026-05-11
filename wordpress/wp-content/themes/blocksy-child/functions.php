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
 * Encryption key for referral data (AES). Use PORTAL_ENCRYPTION_KEY in wp-config to override.
 */
if (!function_exists('get_encryption_key')) {
    function get_encryption_key() {
        if (defined('PORTAL_ENCRYPTION_KEY') && PORTAL_ENCRYPTION_KEY !== '') {
            return PORTAL_ENCRYPTION_KEY;
        }
        if (defined('AUTH_KEY') && AUTH_KEY !== '') {
            return AUTH_KEY;
        }
        return 'portal-referral-key-16';
    }
}

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

add_filter('body_class', function($classes) {
    if (is_page(['portal-login', 'register', 'portal-profile', 'portal-resources', 'contact'])) {
        $classes[] = 'portal-single-view';
    }
    return $classes;
}, 999);

function is_portal_page() {
    global $post;
    if (is_admin() || !$post) return false;
    
    // Direct slugs
    $portal_slugs = [
        'portal', 'dashboard', 'portal-profile', 'portal-resources',
        'portal-referrals', 'portal-login', 'register', 'contact',
        'profile', 'resources', 'referrals', 'login'  // Added child page slugs
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
            
            #portal-root {
                min-height: calc(100vh - 70px);
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
        
        <div id="portal-root">
            <div class="portal-init-loading">
                <div class="spinner"></div>
                <h2>Loading Portal</h2>
                <p>Preparing your experience...</p>
            </div>
        </div>
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
    
    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: Mobile Medical LA Portal <nick.yefimov@mobilemedicalla.com>'
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
    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: Mobile Medical LA Portal <nick.yefimov@mobilemedicalla.com>'
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
// 9. GET RESOURCES (AJAX)
// ============================================
add_action('wp_ajax_get_resources', function() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Not authorized');
        return;
    }
    
    global $wpdb;
    $resources = $wpdb->get_results(
        "SELECT id, title, description, file_path, access_level, created_at 
         FROM {$wpdb->prefix}portal_resources 
         ORDER BY title",
        ARRAY_A
    );
    
    // Add category/type based on title or access_level
    foreach ($resources as &$r) {
        $r['category'] = $r['access_level'] ?: 'General';
        $r['type'] = strpos($r['file_path'] ?? '', '.pdf') !== false ? 'PDF' : 'Document';
        $r['url'] = $r['file_path'] ?: '#';
    }
    
    wp_send_json_success($resources);
});

// ============================================
// 10. GET REFERRALS (AJAX)
// ============================================
add_action('wp_ajax_get_referrals', function() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Not authorized');
        return;
    }
    
    global $wpdb;
    $user_id = get_current_user_id();
    
    // Get portal_user id
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
    
    // Decrypt patient_name for display (simplified - shows as "Patient")
    foreach ($referrals as &$ref) {
        $ref['patient_name'] = 'Patient #' . $ref['submission_id'];
    }
    
    wp_send_json_success($referrals);
});

// ============================================
// 11. SUBMIT REFERRAL (AJAX)
// ============================================
add_action('wp_ajax_submit_referral', function() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Not authorized');
        return;
    }
    
    global $wpdb;
    $user_id = get_current_user_id();
    
    // Get portal user id
    $portal_user = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}portal_users WHERE wp_user_id = %d",
        $user_id
    ));
    
    $result = $wpdb->insert($wpdb->prefix . 'referral_submissions', [
        'provider_name'     => sanitize_text_field($_POST['provider_name'] ?? ''),
        'provider_practice' => sanitize_text_field($_POST['provider_practice'] ?? ''),
        'provider_email'    => sanitize_email($_POST['provider_email'] ?? ''),
        'provider_phone'    => sanitize_text_field($_POST['provider_phone'] ?? ''),
        'patient_name'      => sanitize_text_field($_POST['patient_name'] ?? ''),
        'patient_email'     => sanitize_email($_POST['patient_email'] ?? ''),
        'reason'            => sanitize_text_field($_POST['reason'] ?? ''),
        'notes'             => sanitize_textarea_field($_POST['notes'] ?? ''),
        'user_id'           => $portal_user->id ?? null,
        'created_at'        => current_time('mysql'),
        'validation_token'  => wp_generate_uuid4(),
        'is_validated'      => 0
    ]);
    
    if ($result) {
        wp_send_json_success(['message' => 'Referral submitted successfully']);
    } else {
        wp_send_json_error('Failed to submit referral');
    }
});

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
    
    $resource_count = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->prefix}portal_resources"
    ) ?: 0;
    
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