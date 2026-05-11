<?php
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_hook('before_invoke:db', function() {
        // Completely override wpdb connection
        add_filter('wpdb_connect_args', function($args) {
            $args = [
                'host' => 'db',
                'user' => 'wordpress',
                'pass' => 'wordpress',
                'name' => 'wordpress',
                'ssl' => false,
                'client_flags' => 0
            ];
            
            // Force MySQLi to not use SSL
            if (defined('MYSQLI_CLIENT_SSL')) {
                $args['client_flags'] &= ~MYSQLI_CLIENT_SSL;
            }
            
            error_log('WP-CLI SSL override: Forcing non-SSL connection');
            return $args;
        }, 9999);
        
        // Also set global variables
        global $wpdb;
        if ($wpdb) {
            $wpdb->db_connect(false); // Force reconnection with new settings
        }
    });
}
