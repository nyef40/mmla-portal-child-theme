<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Service class for portal registration and portal_users persistence.
 */
class PortalRegistrationService
{
    private $wpdb;

    public function __construct($wpdb)
    {
        $this->wpdb = $wpdb;
    }

    public function register(array $post): array
    {
        if (!$this->isSecurityCheckValid($post)) {
            return ['ok' => false, 'message' => 'Security check failed'];
        }

        $payload = $this->buildPayload($post);
        if (empty($payload['username']) || empty($payload['email']) || empty($payload['password'])) {
            return ['ok' => false, 'message' => 'Please fill in all required fields'];
        }

        if (username_exists($payload['username'])) {
            return ['ok' => false, 'message' => 'Username already exists'];
        }
        if (email_exists($payload['email'])) {
            return ['ok' => false, 'message' => 'Email already registered'];
        }

        $user_id = wp_create_user($payload['username'], $payload['password'], $payload['email']);
        if (is_wp_error($user_id)) {
            return ['ok' => false, 'message' => $user_id->get_error_message()];
        }

        update_user_meta($user_id, 'first_name', $payload['first_name']);
        update_user_meta($user_id, 'last_name', $payload['last_name']);
        wp_update_user([
            'ID' => $user_id,
            'display_name' => trim($payload['first_name'] . ' ' . $payload['last_name']),
        ]);

        $token = wp_generate_password(32, false, false);
        $token_hash = hash('sha256', $token);
        $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $inserted = $this->wpdb->insert($this->table('portal_users'), [
            'wp_user_id'       => $user_id,
            'username'         => $payload['username'],
            'email'            => $payload['email'],
            'first_name'       => $payload['first_name'],
            'last_name'        => $payload['last_name'],
            'phone'            => $payload['phone'] ?: null,
            'practice'         => $payload['practice'] ?: null,
            'address'          => $payload['address'] ?: null,
            'city'             => $payload['city'] ?: null,
            'state'            => $payload['state'] ?: null,
            'zip'              => $payload['zip'] ?: null,
            'email_verified'   => 0,
            'specialty'        => $payload['specialty'] ?: null,
            'license_number'   => $payload['license_number'] ?: null,
            'role'             => $payload['role'] ?: null,
            'validation_token' => $token_hash,
            'token_expiry'     => $expiry,
            'created_at'       => current_time('mysql'),
        ]);

        if (!$inserted) {
            error_log('[Portal] Failed to insert portal_users record for user ' . $user_id . ': ' . $this->wpdb->last_error);
            return ['ok' => false, 'message' => 'Account could not be saved. Please try again or contact support.'];
        }

        if (function_exists('send_verification_email_direct')) {
            send_verification_email_direct($user_id, $payload['email'], $payload['first_name'], $token);
        }

        return ['ok' => true, 'user_id' => $user_id];
    }

    private function isSecurityCheckValid(array $post): bool
    {
        $nonce = $post['nonce'] ?? '';
        if (wp_verify_nonce($nonce, 'portal_register_nonce') || wp_verify_nonce($nonce, 'portal_nonce')) {
            return true;
        }

        // Fallback: one-time page token (cache/session edge cases on live).
        $page_token = $post['portal_register_token'] ?? '';
        if ($page_token === '') {
            return false;
        }

        $token_key = 'portal_reg_' . hash('sha256', $page_token);
        if (get_transient($token_key) !== '1') {
            return false;
        }

        delete_transient($token_key);
        return true;
    }

    private function buildPayload(array $post): array
    {
        return [
            'username' => sanitize_user($post['username'] ?? ''),
            'email' => sanitize_email($post['email'] ?? ''),
            'password' => $post['password'] ?? '',
            'first_name' => sanitize_text_field($post['first_name'] ?? ''),
            'last_name' => sanitize_text_field($post['last_name'] ?? ''),
            'practice' => sanitize_text_field($post['practice'] ?? ($post['practice_name'] ?? '')),
            'phone' => sanitize_text_field($post['phone'] ?? ''),
            'role' => sanitize_text_field($post['role'] ?? ''),
            'specialty' => sanitize_text_field($post['specialty'] ?? ''),
            'license_number' => sanitize_text_field($post['license_number'] ?? ''),
            'address' => sanitize_text_field($post['address'] ?? ''),
            'city' => sanitize_text_field($post['city'] ?? ''),
            'state' => sanitize_text_field($post['state'] ?? ''),
            'zip' => sanitize_text_field($post['zip'] ?? ''),
        ];
    }

    private function table(string $name): string
    {
        return $this->wpdb->prefix . $name;
    }
}
