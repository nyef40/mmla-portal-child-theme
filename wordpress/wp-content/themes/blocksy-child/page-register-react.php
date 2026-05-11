<?php
/**
 * Template Name: Register React
 * Description: React-based registration page for the portal system.
 */
if (is_user_logged_in()) {
    wp_redirect(home_url('/dashboard/'));
    exit;
}
get_header();
?>
<div id="portal-register-root"></div>
<?php
wp_enqueue_script('portal-register-js', get_stylesheet_directory_uri() . '/portal/dist/register.js', [], filemtime(get_stylesheet_directory() . '/portal/dist/register.js'), true);
wp_enqueue_style('portal-css', get_stylesheet_directory_uri() . '/portal/dist/portal.css', [], filemtime(get_stylesheet_directory() . '/portal/dist/portal.css'));
wp_localize_script('portal-register-js', 'portalSettings', [
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('portal_register_nonce')
]);
get_footer();