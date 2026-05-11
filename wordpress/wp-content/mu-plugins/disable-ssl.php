<?php
add_filter('database_connection_params', function($params) {
    $params['ssl'] = false;
    $params['mysql_client_flags'] = MYSQLI_CLIENT_NO_SCHEMA | MYSQLI_CLIENT_IGNORE_SPACE;
    error_log('wpdb SSL disabled: ' . print_r($params, true));
    return $params;
}, 9999);
