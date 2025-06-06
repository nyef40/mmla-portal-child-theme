<?php
date_default_timezone_set('America/Los_Angeles');
define('WP_DEBUG_LOG_TIMEZONE', 'America/Los_Angeles');
define('WP_CACHE', true); // WP-Optimize Cache
// ** Database settings - You can get this info from your web host ** //
define( 'DB_NAME', 'wordpress' );
define( 'DB_USER', 'wordpress' );
define( 'DB_PASSWORD', 'wordpress' );
define( 'DB_HOST', 'db' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );
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
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );
define('SCRIPT_DEBUG', false);
define('WP_DISABLE_FATAL_ERROR_HANDLER', true);
@ini_set('display_errors', 0);
$table_prefix = 'lqbk_';
define ('MMLA_ENCRYPTION_KEY', 'mmla_2025');
// Absolute path to the WordPress directory
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}
// Sets up WordPress vars and included files
require_once ABSPATH . 'wp-settings.php';
