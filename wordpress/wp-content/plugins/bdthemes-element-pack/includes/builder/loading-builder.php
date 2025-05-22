<?php
require_once 'meta.php';
require_once 'builder-cpt.php';
require_once 'builder-integration.php';
require_once 'themes-hooks/themes/astra.php';
require_once 'themes-hooks/themes/bbtheme.php';
require_once 'themes-hooks/themes/generatepress.php';
require_once 'themes-hooks/themes/genesis.php';
require_once 'themes-hooks/themes/neve.php';
require_once 'themes-hooks/themes/oceanwp.php';
require_once 'themes-hooks/theme-support.php';
require_once 'themes-hooks/activator.php';

function bdt_templates_render_elementor_content( $content_id ) {
	$elementor_instance = \Elementor\Plugin::instance();
	$has_css            = false;

	/**
	 * CSS Print Method Internal and Exteral option support for Header and Footer Builder.
	 */
	if ( ( 'internal' === get_option( 'elementor_css_print_method' ) ) || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
		$has_css = true;
	}

	return $elementor_instance->frontend->get_builder_content_for_display( $content_id, $has_css );
}