<?php
wp_head();
if (!is_user_logged_in()) {
    wp_redirect('/portal/login');
    exit;
}
?>
<div id="portal-root"></div>
<script>window.PortalPage = 'dashboard';</script>
<?php
wp_footer();
