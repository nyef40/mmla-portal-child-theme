<?php
/**
 * Template Name: Dashboard
 * Content is rendered by the React app (portal-loader). This template only ensures auth and output header/footer.
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!is_user_logged_in()) {
    wp_redirect(home_url('/portal-login/'));
    exit;
}

if (function_exists('user_has_portal_access_enhanced') && !user_has_portal_access_enhanced()) {
    wp_redirect(home_url('/portal-login/'));
    exit;
}

get_header();
// React app mounts in #portal-root (from portal-loader).
get_footer();
