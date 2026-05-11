<?php
require_once('wp-load.php');

$to = 'test@example.com';
$subject = 'Test Email from WordPress';
$message = 'This is a test email to verify MailHog configuration.';
$headers = array('Content-Type: text/html; charset=UTF-8');

$sent = wp_mail($to, $subject, $message, $headers);

if ($sent) {
    echo "Email sent successfully! Check MailHog at http://localhost:8025\n";
} else {
    echo "Failed to send email.\n";
    global $phpmailer;
    if (isset($phpmailer)) {
        echo "Error: " . $phpmailer->ErrorInfo . "\n";
    }
}
