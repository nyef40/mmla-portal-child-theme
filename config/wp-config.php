<?php
date_default_timezone_set('America/Los_Angeles');

// Set WordPress timezone
define('WP_TIMEZONE', 'America/Los_Angeles');

define('WP_DEBUG_LOG_TIMEZONE', 'America/Los_Angeles');
if (!function_exists('getenv_docker')) {
    function getenv_docker($env, $default) {
        if ($fileEnv = getenv($env . '_FILE')) {
            return rtrim(file_get_contents($fileEnv), "\r\n");
        } else if (($val = getenv($env)) !== false) {
            return $val;
        } else {
            return $default;
        }
    }
}
define('DB_NAME', getenv_docker('WORDPRESS_DB_NAME', 'wordpress'));
define('DB_USER', getenv_docker('WORDPRESS_DB_USER', 'wordpress'));
define('DB_PASSWORD', getenv_docker('WORDPRESS_DB_PASSWORD', 'wordpress'));
define('DB_HOST', getenv_docker('WORDPRESS_DB_HOST', 'db'));
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');
$table_prefix = 'lqbk_';
define('MMLA_ENCRYPTION_KEY', 'mmla_2025');
define('MYSQL_SSL', false);
define('MYSQL_CLIENT_FLAGS', 0);
define('FORCE_SSL_ADMIN', false);
define('WP_HOME', 'http://localhost:8080');
define('WP_SITEURL', 'http://localhost:8080');
// define('MYSQL_CLIENT_FLAGS', MYSQLI_CLIENT_NO_SCHEMA | MYSQLI_CLIENT_IGNORE_SPACE);

// Authentication Unique Keys and Salts
define('AUTH_KEY',         'rS@~13VF!%.@1eqjde`1iFY@jMB+A,6|^& {-IJG?sC`r?A)9onW8NL-I@5M5~-[');
define('SECURE_AUTH_KEY',  'DDt~F)k>!OY<OHa$=~Q=GM|Z(M/8U|F] /0@2-VW>4)T04xxs$egxE;g*j|*yAMl');
define('LOGGED_IN_KEY',    'S-(Fkg!Wii7m|>V;zy{zMT+$_x<j3-@]+jioWn)/F~7=eG&A$=I42!NYH7)CmD:n');
define('NONCE_KEY',        'u+h.A_I(`ezF}:UVe)hK(x|,#)-]Der rc#iMcW!u1f$Gps`1x7l6?:tn9V1=M!,');
define('AUTH_SALT',        'h3QD2Mq=,IYuX6WO3N/bJ0Igix:G<H!f+}#:c,!7BKuC(_nk(FKR&Qo<^-R>kPO$');
define('SECURE_AUTH_SALT', 'NY5q1zI?69wh{?-%b3V~`M_|*DoHq5oBmC[osoG4asG30Qv|l]&Nz-LMd>Co*dt|');
define('LOGGED_IN_SALT',   '7$ +q1$-X`/z-!L|WAq&4 +:WDg8%`LJZT&p6R6_uc8^wcqT=#i`$|,a=E*$1+rx');
define('NONCE_SALT',       't;|&PIC)U-}4/`N4$X6.cJkzP9=CNZbBe:w0]C/*Ah`L1z$ENf#;t>ru>;p]:Naa');
// For developers: WordPress debugging mode
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);
define('WP_DISABLE_FATAL_ERROR_HANDLER', false);
@ini_set('display_errors', 0);
@ini_set('log_errors', 1);
@ini_set('error_log', '/var/www/html/wp-content/debug.log');
error_reporting(E_ALL & ~E_DEPRECATED);

// WordPress SMTP
define('WPMS_ON', true);
define('WPMS_SMTP_HOST', 'mailhog');
define('WPMS_SMTP_PORT', 1025);
define('WPMS_SSL', 'none');
define('WPMS_FROM', 'no-reply@mmla.local');
define('WPMS_FROM_NAME', 'Mobile Medical LA');

if (!function_exists('wp_suppress_translation_notices')) {
    function wp_suppress_translation_notices() {
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            if (strpos($errstr, '_load_textdomain_just_in_time') !== false) {
                return true;
            }
            if (strpos($errstr, 'Translation loading') !== false) {
                return true;
            }
            if (strpos($errstr, 'blocksy') !== false && strpos($errstr, 'triggered too early') !== false) {
                return true;
            }
            if (strpos($errstr, 'wpforms') !== false && strpos($errstr, 'triggered too early') !== false) {
                return true;
            }
            if (strpos($errfile, 'symfony/css-selector') !== false && strpos($errstr, 'Deprecated') !== false) {
                return true;
            }
            return false;
        }, E_NOTICE | E_WARNING);
    }
}
wp_suppress_translation_notices();
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false) {
    $_SERVER['HTTPS'] = 'on';
}
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}
require_once ABSPATH . 'wp-settings.php';
