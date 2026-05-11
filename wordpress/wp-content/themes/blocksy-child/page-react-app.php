<?php
/**
 * Template Name: React App (Unified)
 * Description: Single page template for entire React application
 */

// Disable WordPress admin bar for React app
show_admin_bar(false);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class('react-app-page'); ?>>
    <!-- React Mount Point -->
    <div id="react-root"></div>
    
    <!-- WordPress Data Bridge -->
    <script>
        window.wpData = {
            ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
            nonce: '<?php echo wp_create_nonce('portal_nonce'); ?>',
            restUrl: '<?php echo esc_url_raw(rest_url()); ?>',
            restNonce: '<?php echo wp_create_nonce('wp_rest'); ?>',
            isLoggedIn: <?php echo is_user_logged_in() ? 'true' : 'false'; ?>,
            userId: <?php echo get_current_user_id(); ?>,
            siteUrl: '<?php echo esc_url(home_url('/')); ?>'
        };
    </script>
    
    <?php wp_footer(); ?>
</body>
</html>
