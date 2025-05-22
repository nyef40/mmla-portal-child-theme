<?php
function mmla_portal_register_user($data) {
    global $wpdb;
    $user_id = wp_create_user($data['username'], $data['password'], $data['email']);
    if (!is_wp_error($user_id)) {
        $wpdb->insert($wpdb->prefix . 'portal_users', [
            'wp_user_id' => $user_id,
            'specialty' => $data['specialty'],
            'license_number' => $data['license_number'],
            'verified' => 0, // Explicit default
        ]);
    }
    return $user_id;
}

function mmla_portal_login($username, $password) {
    $user = wp_authenticate($username, $password);
    if (is_wp_error($user)) {
        return false;
    }
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, true);
    return true;
}
