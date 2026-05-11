<?php
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_hook('before_invoke:db query', function() {
        WP_CLI::add_command('db query', function($args, $assoc_args) {
            $query = $args[0];
            $cmd = "/usr/bin/env mariadb --no-defaults --no-auto-rehash --batch --skip-column-names --host='db' --user='wordpress' --password='wordpress' --default-character-set='utf8' --skip-ssl --execute='{$query}'";
            $result = WP_CLI\Process::create($cmd)->run();
            if ($result->return_code === 0) {
                WP_CLI::log($result->stdout);
            } else {
                WP_CLI::error($result->stderr);
            }
        }, ['shortdesc' => 'Execute a MySQL query with --skip-ssl']);
    });
}
