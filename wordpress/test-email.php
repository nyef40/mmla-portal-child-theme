<?php
require_once '/var/www/html/wp-load.php';
$to = 'testuser15@example.com';
$subject = 'Test Email from WordPress';
$message = '<h2>Test Email</h2><p>This is a test email sent from WordPress.</p>';
$headers = [
    'Content-Type: text/html; charset=UTF-8',
    'From: ' . (defined('WPMS_FROM_NAME') ? WPMS_FROM_NAME : 'Mobile Medical LA') . ' <' . (defined('WPMS_FROM') ? WPMS_FROM : 'no-reply@mmla.local') . '>'
];
add_action('wp_mail_failed', function($wp_error) {
    error_log('Test email failed: ' . print_r($wp_error, true));
});
add_filter('wp_mail', function($args) {
    error_log('wp_mail args: ' . print_r($args, true));
    return $args;
});
error_log('Sending test email with From: ' . (defined('WPMS_FROM_NAME') ? WPMS_FROM_NAME : 'Mobile Medical LA') . ' <' . (defined('WPMS_FROM') ? WPMS_FROM : 'no-reply@mmla.local') . '>');
$result = wp_mail($to, $subject, $message, $headers);
error_log('Test email result: ' . ($result ? 'Success' : 'Failed'));
echo $result ? 'Email sent' : 'Email failed';
?>
