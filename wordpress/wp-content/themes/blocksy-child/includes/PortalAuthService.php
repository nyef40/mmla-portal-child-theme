<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Service class for portal authentication and login side effects.
 */
class PortalAuthService
{
    private $wpdb;

    public function __construct($wpdb)
    {
        $this->wpdb = $wpdb;
    }

    public function isLoginNonceValid(string $nonce): bool
    {
        return wp_verify_nonce($nonce, 'portal_login_form_nonce')
            || wp_verify_nonce($nonce, 'portal_login_nonce')
            || wp_verify_nonce($nonce, 'portal_nonce');
    }

    public function login(string $username, string $password, bool $remember): array
    {
        $user = wp_authenticate($username, $password);
        if (is_wp_error($user)) {
            return ['ok' => false, 'message' => 'Invalid username or password'];
        }

        wp_clear_auth_cookie();
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, $remember, is_ssl());

        update_user_meta($user->ID, 'last_login', current_time('mysql'));
        $this->updatePortalLastLogin((int) $user->ID);

        return ['ok' => true, 'user' => $user];
    }

    private function updatePortalLastLogin(int $user_id): void
    {
        $this->wpdb->update(
            $this->table('portal_users'),
            ['last_login' => current_time('mysql')],
            ['wp_user_id' => $user_id],
            ['%s'],
            ['%d']
        );
    }

    private function table(string $name): string
    {
        return $this->wpdb->prefix . $name;
    }
}
