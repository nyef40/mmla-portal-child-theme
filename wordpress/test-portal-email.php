<?php
require_once('wp-load.php');

// Simulate what happens during portal registration
function send_portal_verification_email($user_id) {
    error_log("send_portal_verification_email called for user: " . $user_id);
    
    $user = get_userdata($user_id);
    $email = $user->user_email;
    
    // Generate verification token (mimic your portal logic)
    $token = wp_generate_password(32, false);
    error_log("Generated token: " . $token);
    
    // Build verification link
    $verification_link = add_query_arg(array(
        'action' => 'verify_email',
        'token' => $token,
        'user_id' => $user_id
    ), site_url('/portal/verify/'));
    
    error_log("Verification link: " . $verification_link);
    
    // Email subject and content
    $subject = 'Verify Your Email - Mobile Medical LA Portal';
    $message = "Hello,\n\n";
    $message .= "Please verify your email by clicking this link:\n";
    $message .= $verification_link . "\n\n";
    $message .= "Thank you,\nMobile Medical LA Team";
    
    $headers = array('From: Mobile Medical LA Portal <no-reply@mmla.local>');
    
    // Send email
    $sent = wp_mail($email, $subject, $message, $headers);
    
    error_log("Email sent: " . ($sent ? 'Yes' : 'No'));
    return $sent;
}

// Test with a real user ID (use admin2 or admin3)
$test_user_id = 65; // admin2
send_portal_verification_email($test_user_id);

echo "Test completed. Check MailHog and error log.\n";
