<?php
/**
 * Portal API Endpoints
 * File: portal-api.php
 * Path: /wp-content/themes/blocksy-child/portal/api/
 */

// Register API endpoints
function register_portal_api_endpoints() {
    // User profile endpoints
    register_rest_route('portal/v1', '/profile', [
        'methods' => 'GET',
        'callback' => 'get_portal_profile_api',
        'permission_callback' => function() {
            return is_user_logged_in();
        }
    ]);
    
    register_rest_route('portal/v1', '/profile', [
        'methods' => 'POST',
        'callback' => 'update_portal_profile_api',
        'permission_callback' => function() {
            return is_user_logged_in();
        }
    ]);
    
    // Resources endpoints
    register_rest_route('portal/v1', '/resources', [
        'methods' => 'GET',
        'callback' => 'get_portal_resources_api',
        'permission_callback' => function() {
            return is_user_logged_in();
        }
    ]);
    
    register_rest_route('portal/v1', '/resources/access', [
        'methods' => 'POST',
        'callback' => 'log_resource_access_api',
        'permission_callback' => function() {
            return is_user_logged_in();
        }
    ]);
}
add_action('rest_api_init', 'register_portal_api_endpoints');

// API callback functions
function get_portal_profile_api($request) {
    $user_id = get_current_user_id();
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'portal_users';
    
    $profile = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$table_name} WHERE wp_user_id = %d",
        $user_id
    ));
    
    if (!$profile) {
        // Create empty profile if it doesn't exist
        $wpdb->insert(
            $table_name,
            [
                'wp_user_id' => $user_id,
                'specialty' => '',
                'license_number' => '',
                'verified' => 0,
                'created_at' => current_time('mysql')
            ]
        );
        
        $profile = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE wp_user_id = %d",
            $user_id
        ));
    }
    
    return rest_ensure_response($profile);
}

function update_portal_profile_api($request) {
    $user_id = get_current_user_id();
    $params = $request->get_json_params();
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'portal_users';
    
    // Check if profile exists
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$table_name} WHERE wp_user_id = %d",
        $user_id
    ));
    
    if ($exists) {
        // Update existing profile
        $result = $wpdb->update(
            $table_name,
            [
                'specialty' => sanitize_text_field($params['specialty']),
                'license_number' => sanitize_text_field($params['license_number'])
            ],
            ['wp_user_id' => $user_id]
        );
    } else {
        // Create new profile
        $result = $wpdb->insert(
            $table_name,
            [
                'wp_user_id' => $user_id,
                'specialty' => sanitize_text_field($params['specialty']),
                'license_number' => sanitize_text_field($params['license_number']),
                'verified' => 0,
                'created_at' => current_time('mysql')
            ]
        );
    }
    
    if ($result === false) {
        return new WP_Error('update_failed', 'Failed to update profile', ['status' => 500]);
    }
    
    return rest_ensure_response(['success' => true]);
}

function get_portal_resources_api($request) {
    if (function_exists('mmla_portal_get_resources_normalized')) {
        return rest_ensure_response(mmla_portal_get_resources_normalized());
    }
    return rest_ensure_response([]);
}

function log_resource_access_api($request) {
    $user_id = get_current_user_id();
    $params = $request->get_json_params();
    $resource_id = intval($params['resource_id']);
    
    global $wpdb;
    $portal_users_table = $wpdb->prefix . 'portal_users';
    $access_logs_table = $wpdb->prefix . 'portal_access_logs';
    
    // Get portal user ID
    $portal_user_id = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$portal_users_table} WHERE wp_user_id = %d",
        $user_id
    ));
    
    if (!$portal_user_id) {
        return new WP_Error('user_not_found', 'Portal user not found', ['status' => 404]);
    }
    
    // Log access
    $result = $wpdb->insert(
        $access_logs_table,
        [
            'user_id' => $portal_user_id,
            'resource_id' => $resource_id,
            'accessed_at' => current_time('mysql'),
            'action' => 'view',
            'details' => json_encode([
                'ip' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT']
            ])
        ]
    );
    
    if ($result === false) {
        return new WP_Error('log_failed', 'Failed to log resource access', ['status' => 500]);
    }
    
    return rest_ensure_response(['success' => true]);
}