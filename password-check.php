<?php
require_once('wp-load.php');

$stored_hash = '$wp$2y$10$87chC.v0bQHmPZFhB9m1be3RnkYbaPBwj1F0W8W5R5.Kz7ThlYlrm';
$password_to_check = 'tiger2025';

if (wp_check_password($password_to_check, $stored_hash)) {
    echo "Password 'tiger2025' matches the stored hash!";
} else {
    echo "Password does not match.";
}
