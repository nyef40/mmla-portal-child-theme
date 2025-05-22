<?php
/**
 * Plugin Name:       WPForms User Registration
 * Plugin URI:        https://wpforms.com
 * Description:       User Registration, Login and Reset Password forms with WPForms.
 * Requires at least: 4.9
 * Requires PHP:      5.6
 * Author:            WPForms
 * Author URI:        https://wpforms.com
 * Version:           2.1.0
 * Text Domain:       wpforms-user-registration
 * Domain Path:       languages
 *
 * WPForms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * WPForms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WPForms. If not, see <https://www.gnu.org/licenses/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WPForms.Comments.PHPDocDefine.MissPHPDoc
// Plugin constants.
define( 'WPFORMS_USER_REGISTRATION_VERSION', '2.1.0' );
define( 'WPFORMS_USER_REGISTRATION_FILE', __FILE__ );
define( 'WPFORMS_USER_REGISTRATION_PATH', plugin_dir_path( WPFORMS_USER_REGISTRATION_FILE ) );
define( 'WPFORMS_USER_REGISTRATION_URL', plugin_dir_url( WPFORMS_USER_REGISTRATION_FILE ) );
// phpcs:enable WPForms.Comments.PHPDocDefine.MissPHPDoc

/**
 * Load the provider class.
 *
 * @since 2.0.0
 */
function wpforms_user_registration_load() {

	// Check requirements.
	if ( ! wpforms_user_registration_required() ) {
		return;
	}

	// Load the plugin.
	wpforms_user_registration();
}

add_action( 'wpforms_loaded', 'wpforms_user_registration_load' );

/**
 * Check addon requirements.
 *
 * @since 2.0.0
 */
function wpforms_user_registration_required() {

	if ( version_compare( PHP_VERSION, '5.6', '<' ) ) {
		add_action( 'admin_init', 'wpforms_user_registration_deactivation' );
		add_action( 'admin_notices', 'wpforms_user_registration_fail_php_version' );

		return false;
	}

	if ( ! function_exists( 'wpforms' ) || ! wpforms()->pro ) {
		return false;
	}

	if ( version_compare( wpforms()->version, '1.7.6', '<' ) ) {
		add_action( 'admin_init', 'wpforms_user_registration_deactivation' );
		add_action( 'admin_notices', 'wpforms_user_registration_fail_wpforms_version' );

		return false;
	}

	if ( ! function_exists( 'wpforms_get_license_type' ) || ! in_array( wpforms_get_license_type(), [ 'pro', 'elite', 'agency', 'ultimate' ], true ) ) {
		return false;
	}

	return true;
}

/**
 * Deactivate the plugin.
 *
 * @since 2.0.0
 */
function wpforms_user_registration_deactivation() {

	deactivate_plugins( plugin_basename( __FILE__ ) );
}

/**
 * Admin notice for a minimum PHP version.
 *
 * @since 2.0.0
 */
function wpforms_user_registration_fail_php_version() {

	echo '<div class="notice notice-error"><p>';
	printf(
		wp_kses( /* translators: %s - WPForms.com documentation page URL. */
			__( 'The WPForms User Registration plugin has been deactivated. Your site is running an outdated version of PHP that is no longer supported and is not compatible with the User Registration addon. <a href="%s" target="_blank" rel="noopener noreferrer">Read more</a> for additional information.', 'wpforms-user-registration' ),
			[
				'a' => [
					'href'   => [],
					'rel'    => [],
					'target' => [],
				],
			]
		),
		'https://wpforms.com/docs/supported-php-version/'
	);
	echo '</p></div>';

	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
	// phpcs:enable WordPress.Security.NonceVerification.Recommended
}

/**
 * Admin notice for minimum WPForms version.
 *
 * @since 2.0.0
 */
function wpforms_user_registration_fail_wpforms_version() {

	echo '<div class="notice notice-error"><p>';
	esc_html_e( 'The WPForms User Registration plugin has been deactivated, because it requires WPForms v1.7.6 or later to work.', 'wpforms-user-registration' );
	echo '</p></div>';

	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
	// phpcs:enable WordPress.Security.NonceVerification.Recommended
}

/**
 * Get the instance of the `\WPFormsUserRegistration\Plugin` class.
 * This function is useful for quickly grabbing data used throughout the plugin.
 *
 * @since 2.0.0
 *
 * @return \WPFormsUserRegistration\Plugin
 */
function wpforms_user_registration() {

	// Actually, load the User Registration addon now, as we met all the requirements.
	require_once __DIR__ . '/vendor/autoload.php';

	return \WPFormsUserRegistration\Plugin::get_instance();
}

/**
 * Load the plugin updater.
 *
 * @since 1.0.0
 *
 * @param string $key License key.
 */
function wpforms_user_registration_updater( $key ) {

	new \WPForms_Updater(
		[
			'plugin_name' => 'WPForms User Registration',
			'plugin_slug' => 'wpforms-user-registration',
			'plugin_path' => plugin_basename( WPFORMS_USER_REGISTRATION_FILE ),
			'plugin_url'  => trailingslashit( WPFORMS_USER_REGISTRATION_URL ),
			'remote_url'  => WPFORMS_UPDATER_API,
			'version'     => WPFORMS_USER_REGISTRATION_VERSION,
			'key'         => $key,
		]
	);
}

add_action( 'wpforms_updater', 'wpforms_user_registration_updater' );
