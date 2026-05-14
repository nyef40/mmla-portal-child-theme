<?php
/**
 * Enhanced Portal Authentication Functions
 * Handles login, registration, and user management for the portal system
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Prevent multiple inclusions
if (defined('PORTAL_AUTH_ENHANCED_LOADED')) {
    return;
}
define('PORTAL_AUTH_ENHANCED_LOADED', true);

if (function_exists('portal_debug')) {
    portal_debug("REGISTRATION ATTEMPT", [
        'post_data' => $_POST,
        'files' => $_FILES,
        'user_logged_in' => is_user_logged_in(),
        'ajax' => wp_doing_ajax()
    ]);
}

/**
 * Handle AJAX login requests
 */
function handle_portal_login() {
    if (function_exists('portal_debug')) {
        portal_debug("LOGIN HANDLER - Started", $_POST);
    }
    if (!class_exists('PortalAuthService')) {
        wp_send_json_error('Authentication service unavailable');
        return;
    }
    if (!isset($_POST['nonce']) || !isset($_POST['username']) || !isset($_POST['password'])) {
        wp_send_json_error('Missing required data');
        return;
    }

    $nonce = (string) $_POST['nonce'];
    global $wpdb;
    $auth_service = new PortalAuthService($wpdb);
    if (!$auth_service->isLoginNonceValid($nonce)) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        wp_send_json_error('Security check failed');
        return;
    }

    $username = sanitize_text_field($_POST['username']);
    $password = (string) $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    if (empty($username) || empty($password)) {
        wp_send_json_error('Please enter both username and password');
        return;
    }

    $login_result = $auth_service->login($username, $password, $remember);
    if (!$login_result['ok']) {
        wp_send_json_error($login_result['message']);
        return;
    }
    $user = $login_result['user'];
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    wp_send_json_success([
        'message' => 'Login successful! Redirecting...',
        'redirect' => home_url('/dashboard/'),
        'user_id' => $user->ID,
        'user_name' => $user->display_name
    ]);
}

add_action('wp_ajax_nopriv_portal_login', 'handle_portal_login');
add_action('wp_ajax_portal_login', 'handle_portal_login');

if (defined('PORTAL_DEBUG') && PORTAL_DEBUG) {
    add_action('wp_verify_nonce_failed', function($nonce, $action, $user, $token) {
        error_log("Nonce verification failed: nonce=$nonce, action=$action, user_id=" . ($user ? $user->ID : 0) . ", token=$token");
    }, 10, 4);
}

/**
 * Registration: handled in functions.php (portal_process_registration + handle_portal_register).
 * That flow inserts into portal_users, sends verification email, and sets email_verified on link click.
 * No duplicate handler here so local and live use the same code path.
 */

/**
 * Handle contact form submissions
 */
function handle_contact_form_submission() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'contact_form_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }

    $first_name = sanitize_text_field($_POST['first_name'] ?? '');
    $last_name = sanitize_text_field($_POST['last_name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $subject = sanitize_text_field($_POST['subject'] ?? '');
    $message = sanitize_textarea_field($_POST['message'] ?? '');

    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($email) || empty($subject) || empty($message)) {
        wp_send_json_error('Please fill in all required fields');
        return;
    }

    // Validate email
    if (!is_email($email)) {
        wp_send_json_error('Please enter a valid email address');
        return;
    }

    // Store in database
    global $wpdb;
    $table_name = $wpdb->prefix . 'contact_submissions';
    
    // Create table if it doesn't exist
    $wpdb->query("CREATE TABLE IF NOT EXISTS {$table_name} (
        id int(11) NOT NULL AUTO_INCREMENT,
        first_name varchar(100) NOT NULL,
        last_name varchar(100) NOT NULL,
        email varchar(255) NOT NULL,
        phone varchar(20),
        subject varchar(255) NOT NULL,
        message text NOT NULL,
        submitted_at datetime DEFAULT CURRENT_TIMESTAMP,
        status varchar(20) DEFAULT 'new',
        PRIMARY KEY (id)
    )");

    $result = $wpdb->insert(
        $table_name,
        [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject,
            'message' => $message,
            'submitted_at' => current_time('mysql')
        ]
    );

    if ($result === false) {
        wp_send_json_error('Failed to save your message. Please try again.');
        return;
    }

    // Send email notification
    $admin_email = get_option('admin_email');
    $site_name = get_bloginfo('name');
    $from_addr = mmla_portal_outbound_from_email();

    // Email to admin
    $admin_subject = '[' . $site_name . '] New Contact Form Submission: ' . $subject;
    $admin_message = "New contact form submission received:\n\n";
    $admin_message .= "Name: {$first_name} {$last_name}\n";
    $admin_message .= "Email: {$email}\n";
    $admin_message .= "Phone: {$phone}\n";
    $admin_message .= "Subject: {$subject}\n\n";
    $admin_message .= "Message:\n{$message}\n\n";
    $admin_message .= "Submitted: " . current_time('mysql') . "\n";
    $admin_message .= "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n";

    $headers = [
        'From: ' . $site_name . ' <' . $from_addr . '>',
        'Reply-To: ' . $first_name . ' ' . $last_name . ' <' . $email . '>',
        'Content-Type: text/plain; charset=UTF-8'
    ];

    // Send to admin
    wp_mail($admin_email, $admin_subject, $admin_message, $headers);

    // Auto-reply to user
    $user_subject = 'Thank you for contacting ' . $site_name;
    $user_message = "Dear {$first_name},\n\n";
    $user_message .= "Thank you for contacting us. We have received your message regarding: {$subject}\n\n";
    $user_message .= "We will review your inquiry and get back to you within 24 hours.\n\n";
    $user_message .= "For urgent matters, please call us at (123) 456-7890.\n\n";
    $user_message .= "Best regards,\n";
    $user_message .= "Mobile Medical LA Team\n\n";
    $user_message .= "---\n";
    $user_message .= "Your message:\n{$message}";

    $user_headers = [
        'From: ' . $site_name . ' <' . $from_addr . '>',
        'Content-Type: text/plain; charset=UTF-8'
    ];

    wp_mail($email, $user_subject, $user_message, $user_headers);

    wp_send_json_success([
        'message' => 'Thank you for your message! We\'ll get back to you within 24 hours. A confirmation email has been sent to your email address.'
    ]);
}
add_action('wp_ajax_submit_contact_form', 'handle_contact_form_submission');
add_action('wp_ajax_nopriv_submit_contact_form', 'handle_contact_form_submission');

/**
 * Check if user has portal access
 */
function user_has_portal_access_enhanced($user_id = null) {
    portal_debug("PORTAL ACCESS CHECK - Entry", ['user_id' => $user_id]);
    
    global $wpdb;
    
    if (!$user_id) {
        $user_id = get_current_user_id();
        portal_debug("PORTAL ACCESS CHECK - Got current user", ['user_id' => $user_id]);
    }

    if (!$user_id) {
        portal_debug("PORTAL ACCESS CHECK - No user ID, returning false");
        return false;
    }

    // Check if user exists in portal_users table and is verified
    $portal_user_table = $wpdb->prefix . "portal_users";
    portal_debug("PORTAL ACCESS CHECK - Querying table", ['table' => $portal_user_table]);
    
    $portal_user = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$portal_user_table} WHERE wp_user_id = %d AND email_verified = 1",
        $user_id
    ));

    $result = !empty($portal_user);
    portal_debug("PORTAL ACCESS CHECK - Result", [
        'user_exists' => !empty($portal_user),
        'user_data' => $portal_user,
        'final_result' => $result
    ]);

    return $result;
}

/**
 * Get dashboard data via AJAX
 */
function fetch_dashboard_data_callback() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
        return;
    }

    $user_id = get_current_user_id();
    $user = get_userdata($user_id);

    $dashboard_data = [
        'user' => [
            'id' => $user_id,
            'name' => $user->first_name . ' ' . $user->last_name,
            'email' => $user->user_email,
            'last_login' => get_user_meta($user_id, 'last_login', true)
        ],
        'stats' => [
            'pending_referrals' => 2, // Mock data
            'resources_accessed' => 5,
            'last_resource' => 'Understanding HIPAA Compliance'
        ]
    ];

    wp_send_json_success($dashboard_data);
}
add_action('wp_ajax_fetch_dashboard_data', 'fetch_dashboard_data_callback');

/**
 * Get user profile data
 */
function get_user_profile_callback() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
        return;
    }

    $user_id = get_current_user_id();
    $user = get_userdata($user_id);

    $profile_data = [
        'personal' => [
            'first_name' => get_user_meta($user_id, 'first_name', true),
            'last_name' => get_user_meta($user_id, 'last_name', true),
            'email' => $user->user_email,
            'phone' => get_user_meta($user_id, 'phone', true)
        ],
        'professional' => [
            'practice_name' => get_user_meta($user_id, 'practice_name', true),
            'specialty' => get_user_meta($user_id, 'specialty', true),
            'license_number' => get_user_meta($user_id, 'license_number', true),
            'address' => get_user_meta($user_id, 'address', true),
            'city' => get_user_meta($user_id, 'city', true),
            'state' => get_user_meta($user_id, 'state', true),
            'zip' => get_user_meta($user_id, 'zip', true)
        ]
    ];

    wp_send_json_success($profile_data);
}
add_action('wp_ajax_get_user_profile', 'get_user_profile_callback');

/**
 * Update user profile
 */
function update_user_profile_callback() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
        return;
    }

    $user_id = get_current_user_id();

    // Update personal information
    if (isset($_POST['first_name'])) {
        update_user_meta($user_id, 'first_name', sanitize_text_field($_POST['first_name']));
    }
    if (isset($_POST['last_name'])) {
        update_user_meta($user_id, 'last_name', sanitize_text_field($_POST['last_name']));
    }
    if (isset($_POST['phone'])) {
        update_user_meta($user_id, 'phone', sanitize_text_field($_POST['phone']));
    }

    // Update professional information
    if (isset($_POST['practice_name'])) {
        update_user_meta($user_id, 'practice_name', sanitize_text_field($_POST['practice_name']));
    }
    if (isset($_POST['specialty'])) {
        update_user_meta($user_id, 'specialty', sanitize_text_field($_POST['specialty']));
    }
    if (isset($_POST['license_number'])) {
        update_user_meta($user_id, 'license_number', sanitize_text_field($_POST['license_number']));
    }
    if (isset($_POST['address'])) {
        update_user_meta($user_id, 'address', sanitize_text_field($_POST['address']));
    }
    if (isset($_POST['city'])) {
        update_user_meta($user_id, 'city', sanitize_text_field($_POST['city']));
    }
    if (isset($_POST['state'])) {
        update_user_meta($user_id, 'state', sanitize_text_field($_POST['state']));
    }
    if (isset($_POST['zip'])) {
        update_user_meta($user_id, 'zip', sanitize_text_field($_POST['zip']));
    }

    wp_send_json_success('Profile updated successfully');
}
add_action('wp_ajax_update_user_profile', 'update_user_profile_callback');

/**
 * Get referrals data
 */
function get_referrals_callback() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'referral_submissions';

    $referrals = $wpdb->get_results("
        SELECT submission_id, provider_name, provider_practice, provider_email,
               patient_name, reason, created_at, is_validated
        FROM {$table_name}
        ORDER BY created_at DESC
        LIMIT 20
    ");

    // Some legacy referrals were stored with MySQL AES_ENCRYPT(patient_name, 'mmla_2025').
    // Newer rows are plaintext. try_decrypt_field() handles both transparently:
    // decrypts encrypted bytes, passes plaintext through unchanged.
    foreach ($referrals as $ref) {
        if (function_exists('try_decrypt_field')) {
            if (isset($ref->patient_name))  $ref->patient_name  = try_decrypt_field($ref->patient_name);
            if (isset($ref->patient_email)) $ref->patient_email = try_decrypt_field($ref->patient_email);
        }
        // Cast tinyint to native int so JS sees `0` (falsy) instead of `"0"` (truthy).
        if (isset($ref->is_validated)) $ref->is_validated = (int) $ref->is_validated;
    }

    wp_send_json_success($referrals);
}
add_action('wp_ajax_get_referrals', 'get_referrals_callback');

/**
 * Submit new referral
 */
function submit_referral_callback() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'referral_submissions';

    $provider_name = sanitize_text_field($_POST['provider_name'] ?? '');
    $provider_practice = sanitize_text_field($_POST['provider_practice'] ?? '');
    $provider_email = sanitize_email($_POST['provider_email'] ?? '');
    $provider_phone = sanitize_text_field($_POST['provider_phone'] ?? '');
    $patient_name_plain  = sanitize_text_field($_POST['patient_name']  ?? '');
    $patient_email_plain = sanitize_email($_POST['patient_email'] ?? '');
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

    $notes = sanitize_textarea_field($_POST['notes'] ?? '');

    // Encrypt PHI fields at rest with MySQL-compatible AES-128 ECB.
    // Read path uses try_decrypt_field() to transparently decrypt these values.
    $patient_name  = ($patient_name_plain  !== '' && function_exists('mysql_aes_encrypt'))
        ? mysql_aes_encrypt($patient_name_plain)
        : $patient_name_plain;
    $patient_email = ($patient_email_plain !== '' && function_exists('mysql_aes_encrypt'))
        ? mysql_aes_encrypt($patient_email_plain)
        : $patient_email_plain;

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

    $result = $wpdb->insert(
        $table_name,
        [
            'provider_name' => $provider_name,
            'provider_practice' => $provider_practice,
            'provider_email' => $provider_email,
            'provider_phone' => $provider_phone,
            'patient_name' => $patient_name,
            'patient_email' => $patient_email,
            'reason' => $reason,
            'notes' => $notes,
            'user_id' => $portal_user_id,
            'created_at' => current_time('mysql'),
            'is_validated' => 0,
            'validation_token' => $validation_token_hash,
        ]
    );

    if ($result === false) {
        wp_send_json_error('Failed to submit referral');
        return;
    }

    $submission_id = (int) $wpdb->insert_id;

    if (function_exists('mmla_send_referral_provider_validation_email')) {
        mmla_send_referral_provider_validation_email(
            $submission_id,
            $provider_email,
            $provider_name,
            $patient_name_plain,
            $reason,
            $raw_validation_token
        );
    }

    wp_send_json_success('Referral submitted successfully');
}
add_action('wp_ajax_submit_referral', 'submit_referral_callback');

/**
 * Get resources data
 */
function get_resources_callback() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
        return;
    }
    $resources = function_exists('mmla_portal_get_resources_normalized')
        ? mmla_portal_get_resources_normalized()
        : [];
    wp_send_json_success($resources);
}
add_action('wp_ajax_get_resources', 'get_resources_callback');

/**
 * Log resource access
 */
function log_resource_access_callback() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
        return;
    }

    $resource_id = intval($_POST['resource_id'] ?? 0);
    $user_id = get_current_user_id();

    // Log the access (you could store this in a custom table)
    update_user_meta($user_id, 'last_resource_access', current_time('mysql'));
    update_user_meta($user_id, 'last_resource_id', $resource_id);

    wp_send_json_success('Access logged');
}
add_action('wp_ajax_log_resource_access', 'log_resource_access_callback');

/**
 * Configure WordPress mail settings for better email delivery
 */
function configure_wp_mail() {
    add_filter('wp_mail_content_type', function () {
        return 'text/html';
    });

    add_filter('wp_mail_from_name', function () {
        return mmla_portal_outbound_from_name();
    });

    add_filter('wp_mail_from', function () {
        return mmla_portal_outbound_from_email();
    });
}
add_action('init', 'configure_wp_mail');
