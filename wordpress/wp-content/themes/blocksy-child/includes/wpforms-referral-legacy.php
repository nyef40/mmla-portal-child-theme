<?php
if (!defined("ABSPATH")) { exit; }

if (!function_exists("mmla_wpforms_referral_form_id")) {
    function mmla_wpforms_referral_form_id() {
        return (int) apply_filters("mmla_wpforms_referral_form_id", 1479);
    }
}

if (!function_exists('wpforms_smart_tags')) {
    function wpforms_smart_tags($tags) {
        $tags['validation_url'] = 'Validation URL';
        return $tags;
    }
    add_filter('wpforms_smart_tags', 'wpforms_smart_tags');
}

/**
 * Get HTML email template with styled validation button
 */
function get_provider_email_template($provider_name, $validation_url) {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Email Validation - Mobile Medical LA</title>
    </head>
    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;">
        <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <div style="text-align: center; margin-bottom: 30px;">
                    <h1 style="color: #0066cc; margin: 0; font-size: 24px;">Mobile Medical LA</h1>
                </div>
                
                <h2 style="color: #333; margin-top: 0;">Email Validation Required</h2>
                
                <p>Dear ' . esc_html($provider_name) . ',</p>
                
                <p>Thank you for referring a patient to Mobile Medical LA. Please validate your email address by clicking the button below:</p>
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . esc_url($validation_url) . '" style="display: inline-block; background-color: #0066cc; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px; border: none; cursor: pointer;">Validate Email Address</a>
                </div>
                
                <p>If the button above doesn\'t work, you can copy and paste this link into your browser:</p>
                <p style="word-break: break-all; color: #666; background: #f9f9f9; padding: 10px; border-radius: 4px; font-size: 14px;">' . esc_url($validation_url) . '</p>
                
                <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 14px;">
                    <p>Best regards,<br>
                    <strong>Mobile Medical LA Team</strong></p>
                </div>
            </div>
        </div>
    </body>
    </html>';
}

/**
 * Helper function to get validation URL
 */
function get_validation_url($entry_id) {
    global $wpdb;
    
    // Try transient first
    $transient_key = 'wpforms_validation_url_' . $entry_id;
    $validation_url = get_transient($transient_key);
    // error_log("Transient lookup for entry_id $entry_id: " . ($validation_url ? "Found" : "Not found"), 3, WP_CONTENT_DIR . '/debug.log');
    
    if ($validation_url) {
        return $validation_url;
    }
    
    // Try option next
    $validation_url = get_option('wpforms_validation_url_' . $entry_id);
    // error_log("Option lookup for entry_id $entry_id: " . ($validation_url ? "Found" : "Not found"), 3, WP_CONTENT_DIR . '/debug.log');
    
    if ($validation_url) {
        return $validation_url;
    }
    
    // Try direct database lookup
    try {
        // Get provider email from entry
        $provider_email = $wpdb->get_var($wpdb->prepare(
            "SELECT value FROM {$wpdb->prefix}wpforms_entry_fields 
             WHERE entry_id = %d AND field_id = 15",
            $entry_id
        ));
        
        if ($provider_email) {
            // error_log("Found provider email for entry $entry_id: $provider_email", 3, WP_CONTENT_DIR . '/debug.log');
            
            // Use direct query with BINARY to force case-sensitive comparison
            $submission = $wpdb->get_row($wpdb->prepare(
                "SELECT submission_id, validation_token 
                 FROM {$wpdb->prefix}referral_submissions 
                 WHERE provider_email = %s 
                 ORDER BY submission_id DESC LIMIT 1",
                $provider_email
            ));
            
            if ($submission) {
                $validation_url = add_query_arg(
                    array(
                        'action' => 'validate_email',
                        'token' => $submission->validation_token,
                        'submission_id' => $submission->submission_id
                    ),
                    home_url()
                );
                
                // Save for future use
                set_transient($transient_key, $validation_url, 14 * DAY_IN_SECONDS);
                update_option('wpforms_validation_url_' . $entry_id, $validation_url, false);
                
                // error_log("Created validation URL from submission: $validation_url", 3, WP_CONTENT_DIR . '/debug.log');
                return $validation_url;
            }
        }
    } catch (Exception $e) {
        error_log("Error in get_validation_url: " . $e->getMessage(), 3, WP_CONTENT_DIR . '/debug.log');
    }
    
    // Last resort - get the most recent submission
    try {
        $submission = $wpdb->get_row(
            "SELECT submission_id, validation_token 
             FROM {$wpdb->prefix}referral_submissions 
             ORDER BY submission_id DESC LIMIT 1"
        );
        
        if ($submission) {
            $validation_url = add_query_arg(
                array(
                    'action' => 'validate_email',
                    'token' => $submission->validation_token,
                    'submission_id' => $submission->submission_id
                ),
                home_url()
            );
            
            // error_log("Last resort validation URL from most recent submission: $validation_url", 3, WP_CONTENT_DIR . '/debug.log');
            return $validation_url;
        }
    } catch (Exception $e) {
        error_log("Error in get_validation_url last resort: " . $e->getMessage(), 3, WP_CONTENT_DIR . '/debug.log');
    }
    
    return false;
}

/**
 * Process validation_url smart tag with duplication prevention
 */
function wpforms_smart_tag_process_validation_url($content, $tag, $form_data, $fields, $entry_id) {
    error_log("Smart tag process called for entry_id: $entry_id", 3, WP_CONTENT_DIR . '/debug.log');

    if (empty($entry_id)) {
        error_log("Smart tag: No entry_id provided", 3, WP_CONTENT_DIR . '/debug.log');
        return $content;
    }

    // Check if this content already has been processed
    if (strpos($content, 'Validate Email Address') !== false) {
        error_log("Smart tag: Content already processed, skipping", 3, WP_CONTENT_DIR . '/debug.log');
        return $content;
    }

    $validation_url = get_validation_url($entry_id);
    if ($validation_url) {
        // Get provider name for personalized email
        global $wpdb;
        $provider_name = $wpdb->get_var($wpdb->prepare(
            "SELECT value FROM {$wpdb->prefix}wpforms_entry_fields 
             WHERE entry_id = %d AND field_id = 2",
            $entry_id
        ));
        
        if (!$provider_name) {
            $provider_name = 'Doctor';
        }
        
        // Return complete HTML email template
        $html_template = get_provider_email_template($provider_name, $validation_url);
        error_log("Smart tag replaced with HTML template", 3, WP_CONTENT_DIR . '/debug.log');
        return $html_template;
    }

    error_log("Smart tag: No validation_url found for entry_id: $entry_id", 3, WP_CONTENT_DIR . '/debug.log');
    return $content;
}
add_filter('wpforms_smart_tag_process_validation_url', 'wpforms_smart_tag_process_validation_url', 10, 5);

/**
 * Modify email content to include validation URL
 */
function modify_email_content($message, $notification = null, $form_data = null, $fields = null, $entry_id = null) {
    global $wpdb;
    // error_log("Email modification called with " . count(func_get_args()) . " parameters", 3, WP_CONTENT_DIR . '/debug.log');
    
    // Skip if no validation_url placeholder
    if (empty($message) || strpos($message, '{validation_url}') === false) {
        return $message;
    }
    
    // Try to get the entry ID from parameters
    if (is_array($notification) && isset($notification['entry_id'])) {
        $entry_id = absint($notification['entry_id']);
        error_log("Got entry_id from notification: $entry_id", 3, WP_CONTENT_DIR . '/debug.log');
    } elseif (empty($entry_id) && isset($GLOBALS['wpforms_process']) && !empty($GLOBALS['wpforms_process']->entry_id)) {
        $entry_id = $GLOBALS['wpforms_process']->entry_id;
        error_log("Got entry_id from global process: $entry_id", 3, WP_CONTENT_DIR . '/debug.log');
    } elseif (empty($entry_id)) {
        $entry_id = $wpdb->get_var("SELECT MAX(entry_id) FROM {$wpdb->prefix}wpforms_entries");
        error_log("Got most recent entry_id: $entry_id", 3, WP_CONTENT_DIR . '/debug.log');
    }
    
    if ($entry_id) {
        $validation_url = get_validation_url($entry_id);
        if ($validation_url) {
            // Get provider name for personalized email
            $provider_name = $wpdb->get_var($wpdb->prepare(
                "SELECT value FROM {$wpdb->prefix}wpforms_entry_fields 
                 WHERE entry_id = %d AND field_id = 2",
                $entry_id
            ));
            
            if (!$provider_name) {
                $provider_name = 'Doctor';
            }
            
            // Replace with complete HTML template
            $html_template = get_provider_email_template($provider_name, $validation_url);
            $message = str_replace('{validation_url}', $html_template, $message);
            error_log("Email modified with HTML template", 3, WP_CONTENT_DIR . '/debug.log');
        } else {
            error_log("No validation URL found for entry $entry_id", 3, WP_CONTENT_DIR . '/debug.log');
        }
    } else {
        error_log("No entry ID found for email notification", 3, WP_CONTENT_DIR . '/debug.log');
    }
    
    return $message;
}
add_filter('wpforms_emails_notifications_message', 'modify_email_content', 20, 5);
add_filter('wpforms_email_message', 'modify_email_content', 20, 5);

/**
 * Enhanced last chance email modification with email type detection
 */
function last_chance_email_modification($args) {
    global $wpdb;
    // error_log("Last chance email modification", 3, WP_CONTENT_DIR . '/debug.log');

    if (!isset($args['message']) || strpos($args['message'], '{validation_url}') === false) {
        return $args;
    }

    // Get recipient email
    $to = isset($args['to']) ? $args['to'] : '';
    if (is_array($to)) {
        $to = reset($to);
    }
    
    // error_log("Email recipient: $to", 3, WP_CONTENT_DIR . '/debug.log');
    
    // Check if this is an admin email (should not have validation links)
    $admin_emails = [
        'nick.yefimov@mobilemedicalla.com',
        'nyef40@gmail.com',
        get_option('admin_email')
    ];
    
    if (in_array($to, $admin_emails)) {
        // error_log("Admin email detected - removing validation URL", 3, WP_CONTENT_DIR . '/debug.log');
        // For admin emails, just remove the validation URL placeholder
        $args['message'] = str_replace('{validation_url}', '', $args['message']);
        return $args;
    }
    
    // This is a provider email - add validation URL
    // error_log("Provider email detected - adding validation URL", 3, WP_CONTENT_DIR . '/debug.log');
    
    if (!empty($to)) {
        // Try to find the entry ID based on the recipient email
        $entry_id = $wpdb->get_var($wpdb->prepare(
            "SELECT entry_id FROM {$wpdb->prefix}wpforms_entry_fields 
             WHERE field_id = 15 AND value = %s 
             ORDER BY id DESC LIMIT 1",
            $to
        ));
        
        if ($entry_id) {
            // error_log("Found entry ID $entry_id for email $to", 3, WP_CONTENT_DIR . '/debug.log');
            $validation_url = get_validation_url($entry_id);
            
            if ($validation_url) {
                // Get provider name
                $provider_name = $wpdb->get_var($wpdb->prepare(
                    "SELECT value FROM {$wpdb->prefix}wpforms_entry_fields 
                     WHERE entry_id = %d AND field_id = 2",
                    $entry_id
                ));
                
                if (!$provider_name) {
                    $provider_name = 'Doctor';
                }
                
                // Replace with HTML template
                $html_template = get_provider_email_template($provider_name, $validation_url);
                $args['message'] = str_replace('{validation_url}', $html_template, $args['message']);
                // error_log("Provider email: validation URL added", 3, WP_CONTENT_DIR . '/debug.log');
            }
        }
    }

    return $args;
}
add_filter('wp_mail', 'last_chance_email_modification', 999);

/**
 * Force WPForms to send HTML emails
 */
function force_wpforms_html_email($args) {
    $args['headers'] = array('Content-Type: text/html; charset=UTF-8');
    return $args;
}
add_filter('wpforms_email_send_args', 'force_wpforms_html_email', 10, 1);

/**
 * Send fallback admin notification
 */
function send_fallback_admin_notification($fields, $entry, $form_data, $entry_id) {
    if ((int) $form_data['id'] !== mmla_wpforms_referral_form_id()) {
        return;
    }
    
    // Get form field values
    $provider_name = isset($fields[2]['value']) ? sanitize_text_field($fields[2]['value']) : '';
    $provider_practice = isset($fields[14]['value']) ? sanitize_text_field($fields[14]['value']) : '';
    $provider_email = isset($fields[15]['value']) ? sanitize_email($fields[15]['value']) : '';
    $provider_phone = isset($fields[16]['value']) ? sanitize_text_field($fields[16]['value']) : '';
    $patient_name = isset($fields[18]['value']) ? sanitize_text_field($fields[18]['value']) : '';
    $reason = isset($fields[23]['value']) ? sanitize_text_field($fields[23]['value']) : '';
    
    // Store the notification in the database instead of sending email
    global $wpdb;
    $table_name = $wpdb->prefix . 'admin_notifications';
    
    // Create the table if it doesn't exist
    $wpdb->query("
        CREATE TABLE IF NOT EXISTS {$table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            provider_name VARCHAR(255),
            provider_email VARCHAR(255),
            patient_name VARCHAR(255),
            reason VARCHAR(255),
            submission_id INT,
            created_at DATETIME,
            is_read TINYINT DEFAULT 0
        )
    ");
    
    // Get the most recent submission ID
    $submission_id = $wpdb->get_var("SELECT MAX(submission_id) FROM {$wpdb->prefix}referral_submissions");
    
    // Insert the notification
    $wpdb->insert(
        $table_name,
        array(
            'provider_name' => $provider_name,
            'provider_email' => $provider_email,
            'patient_name' => $patient_name,
            'reason' => $reason,
            'submission_id' => $submission_id,
            'created_at' => current_time('mysql'),
            'is_read' => 0
        )
    );
    
    error_log("Admin notification stored in database for submission ID: $submission_id", 3, WP_CONTENT_DIR . '/debug.log');
    
    // Try sending via alternative method
    try {
        // HTML version
        $html_message = "<!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                h1 { color: #0073aa; }
                .details { background: #f9f9f9; padding: 15px; border-left: 4px solid #0073aa; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h1>New Patient Referral</h1>
                <div class='details'>
                    <p><strong>Provider:</strong> {$provider_name}</p>
                    <p><strong>Practice:</strong> {$provider_practice}</p>
                    <p><strong>Email:</strong> {$provider_email}</p>
                    <p><strong>Phone:</strong> {$provider_phone}</p>
                    <p><strong>Patient:</strong> {$patient_name}</p>
                    <p><strong>Reason:</strong> {$reason}</p>
                </div>
                <p>Please log in to the admin dashboard to view full details.</p>
            </div>
        </body>
        </html>";
        
        // Try using PHP's mail function directly as a last resort
        $subject = 'New Patient Referral';
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: Mobile Medical LA <nyef40@gmail.com>',
        ];
        
        // Only use this in development - not recommended for production
        if (function_exists('mail')) {
            $mail_result = mail('nick.yefimov@mobilemedicalla.com', $subject, $html_message, implode("\r\n", $headers));
            error_log("PHP mail() function result: " . ($mail_result ? 'Success' : 'Failed'), 3, WP_CONTENT_DIR . '/debug.log');
        }
    } catch (Exception $e) {
        error_log("Error sending alternative admin notification: " . $e->getMessage(), 3, WP_CONTENT_DIR . '/debug.log');
    }
}

/**
 * Handle referral form submission
 */
function handle_referral_submission($fields, $entry, $form_data) {
    // error_log("Form submission: Form ID = " . $form_data['id'], 3, WP_CONTENT_DIR . '/debug.log');
    // error_log("Fields received: " . print_r(array_keys($fields), true), 3, WP_CONTENT_DIR . '/debug.log');

    if ((int) $form_data['id'] !== mmla_wpforms_referral_form_id()) {
        error_log("Not the referral form (ID " . $form_data['id'] . "), skipping", 3, WP_CONTENT_DIR . '/debug.log');
        return $fields;
    }

    // Get entry ID if available
    $entry_id = isset($entry['id']) ? absint($entry['id']) : 0;
    if (!$entry_id && isset($GLOBALS['wpforms_process']) && !empty($GLOBALS['wpforms_process']->entry_id)) {
        $entry_id = $GLOBALS['wpforms_process']->entry_id;
    }
    // error_log("Entry ID for referral: " . ($entry_id ? $entry_id : "Not available"), 3, WP_CONTENT_DIR . '/debug.log');

    static $processed_entries = [];
    if ($entry_id && in_array($entry_id, $processed_entries)) {
        error_log("Duplicate processing attempt for Entry ID = $entry_id, skipping", 3, WP_CONTENT_DIR . '/debug.log');
        return $fields;
    }
    if ($entry_id) {
        $processed_entries[] = $entry_id;
    }

    try {
        global $wpdb;

        $provider_name = isset($fields[2]['value']) ? sanitize_text_field($fields[2]['value']) : '';
        $provider_practice = isset($fields[14]['value']) ? sanitize_text_field($fields[14]['value']) : '';
        $provider_email = isset($fields[15]['value']) ? sanitize_email($fields[15]['value']) : '';
        $provider_phone = isset($fields[16]['value']) ? sanitize_text_field($fields[16]['value']) : '';
        $patient_name = isset($fields[18]['value']) ? sanitize_text_field($fields[18]['value']) : '';
        $patient_email = isset($fields[19]['value']) ? sanitize_email($fields[19]['value']) : '';
        $patient_phone = isset($fields[20]['value']) ? sanitize_text_field($fields[20]['value']) : '';
        $insurance = isset($fields[21]['value']) ? sanitize_text_field($fields[21]['value']) : '';
        $reason = isset($fields[23]['value']) ? sanitize_text_field($fields[23]['value']) : '';
        $notes = isset($fields[24]['value']) ? sanitize_textarea_field($fields[24]['value']) : '';

        // error_log("Provider Name: $provider_name, Email: $provider_email", 3, WP_CONTENT_DIR . '/debug.log');

        $validation_token = wp_generate_uuid4();
        // error_log("Generated validation token: $validation_token", 3, WP_CONTENT_DIR . '/debug.log');

        $encryption_key = get_encryption_key();
        $table_name = $wpdb->prefix . 'referral_submissions';

        // Fix collation issues by using direct SQL without encryption first
        $result = $wpdb->query($wpdb->prepare(
            "INSERT INTO {$table_name} 
             (provider_name, provider_practice, provider_email, provider_phone, 
              patient_name, patient_email, patient_phone, insurance, 
              reason, notes, validation_token, is_validated, created_at)
             VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %s)",
            $provider_name, $provider_practice, $provider_email, $provider_phone,
            $patient_name, $patient_email, $patient_phone, $insurance,
            $reason, $notes, $validation_token, 0, current_time('mysql')
        ));

        if ($result === false) {
            error_log("Database insertion failed: " . $wpdb->last_error, 3, WP_CONTENT_DIR . '/debug.log');
            return $fields;
        }

        $submission_id = $wpdb->insert_id;
        error_log("Database insertion successful: $submission_id", 3, WP_CONTENT_DIR . '/debug.log');

        // Now update with encryption
        $wpdb->query($wpdb->prepare(
            "UPDATE {$table_name} SET 
             patient_name = AES_ENCRYPT(%s, %s),
             patient_email = AES_ENCRYPT(%s, %s),
             insurance = AES_ENCRYPT(%s, %s)
             WHERE submission_id = %d",
            $patient_name, $encryption_key,
            $patient_email, $encryption_key,
            $insurance, $encryption_key,
            $submission_id
        ));

        $validation_url = add_query_arg(
            array(
                'action' => 'validate_email',
                'token' => $validation_token,
                'submission_id' => $submission_id
            ),
            home_url()
        );

        if ($entry_id) {
            $transient_key = 'wpforms_validation_url_' . $entry_id;
            $transient_set = set_transient($transient_key, $validation_url, 14 * DAY_IN_SECONDS);
            // error_log("Transient set result: " . ($transient_set ? "Success" : "Failed"), 3, WP_CONTENT_DIR . '/debug.log');
            $check_transient = get_transient($transient_key);
            // error_log("Immediate transient check: " . ($check_transient ? $check_transient : "Not found"), 3, WP_CONTENT_DIR . '/debug.log');

            update_option('wpforms_validation_url_' . $entry_id, $validation_url, true);
            $check_option = get_option('wpforms_validation_url_' . $entry_id);
            // error_log("Immediate option check: " . ($check_option ? $check_option : "Not found"), 3, WP_CONTENT_DIR . '/debug.log');
        } else {
            // error_log("No entry_id available, storing validation URL in global option", 3, WP_CONTENT_DIR . '/debug.log');
            update_option('latest_validation_url', $validation_url, true);
        }
    } catch (Exception $e) {
        error_log("Error in referral processing: " . $e->getMessage(), 3, WP_CONTENT_DIR . '/debug.log');
    }

    return $fields;
}
if (!apply_filters('mmla_redirect_refer_a_patient_to_portal', true)) {
    add_action('wpforms_process', 'handle_referral_submission', 10, 3);
}

/**
 * Handle referral validation
 */
function handle_referral_validation() {
    date_default_timezone_set('America/Los_Angeles');

    if (isset($_GET['action']) && $_GET['action'] === 'validate_email' && 
        isset($_GET['token']) && isset($_GET['submission_id'])) {
        global $wpdb;
        $token = sanitize_text_field($_GET['token']);
        $submission_id = absint($_GET['submission_id']);
        // error_log("Validation attempt: Token=$token, Submission ID=$submission_id", 3, WP_CONTENT_DIR . '/debug.log');

        $table_name = $wpdb->prefix . 'referral_submissions';
        $submission = $wpdb->get_row($wpdb->prepare(
            "SELECT submission_id FROM {$table_name} 
             WHERE validation_token = %s AND submission_id = %d AND is_validated = 0",
            $token, $submission_id
        ));

        if ($submission) {
            $result = $wpdb->update(
                $table_name,
                array('is_validated' => 1),
                array('submission_id' => $submission->submission_id),
                array('%d'),
                array('%d')
            );
            if ($result === false) {
                error_log("ERROR: Failed to update is_validated for submission_id=$submission_id: " . $wpdb->last_error, 3, WP_CONTENT_DIR . '/debug.log');
                wp_die('Validation failed due to a database error.', 'Validation Error', array('response' => 500));
            }
            error_log("Validation successful: Updated is_validated for submission_id=$submission_id", 3, WP_CONTENT_DIR . '/debug.log');
            wp_die('Thank you! Your email has been successfully validated.', 'Email Validated', array('response' => 200));
        } else {
            error_log("ERROR: Invalid token or submission already validated for submission_id=$submission_id", 3, WP_CONTENT_DIR . '/debug.log');
            wp_die('Invalid validation link or already validated.', 'Validation Failed', array('response' => 400));
        }
    }
}
add_action('init', 'handle_referral_validation');
