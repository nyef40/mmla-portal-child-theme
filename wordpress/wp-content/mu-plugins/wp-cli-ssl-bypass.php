<?php
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_hook('before_invoke:db', function() {
        $db_config = [
            'host' => 'db',
            'user' => 'wordpress',
            'pass' => 'wordpress',
            'name' => 'wordpress',
            'ssl' => false,
            'client_flags' => 0
        ];
        WP_CLI::add_wp_hook('wpdb_connect_args', function($args) use ($db_config) {
            $args['ssl'] = false;
            $args['client_flags'] = 0;
            error_log('WP-CLI SSL bypass: ' . print_r($args, true));
            return $args;
        }, 9999);
        error_log('WP-CLI DB config: ' . print_r($db_config, true));
    });
}
