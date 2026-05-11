<?php
/**
 * Template Name: Portal Referrals
 * Content is rendered by the React app (portal.js). This template only ensures auth and output header/footer.
 */
portal_debug("TEMPLATE LOADED", [
    'template' => basename(__FILE__),
    'user_logged_in' => is_user_logged_in(),
    'request_uri' => $_SERVER['REQUEST_URI']
]);

if (!is_user_logged_in() || (function_exists('user_has_portal_access_enhanced') && !user_has_portal_access_enhanced())) {
    portal_debug("REDIRECT TRIGGERED", [
        'reason' => !is_user_logged_in() ? 'not_logged_in' : 'no_portal_access',
        'redirect_to' => '/portal-login/'
    ]);
    wp_redirect(home_url('/portal-login/'));
    exit;
}

get_header();
// React app mounts in #portal-root (from portal-loader) and renders the Referrals view.
get_footer();
